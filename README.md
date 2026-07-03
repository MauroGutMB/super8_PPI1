# 🏖️ Sistema de Classificação Super 8 — Beach Tennis

Sistema web para organizar um torneio **Super 8** de beach tennis: 8 participantes,
7 rodadas no formato todos contra todos, lançamento de placares e classificação em
tempo real. Desenvolvido para a disciplina **Programação para Internet I**.

**Tecnologias:** PHP · HTML · CSS · JSON.

## ▶️ Como rodar localmente

Requisito: **PHP 8+** instalado.

```bash
git clone <url-do-repositorio>
cd super8
php -S localhost:8000
```

Abra **http://localhost:8000** no navegador. A pasta `data/` precisa ter permissão
de escrita (em Linux: `chmod 775 data`).

## 🕹️ Fluxo de uso

1. **Participantes** — cadastre exatamente 8 jogadores (nome obrigatório, apelido opcional).
2. **Configuração** — escolha o formato e gere as 7 rodadas:
   - 🔁 **Duplas rotativas**: as duplas mudam a cada rodada e a pontuação é individual.
   - 👥 **Duplas fixas**: 4 duplas (sorteadas ou montadas manualmente) jogam todos
     contra todos; a classificação sai por dupla **e** individual.
3. **Rodadas** — lance o placar das 2 partidas de cada rodada. A próxima rodada só
   libera quando a atual estiver completa. Placares já lançados podem ser **editados**
   (a classificação é recalculada automaticamente).
4. **Classificação** — tabela ordenada (recarregada automaticamente enquanto o
   torneio está em andamento), gráfico de evolução de pontos e botão de
   imprimir/exportar (com CSS de impressão dedicado).
5. **Reiniciar** — o menu inicial leva a uma página de confirmação que zera tudo
   para um novo evento.

## 🧮 Regras de pontuação adotadas

| Critério | Pontos |
|---|---|
| Vitória na partida | +2 |
| Empate (ex.: 4 × 4) | +1 para cada lado |
| Derrota | 0 |
| Cada game vencido | +1 |

Placar válido: games de 0 a 7, e pelo menos um dos lados precisa marcar 4 games ou
mais (sets curtos do Super 8 vão até 4 ou 5 games; o empate 4 × 4 é permitido).

**Critérios de desempate**, nesta ordem:

1. Total de pontos
2. Saldo de games (vencidos − perdidos)
3. Games vencidos
4. Número de vitórias
5. Ordem alfabética do nome

## 🎲 Geração das rodadas

- **Duplas rotativas** — usa um esquema de *whist tournament* para 8 jogadores:
  um jogador fica fixo e os demais giram em Z₇; em cada rodada os pares cobrem
  exatamente uma vez cada "diferença" módulo 7. Isso **garante matematicamente que
  ninguém joga com o mesmo parceiro duas vezes** nas 7 rodadas (bônus do desafio).
  A ordem dos jogadores é embaralhada antes, mantendo o caráter de sorteio.
- **Duplas fixas** — todos contra todos entre as 4 duplas (3 confrontos por ciclo),
  repetido até completar as 7 rodadas: **turno** (rodadas 1–3), **returno** com
  mando invertido (4–6) e **rodada final** repetindo o padrão da 1ª (7).

## 🗂️ Estrutura do projeto

```
super8/
├── index.php                      → Ponto de entrada único (front controller):
│                                    roteia ?pagina=... (exibição) e ?acao=... (POST)
├── config/
│   └── config.php                 → Constantes (pastas, rodadas, pontuação) e
│                                    carregamento dos utilitários
├── utils/
│   ├── json_helper.php            → ler_json(), gravar_json(), estado do torneio
│   ├── pontuacao.php              → Cálculo de pontos, desempate e evolução
│   ├── sorteio.php                → Algoritmos de geração de confrontos
│   ├── layout.php                 → Cabeçalho/rodapé compartilhados
│   ├── paginas/                   → Uma página por arquivo:
│   │   ├── inicio.php             →   menu inicial com o status do torneio
│   │   ├── participantes.php      →   formulário dos 8 participantes
│   │   ├── configuracao.php       →   escolha do formato e das duplas
│   │   ├── rodadas.php            →   rodadas, progresso e formulários de placar
│   │   ├── classificacao.php      →   tabelas, regras e gráfico SVG de evolução
│   │   └── reiniciar.php          →   confirmação do reinício do torneio
│   └── acoes/                     → Processamento de formulários (POST + redirect):
│       ├── salvar_participantes.php → valida e grava participantes.json
│       ├── gerar_rodadas.php        → sorteio + geração das 7 rodadas
│       ├── salvar_placar.php        → valida o placar e atualiza rodadas.json
│       └── reiniciar.php            → zera o torneio (apaga os JSONs)
├── style/style.css                → Estilos (mobile-first, com CSS de impressão)
└── data/                          → participantes.json e rodadas.json (gerados em uso)
```

Toda requisição passa pelo `index.php`: páginas (`?pagina=...`) apenas exibem;
ações (`?acao=...`) validam o POST, gravam os JSONs e redirecionam com mensagem
de sucesso ou erro (padrão *POST → redirect → GET*). Confirmações destrutivas
usam página/checkbox de confirmação, e a classificação se mantém atualizada com
`<meta http-equiv="refresh">` enquanto o torneio está em andamento.

## 📄 Estrutura dos JSONs

`data/participantes.json`:

```json
{ "participantes": [ { "id": 1, "nome": "Fulano da Silva", "apelido": "Fula" } ] }
```

`data/rodadas.json` (as duplas são sempre listas de ids de jogador, o que torna a
estrutura igual para os dois formatos):

```json
{
  "formato": "rotativas",
  "rodadas": [
    {
      "numero": 1,
      "partidas": [
        { "quadra": 1, "dupla_a": [1, 5], "dupla_b": [3, 7], "games_a": 4, "games_b": 2 },
        { "quadra": 2, "dupla_a": [2, 8], "dupla_b": [4, 6], "games_a": null, "games_b": null }
      ]
    }
  ]
}
```

No formato de duplas fixas há ainda a chave `"duplas_fixas": [[1,2],[3,4],...]`.
