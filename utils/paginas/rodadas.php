<?php
$participantes = carregar_participantes();
$torneio       = carregar_torneio();

cabecalho('Rodadas e Placares');
mensagens_flash();

if ($participantes === null || $torneio === null) {
    echo '<p class="msg msg-erro">As rodadas ainda não foram geradas. '
       . 'Passe pelo <a href="' . e(url_para('inicio')) . '">menu inicial</a> para configurar o torneio.</p>';
    rodape();
    exit;
}

$nomes = mapa_nomes($participantes);

function nome_dupla(array $dupla, array $nomes): string
{
    return e($nomes[$dupla[0]] . ' & ' . $nomes[$dupla[1]]);
}

$atual  = rodada_atual($torneio);
$total  = count($torneio['rodadas']);
$feitas = $atual === null ? $total : $atual - 1;

$editar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
?>

<p class="msg msg-info">
    Formato: <strong><?= $torneio['formato'] === 'fixas' ? '👥 duplas fixas' : '🔁 duplas rotativas' ?></strong>
    <?php if ($atual !== null): ?>
        — em andamento: <strong>Rodada <?= $atual ?> de <?= $total ?></strong>
        (faltam <?= $total - $feitas ?>)
    <?php else: ?>
        — <strong>todas as rodadas concluídas! 🏆</strong>
        Confira a <a href="<?= e(url_para('classificacao')) ?>">classificação final</a>.
    <?php endif; ?>
</p>
<div class="progresso-wrap">
    <div class="progresso-label" aria-hidden="true">
        <span>Progresso</span>
        <span><?= $feitas ?> de <?= $total ?> rodadas concluídas</span>
    </div>
    <div class="progresso" role="progressbar" aria-valuemin="0" aria-valuemax="<?= $total ?>"
         aria-valuenow="<?= $feitas ?>"
         aria-label="<?= $feitas ?> de <?= $total ?> rodadas concluídas">
        <div class="progresso-barra" style="width: <?= round($feitas / $total * 100) ?>%"></div>
    </div>
</div>

<?php foreach ($torneio['rodadas'] as $rodada):
    $numero    = $rodada['numero'];
    $concluida = rodada_completa($rodada);
    $ehAtual   = $numero === $atual;
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
        $completa = partida_completa($partida);
        $editando = $ehAtual && (!$completa || $editar === $indice); ?>
    <article class="partida" id="partida-<?= $numero ?>-<?= $indice ?>">
        <p class="confronto">
            <span class="quadra">Quadra <?= $partida['quadra'] ?></span>
            <strong><?= nome_dupla($partida['dupla_a'], $nomes) ?></strong>
            <span class="vs">×</span>
            <strong><?= nome_dupla($partida['dupla_b'], $nomes) ?></strong>
        </p>

        <?php if ($completa && !$editando): ?>
            <p class="placar-final">
                Placar: <strong><?= $partida['games_a'] ?> × <?= $partida['games_b'] ?></strong>
                <?php if ($ehAtual): ?>
                    <a class="botao botao-mini"
                       href="<?= e(url_para('rodadas') . '&editar=' . $indice) ?>#partida-<?= $numero ?>-<?= $indice ?>">
                        ✏️ Editar
                    </a>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <?php if ($editando): ?>
            <form class="form-placar" method="post" action="index.php?acao=salvar_placar">
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
            </form>
        <?php elseif (!$completa): ?>
            <p class="aguardando">Disponível quando a rodada anterior for concluída.</p>
        <?php endif; ?>
    </article>
    <?php endforeach; ?>
</section>
<?php endforeach; ?>

<?php rodape(); ?>
