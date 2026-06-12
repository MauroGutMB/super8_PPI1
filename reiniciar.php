<?php
/**
 * Zera o torneio: apaga participantes.json e rodadas.json.
 * Aceita apenas POST para evitar reset acidental por link.
 */

require_once __DIR__ . '/utils/json_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?erro=' . urlencode('Use o botão de reiniciar no menu.'));
    exit;
}

apagar_json('participantes.json');
apagar_json('rodadas.json');

header('Location: index.php?ok=' . urlencode('Torneio reiniciado. Cadastre os novos participantes.'));
exit;
