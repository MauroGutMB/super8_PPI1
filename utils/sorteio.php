<?php
/**
 * Algoritmos de geração de confrontos.
 *
 * - Duplas rotativas: esquema de "whist tournament" para 8 jogadores.
 *   Garante matematicamente que ninguém repete parceiro nas 7 rodadas:
 *   um jogador fica fixo e os outros 7 giram em Z7; em cada rodada os
 *   pares cobrem exatamente uma vez cada diferença módulo 7, então cada
 *   par de jogadores joga junto uma única vez. A ordem dos jogadores é
 *   embaralhada antes, mantendo o caráter de sorteio.
 *
 * - Duplas fixas: todos contra todos entre as 4 duplas (3 confrontos
 *   possíveis por dupla). O ciclo de 3 rodadas é repetido até completar
 *   as 7 rodadas do Super 8: turno (1-3), returno com mando invertido
 *   (4-6) e rodada final repetindo o padrão da 1ª (7).
 */

const TOTAL_RODADAS = 7;

function gerar_rodadas_rotativas(array $ids): array
{
    shuffle($ids);                 // sorteio da posição de cada jogador no esquema
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

/**
 * @param array $duplas 4 duplas, cada uma um array com 2 ids de jogador
 */
function gerar_rodadas_fixas(array $duplas): array
{
    $padroes = [
        [[0, 1], [2, 3]],
        [[0, 2], [1, 3]],
        [[0, 3], [1, 2]],
    ];

    $rodadas = [];
    for ($r = 0; $r < TOTAL_RODADAS; $r++) {
        $returno  = intdiv($r, 3) === 1;
        $partidas = [];
        foreach ($padroes[$r % 3] as $quadra => [$ia, $ib]) {
            if ($returno) {
                [$ia, $ib] = [$ib, $ia];
            }
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
