<?php
/**
 * Cabeçalho e rodapé compartilhados por todas as páginas.
 * Como tudo é servido pelo index.php na raiz, os caminhos são sempre relativos a ela.
 */

function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/**
 * @param int|null $refresh Se informado, a página recarrega sozinha a cada N segundos
 *                          (usado na classificação enquanto o torneio está em andamento).
 */
function cabecalho(string $titulo, ?int $refresh = null): void
{
    $links = [
        'inicio'        => 'Início',
        'participantes' => 'Participantes',
        'configuracao'  => 'Configuração',
        'rodadas'       => 'Rodadas',
        'classificacao' => 'Classificação',
    ];
    $atual = $_GET['pagina'] ?? 'inicio';
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($refresh !== null): ?>
        <meta http-equiv="refresh" content="<?= $refresh ?>">
    <?php endif; ?>
    <title><?= e($titulo) ?> · Super 8 Beach Tennis</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
<a href="#conteudo" class="skip-link">Pular para o conteúdo</a>
<header class="topo">
    <div class="topo-inner">
        <h1>🏖️ Super 8 · Beach Tennis</h1>
        <nav aria-label="Navegação principal">
            <?php foreach ($links as $pagina => $rotulo): ?>
                <a href="<?= e(url_para($pagina)) ?>"
                   class="<?= $pagina === $atual ? 'ativo' : '' ?>"
                   <?= $pagina === $atual ? 'aria-current="page"' : '' ?>
                ><?= e($rotulo) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>
<main id="conteudo">
    <h2><?= e($titulo) ?></h2>
    <?php
}

function rodape(): void
{
    ?>
</main>
<footer class="rodape">Sistema Super 8 — Programação para Internet I</footer>
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
