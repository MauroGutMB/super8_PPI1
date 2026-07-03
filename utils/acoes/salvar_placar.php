<?php
/**
 * Recebe o placar de uma partida via POST, valida as regras,
 * atualiza data/rodadas.json e redireciona de volta às rodadas.
 *
 * Regras de validação:
 *  - games entre 0 e 7, e o maior valor deve ser pelo menos 4
 *    (sets curtos do Super 8 vão até 4 ou 5 games; empate 4x4 é aceito);
 *  - só é possível lançar ou editar placar na rodada atual (a primeira pendente);
 *  - rodadas passadas e torneio finalizado são bloqueados para edição.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('rodadas', erro: 'Use o formulário de placar.');
}

$torneio = carregar_torneio();
if ($torneio === null) {
    redirecionar('rodadas', erro: 'O torneio ainda não foi gerado.');
}

$numero  = filter_input(INPUT_POST, 'rodada', FILTER_VALIDATE_INT);
$indice  = filter_input(INPUT_POST, 'partida', FILTER_VALIDATE_INT);
$games_a = filter_input(INPUT_POST, 'games_a', FILTER_VALIDATE_INT);
$games_b = filter_input(INPUT_POST, 'games_b', FILTER_VALIDATE_INT);

if ($numero === null || $numero === false || !isset($torneio['rodadas'][$numero - 1])) {
    redirecionar('rodadas', erro: 'Rodada inválida.');
}
$rodada = &$torneio['rodadas'][$numero - 1];

if ($indice === null || $indice === false || !isset($rodada['partidas'][$indice])) {
    redirecionar('rodadas', erro: 'Partida inválida.');
}
$partida = &$rodada['partidas'][$indice];

if ($games_a === false || $games_b === false || $games_a === null || $games_b === null
    || $games_a < 0 || $games_a > 7 || $games_b < 0 || $games_b > 7) {
    redirecionar('rodadas', erro: 'Informe games entre 0 e 7 para as duas duplas.');
}
if (max($games_a, $games_b) < 4) {
    redirecionar('rodadas', erro: 'Placar inválido: pelo menos uma dupla precisa ter 4 games ou mais.');
}

$atual  = rodada_atual($torneio);
$edicao = partida_completa($partida);
if ($numero !== $atual) {
    if ($atual === null) {
        redirecionar('rodadas', erro: 'O torneio já foi finalizado; não é possível editar placares.');
    }
    if ($numero < $atual) {
        redirecionar('rodadas', erro: 'Esta rodada já foi concluída; placares de rodadas passadas não podem ser alterados.');
    }
    redirecionar('rodadas', erro: "Lance primeiro os placares da rodada $atual.");
}

$partida['games_a'] = $games_a;
$partida['games_b'] = $games_b;

if (!gravar_json('rodadas.json', $torneio)) {
    redirecionar('rodadas', erro: 'Falha ao gravar rodadas.json (verifique permissões da pasta data/).');
}

$novaAtual = rodada_atual($torneio);
if ($novaAtual === null) {
    redirecionar('rodadas', ok: 'Placar salvo — torneio finalizado! 🏆 Confira a classificação.');
}
if ($novaAtual !== $numero) {
    redirecionar('rodadas', ok: "Placar salvo! Rodada $numero concluída — rodada $novaAtual liberada.");
}
redirecionar('rodadas', ok: $edicao ? 'Placar atualizado e classificação recalculada!' : 'Placar salvo!');
