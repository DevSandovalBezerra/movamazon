// JavaScript para Etapa 4: Ficha de Inscrição
class EtapaFicha {
    constructor() {
        this.produtosExtras = [];
        this.cupomAplicado = null;
        this.valorDesconto = 0;
        this.tamanhoCamiseta = null;
        this.respostasQuestionario = {};
        this.init();
    }

    init() {
        this.carregarDadosSessao();
        this.bindEvents();
        this.atualizarResumoCompra();
        this.validarFormulario();
    }

    carregarDadosSessao() {
        // Carregar dados da sessão se existirem
        if (window.sistemaInscricao && window.sistemaInscricao.dadosInscricao) {
            this.produtosExtras = window.sistemaInscricao.dadosInscricao.produtos_extras || [];
            this.cupomAplicado = window.sistemaInscricao.dadosInscricao.cupom_aplicado || null;
            this.valorDesconto = window.sistemaInscricao.dadosInscricao.valor_desconto || 0;
            this.tamanhoCamiseta = window.sistemaInscricao.dadosInscricao.tamanho_camiseta || null;
            this.respostasQuestionario = window.sistemaInscricao.dadosInscricao.respostas_questionario || {};
        }

        this.atualizarInterface();
    }

    bindEvents() {
        // Event listener para seleção de tamanho
        document.querySelectorAll('input[name="tamanho_camiseta"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.tamanhoCamiseta = e.target.value;
                this.validarFormulario();
            });
        });

        // Event listener para questionário
        document.querySelectorAll('#questionarioForm input, #questionarioForm textarea, #questionarioForm select').forEach(input => {
            input.addEventListener('input', () => {
                this.capturarRespostasQuestionario();
                this.validarFormulario();
            });
        });

        // Event listener para botão próximo
        const btnProximo = document.getElementById('btn-prosseguir');
        if (btnProximo) {
            btnProximo.addEventListener('click', () => {
                this.validarESalvar();
            });
        }
    }

    capturarRespostasQuestionario() {
        const form = document.getElementById('questionarioForm');
        if (form) {
            const formData = new FormData(form);
            this.respostasQuestionario = {};

            for (let [key, value] of formData.entries()) {
                if (key.startsWith('questionario[')) {
                    const questionarioId = key.match(/\[(\d+)\]/)[1];
                    this.respostasQuestionario[questionarioId] = value;
                }
            }
        }
    }

    aplicarCupom() {
        const codigo = document.getElementById('cupomCodigo').value.trim();

        if (!codigo) {
            this.mostrarErroCupom('Digite o código do cupom');
            return;
        }

        this.mostrarLoading('Validando cupom...');

        fetch('/api/inscricao/validar_cupom.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    codigo: codigo,
                    evento_id: this.getEventoId(),
                    valor_total: this.getValorTotalModalidades()
                })
            })
            .then(response => response.json())
            .then(data => {
                this.ocultarLoading();
                if (data.success) {
                    this.cupomAplicado = data.cupom;
                    this.valorDesconto = data.valor_desconto;
                    this.mostrarSucessoCupom(`Cupom aplicado! Desconto: R$ ${data.valor_desconto.toFixed(2)}`);
                    this.atualizarResumoCompra();
                } else {
                    this.mostrarErroCupom(data.error || 'Cupom inválido');
                }
            })
            .catch(error => {
                this.ocultarLoading();
                this.mostrarErroCupom('Erro na comunicação com o servidor');
                console.error('Erro:', error);
            });
    }

    adicionarProdutoExtra(produtoId, nome, valor) {
        // Verificar se já foi adicionado
        const index = this.produtosExtras.findIndex(p => p.id === produtoId);

        if (index === -1) {
            // Adicionar produto
            this.produtosExtras.push({
                id: produtoId,
                nome: nome,
                valor: valor,
                quantidade: 1
            });

            this.mostrarSucesso(`Produto "${nome}" adicionado!`);
        } else {
            // Aumentar quantidade
            this.produtosExtras[index].quantidade++;
            this.mostrarSucesso(`Quantidade de "${nome}" aumentada!`);
        }

        this.atualizarResumoCompra();
        this.validarFormulario();
    }

    removerProdutoExtra(produtoId) {
        const index = this.produtosExtras.findIndex(p => p.id === produtoId);

        if (index !== -1) {
            const produto = this.produtosExtras[index];

            if (produto.quantidade > 1) {
                produto.quantidade--;
            } else {
                this.produtosExtras.splice(index, 1);
            }

            this.atualizarResumoCompra();
            this.validarFormulario();
        }
    }

    atualizarResumoCompra() {
        const container = document.getElementById('resumoCompra');
        if (!container) return;

        const valorModalidades = this.getValorTotalModalidades();
        const valorProdutosExtras = this.getValorTotalProdutosExtras();
        const subtotal = valorModalidades + valorProdutosExtras;
        const desconto = this.valorDesconto;
        const total = subtotal - desconto;

        let html = `
            <div class="resumo-item">
                <span>Modalidades</span>
                <span>R$ ${valorModalidades.toFixed(2)}</span>
            </div>
        `;

        if (this.produtosExtras.length > 0) {
            html += `
                <div class="resumo-item">
                    <span>Produtos Extras</span>
                    <span>R$ ${valorProdutosExtras.toFixed(2)}</span>
                </div>
            `;
        }

        if (desconto > 0) {
            html += `
                <div class="resumo-item desconto">
                    <span>Desconto (${this.cupomAplicado?.codigo || 'Cupom'})</span>
                    <span>- R$ ${desconto.toFixed(2)}</span>
                </div>
            `;
        }

        html += `
            <hr>
            <div class="resumo-item total">
                <strong>Total</strong>
                <strong>R$ ${total.toFixed(2)}</strong>
            </div>
        `;

        container.innerHTML = html;
    }

    getValorTotalModalidades() {
        if (window.sistemaInscricao && window.sistemaInscricao.dadosInscricao) {
            return window.sistemaInscricao.dadosInscricao.valor_total_modalidades || 0;
        }
        return 0;
    }

    getValorTotalProdutosExtras() {
        return this.produtosExtras.reduce((total, produto) => {
            return total + (produto.valor * produto.quantidade);
        }, 0);
    }

    getEventoId() {
        // Extrair ID do evento da URL
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('evento_id');
    }

    validarFormulario() {
        let valido = true;
        const btnProximo = document.getElementById('btn-prosseguir');

        // Validar tamanho de camiseta
        if (!this.tamanhoCamiseta) {
            valido = false;
        }

        // Validar questionário obrigatório
        const questionariosObrigatorios = document.querySelectorAll('#questionarioForm [required]');
        questionariosObrigatorios.forEach(input => {
            if (!input.value.trim()) {
                valido = false;
            }
        });

        if (btnProximo) {
            btnProximo.disabled = !valido;
        }

        return valido;
    }

    validarESalvar() {
        if (!this.validarFormulario()) {
            this.mostrarErro('Preencha todos os campos obrigatórios');
            return false;
        }

        // Capturar respostas do questionário
        this.capturarRespostasQuestionario();

        // Salvar dados na sessão
        if (window.sistemaInscricao) {
            window.sistemaInscricao.salvarDadosEtapa({
                produtos_extras: this.produtosExtras,
                cupom_aplicado: this.cupomAplicado,
                valor_desconto: this.valorDesconto,
                tamanho_camiseta: this.tamanhoCamiseta,
                respostas_questionario: this.respostasQuestionario,
                valor_total_produtos_extras: this.getValorTotalProdutosExtras(),
                valor_total_geral: this.getValorTotalModalidades() + this.getValorTotalProdutosExtras() - this.valorDesconto
            });
        }

        // Prosseguir para próxima etapa
        if (window.sistemaInscricao) {
            window.sistemaInscricao.prosseguirEtapa();
        }

        return true;
    }

    atualizarInterface() {
        // Marcar tamanho selecionado
        if (this.tamanhoCamiseta) {
            const radio = document.querySelector(`input[name="tamanho_camiseta"][value="${this.tamanhoCamiseta}"]`);
            if (radio) {
                radio.checked = true;
            }
        }

        // Preencher respostas do questionário
        Object.keys(this.respostasQuestionario).forEach(questionarioId => {
            const input = document.getElementById(`questionario_${questionarioId}`);
            if (input) {
                input.value = this.respostasQuestionario[questionarioId];
            }
        });

        // Preencher cupom aplicado
        if (this.cupomAplicado) {
            const input = document.getElementById('cupomCodigo');
            if (input) {
                input.value = this.cupomAplicado.codigo;
                input.disabled = true;
            }
        }

        this.validarFormulario();
    }

    // Funções de feedback
    mostrarErroCupom(mensagem) {
        const container = document.getElementById('cupomResultado');
        if (container) {
            container.innerHTML = `<div class="cupom-erro">${mensagem}</div>`;
        }
    }

    mostrarSucessoCupom(mensagem) {
        const container = document.getElementById('cupomResultado');
        if (container) {
            container.innerHTML = `<div class="cupom-sucesso">${mensagem}</div>`;
        }
    }

    mostrarSucesso(mensagem) {
        if (window.sistemaInscricao) {
            window.sistemaInscricao.mostrarSucesso(mensagem);
        } else {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: mensagem
            });
        }
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

    mostrarLoading(mensagem) {
        if (window.sistemaInscricao) {
            window.sistemaInscricao.mostrarLoading(mensagem);
        }
    }

    ocultarLoading() {
        if (window.sistemaInscricao) {
            window.sistemaInscricao.ocultarLoading();
        }
    }

    // Métodos de acesso
    getProdutosExtras() {
        return this.produtosExtras;
    }

    getCupomAplicado() {
        return this.cupomAplicado;
    }

    getValorDesconto() {
        return this.valorDesconto;
    }

    getTamanhoCamiseta() {
        return this.tamanhoCamiseta;
    }

    getRespostasQuestionario() {
        return this.respostasQuestionario;
    }

    // Métodos utilitários
    limparCupom() {
        this.cupomAplicado = null;
        this.valorDesconto = 0;

        const input = document.getElementById('cupomCodigo');
        if (input) {
            input.value = '';
            input.disabled = false;
        }

        const container = document.getElementById('cupomResultado');
        if (container) {
            container.innerHTML = '';
        }

        this.atualizarResumoCompra();
    }

    limparProdutosExtras() {
        this.produtosExtras = [];
        this.atualizarResumoCompra();
        this.validarFormulario();
    }
}

// Funções globais
function aplicarCupom() {
    if (window.etapaFicha) {
        window.etapaFicha.aplicarCupom();
    }
}

function adicionarProdutoExtra(produtoId, nome, valor) {
    if (window.etapaFicha) {
        window.etapaFicha.adicionarProdutoExtra(produtoId, nome, valor);
    }
}

function removerProdutoExtra(produtoId) {
    if (window.etapaFicha) {
        window.etapaFicha.removerProdutoExtra(produtoId);
    }
}

// Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    window.etapaFicha = new EtapaFicha();
});

// Exportar para uso em outros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EtapaFicha;
}