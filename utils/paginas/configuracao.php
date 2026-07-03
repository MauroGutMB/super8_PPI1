<?php
$participantes = carregar_participantes();
$torneio       = carregar_torneio();

cabecalho('Formato do Torneio');
mensagens_flash();

if ($participantes === null): ?>
    <p class="msg msg-erro">
        Antes de configurar o torneio é preciso
        <a href="<?= e(url_para('participantes')) ?>">cadastrar os 8 participantes</a>.
    </p>
<?php else: ?>

    <?php if ($torneio !== null): ?>
        <p class="msg msg-erro">
            ⚠️ Já existem rodadas geradas (formato:
            <strong><?= $torneio['formato'] === 'fixas' ? 'duplas fixas' : 'duplas rotativas' ?></strong>).
            Gerar novamente apaga todos os placares lançados.
        </p>
    <?php endif; ?>

    <form action="index.php?acao=gerar_rodadas" method="post" class="formulario">

        <fieldset>
            <legend>Escolha o formato de duplas</legend>
            <label class="opcao-formato">
                <input type="radio" name="formato" value="rotativas" checked>
                <span>
                    <strong>🔁 Duplas rotativas (Rei/Rainha da Quadra)</strong><br>
                    As duplas mudam a cada rodada. O sorteio garante que ninguém
                    joga com o mesmo parceiro duas vezes nas 7 rodadas.
                    Pontuação acumulada individualmente.
                </span>
            </label>
            <label class="opcao-formato">
                <input type="radio" name="formato" value="fixas">
                <span>
                    <strong>👥 Duplas fixas</strong><br>
                    4 duplas definidas no início se enfrentam em todos contra todos
                    (turno, returno e rodada final). Classificação por dupla e individual.
                </span>
            </label>
        </fieldset>

        <fieldset>
            <legend>Formação das 4 duplas (usada apenas no formato de duplas fixas)</legend>
            <label class="opcao-sorteio">
                <input type="checkbox" name="sortear_duplas" value="1" checked>
                Sortear as duplas automaticamente
            </label>
            <p>Para montar as duplas manualmente, desmarque o sorteio e escolha os pares:</p>
            <?php for ($d = 0; $d < 4; $d++): ?>
                <div class="linha-dupla">
                    <strong>Dupla <?= $d + 1 ?>:</strong>
                    <?php for ($j = 0; $j < 2; $j++): ?>
                        <select name="dupla[<?= $d ?>][]">
                            <?php foreach ($participantes as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                    <?= $p['id'] === $d * 2 + $j + 1 ? 'selected' : '' ?>>
                                    <?= e($p['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endfor; ?>
                </div>
            <?php endfor; ?>
        </fieldset>

        <?php if ($torneio !== null): ?>
            <label class="opcao-sorteio">
                <input type="checkbox" name="confirmar_regerar" value="1" required>
                Entendo que gerar novas rodadas apaga os placares já lançados.
            </label>
        <?php endif; ?>

        <button type="submit" class="botao">🎲 Gerar as 7 rodadas</button>
    </form>
<?php endif;

rodape();
