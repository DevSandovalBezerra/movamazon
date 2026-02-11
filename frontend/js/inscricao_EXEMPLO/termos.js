// JavaScript para Etapa 2: Termos e Condições
class EtapaTermos {
    constructor() {
        this.termosLidos = false;
        this.init();
    }

    init() {
        this.bindEvents();
        this.verificarScrollTermos();
    }

    bindEvents() {
        // Event listener para scroll dos termos
        const termosDiv = document.getElementById('termosScroll');
        if (termosDiv) {
            termosDiv.addEventListener('scroll', () => {
                this.verificarScrollTermos();
            });
        }

        // Event listener para mudança no checkbox
        const checkbox = document.getElementById('aceiteTermos');
        if (checkbox) {
            checkbox.addEventListener('change', () => {
                this.atualizarBotaoProximo();
            });
        }

        // Event listener para botão próximo
        const btnProximo = document.getElementById('btn-prosseguir');
        if (btnProximo) {
            btnProximo.addEventListener('click', () => {
                this.validarESalvar();
            });
        }
    }

    verificarScrollTermos() {
        const termosDiv = document.getElementById('termosScroll');
        const checkbox = document.getElementById('aceiteTermos');

        if (!termosDiv || !checkbox) return;

        // Verificar se chegou ao final do scroll
        const scrollTop = termosDiv.scrollTop;
        const scrollHeight = termosDiv.scrollHeight;
        const clientHeight = termosDiv.clientHeight;

        // Considerar que chegou ao final se estiver a 50px do final
        if (scrollTop + clientHeight >= scrollHeight - 50) {
            this.termosLidos = true;
            checkbox.disabled = false;
            this.mostrarMensagemSucesso();
        } else {
            this.termosLidos = false;
            checkbox.disabled = true;
            checkbox.checked = false;
            this.ocultarMensagemSucesso();
        }

        this.atualizarBotaoProximo();
    }

    mostrarMensagemSucesso() {
        let mensagem = document.getElementById('mensagem-termos-lidos');
        if (!mensagem) {
            mensagem = document.createElement('div');
            mensagem.id = 'mensagem-termos-lidos';
            mensagem.className = 'alert alert-success mt-3';
            mensagem.innerHTML = '<i class="fas fa-check-circle"></i> Termos lidos! Agora você pode marcar o aceite.';

            const aceiteContainer = document.querySelector('.aceite-container');
            if (aceiteContainer) {
                aceiteContainer.appendChild(mensagem);
            }
        }
        mensagem.style.display = 'block';
    }

    ocultarMensagemSucesso() {
        const mensagem = document.getElementById('mensagem-termos-lidos');
        if (mensagem) {
            mensagem.style.display = 'none';
        }
    }

    atualizarBotaoProximo() {
        const btnProximo = document.getElementById('btn-prosseguir');
        const checkbox = document.getElementById('aceiteTermos');

        if (btnProximo && checkbox) {
            btnProximo.disabled = !(this.termosLidos && checkbox.checked);
        }
    }

    validarESalvar() {
        const checkbox = document.getElementById('aceiteTermos');

        if (!checkbox || !checkbox.checked) {
            this.mostrarErro('Você deve aceitar os termos e condições para continuar.');
            return false;
        }

        if (!this.termosLidos) {
            this.mostrarErro('Você deve ler todos os termos antes de prosseguir.');
            return false;
        }

        // Salvar dados na sessão
        if (window.sistemaInscricao) {
            window.sistemaInscricao.salvarDadosEtapa({
                termos_aceitos: true,
                data_aceite: new Date().toISOString()
            });
        }

        // Prosseguir para próxima etapa
        if (window.sistemaInscricao) {
            window.sistemaInscricao.prosseguirEtapa();
        }

        return true;
    }

    mostrarErro(mensagem) {
        if (window.sistemaInscricao) {
            window.sistemaInscricao.mostrarErro(mensagem);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: mensagem
            });
        }
    }

    // Métodos utilitários
    getTermosLidos() {
        return this.termosLidos;
    }

    getAceiteConfirmado() {
        const checkbox = document.getElementById('aceiteTermos');
        return checkbox ? checkbox.checked : false;
    }

    // Forçar leitura dos termos (para testes)
    forcarLeitura() {
        const termosDiv = document.getElementById('termosScroll');
        if (termosDiv) {
            termosDiv.scrollTop = termosDiv.scrollHeight;
        }
    }

    // Resetar estado
    resetar() {
        this.termosLidos = false;
        const checkbox = document.getElementById('aceiteTermos');
        if (checkbox) {
            checkbox.checked = false;
            checkbox.disabled = true;
        }
        this.ocultarMensagemSucesso();
        this.atualizarBotaoProximo();
    }
}

// Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    window.etapaTermos = new EtapaTermos();
});

// Exportar para uso em outros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EtapaTermos;
}