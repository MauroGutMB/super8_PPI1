<?php
$participantes = carregar_participantes() ?? [];
$torneio       = carregar_torneio();

cabecalho('Cadastro de Participantes');
mensagens_flash();

if ($torneio !== null): ?>
    <p class="msg msg-erro">
        As rodadas já foram geradas, então os participantes não podem mais ser alterados.
        Para um novo evento, use <strong>Reiniciar torneio</strong> no menu inicial.
    </p>
<?php else: ?>
    <p>Informe os <strong>8 participantes</strong> do Super 8. O apelido é opcional
       e, quando preenchido, é usado nas tabelas e confrontos.</p>

    <form action="index.php?acao=salvar_participantes" method="post" class="formulario">
        <?php for ($i = 0; $i < 8; $i++): ?>
            <fieldset class="linha-jogador">
                <legend>Jogador <?= $i + 1 ?></legend>
                <label>
                    Nome completo *
                    <input type="text" name="nome[]" required maxlength="60"
                           value="<?= e($participantes[$i]['nome'] ?? '') ?>">
                </label>
                <label>
                    Apelido (opcional)
                    <input type="text" name="apelido[]" maxlength="30"
                           value="<?= e($participantes[$i]['apelido'] ?? '') ?>">
                </label>
            </fieldset>
        <?php endfor; ?>
        <button type="submit" class="botao">💾 Salvar participantes</button>
    </form>
<?php endif;

rodape();
