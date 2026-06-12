<?php
/**
 * Recebe o formato escolhido, monta as duplas (sorteadas ou manuais),
 * gera as 7 rodadas e grava data/rodadas.json.
 */

require_once __DIR__ . '/../utils/json_helper.php';
require_once __DIR__ . '/../utils/sorteio.php';

function voltar(string $erro): never
{
    header('Location: configuracao.php?erro=' . urlencode($erro));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    voltar('Envie o formulário de configuração.');
}

$participantes = carregar_participantes();
if ($participantes === null) {
    voltar('Cadastre os 8 participantes antes de gerar as rodadas.');
}

$ids     = array_column($participantes, 'id');
$formato = $_POST['formato'] ?? '';

if ($formato === 'rotativas') {
    $dados = [
        'formato' => 'rotativas',
        'rodadas' => gerar_rodadas_rotativas($ids),
    ];
} elseif ($formato === 'fixas') {
    if (!empty($_POST['sortear_duplas'])) {
        shuffle($ids);
        $duplas = array_chunk($ids, 2);
    } else {
        $duplas    = [];
        $recebidas = $_POST['dupla'] ?? [];
        for ($d = 0; $d < 4; $d++) {
            $par = array_map('intval', (array) ($recebidas[$d] ?? []));
            if (count($par) !== 2) {
                voltar('A dupla ' . ($d + 1) . ' está incompleta.');
            }
            $duplas[] = $par;
        }
        $todos = array_merge(...$duplas);
        sort($todos);
        $esperado = $ids;
        sort($esperado);
        if ($todos !== $esperado) {
            voltar('Cada jogador deve aparecer em exatamente uma dupla. Revise a escolha.');
        }
    }
    $dados = [
        'formato'      => 'fixas',
        'duplas_fixas' => $duplas,
        'rodadas'      => gerar_rodadas_fixas($duplas),
    ];
} else {
    voltar('Escolha um formato de duplas válido.');
}

if (!gravar_json('rodadas.json', $dados)) {
    voltar('Falha ao gravar o arquivo rodadas.json (verifique permissões da pasta data/).');
}

header('Location: ../rodadas/rodadas.php?ok='
    . urlencode('Rodadas geradas! Bom torneio. 🎾'));
exit;
