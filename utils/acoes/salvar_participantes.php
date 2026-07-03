<?php
/**
 * Recebe o POST do cadastro, valida e grava data/participantes.json.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('participantes', erro: 'Envie o formulário de cadastro.');
}

if (carregar_torneio() !== null) {
    redirecionar('participantes', erro: 'As rodadas já foram geradas: reinicie o torneio para trocar os participantes.');
}

$nomes    = $_POST['nome'] ?? [];
$apelidos = $_POST['apelido'] ?? [];

if (!is_array($nomes) || count($nomes) !== 8) {
    redirecionar('participantes', erro: 'São necessários exatamente 8 participantes.');
}

$participantes = [];
foreach ($nomes as $i => $nome) {
    $nome    = trim((string) $nome);
    $apelido = trim((string) ($apelidos[$i] ?? ''));
    if ($nome === '') {
        redirecionar('participantes', erro: 'O nome do jogador ' . ($i + 1) . ' está vazio.');
    }
    if (mb_strlen($nome) > LIMITE_NOME) {
        redirecionar('participantes', erro: 'O nome do jogador ' . ($i + 1) . ' excede ' . LIMITE_NOME . ' caracteres.');
    }
    if (mb_strlen($apelido) > LIMITE_APELIDO) {
        redirecionar('participantes', erro: 'O apelido do jogador ' . ($i + 1) . ' excede ' . LIMITE_APELIDO . ' caracteres.');
    }
    $participantes[] = [
        'id'      => $i + 1,
        'nome'    => $nome,
        'apelido' => $apelido,
    ];
}

$nomesNormalizados = array_map(fn($p) => mb_strtolower($p['nome']), $participantes);
if (count(array_unique($nomesNormalizados)) !== 8) {
    redirecionar('participantes', erro: 'Há nomes repetidos: cada participante deve ter um nome diferente.');
}

// O rótulo exibido nas rodadas e tabelas (apelido ou, sem ele, o nome) deve ser
// único — cobre apelido repetido e também apelido igual ao rótulo de outro jogador.
$rotulos = array_map(fn($p) => mb_strtolower(nome_exibicao($p)), $participantes);
if (count(array_unique($rotulos)) !== 8) {
    redirecionar('participantes', erro: 'Há apelidos repetidos ou iguais ao nome de outro jogador: cada um deve aparecer com um rótulo diferente.');
}

if (!gravar_json('participantes.json', ['participantes' => $participantes])) {
    redirecionar('participantes', erro: 'Falha ao gravar o arquivo participantes.json (verifique permissões da pasta data/).');
}

redirecionar('configuracao', ok: 'Participantes salvos! Agora escolha o formato do torneio.');
