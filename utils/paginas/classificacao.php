<?php
$participantes = carregar_participantes();
$torneio       = carregar_torneio();

if ($participantes === null || $torneio === null) {
    cabecalho('Classificação');
    mensagens_flash();
    echo '<p class="msg msg-erro">O torneio ainda não foi gerado. '
       . 'Passe pelo <a href="' . e(url_para('inicio')) . '">menu inicial</a> para configurar.</p>';
    rodape();
    exit;
}

$finalizado   = rodada_atual($torneio) === null;
$formatoFixas = $torneio['formato'] === 'fixas';
$ranking      = $formatoFixas
    ? calcular_classificacao_duplas($participantes, $torneio)
    : calcular_classificacao($participantes, $torneio);
$series       = $formatoFixas
    ? evolucao_pontuacao_duplas($participantes, $torneio)
    : evolucao_pontuacao($participantes, $torneio);

cabecalho('Classificação', $finalizado ? null : 20);
mensagens_flash();

function tabela_classificacao(array $linhas, string $rotulo): void
{
    ?>
    <div class="tabela-rolagem">
    <table class="tabela-classificacao">
        <thead>
        <tr>
            <th>#</th><th><?= e($rotulo) ?></th><th>J</th><th>V</th><th>D</th>
            <th>GP</th><th>GC</th><th>Saldo</th><th>Pontos</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($linhas as $pos => $l): ?>
        <tr class="<?= $pos === 0 ? 'lider' : '' ?>">
            <td><?= $pos + 1 ?>º</td>
            <td class="celula-nome">
                <?= e($l['nome']) ?>
                <?php if (($l['apelido'] ?? '') !== ''): ?>
                    <small>(<?= e($l['apelido']) ?>)</small>
                <?php endif; ?>
            </td>
            <td><?= $l['jogos'] ?></td>
            <td><?= $l['vitorias'] ?></td>
            <td><?= $l['derrotas'] ?></td>
            <td><?= $l['games_pro'] ?></td>
            <td><?= $l['games_contra'] ?></td>
            <td><?= $l['saldo'] > 0 ? '+' : '' ?><?= $l['saldo'] ?></td>
            <td class="celula-pontos"><?= $l['pontos'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php
}

function grafico_evolucao(array $series, int $totalRodadas): void
{
    if (count(reset($series) ?: []) < 1) {
        echo '<p class="aguardando">O gráfico aparece após a primeira rodada concluída.</p>';
        return;
    }

    $cores = ['#e63946', '#f4a261', '#2a9d8f', '#264653', '#7b2cbf', '#0077b6', '#d4a017', '#6a994e'];
    $larg = 680; $alt = 300; $mEsq = 40; $mInf = 30; $mSup = 15; $mDir = 15;
    $maximo  = max(1, max(array_map('max', $series)));
    $passoX  = ($larg - $mEsq - $mDir) / max(1, $totalRodadas);
    $escalaY = ($alt - $mSup - $mInf) / $maximo;

    $x = fn(int $r): float => $mEsq + $r * $passoX;
    $y = fn(int $pts): float => $alt - $mInf - $pts * $escalaY;
    ?>
    <svg viewBox="0 0 <?= $larg ?> <?= $alt ?>" class="grafico" role="img"
         aria-label="Evolução da pontuação por rodada">
        <?php for ($g = 0; $g <= 4; $g++): $vy = $mSup + $g * ($alt - $mSup - $mInf) / 4; ?>
            <line x1="<?= $mEsq ?>" y1="<?= $vy ?>" x2="<?= $larg - $mDir ?>" y2="<?= $vy ?>"
                  stroke="#ddd" stroke-width="1"/>
            <text x="<?= $mEsq - 6 ?>" y="<?= $vy + 4 ?>" text-anchor="end" font-size="11"
                  fill="#666"><?= round($maximo * (4 - $g) / 4) ?></text>
        <?php endfor; ?>
        <?php for ($r = 1; $r <= $totalRodadas; $r++): ?>
            <text x="<?= $x($r) ?>" y="<?= $alt - 8 ?>" text-anchor="middle" font-size="11"
                  fill="#666">R<?= $r ?></text>
        <?php endfor; ?>
        <?php $i = 0;
        foreach ($series as $pontos):
            $linha = $x(0) . ',' . $y(0);
            foreach ($pontos as $r => $pts) {
                $linha .= ' ' . $x($r + 1) . ',' . $y($pts);
            }
        ?>
            <polyline points="<?= $linha ?>" fill="none" stroke="<?= $cores[$i % count($cores)] ?>"
                      stroke-width="2.5" stroke-linejoin="round"/>
        <?php $i++; endforeach; ?>
    </svg>
    <ul class="legenda">
        <?php $i = 0; foreach ($series as $rotulo => $pontos): ?>
            <li><span class="cor" style="background: <?= $cores[$i % count($cores)] ?>"></span>
                <?= e((string) $rotulo) ?></li>
        <?php $i++; endforeach; ?>
    </ul>
    <?php
}
?>

<p class="msg msg-info">
    <?php if ($finalizado): ?>
        🏆 Torneio finalizado — <?= $formatoFixas ? 'dupla campeã' : 'campeão(ã)' ?>:
        <strong><?= e($ranking[0]['nome']) ?></strong>!
    <?php else: ?>
        Tabela atualizada automaticamente a cada 20 segundos
        (rodada atual: <?= rodada_atual($torneio) ?> de <?= count($torneio['rodadas']) ?>).
    <?php endif; ?>
</p>

<p class="acoes-classificacao">
    <button type="button" class="botao botao-mini" onclick="window.print()">
        🖨️ Imprimir / exportar
    </button>
    <noscript>Para imprimir ou exportar, use <kbd>Ctrl</kbd>+<kbd>P</kbd>.</noscript>
</p>

<h3>Ranking <?= $formatoFixas ? 'das duplas' : 'individual' ?></h3>
<?php tabela_classificacao($ranking, $formatoFixas ? 'Dupla' : 'Jogador'); ?>

<details class="regras" open>
    <summary>Regras de pontuação e desempate</summary>
    <ul>
        <li><strong>Vitória:</strong> +2 pontos · <strong>Derrota:</strong> 0 pontos ·
            <strong>Cada game vencido:</strong> +1 ponto. Empates não são permitidos.</li>
        <li><strong>Desempate:</strong> 1) pontos, 2) saldo de games, 3) games vencidos,
            4) vitórias, 5) ordem alfabética.</li>
        <li>J = jogos · V/D = vitórias e derrotas · GP/GC = games pró e contra.</li>
    </ul>
</details>

<h3>📊 Evolução da pontuação<?= $formatoFixas ? ' das duplas' : '' ?></h3>
<?php grafico_evolucao($series, count($torneio['rodadas'])); ?>

<?php rodape(); ?>
