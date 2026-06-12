/**
 * ui.js — apenas interações visuais.
 * Toda a lógica do torneio (sorteio, rodadas, pontuação) fica no PHP;
 * aqui só há validação de formulário, envio via fetch() e atualização da tela.
 */

// Confirmação antes de ações destrutivas (reiniciar torneio, regerar rodadas)
document.querySelectorAll("form[data-confirmar]").forEach((form) => {
    form.addEventListener("submit", (evento) => {
        const mensagem = form.dataset.mensagem || "Tem certeza?";
        if (!confirm(mensagem)) {
            evento.preventDefault();
        }
    });
});

// Configuração: mostra a formação de duplas só quando "fixas" está marcado
const formConfig = document.getElementById("form-config");
if (formConfig) {
    const secaoDuplas = document.getElementById("secao-duplas");
    const escolhaDuplas = document.getElementById("escolha-duplas");
    const sortear = document.getElementById("sortear-duplas");

    const atualizar = () => {
        const fixas = formConfig.formato.value === "fixas";
        secaoDuplas.hidden = !fixas;
        escolhaDuplas.hidden = !fixas || sortear.checked;
    };
    formConfig.querySelectorAll("input[name=formato]").forEach((r) =>
        r.addEventListener("change", atualizar)
    );
    sortear.addEventListener("change", atualizar);
    atualizar();
}

// Cadastro: avisa visualmente se algum nome ficou em branco antes do envio
const formCadastro = document.getElementById("form-cadastro");
if (formCadastro) {
    formCadastro.addEventListener("submit", (evento) => {
        const vazios = [...formCadastro.querySelectorAll("input[name='nome[]']")]
            .filter((campo) => campo.value.trim() === "");
        if (vazios.length > 0) {
            evento.preventDefault();
            vazios.forEach((campo) => (campo.style.borderColor = "#e63946"));
            vazios[0].focus();
            alert("Preencha o nome de todos os 8 participantes.");
        }
    });
}

// Rodadas: botão "Editar" revela o formulário de um placar já lançado
document.querySelectorAll(".botao-editar").forEach((botao) => {
    botao.addEventListener("click", () => {
        const partida = botao.closest(".partida");
        partida.querySelector(".form-placar").classList.remove("oculto");
        botao.disabled = true;
    });
});

// Rodadas: envio do placar via fetch, com feedback inline
document.querySelectorAll(".form-placar").forEach((form) => {
    form.addEventListener("submit", async (evento) => {
        evento.preventDefault();
        const feedback = form.querySelector(".feedback");
        feedback.textContent = "Salvando...";
        feedback.className = "feedback";

        try {
            const resposta = await fetch(form.action, {
                method: "POST",
                body: new FormData(form),
            });
            const dados = await resposta.json();
            feedback.textContent = dados.mensagem;
            feedback.classList.add(dados.ok ? "ok" : "erro");

            // Quando a rodada fecha (ou um placar é editado), recarrega para
            // o PHP renderizar a próxima rodada e o progresso atualizado.
            if (dados.ok) {
                setTimeout(() => location.reload(), 700);
            }
        } catch {
            feedback.textContent = "Erro de conexão com o servidor.";
            feedback.classList.add("erro");
        }
    });
});

// Classificação: atualização em tempo real via fetch no endpoint JSON
const tabelaIndividual = document.getElementById("tabela-individual");
if (tabelaIndividual) {
    const preencher = (tabela, linhas) => {
        const corpo = tabela.querySelector("tbody");
        corpo.innerHTML = "";
        linhas.forEach((linha, posicao) => {
            const tr = document.createElement("tr");
            if (posicao === 0) tr.classList.add("lider");
            const apelido = linha.apelido ? ` <small>(${linha.apelido})</small>` : "";
            tr.innerHTML =
                `<td>${posicao + 1}º</td>` +
                `<td class="celula-nome">${linha.nome}${apelido}</td>` +
                `<td>${linha.jogos}</td><td>${linha.vitorias}</td>` +
                `<td>${linha.empates}</td><td>${linha.derrotas}</td>` +
                `<td>${linha.games_pro}</td><td>${linha.games_contra}</td>` +
                `<td>${linha.saldo > 0 ? "+" : ""}${linha.saldo}</td>` +
                `<td class="celula-pontos">${linha.pontos}</td>`;
            corpo.appendChild(tr);
        });
    };

    setInterval(async () => {
        try {
            const resposta = await fetch("dados_classificacao.php");
            const dados = await resposta.json();
            if (!dados.ok) return;
            preencher(tabelaIndividual, dados.individual);
            const tabelaDuplas = document.getElementById("tabela-duplas");
            if (tabelaDuplas && dados.duplas) preencher(tabelaDuplas, dados.duplas);
        } catch {
            // sem conexão: mantém a tabela renderizada pelo PHP
        }
    }, 10000);
}

// Classificação: impressão / exportação
const botaoImprimir = document.getElementById("botao-imprimir");
if (botaoImprimir) {
    botaoImprimir.addEventListener("click", () => window.print());
}
