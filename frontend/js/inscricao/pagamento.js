if (window.getApiBase) { window.getApiBase(); }
// ========== MODO PADRÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Checkout Transparente ativo na pÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡gina de inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o.
// ROLLBACK: defina USE_CHECKOUT_PRO_REDIRECT = true para voltar ao redirect.
const USE_CHECKOUT_PRO_REDIRECT = false;

// VariÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡veis globais para MercadoPago
let mp = null;
let bricksBuilder = null;

// FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o para inicializar MercadoPago usando public key dinÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢mica
async function inicializarMercadoPago() {
    return new Promise(async (resolve, reject) => {
        // Se jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ foi inicializado, retornar imediatamente
        if (mp && bricksBuilder) {
            console.log("ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ MercadoPago jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ inicializado");
            resolve({ mp, bricksBuilder });
            return;
        }

        try {
            // Buscar configuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o do servidor
            const config = await getMercadoPagoConfig();
            
            if (!config.public_key) {
                throw new Error('Public key nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrada na configuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o');
            }

            // Verificar se o SDK jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel
            if (typeof MercadoPago !== 'undefined') {
                mp = new MercadoPago(config.public_key);
                bricksBuilder = mp.bricks();
                
                console.log("=== MERCADO PAGO INITIALIZATION ===");
                console.log("Environment:", config.environment);
                console.log("Is Production:", config.is_production);
                console.log("MercadoPago instance:", mp);
                console.log("BricksBuilder instance:", bricksBuilder);
                
                // Tornar acessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel globalmente
                window.mp = mp;
                window.bricksBuilder = bricksBuilder;
                
                resolve({ mp, bricksBuilder });
                return;
            }

            // Se nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel, aguardar
            console.log("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€šÃ‚Â³ Aguardando SDK do MercadoPago carregar...");
            let attempts = 0;
            const maxAttempts = 50; // 5 segundos mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ximo (50 * 100ms)
            
            const checkInterval = setInterval(() => {
                attempts++;
                
                if (typeof MercadoPago !== 'undefined') {
                    clearInterval(checkInterval);
                    try {
                        mp = new MercadoPago(config.public_key);
                        bricksBuilder = mp.bricks();
                        
                        console.log("=== MERCADO PAGO INITIALIZATION ===");
                        console.log("Environment:", config.environment);
                        console.log("Is Production:", config.is_production);
                        console.log("MercadoPago instance:", mp);
                        console.log("BricksBuilder instance:", bricksBuilder);
                        
                        // Tornar acessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel globalmente
                        window.mp = mp;
                        window.bricksBuilder = bricksBuilder;
                        
                        resolve({ mp, bricksBuilder });
                    } catch (error) {
                        console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ ============================================");
                        console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ ERRO AO INICIALIZAR MERCADOPAGO");
                        console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ ============================================");
                        console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Mensagem:", error.message);
                        console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Stack:", error.stack);
                        console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Tipo:", error.name);
                        console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Config:", config);
                        console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ ============================================");
                        reject(error);
                    }
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    const error = new Error('SDK do MercadoPago nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o carregou apÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³s 5 segundos');
                    console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢", error.message);
                    reject(error);
                }
            }, 100);
        } catch (error) {
            console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ ============================================");
            console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ ERRO AO OBTER CONFIGURAÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O DO MERCADO PAGO");
            console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ ============================================");
            console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Mensagem:", error.message);
            console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Stack:", error.stack);
            console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Tipo:", error.name);
            console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ ============================================");
            reject(error);
        }
    });
}

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
                console.log("[PAGAMENTO] Payment Brick inicializado com sucesso");
                
                // Verificar se os elementos de radio estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o presentes
                setTimeout(() => {
                    const radioButtons = document.querySelectorAll('#paymentBrick_container input[type="radio"]');
                    console.log(`[PAGAMENTO] Radio buttons encontrados: ${radioButtons.length}`);
                    if (radioButtons.length === 0) {
                        console.warn('[PAGAMENTO] Nenhum radio button encontrado no Payment Brick - pode indicar problema de renderizaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o');
                    }
                }, 1000);
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
                    const payload = {
                        ...formData,
                        device_id: (typeof window.MP_DEVICE_SESSION_ID !== 'undefined' ? window.MP_DEVICE_SESSION_ID : '') || '',
                        inscricao_id: window.dadosInscricao?.inscricaoId || ''
                    };
                    fetch(getApiUrl('inscricao/process_payment_preference.php'), {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify(payload),
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
                                                /*
                                                 Callback chamado quando o StatusScreen estiver pronto.
                                                 Aqui vocÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âª pode ocultar o Payment Brick e mostrar mensagens de sucesso.
                                                */
                                                console.log("=== STATUS SCREEN BRICK READY ===");
                                                document.getElementById("paymentBrick_container").style.display = 'none';
                                                
                                                // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Mostrar mensagem de sucesso e aviso sobre email
                                                mostrarMensagemSucesso(response.id);
                                            },
                                            onError: (error) => {
                                                // callback chamado para todos os casos de erro do Brick
                                                console.error('StatusScreen error:', error);
                                                mostrarErro('Erro ao exibir status do pagamento: ' + error.message);
                                            },
                                        },
                                    };
                                    window.statusScreenBrickController = await bricksBuilder.create(
                                        'statusScreen',
                                        'statusScreenBrick_container',
                                        settings,
                                    );
                                };
                                // Usar bricksBuilder do parÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢metro ou global como fallback
                                const builderForStatus = bricksBuilder || window.bricksBuilder;
                                if (builderForStatus) {
                                    renderStatusScreenBrick(builderForStatus);
                                } else {
                                    console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ bricksBuilder nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel para renderStatusScreenBrick');
                                    mostrarErro('Erro ao exibir status do pagamento: SDK nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o inicializado');
                                }
                            } else {
                                console.error('Payment failed:', response);
                                reject(new Error(response?.error || 'Payment failed'));
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

        // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Aguardar window.dadosInscricao estar disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel antes de calcular
        function inicializarQuandoDadosDisponiveis() {
            if (!window.dadosInscricao) {
                console.log('[PAGAMENTO] Aguardando window.dadosInscricao...');
                setTimeout(inicializarQuandoDadosDisponiveis, 100);
                return;
            }

            console.log('[PAGAMENTO] window.dadosInscricao disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel, inicializando resumo...');
            console.log('[PAGAMENTO] Dados:', {
                totalModalidades: window.dadosInscricao.totalModalidades,
                totalProdutosExtras: window.dadosInscricao.totalProdutosExtras,
                valorDesconto: window.dadosInscricao.valorDesconto
            });

            // Inicializar APENAS o resumo e event listeners
            // NÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O inicializar o pagamento automaticamente
            if (typeof renderizarResumoCompra === 'function') {
                renderizarResumoCompra();
            }
            
            if (typeof updateTotalAmount === 'function') {
                updateTotalAmount();
            }
            
            if (typeof setupEventListeners === 'function') {
                setupEventListeners();
            }

            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ InicializaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡sica concluÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­da - aguardando clique do usuÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio');
        }

        // Iniciar verificaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
        inicializarQuandoDadosDisponiveis();

    } catch (error) {
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro ao inicializar:', error);
    }
});

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o principal para inicializar o pagamento (INATIVA quando USE_CHECKOUT_PRO_REDIRECT = true; ver comentÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio ROLLBACK no topo)
async function inicializarPagamento() {
    if (typeof USE_CHECKOUT_PRO_REDIRECT !== 'undefined' && USE_CHECKOUT_PRO_REDIRECT) {
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€šÃ‚Â­ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Checkout Pro redirect ativo; inicializarPagamento nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o executa Brick.');
        return;
    }
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â¡ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ ============================================');
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â¡ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ inicializarPagamento() - INICIANDO...');
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â¡ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ ============================================');
    
    if (window.paymentBrickController) {
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Payment Brick jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ foi inicializado anteriormente');
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â window.paymentBrickController:', window.paymentBrickController);
        return;
    }

    try {
        // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Aguardar SDK do MercadoPago estar disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€šÃ‚Â³ Aguardando inicializaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o do MercadoPago...');
        const { mp: mpInstance, bricksBuilder: bricksBuilderInstance } = await inicializarMercadoPago();
        
        // Garantir que as variÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡veis globais estejam atualizadas
        if (!bricksBuilder) {
            bricksBuilder = bricksBuilderInstance;
        }
        
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ MercadoPago inicializado, continuando...');

        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Calculando total...');
        const total = calcularTotal();
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Total calculado:', total);

        if (total <= 0) {
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Total invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido:', total);
            throw new Error('Valor total invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido');
        }

        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Valor total calculado:', total);

        // Criar prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o se necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Verificando inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o ID...');
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â window.dadosInscricao:', window.dadosInscricao);
        let inscricaoId = window.dadosInscricao?.inscricaoId;
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â InscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o ID atual:', inscricaoId);
        
        if (!inscricaoId) {
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â Criando prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o...');
            inscricaoId = await criarPreInscricao(total);
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ PrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o criada, ID:', inscricaoId);
        } else {
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Usando inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o ID existente:', inscricaoId);
        }

        // Criar preference
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â Criando preference...');
        const preferenceResult = await criarPreference(inscricaoId, total);
        const preferenceId = preferenceResult.preference_id || preferenceResult;
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Preference criada, ID:', preferenceId);

        // Configurar elementos HTML necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rios
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â§ Configurando elementos HTML...');
        configurarElementosHTML(total, preferenceId);
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Elementos HTML configurados');

        // Aguardar um pouco para garantir que o DOM estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ totalmente atualizado
        await new Promise(resolve => setTimeout(resolve, 100));

        // Se o container do Brick nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o existe (pÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡gina usa Checkout Pro redirect), redirecionar para init_point
        const brickContainer = document.getElementById('paymentBrick_container');
        const initPoint = preferenceResult.init_point || preferenceResult.initPoint;
        if (!brickContainer && initPoint) {
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Container Brick nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado (Checkout Pro). Redirecionando para init_point...');
            window.location.href = initPoint;
            return;
        }
        if (!brickContainer) {
            throw new Error('Container de pagamento nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado. Use o botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o Finalizar Compra para ser redirecionado ao Mercado Pago.');
        }

        if (brickContainer) {
            const isVisible = brickContainer.offsetWidth > 0 && brickContainer.offsetHeight > 0;
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â VerificaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o final do container:', {
                width: brickContainer.offsetWidth,
                height: brickContainer.offsetHeight,
                isVisible: isVisible,
                display: window.getComputedStyle(brickContainer).display,
                visibility: window.getComputedStyle(brickContainer).visibility
            });

            if (!isVisible) {
                console.warn('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Container nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ visÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel, forÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ando visibilidade...');
                brickContainer.style.display = 'block';
                brickContainer.style.visibility = 'visible';
                brickContainer.style.minHeight = '400px';
                brickContainer.style.width = '100%';
                
                // Aguardar mais um frame
                await new Promise(resolve => requestAnimationFrame(resolve));
            }
        }

        // Renderizar o Brick usando o bricksBuilder garantido
        const builderToUse = bricksBuilder || bricksBuilderInstance || window.bricksBuilder;
        if (!builderToUse) {
            throw new Error('bricksBuilder nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel');
        }
        
        await renderPaymentBrick(builderToUse);

        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Payment Brick renderizado com sucesso!');

    } catch (error) {
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ ============================================');
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ ERRO AO INICIALIZAR PAGAMENTO');
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ ============================================');
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Mensagem:', error.message);
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Stack:', error.stack);
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Tipo:', error.name);
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Dados de inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o:', window.dadosInscricao);
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ MercadoPago disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel:', typeof MercadoPago !== 'undefined');
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ bricksBuilder disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel:', !!window.bricksBuilder);
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ ============================================');
        
        // Mostrar erro ao usuÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio de forma amigÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡vel
        if (window.mostrarErro) {
            window.mostrarErro('Erro ao inicializar pagamento: ' + error.message);
        } else {
            alert('Erro ao inicializar pagamento: ' + error.message);
        }
        
        // Re-throw para que o caller possa tratar se necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
        throw error;
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

    if (!result?.success) {
        throw new Error(result?.message || 'Falha ao salvar inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o');
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

    if (!result?.success || !result?.preference_id) {
        throw new Error(result?.error || 'Falha ao criar preferÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia');
    }

    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Preference criada:', result.preference_id, 'init_point:', result.init_point ? 'ok' : 'n/a');
    return { preference_id: result.preference_id, init_point: result.init_point || '' };
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

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Calcular subtotal (modalidades + extras - desconto, ANTES da taxa de repasse)
// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Arredondar para 2 casas decimais (evitar erros de ponto flutuante)
function arredondar(valor) {
    return Math.round((valor + Number.EPSILON) * 100) / 100;
}

function calcularSubtotal() {
    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Usar valores jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ calculados no PHP quando disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­veis
    // FÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³rmula: modalidade + produtos_extras - desconto_cupom
    const totalModalidades = parseFloat(window.dadosInscricao?.totalModalidades || 0);
    const totalProdutosExtras = parseFloat(window.dadosInscricao?.totalProdutosExtras || 0);
    const valorDesconto = parseFloat(window.dadosInscricao?.valorDesconto || 0);

    // Se os valores calculados estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­veis, usar diretamente
    if (totalModalidades > 0 || totalProdutosExtras > 0) {
        const subtotal = totalModalidades + totalProdutosExtras - valorDesconto;
        console.log('[PAGAMENTO] calcularSubtotal usando valores calculados:', {
            totalModalidades,
            totalProdutosExtras,
            valorDesconto,
            subtotal: arredondar(Math.max(0, subtotal))
        });
        return arredondar(Math.max(0, subtotal));
    }

    // Fallback: calcular manualmente se valores nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o estiverem disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­veis
    const modalidades = window.dadosInscricao?.modalidades || [];
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];

    console.log('[PAGAMENTO] calcularSubtotal - Fallback: calculando manualmente');
    console.log('[PAGAMENTO] calcularSubtotal - Produtos extras:', produtosExtras);

    let subtotal = 0;
    modalidades.forEach(m => { subtotal += parseFloat(m.preco_total || 0); });
    produtosExtras.forEach(p => { 
        const valor = parseFloat(p.valor || 0);
        console.log('[PAGAMENTO] Adicionando produto extra:', p.nome, 'valor:', valor);
        subtotal += valor;
    });
    subtotal -= parseFloat(valorDesconto);

    return arredondar(Math.max(0, subtotal));
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Calcular total (subtotal + taxa de repasse)
function calcularTotal() {
    let total = calcularSubtotal(); // jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ vem arredondado

    // Aplicar taxa de repasse (percentual sobre subtotal)
    const percentualRepasse = window.dadosInscricao?.percentualRepasse || 0;
    if (percentualRepasse > 0) {
        const taxaRepasse = arredondar(total * (percentualRepasse / 100));
        total = arredondar(total + taxaRepasse);
    }

    console.log('[PAGAMENTO] calcularTotal() resultado:', total, 'tipo:', typeof total, 'percentualRepasse:', percentualRepasse + '%');

    return Math.max(0, total);
}


// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Montar payload para prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
function montarPayloadPreInscricao(total) {
    const modalidade = window.dadosInscricao?.modalidades?.[0] || {};
    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Produtos extras vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªm de dadosInscricao.produtosExtras
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];

    // Calcular valores separados
    const valorModalidades = window.dadosInscricao?.totalModalidades || 0;
    const valorExtras = window.dadosInscricao?.totalProdutosExtras || 0;
    const valorDesconto = window.dadosInscricao?.valorDesconto || 0;

    return {
        evento_id: window.dadosInscricao?.eventoId || 1,
        modalidade_id: modalidade.id || 1, // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Enviar modalidade_id em vez de modalidades
        tamanho_camiseta: window.dadosInscricao?.ficha?.tamanho_camiseta || 'M',
        valor_modalidades: valorModalidades,
        valor_extras: valorExtras,
        valor_desconto: valorDesconto,
        cupom: window.dadosInscricao?.cupomAplicado || null,
        produtos_extras: produtosExtras,
        seguro: 0
    };
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Montar payload para criar preference
function montarPayloadCreatePreference(inscricaoId, total) {
    const modalidade = window.dadosInscricao?.modalidades?.[0] || {};
    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Produtos extras vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªm de dadosInscricao.produtosExtras
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];

    return {
        inscricao_id: inscricaoId,
        modalidade_nome: modalidade.nome || 'InscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o',
        lote_numero: modalidade.lote_numero || null,
        valor_total: total,
        evento_nome: window.dadosInscricao?.evento?.nome || 'Evento',
        kit_nome: modalidade.kit_nome || null,
        produtos_extras: produtosExtras,
        cupom: window.dadosInscricao?.cupomAplicado || null,
        valor_desconto: window.dadosInscricao?.valorDesconto || 0,
        seguro: 0,
        origem: 'inscricao' // back_urls no backend (inscricao ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ /inscricao/sucesso)
    };
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Mostrar erro
function mostrarErro(mensagem) {
    console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro:', mensagem);
    alert('Erro: ' + mensagem);
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Validar se dados estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o prontos para pagamento
function validarDadosParaPagamento() {
    const modalidades = window.dadosInscricao?.modalidades || [];
    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Produtos extras vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªm de dadosInscricao.produtosExtras
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];
    const total = calcularTotal();

    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Validando dados para pagamento:', {
        modalidades: modalidades.length,
        produtosExtras: produtosExtras.length,
        produtosExtrasData: produtosExtras,
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


// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Clique em Finalizar Compra:
// - redirect ativo: cria preferÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia e redireciona
// - transparente ativo: inicializa Brick/PIX/Boleto na pÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡gina
function attachClickListener(btnPagar) {
    if (!btnPagar) {
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ attachClickListener: botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o fornecido');
        return;
    }

    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Â Anexando listener ao botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o de pagamento...');

    btnPagar.addEventListener('click', async function (e) {
        e.preventDefault();

        if (btnPagar.disabled) return;

        try {
            if (!validarDadosParaPagamento()) {
                if (window.mostrarErro) {
                    window.mostrarErro('Verifique os dados antes de finalizar.');
                } else {
                    alert('Verifique os dados antes de finalizar.');
                }
                return;
            }

            const total = calcularTotal();
            if (total <= 0) {
                if (window.mostrarErro) {
                    window.mostrarErro('Valor total invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido.');
                } else {
                    alert('Valor total invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido.');
                }
                return;
            }

            if (typeof USE_CHECKOUT_PRO_REDIRECT !== 'undefined' && USE_CHECKOUT_PRO_REDIRECT) {
                btnPagar.disabled = true;
                btnPagar.classList.add('opacity-50', 'cursor-not-allowed');
                btnPagar.innerHTML = '<span class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-2"></span> Redirecionando ao Mercado Pago...';

                let inscricaoId = window.dadosInscricao?.inscricaoId;
                if (!inscricaoId) {
                    inscricaoId = await criarPreInscricao(total);
                    if (!window.dadosInscricao) window.dadosInscricao = {};
                    window.dadosInscricao.inscricaoId = inscricaoId;
                }

                const result = await criarPreference(inscricaoId, total);
                const initPoint = result.init_point || result.initPoint;

                if (initPoint) {
                    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Redirecionando para Checkout Pro:', initPoint);
                    window.location.href = initPoint;
                    return;
                }

                throw new Error('Resposta do servidor sem link de pagamento.');
            }

            // Transparente na pÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡gina: renderiza Brick e mantÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©m PIX/Boleto habilitados
            if (typeof inicializarPagamento === 'function') {
                const janelaPagamento = document.getElementById('janela-pagamento-mercadopago');
                if (janelaPagamento) {
                    janelaPagamento.classList.remove('hidden');
                }
                await inicializarPagamento();
                return;
            }
            throw new Error('FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o de inicializaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o do checkout nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel.');
        } catch (error) {
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [FINALIZAR_COMPRA]', error.message);
            btnPagar.disabled = false;
            btnPagar.classList.remove('opacity-50', 'cursor-not-allowed');
            btnPagar.innerHTML = '<i class="fas fa-credit-card mr-2"></i> Finalizar Compra';
            if (window.mostrarErro) {
                window.mostrarErro('Erro ao processar pagamento: ' + error.message);
            } else {
                alert('Erro ao processar pagamento: ' + error.message);
            }
        }
    });

    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Listener de pagamento anexado ao botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o');
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Setup de event listeners (SIMPLIFICADO baseado no exemplo funcional)
function setupEventListeners() {
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â§ SETUP EVENT LISTENERS - Iniciando...');
    
    const btnPagar = document.getElementById('btn-finalizar-compra');
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado:', btnPagar);
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ tem listener?', btnPagar?.hasAttribute('data-listener-added'));
    
    // Remover listener anterior se existir (para evitar duplicaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o)
    if (btnPagar && btnPagar.hasAttribute('data-listener-added')) {
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ tem listener, removendo e recriando...');
        const newBtn = btnPagar.cloneNode(true);
        btnPagar.parentNode.replaceChild(newBtn, btnPagar);
        // Buscar novamente apÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³s clonar
        const btnPagarNovo = document.getElementById('btn-finalizar-compra');
        if (btnPagarNovo) {
            btnPagarNovo.setAttribute('data-listener-added', 'true');
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Novo botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o preparado para listener');
            attachClickListener(btnPagarNovo);
        }
    } else if (btnPagar) {
        btnPagar.setAttribute('data-listener-added', 'true');
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Atributo data-listener-added definido');
        attachClickListener(btnPagar);
    } else {
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o btn-finalizar-compra NÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O encontrado!');
    }

    // BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o voltar ao resumo
    const btnVoltar = document.getElementById('btn-voltar-resumo');
    if (btnVoltar && !btnVoltar.hasAttribute('data-listener-added')) {
        btnVoltar.setAttribute('data-listener-added', 'true');
        btnVoltar.addEventListener('click', function (e) {
            e.preventDefault();

            // Ocultar janela de pagamento
            const janelaPagamento = document.getElementById('janela-pagamento-mercadopago');
            if (janelaPagamento) {
                janelaPagamento.classList.add('hidden');
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

    // ROLLBACK: com USE_CHECKOUT_PRO_REDIRECT = false, reative os blocos abaixo para PIX/Boleto na pÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡gina
    if (typeof USE_CHECKOUT_PRO_REDIRECT === 'undefined' || !USE_CHECKOUT_PRO_REDIRECT) {
        const btnPix = document.getElementById('btn-pix-pagamento');
        if (btnPix && !btnPix.hasAttribute('data-listener-added')) {
            btnPix.setAttribute('data-listener-added', 'true');
            btnPix.addEventListener('click', async function (e) {
                e.preventDefault();
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o PIX clicado');
                await gerarPixPagamento();
            });
        }
        const btnBoleto = document.getElementById('btn-boleto-pagamento');
        if (btnBoleto && !btnBoleto.hasAttribute('data-listener-added')) {
            btnBoleto.setAttribute('data-listener-added', 'true');
            btnBoleto.addEventListener('click', async function (e) {
                e.preventDefault();
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o Boleto clicado');
                await gerarBoletoPagamento();
            });
        }
    }
    
    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ setupEventListeners concluÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­do');
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ EXPORTAR TODAS AS FUNÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ES PARA WINDOW (apÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³s todas as definiÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes)
window.setupEventListeners = setupEventListeners;
window.atualizarBotaoPagamento = atualizarBotaoPagamento;
window.inicializarPagamento = inicializarPagamento;
window.calcularTotal = calcularTotal;
window.validarDadosParaPagamento = validarDadosParaPagamento;
window.mostrarErro = mostrarErro;
window.renderizarResumoCompra = renderizarResumoCompra;
window.updateTotalAmount = updateTotalAmount;

console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ ============================================');
console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ TODAS AS FUNÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ES EXPORTADAS PARA WINDOW');
console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ ============================================');
console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­veis:', {
    setupEventListeners: typeof window.setupEventListeners,
    atualizarBotaoPagamento: typeof window.atualizarBotaoPagamento,
    inicializarPagamento: typeof window.inicializarPagamento,
    validarDadosParaPagamento: typeof window.validarDadosParaPagamento,
    calcularTotal: typeof window.calcularTotal,
    mostrarErro: typeof window.mostrarErro,
    renderizarResumoCompra: typeof window.renderizarResumoCompra,
    updateTotalAmount: typeof window.updateTotalAmount
});

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Polling: sync como fallback do webhook; se pago, redireciona; ao timeout, CTA "Ir para minha ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rea"
function iniciarPollingStatusPagamentoPix(inscricaoId) {
    const intervaloMs = 5000;
    const maxTentativas = 72; // 6 minutos
    let tentativas = 0;
    const syncUrl = getApiUrl('participante/sync_payment_status.php?inscricao_id=' + encodeURIComponent(inscricaoId));

    const timer = setInterval(async () => {
        tentativas++;
        try {
            // Sync consulta o MP e atualiza o banco (fallback quando webhook nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o dispara)
            const res = await fetch(syncUrl, { method: 'GET', credentials: 'same-origin' });
            const data = await res.json();
            if (data.success && data.inscricao && (data.inscricao.status === 'confirmada' || data.inscricao.status_pagamento === 'pago')) {
                clearInterval(timer);
                window.location.href = 'sucesso.php?inscricao_id=' + inscricaoId + '&status=success';
                return;
            }
        } catch (e) {
            console.warn('[PIX] Polling sync:', e.message);
        }
        if (tentativas >= maxTentativas) {
            clearInterval(timer);
            mostrarCtaIrParaMinhaArea(inscricaoId);
        }
    }, intervaloMs);
}

// Exibir mensagem e botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o "Ir para minha ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rea" apÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³s timeout do polling PIX
function mostrarCtaIrParaMinhaArea(inscricaoId) {
    const pixContainer = document.getElementById('pix-container');
    const urlMinhasInscricoes = '../../participante/index.php?page=minhas-inscricoes&inscricao_id=' + encodeURIComponent(inscricaoId);
    const urlLogin = '../../auth/login.php?area=participante&redirect=minhas-inscricoes&retorno_pagamento=1&inscricao_id=' + encodeURIComponent(inscricaoId);

    const ctaHtml = `
        <div class="pix-cta-timeout" style="margin-top:20px;padding:20px;background:#fff8e6;border:1px solid #f59e0b;border-radius:12px;text-align:center;">
            <p style="margin:0 0 16px 0;color:#92400e;font-size:15px;font-weight:600;">
                Se vocÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âª jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ efetuou o pagamento, acesse sua ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rea para confirmar sua inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o.
            </p>
            <a href="${urlMinhasInscricoes}" class="cta-ir-area" style="display:inline-block;background:#0b4340;color:white;padding:12px 24px;border-radius:8px;font-weight:600;text-decoration:none;">
                Ir para minha ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rea
            </a>
            <p style="margin:12px 0 0 0;font-size:12px;color:#6b7280;">
                NÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o concluiu o pagamento? <a href="${urlLogin}" style="color:#0b4340;">Fazer login</a>
            </p>
        </div>
    `;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'Aguardando confirmaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o',
            text: 'Se vocÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âª jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ efetuou o pagamento, acesse sua ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rea para confirmar sua inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o.',
            confirmButtonText: 'Ir para minha ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rea',
            confirmButtonColor: '#0b4340',
            showCancelButton: true,
            cancelButtonText: 'Ficar na pÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡gina'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = urlMinhasInscricoes;
            }
        });
    }

    if (pixContainer) {
        const wrap = document.createElement('div');
        wrap.innerHTML = ctaHtml;
        pixContainer.appendChild(wrap.firstElementChild);
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
        const inscricaoId = window.dadosInscricao?.inscricaoId;
        if (!inscricaoId) {
            throw new Error('ID da inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado');
        }

        // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Tentar gerar PIX diretamente - backend recupera dados do banco
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾ [GERAR_PIX] Tentando gerar PIX com dados do banco...');
        
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

        const responseText = await response.text();
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ [GERAR_PIX] Response status:', response.status);
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ [GERAR_PIX] Response text (primeiros 500 chars):', responseText.substring(0, 500));
        
        let errorData;
        
        try {
            errorData = JSON.parse(responseText);
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ [GERAR_PIX] JSON parseado:', errorData);
        } catch (e) {
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_PIX] Erro ao parsear JSON:', e);
            errorData = { error: responseText || `HTTP ${response.status}: ${response.statusText}` };
        }

        // Se erro, verificar se ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© por dados faltando
        if (!response.ok) {
            const errorMessage = errorData.error || errorData.message || `Erro HTTP ${response.status}`;
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_PIX] ============================================');
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_PIX] ERRO DETECTADO!');
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_PIX] Status:', response.status);
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_PIX] Mensagem:', errorMessage);
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_PIX] Error data:', JSON.stringify(errorData, null, 2));
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_PIX] Campos faltantes:', errorData.campos_faltantes);
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_PIX] ============================================');
            
            // Restaurar botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
            if (btnPix) {
                btnPix.disabled = false;
                btnPix.innerHTML = '<span>ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢Ãƒâ€šÃ‚Â³</span><span>Pagar com PIX</span>';
                btnPix.style.opacity = '1';
            }
            
            // Verificar se ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© erro de CPF faltando
            const errorLower = errorMessage.toLowerCase();
            const temCamposFaltantes = errorData.campos_faltantes && Array.isArray(errorData.campos_faltantes) && errorData.campos_faltantes.length > 0;
            
            if (errorLower.includes('cpf') || errorLower.includes('documento') || (temCamposFaltantes && errorData.campos_faltantes.includes('cpf'))) {
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â [GERAR_PIX] CPF faltando, coletando...');
                try {
                    await coletarCPFModal();
                    // Tentar novamente apÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³s coletar CPF
                    return await gerarPixPagamento();
                } catch (error) {
                    if (error.message === 'CPF nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o informado') {
                        return; // UsuÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio cancelou
                    }
                    throw error;
                }
            }
            
            // Se nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o for erro de CPF, lanÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ar erro normalmente
            throw new Error(errorMessage);
        }

        // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Usar errorData jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ parseado (nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o pode ler response.json() novamente)
        const result = errorData;
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ [GERAR_PIX] Resultado PIX:', result);

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

        // Polling: verificar status do pagamento a cada 5s e redirecionar ao sucesso quando aprovado
        // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: inscricaoId jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ foi declarado na linha 929, apenas reutilizar
        if (inscricaoId) {
            iniciarPollingStatusPagamentoPix(inscricaoId);
        }

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

        // Usar SweetAlert para mostrar erro de forma elegante
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro ao Gerar PIX',
                text: error.message || 'NÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o foi possÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel gerar o cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³digo PIX. Tente novamente ou escolha outra forma de pagamento.',
                confirmButtonText: 'Entendi',
                confirmButtonColor: '#0b4340'
            });
        } else {
            mostrarErro('Erro ao gerar PIX: ' + error.message);
        }

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
    if (!container) {
        console.warn('[PAGAMENTO] renderizarResumoCompra: elemento #resumo-compra nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado');
        return;
    }

    if (!window.dadosInscricao) {
        console.warn('[PAGAMENTO] renderizarResumoCompra: window.dadosInscricao nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel');
        return;
    }

    const modalidades = window.dadosInscricao?.modalidades || [];
    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Usar produtosExtras diretamente de window.dadosInscricao (nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o de ficha)
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];
    
    console.log('[PAGAMENTO] renderizarResumoCompra:', {
        modalidadesCount: modalidades.length,
        produtosExtrasCount: produtosExtras.length,
        totalModalidades: window.dadosInscricao?.totalModalidades,
        totalProdutosExtras: window.dadosInscricao?.totalProdutosExtras,
        valorDesconto: window.dadosInscricao?.valorDesconto
    });

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
    const valorDesconto = window.dadosInscricao?.valorDesconto || 0;
    if (valorDesconto > 0) {
        html += `
            <div class="flex justify-between py-2">
                <span class="text-red-600">- Desconto</span>
                <span class="font-semibold text-red-600">R$ ${parseFloat(valorDesconto).toFixed(2).replace('.', ',')}</span>
            </div>
        `;
    }

    // Mostrar taxa de repasse se houver
    const percentualRepasse = window.dadosInscricao?.percentualRepasse || 0;
    if (percentualRepasse > 0) {
        const subtotal = calcularSubtotal();
        const taxaRepasse = subtotal * (percentualRepasse / 100);
        html += `
            <div class="flex justify-between py-2 text-gray-600">
                <span>Taxa administrativa (${percentualRepasse.toFixed(2).replace('.', ',')}%)</span>
                <span class="font-semibold">R$ ${taxaRepasse.toFixed(2).replace('.', ',')}</span>
            </div>
        `;
    }

    container.innerHTML = html;
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Atualizar valor total
function updateTotalAmount() {
    const totalElement = document.getElementById('total-geral');
    if (!totalElement) {
        console.warn('[PAGAMENTO] updateTotalAmount: elemento #total-geral nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado');
        return;
    }

    if (!window.dadosInscricao) {
        console.warn('[PAGAMENTO] updateTotalAmount: window.dadosInscricao nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel');
        return;
    }

    const total = calcularTotal();
    console.log('[PAGAMENTO] updateTotalAmount: atualizando total para R$', total.toFixed(2));
    
    totalElement.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;

    // Atualizar estado do botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o quando total mudar
    if (typeof atualizarBotaoPagamento === 'function') {
        atualizarBotaoPagamento();
    }
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Inicializar array global de produtos extras selecionados
// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CORREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Produtos extras estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o em ficha.produtos_extras
window.produtosExtrasSelecionados = window.dadosInscricao?.ficha?.produtos_extras || window.dadosInscricao?.produtosExtras || [];

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o para mostrar mensagem de sucesso apÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³s pagamento aprovado (com SweetAlert)
function mostrarMensagemSucesso(paymentId) {
    const inscricaoId = window.dadosInscricao?.inscricaoId;
    const userEmail = window.dadosInscricao?.ficha?.email || window.dadosInscricao?.usuario?.email || 'seu email';
    const eventoNome = window.dadosInscricao?.evento?.nome || 'evento';
    const valorTotal = calcularTotal();
    
    // Determinar URL da home baseado na estrutura do projeto
    const homeUrl = '../public/index.php';
    
    Swal.fire({
        icon: 'success',
        title: 'InscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o Confirmada!',
        html: `
            <div style="text-align: left; padding: 1rem 0;">
                <p style="font-size: 1.1rem; color: #1f2937; margin-bottom: 1.5rem;">
                    Seu pagamento foi aprovado com sucesso. Sua participaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o no evento estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ garantida!
                </p>
                
                <div style="background: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <p style="font-weight: 600; color: #0369a1; margin-bottom: 0.75rem; display: flex; align-items: center;">
                        <svg style="width: 1.25rem; height: 1.25rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Verifique sua caixa de correio
                    </p>
                    <p style="color: #075985; margin-bottom: 0.75rem;">
                        Enviamos um email de confirmaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o para <strong>${userEmail}</strong>
                    </p>
                    <ul style="color: #0c4a6e; font-size: 0.875rem; margin: 0; padding-left: 1.25rem;">
                        <li style="margin-bottom: 0.5rem;">Verifique sua pasta de <strong>Spam</strong> ou <strong>Lixo EletrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´nico</strong></li>
                        <li style="margin-bottom: 0.5rem;">O email pode levar alguns minutos para chegar</li>
                        <li>Mantenha seu nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºmero de inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o para o dia do evento</li>
                    </ul>
                </div>
                
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
                    <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">
                        <strong style="color: #374151;">Evento:</strong> ${eventoNome}
                    </p>
                    <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">
                        <strong style="color: #374151;">Valor pago:</strong> R$ ${valorTotal.toFixed(2).replace('.', ',')}
                    </p>
                </div>
            </div>
        `,
        showCancelButton: false,
        confirmButtonText: 'Voltar ao InÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­cio',
        confirmButtonColor: '#0b4340',
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'swal2-popup-custom',
            title: 'swal2-title-custom',
            htmlContainer: 'swal2-html-container-custom'
        },
        buttonsStyling: true,
        width: '600px',
        padding: '2rem'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = homeUrl;
        }
    });
    
    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Mensagem de sucesso exibida com SweetAlert');
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Verificar se usuÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio tem CPF cadastrado
async function verificarCPFUsuario() {
    try {
        const response = await fetch(getApiUrl('participante/verificar_cpf.php'), {
            method: 'GET',
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error('Erro ao verificar CPF');
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erro ao verificar CPF:', error);
        return { success: false, tem_cpf: false, cpf: null };
    }
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Modal para coletar CPF
async function coletarCPFModal() {
    return new Promise((resolve, reject) => {
        if (typeof Swal === 'undefined') {
            reject(new Error('SweetAlert2 nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel'));
            return;
        }
        
        Swal.fire({
            title: 'CPF ObrigatÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³rio',
            html: `
                <p style="text-align: left; margin-bottom: 16px; color: #6c757d;">
                    Para pagar com boleto bancÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio, ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio informar seu CPF.
                </p>
                <input 
                    type="text" 
                    id="swal-cpf-input" 
                    class="swal2-input" 
                    placeholder="000.000.000-00"
                    maxlength="14"
                    style="text-align: center; font-size: 16px; letter-spacing: 2px;"
                >
                <p style="text-align: left; margin-top: 8px; font-size: 12px; color: #6c757d;">
                    Seus dados estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o protegidos e serÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o salvos apenas para processamento do pagamento.
                </p>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Salvar e Continuar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            focusConfirm: false,
            preConfirm: async () => {
                const cpfInput = document.getElementById('swal-cpf-input');
                const cpf = cpfInput?.value || '';
                
                if (!cpf || cpf.trim() === '') {
                    Swal.showValidationMessage('Por favor, informe seu CPF');
                    return false;
                }
                
                // Validar formato bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡sico
                const cpfLimpo = cpf.replace(/[^0-9]/g, '');
                if (cpfLimpo.length !== 11) {
                    Swal.showValidationMessage('CPF deve conter 11 dÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­gitos');
                    return false;
                }
                
                // Salvar CPF
                try {
                    const response = await fetch(getApiUrl('participante/atualizar_cpf.php'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ cpf: cpfLimpo })
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        Swal.showValidationMessage(data.message || 'Erro ao salvar CPF');
                        return false;
                    }
                    
                    return true;
                } catch (error) {
                    Swal.showValidationMessage('Erro ao salvar CPF. Tente novamente.');
                    return false;
                }
            },
            didOpen: () => {
                const cpfInput = document.getElementById('swal-cpf-input');
                if (cpfInput) {
                    // MÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡scara de CPF
                    cpfInput.addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length <= 11) {
                            value = value.replace(/(\d{3})(\d)/, '$1.$2');
                            value = value.replace(/(\d{3})(\d)/, '$1.$2');
                            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                            e.target.value = value;
                        }
                    });
                    
                    cpfInput.focus();
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                resolve();
            } else {
                reject(new Error('CPF nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o informado'));
            }
        });
    });
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Verificar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o do usuÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
async function verificarEnderecoUsuario() {
    try {
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â [VERIFICAR_ENDERECO] Iniciando verificaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o...');
        const response = await fetch(getApiUrl('participante/verificar_endereco.php'), {
            method: 'GET',
            credentials: 'same-origin'
        });
        
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â [VERIFICAR_ENDERECO] Response status:', response.status, response.statusText);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [VERIFICAR_ENDERECO] Erro HTTP:', response.status, errorText);
            throw new Error('Erro ao verificar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o');
        }
        
        const data = await response.json();
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€¦Ã‚Â  [VERIFICAR_ENDERECO] Resposta completa da API:', JSON.stringify(data, null, 2));
        return data;
    } catch (error) {
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [VERIFICAR_ENDERECO] Erro ao verificar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o:', error);
        return { success: false, endereco_completo: false, campos_faltando: [] };
    }
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Modal para coletar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o
async function coletarEnderecoModal() {
    return new Promise((resolve, reject) => {
        if (typeof Swal === 'undefined') {
            reject(new Error('SweetAlert2 nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel'));
            return;
        }
        
        Swal.fire({
            title: 'EndereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o ObrigatÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³rio',
            html: `
                <p style="text-align: left; margin-bottom: 16px; color: #6c757d;">
                    Para pagar com boleto bancÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio, ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio informar seu endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o completo.
                </p>
                <div style="text-align: left;">
                    <input 
                        type="text" 
                        id="swal-cep-input" 
                        class="swal2-input" 
                        placeholder="CEP (00000-000)"
                        maxlength="9"
                        style="margin-bottom: 8px;"
                    >
                    <input 
                        type="text" 
                        id="swal-endereco-input" 
                        class="swal2-input" 
                        placeholder="Logradouro (Rua, Avenida, etc.)"
                        style="margin-bottom: 8px;"
                    >
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                        <input 
                            type="text" 
                            id="swal-numero-input" 
                            class="swal2-input" 
                            placeholder="NÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºmero"
                        >
                        <input 
                            type="text" 
                            id="swal-complemento-input" 
                            class="swal2-input" 
                            placeholder="Complemento (opcional)"
                        >
                    </div>
                    <input 
                        type="text" 
                        id="swal-bairro-input" 
                        class="swal2-input" 
                        placeholder="Bairro"
                        style="margin-bottom: 8px;"
                    >
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 8px; margin-bottom: 8px;">
                        <input 
                            type="text" 
                            id="swal-cidade-input" 
                            class="swal2-input" 
                            placeholder="Cidade"
                        >
                        <select 
                            id="swal-uf-input" 
                            class="swal2-input"
                            style="padding: 8px 12px;"
                        >
                            <option value="">UF</option>
                            <option value="AC">AC</option>
                            <option value="AL">AL</option>
                            <option value="AP">AP</option>
                            <option value="AM">AM</option>
                            <option value="BA">BA</option>
                            <option value="CE">CE</option>
                            <option value="DF">DF</option>
                            <option value="ES">ES</option>
                            <option value="GO">GO</option>
                            <option value="MA">MA</option>
                            <option value="MT">MT</option>
                            <option value="MS">MS</option>
                            <option value="MG">MG</option>
                            <option value="PA">PA</option>
                            <option value="PB">PB</option>
                            <option value="PR">PR</option>
                            <option value="PE">PE</option>
                            <option value="PI">PI</option>
                            <option value="RJ">RJ</option>
                            <option value="RN">RN</option>
                            <option value="RS">RS</option>
                            <option value="RO">RO</option>
                            <option value="RR">RR</option>
                            <option value="SC">SC</option>
                            <option value="SP">SP</option>
                            <option value="SE">SE</option>
                            <option value="TO">TO</option>
                        </select>
                    </div>
                </div>
                <p style="text-align: left; margin-top: 8px; font-size: 12px; color: #6c757d;">
                    Seus dados estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o protegidos e serÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o salvos apenas para processamento do pagamento.
                </p>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Salvar e Continuar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            focusConfirm: false,
            width: '600px',
            preConfirm: async () => {
                const cep = document.getElementById('swal-cep-input')?.value || '';
                const endereco = document.getElementById('swal-endereco-input')?.value || '';
                const numero = document.getElementById('swal-numero-input')?.value || '';
                const complemento = document.getElementById('swal-complemento-input')?.value || '';
                const bairro = document.getElementById('swal-bairro-input')?.value || '';
                const cidade = document.getElementById('swal-cidade-input')?.value || '';
                const uf = document.getElementById('swal-uf-input')?.value || '';
                
                // Validar campos obrigatÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³rios
                const cepLimpo = cep.replace(/\D/g, '');
                if (!cepLimpo || cepLimpo.length !== 8) {
                    Swal.showValidationMessage('CEP deve conter 8 dÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­gitos');
                    return false;
                }
                
                if (!endereco || endereco.trim() === '') {
                    Swal.showValidationMessage('Logradouro ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© obrigatÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³rio');
                    return false;
                }
                
                if (!numero || numero.trim() === '') {
                    Swal.showValidationMessage('NÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºmero ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© obrigatÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³rio');
                    return false;
                }
                
                if (!bairro || bairro.trim() === '') {
                    Swal.showValidationMessage('Bairro ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© obrigatÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³rio');
                    return false;
                }
                
                if (!cidade || cidade.trim() === '') {
                    Swal.showValidationMessage('Cidade ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© obrigatÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ria');
                    return false;
                }
                
                if (!uf || uf.length !== 2) {
                    Swal.showValidationMessage('UF ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© obrigatÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ria');
                    return false;
                }
                
                // Salvar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o
                try {
                    const response = await fetch(getApiUrl('participante/atualizar_endereco.php'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            cep: cepLimpo,
                            endereco: endereco.trim(),
                            numero: numero.trim(),
                            complemento: complemento.trim() || null,
                            bairro: bairro.trim(),
                            cidade: cidade.trim(),
                            uf: uf.toUpperCase()
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        Swal.showValidationMessage(data.message || 'Erro ao salvar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o');
                        return false;
                    }
                    
                    return true;
                } catch (error) {
                    Swal.showValidationMessage('Erro ao salvar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o. Tente novamente.');
                    return false;
                }
            },
            didOpen: () => {
                const cepInput = document.getElementById('swal-cep-input');
                if (cepInput) {
                    // MÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡scara de CEP
                    cepInput.addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length <= 8) {
                            value = value.replace(/(\d{5})(\d)/, '$1-$2');
                            e.target.value = value;
                            
                            // Buscar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o quando CEP tiver 8 dÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­gitos
                            if (value.replace(/\D/g, '').length === 8) {
                                buscarEnderecoPorCEPModal(value.replace(/\D/g, ''));
                            }
                        }
                    });
                    
                    cepInput.focus();
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                resolve();
            } else {
                reject(new Error('EndereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o informado'));
            }
        });
    });
}

// FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o auxiliar para buscar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o via ViaCEP no modal
async function buscarEnderecoPorCEPModal(cep) {
    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await response.json();
        
        if (!data.erro) {
            const enderecoInput = document.getElementById('swal-endereco-input');
            const bairroInput = document.getElementById('swal-bairro-input');
            const cidadeInput = document.getElementById('swal-cidade-input');
            const ufInput = document.getElementById('swal-uf-input');
            
            if (enderecoInput) enderecoInput.value = data.logradouro || '';
            if (bairroInput) bairroInput.value = data.bairro || '';
            if (cidadeInput) cidadeInput.value = data.localidade || '';
            if (ufInput) ufInput.value = data.uf || '';
            
            // Focar no campo nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºmero apÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³s preencher
            const numeroInput = document.getElementById('swal-numero-input');
            if (numeroInput) numeroInput.focus();
        }
    } catch (error) {
        console.error('Erro ao buscar CEP:', error);
    }
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Gerar Boleto para pagamento
async function gerarBoletoPagamento() {
    try {
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â¡ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ [GERAR_BOLETO] Iniciando geraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o de boleto...');
        
        // Obter dados necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rios
        const inscricaoId = window.dadosInscricao?.inscricaoId;
        const total = calcularTotal();
        
        if (!inscricaoId) {
            throw new Error('ID da inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado');
        }
        
        if (total <= 0) {
            throw new Error('Valor total invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido');
        }
        
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ [GERAR_BOLETO] Dados:', { inscricaoId, total });
        
        // Preparar UI
        const btnBoleto = document.getElementById('btn-boleto-pagamento');
        const boletoContainer = document.getElementById('boleto-container');

        if (!btnBoleto || !boletoContainer) {
            throw new Error('Elementos Boleto nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrados');
        }

        btnBoleto.disabled = true;
        btnBoleto.innerHTML = '<span>ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€šÃ‚Â³</span><span>Gerando Boleto...</span>';
        btnBoleto.style.opacity = '0.7';

        boletoContainer.classList.remove('hidden');
        boletoContainer.innerHTML = `
            <div style="text-align:center;padding:20px;background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef;">
                <div style="display:inline-block;width:20px;height:20px;border:2px solid #007bff;border-radius:50%;border-top-color:transparent;animation:spin 1s linear infinite;"></div>
                <p style="margin:12px 0 0 0;color:#6c757d;font-size:14px;">Gerando boleto bancÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio...</p>
            </div>
            <style>
                @keyframes spin { to { transform: rotate(360deg); } }
            </style>
        `;

        boletoContainer.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Tentar gerar boleto diretamente - backend recupera dados do banco
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾ [GERAR_BOLETO] Tentando gerar boleto com dados do banco...');
        
        const response = await fetch(getApiUrl('inscricao/create_boleto.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                inscricao_id: inscricaoId,
                valor_total: total
            })
        });

        const responseText = await response.text();
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ [GERAR_BOLETO] Response status:', response.status);
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ [GERAR_BOLETO] Response text (primeiros 500 chars):', responseText.substring(0, 500));
        
        let errorData;
        
        try {
            errorData = JSON.parse(responseText);
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ [GERAR_BOLETO] JSON parseado:', errorData);
        } catch (e) {
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] Erro ao parsear JSON:', e);
            errorData = { error: responseText || `HTTP ${response.status}: ${response.statusText}` };
        }

        // Se erro, verificar se ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© por dados faltando
        if (!response.ok) {
            const errorMessage = errorData.error || errorData.message || `Erro HTTP ${response.status}`;
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] ============================================');
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] ERRO DETECTADO!');
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] Status:', response.status);
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] Mensagem:', errorMessage);
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] Error data:', JSON.stringify(errorData, null, 2));
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] Campos faltantes:', errorData.campos_faltantes);
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] ============================================');
            
            // Restaurar botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
            if (btnBoleto) {
                btnBoleto.disabled = false;
                btnBoleto.innerHTML = 'Pagar com Boleto';
                btnBoleto.style.opacity = '1';
            }
            
            // Verificar se ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© erro de CPF faltando
            const errorLower = errorMessage.toLowerCase();
            if (errorLower.includes('cpf') || errorLower.includes('documento')) {
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â [GERAR_BOLETO] CPF faltando, coletando...');
                try {
                    await coletarCPFModal();
                    // Tentar novamente apÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³s coletar CPF
                    return await gerarBoletoPagamento();
                } catch (error) {
                    if (error.message === 'CPF nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o informado') {
                        return; // UsuÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio cancelou
                    }
                    throw error;
                }
            }
            
            // Verificar se ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© erro de endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o faltando (mais abrangente)
            // Verificar tambÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©m se hÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ campos_faltantes no response (do backend)
            const temCamposFaltantes = errorData.campos_faltantes && Array.isArray(errorData.campos_faltantes) && errorData.campos_faltantes.length > 0;
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â [GERAR_BOLETO] Verificando tipo de erro...');
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â [GERAR_BOLETO] temCamposFaltantes:', temCamposFaltantes);
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â [GERAR_BOLETO] errorLower:', errorLower);
            
            const erroEndereco = errorLower.includes('endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o') || errorLower.includes('endereco') || 
                errorLower.includes('cep') || errorLower.includes('bairro') ||
                errorLower.includes('cidade') || errorLower.includes('uf') ||
                errorLower.includes('numero') || errorLower.includes('nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºmero') ||
                errorLower.includes('dados de endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o') || errorLower.includes('dados cadastrais') ||
                errorLower.includes('verifique seus dados') || temCamposFaltantes;
            
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â [GERAR_BOLETO] erroEndereco detectado?', erroEndereco);
            
            if (erroEndereco) {
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â [GERAR_BOLETO] ============================================');
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â [GERAR_BOLETO] ENDEREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡O FALTANDO DETECTADO!');
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â [GERAR_BOLETO] Mensagem:', errorMessage);
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â [GERAR_BOLETO] Campos faltantes:', errorData.campos_faltantes);
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â [GERAR_BOLETO] Abrindo modal para coletar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o...');
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â [GERAR_BOLETO] ============================================');
                
                try {
                    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾ [GERAR_BOLETO] Chamando coletarEnderecoModal()...');
                    await coletarEnderecoModal();
                    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ [GERAR_BOLETO] EndereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o coletado com sucesso!');
                    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾ [GERAR_BOLETO] Tentando gerar boleto novamente...');
                    // Tentar novamente apÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³s coletar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o
                    return await gerarBoletoPagamento();
                } catch (error) {
                    console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] Erro ao coletar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o:', error);
                    console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] Stack:', error.stack);
                    if (error.message === 'EndereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o informado') {
                        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â [GERAR_BOLETO] UsuÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio cancelou coleta de endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o');
                        return; // UsuÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio cancelou
                    }
                    // Se houver outro erro, mostrar ao usuÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro ao coletar endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o',
                        text: error.message || 'NÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o foi possÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel coletar o endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o. Tente novamente.'
                    });
                    throw error;
                }
            }
            
            // Outro tipo de erro - mostrar ao usuÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] Erro nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o reconhecido como CPF ou endereÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o');
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] Mensagem completa:', errorMessage);
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ [GERAR_BOLETO] Error data completo:', JSON.stringify(errorData, null, 2));
            
            Swal.fire({
                icon: 'error',
                title: 'Erro ao gerar boleto',
                text: errorMessage,
                confirmButtonText: 'OK'
            });
            
            throw new Error(errorMessage);
        }

        // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Processar resultado (pode ser success: false com use_pix quando boleto rejeitado)
        const result = errorData; // JÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ foi parseado acima
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ [GERAR_BOLETO] Resultado completo:', result);
        
        if (!result.success) {
            const usePix = result.use_pix === true || result.error_code === 'BOLETO_REJECTED_BY_BANK';
            const msg = result.message || result.error || 'NÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o foi possÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel gerar o boleto.';
            if (usePix && typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'info',
                    title: 'Boleto indisponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel',
                    text: msg,
                    confirmButtonText: 'Entendi',
                    confirmButtonColor: '#0b4340'
                });
                const pixSection = document.querySelector('#opcao-pix, [data-forma="pix"], .secao-pix') || document.querySelector('.border-green-500');
                if (pixSection) {
                    pixSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }
            throw new Error(msg);
        }
        
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ [GERAR_BOLETO] Boleto gerado com sucesso!');

        // Verificar se barcode estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ presente
        if (!result.barcode || result.barcode === '') {
            console.warn('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â [GERAR_BOLETO] ATENÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O: Barcode nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o foi retornado pela API!');
            console.warn('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â [GERAR_BOLETO] Dados recebidos:', JSON.stringify(result, null, 2));
        } else {
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ [GERAR_BOLETO] Barcode recebido:', result.barcode);
        }

        const dataVencimento = result.date_of_expiration ? new Date(result.date_of_expiration).toLocaleDateString('pt-BR') : 'N/A';
        
        // Verificar se boleto estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ximo do vencimento (menos de 24 horas)
        let avisoVencimento = '';
        if (result.date_of_expiration) {
            const dataVencimentoObj = new Date(result.date_of_expiration);
            const agora = new Date();
            const horasRestantes = (dataVencimentoObj - agora) / (1000 * 60 * 60);
            
            if (horasRestantes < 24 && horasRestantes > 0) {
                const horas = Math.round(horasRestantes);
                avisoVencimento = `
                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px; margin: 16px 0; text-align: center;">
                        <strong style="color: #856404; font-size: 14px;">ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â AtenÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o!</strong>
                        <p style="margin: 8px 0 0 0; color: #856404; font-size: 13px;">
                            Seu boleto vence em ${horas} ${horas === 1 ? 'hora' : 'horas'}.<br>
                            Realize o pagamento o quanto antes para garantir sua inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o.
                        </p>
                    </div>
                `;
            } else if (horasRestantes <= 0) {
                avisoVencimento = `
                    <div style="background: #f8d7da; border: 1px solid #dc3545; border-radius: 8px; padding: 12px; margin: 16px 0; text-align: center;">
                        <strong style="color: #721c24; font-size: 14px;">ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Boleto Expirado</strong>
                        <p style="margin: 8px 0 0 0; color: #721c24; font-size: 13px;">
                            Este boleto jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ expirou. Entre em contato com o suporte para gerar um novo boleto.
                        </p>
                    </div>
                `;
            }
        }

        boletoContainer.innerHTML = `
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 24px; margin: 16px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="background: #007bff; color: white; padding: 8px 16px; border-radius: 20px; display: inline-block; font-size: 14px; font-weight: 600; margin-bottom: 12px;">
                        ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾ Boleto BancÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
                    </div>
                    <h3 style="margin: 0; color: #2c3e50; font-size: 18px; font-weight: 600;">
                        R$ ${result.transaction_amount.toFixed(2).replace('.', ',')}
                    </h3>
                    <p style="margin: 8px 0 0 0; color: #6c757d; font-size: 14px;">
                        Vencimento: ${dataVencimento}
                    </p>
                </div>
                
                ${avisoVencimento}
                
                    <div style="background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 16px; margin: 16px 0;">
                        <label style="display: block; font-size: 14px; font-weight: 600; color: #495057; margin-bottom: 8px;">
                            CÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³digo de Barras
                        </label>
                        <div style="position: relative;">
                            <input type="text" readonly value="${result.barcode || 'CÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³digo de barras nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel. Use o link abaixo para visualizar o boleto.'}" style="width: 100%; border: 1px solid #ced4da; border-radius: 6px; padding: 12px; font-family: monospace; font-size: 14px; background: #f8f9fa; color: #212529;" id="boleto-barcode-input">
                            ${result.barcode ? `<button onclick="const input = document.getElementById('boleto-barcode-input'); input.select(); document.execCommand('copy'); this.textContent='Copiado!'; setTimeout(() => this.textContent='Copiar', 2000);" style="position: absolute; top: 8px; right: 8px; background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">Copiar</button>` : ''}
                        </div>
                        ${!result.barcode ? `<div style="margin-top: 8px; padding: 8px; background: #fff3cd; border-radius: 4px; font-size: 12px; color: #856404;">
                            <strong>ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾Ãƒâ€šÃ‚Â¹ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Nota:</strong> O cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³digo de barras serÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ exibido no PDF do boleto. Clique em "Baixar Boleto PDF" para visualizar.
                        </div>` : ''}
                    </div>
                
                <div style="text-align: center; margin-top: 20px; display: flex; gap: 12px; justify-content: center;">
                    ${result.ticket_url ? `<a href="${result.ticket_url}" target="_blank" rel="noopener noreferrer" onclick="event.preventDefault(); window.open('${result.ticket_url}', '_blank', 'noopener,noreferrer'); return false;" style="background: #007bff; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: inline-block; transition: background 0.2s; cursor: pointer;">
                        ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Baixar Boleto PDF
                    </a>` : ''}
                    ${result.external_resource_url ? `<a href="${result.external_resource_url}" target="_blank" rel="noopener noreferrer" onclick="event.preventDefault(); window.open('${result.external_resource_url}', '_blank', 'noopener,noreferrer'); return false;" style="background: #28a745; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: inline-block; transition: background 0.2s; cursor: pointer;">
                        ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Â Ver Boleto Online
                    </a>` : ''}
                    ${!result.ticket_url && !result.external_resource_url ? `<div style="padding: 12px; background: #fff3cd; border-radius: 8px; color: #856404; font-size: 14px;">
                        <strong>ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â AtenÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o:</strong> O boleto ainda nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel. Aguarde alguns instantes e tente novamente.
                    </div>` : ''}
                </div>
                
                <div style="margin-top: 16px; padding: 12px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                    <p style="margin: 0; font-size: 13px; color: #856404;">
                        <strong>ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Importante:</strong> O boleto vence em ${dataVencimento}. ApÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³s o pagamento, a confirmaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o pode levar atÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© 2 dias ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºteis.
                    </p>
                </div>
            </div>
        `;

        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Boleto gerado com sucesso!');

    } catch (error) {
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro ao gerar Boleto:', error);

        const boletoContainer = document.getElementById('boleto-container');
        if (boletoContainer) {
            boletoContainer.innerHTML = `
                <div style="text-align:center;padding:20px;background:#fff5f5;border:1px solid #fed7d7;border-radius:8px;">
                    <div style="color:#e53e3e;font-size:24px;margin-bottom:8px;">ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â</div>
                    <p style="margin:0;color:#c53030;font-size:14px;">Falha ao gerar boleto: ${error.message}</p>
                </div>
            `;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro ao Gerar Boleto',
                text: error.message || 'NÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o foi possÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel gerar o boleto. Tente novamente ou escolha outra forma de pagamento.',
                confirmButtonText: 'Entendi',
                confirmButtonColor: '#0b4340'
            });
        } else {
            mostrarErro('Erro ao gerar boleto: ' + error.message);
        }

    } finally {
        const btnBoleto = document.getElementById('btn-boleto-pagamento');
        if (btnBoleto) {
            btnBoleto.disabled = false;
            btnBoleto.innerHTML = '<span>ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾</span><span>Pagar com Boleto</span>';
            btnBoleto.style.opacity = '1';
        }
    }
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Re-exportar funÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes no final (garantir que estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o todas disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­veis)
window.setupEventListeners = setupEventListeners;
window.atualizarBotaoPagamento = atualizarBotaoPagamento;
window.inicializarPagamento = inicializarPagamento;
window.validarDadosParaPagamento = validarDadosParaPagamento;
window.calcularTotal = calcularTotal;
window.atualizarBotaoFinalizarCompra = atualizarBotaoPagamento;
window.mostrarErro = mostrarErro;
window.renderizarResumoCompra = renderizarResumoCompra;
window.updateTotalAmount = updateTotalAmount;
window.mostrarMensagemSucesso = mostrarMensagemSucesso;
window.gerarBoletoPagamento = gerarBoletoPagamento;
