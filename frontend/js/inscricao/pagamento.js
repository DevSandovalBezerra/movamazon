// ========== MODO PADRÃO: Checkout Transparente ativo na página de inscrição.
// ROLLBACK: defina USE_CHECKOUT_PRO_REDIRECT = true para voltar ao redirect.
const USE_CHECKOUT_PRO_REDIRECT = false;

// Variáveis globais para MercadoPago
let mp = null;
let bricksBuilder = null;

// Função para inicializar MercadoPago usando public key dinâmica
async function inicializarMercadoPago() {
    return new Promise(async (resolve, reject) => {
        // Se já foi inicializado, retornar imediatamente
        if (mp && bricksBuilder) {
            console.log("✅ MercadoPago já inicializado");
            resolve({ mp, bricksBuilder });
            return;
        }

        try {
            // Buscar configuração do servidor
            const config = await getMercadoPagoConfig();
            
            if (!config.public_key) {
                throw new Error('Public key não encontrada na configuração');
            }

            // Verificar se o SDK já está disponível
            if (typeof MercadoPago !== 'undefined') {
                mp = new MercadoPago(config.public_key);
                bricksBuilder = mp.bricks();
                
                console.log("=== MERCADO PAGO INITIALIZATION ===");
                console.log("Environment:", config.environment);
                console.log("Is Production:", config.is_production);
                console.log("MercadoPago instance:", mp);
                console.log("BricksBuilder instance:", bricksBuilder);
                
                // Tornar acessível globalmente
                window.mp = mp;
                window.bricksBuilder = bricksBuilder;
                
                resolve({ mp, bricksBuilder });
                return;
            }

            // Se não está disponível, aguardar
            console.log("⏳ Aguardando SDK do MercadoPago carregar...");
            let attempts = 0;
            const maxAttempts = 50; // 5 segundos máximo (50 * 100ms)
            
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
                        
                        // Tornar acessível globalmente
                        window.mp = mp;
                        window.bricksBuilder = bricksBuilder;
                        
                        resolve({ mp, bricksBuilder });
                    } catch (error) {
                        console.error("❌ ============================================");
                        console.error("❌ ERRO AO INICIALIZAR MERCADOPAGO");
                        console.error("❌ ============================================");
                        console.error("❌ Mensagem:", error.message);
                        console.error("❌ Stack:", error.stack);
                        console.error("❌ Tipo:", error.name);
                        console.error("❌ Config:", config);
                        console.error("❌ ============================================");
                        reject(error);
                    }
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    const error = new Error('SDK do MercadoPago não carregou após 5 segundos');
                    console.error("❌", error.message);
                    reject(error);
                }
            }, 100);
        } catch (error) {
            console.error("❌ ============================================");
            console.error("❌ ERRO AO OBTER CONFIGURAÇÃO DO MERCADO PAGO");
            console.error("❌ ============================================");
            console.error("❌ Mensagem:", error.message);
            console.error("❌ Stack:", error.stack);
            console.error("❌ Tipo:", error.name);
            console.error("❌ ============================================");
            reject(error);
        }
    });
}

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
                console.log("[PAGAMENTO] Payment Brick inicializado com sucesso");
                
                // Verificar se os elementos de radio estão presentes
                setTimeout(() => {
                    const radioButtons = document.querySelectorAll('#paymentBrick_container input[type="radio"]');
                    console.log(`[PAGAMENTO] Radio buttons encontrados: ${radioButtons.length}`);
                    if (radioButtons.length === 0) {
                        console.warn('[PAGAMENTO] Nenhum radio button encontrado no Payment Brick - pode indicar problema de renderização');
                    }
                }, 1000);
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
                                                 Aqui você pode ocultar o Payment Brick e mostrar mensagens de sucesso.
                                                */
                                                console.log("=== STATUS SCREEN BRICK READY ===");
                                                document.getElementById("paymentBrick_container").style.display = 'none';
                                                
                                                // ✅ Mostrar mensagem de sucesso e aviso sobre email
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
                                // Usar bricksBuilder do parâmetro ou global como fallback
                                const builderForStatus = bricksBuilder || window.bricksBuilder;
                                if (builderForStatus) {
                                    renderStatusScreenBrick(builderForStatus);
                                } else {
                                    console.error('❌ bricksBuilder não disponível para renderStatusScreenBrick');
                                    mostrarErro('Erro ao exibir status do pagamento: SDK não inicializado');
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

// ✅ Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    try {
        console.log('✅ DOM carregado, inicializando apenas resumo...');

        // ✅ CORREÇÃO: Aguardar window.dadosInscricao estar disponível antes de calcular
        function inicializarQuandoDadosDisponiveis() {
            if (!window.dadosInscricao) {
                console.log('[PAGAMENTO] Aguardando window.dadosInscricao...');
                setTimeout(inicializarQuandoDadosDisponiveis, 100);
                return;
            }

            console.log('[PAGAMENTO] window.dadosInscricao disponível, inicializando resumo...');
            console.log('[PAGAMENTO] Dados:', {
                totalModalidades: window.dadosInscricao.totalModalidades,
                totalProdutosExtras: window.dadosInscricao.totalProdutosExtras,
                valorDesconto: window.dadosInscricao.valorDesconto
            });

            // Inicializar APENAS o resumo e event listeners
            // NÃO inicializar o pagamento automaticamente
            if (typeof renderizarResumoCompra === 'function') {
                renderizarResumoCompra();
            }
            
            if (typeof updateTotalAmount === 'function') {
                updateTotalAmount();
            }
            
            if (typeof setupEventListeners === 'function') {
                setupEventListeners();
            }

            console.log('✅ Inicialização básica concluída - aguardando clique do usuário');
        }

        // Iniciar verificação
        inicializarQuandoDadosDisponiveis();

    } catch (error) {
        console.error('❌ Erro ao inicializar:', error);
    }
});

