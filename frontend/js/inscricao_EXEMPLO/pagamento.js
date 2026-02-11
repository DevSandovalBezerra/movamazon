// ✅ VERSÃO CORRIGIDA - FORÇAR RECARREGAMENTO - TIMESTAMP: 2024-12-19
console.log('🔄 ARQUIVO PAGAMENTO.JS RECARREGADO - VERSÃO CORRIGIDA - TIMESTAMP: 2024-12-19');

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
    // ✅ CORREÇÃO: Verificar se os elementos existem antes de acessá-los
    const valorElement = document.getElementById('valor_payment');
    const preferenceElement = document.getElementById('preference_id');
    const usePreferenceElement = document.getElementById('use_preference_id');

    if (!valorElement || !preferenceElement || !usePreferenceElement) {
        console.error('❌ Elementos DOM não encontrados para Payment Brick');
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
document.addEventListener('DOMContentLoaded', function () {
    try {
        console.log('✅ DOM carregado, inicializando apenas resumo...');

        // Inicializar APENAS o resumo e event listeners
        // NÃO inicializar o pagamento automaticamente
        renderizarResumoCompra();
        updateTotalAmount();
        setupEventListeners();

        // Validar e habilitar botão de pagamento
        atualizarBotaoPagamento();

        console.log('✅ Inicialização básica concluída - aguardando clique do usuário');

    } catch (error) {
        console.error('❌ Erro ao inicializar:', error);
    }
});

// ✅ Função principal para inicializar o pagamento
async function inicializarPagamento() {
    if (window.paymentBrickController) {
        console.log('✅ Payment Brick já foi inicializado');
        return;
    }

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

    if (!result ? .success) {
        throw new Error(result ? .message || 'Falha ao salvar inscrição');
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

// ✅ Calcular total
function calcularTotal() {
    const modalidades = window.dadosInscricao ? .modalidades || [];
    // ✅ CORREÇÃO: Produtos extras estão em ficha.produtos_extras, não em produtosExtras
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

// ✅ Montar payload para pré-inscrição
function montarPayloadPreInscricao(total) {
    const modalidade = window.dadosInscricao ? .modalidades ? . [0] || {};
    // ✅ CORREÇÃO: Produtos extras estão em ficha.produtos_extras
    const produtosExtras = window.dadosInscricao ? .ficha ? .produtos_extras || window.dadosInscricao ? .produtosExtras || [];

    // Calcular valores separados
    const valorModalidades = window.dadosInscricao ? .totalModalidades || 0;
    const valorExtras = window.dadosInscricao ? .totalProdutosExtras || 0;
    const valorDesconto = window.dadosInscricao ? .valorDesconto || 0;

    return {
        evento_id: window.dadosInscricao ? .eventoId || 1,
        modalidade_id: modalidade.id || 1, // ✅ CORREÇÃO: Enviar modalidade_id em vez de modalidades
        tamanho_camiseta: window.dadosInscricao ? .ficha ? .tamanho_camiseta || 'M',
        valor_modalidades: valorModalidades,
        valor_extras: valorExtras,
        valor_desconto: valorDesconto,
        cupom: window.dadosInscricao ? .cupomAplicado || null,
        produtos_extras: produtosExtras,
        seguro: 0
    };
}

// ✅ Montar payload para criar preference
function montarPayloadCreatePreference(inscricaoId, total) {
    const modalidade = window.dadosInscricao ? .modalidades ? . [0] || {};
    // ✅ CORREÇÃO: Produtos extras estão em ficha.produtos_extras
    const produtosExtras = window.dadosInscricao ? .ficha ? .produtos_extras || window.dadosInscricao ? .produtosExtras || [];

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

// ✅ Validar se dados estão prontos para pagamento
function validarDadosParaPagamento() {
    const modalidades = window.dadosInscricao ? .modalidades || [];
    const produtosExtras = window.dadosInscricao ? .ficha ? .produtos_extras || window.dadosInscricao ? .produtosExtras || [];
    const total = calcularTotal();

    console.log('🔍 Validando dados para pagamento:', {
        modalidades: modalidades.length,
        produtosExtras: produtosExtras.length,
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

// ✅ Setup de event listeners
function setupEventListeners() {
    const btnPagar = document.getElementById('btn-finalizar-compra');
    if (btnPagar && !btnPagar.hasAttribute('data-listener-added')) {
        btnPagar.setAttribute('data-listener-added', 'true');
        btnPagar.addEventListener('click', async function (e) {
            e.preventDefault();

            console.log('✅ Botão Finalizar Compra clicado');

            // Verificar se dados ainda são válidos
            if (!validarDadosParaPagamento()) {
                mostrarErro('Dados de inscrição inválidos. Verifique suas seleções.');
                return;
            }

            // Mostrar janela de pagamento
            const janelaPagamento = document.getElementById('janela-pagamento-mercadopago');
            if (janelaPagamento) {
                janelaPagamento.classList.remove('hidden');
                console.log('✅ Janela de pagamento exibida');

                // Scroll para a janela de pagamento
                janelaPagamento.scrollIntoView({
                    behavior: 'smooth'
                });
            }

            // Inicializar pagamento apenas uma vez
            if (!window.paymentInitialized) {
                window.paymentInitialized = true;
                console.log('✅ Inicializando pagamento...');
                await inicializarPagamento();
            }
        });
    }

    // Botão voltar ao resumo
    const btnVoltar = document.getElementById('btn-voltar-resumo');
    if (btnVoltar && !btnVoltar.hasAttribute('data-listener-added')) {
        btnVoltar.setAttribute('data-listener-added', 'true');
        btnVoltar.addEventListener('click', function (e) {
            e.preventDefault();

            console.log('✅ Botão voltar clicado');

            // Ocultar janela de pagamento
            const janelaPagamento = document.getElementById('janela-pagamento-mercadopago');
            if (janelaPagamento) {
                janelaPagamento.classList.add('hidden');
                console.log('✅ Janela de pagamento ocultada');
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

    // ✅ Event listener para botão PIX
    const btnPix = document.getElementById('btn-pix-pagamento');
    if (btnPix && !btnPix.hasAttribute('data-listener-added')) {
        btnPix.setAttribute('data-listener-added', 'true');
        btnPix.addEventListener('click', async function (e) {
            e.preventDefault();

            console.log('✅ Botão PIX clicado');
            await gerarPixPagamento();
        });
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
        const inscricaoId = window.dadosInscricao ? .inscricaoId;
        if (!inscricaoId) {
            throw new Error('ID da inscrição não encontrado');
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
        console.log('📥 Resultado PIX:', result);

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

        mostrarErro('Erro ao gerar PIX: ' + error.message);

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

// ✅ Atualizar valor total
function updateTotalAmount() {
    const totalElement = document.getElementById('total-geral');
    if (totalElement) {
        const total = calcularTotal();
        totalElement.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;

        // Atualizar estado do botão quando total mudar
        atualizarBotaoPagamento();
    }
}

// ✅ Inicializar array global de produtos extras selecionados
// ✅ CORREÇÃO: Produtos extras estão em ficha.produtos_extras
window.produtosExtrasSelecionados = window.dadosInscricao ? .ficha ? .produtos_extras || window.dadosInscricao ? .produtosExtras || [];

// ✅ Função global para atualizar botão de pagamento (pode ser chamada de outros arquivos)
window.atualizarBotaoFinalizarCompra = atualizarBotaoPagamento;