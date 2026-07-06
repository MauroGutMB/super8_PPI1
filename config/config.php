<?php
define('BASE_DIR', dirname(__DIR__));
define('DATA_DIR', BASE_DIR . '/data');

const TOTAL_RODADAS  = 7;
const PONTOS_VITORIA = 2;
const LIMITE_NOME    = 60;
const LIMITE_APELIDO = 30;

require_once BASE_DIR . '/utils/json_helper.php';
require_once BASE_DIR . '/utils/sorteio.php';
require_once BASE_DIR . '/utils/pontuacao.php';
require_once BASE_DIR . '/utils/layout.php';

function url_para(string $pagina): string
{
    return 'index.php?pagina=' . urlencode($pagina);
}

function redirecionar(string $pagina, ?string $ok = null, ?string $erro = null): never
{
    $url = url_para($pagina);
    if ($ok !== null) {
        $url .= '&ok=' . urlencode($ok);
    }
    if ($erro !== null) {
        $url .= '&erro=' . urlencode($erro);
    }
    header('Location: ' . $url);
    exit;
}
