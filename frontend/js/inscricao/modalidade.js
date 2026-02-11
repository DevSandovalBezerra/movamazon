// JavaScript para Etapa 1: Seleção de Modalidade
class EtapaModalidade {
    constructor() {
        this.modalidadesSelecionadas = [];
        this.init();
    }

    init() {
        this.carregarDadosSessao();
        this.bindEvents();
        this.atualizarInterface();
    }

    carregarDadosSessao() {
        // Carregar modalidades já selecionadas da sessão
        if (window.sistemaInscricao && window.sistemaInscricao.dadosInscricao) {
            this.modalidadesSelecionadas = window.sistemaInscricao.dadosInscricao.modalidades_selecionadas || [];
        }
        this.atualizarSelecoes();
    }

    bindEvents() {
        // Event listeners para seleção de modalidades
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-selecionar')) {
                const modalidadeCard = e.target.closest('.modalidade-card');
                if (modalidadeCard) {
                    const modalidadeId = parseInt(modalidadeCard.dataset.modalidadeId);
                    this.toggleModalidade(modalidadeId, modalidadeCard);
                }
            }
        });

        // Event listener para botão próximo
        const btnProximo = document.getElementById('btn-prosseguir');
        if (btnProximo) {
            btnProximo.addEventListener('click', () => {
                this.validarESalvar();
            });
        }
    }

    toggleModalidade(modalidadeId, cardElement) {
        const index = this.modalidadesSelecionadas.findIndex(m => m.id === modalidadeId);

        if (index === -1) {
            // Adicionar modalidade
            this.adicionarModalidade(modalidadeId, cardElement);
        } else {
            // Remover modalidade
            this.removerModalidade(modalidadeId, cardElement);
        }

        this.atualizarInterface();
    }

    adicionarModalidade(modalidadeId, cardElement) {
        // Buscar dados da modalidade
        const dadosModalidade = this.obterDadosModalidade(cardElement);

        // Verificar se já não foi adicionada
        if (!this.modalidadesSelecionadas.find(m => m.id === modalidadeId)) {
            this.modalidadesSelecionadas.push(dadosModalidade);

            // Atualizar visual do card
            cardElement.classList.add('selecionada');
            const btn = cardElement.querySelector('.btn-selecionar');
            if (btn) {
                btn.textContent = 'Selecionado';
                btn.classList.add('selecionado');
            }
        }
    }

    removerModalidade(modalidadeId, cardElement) {
        const index = this.modalidadesSelecionadas.findIndex(m => m.id === modalidadeId);
        if (index !== -1) {
            this.modalidadesSelecionadas.splice(index, 1);

            // Atualizar visual do card
            cardElement.classList.remove('selecionada');
            const btn = cardElement.querySelector('.btn-selecionar');
            if (btn) {
                btn.textContent = 'Selecionar';
                btn.classList.remove('selecionado');
            }
        }
    }

    obterDadosModalidade(cardElement) {
        const modalidadeId = parseInt(cardElement.dataset.modalidadeId);
        const nome = cardElement.querySelector('.modalidade-nome') ? .textContent || '';
        const categoria = cardElement.querySelector('.categoria-badge') ? .textContent || '';
        const preco = this.extrairPreco(cardElement);
        const kitNome = cardElement.querySelector('.kit-details h5') ? .textContent || '';
        const distancia = cardElement.querySelector('.info-item') ? .textContent || '';

        return {
            id: modalidadeId,
            nome: nome,
            categoria: categoria,
            preco: preco,
            kit_nome: kitNome,
            distancia: distancia,
            timestamp: new Date().toISOString()
        };
    }

    extrairPreco(cardElement) {
        const precoElement = cardElement.querySelector('.preco');
        if (precoElement) {
            const precoTexto = precoElement.textContent;
            const precoNumerico = precoTexto.replace(/[^\d,]/g, '').replace(',', '.');
            return parseFloat(precoNumerico) || 0;
        }
        return 0;
    }

    atualizarSelecoes() {
        // Marcar cards já selecionados
        this.modalidadesSelecionadas.forEach(modalidade => {
            const card = document.querySelector(`[data-modalidade-id="${modalidade.id}"]`);
            if (card) {
                card.classList.add('selecionada');
                const btn = card.querySelector('.btn-selecionar');
                if (btn) {
                    btn.textContent = 'Selecionado';
                    btn.classList.add('selecionado');
                }
            }
        });
    }

    atualizarInterface() {
        // Atualizar contador de modalidades selecionadas
        this.atualizarContador();

        // Atualizar botão próximo
        this.atualizarBotaoProximo();

        // Atualizar resumo se existir
        this.atualizarResumo();
    }

    atualizarContador() {
        const contador = document.getElementById('contador-modalidades');
        if (contador) {
            contador.textContent = this.modalidadesSelecionadas.length;
        }
    }

    atualizarBotaoProximo() {
        const btnProximo = document.getElementById('btn-prosseguir');
        if (btnProximo) {
            btnProximo.disabled = this.modalidadesSelecionadas.length === 0;
        }
    }

    atualizarResumo() {
        const resumoContainer = document.getElementById('resumo-selecao');
        if (resumoContainer) {
            if (this.modalidadesSelecionadas.length > 0) {
                resumoContainer.innerHTML = this.gerarHTMLResumo();
                resumoContainer.style.display = 'block';
            } else {
                resumoContainer.style.display = 'none';
            }
        }
    }

    gerarHTMLResumo() {
        let html = '<h5>Modalidades Selecionadas:</h5><ul class="list-unstyled">';

        this.modalidadesSelecionadas.forEach(modalidade => {
            html += `
                <li class="d-flex justify-content-between align-items-center mb-2">
                    <span>${modalidade.nome} - ${modalidade.categoria}</span>
                    <span class="badge badge-primary">R$ ${modalidade.preco.toFixed(2)}</span>
                </li>
            `;
        });

        const total = this.modalidadesSelecionadas.reduce((sum, m) => sum + m.preco, 0);
        html += `
            </ul>
            <div class="border-top pt-2">
                <strong>Total: R$ ${total.toFixed(2)}</strong>
            </div>
        `;

        return html;
    }

    validarESalvar() {
        if (this.modalidadesSelecionadas.length === 0) {
            this.mostrarErro('Selecione pelo menos uma modalidade para continuar.');
            return false;
        }

        // Salvar dados na sessão
        if (window.sistemaInscricao) {
            window.sistemaInscricao.salvarDadosEtapa({
                modalidades_selecionadas: this.modalidadesSelecionadas,
                valor_total_modalidades: this.modalidadesSelecionadas.reduce((sum, m) => sum + m.preco, 0)
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
    getModalidadesSelecionadas() {
        return this.modalidadesSelecionadas;
    }

    getValorTotal() {
        return this.modalidadesSelecionadas.reduce((sum, m) => sum + m.preco, 0);
    }

    limparSelecoes() {
        this.modalidadesSelecionadas = [];
        this.atualizarInterface();

        // Limpar visual dos cards
        document.querySelectorAll('.modalidade-card').forEach(card => {
            card.classList.remove('selecionada');
            const btn = card.querySelector('.btn-selecionar');
            if (btn) {
                btn.textContent = 'Selecionar';
                btn.classList.remove('selecionado');
            }
        });
    }
}

// Função global para seleção de modalidade
function selecionarModalidade(modalidadeId) {
    if (window.etapaModalidade) {
        const cardElement = document.querySelector(`[data-modalidade-id="${modalidadeId}"]`);
        if (cardElement) {
            window.etapaModalidade.toggleModalidade(modalidadeId, cardElement);
        }
    }
}

// Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    window.etapaModalidade = new EtapaModalidade();
});

// Exportar para uso em outros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EtapaModalidade;
}