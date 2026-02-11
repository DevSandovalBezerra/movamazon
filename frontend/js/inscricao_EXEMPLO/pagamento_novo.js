// Configuração do Mercado Pago (Bricks) - EXATAMENTE como no exemplo funcional
const mp = new MercadoPago('TEST-08778670-bce3-4b7f-9641-be7d9103032e');
const bricksBuilder = mp.bricks();

console.log("=== MERCADO PAGO INITIALIZATION ===");
console.log("MercadoPago instance:", mp);
console.log("BricksBuilder instance:", bricksBuilder);

// ✅ Base dinâmico para APIs
if (!window.API_BASE) {
    (function () {
        var path = window.location.pathname || '';
        var idx = path.indexOf('/frontend/');
        window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
    })();
}

// ✅ Função para construir URLs usando API_BASE
function getApiUrl(endpoint) {
    const url = `${window.API_BASE}/api/${endpoint}`;
    return url;
}

// ✅ Renderizar Payment Brick EXATAMENTE como no exemplo funcional
const renderPaymentBrick = async (bricksBuilder) => {
    const amount = parseFloat(document.getElementById('valor_payment').value);
    const preferenceId = document.getElementById('preference_id').value;
    const usePreferenceId = document.getElementById('use_preference_id').value === 'true';

    console.log("=== PAYMENT BRICK INITIALIZATION ===");
    console.log("Amount:", amount);
    console.log("Preference ID:", preferenceId);
    console.log("Use Preference ID:", usePreferenceId);

    const settings = {
        initialization: usePreferenceId ? {
            /*
             Usando preferenceId - MercadoPago usa configuração da preference
             mas ainda precisa do amount para validação
            */
            amount: amount,
            preferenceId: preferenceId,
        } : {
            /*
             Usando amount - MercadoPago decide métodos baseado no valor
            */
            amount: amount,
        },
        customization: {
            paymentMethods: {
                creditCard: "all",
                debitCard: "all",
                mercadoPago: "all"
            },
        },
        callbacks: {
            onReady: () => {
                /*
                 Callback chamado quando o Brick estiver pronto.
                 Aqui você pode ocultar loadings do seu site, por exemplo.
                */
                console.log("=== PAYMENT BRICK READY ===");
            },
            onSubmit: ({
                selectedPaymentMethod,
                formData
            }) => {
                // callback chamado ao clicar no botão de submissão dos dados
                console.log("=== PAYMENT SUBMISSION ===");
                console.log("Selected Payment Method:", selectedPaymentMethod);
                console.log("Form Data:", formData);

                return new Promise((resolve, reject) => {
                    fetch(getApiUrl('inscricao/process_payment_preference.php'), {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify(formData),
                        })
                        .then((response) => response.json())
                        .then((response) => {
                            // receber o resultado do pagamento
                            console.log('Payment response:', response);

                            if (response && response.success && response.id) {
                                const renderStatusScreenBrick = async (bricksBuilder) => {
                                    const settings = {
                                        initialization: {
                                            paymentId: response.id, // id do pagamento a ser mostrado
                                        },
                                        callbacks: {
                                            onReady: () => {
                                                document.getElementById("paymentBrick_container").style.display = 'none';
                                            },
                                            onError: (error) => {
                                                // callback chamado para todos os casos de erro do Brick
                                                console.error('StatusScreen error:', error);
                                            },
                                        },
                                    };
                                    window.statusScreenBrickController = await bricksBuilder.create(
                                        'statusScreen',
                                        'statusScreenBrick_container',
                                        settings,
                                    );
                                };
                                renderStatusScreenBrick(bricksBuilder);
                            } else {
                                console.error('Payment failed:', response);
                                reject(new Error(response ? .error || 'Payment failed'));
                            }

                            resolve();
                        })
                        .catch((error) => {
                            // lidar com a resposta de erro ao tentar criar o pagamento
                            console.error('Payment error:', error);
                            reject(error);
                        });
                });
            },
            onError: (error) => {
                // callback chamado para todos os casos de erro do Brick
                console.error('Brick error:', error);
            },
        },
    };
    window.paymentBrickController = await bricksBuilder.create(
        "payment",
        "paymentBrick_container",
        settings
    );
};

