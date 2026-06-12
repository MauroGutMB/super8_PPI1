<?php
/**
 * Endpoint JSON consumido via fetch() pelo ui.js para atualizar a tabela
 * de classificação sem recarregar a página. Todo o cálculo é feito em PHP.
 */

require_once __DIR__ . '/../utils/json_helper.php';
require_once __DIR__ . '/../utils/pontuacao.php';

header('Content-Type: application/json; charset=utf-8');

$participantes = carregar_participantes();
$torneio       = carregar_torneio();

if ($participantes === null || $torneio === null) {
    echo json_encode(['ok' => false, 'mensagem' => 'Torneio não gerado.']);
    exit;
}

$resposta = [
    'ok'         => true,
    'formato'    => $torneio['formato'],
    'individual' => calcular_classificacao($participantes, $torneio),
];
if ($torneio['formato'] === 'fixas') {
    $resposta['duplas'] = calcular_classificacao_duplas($participantes, $torneio);
}

echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
