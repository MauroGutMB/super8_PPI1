<?php
function gerar_rodadas_rotativas(array $ids): array
{
    // Whist para 8 jogadores: um fixo e os demais girando em Z7 garantem parceiro inédito em todas as rodadas
    shuffle($ids);
    $fixo = $ids[7];
    $giro = fn(int $x): int => $ids[$x % 7];

    $rodadas = [];
    for ($r = 0; $r < TOTAL_RODADAS; $r++) {
        $rodadas[] = [
            'numero'   => $r + 1,
            'partidas' => [
                [
                    'quadra'  => 1,
                    'dupla_a' => [$fixo, $giro($r)],
                    'dupla_b' => [$giro($r + 1), $giro($r + 3)],
                    'games_a' => null,
                    'games_b' => null,
                ],
                [
                    'quadra'  => 2,
                    'dupla_a' => [$giro($r + 2), $giro($r + 6)],
                    'dupla_b' => [$giro($r + 4), $giro($r + 5)],
                    'games_a' => null,
                    'games_b' => null,
                ],
            ],
        ];
    }
    return $rodadas;
}

function gerar_rodadas_fixas(array $duplas): array
{
    $padroes = [
        [[0, 1], [2, 3]],
        [[0, 2], [1, 3]],
        [[0, 3], [1, 2]],
    ];

    $rodadas = [];
    foreach ($padroes as $r => $confrontos) {
        $partidas = [];
        foreach ($confrontos as $quadra => [$ia, $ib]) {
            $partidas[] = [
                'quadra'  => $quadra + 1,
                'dupla_a' => $duplas[$ia],
                'dupla_b' => $duplas[$ib],
                'games_a' => null,
                'games_b' => null,
            ];
        }
        $rodadas[] = ['numero' => $r + 1, 'partidas' => $partidas];
    }
    return $rodadas;
}
