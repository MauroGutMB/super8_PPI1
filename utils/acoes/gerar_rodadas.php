<?php
/**
 * Recebe o formato escolhido, monta as duplas (sorteadas ou manuais),
 * gera as 7 rodadas e grava data/rodadas.json.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('configuracao', erro: 'Envie o formulário de configuração.');
}

$participantes = carregar_participantes();
if ($participantes === null) {
    redirecionar('configuracao', erro: 'Cadastre os 8 participantes antes de gerar as rodadas.');
}

// Regerar rodadas apaga os placares: exige a confirmação marcada no formulário.
if (carregar_torneio() !== null && empty($_POST['confirmar_regerar'])) {
    redirecionar('configuracao', erro: 'Marque a confirmação para gerar novas rodadas (os placares lançados serão apagados).');
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
                redirecionar('configuracao', erro: 'A dupla ' . ($d + 1) . ' está incompleta.');
            }
            if ($par[0] === $par[1]) {
                redirecionar('configuracao', erro: 'A dupla ' . ($d + 1) . ' tem o mesmo jogador nas duas posições.');
            }
            $duplas[] = $par;
        }

        $nomes  = array_column($participantes, 'nome', 'id');
        $vistos = []; // id do jogador => número da dupla em que já apareceu
        foreach ($duplas as $d => $par) {
            foreach ($par as $id) {
                if (isset($vistos[$id])) {
                    redirecionar('configuracao', erro: sprintf(
                        'O jogador %s aparece em mais de uma dupla (duplas %d e %d). Cada jogador entra em exatamente uma dupla.',
                        $nomes[$id] ?? "nº $id",
                        $vistos[$id],
                        $d + 1
                    ));
                }
                $vistos[$id] = $d + 1;
            }
        }

        // Salvaguarda: só ids de participantes cadastrados
        $todos = array_merge(...$duplas);
        sort($todos);
        $esperado = $ids;
        sort($esperado);
        if ($todos !== $esperado) {
            redirecionar('configuracao', erro: 'Seleção de duplas inválida: use apenas os 8 participantes cadastrados.');
        }
    }
    $dados = [
        'formato'      => 'fixas',
        'duplas_fixas' => $duplas,
        'rodadas'      => gerar_rodadas_fixas($duplas),
    ];
} else {
    redirecionar('configuracao', erro: 'Escolha um formato de duplas válido.');
}

if (!gravar_json('rodadas.json', $dados)) {
    redirecionar('configuracao', erro: 'Falha ao gravar o arquivo rodadas.json (verifique permissões da pasta data/).');
}

redirecionar('rodadas', ok: 'Rodadas geradas! Bom torneio. 🎾');
