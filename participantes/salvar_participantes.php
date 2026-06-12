<?php
/**
 * Recebe o POST do cadastro, valida e grava data/participantes.json.
 */

require_once __DIR__ . '/../utils/json_helper.php';

function voltar(string $erro): never
{
    header('Location: cadastro.php?erro=' . urlencode($erro));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    voltar('Envie o formulário de cadastro.');
}

if (carregar_torneio() !== null) {
    voltar('As rodadas já foram geradas: reinicie o torneio para trocar os participantes.');
}

$nomes    = $_POST['nome'] ?? [];
$apelidos = $_POST['apelido'] ?? [];

if (!is_array($nomes) || count($nomes) !== 8) {
    voltar('São necessários exatamente 8 participantes.');
}

$participantes = [];
foreach ($nomes as $i => $nome) {
    $nome    = trim((string) $nome);
    $apelido = trim((string) ($apelidos[$i] ?? ''));
    if ($nome === '') {
        voltar('O nome do jogador ' . ($i + 1) . ' está vazio.');
    }
    $participantes[] = [
        'id'      => $i + 1,
        'nome'    => $nome,
        'apelido' => $apelido,
    ];
}

$nomesNormalizados = array_map(fn($p) => mb_strtolower($p['nome']), $participantes);
if (count(array_unique($nomesNormalizados)) !== 8) {
    voltar('Há nomes repetidos: cada participante deve ter um nome diferente.');
}

if (!gravar_json('participantes.json', ['participantes' => $participantes])) {
    voltar('Falha ao gravar o arquivo participantes.json (verifique permissões da pasta data/).');
}

header('Location: ../configuracao/configuracao.php?ok='
    . urlencode('Participantes salvos! Agora escolha o formato do torneio.'));
exit;
