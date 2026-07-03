<?php
/**
 * Configurações centrais do sistema e carregamento dos utilitários.
 * Todo request passa por aqui (via index.php) antes de qualquer página ou ação.
 */

define('BASE_DIR', dirname(__DIR__));
define('DATA_DIR', BASE_DIR . '/data'); // participantes.json e rodadas.json (gerados em uso)

const TOTAL_RODADAS  = 7; // rodadas do Super 8
const PONTOS_VITORIA = 2;
const PONTOS_EMPATE  = 1;

require_once BASE_DIR . '/utils/json_helper.php';
require_once BASE_DIR . '/utils/sorteio.php';
require_once BASE_DIR . '/utils/pontuacao.php';
require_once BASE_DIR . '/utils/layout.php';

/** URL de uma página do sistema (roteada pelo index.php). */
function url_para(string $pagina): string
{
    return 'index.php?pagina=' . urlencode($pagina);
}

/** Redireciona para uma página, com mensagem opcional de sucesso ou de erro. */
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
