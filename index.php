<?php
require_once __DIR__ . '/utils/json_helper.php';
require_once __DIR__ . '/utils/layout.php';

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
       href="participantes/cadastro.php">
        <div class="cartao-icone" aria-hidden="true">👤</div>
        <div class="cartao-texto">
            <h3>1. Participantes</h3>
            <p>Cadastrar os 8 jogadores do torneio.</p>
        </div>
    </a>
    <a class="cartao <?= in_array($estado, ['em_andamento', 'finalizado']) ? 'feito' : '' ?> <?= $estado === 'sem_participantes' ? 'bloqueado' : '' ?>"
       href="configuracao/configuracao.php">
        <div class="cartao-icone" aria-hidden="true">⚙️</div>
        <div class="cartao-texto">
            <h3>2. Formato e Rodadas</h3>
            <p>Escolher duplas fixas ou rotativas e gerar as 7 rodadas.</p>
        </div>
    </a>
    <a class="cartao <?= !in_array($estado, ['em_andamento', 'finalizado']) ? 'bloqueado' : '' ?>"
       href="rodadas/rodadas.php">
        <div class="cartao-icone" aria-hidden="true">🎾</div>
        <div class="cartao-texto">
            <h3>3. Rodadas e Placares</h3>
            <p>Acompanhar os confrontos e lançar os resultados.</p>
        </div>
    </a>
    <a class="cartao <?= !in_array($estado, ['em_andamento', 'finalizado']) ? 'bloqueado' : '' ?>"
       href="classificacao/classificacao.php">
        <div class="cartao-icone" aria-hidden="true">🏆</div>
        <div class="cartao-texto">
            <h3>4. Classificação</h3>
            <p>Tabela de pontuação atualizada em tempo real.</p>
        </div>
    </a>
</div>

<?php if ($estado !== 'sem_participantes'): ?>
<form action="reiniciar.php" method="post" class="form-reiniciar" data-confirmar
      data-mensagem="Tem certeza? Todos os participantes, rodadas e placares serão apagados.">
    <button type="submit" class="botao botao-perigo">🔄 Reiniciar torneio (apagar tudo)</button>
</form>
<?php endif; ?>

<?php rodape(); ?>
