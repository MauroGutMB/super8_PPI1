<?php
/**
 * Funções reutilizáveis de leitura e escrita de arquivos JSON.
 * Toda a persistência do sistema passa por aqui (sem banco de dados).
 * DATA_DIR é definida em config/config.php.
 */

function ler_json(string $arquivo): ?array
{
    $caminho = DATA_DIR . '/' . $arquivo;
    if (!file_exists($caminho)) {
        return null;
    }
    $dados = json_decode(file_get_contents($caminho), true);
    return is_array($dados) ? $dados : null;
}

function gravar_json(string $arquivo, array $dados): bool
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0775, true);
    }
    $json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(DATA_DIR . '/' . $arquivo, $json, LOCK_EX) !== false;
}

function apagar_json(string $arquivo): void
{
    $caminho = DATA_DIR . '/' . $arquivo;
    if (file_exists($caminho)) {
        unlink($caminho);
    }
}

/** Retorna a lista de participantes ou null se ainda não cadastrados. */
function carregar_participantes(): ?array
{
    $dados = ler_json('participantes.json');
    $lista = $dados['participantes'] ?? null;
    if (!is_array($lista) || count($lista) !== 8) {
        return null;
    }
    foreach ($lista as &$p) {
        $p['apelido'] ??= ''; // JSON antigo/editado à mão pode não ter a chave
    }
    unset($p);
    return $lista;
}

/** Rótulo exibido do participante: apelido quando preenchido, senão o nome. */
function nome_exibicao(array $p): string
{
    return ($p['apelido'] ?? '') !== '' ? $p['apelido'] : $p['nome'];
}

/** Mapa id do jogador => rótulo exibido, usado nas rodadas e na classificação. */
function mapa_nomes(array $participantes): array
{
    $nomes = [];
    foreach ($participantes as $p) {
        $nomes[$p['id']] = nome_exibicao($p);
    }
    return $nomes;
}

/** Retorna o torneio (formato + rodadas) ou null se ainda não gerado. */
function carregar_torneio(): ?array
{
    $dados = ler_json('rodadas.json');
    return (isset($dados['rodadas']) && is_array($dados['rodadas'])) ? $dados : null;
}

/**
 * Estado geral do sistema, usado pelo menu e pelas validações de fluxo:
 * sem_participantes → sem_rodadas → em_andamento → finalizado
 */
function estado_torneio(): string
{
    if (carregar_participantes() === null) {
        return 'sem_participantes';
    }
    $torneio = carregar_torneio();
    if ($torneio === null) {
        return 'sem_rodadas';
    }
    foreach ($torneio['rodadas'] as $rodada) {
        foreach ($rodada['partidas'] as $partida) {
            if ($partida['games_a'] === null || $partida['games_b'] === null) {
                return 'em_andamento';
            }
        }
    }
    return 'finalizado';
}

/** Número da primeira rodada com partida pendente (ou null se tudo lançado). */
function rodada_atual(array $torneio): ?int
{
    foreach ($torneio['rodadas'] as $rodada) {
        foreach ($rodada['partidas'] as $partida) {
            if ($partida['games_a'] === null || $partida['games_b'] === null) {
                return $rodada['numero'];
            }
        }
    }
    return null;
}