// ✅ Função principal para inicializar o pagamento (INATIVA quando USE_CHECKOUT_PRO_REDIRECT = true; ver comentário ROLLBACK no topo)
async function inicializarPagamento() {
    if (typeof USE_CHECKOUT_PRO_REDIRECT !== 'undefined' && USE_CHECKOUT_PRO_REDIRECT) {
        console.log('⏭️ Checkout Pro redirect ativo; inicializarPagamento não executa Brick.');
        return;
    }
    console.log('🚀 ============================================');
    console.log('🚀 inicializarPagamento() - INICIANDO...');
    console.log('🚀 ============================================');
    
    if (window.paymentBrickController) {
        console.log('⚠️ Payment Brick já foi inicializado anteriormente');
        console.log('🔍 window.paymentBrickController:', window.paymentBrickController);
        return;
    }

    try {
        // ✅ Aguardar SDK do MercadoPago estar disponível
        console.log('⏳ Aguardando inicialização do MercadoPago...');
        const { mp: mpInstance, bricksBuilder: bricksBuilderInstance } = await inicializarMercadoPago();
        
        // Garantir que as variáveis globais estejam atualizadas
        if (!bricksBuilder) {
            bricksBuilder = bricksBuilderInstance;
        }
        
        console.log('✅ MercadoPago inicializado, continuando...');

        console.log('🔍 Calculando total...');
        const total = calcularTotal();
        console.log('🔍 Total calculado:', total);

        if (total <= 0) {
            console.error('❌ Total inválido:', total);
            throw new Error('Valor total inválido');
        }

        console.log('✅ Valor total calculado:', total);

        // Criar pré-inscrição se necessário
        console.log('🔍 Verificando inscrição ID...');
        console.log('🔍 window.dadosInscricao:', window.dadosInscricao);
        let inscricaoId = window.dadosInscricao?.inscricaoId;
        console.log('🔍 Inscrição ID atual:', inscricaoId);
        
        if (!inscricaoId) {
            console.log('📝 Criando pré-inscrição...');
            inscricaoId = await criarPreInscricao(total);
            console.log('✅ Pré-inscrição criada, ID:', inscricaoId);
        } else {
            console.log('✅ Usando inscrição ID existente:', inscricaoId);
        }

        // Criar preference
        console.log('📝 Criando preference...');
        const preferenceResult = await criarPreference(inscricaoId, total);
        const preferenceId = preferenceResult.preference_id || preferenceResult;
        console.log('✅ Preference criada, ID:', preferenceId);

        // Configurar elementos HTML necessários
        console.log('🔧 Configurando elementos HTML...');
        configurarElementosHTML(total, preferenceId);
        console.log('✅ Elementos HTML configurados');

        // Aguardar um pouco para garantir que o DOM está totalmente atualizado
        await new Promise(resolve => setTimeout(resolve, 100));

        // Se o container do Brick não existe (página usa Checkout Pro redirect), redirecionar para init_point
        const brickContainer = document.getElementById('paymentBrick_container');
        const initPoint = preferenceResult.init_point || preferenceResult.initPoint;
        if (!brickContainer && initPoint) {
            console.log('✅ Container Brick não encontrado (Checkout Pro). Redirecionando para init_point...');
            window.location.href = initPoint;
            return;
        }
        if (!brickContainer) {
            throw new Error('Container de pagamento não encontrado. Use o botão Finalizar Compra para ser redirecionado ao Mercado Pago.');
        }

        if (brickContainer) {
            const isVisible = brickContainer.offsetWidth > 0 && brickContainer.offsetHeight > 0;
            console.log('🔍 Verificação final do container:', {
                width: brickContainer.offsetWidth,
                height: brickContainer.offsetHeight,
                isVisible: isVisible,
                display: window.getComputedStyle(brickContainer).display,
                visibility: window.getComputedStyle(brickContainer).visibility
            });

            if (!isVisible) {
                console.warn('⚠️ Container não está visível, forçando visibilidade...');
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
            throw new Error('bricksBuilder não está disponível');
        }
        
        await renderPaymentBrick(builderToUse);

        console.log('✅ Payment Brick renderizado com sucesso!');

    } catch (error) {
        console.error('❌ ============================================');
        console.error('❌ ERRO AO INICIALIZAR PAGAMENTO');
        console.error('❌ ============================================');
        console.error('❌ Mensagem:', error.message);
        console.error('❌ Stack:', error.stack);
        console.error('❌ Tipo:', error.name);
        console.error('❌ Dados de inscrição:', window.dadosInscricao);
        console.error('❌ MercadoPago disponível:', typeof MercadoPago !== 'undefined');
        console.error('❌ bricksBuilder disponível:', !!window.bricksBuilder);
        console.error('❌ ============================================');
        
        // Mostrar erro ao usuário de forma amigável
        if (window.mostrarErro) {
            window.mostrarErro('Erro ao inicializar pagamento: ' + error.message);
        } else {
            alert('Erro ao inicializar pagamento: ' + error.message);
        }
        
        // Re-throw para que o caller possa tratar se necessário
        throw error;
    }
}

// ✅ Criar pré-inscrição
async function criarPreInscricao(total) {
    // ✅ NOVO: Usar a nova API de salvamento independente
    const payload = montarPayloadPreInscricao(total);

    console.log('📤 Payload enviado para save_inscricao:', payload);

    const response = await fetch(getApiUrl('inscricao/save_inscricao.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    console.log('📥 Response status:', response.status);

    if (!response.ok) {
        const errorText = await response.text();
        console.error('❌ Erro na resposta:', errorText);
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const result = await response.json();
    console.log('📥 Resultado da inscrição:', result);

    if (!result?.success) {
        throw new Error(result?.message || 'Falha ao salvar inscrição');
    }

    const inscricaoId = result.inscricao_id;
    if (!window.dadosInscricao) window.dadosInscricao = {};
    window.dadosInscricao.inscricaoId = inscricaoId;
    window.dadosInscricao.externalReference = result.external_reference;

    console.log('✅ Inscrição salva no banco: ID=' + inscricaoId + ', ExternalRef=' + result.external_reference);
    return inscricaoId;
}

// ✅ Criar preference
async function criarPreference(inscricaoId, total) {
    const payload = montarPayloadCreatePreference(inscricaoId, total);

    console.log('📤 Payload enviado para create_preference:', payload);

    const response = await fetch(getApiUrl('inscricao/create_preference.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    console.log('📥 Response status create_preference:', response.status);

    if (!response.ok) {
        const errorText = await response.text();
        console.error('❌ Erro na resposta create_preference:', errorText);
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const result = await response.json();
    console.log('📥 Resultado da preference:', result);

    if (!result?.success || !result?.preference_id) {
        throw new Error(result?.error || 'Falha ao criar preferência');
    }

    console.log('✅ Preference criada:', result.preference_id, 'init_point:', result.init_point ? 'ok' : 'n/a');
    return { preference_id: result.preference_id, init_point: result.init_point || '' };
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

    // Atualizar display do valor
    const valorDisplay = document.getElementById('valor-display');
    if (valorDisplay) {
        valorDisplay.textContent = total.toFixed(2).replace('.', ',');
    }

    console.log('✅ Elementos HTML configurados:', {
        valor: total,
        preferenceId: preferenceId,
        usePreferenceId: 'true'
    });
}

// ✅ Calcular subtotal (modalidades + extras - desconto, ANTES da taxa de repasse)
// ✅ Arredondar para 2 casas decimais (evitar erros de ponto flutuante)
function arredondar(valor) {
    return Math.round((valor + Number.EPSILON) * 100) / 100;
}

function calcularSubtotal() {
    // ✅ CORREÇÃO: Usar valores já calculados no PHP quando disponíveis
    // Fórmula: modalidade + produtos_extras - desconto_cupom
    const totalModalidades = parseFloat(window.dadosInscricao?.totalModalidades || 0);
    const totalProdutosExtras = parseFloat(window.dadosInscricao?.totalProdutosExtras || 0);
    const valorDesconto = parseFloat(window.dadosInscricao?.valorDesconto || 0);

    // Se os valores calculados estão disponíveis, usar diretamente
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

    // Fallback: calcular manualmente se valores não estiverem disponíveis
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

// ✅ Calcular total (subtotal + taxa de repasse)
function calcularTotal() {
    let total = calcularSubtotal(); // já vem arredondado

    // Aplicar taxa de repasse (percentual sobre subtotal)
    const percentualRepasse = window.dadosInscricao?.percentualRepasse || 0;
    if (percentualRepasse > 0) {
        const taxaRepasse = arredondar(total * (percentualRepasse / 100));
        total = arredondar(total + taxaRepasse);
    }

    console.log('[PAGAMENTO] calcularTotal() resultado:', total, 'tipo:', typeof total, 'percentualRepasse:', percentualRepasse + '%');

    return Math.max(0, total);
}


// ✅ Montar payload para pré-inscrição
function montarPayloadPreInscricao(total) {
    const modalidade = window.dadosInscricao?.modalidades?.[0] || {};
    // ✅ CORREÇÃO: Produtos extras vêm de dadosInscricao.produtosExtras
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];

    // Calcular valores separados
    const valorModalidades = window.dadosInscricao?.totalModalidades || 0;
    const valorExtras = window.dadosInscricao?.totalProdutosExtras || 0;
    const valorDesconto = window.dadosInscricao?.valorDesconto || 0;

    return {
        evento_id: window.dadosInscricao?.eventoId || 1,
        modalidade_id: modalidade.id || 1, // ✅ CORREÇÃO: Enviar modalidade_id em vez de modalidades
        tamanho_camiseta: window.dadosInscricao?.ficha?.tamanho_camiseta || 'M',
        valor_modalidades: valorModalidades,
        valor_extras: valorExtras,
        valor_desconto: valorDesconto,
        cupom: window.dadosInscricao?.cupomAplicado || null,
        produtos_extras: produtosExtras,
        seguro: 0
    };
}

// ✅ Montar payload para criar preference
function montarPayloadCreatePreference(inscricaoId, total) {
    const modalidade = window.dadosInscricao?.modalidades?.[0] || {};
    // ✅ CORREÇÃO: Produtos extras vêm de dadosInscricao.produtosExtras
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];

    return {
        inscricao_id: inscricaoId,
        modalidade_nome: modalidade.nome || 'Inscrição',
        lote_numero: modalidade.lote_numero || null,
        valor_total: total,
        evento_nome: window.dadosInscricao?.evento?.nome || 'Evento',
        kit_nome: modalidade.kit_nome || null,
        produtos_extras: produtosExtras,
        cupom: window.dadosInscricao?.cupomAplicado || null,
        valor_desconto: window.dadosInscricao?.valorDesconto || 0,
        seguro: 0,
        origem: 'inscricao' // back_urls no backend (inscricao → /inscricao/sucesso)
    };
}

// ✅ Mostrar erro
function mostrarErro(mensagem) {
    console.error('❌ Erro:', mensagem);
    alert('Erro: ' + mensagem);
}

// ✅ Validar se dados estão prontos para pagamento
function validarDadosParaPagamento() {
    const modalidades = window.dadosInscricao?.modalidades || [];
    // ✅ CORREÇÃO: Produtos extras vêm de dadosInscricao.produtosExtras
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];
    const total = calcularTotal();

    console.log('🔍 Validando dados para pagamento:', {
        modalidades: modalidades.length,
        produtosExtras: produtosExtras.length,
        produtosExtrasData: produtosExtras,
        total: total,
        dadosInscricao: !!window.dadosInscricao
    });

    // Verificar se tem pelo menos uma modalidade selecionada
    if (modalidades.length === 0) {
        console.log('❌ Nenhuma modalidade selecionada');
        return false;
    }

    // Verificar se o total é maior que zero
    if (total <= 0) {
        console.log('❌ Total inválido:', total);
        return false;
    }

    // Verificar se dados básicos existem
    if (!window.dadosInscricao) {
        console.log('❌ Dados de inscrição não encontrados');
        return false;
    }

    console.log('✅ Dados válidos para pagamento');
    return true;
}

// ✅ Habilitar/desabilitar botão de pagamento
function atualizarBotaoPagamento() {
    const btnPagar = document.getElementById('btn-finalizar-compra');
    if (!btnPagar) return;

    const dadosValidos = validarDadosParaPagamento();

    if (dadosValidos) {
        btnPagar.disabled = false;
        btnPagar.classList.remove('opacity-50', 'cursor-not-allowed');
        btnPagar.classList.add('hover:bg-blue-700');
        console.log('✅ Botão Finalizar Compra habilitado');
    } else {
        btnPagar.disabled = true;
        btnPagar.classList.add('opacity-50', 'cursor-not-allowed');
        btnPagar.classList.remove('hover:bg-blue-700');
        console.log('❌ Botão Finalizar Compra desabilitado');
    }
}


// ✅ Clique em Finalizar Compra:
// - redirect ativo: cria preferência e redireciona
// - transparente ativo: inicializa Brick/PIX/Boleto na página
function attachClickListener(btnPagar) {
    if (!btnPagar) {
        console.error('❌ attachClickListener: botão não fornecido');
        return;
    }

    console.log('🔗 Anexando listener ao botão de pagamento...');

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
                    window.mostrarErro('Valor total inválido.');
                } else {
                    alert('Valor total inválido.');
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
                    console.log('✅ Redirecionando para Checkout Pro:', initPoint);
                    window.location.href = initPoint;
                    return;
                }

                throw new Error('Resposta do servidor sem link de pagamento.');
            }

            // Transparente na página: renderiza Brick e mantém PIX/Boleto habilitados
            if (typeof inicializarPagamento === 'function') {
                const janelaPagamento = document.getElementById('janela-pagamento-mercadopago');
                if (janelaPagamento) {
                    janelaPagamento.classList.remove('hidden');
                }
                await inicializarPagamento();
                return;
            }
            throw new Error('Função de inicialização do checkout não disponível.');
        } catch (error) {
            console.error('❌ [FINALIZAR_COMPRA]', error.message);
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

    console.log('✅ Listener de pagamento anexado ao botão');
}

// ✅ Setup de event listeners (SIMPLIFICADO baseado no exemplo funcional)
function setupEventListeners() {
    console.log('🔧 SETUP EVENT LISTENERS - Iniciando...');
    
    const btnPagar = document.getElementById('btn-finalizar-compra');
    console.log('🔍 Botão encontrado:', btnPagar);
    console.log('🔍 Botão já tem listener?', btnPagar?.hasAttribute('data-listener-added'));
    
    // Remover listener anterior se existir (para evitar duplicação)
    if (btnPagar && btnPagar.hasAttribute('data-listener-added')) {
        console.log('⚠️ Botão já tem listener, removendo e recriando...');
        const newBtn = btnPagar.cloneNode(true);
        btnPagar.parentNode.replaceChild(newBtn, btnPagar);
        // Buscar novamente após clonar
        const btnPagarNovo = document.getElementById('btn-finalizar-compra');
        if (btnPagarNovo) {
            btnPagarNovo.setAttribute('data-listener-added', 'true');
            console.log('✅ Novo botão preparado para listener');
            attachClickListener(btnPagarNovo);
        }
    } else if (btnPagar) {
        btnPagar.setAttribute('data-listener-added', 'true');
        console.log('✅ Atributo data-listener-added definido');
        attachClickListener(btnPagar);
    } else {
        console.error('❌ Botão btn-finalizar-compra NÃO encontrado!');
    }

    // Botão voltar ao resumo
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

            // Ocultar container PIX se estiver visível
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

    // ROLLBACK: com USE_CHECKOUT_PRO_REDIRECT = false, reative os blocos abaixo para PIX/Boleto na página
    if (typeof USE_CHECKOUT_PRO_REDIRECT === 'undefined' || !USE_CHECKOUT_PRO_REDIRECT) {
        const btnPix = document.getElementById('btn-pix-pagamento');
        if (btnPix && !btnPix.hasAttribute('data-listener-added')) {
            btnPix.setAttribute('data-listener-added', 'true');
            btnPix.addEventListener('click', async function (e) {
                e.preventDefault();
                console.log('✅ Botão PIX clicado');
                await gerarPixPagamento();
            });
        }
        const btnBoleto = document.getElementById('btn-boleto-pagamento');
        if (btnBoleto && !btnBoleto.hasAttribute('data-listener-added')) {
            btnBoleto.setAttribute('data-listener-added', 'true');
            btnBoleto.addEventListener('click', async function (e) {
                e.preventDefault();
                console.log('✅ Botão Boleto clicado');
                await gerarBoletoPagamento();
            });
        }
    }
    
    console.log('✅ setupEventListeners concluído');
}

// ✅ EXPORTAR TODAS AS FUNÇÕES PARA WINDOW (após todas as definições)
window.setupEventListeners = setupEventListeners;
window.atualizarBotaoPagamento = atualizarBotaoPagamento;
window.inicializarPagamento = inicializarPagamento;
window.calcularTotal = calcularTotal;
window.validarDadosParaPagamento = validarDadosParaPagamento;
window.mostrarErro = mostrarErro;
window.renderizarResumoCompra = renderizarResumoCompra;
window.updateTotalAmount = updateTotalAmount;

console.log('✅ ============================================');
console.log('✅ TODAS AS FUNÇÕES EXPORTADAS PARA WINDOW');
console.log('✅ ============================================');
console.log('✅ Funções disponíveis:', {
    setupEventListeners: typeof window.setupEventListeners,
    atualizarBotaoPagamento: typeof window.atualizarBotaoPagamento,
    inicializarPagamento: typeof window.inicializarPagamento,
    validarDadosParaPagamento: typeof window.validarDadosParaPagamento,
    calcularTotal: typeof window.calcularTotal,
    mostrarErro: typeof window.mostrarErro,
    renderizarResumoCompra: typeof window.renderizarResumoCompra,
    updateTotalAmount: typeof window.updateTotalAmount
});

// ✅ Polling: sync como fallback do webhook; se pago, redireciona; ao timeout, CTA "Ir para minha área"
function iniciarPollingStatusPagamentoPix(inscricaoId) {
    const intervaloMs = 5000;
    const maxTentativas = 72; // 6 minutos
    let tentativas = 0;
    const syncUrl = getApiUrl('participante/sync_payment_status.php?inscricao_id=' + encodeURIComponent(inscricaoId));

    const timer = setInterval(async () => {
        tentativas++;
        try {
            // Sync consulta o MP e atualiza o banco (fallback quando webhook não dispara)
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

// Exibir mensagem e botão "Ir para minha área" após timeout do polling PIX
function mostrarCtaIrParaMinhaArea(inscricaoId) {
    const pixContainer = document.getElementById('pix-container');
    const urlMinhasInscricoes = '../../participante/index.php?page=minhas-inscricoes&inscricao_id=' + encodeURIComponent(inscricaoId);
    const urlLogin = '../../auth/login.php?area=participante&redirect=minhas-inscricoes&retorno_pagamento=1&inscricao_id=' + encodeURIComponent(inscricaoId);

    const ctaHtml = `
        <div class="pix-cta-timeout" style="margin-top:20px;padding:20px;background:#fff8e6;border:1px solid #f59e0b;border-radius:12px;text-align:center;">
            <p style="margin:0 0 16px 0;color:#92400e;font-size:15px;font-weight:600;">
                Se você já efetuou o pagamento, acesse sua área para confirmar sua inscrição.
            </p>
            <a href="${urlMinhasInscricoes}" class="cta-ir-area" style="display:inline-block;background:#0b4340;color:white;padding:12px 24px;border-radius:8px;font-weight:600;text-decoration:none;">
                Ir para minha área
            </a>
            <p style="margin:12px 0 0 0;font-size:12px;color:#6b7280;">
                Não concluiu o pagamento? <a href="${urlLogin}" style="color:#0b4340;">Fazer login</a>
            </p>
        </div>
    `;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'Aguardando confirmação',
            text: 'Se você já efetuou o pagamento, acesse sua área para confirmar sua inscrição.',
            confirmButtonText: 'Ir para minha área',
            confirmButtonColor: '#0b4340',
            showCancelButton: true,
            cancelButtonText: 'Ficar na página'
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

// ✅ Gerar PIX para pagamento
async function gerarPixPagamento() {
    try {
        const btnPix = document.getElementById('btn-pix-pagamento');
        const pixContainer = document.getElementById('pix-container');

        if (!btnPix || !pixContainer) {
            throw new Error('Elementos PIX não encontrados');
        }

        // Estado de loading
        btnPix.disabled = true;
        btnPix.innerHTML = '<span>⏳</span><span>Gerando PIX...</span>';
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
        console.log('💰 Total para PIX:', total);

        // Verificar se temos inscrição ID
        const inscricaoId = window.dadosInscricao?.inscricaoId;
        if (!inscricaoId) {
            throw new Error('ID da inscrição não encontrado');
        }

        // ✅ Tentar gerar PIX diretamente - backend recupera dados do banco
        console.log('🔄 [GERAR_PIX] Tentando gerar PIX com dados do banco...');
        
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
        console.log('📥 [GERAR_PIX] Response status:', response.status);
        console.log('📥 [GERAR_PIX] Response text (primeiros 500 chars):', responseText.substring(0, 500));
        
        let errorData;
        
        try {
            errorData = JSON.parse(responseText);
            console.log('✅ [GERAR_PIX] JSON parseado:', errorData);
        } catch (e) {
            console.error('❌ [GERAR_PIX] Erro ao parsear JSON:', e);
            errorData = { error: responseText || `HTTP ${response.status}: ${response.statusText}` };
        }

        // Se erro, verificar se é por dados faltando
        if (!response.ok) {
            const errorMessage = errorData.error || errorData.message || `Erro HTTP ${response.status}`;
            console.error('❌ [GERAR_PIX] ============================================');
            console.error('❌ [GERAR_PIX] ERRO DETECTADO!');
            console.error('❌ [GERAR_PIX] Status:', response.status);
            console.error('❌ [GERAR_PIX] Mensagem:', errorMessage);
            console.error('❌ [GERAR_PIX] Error data:', JSON.stringify(errorData, null, 2));
            console.error('❌ [GERAR_PIX] Campos faltantes:', errorData.campos_faltantes);
            console.error('❌ [GERAR_PIX] ============================================');
            
            // Restaurar botão
            if (btnPix) {
                btnPix.disabled = false;
                btnPix.innerHTML = '<span>💳</span><span>Pagar com PIX</span>';
                btnPix.style.opacity = '1';
            }
            
            // Verificar se é erro de CPF faltando
            const errorLower = errorMessage.toLowerCase();
            const temCamposFaltantes = errorData.campos_faltantes && Array.isArray(errorData.campos_faltantes) && errorData.campos_faltantes.length > 0;
            
            if (errorLower.includes('cpf') || errorLower.includes('documento') || (temCamposFaltantes && errorData.campos_faltantes.includes('cpf'))) {
                console.log('⚠️ [GERAR_PIX] CPF faltando, coletando...');
                try {
                    await coletarCPFModal();
                    // Tentar novamente após coletar CPF
                    return await gerarPixPagamento();
                } catch (error) {
                    if (error.message === 'CPF não informado') {
                        return; // Usuário cancelou
                    }
                    throw error;
                }
            }
            
            // Se não for erro de CPF, lançar erro normalmente
            throw new Error(errorMessage);
        }

        // ✅ Usar errorData já parseado (não pode ler response.json() novamente)
        const result = errorData;
        console.log('📥 [GERAR_PIX] Resultado PIX:', result);

        if (!result.success) {
            throw new Error(result.error || 'Falha ao gerar PIX');
        }

        // Renderizar interface PIX
        pixContainer.innerHTML = `
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 24px; margin: 16px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="background: #00a650; color: white; padding: 8px 16px; border-radius: 20px; display: inline-block; font-size: 14px; font-weight: 600; margin-bottom: 12px;">
                        💳 PIX Instantâneo
                    </div>
                    <h3 style="margin: 0; color: #2c3e50; font-size: 18px; font-weight: 600;">
                        R$ ${result.transaction_amount.toFixed(2).replace('.', ',')}
                    </h3>
                    <p style="margin: 8px 0 0 0; color: #6c757d; font-size: 14px;">
                        Código: #${result.external_reference}
                    </p>
                </div>
                
                <div style="text-align: center; margin: 20px 0;">
                    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: inline-block;">
                        <img src="data:image/png;base64, ${result.qr_code_base64}" style="width: 180px; height: 180px; border-radius: 8px;" />
                    </div>
                </div>
                
                <div style="background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 16px; margin: 16px 0;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #495057; margin-bottom: 8px;">
                        Código PIX (Copie e cole no seu app)
                    </label>
                    <div style="position: relative;">
                        <textarea readonly style="width: 100%; height: 80px; border: 1px solid #ced4da; border-radius: 6px; padding: 12px; font-family: monospace; font-size: 12px; resize: none; background: #f8f9fa;">${result.qr_code}</textarea>
                        <button onclick="navigator.clipboard.writeText(this.previousElementSibling.value); this.textContent='Copiado!'; setTimeout(() => this.textContent='Copiar', 2000);" style="position: absolute; top: 8px; right: 8px; background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">Copiar</button>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="${result.ticket_url}" target="_blank" style="background: #00a650; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: inline-block; transition: background 0.2s;">
                        📱 Abrir no App
                    </a>
                </div>
                
                <div style="margin-top: 16px; padding: 12px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #2196f3;">
                    <p style="margin: 0; font-size: 13px; color: #1565c0;">
                        <strong>💡 Dica:</strong> Escaneie o QR Code com seu app bancário ou copie o código PIX para pagar instantaneamente.
                    </p>
                </div>
            </div>
        `;

        console.log('✅ PIX gerado com sucesso!');

        // Polling: verificar status do pagamento a cada 5s e redirecionar ao sucesso quando aprovado
        // ✅ CORREÇÃO: inscricaoId já foi declarado na linha 929, apenas reutilizar
        if (inscricaoId) {
            iniciarPollingStatusPagamentoPix(inscricaoId);
        }

    } catch (error) {
        console.error('❌ Erro ao gerar PIX:', error);

        const pixContainer = document.getElementById('pix-container');
        if (pixContainer) {
            pixContainer.innerHTML = `
                <div style="text-align:center;padding:20px;background:#fff5f5;border:1px solid #fed7d7;border-radius:8px;">
                    <div style="color:#e53e3e;font-size:24px;margin-bottom:8px;">⚠️</div>
                    <p style="margin:0;color:#c53030;font-size:14px;">Falha ao gerar PIX: ${error.message}</p>
                </div>
            `;
        }

        // Usar SweetAlert para mostrar erro de forma elegante
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro ao Gerar PIX',
                text: error.message || 'Não foi possível gerar o código PIX. Tente novamente ou escolha outra forma de pagamento.',
                confirmButtonText: 'Entendi',
                confirmButtonColor: '#0b4340'
            });
        } else {
            mostrarErro('Erro ao gerar PIX: ' + error.message);
        }

    } finally {
        // Restaurar botão
        const btnPix = document.getElementById('btn-pix-pagamento');
        if (btnPix) {
            btnPix.disabled = false;
            btnPix.innerHTML = '<span>💳</span><span>Pagar com PIX</span>';
            btnPix.style.opacity = '1';
        }
    }
}

// ✅ Renderizar resumo da compra
function renderizarResumoCompra() {
    const container = document.getElementById('resumo-compra');
    if (!container) {
        console.warn('[PAGAMENTO] renderizarResumoCompra: elemento #resumo-compra não encontrado');
        return;
    }

    if (!window.dadosInscricao) {
        console.warn('[PAGAMENTO] renderizarResumoCompra: window.dadosInscricao não está disponível');
        return;
    }

    const modalidades = window.dadosInscricao?.modalidades || [];
    // ✅ CORREÇÃO: Usar produtosExtras diretamente de window.dadosInscricao (não de ficha)
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

// ✅ Atualizar valor total
function updateTotalAmount() {
    const totalElement = document.getElementById('total-geral');
    if (!totalElement) {
        console.warn('[PAGAMENTO] updateTotalAmount: elemento #total-geral não encontrado');
        return;
    }

    if (!window.dadosInscricao) {
        console.warn('[PAGAMENTO] updateTotalAmount: window.dadosInscricao não está disponível');
        return;
    }

    const total = calcularTotal();
    console.log('[PAGAMENTO] updateTotalAmount: atualizando total para R$', total.toFixed(2));
    
    totalElement.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;

    // Atualizar estado do botão quando total mudar
    if (typeof atualizarBotaoPagamento === 'function') {
        atualizarBotaoPagamento();
    }
}

// ✅ Inicializar array global de produtos extras selecionados
// ✅ CORREÇÃO: Produtos extras estão em ficha.produtos_extras
window.produtosExtrasSelecionados = window.dadosInscricao?.ficha?.produtos_extras || window.dadosInscricao?.produtosExtras || [];

// ✅ Função para mostrar mensagem de sucesso após pagamento aprovado (com SweetAlert)
function mostrarMensagemSucesso(paymentId) {
    const inscricaoId = window.dadosInscricao?.inscricaoId;
    const userEmail = window.dadosInscricao?.ficha?.email || window.dadosInscricao?.usuario?.email || 'seu email';
    const eventoNome = window.dadosInscricao?.evento?.nome || 'evento';
    const valorTotal = calcularTotal();
    
    // Determinar URL da home baseado na estrutura do projeto
    const homeUrl = '../public/index.php';
    
    Swal.fire({
        icon: 'success',
        title: 'Inscrição Confirmada!',
        html: `
            <div style="text-align: left; padding: 1rem 0;">
                <p style="font-size: 1.1rem; color: #1f2937; margin-bottom: 1.5rem;">
                    Seu pagamento foi aprovado com sucesso. Sua participação no evento está garantida!
                </p>
                
                <div style="background: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <p style="font-weight: 600; color: #0369a1; margin-bottom: 0.75rem; display: flex; align-items: center;">
                        <svg style="width: 1.25rem; height: 1.25rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Verifique sua caixa de correio
                    </p>
                    <p style="color: #075985; margin-bottom: 0.75rem;">
                        Enviamos um email de confirmação para <strong>${userEmail}</strong>
                    </p>
                    <ul style="color: #0c4a6e; font-size: 0.875rem; margin: 0; padding-left: 1.25rem;">
                        <li style="margin-bottom: 0.5rem;">Verifique sua pasta de <strong>Spam</strong> ou <strong>Lixo Eletrônico</strong></li>
                        <li style="margin-bottom: 0.5rem;">O email pode levar alguns minutos para chegar</li>
                        <li>Mantenha seu número de inscrição para o dia do evento</li>
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
        confirmButtonText: 'Voltar ao Início',
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
    
    console.log('✅ Mensagem de sucesso exibida com SweetAlert');
}

// ✅ Verificar se usuário tem CPF cadastrado
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

// ✅ Modal para coletar CPF
async function coletarCPFModal() {
    return new Promise((resolve, reject) => {
        if (typeof Swal === 'undefined') {
            reject(new Error('SweetAlert2 não está disponível'));
            return;
        }
        
        Swal.fire({
            title: 'CPF Obrigatório',
            html: `
                <p style="text-align: left; margin-bottom: 16px; color: #6c757d;">
                    Para pagar com boleto bancário, é necessário informar seu CPF.
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
                    Seus dados estão protegidos e serão salvos apenas para processamento do pagamento.
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
                
                // Validar formato básico
                const cpfLimpo = cpf.replace(/[^0-9]/g, '');
                if (cpfLimpo.length !== 11) {
                    Swal.showValidationMessage('CPF deve conter 11 dígitos');
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
                    // Máscara de CPF
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
                reject(new Error('CPF não informado'));
            }
        });
    });
}

// ✅ Verificar endereço do usuário
async function verificarEnderecoUsuario() {
    try {
        console.log('🔍 [VERIFICAR_ENDERECO] Iniciando verificação...');
        const response = await fetch(getApiUrl('participante/verificar_endereco.php'), {
            method: 'GET',
            credentials: 'same-origin'
        });
        
        console.log('🔍 [VERIFICAR_ENDERECO] Response status:', response.status, response.statusText);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ [VERIFICAR_ENDERECO] Erro HTTP:', response.status, errorText);
            throw new Error('Erro ao verificar endereço');
        }
        
        const data = await response.json();
        console.log('📊 [VERIFICAR_ENDERECO] Resposta completa da API:', JSON.stringify(data, null, 2));
        return data;
    } catch (error) {
        console.error('❌ [VERIFICAR_ENDERECO] Erro ao verificar endereço:', error);
        return { success: false, endereco_completo: false, campos_faltando: [] };
    }
}

// ✅ Modal para coletar endereço
async function coletarEnderecoModal() {
    return new Promise((resolve, reject) => {
        if (typeof Swal === 'undefined') {
            reject(new Error('SweetAlert2 não está disponível'));
            return;
        }
        
        Swal.fire({
            title: 'Endereço Obrigatório',
            html: `
                <p style="text-align: left; margin-bottom: 16px; color: #6c757d;">
                    Para pagar com boleto bancário, é necessário informar seu endereço completo.
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
                            placeholder="Número"
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
                    Seus dados estão protegidos e serão salvos apenas para processamento do pagamento.
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
                
                // Validar campos obrigatórios
                const cepLimpo = cep.replace(/\D/g, '');
                if (!cepLimpo || cepLimpo.length !== 8) {
                    Swal.showValidationMessage('CEP deve conter 8 dígitos');
                    return false;
                }
                
                if (!endereco || endereco.trim() === '') {
                    Swal.showValidationMessage('Logradouro é obrigatório');
                    return false;
                }
                
                if (!numero || numero.trim() === '') {
                    Swal.showValidationMessage('Número é obrigatório');
                    return false;
                }
                
                if (!bairro || bairro.trim() === '') {
                    Swal.showValidationMessage('Bairro é obrigatório');
                    return false;
                }
                
                if (!cidade || cidade.trim() === '') {
                    Swal.showValidationMessage('Cidade é obrigatória');
                    return false;
                }
                
                if (!uf || uf.length !== 2) {
                    Swal.showValidationMessage('UF é obrigatória');
                    return false;
                }
                
                // Salvar endereço
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
                        Swal.showValidationMessage(data.message || 'Erro ao salvar endereço');
                        return false;
                    }
                    
                    return true;
                } catch (error) {
                    Swal.showValidationMessage('Erro ao salvar endereço. Tente novamente.');
                    return false;
                }
            },
            didOpen: () => {
                const cepInput = document.getElementById('swal-cep-input');
                if (cepInput) {
                    // Máscara de CEP
                    cepInput.addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length <= 8) {
                            value = value.replace(/(\d{5})(\d)/, '$1-$2');
                            e.target.value = value;
                            
                            // Buscar endereço quando CEP tiver 8 dígitos
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
                reject(new Error('Endereço não informado'));
            }
        });
    });
}

// Função auxiliar para buscar endereço via ViaCEP no modal
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
            
            // Focar no campo número após preencher
            const numeroInput = document.getElementById('swal-numero-input');
            if (numeroInput) numeroInput.focus();
        }
    } catch (error) {
        console.error('Erro ao buscar CEP:', error);
    }
}

// ✅ Gerar Boleto para pagamento
async function gerarBoletoPagamento() {
    try {
        console.log('🚀 [GERAR_BOLETO] Iniciando geração de boleto...');
        
        // Obter dados necessários
        const inscricaoId = window.dadosInscricao?.inscricaoId;
        const total = calcularTotal();
        
        if (!inscricaoId) {
            throw new Error('ID da inscrição não encontrado');
        }
        
        if (total <= 0) {
            throw new Error('Valor total inválido');
        }
        
        console.log('📋 [GERAR_BOLETO] Dados:', { inscricaoId, total });
        
        // Preparar UI
        const btnBoleto = document.getElementById('btn-boleto-pagamento');
        const boletoContainer = document.getElementById('boleto-container');

        if (!btnBoleto || !boletoContainer) {
            throw new Error('Elementos Boleto não encontrados');
        }

        btnBoleto.disabled = true;
        btnBoleto.innerHTML = '<span>⏳</span><span>Gerando Boleto...</span>';
        btnBoleto.style.opacity = '0.7';

        boletoContainer.classList.remove('hidden');
        boletoContainer.innerHTML = `
            <div style="text-align:center;padding:20px;background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef;">
                <div style="display:inline-block;width:20px;height:20px;border:2px solid #007bff;border-radius:50%;border-top-color:transparent;animation:spin 1s linear infinite;"></div>
                <p style="margin:12px 0 0 0;color:#6c757d;font-size:14px;">Gerando boleto bancário...</p>
            </div>
            <style>
                @keyframes spin { to { transform: rotate(360deg); } }
            </style>
        `;

        boletoContainer.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // ✅ Tentar gerar boleto diretamente - backend recupera dados do banco
        console.log('🔄 [GERAR_BOLETO] Tentando gerar boleto com dados do banco...');
        
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
        console.log('📥 [GERAR_BOLETO] Response status:', response.status);
        console.log('📥 [GERAR_BOLETO] Response text (primeiros 500 chars):', responseText.substring(0, 500));
        
        let errorData;
        
        try {
            errorData = JSON.parse(responseText);
            console.log('✅ [GERAR_BOLETO] JSON parseado:', errorData);
        } catch (e) {
            console.error('❌ [GERAR_BOLETO] Erro ao parsear JSON:', e);
            errorData = { error: responseText || `HTTP ${response.status}: ${response.statusText}` };
        }

        // Se erro, verificar se é por dados faltando
        if (!response.ok) {
            const errorMessage = errorData.error || errorData.message || `Erro HTTP ${response.status}`;
            console.error('❌ [GERAR_BOLETO] ============================================');
            console.error('❌ [GERAR_BOLETO] ERRO DETECTADO!');
            console.error('❌ [GERAR_BOLETO] Status:', response.status);
            console.error('❌ [GERAR_BOLETO] Mensagem:', errorMessage);
            console.error('❌ [GERAR_BOLETO] Error data:', JSON.stringify(errorData, null, 2));
            console.error('❌ [GERAR_BOLETO] Campos faltantes:', errorData.campos_faltantes);
            console.error('❌ [GERAR_BOLETO] ============================================');
            
            // Restaurar botão
            if (btnBoleto) {
                btnBoleto.disabled = false;
                btnBoleto.innerHTML = 'Pagar com Boleto';
                btnBoleto.style.opacity = '1';
            }
            
            // Verificar se é erro de CPF faltando
            const errorLower = errorMessage.toLowerCase();
            if (errorLower.includes('cpf') || errorLower.includes('documento')) {
                console.log('⚠️ [GERAR_BOLETO] CPF faltando, coletando...');
                try {
                    await coletarCPFModal();
                    // Tentar novamente após coletar CPF
                    return await gerarBoletoPagamento();
                } catch (error) {
                    if (error.message === 'CPF não informado') {
                        return; // Usuário cancelou
                    }
                    throw error;
                }
            }
            
            // Verificar se é erro de endereço faltando (mais abrangente)
            // Verificar também se há campos_faltantes no response (do backend)
            const temCamposFaltantes = errorData.campos_faltantes && Array.isArray(errorData.campos_faltantes) && errorData.campos_faltantes.length > 0;
            console.log('🔍 [GERAR_BOLETO] Verificando tipo de erro...');
            console.log('🔍 [GERAR_BOLETO] temCamposFaltantes:', temCamposFaltantes);
            console.log('🔍 [GERAR_BOLETO] errorLower:', errorLower);
            
            const erroEndereco = errorLower.includes('endereço') || errorLower.includes('endereco') || 
                errorLower.includes('cep') || errorLower.includes('bairro') ||
                errorLower.includes('cidade') || errorLower.includes('uf') ||
                errorLower.includes('numero') || errorLower.includes('número') ||
                errorLower.includes('dados de endereço') || errorLower.includes('dados cadastrais') ||
                errorLower.includes('verifique seus dados') || temCamposFaltantes;
            
            console.log('🔍 [GERAR_BOLETO] erroEndereco detectado?', erroEndereco);
            
            if (erroEndereco) {
                console.log('⚠️ [GERAR_BOLETO] ============================================');
                console.log('⚠️ [GERAR_BOLETO] ENDEREÇO FALTANDO DETECTADO!');
                console.log('⚠️ [GERAR_BOLETO] Mensagem:', errorMessage);
                console.log('⚠️ [GERAR_BOLETO] Campos faltantes:', errorData.campos_faltantes);
                console.log('⚠️ [GERAR_BOLETO] Abrindo modal para coletar endereço...');
                console.log('⚠️ [GERAR_BOLETO] ============================================');
                
                try {
                    console.log('🔄 [GERAR_BOLETO] Chamando coletarEnderecoModal()...');
                    await coletarEnderecoModal();
                    console.log('✅ [GERAR_BOLETO] Endereço coletado com sucesso!');
                    console.log('🔄 [GERAR_BOLETO] Tentando gerar boleto novamente...');
                    // Tentar novamente após coletar endereço
                    return await gerarBoletoPagamento();
                } catch (error) {
                    console.error('❌ [GERAR_BOLETO] Erro ao coletar endereço:', error);
                    console.error('❌ [GERAR_BOLETO] Stack:', error.stack);
                    if (error.message === 'Endereço não informado') {
                        console.log('⚠️ [GERAR_BOLETO] Usuário cancelou coleta de endereço');
                        return; // Usuário cancelou
                    }
                    // Se houver outro erro, mostrar ao usuário
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro ao coletar endereço',
                        text: error.message || 'Não foi possível coletar o endereço. Tente novamente.'
                    });
                    throw error;
                }
            }
            
            // Outro tipo de erro - mostrar ao usuário
            console.error('❌ [GERAR_BOLETO] Erro não reconhecido como CPF ou endereço');
            console.error('❌ [GERAR_BOLETO] Mensagem completa:', errorMessage);
            console.error('❌ [GERAR_BOLETO] Error data completo:', JSON.stringify(errorData, null, 2));
            
            Swal.fire({
                icon: 'error',
                title: 'Erro ao gerar boleto',
                text: errorMessage,
                confirmButtonText: 'OK'
            });
            
            throw new Error(errorMessage);
        }

        // ✅ Processar resultado (pode ser success: false com use_pix quando boleto rejeitado)
        const result = errorData; // Já foi parseado acima
        console.log('📥 [GERAR_BOLETO] Resultado completo:', result);
        
        if (!result.success) {
            const usePix = result.use_pix === true || result.error_code === 'BOLETO_REJECTED_BY_BANK';
            const msg = result.message || result.error || 'Não foi possível gerar o boleto.';
            if (usePix && typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'info',
                    title: 'Boleto indisponível',
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
        
        console.log('✅ [GERAR_BOLETO] Boleto gerado com sucesso!');

        // Verificar se barcode está presente
        if (!result.barcode || result.barcode === '') {
            console.warn('⚠️ [GERAR_BOLETO] ATENÇÃO: Barcode não foi retornado pela API!');
            console.warn('⚠️ [GERAR_BOLETO] Dados recebidos:', JSON.stringify(result, null, 2));
        } else {
            console.log('✅ [GERAR_BOLETO] Barcode recebido:', result.barcode);
        }

        const dataVencimento = result.date_of_expiration ? new Date(result.date_of_expiration).toLocaleDateString('pt-BR') : 'N/A';
        
        // Verificar se boleto está próximo do vencimento (menos de 24 horas)
        let avisoVencimento = '';
        if (result.date_of_expiration) {
            const dataVencimentoObj = new Date(result.date_of_expiration);
            const agora = new Date();
            const horasRestantes = (dataVencimentoObj - agora) / (1000 * 60 * 60);
            
            if (horasRestantes < 24 && horasRestantes > 0) {
                const horas = Math.round(horasRestantes);
                avisoVencimento = `
                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px; margin: 16px 0; text-align: center;">
                        <strong style="color: #856404; font-size: 14px;">⚠️ Atenção!</strong>
                        <p style="margin: 8px 0 0 0; color: #856404; font-size: 13px;">
                            Seu boleto vence em ${horas} ${horas === 1 ? 'hora' : 'horas'}.<br>
                            Realize o pagamento o quanto antes para garantir sua inscrição.
                        </p>
                    </div>
                `;
            } else if (horasRestantes <= 0) {
                avisoVencimento = `
                    <div style="background: #f8d7da; border: 1px solid #dc3545; border-radius: 8px; padding: 12px; margin: 16px 0; text-align: center;">
                        <strong style="color: #721c24; font-size: 14px;">❌ Boleto Expirado</strong>
                        <p style="margin: 8px 0 0 0; color: #721c24; font-size: 13px;">
                            Este boleto já expirou. Entre em contato com o suporte para gerar um novo boleto.
                        </p>
                    </div>
                `;
            }
        }

        boletoContainer.innerHTML = `
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 24px; margin: 16px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="background: #007bff; color: white; padding: 8px 16px; border-radius: 20px; display: inline-block; font-size: 14px; font-weight: 600; margin-bottom: 12px;">
                        📄 Boleto Bancário
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
                            Código de Barras
                        </label>
                        <div style="position: relative;">
                            <input type="text" readonly value="${result.barcode || 'Código de barras não disponível. Use o link abaixo para visualizar o boleto.'}" style="width: 100%; border: 1px solid #ced4da; border-radius: 6px; padding: 12px; font-family: monospace; font-size: 14px; background: #f8f9fa; color: #212529;" id="boleto-barcode-input">
                            ${result.barcode ? `<button onclick="const input = document.getElementById('boleto-barcode-input'); input.select(); document.execCommand('copy'); this.textContent='Copiado!'; setTimeout(() => this.textContent='Copiar', 2000);" style="position: absolute; top: 8px; right: 8px; background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">Copiar</button>` : ''}
                        </div>
                        ${!result.barcode ? `<div style="margin-top: 8px; padding: 8px; background: #fff3cd; border-radius: 4px; font-size: 12px; color: #856404;">
                            <strong>ℹ️ Nota:</strong> O código de barras será exibido no PDF do boleto. Clique em "Baixar Boleto PDF" para visualizar.
                        </div>` : ''}
                    </div>
                
                <div style="text-align: center; margin-top: 20px; display: flex; gap: 12px; justify-content: center;">
                    ${result.ticket_url ? `<a href="${result.ticket_url}" target="_blank" rel="noopener noreferrer" onclick="event.preventDefault(); window.open('${result.ticket_url}', '_blank', 'noopener,noreferrer'); return false;" style="background: #007bff; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: inline-block; transition: background 0.2s; cursor: pointer;">
                        📥 Baixar Boleto PDF
                    </a>` : ''}
                    ${result.external_resource_url ? `<a href="${result.external_resource_url}" target="_blank" rel="noopener noreferrer" onclick="event.preventDefault(); window.open('${result.external_resource_url}', '_blank', 'noopener,noreferrer'); return false;" style="background: #28a745; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: inline-block; transition: background 0.2s; cursor: pointer;">
                        🔗 Ver Boleto Online
                    </a>` : ''}
                    ${!result.ticket_url && !result.external_resource_url ? `<div style="padding: 12px; background: #fff3cd; border-radius: 8px; color: #856404; font-size: 14px;">
                        <strong>⚠️ Atenção:</strong> O boleto ainda não está disponível. Aguarde alguns instantes e tente novamente.
                    </div>` : ''}
                </div>
                
                <div style="margin-top: 16px; padding: 12px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                    <p style="margin: 0; font-size: 13px; color: #856404;">
                        <strong>⚠️ Importante:</strong> O boleto vence em ${dataVencimento}. Após o pagamento, a confirmação pode levar até 2 dias úteis.
                    </p>
                </div>
            </div>
        `;

        console.log('✅ Boleto gerado com sucesso!');

    } catch (error) {
        console.error('❌ Erro ao gerar Boleto:', error);

        const boletoContainer = document.getElementById('boleto-container');
        if (boletoContainer) {
            boletoContainer.innerHTML = `
                <div style="text-align:center;padding:20px;background:#fff5f5;border:1px solid #fed7d7;border-radius:8px;">
                    <div style="color:#e53e3e;font-size:24px;margin-bottom:8px;">⚠️</div>
                    <p style="margin:0;color:#c53030;font-size:14px;">Falha ao gerar boleto: ${error.message}</p>
                </div>
            `;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro ao Gerar Boleto',
                text: error.message || 'Não foi possível gerar o boleto. Tente novamente ou escolha outra forma de pagamento.',
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
            btnBoleto.innerHTML = '<span>📄</span><span>Pagar com Boleto</span>';
            btnBoleto.style.opacity = '1';
        }
    }
}

// ✅ Re-exportar funções no final (garantir que estão todas disponíveis)
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
