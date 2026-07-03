<?php
/**
 * Zera o torneio: apaga participantes.json e rodadas.json.
 * Aceita apenas POST (vindo da página de confirmação) para evitar reset acidental.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('inicio', erro: 'Use a página de confirmação para reiniciar.');
}

apagar_json('participantes.json');
apagar_json('rodadas.json');

redirecionar('inicio', ok: 'Torneio reiniciado. Cadastre os novos participantes.');
