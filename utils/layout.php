<?php
/**
 * Cabeçalho e rodapé compartilhados por todas as páginas.
 * $base é o caminho relativo até a raiz do projeto ('.' ou '..').
 */

function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function cabecalho(string $titulo, string $base = '.'): void
{
    $links = [
        'index.php'                       => 'Início',
        'participantes/cadastro.php'      => 'Participantes',
        'configuracao/configuracao.php'   => 'Configuração',
        'rodadas/rodadas.php'             => 'Rodadas',
        'classificacao/classificacao.php' => 'Classificação',
    ];
    $atual = basename($_SERVER['SCRIPT_NAME']);
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> · Super 8 Beach Tennis</title>
    <link rel="stylesheet" href="<?= e($base) ?>/css/style.css">
</head>
<body>
<header class="topo">
    <h1>🏖️ Super 8 · Beach Tennis</h1>
    <nav>
        <?php foreach ($links as $href => $rotulo): ?>
            <a href="<?= e($base . '/' . $href) ?>"
               class="<?= basename($href) === $atual ? 'ativo' : '' ?>"><?= e($rotulo) ?></a>
        <?php endforeach; ?>
    </nav>
</header>
<main>
    <h2><?= e($titulo) ?></h2>
    <?php
}

function rodape(string $base = '.'): void
{
    ?>
</main>
<footer class="rodape">Sistema Super 8 — Programação para Internet I</footer>
<script src="<?= e($base) ?>/js/ui.js"></script>
</body>
</html>
    <?php
}

/** Caixa de mensagem vinda por query string (?ok=... ou ?erro=...). */
function mensagens_flash(): void
{
    if (!empty($_GET['ok'])) {
        echo '<p class="msg msg-ok">' . e($_GET['ok']) . '</p>';
    }
    if (!empty($_GET['erro'])) {
        echo '<p class="msg msg-erro">' . e($_GET['erro']) . '</p>';
    }
}
