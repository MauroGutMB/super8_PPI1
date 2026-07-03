<?php
$estado  = estado_torneio();
$torneio = carregar_torneio();
$rotulos = [
    'sem_participantes' => 'Aguardando cadastro dos 8 participantes',
    'sem_rodadas'       => 'Participantes prontos — escolha o formato e gere as rodadas',
    'em_andamento'      => 'Torneio em andamento',
    'finalizado'        => 'Torneio finalizado 🏆',
];

cabecalho('Menu do Sistema');
mensagens_flash();
?>

<p class="msg msg-info">Status: <strong><?= e($rotulos[$estado]) ?></strong>
<?php if ($estado === 'em_andamento' && $torneio !== null):
    $atual = rodada_atual($torneio); ?>
    — Rodada <?= $atual ?> de <?= count($torneio['rodadas']) ?>
<?php endif; ?>
</p>

<div class="cartoes">
    <a class="cartao <?= $estado !== 'sem_participantes' ? 'feito' : '' ?>"
       href="<?= e(url_para('participantes')) ?>">
        <div class="cartao-icone" aria-hidden="true">👤</div>
        <div class="cartao-texto">
            <h3>1. Participantes</h3>
            <p>Cadastrar os 8 jogadores do torneio.</p>
        </div>
    </a>
    <a class="cartao <?= in_array($estado, ['em_andamento', 'finalizado']) ? 'feito' : '' ?> <?= $estado === 'sem_participantes' ? 'bloqueado' : '' ?>"
       href="<?= e(url_para('configuracao')) ?>">
        <div class="cartao-icone" aria-hidden="true">⚙️</div>
        <div class="cartao-texto">
            <h3>2. Formato e Rodadas</h3>
            <p>Escolher duplas fixas ou rotativas e gerar as 7 rodadas.</p>
        </div>
    </a>
    <a class="cartao <?= !in_array($estado, ['em_andamento', 'finalizado']) ? 'bloqueado' : '' ?>"
       href="<?= e(url_para('rodadas')) ?>">
        <div class="cartao-icone" aria-hidden="true">🎾</div>
        <div class="cartao-texto">
            <h3>3. Rodadas e Placares</h3>
            <p>Acompanhar os confrontos e lançar os resultados.</p>
        </div>
    </a>
    <a class="cartao <?= !in_array($estado, ['em_andamento', 'finalizado']) ? 'bloqueado' : '' ?>"
       href="<?= e(url_para('classificacao')) ?>">
        <div class="cartao-icone" aria-hidden="true">🏆</div>
        <div class="cartao-texto">
            <h3>4. Classificação</h3>
            <p>Tabela de pontuação atualizada em tempo real.</p>
        </div>
    </a>
</div>

<?php if ($estado !== 'sem_participantes'): ?>
<p class="form-reiniciar">
    <a class="botao botao-perigo" href="<?= e(url_para('reiniciar')) ?>">
        🔄 Reiniciar torneio (apagar tudo)
    </a>
</p>
<?php endif; ?>

<?php rodape(); ?>
