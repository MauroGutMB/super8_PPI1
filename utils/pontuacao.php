<?php
/**
 * Cálculo de pontuação e classificação.
 *
 * Regra adotada (documentada no README):
 *   - Vitória na partida ... +2 pontos
 *   - Empate (ex.: 4x4) .... +1 ponto para cada lado
 *   - Derrota ............... 0 pontos
 *   - Cada game vencido .... +1 ponto
 *
 * Critérios de desempate, nesta ordem:
 *   1. Total de pontos
 *   2. Saldo de games (vencidos - perdidos)
 *   3. Games vencidos
 *   4. Número de vitórias
 *   5. Ordem alfabética do nome
 */

require_once __DIR__ . '/json_helper.php';

const PONTOS_VITORIA = 2;
const PONTOS_EMPATE  = 1;

function partida_completa(array $partida): bool
{
    return $partida['games_a'] !== null && $partida['games_b'] !== null;
}

/** Pontos que cada jogador de uma dupla ganha em uma partida. */
function pontos_da_partida(int $games_pro, int $games_contra): int
{
    $pontos = $games_pro; // 1 ponto por game vencido
    if ($games_pro > $games_contra) {
        $pontos += PONTOS_VITORIA;
    } elseif ($games_pro === $games_contra) {
        $pontos += PONTOS_EMPATE;
    }
    return $pontos;
}

function linha_zerada(int $id, string $nome, string $apelido = ''): array
{
    return [
        'id'           => $id,
        'nome'         => $nome,
        'apelido'      => $apelido,
        'jogos'        => 0,
        'vitorias'     => 0,
        'empates'      => 0,
        'derrotas'     => 0,
        'games_pro'    => 0,
        'games_contra' => 0,
        'saldo'        => 0,
        'pontos'       => 0,
    ];
}

function acumular_resultado(array &$linha, int $pro, int $contra): void
{
    $linha['jogos']++;
    $linha['games_pro']    += $pro;
    $linha['games_contra'] += $contra;
    $linha['saldo']         = $linha['games_pro'] - $linha['games_contra'];
    $linha['pontos']       += pontos_da_partida($pro, $contra);
    if ($pro > $contra) {
        $linha['vitorias']++;
    } elseif ($pro === $contra) {
        $linha['empates']++;
    } else {
        $linha['derrotas']++;
    }
}

function comparar_linhas(array $a, array $b): int
{
    return [$b['pontos'], $b['saldo'], $b['games_pro'], $b['vitorias'], $a['nome']]
       <=> [$a['pontos'], $a['saldo'], $a['games_pro'], $a['vitorias'], $b['nome']];
}

/** Classificação individual: lista ordenada de linhas, uma por jogador. */
function calcular_classificacao(array $participantes, array $torneio): array
{
    $tabela = [];
    foreach ($participantes as $p) {
        $tabela[$p['id']] = linha_zerada($p['id'], $p['nome'], $p['apelido'] ?? '');
    }

    foreach ($torneio['rodadas'] as $rodada) {
        foreach ($rodada['partidas'] as $partida) {
            if (!partida_completa($partida)) {
                continue;
            }
            foreach ($partida['dupla_a'] as $id) {
                acumular_resultado($tabela[$id], $partida['games_a'], $partida['games_b']);
            }
            foreach ($partida['dupla_b'] as $id) {
                acumular_resultado($tabela[$id], $partida['games_b'], $partida['games_a']);
            }
        }
    }

    $lista = array_values($tabela);
    usort($lista, 'comparar_linhas');
    return $lista;
}

/** Classificação por dupla (apenas formato de duplas fixas). */
function calcular_classificacao_duplas(array $participantes, array $torneio): array
{
    $nomes = [];
    foreach ($participantes as $p) {
        $nomes[$p['id']] = ($p['apelido'] ?? '') !== '' ? $p['apelido'] : $p['nome'];
    }

    $tabela = [];
    foreach ($torneio['duplas_fixas'] as $i => $dupla) {
        $jogadores = $dupla;
        sort($jogadores);
        $chave = implode('-', $jogadores);
        $tabela[$chave] = linha_zerada(
            $i + 1,
            $nomes[$dupla[0]] . ' / ' . $nomes[$dupla[1]]
        );
    }

    foreach ($torneio['rodadas'] as $rodada) {
        foreach ($rodada['partidas'] as $partida) {
            if (!partida_completa($partida)) {
                continue;
            }
            foreach (['dupla_a' => 'games_a', 'dupla_b' => 'games_b'] as $lado => $campo) {
                $jogadores = $partida[$lado];
                sort($jogadores);
                $chave  = implode('-', $jogadores);
                $outro  = $campo === 'games_a' ? 'games_b' : 'games_a';
                acumular_resultado($tabela[$chave], $partida[$campo], $partida[$outro]);
            }
        }
    }

    $lista = array_values($tabela);
    usort($lista, 'comparar_linhas');
    return $lista;
}

/**
 * Evolução da pontuação individual ao longo das rodadas concluídas.
 * Retorna [id_jogador => [pontos acumulados após a rodada 1, 2, ...]].
 */
function evolucao_pontuacao(array $participantes, array $torneio): array
{
    $acumulado = [];
    $evolucao  = [];
    foreach ($participantes as $p) {
        $acumulado[$p['id']] = 0;
        $evolucao[$p['id']]  = [];
    }

    foreach ($torneio['rodadas'] as $rodada) {
        $completa = true;
        foreach ($rodada['partidas'] as $partida) {
            if (!partida_completa($partida)) {
                $completa = false;
                break;
            }
        }
        if (!$completa) {
            break; // só conta rodadas inteiramente lançadas, em ordem
        }
        foreach ($rodada['partidas'] as $partida) {
            foreach ($partida['dupla_a'] as $id) {
                $acumulado[$id] += pontos_da_partida($partida['games_a'], $partida['games_b']);
            }
            foreach ($partida['dupla_b'] as $id) {
                $acumulado[$id] += pontos_da_partida($partida['games_b'], $partida['games_a']);
            }
        }
        foreach ($acumulado as $id => $pontos) {
            $evolucao[$id][] = $pontos;
        }
    }
    return $evolucao;
}
