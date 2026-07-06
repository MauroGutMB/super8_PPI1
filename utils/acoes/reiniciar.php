<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('inicio', erro: 'Use a página de confirmação para reiniciar.');
}

apagar_json('participantes.json');
apagar_json('rodadas.json');

redirecionar('inicio', ok: 'Torneio reiniciado. Cadastre os novos participantes.');
