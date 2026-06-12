<?php
require_once __DIR__ . '/../utils/json_helper.php';
require_once __DIR__ . '/../utils/pontuacao.php';
require_once __DIR__ . '/../utils/layout.php';

$participantes = carregar_participantes();
$torneio       = carregar_torneio();

cabecalho('Rodadas e Placares', '..');
mensagens_flash();

if ($participantes === null || $torneio === null) {
    echo '<p class="msg msg-erro">As rodadas ainda não foram geradas. '
       . 'Passe pelo <a href="../index.php">menu inicial</a> para configurar o torneio.</p>';
    rodape('..');
    exit;
}

$nomes = [];
foreach ($participantes as $p) {
    $nomes[$p['id']] = $p['apelido'] !== '' ? $p['apelido'] : $p['nome'];
}

function nome_dupla(array $dupla, array $nomes): string
{
    return e($nomes[$dupla[0]] . ' & ' . $nomes[$dupla[1]]);
}

$atual = rodada_atual($torneio);
$total = count($torneio['rodadas']);
$feitas = $atual === null ? $total : $atual - 1;
?>

<p class="msg msg-info">
    Formato: <strong><?= $torneio['formato'] === 'fixas' ? '👥 duplas fixas' : '🔁 duplas rotativas' ?></strong>
    <?php if ($atual !== null): ?>
        — em andamento: <strong>Rodada <?= $atual ?> de <?= $total ?></strong>
        (faltam <?= $total - $feitas ?>)
    <?php else: ?>
        — <strong>todas as rodadas concluídas! 🏆</strong>
        Confira a <a href="../classificacao/classificacao.php">classificação final</a>.
    <?php endif; ?>
</p>
<div class="progresso" role="progressbar" aria-valuemin="0" aria-valuemax="<?= $total ?>"
     aria-valuenow="<?= $feitas ?>">
    <div class="progresso-barra" style="width: <?= round($feitas / $total * 100) ?>%"></div>
</div>

<?php foreach ($torneio['rodadas'] as $rodada):
    $numero    = $rodada['numero'];
    $concluida = true;
    foreach ($rodada['partidas'] as $p) {
        if (!partida_completa($p)) {
            $concluida = false;
            break;
        }
    }
    $ehAtual = $numero === $atual;
    $classe  = $ehAtual ? 'rodada-atual' : ($concluida ? 'rodada-concluida' : 'rodada-futura');
?>
<section class="rodada <?= $classe ?>">
    <h3>
        Rodada <?= $numero ?> de <?= $total ?>
        <?php if ($ehAtual): ?><span class="selo selo-atual">em andamento</span>
        <?php elseif ($concluida): ?><span class="selo selo-ok">concluída ✓</span>
        <?php else: ?><span class="selo selo-espera">aguardando</span><?php endif; ?>
    </h3>

    <?php foreach ($rodada['partidas'] as $indice => $partida):
        $completa = partida_completa($partida); ?>
    <article class="partida">
        <p class="confronto">
            <span class="quadra">Quadra <?= $partida['quadra'] ?></span>
            <strong><?= nome_dupla($partida['dupla_a'], $nomes) ?></strong>
            <span class="vs">×</span>
            <strong><?= nome_dupla($partida['dupla_b'], $nomes) ?></strong>
        </p>

        <?php if ($completa): ?>
            <p class="placar-final">
                Placar: <strong><?= $partida['games_a'] ?> × <?= $partida['games_b'] ?></strong>
                <button type="button" class="botao botao-mini botao-editar">✏️ Editar</button>
            </p>
        <?php endif; ?>

        <?php if ($ehAtual && !$completa || $completa): ?>
            <form class="form-placar <?= $completa ? 'oculto' : '' ?>" method="post"
                  action="salvar_placar.php">
                <input type="hidden" name="rodada" value="<?= $numero ?>">
                <input type="hidden" name="partida" value="<?= $indice ?>">
                <label>
                    <?= nome_dupla($partida['dupla_a'], $nomes) ?>
                    <input type="number" name="games_a" min="0" max="7" required
                           inputmode="numeric" value="<?= $partida['games_a'] ?? '' ?>">
                </label>
                <span class="vs">×</span>
                <label>
                    <?= nome_dupla($partida['dupla_b'], $nomes) ?>
                    <input type="number" name="games_b" min="0" max="7" required
                           inputmode="numeric" value="<?= $partida['games_b'] ?? '' ?>">
                </label>
                <button type="submit" class="botao botao-mini">Salvar placar</button>
                <output class="feedback"></output>
            </form>
        <?php elseif (!$completa): ?>
            <p class="aguardando">Disponível quando a rodada anterior for concluída.</p>
        <?php endif; ?>
    </article>
    <?php endforeach; ?>
</section>
<?php endforeach; ?>

<?php rodape('..'); ?>