// ✅ Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', async function () {
    try {
        console.log('✅ DOM carregado, inicializando...');

        // Aguardar um pouco para garantir que tudo está pronto
        setTimeout(async () => {
            try {
                await inicializarPagamento();
            } catch (error) {
                console.error('❌ Erro ao inicializar:', error);
            }
        }, 100);

    } catch (error) {
        console.error('❌ Erro ao inicializar:', error);
    }
});

// ✅ Função principal para inicializar o pagamento
async function inicializarPagamento() {
    try {
        const total = calcularTotal();

        if (total <= 0) {
            throw new Error('Valor total inválido');
        }

        console.log('✅ Valor total calculado:', total);

        // Criar pré-inscrição se necessário
        let inscricaoId = window.dadosInscricao ? .inscricaoId;
        if (!inscricaoId) {
            inscricaoId = await criarPreInscricao(total);
        }

        // Criar preference
        const preferenceId = await criarPreference(inscricaoId, total);

        // Configurar elementos HTML necessários
        configurarElementosHTML(total, preferenceId);

        // Renderizar o Brick
        await renderPaymentBrick(bricksBuilder);

        console.log('✅ Payment Brick renderizado com sucesso!');

    } catch (error) {
        console.error('❌ Erro ao inicializar pagamento:', error);
        mostrarErro('Erro ao inicializar pagamento: ' + error.message);
    }
}

