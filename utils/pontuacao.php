<?php
function partida_completa(array $partida): bool
{
    return $partida['games_a'] !== null && $partida['games_b'] !== null;
}

function rodada_completa(array $rodada): bool
{
    foreach ($rodada['partidas'] as $partida) {
        if (!partida_completa($partida)) {
            return false;
        }
    }
    return true;
}

function pontos_da_partida(int $games_pro, int $games_contra): int
{
    return $games_pro + ($games_pro > $games_contra ? PONTOS_VITORIA : 0);
}

function chave_dupla(array $dupla): string
{
    sort($dupla);
    return implode('-', $dupla);
}

function linha_zerada(int $id, string $nome, string $apelido = ''): array
{
    return [
        'id'           => $id,
        'nome'         => $nome,
        'apelido'      => $apelido,
        'jogos'        => 0,
        'vitorias'     => 0,
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
    } else {
        $linha['derrotas']++;
    }
}

function comparar_linhas(array $a, array $b): int
{
    return [$b['pontos'], $b['saldo'], $b['games_pro'], $b['vitorias'], $a['nome']]
       <=> [$a['pontos'], $a['saldo'], $a['games_pro'], $a['vitorias'], $b['nome']];
}

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

function calcular_classificacao_duplas(array $participantes, array $torneio): array
{
    $nomes = mapa_nomes($participantes);

    $tabela = [];
    foreach ($torneio['duplas_fixas'] as $i => $dupla) {
        $tabela[chave_dupla($dupla)] = linha_zerada(
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
                $outro = $campo === 'games_a' ? 'games_b' : 'games_a';
                acumular_resultado($tabela[chave_dupla($partida[$lado])], $partida[$campo], $partida[$outro]);
            }
        }
    }

    $lista = array_values($tabela);
    usort($lista, 'comparar_linhas');
    return $lista;
}

function evolucao_pontuacao(array $participantes, array $torneio): array
{
    $acumulado = [];
    $evolucao  = [];
    foreach ($participantes as $p) {
        $acumulado[$p['id']] = 0;
        $evolucao[$p['id']]  = [];
    }

    foreach ($torneio['rodadas'] as $rodada) {
        if (!rodada_completa($rodada)) {
            break;
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

    $series = [];
    foreach ($participantes as $p) {
        $series[nome_exibicao($p)] = $evolucao[$p['id']];
    }
    return $series;
}

function evolucao_pontuacao_duplas(array $participantes, array $torneio): array
{
    $nomes     = mapa_nomes($participantes);
    $acumulado = [];
    $evolucao  = [];
    foreach ($torneio['duplas_fixas'] as $dupla) {
        $chave = chave_dupla($dupla);
        $acumulado[$chave] = 0;
        $evolucao[$chave]  = [];
    }

    foreach ($torneio['rodadas'] as $rodada) {
        if (!rodada_completa($rodada)) {
            break;
        }
        foreach ($rodada['partidas'] as $partida) {
            foreach (['dupla_a' => 'games_a', 'dupla_b' => 'games_b'] as $lado => $campo) {
                $outro = $campo === 'games_a' ? 'games_b' : 'games_a';
                $acumulado[chave_dupla($partida[$lado])] += pontos_da_partida($partida[$campo], $partida[$outro]);
            }
        }
        foreach ($acumulado as $chave => $pontos) {
            $evolucao[$chave][] = $pontos;
        }
    }

    $series = [];
    foreach ($torneio['duplas_fixas'] as $dupla) {
        $series[$nomes[$dupla[0]] . ' / ' . $nomes[$dupla[1]]] = $evolucao[chave_dupla($dupla)];
    }
    return $series;
}
