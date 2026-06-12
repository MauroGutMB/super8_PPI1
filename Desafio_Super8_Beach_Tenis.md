# 🏖️ Desafio: Sistema de Classificação Super 8 — Beach Tennis

> **Disciplina:** Programação para Internet I  
> **Curso:** Análise e Desenvolvimento de Sistemas  
> **Tecnologias:** HTML · CSS · JavaScript · PHP · JSON  

---

## 📖 Contexto

O **Super 8** é um dos formatos de torneio mais populares no beach tennis. Nele, **8 participantes** jogam no sistema **"todos contra todos"** ao longo de **7 rodadas**, acumulando pontos a cada partida disputada. Ao final, quem tiver a maior pontuação individual é coroado campeão do dia.

O evento é muito utilizado em aulas e torneios internos de fim de semana por garantir bastante tempo de quadra, diversão e a chance de jogar com diferentes parceiros.

Hoje, professores e organizadores usam plataformas dedicadas para sortear confrontos e calcular pontuações automaticamente. **O seu desafio é construir um sistema web capaz de fazer isso do zero.**

---

## 🎯 Objetivo do Desafio

Desenvolver um **sistema web completo** para organizar, acompanhar e gerar a classificação de um torneio Super 8 de beach tennis, utilizando as tecnologias vistas em aula, **sem uso de banco de dados** — toda a persistência será feita via **arquivos JSON**.

---

## ⚙️ Como o Sistema Deve Funcionar

### 1. Cadastro de Participantes

O organizador (professor ou responsável pelo torneio) inicia o sistema cadastrando **exatamente 8 participantes**. Cada participante deve ter, no mínimo:

- Nome completo
- Apelido/nickname (opcional)

Os dados dos participantes devem ser salvos em um arquivo JSON (`participantes.json`).

---

### 2. Escolha do Formato de Duplas

Antes de gerar as rodadas, o sistema deve perguntar ao organizador qual formato de jogo será utilizado:

#### 🔁 Opção A — Duplas Rotativas (Rei/Rainha da Quadra)
Neste formato, as duplas são **sorteadas a cada rodada**. O objetivo é que, ao longo das 7 rodadas, cada jogador atue ao lado e contra diferentes pessoas. A formação das duplas pode ser aleatória (sorteio) ou seguir um esquema pré-definido que garanta a máxima rotatividade.

- A cada rodada, o sistema sorteia ou escala os pares automaticamente.
- O ponto é acumulado **individualmente**: cada game ou partida vencida soma para o jogador, independente de quem foi seu parceiro naquela rodada.

#### 👥 Opção B — Duplas Fixas
Neste formato, as duplas são **definidas no início e não mudam**. São formadas **4 duplas fixas** que se enfrentam ao longo das rodadas no sistema todos contra todos.

- A pontuação pode ser acumulada **por dupla** ou também distribuída individualmente.
- A tabela de classificação pode exibir tanto o ranking de duplas quanto o individual.

> 💡 **Dica para o aluno:** Pense bem na estrutura do seu JSON para suportar os dois formatos. Como você representaria uma "rodada" de forma flexível o suficiente para funcionar com duplas fixas e rotativas?

---

### 3. Geração das Rodadas

Com os participantes cadastrados e o formato escolhido, o sistema deve **gerar automaticamente as 7 rodadas** do torneio.

Cada rodada é composta por **2 partidas simultâneas** (já que são 8 jogadores divididos em 2 quadras, com 4 jogadores por quadra, em duplas). O sistema deve exibir claramente:

- Número da rodada (ex.: Rodada 1 de 7)
- Dupla A × Dupla B (Quadra 1)
- Dupla C × Dupla D (Quadra 2)

As rodadas geradas devem ser salvas em `rodadas.json`.

---

### 4. Lançamento de Placares

Para cada partida de cada rodada, o organizador deve conseguir **registrar o placar**. O formato de jogo do Super 8 usa sets curtos, normalmente indo até **4 ou 5 games**. Exemplos de placar:

- `4 x 2` (a dupla A venceu por 4 games a 2)
- `4 x 4` (empate — se a regra do torneio permitir)

O sistema deve:

1. Exibir a rodada atual com as partidas pendentes de placar.
2. Permitir o lançamento do resultado de cada partida.
3. Salvar o resultado no JSON após confirmação.
4. Avançar para a próxima rodada somente quando todos os placares da rodada atual estiverem preenchidos.

Os resultados devem ser salvos no próprio `rodadas.json`, atualizando o registro da rodada correspondente.

---

### 5. Cálculo de Pontuação

A pontuação deve ser calculada **automaticamente** com base nos placares lançados. Sugestão de critérios (o aluno pode adotar ou adaptar):

| Critério | Pontos |
|---|---|
| Vitória na partida | +2 pontos |
| Derrota na partida | +0 pontos |
| Cada game vencido | +1 ponto |

> O aluno pode escolher entre pontuar apenas por vitórias ou também acumular pontos por games ganhos — **documente claramente a regra escolhida no seu sistema**.

---

### 6. Tabela de Classificação

O sistema deve exibir, em tempo real (atualizada conforme os placares são inseridos), uma **tabela de classificação** com:

- Posição
- Nome do jogador / dupla
- Partidas jogadas
- Vitórias / Derrotas
- Games vencidos / perdidos
- **Total de pontos**

A tabela deve ser ordenada do maior para o menor pontuador. Em caso de empate, defina e documente seu critério de desempate (ex.: saldo de games).

---

## 🗂️ Estrutura de Arquivos Sugerida

