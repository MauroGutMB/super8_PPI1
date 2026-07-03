<?php
/**
 * Ponto de entrada único do sistema (front controller).
 *
 *   Páginas: index.php?pagina=inicio|participantes|configuracao|rodadas|classificacao|reiniciar
 *   Ações:   index.php?acao=salvar_participantes|gerar_rodadas|salvar_placar|reiniciar (POST)
 *
 * As ações processam formulários e redirecionam; as páginas apenas exibem.
 */

require_once __DIR__ . '/config/config.php';

$acoes = [
    'salvar_participantes' => 'salvar_participantes.php',
    'gerar_rodadas'        => 'gerar_rodadas.php',
    'salvar_placar'        => 'salvar_placar.php',
    'reiniciar'            => 'reiniciar.php',
];

$paginas = [
    'inicio'        => 'inicio.php',
    'participantes' => 'participantes.php',
    'configuracao'  => 'configuracao.php',
    'rodadas'       => 'rodadas.php',
    'classificacao' => 'classificacao.php',
    'reiniciar'     => 'reiniciar.php',
];

$acao = $_GET['acao'] ?? null;
if ($acao !== null) {
    if (!isset($acoes[$acao])) {
        redirecionar('inicio', erro: 'Ação desconhecida.');
    }
    require __DIR__ . '/utils/acoes/' . $acoes[$acao];
    exit;
}

$pagina = $_GET['pagina'] ?? 'inicio';
if (!isset($paginas[$pagina])) {
    $pagina = 'inicio';
}
require __DIR__ . '/utils/paginas/' . $paginas[$pagina];
