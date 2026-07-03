<?php
/**
 * Recebe o placar de uma partida (via fetch), valida as regras,
 * atualiza data/rodadas.json e responde em JSON.
 *
 * Regras de validação:
 *  - games entre 0 e 7, e o maior valor deve ser pelo menos 4
 *    (sets curtos do Super 8 vão até 4 ou 5 games; empate 4x4 é aceito);
 *  - só é possível lançar ou editar placar na rodada atual (a primeira pendente);
 *  - rodadas passadas e torneio finalizado são bloqueados para edição.
 */

require_once __DIR__ . '/../utils/json_helper.php';
require_once __DIR__ . '/../utils/pontuacao.php';

header('Content-Type: application/json; charset=utf-8');

function responder(bool $ok, string $mensagem, array $extra = []): never
{
    echo json_encode(['ok' => $ok, 'mensagem' => $mensagem] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Use o formulário de placar.');
}

$torneio = carregar_torneio();
if ($torneio === null) {
    responder(false, 'O torneio ainda não foi gerado.');
}

$numero  = filter_input(INPUT_POST, 'rodada', FILTER_VALIDATE_INT);
$indice  = filter_input(INPUT_POST, 'partida', FILTER_VALIDATE_INT);
$games_a = filter_input(INPUT_POST, 'games_a', FILTER_VALIDATE_INT);
$games_b = filter_input(INPUT_POST, 'games_b', FILTER_VALIDATE_INT);

if ($numero === null || $numero === false || !isset($torneio['rodadas'][$numero - 1])) {
    responder(false, 'Rodada inválida.');
}
$rodada = &$torneio['rodadas'][$numero - 1];

if ($indice === null || $indice === false || !isset($rodada['partidas'][$indice])) {
    responder(false, 'Partida inválida.');
}
$partida = &$rodada['partidas'][$indice];

if ($games_a === false || $games_b === false || $games_a === null || $games_b === null
    || $games_a < 0 || $games_a > 7 || $games_b < 0 || $games_b > 7) {
    responder(false, 'Informe games entre 0 e 7 para as duas duplas.');
}
if (max($games_a, $games_b) < 4) {
    responder(false, 'Placar inválido: pelo menos uma dupla precisa ter 4 games ou mais.');
}

$atual  = rodada_atual($torneio);
$edicao = partida_completa($partida);
if ($numero !== $atual) {
    if ($atual === null) {
        responder(false, 'O torneio já foi finalizado; não é possível editar placares.');
    }
    if ($numero < $atual) {
        responder(false, 'Esta rodada já foi concluída; placares de rodadas passadas não podem ser alterados.');
    }
    responder(false, "Lance primeiro os placares da rodada $atual.");
}

$partida['games_a'] = $games_a;
$partida['games_b'] = $games_b;

if (!gravar_json('rodadas.json', $torneio)) {
    responder(false, 'Falha ao gravar rodadas.json (verifique permissões da pasta data/).');
}

$novaAtual = rodada_atual($torneio);
responder(true, $edicao ? 'Placar atualizado e classificação recalculada!' : 'Placar salvo!', [
    'rodada_completa'    => $novaAtual !== $numero,
    'torneio_finalizado' => $novaAtual === null,
]);
