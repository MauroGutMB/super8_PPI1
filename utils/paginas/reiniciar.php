<?php
if (estado_torneio() === 'sem_participantes') {
    redirecionar('inicio', erro: 'Não há torneio para reiniciar.');
}

cabecalho('Reiniciar Torneio');
mensagens_flash();
?>

<p class="msg msg-erro">
    ⚠️ Tem certeza? <strong>Todos os participantes, rodadas e placares serão apagados.</strong>
    Esta ação não pode ser desfeita.
</p>

<form action="index.php?acao=reiniciar" method="post" class="form-reiniciar">
    <button type="submit" class="botao botao-perigo">🔄 Sim, apagar tudo e reiniciar</button>
    <a class="botao" href="<?= e(url_para('inicio')) ?>">Cancelar e voltar ao menu</a>
</form>

<?php rodape(); ?>
