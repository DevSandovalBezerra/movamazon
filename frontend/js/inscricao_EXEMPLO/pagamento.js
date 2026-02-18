if (window.getApiBase) { window.getApiBase(); }
// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ VERSÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O CORRIGIDA - FORÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡AR RECARREGAMENTO - TIMESTAMP: 2024-12-19
console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾ ARQUIVO PAGAMENTO.JS RECARREGADO - VERSÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O CORRIGIDA - TIMESTAMP: 2024-12-19');

// ConfiguraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o do Mercado Pago (Bricks) - EXATAMENTE como no exemplo funcional
const mp = new MercadoPago('TEST-08778670-bce3-4b7f-9641-be7d9103032e');
const bricksBuilder = mp.bricks();

console.log("=== MERCADO PAGO INITIALIZATION ===");
console.log("MercadoPago instance:", mp);
console.log("BricksBuilder instance:", bricksBuilder);

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Base dinÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢mico para APIs
if (!window.API_BASE) {
    (function () {
        var path = window.location.pathname || '';
        var idx = path.indexOf('/frontend/');
        window.API_BASE = idx > 0 ? path.slice(0, idx) + '/api' : '/api';
    })();
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o para construir URLs usando API_BASE
function getApiUrl(endpoint) {
    const url = `${window.API_BASE}/${endpoint}`;
    return url;
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Renderizar Payment Brick EXATAMENTE como no exemplo funcional
const renderPaymentBrick = async (bricksBuilder) => {
    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Verificar se os elementos existem antes de acessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡-los
    const valorElement = document.getElementById('valor_payment');
    const preferenceElement = document.getElementById('preference_id');
    const usePreferenceElement = document.getElementById('use_preference_id');

    if (!valorElement || !preferenceElement || !usePreferenceElement) {
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Elementos DOM nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrados para Payment Brick');
        return;
    }

    const amount = parseFloat(valorElement.value);
    const preferenceId = preferenceElement.value;
    const usePreferenceId = usePreferenceElement.value === 'true';

    console.log("=== PAYMENT BRICK INITIALIZATION ===");
    console.log("Amount:", amount);
    console.log("Preference ID:", preferenceId);
    console.log("Use Preference ID:", usePreferenceId);

    const settings = {
        initialization: usePreferenceId ? {
            /*
             Usando preferenceId - MercadoPago usa configuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o da preference
             mas ainda precisa do amount para validaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
            */
            amount: amount,
            preferenceId: preferenceId,
        } : {
            /*
             Usando amount - MercadoPago decide mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©todos baseado no valor
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
                 Aqui vocÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âª pode ocultar loadings do seu site, por exemplo.
                */
                console.log("=== PAYMENT BRICK READY ===");
            },
            onSubmit: ({
                selectedPaymentMethod,
                formData
            }) => {
                // callback chamado ao clicar no botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o de submissÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o dos dados
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

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    try {
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ DOM carregado, inicializando apenas resumo...');

        // Inicializar APENAS o resumo e event listeners
        // NÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O inicializar o pagamento automaticamente
        renderizarResumoCompra();
        updateTotalAmount();
        setupEventListeners();

        // Validar e habilitar botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o de pagamento
        atualizarBotaoPagamento();

        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ InicializaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡sica concluÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­da - aguardando clique do usuÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio');

    } catch (error) {
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro ao inicializar:', error);
    }
});

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o principal para inicializar o pagamento
async function inicializarPagamento() {
    if (window.paymentBrickController) {
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Payment Brick jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ foi inicializado');
        return;
    }

    try {
        const total = calcularTotal();

        if (total <= 0) {
            throw new Error('Valor total invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido');
        }

        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Valor total calculado:', total);

        // Criar prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o se necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
        let inscricaoId = window.dadosInscricao ? .inscricaoId;
        if (!inscricaoId) {
            inscricaoId = await criarPreInscricao(total);
        }

        // Criar preference
        const preferenceId = await criarPreference(inscricaoId, total);

        // Configurar elementos HTML necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rios
        configurarElementosHTML(total, preferenceId);

        // Renderizar o Brick
        await renderPaymentBrick(bricksBuilder);

        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Payment Brick renderizado com sucesso!');

    } catch (error) {
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro ao inicializar pagamento:', error);
        mostrarErro('Erro ao inicializar pagamento: ' + error.message);
    }
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Criar prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
async function criarPreInscricao(total) {
    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ NOVO: Usar a nova API de salvamento independente
    const payload = montarPayloadPreInscricao(total);

    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¤ Payload enviado para save_inscricao:', payload);

    const response = await fetch(getApiUrl('inscricao/save_inscricao.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Response status:', response.status);

    if (!response.ok) {
        const errorText = await response.text();
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro na resposta:', errorText);
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const result = await response.json();
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Resultado da inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o:', result);

    if (!result ? .success) {
        throw new Error(result ? .message || 'Falha ao salvar inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o');
    }

    const inscricaoId = result.inscricao_id;
    if (!window.dadosInscricao) window.dadosInscricao = {};
    window.dadosInscricao.inscricaoId = inscricaoId;
    window.dadosInscricao.externalReference = result.external_reference;

    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ InscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o salva no banco: ID=' + inscricaoId + ', ExternalRef=' + result.external_reference);
    return inscricaoId;
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Criar preference
async function criarPreference(inscricaoId, total) {
    const payload = montarPayloadCreatePreference(inscricaoId, total);

    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¤ Payload enviado para create_preference:', payload);

    const response = await fetch(getApiUrl('inscricao/create_preference.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Response status create_preference:', response.status);

    if (!response.ok) {
        const errorText = await response.text();
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro na resposta create_preference:', errorText);
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const result = await response.json();
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Resultado da preference:', result);

    if (!result ? .success || !result ? .preference_id) {
        throw new Error(result ? .error || 'Falha ao criar preferÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia');
    }

    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Preference criada:', result.preference_id);
    return result.preference_id;
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Configurar elementos HTML necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rios (como no exemplo funcional)
function configurarElementosHTML(total, preferenceId) {
    // Criar ou atualizar elementos necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rios
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

    // Atualizar display do valor
    const valorDisplay = document.getElementById('valor-display');
    if (valorDisplay) {
        valorDisplay.textContent = total.toFixed(2).replace('.', ',');
    }

    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Elementos HTML configurados:', {
        valor: total,
        preferenceId: preferenceId,
        usePreferenceId: 'true'
    });
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Calcular total
function calcularTotal() {
    const modalidades = window.dadosInscricao ? .modalidades || [];
    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Produtos extras estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o em ficha.produtos_extras, nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o em produtosExtras
    const produtosExtras = window.dadosInscricao ? .ficha ? .produtos_extras || window.dadosInscricao ? .produtosExtras || [];
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

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Montar payload para prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
function montarPayloadPreInscricao(total) {
    const modalidade = window.dadosInscricao ? .modalidades ? . [0] || {};
    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Produtos extras estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o em ficha.produtos_extras
    const produtosExtras = window.dadosInscricao ? .ficha ? .produtos_extras || window.dadosInscricao ? .produtosExtras || [];

    // Calcular valores separados
    const valorModalidades = window.dadosInscricao ? .totalModalidades || 0;
    const valorExtras = window.dadosInscricao ? .totalProdutosExtras || 0;
    const valorDesconto = window.dadosInscricao ? .valorDesconto || 0;

    return {
        evento_id: window.dadosInscricao ? .eventoId || 1,
        modalidade_id: modalidade.id || 1, // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Enviar modalidade_id em vez de modalidades
        tamanho_camiseta: window.dadosInscricao ? .ficha ? .tamanho_camiseta || 'M',
        valor_modalidades: valorModalidades,
        valor_extras: valorExtras,
        valor_desconto: valorDesconto,
        cupom: window.dadosInscricao ? .cupomAplicado || null,
        produtos_extras: produtosExtras,
        seguro: 0
    };
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Montar payload para criar preference
function montarPayloadCreatePreference(inscricaoId, total) {
    const modalidade = window.dadosInscricao ? .modalidades ? . [0] || {};
    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Produtos extras estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o em ficha.produtos_extras
    const produtosExtras = window.dadosInscricao ? .ficha ? .produtos_extras || window.dadosInscricao ? .produtosExtras || [];

    return {
        inscricao_id: inscricaoId,
        modalidade_nome: modalidade.nome || 'InscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o',
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

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Mostrar erro
function mostrarErro(mensagem) {
    console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro:', mensagem);
    Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: mensagem
    });
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Validar se dados estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o prontos para pagamento
function validarDadosParaPagamento() {
    const modalidades = window.dadosInscricao ? .modalidades || [];
    const produtosExtras = window.dadosInscricao ? .ficha ? .produtos_extras || window.dadosInscricao ? .produtosExtras || [];
    const total = calcularTotal();

    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Validando dados para pagamento:', {
        modalidades: modalidades.length,
        produtosExtras: produtosExtras.length,
        total: total,
        dadosInscricao: !!window.dadosInscricao
    });

    // Verificar se tem pelo menos uma modalidade selecionada
    if (modalidades.length === 0) {
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Nenhuma modalidade selecionada');
        return false;
    }

    // Verificar se o total ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© maior que zero
    if (total <= 0) {
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Total invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido:', total);
        return false;
    }

    // Verificar se dados bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡sicos existem
    if (!window.dadosInscricao) {
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Dados de inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrados');
        return false;
    }

    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Dados vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lidos para pagamento');
    return true;
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Habilitar/desabilitar botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o de pagamento
function atualizarBotaoPagamento() {
    const btnPagar = document.getElementById('btn-finalizar-compra');
    if (!btnPagar) return;

    const dadosValidos = validarDadosParaPagamento();

    if (dadosValidos) {
        btnPagar.disabled = false;
        btnPagar.classList.remove('opacity-50', 'cursor-not-allowed');
        btnPagar.classList.add('hover:bg-blue-700');
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o Finalizar Compra habilitado');
    } else {
        btnPagar.disabled = true;
        btnPagar.classList.add('opacity-50', 'cursor-not-allowed');
        btnPagar.classList.remove('hover:bg-blue-700');
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o Finalizar Compra desabilitado');
    }
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Setup de event listeners
function setupEventListeners() {
    const btnPagar = document.getElementById('btn-finalizar-compra');
    if (btnPagar && !btnPagar.hasAttribute('data-listener-added')) {
        btnPagar.setAttribute('data-listener-added', 'true');
        btnPagar.addEventListener('click', async function (e) {
            e.preventDefault();

            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o Finalizar Compra clicado');

            // Verificar se dados ainda sÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lidos
            if (!validarDadosParaPagamento()) {
                mostrarErro('Dados de inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lidos. Verifique suas seleÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes.');
                return;
            }

            // Mostrar janela de pagamento
            const janelaPagamento = document.getElementById('janela-pagamento-mercadopago');
            if (janelaPagamento) {
                janelaPagamento.classList.remove('hidden');
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Janela de pagamento exibida');

                // Scroll para a janela de pagamento
                janelaPagamento.scrollIntoView({
                    behavior: 'smooth'
                });
            }

            // Inicializar pagamento apenas uma vez
            if (!window.paymentInitialized) {
                window.paymentInitialized = true;
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Inicializando pagamento...');
                await inicializarPagamento();
            }
        });
    }

    // BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o voltar ao resumo
    const btnVoltar = document.getElementById('btn-voltar-resumo');
    if (btnVoltar && !btnVoltar.hasAttribute('data-listener-added')) {
        btnVoltar.setAttribute('data-listener-added', 'true');
        btnVoltar.addEventListener('click', function (e) {
            e.preventDefault();

            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o voltar clicado');

            // Ocultar janela de pagamento
            const janelaPagamento = document.getElementById('janela-pagamento-mercadopago');
            if (janelaPagamento) {
                janelaPagamento.classList.add('hidden');
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Janela de pagamento ocultada');
            }

            // Ocultar container PIX se estiver visÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel
            const pixContainer = document.getElementById('pix-container');
            if (pixContainer) {
                pixContainer.classList.add('hidden');
                pixContainer.innerHTML = '';
            }

            // Scroll para o resumo
            const resumoCompra = document.querySelector('.lg\\:col-span-1');
            if (resumoCompra) {
                resumoCompra.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    }

    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Event listener para botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o PIX
    const btnPix = document.getElementById('btn-pix-pagamento');
    if (btnPix && !btnPix.hasAttribute('data-listener-added')) {
        btnPix.setAttribute('data-listener-added', 'true');
        btnPix.addEventListener('click', async function (e) {
            e.preventDefault();

            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o PIX clicado');
            await gerarPixPagamento();
        });
    }
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Gerar PIX para pagamento
async function gerarPixPagamento() {
    try {
        const btnPix = document.getElementById('btn-pix-pagamento');
        const pixContainer = document.getElementById('pix-container');

        if (!btnPix || !pixContainer) {
            throw new Error('Elementos PIX nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrados');
        }

        // Estado de loading
        btnPix.disabled = true;
        btnPix.innerHTML = '<span>ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€šÃ‚Â³</span><span>Gerando PIX...</span>';
        btnPix.style.opacity = '0.7';

        // Mostrar container PIX
        pixContainer.classList.remove('hidden');
        pixContainer.innerHTML = `
            <div style="text-align:center;padding:20px;background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef;">
                <div style="display:inline-block;width:20px;height:20px;border:2px solid #00a650;border-radius:50%;border-top-color:transparent;animation:spin 1s linear infinite;"></div>
                <p style="margin:12px 0 0 0;color:#6c757d;font-size:14px;">Gerando QR Code PIX...</p>
            </div>
            <style>
                @keyframes spin { to { transform: rotate(360deg); } }
            </style>
        `;

        // Scroll para o PIX
        pixContainer.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // Calcular total
        const total = calcularTotal();
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢Ãƒâ€šÃ‚Â° Total para PIX:', total);

        // Verificar se temos inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o ID
        const inscricaoId = window.dadosInscricao ? .inscricaoId;
        if (!inscricaoId) {
            throw new Error('ID da inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado');
        }

        // Criar PIX via API
        const response = await fetch(getApiUrl('inscricao/create_pix.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                inscricao_id: inscricaoId,
                valor_total: total
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Resultado PIX:', result);

        if (!result.success) {
            throw new Error(result.error || 'Falha ao gerar PIX');
        }

        // Renderizar interface PIX
        pixContainer.innerHTML = `
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 24px; margin: 16px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="background: #00a650; color: white; padding: 8px 16px; border-radius: 20px; display: inline-block; font-size: 14px; font-weight: 600; margin-bottom: 12px;">
                        ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢Ãƒâ€šÃ‚Â³ PIX InstantÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢neo
                    </div>
                    <h3 style="margin: 0; color: #2c3e50; font-size: 18px; font-weight: 600;">
                        R$ ${result.transaction_amount.toFixed(2).replace('.', ',')}
                    </h3>
                    <p style="margin: 8px 0 0 0; color: #6c757d; font-size: 14px;">
                        CÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³digo: #${result.external_reference}
                    </p>
                </div>
                
                <div style="text-align: center; margin: 20px 0;">
                    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: inline-block;">
                        <img src="data:image/png;base64, ${result.qr_code_base64}" style="width: 180px; height: 180px; border-radius: 8px;" />
                    </div>
                </div>
                
                <div style="background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 16px; margin: 16px 0;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #495057; margin-bottom: 8px;">
                        CÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³digo PIX (Copie e cole no seu app)
                    </label>
                    <div style="position: relative;">
                        <textarea readonly style="width: 100%; height: 80px; border: 1px solid #ced4da; border-radius: 6px; padding: 12px; font-family: monospace; font-size: 12px; resize: none; background: #f8f9fa;">${result.qr_code}</textarea>
                        <button onclick="navigator.clipboard.writeText(this.previousElementSibling.value); this.textContent='Copiado!'; setTimeout(() => this.textContent='Copiar', 2000);" style="position: absolute; top: 8px; right: 8px; background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">Copiar</button>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="${result.ticket_url}" target="_blank" style="background: #00a650; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: inline-block; transition: background 0.2s;">
                        ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â± Abrir no App
                    </a>
                </div>
                
                <div style="margin-top: 16px; padding: 12px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #2196f3;">
                    <p style="margin: 0; font-size: 13px; color: #1565c0;">
                        <strong>ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢Ãƒâ€šÃ‚Â¡ Dica:</strong> Escaneie o QR Code com seu app bancÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio ou copie o cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³digo PIX para pagar instantaneamente.
                    </p>
                </div>
            </div>
        `;

        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ PIX gerado com sucesso!');

    } catch (error) {
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro ao gerar PIX:', error);

        const pixContainer = document.getElementById('pix-container');
        if (pixContainer) {
            pixContainer.innerHTML = `
                <div style="text-align:center;padding:20px;background:#fff5f5;border:1px solid #fed7d7;border-radius:8px;">
                    <div style="color:#e53e3e;font-size:24px;margin-bottom:8px;">ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â</div>
                    <p style="margin:0;color:#c53030;font-size:14px;">Falha ao gerar PIX: ${error.message}</p>
                </div>
            `;
        }

        mostrarErro('Erro ao gerar PIX: ' + error.message);

    } finally {
        // Restaurar botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
        const btnPix = document.getElementById('btn-pix-pagamento');
        if (btnPix) {
            btnPix.disabled = false;
            btnPix.innerHTML = '<span>ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢Ãƒâ€šÃ‚Â³</span><span>Pagar com PIX</span>';
            btnPix.style.opacity = '1';
        }
    }
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Renderizar resumo da compra
function renderizarResumoCompra() {
    const container = document.getElementById('resumo-compra');
    if (!container) return;

    const modalidades = window.dadosInscricao ? .modalidades || [];
    const produtosExtras = window.dadosInscricao ? .ficha ? .produtos_extras || window.dadosInscricao ? .produtosExtras || [];

    let html = '';

    // Modalidades
    modalidades.forEach(modalidade => {
        html += `
            <div class="flex justify-between py-2">
                <span>${modalidade.nome || 'Modalidade'}</span>
                <span class="font-semibold">R$ ${parseFloat(modalidade.preco_total || 0).toFixed(2).replace('.', ',')}</span>
            </div>
        `;
    });

    // Produtos extras
    produtosExtras.forEach(produto => {
        html += `
            <div class="flex justify-between py-2">
                <span>+ ${produto.nome || 'Produto Extra'}</span>
                <span class="font-semibold">R$ ${parseFloat(produto.valor || 0).toFixed(2).replace('.', ',')}</span>
            </div>
        `;
    });

    // Mostrar desconto se houver
    const valorDesconto = window.dadosInscricao ? .valorDesconto || 0;
    if (valorDesconto > 0) {
        html += `
            <div class="flex justify-between py-2">
                <span class="text-red-600">- Desconto</span>
                <span class="font-semibold text-red-600">R$ ${parseFloat(valorDesconto).toFixed(2).replace('.', ',')}</span>
            </div>
        `;
    }

    container.innerHTML = html;
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Atualizar valor total
function updateTotalAmount() {
    const totalElement = document.getElementById('total-geral');
    if (totalElement) {
        const total = calcularTotal();
        totalElement.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;

        // Atualizar estado do botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o quando total mudar
        atualizarBotaoPagamento();
    }
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Inicializar array global de produtos extras selecionados
// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Produtos extras estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o em ficha.produtos_extras
window.produtosExtrasSelecionados = window.dadosInscricao ? .ficha ? .produtos_extras || window.dadosInscricao ? .produtosExtras || [];

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o global para atualizar botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o de pagamento (pode ser chamada de outros arquivos)
window.atualizarBotaoFinalizarCompra = atualizarBotaoPagamento;
