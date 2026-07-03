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

$individual = calcular_classificacao($participantes, $torneio);
$finalizado = rodada_atual($torneio) === null;

// Enquanto o torneio está em andamento a página recarrega sozinha (meta refresh),
// mantendo a tabela atualizada sem JavaScript.
cabecalho('Classificação', $finalizado ? null : 20);
mensagens_flash();

function tabela_classificacao(array $linhas, string $rotulo): void
{
    ?>
    <div class="tabela-rolagem">
    <table class="tabela-classificacao">
        <thead>
        <tr>
            <th>#</th><th><?= e($rotulo) ?></th><th>J</th><th>V</th><th>E</th><th>D</th>
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
            <td><?= $l['empates'] ?></td>
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

/** Gráfico de linhas (SVG) com a evolução de pontos por rodada, gerado em PHP. */
function grafico_evolucao(array $participantes, array $torneio): void
{
    $evolucao = evolucao_pontuacao($participantes, $torneio);
    $rodadas  = count(reset($evolucao) ?: []);
    if ($rodadas < 1) {
        echo '<p class="aguardando">O gráfico aparece após a primeira rodada concluída.</p>';
        return;
    }

    $cores = ['#e63946', '#f4a261', '#2a9d8f', '#264653', '#7b2cbf', '#0077b6', '#d4a017', '#6a994e'];
    $larg = 680; $alt = 300; $mEsq = 40; $mInf = 30; $mSup = 15; $mDir = 15;
    $maximo = max(1, max(array_map('max', $evolucao)));
    $passoX = ($larg - $mEsq - $mDir) / max(1, count($torneio['rodadas']));
    $escalaY = ($alt - $mSup - $mInf) / $maximo;

    $x = fn(int $r): float => $mEsq + $r * $passoX;          // r = 0 é o início (0 pontos)
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
        <?php for ($r = 1; $r <= count($torneio['rodadas']); $r++): ?>
            <text x="<?= $x($r) ?>" y="<?= $alt - 8 ?>" text-anchor="middle" font-size="11"
                  fill="#666">R<?= $r ?></text>
        <?php endfor; ?>
        <?php $i = 0;
        foreach ($participantes as $p):
            $pontosJogador = $evolucao[$p['id']];
            $pontosLinha = $x(0) . ',' . $y(0);
            foreach ($pontosJogador as $r => $pts) {
                $pontosLinha .= ' ' . $x($r + 1) . ',' . $y($pts);
            }
            $cor = $cores[$i % count($cores)];
        ?>
            <polyline points="<?= $pontosLinha ?>" fill="none" stroke="<?= $cor ?>"
                      stroke-width="2.5" stroke-linejoin="round"/>
        <?php $i++; endforeach; ?>
    </svg>
    <ul class="legenda">
        <?php $i = 0; foreach ($participantes as $p): ?>
            <li><span class="cor" style="background: <?= $cores[$i % count($cores)] ?>"></span>
                <?= e($p['apelido'] !== '' ? $p['apelido'] : $p['nome']) ?></li>
        <?php $i++; endforeach; ?>
    </ul>
    <?php
}
?>

<p class="msg msg-info">
    <?php if ($finalizado): ?>
        🏆 Torneio finalizado — campeão(ã):
        <strong><?= e($individual[0]['nome']) ?></strong>!
    <?php else: ?>
        Tabela atualizada automaticamente a cada 20 segundos
        (rodada atual: <?= rodada_atual($torneio) ?> de <?= count($torneio['rodadas']) ?>).
        Para imprimir ou exportar, use <kbd>Ctrl</kbd>+<kbd>P</kbd>.
    <?php endif; ?>
</p>

<?php if ($torneio['formato'] === 'fixas'): ?>
    <h3>Ranking das duplas</h3>
    <?php tabela_classificacao(
        calcular_classificacao_duplas($participantes, $torneio),
        'Dupla'
    ); ?>
    <h3>Ranking individual</h3>
<?php endif; ?>

<?php tabela_classificacao($individual, 'Jogador'); ?>

<details class="regras" open>
    <summary>Regras de pontuação e desempate</summary>
    <ul>
        <li><strong>Vitória:</strong> +2 pontos · <strong>Empate (4×4):</strong> +1 ponto ·
            <strong>Derrota:</strong> 0 pontos · <strong>Cada game vencido:</strong> +1 ponto.</li>
        <li><strong>Desempate:</strong> 1) pontos, 2) saldo de games, 3) games vencidos,
            4) vitórias, 5) ordem alfabética.</li>
        <li>J = jogos · V/E/D = vitórias, empates e derrotas · GP/GC = games pró e contra.</li>
    </ul>
</details>

<h3>📊 Evolução da pontuação</h3>
<?php grafico_evolucao($participantes, $torneio); ?>

<?php rodape(); ?>