A maior parte da lógica do sistema deve ser implementada em **PHP no servidor**. O JavaScript no navegador deve ser usado apenas para melhorar a experiência do usuário (ex.: validações simples de formulário, envio de dados via `fetch` e atualização de partes da página sem recarregar). Toda geração de rodadas, sorteio de duplas, cálculo de pontuação e leitura/escrita de JSON é responsabilidade do PHP.

```
super8/
├── index.php                      → Página inicial / menu do sistema
│
├── participantes/
│   ├── cadastro.php               → Exibe o formulário de cadastro dos 8 participantes
│   └── salvar_participantes.php   → Recebe o POST, valida e grava participantes.json
│
├── configuracao/
│   ├── configuracao.php           → Exibe opções de formato (fixas ou rotativas)
│   └── gerar_rodadas.php          → Lógica PHP de sorteio/escalonamento e geração
│                                    das 7 rodadas → grava rodadas.json
│
├── rodadas/
│   ├── rodadas.php                → Lê rodadas.json via PHP e exibe a rodada atual
│   └── salvar_placar.php          → Recebe o POST com o placar, atualiza rodadas.json
│                                    e recalcula a pontuação acumulada
│
├── classificacao/
│   └── classificacao.php          → Lê rodadas.json, calcula e ordena o ranking,
│                                    exibe a tabela de classificação completa
│
├── utils/
│   ├── json_helper.php            → Funções reutilizáveis: ler_json(), gravar_json()
│   ├── pontuacao.php              → Funções de cálculo de pontos e saldo de games
│   └── sorteio.php                → Algoritmo de geração de confrontos (Round 
│                                    para rotativas; todos-contra-todos para fixas)
│
├── css/
│   └── style.css                  → Estilos da interface
│
├── js/
│   └── ui.js                      → Apenas interações visuais: feedback de formulário,
│                                    envio assíncrono via fetch(), exibição de alertas
│
└── data/
    ├── participantes.json          → Jogadores cadastrados
    └── rodadas.json                → Rodadas, confrontos e placares lançados
```

### 🔀 Divisão de Responsabilidades

| Camada | Tecnologia | Responsabilidade |
|---|---|---|
| **Dados** | JSON | Persistência de participantes, rodadas e resultados |
| **Back-end** | PHP | Toda a lógica: sorteio, geração de rodadas, cálculo de pontos, leitura e escrita de JSON, validações de regra de negócio |
| **Front-end** | HTML + CSS | Estrutura e apresentação das páginas |
| **Front-end** | JavaScript (mínimo) | Validação de campos no navegador, chamadas `fetch()` para os scripts PHP, atualização visual sem recarregar a página |

> ⚠️ **Atenção:** Não é permitido implementar a geração de rodadas, o sorteio de duplas ou o cálculo de pontuação diretamente em JavaScript. Essas responsabilidades pertencem ao PHP. O `ui.js` deve ser enxuto — se você se pegar escrevendo lógica de torneio em JS, mova para o PHP.

> A estrutura acima é uma **sugestão**. Você pode organizar de forma diferente, desde que justifique suas escolhas e mantenha a lógica de negócio no PHP.

---

## 📋 Requisitos Funcionais

- [ ] Cadastrar exatamente 8 participantes
- [ ] Escolher entre duplas fixas ou rotativas
- [ ] Gerar automaticamente as 7 rodadas com os confrontos
- [ ] Lançar o placar de cada partida rodada a rodada
- [ ] Calcular e exibir a pontuação acumulada de cada jogador/dupla
- [ ] Exibir tabela de classificação atualizada em tempo real
- [ ] Persistir todos os dados em arquivos JSON (sem banco de dados)
- [ ] Permitir reiniciar/zerar o torneio para um novo evento

---

## 📋 Requisitos Técnicos

- Toda a **lógica de negócio** (geração de rodadas, sorteio de duplas, cálculo de pontuação, validações de regras do torneio) deve ser implementada em **PHP**.
- A leitura e escrita dos arquivos JSON é exclusivamente responsabilidade do **PHP** — o JavaScript não acessa os arquivos diretamente.
- O **JavaScript** deve ser usado apenas para interações visuais: validação de campos no navegador, envio de dados via `fetch()` ou `XMLHttpRequest` e atualização dinâmica de partes da tela.
- A interface deve ser construída em **HTML + CSS**, sendo responsiva para funcionar bem em celulares (os organizadores usam o celular na quadra).
- As páginas principais podem ser `.php` diretamente, eliminando a necessidade de chamar scripts separados para exibir dados.

---

## 🚀 Diferenciais (Bônus)

Os itens abaixo **não são obrigatórios**, mas agregam nota e demonstram domínio avançado:

- 🎲 Algoritmo de sorteio inteligente para duplas rotativas que **garanta** que ninguém jogue com o mesmo parceiro duas vezes.
- 📊 Gráfico simples com a evolução de pontuação dos jogadores ao longo das rodadas.
- 🖨️ Botão para **imprimir** ou exportar a classificação final em HTML formatado.
- 📱 Layout pensado para **uso no celular** durante o torneio (mobile-first).
- 🔄 Possibilidade de **editar um placar** já lançado, recalculando a classificação.
- ⏱️ Indicador visual de qual rodada está em andamento e quantas faltam.

---

## 📦 Entrega

| Item | Descrição |
|---|---|
| **Repositório** | Código-fonte completo em repositório Git (GitHub, GitLab etc.) |
| **README** | Instruções de como rodar o projeto localmente |
| **Demonstração** | Vídeo curto (até 3 min) ou print das telas principais funcionando |
| **Documentação** | Explique as regras de pontuação e o critério de desempate adotados |

---