// ✅ Criar pré-inscrição
async function criarPreInscricao(total) {
    const payload = montarPayloadPreInscricao(total);

    const response = await fetch(getApiUrl('inscricao/precreate.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const result = await response.json();
    if (!result ? .success) {
        throw new Error(result ? .message || 'Falha ao preparar inscrição');
    }

    const inscricaoId = result.inscricao_id;
    if (!window.dadosInscricao) window.dadosInscricao = {};
    window.dadosInscricao.inscricaoId = inscricaoId;

    console.log('✅ Pré-inscrição criada:', inscricaoId);
    return inscricaoId;
}

// ✅ Criar preference
async function criarPreference(inscricaoId, total) {
    const payload = montarPayloadCreatePreference(inscricaoId, total);

    const response = await fetch(getApiUrl('inscricao/create_preference.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const result = await response.json();
    if (!result ? .success || !result ? .preference_id) {
        throw new Error(result ? .error || 'Falha ao criar preferência');
    }

    console.log('✅ Preference criada:', result.preference_id);
    return result.preference_id;
}

// ✅ Configurar elementos HTML necessários (como no exemplo funcional)
function configurarElementosHTML(total, preferenceId) {
    // Criar ou atualizar elementos necessários
    let valorElement = document.getElementById('valor_payment');
    if (!valorElement) {
        valorElement = document.createElement('input');
        valorElement.type = 'hidden';
        valorElement.id = 'valor_payment';
        document.body.appendChild(valorElement);
    }
    valorElement.value = total;

    let preferenceElement = document.getElementById('preference_id');
    if (!preferenceElement) {
        preferenceElement = document.createElement('input');
        preferenceElement.type = 'hidden';
        preferenceElement.id = 'preference_id';
        document.body.appendChild(preferenceElement);
    }
    preferenceElement.value = preferenceId;

    let usePreferenceElement = document.getElementById('use_preference_id');
    if (!usePreferenceElement) {
        usePreferenceElement = document.createElement('input');
        usePreferenceElement.type = 'hidden';
        usePreferenceElement.id = 'use_preference_id';
        document.body.appendChild(usePreferenceElement);
    }
    usePreferenceElement.value = 'true'; // Sempre usar preferenceId

    console.log('✅ Elementos HTML configurados:', {
        valor: total,
        preferenceId: preferenceId,
        usePreferenceId: 'true'
    });
}

// ✅ Calcular total
function calcularTotal() {
    const modalidades = window.dadosInscricao ? .modalidades || [];
    const produtosExtras = window.dadosInscricao ? .produtosExtras || [];
    const valorDesconto = window.dadosInscricao ? .valorDesconto || 0;

    let total = 0;

    // Somar modalidades
    modalidades.forEach(modalidade => {
        total += parseFloat(modalidade.preco_total || 0);
    });

    // Somar produtos extras
    produtosExtras.forEach(produto => {
        total += parseFloat(produto.valor || 0);
    });

    // Aplicar desconto
    total -= parseFloat(valorDesconto);

    return Math.max(0, total);
}

// ✅ Montar payload para pré-inscrição
function montarPayloadPreInscricao(total) {
    const modalidade = window.dadosInscricao ? .modalidades ? . [0] || {};
    const produtosExtras = window.dadosInscricao ? .produtosExtras || [];

    return {
        evento_id: window.dadosInscricao ? .eventoId || 1,
        modalidades: window.dadosInscricao ? .modalidades || [],
        produtos_extras: produtosExtras,
        ficha: window.dadosInscricao ? .ficha || {},
        valor_total: total,
        cupom: window.dadosInscricao ? .cupomAplicado || null,
        valor_desconto: window.dadosInscricao ? .valorDesconto || 0
    };
}

// ✅ Montar payload para criar preference
function montarPayloadCreatePreference(inscricaoId, total) {
    const modalidade = window.dadosInscricao ? .modalidades ? . [0] || {};
    const produtosExtras = window.dadosInscricao ? .produtosExtras || [];

    return {
        inscricao_id: inscricaoId,
        modalidade_nome: modalidade.nome || 'Inscrição',
        lote_numero: modalidade.lote_numero || null,
        valor_total: total,
        evento_nome: window.dadosInscricao ? .evento ? .nome || 'Evento',
        kit_nome: modalidade.kit_nome || null,
        produtos_extras: produtosExtras,
        cupom: window.dadosInscricao ? .cupomAplicado || null,
        valor_desconto: window.dadosInscricao ? .valorDesconto || 0,
        seguro: 0
    };
}

// ✅ Mostrar erro
function mostrarErro(mensagem) {
    console.error('❌ Erro:', mensagem);
    Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: mensagem
    });
}

// ✅ Setup de event listeners
function setupEventListeners() {
    const btnPagar = document.getElementById('btn-finalizar-compra');
    if (btnPagar) {
        btnPagar.addEventListener('click', async function (e) {
            e.preventDefault();

            // Mostrar container do formulário
            const container = document.getElementById('formulario-mercadopago');
            if (container) {
                container.classList.remove('hidden');
            }

            // Inicializar pagamento
            await inicializarPagamento();
        });
    }
}

// ✅ Renderizar resumo da compra
function renderizarResumoCompra() {
    const container = document.getElementById('resumo-compra');
    if (!container) return;

    const modalidades = window.dadosInscricao ? .modalidades || [];
    const produtosExtras = window.dadosInscricao ? .produtosExtras || [];

    let html = '';

    // Modalidades
    modalidades.forEach(modalidade => {
        html += `
            <div class="flex justify-between py-2">
                <span>${modalidade.nome}</span>
                <span class="font-semibold">R$ ${parseFloat(modalidade.preco_total || 0).toFixed(2).replace('.', ',')}</span>
            </div>
        `;
    });

    // Produtos extras
    produtosExtras.forEach(produto => {
        html += `
            <div class="flex justify-between py-2">
                <span>+ ${produto.nome}</span>
                <span class="font-semibold">R$ ${parseFloat(produto.valor || 0).toFixed(2).replace('.', ',')}</span>
            </div>
        `;
    });

    container.innerHTML = html;
}

// ✅ Atualizar valor total
function updateTotalAmount() {
    const totalElement = document.getElementById('total-geral');
    if (totalElement) {
        const total = calcularTotal();
        totalElement.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
    }

    renderizarResumoCompra();
}

// ✅ Inicializar array global de produtos extras selecionados
window.produtosExtrasSelecionados = window.dadosInscricao ? .produtosExtras || [];