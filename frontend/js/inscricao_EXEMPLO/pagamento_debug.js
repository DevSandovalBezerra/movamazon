// DEBUG DETALHADO - Identificar Loop Real
console.log('🔍 SCRIPT CARREGADO - Início');

// Configuração do Mercado Pago
const mp = new MercadoPago('TEST-08778670-bce3-4b7f-9641-be7d9103032e');
const bricksBuilder = mp.bricks();

console.log('🔍 MERCADO PAGO INICIALIZADO');

// Base dinâmico para APIs
if (!window.API_BASE) {
  (function() {
    var path = window.location.pathname || '';
    var idx = path.indexOf('/frontend/');
    window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
    console.log('🔍 API_BASE definido:', window.API_BASE);
  })();
}

function getApiUrl(endpoint) {
    const url = `${window.API_BASE}/api/${endpoint}`;
    console.log('🔍 getApiUrl:', url);
    return url;
}

// Função para log
function log(message) {
    const timestamp = new Date().toLocaleTimeString();
    const logMessage = `[${timestamp}] ${message}`;
    console.log(logMessage);
    
    // Tentar adicionar ao log visual se existir
    const logDiv = document.getElementById('debug-log');
    if (logDiv) {
        logDiv.innerHTML += logMessage + '<br>';
        logDiv.scrollTop = logDiv.scrollHeight;
    }
}

// Calcular total
function calcularTotal() {
    log('🧮 CALCULANDO TOTAL...');
    
    const modalidades = window.dadosInscricao?.modalidades || [];
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];
    const valorDesconto = window.dadosInscricao?.valorDesconto || 0;
    
    log(`Modalidades encontradas: ${modalidades.length}`);
    log(`Produtos extras encontrados: ${produtosExtras.length}`);
    log(`Valor desconto: ${valorDesconto}`);
    
    let total = 0;
    
    // Somar modalidades
    modalidades.forEach((modalidade, index) => {
        const valor = parseFloat(modalidade.preco_total || 0);
        total += valor;
        log(`Modalidade ${index + 1}: ${modalidade.nome} - R$ ${valor}`);
    });
    
    // Somar produtos extras
    produtosExtras.forEach((produto, index) => {
        const valor = parseFloat(produto.valor || 0);
        total += valor;
        log(`Produto ${index + 1}: ${produto.nome} - R$ ${valor}`);
    });
    
    // Aplicar desconto
    total -= parseFloat(valorDesconto);
    
    log(`TOTAL CALCULADO: R$ ${total}`);
    return Math.max(0, total);
}

// Renderizar resumo da compra
function renderizarResumoCompra() {
    log('📝 RENDERIZANDO RESUMO...');
    
    const container = document.getElementById('resumo-compra');
    if (!container) {
        log('❌ Container resumo-compra não encontrado');
        return;
    }
    
    log('✅ Container encontrado');
    
    const modalidades = window.dadosInscricao?.modalidades || [];
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];
    
    log(`Modalidades para renderizar: ${modalidades.length}`);
    log(`Produtos extras para renderizar: ${produtosExtras.length}`);
    
    let html = '';
    
    // Modalidades
    modalidades.forEach((modalidade, index) => {
        log(`Renderizando modalidade ${index + 1}: ${modalidade.nome}`);
        html += `
            <div class="flex justify-between py-2">
                <span>${modalidade.nome || 'Modalidade'}</span>
                <span class="font-semibold">R$ ${parseFloat(modalidade.preco_total || 0).toFixed(2).replace('.', ',')}</span>
            </div>
        `;
    });
    
    // Produtos extras
    produtosExtras.forEach((produto, index) => {
        log(`Renderizando produto ${index + 1}: ${produto.nome}`);
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
        log(`Renderizando desconto: R$ ${valorDesconto}`);
        html += `
            <div class="flex justify-between py-2">
                <span class="text-red-600">- Desconto</span>
                <span class="font-semibold text-red-600">R$ ${parseFloat(valorDesconto).toFixed(2).replace('.', ',')}</span>
            </div>
        `;
    }
    
    container.innerHTML = html;
    log('✅ Resumo renderizado');
}

// Atualizar valor total
function updateTotalAmount() {
    log('💰 ATUALIZANDO VALOR TOTAL...');
    
    const totalElement = document.getElementById('total-geral');
    if (!totalElement) {
        log('❌ Elemento total-geral não encontrado');
        return;
    }
    
    const total = calcularTotal();
    totalElement.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
    
    log(`✅ Valor total atualizado: R$ ${total.toFixed(2).replace('.', ',')}`);
}

// Setup de event listeners
function setupEventListeners() {
    log('🎯 CONFIGURANDO EVENT LISTENERS...');
    
    const btnPagar = document.getElementById('btn-finalizar-compra');
    if (!btnPagar) {
        log('❌ Botão btn-finalizar-compra não encontrado');
        return;
    }
    
    log('✅ Botão encontrado');
    
    if (btnPagar.hasAttribute('data-listener-added')) {
        log('⚠️ Event listener já foi adicionado');
        return;
    }
    
    btnPagar.setAttribute('data-listener-added', 'true');
    log('✅ Event listener adicionado');
    
    btnPagar.addEventListener('click', async function(e) {
        e.preventDefault();
        log('🖱️ BOTÃO CLICADO - Iniciando pagamento...');
        
        // Mostrar container do formulário
        const container = document.getElementById('formulario-mercadopago');
        if (container) {
            container.classList.remove('hidden');
            log('✅ Container do formulário mostrado');
        } else {
            log('❌ Container do formulário não encontrado');
        }
        
        // Verificar se já foi inicializado
        if (window.paymentInitialized) {
            log('⚠️ Pagamento já foi inicializado');
            return;
        }
        
        window.paymentInitialized = true;
        log('✅ Flag de inicialização definida');
        
        // Inicializar pagamento
        await inicializarPagamento();
    });
}

// Inicializar pagamento
async function inicializarPagamento() {
    log('🚀 INICIALIZANDO PAGAMENTO...');
    
    if (window.paymentBrickController) {
        log('⚠️ Payment Brick já foi inicializado');
        return;
    }
    
    try {
        const total = calcularTotal();
        
        if (total <= 0) {
            throw new Error('Valor total inválido');
        }
        
        log(`✅ Valor total válido: R$ ${total}`);
        
        // Criar pré-inscrição se necessário
        let inscricaoId = window.dadosInscricao?.inscricaoId;
        if (!inscricaoId) {
            log('📝 Criando pré-inscrição...');
            inscricaoId = await criarPreInscricao(total);
        } else {
            log(`✅ Inscrição ID já existe: ${inscricaoId}`);
        }
        
        // Criar preference
        log('🎯 Criando preference...');
        const preferenceId = await criarPreference(inscricaoId, total);
        
        // Configurar elementos HTML necessários
        log('⚙️ Configurando elementos HTML...');
        configurarElementosHTML(total, preferenceId);
        
        // Renderizar o Brick
        log('🎨 Renderizando Payment Brick...');
        await renderPaymentBrick(bricksBuilder);
        
        log('✅ Payment Brick renderizado com sucesso!');
        
    } catch (error) {
        log(`❌ Erro ao inicializar pagamento: ${error.message}`);
        console.error('Erro detalhado:', error);
    }
}

// Criar pré-inscrição
async function criarPreInscricao(total) {
    log('📝 CRIANDO PRÉ-INSCRIÇÃO...');
    
    const payload = montarPayloadPreInscricao(total);
    log('Payload pré-inscrição: ' + JSON.stringify(payload, null, 2));
    
    const response = await fetch(getApiUrl('inscricao/precreate.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    const result = await response.json();
    log('Resposta pré-inscrição: ' + JSON.stringify(result, null, 2));
    
    if (!result?.success) {
        throw new Error(result?.message || 'Falha ao preparar inscrição');
    }
    
    const inscricaoId = result.inscricao_id;
    if (!window.dadosInscricao) window.dadosInscricao = {};
    window.dadosInscricao.inscricaoId = inscricaoId;
    
    log(`✅ Pré-inscrição criada: ${inscricaoId}`);
    return inscricaoId;
}

// Criar preference
async function criarPreference(inscricaoId, total) {
    log('🎯 CRIANDO PREFERENCE...');
    
    const payload = montarPayloadCreatePreference(inscricaoId, total);
    log('Payload preference: ' + JSON.stringify(payload, null, 2));
    
    const response = await fetch(getApiUrl('inscricao/create_preference.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    const result = await response.json();
    log('Resposta preference: ' + JSON.stringify(result, null, 2));
    
    if (!result?.success || !result?.preference_id) {
        throw new Error(result?.error || 'Falha ao criar preferência');
    }
    
    log(`✅ Preference criada: ${result.preference_id}`);
    return result.preference_id;
}

// Configurar elementos HTML necessários
function configurarElementosHTML(total, preferenceId) {
    log('⚙️ CONFIGURANDO ELEMENTOS HTML...');
    
    // Criar ou atualizar elementos necessários
    let valorElement = document.getElementById('valor_payment');
    if (!valorElement) {
        valorElement = document.createElement('input');
        valorElement.type = 'hidden';
        valorElement.id = 'valor_payment';
        document.body.appendChild(valorElement);
        log('✅ Elemento valor_payment criado');
    }
    valorElement.value = total;
    
    let preferenceElement = document.getElementById('preference_id');
    if (!preferenceElement) {
        preferenceElement = document.createElement('input');
        preferenceElement.type = 'hidden';
        preferenceElement.id = 'preference_id';
        document.body.appendChild(preferenceElement);
        log('✅ Elemento preference_id criado');
    }
    preferenceElement.value = preferenceId;
    
    let usePreferenceElement = document.getElementById('use_preference_id');
    if (!usePreferenceElement) {
        usePreferenceElement = document.createElement('input');
        usePreferenceElement.type = 'hidden';
        usePreferenceElement.id = 'use_preference_id';
        document.body.appendChild(usePreferenceElement);
        log('✅ Elemento use_preference_id criado');
    }
    usePreferenceElement.value = 'true';
    
    // Atualizar display do valor
    const valorDisplay = document.getElementById('valor-display');
    if (valorDisplay) {
        valorDisplay.textContent = total.toFixed(2).replace('.', ',');
        log('✅ Display do valor atualizado');
    }
    
    log('✅ Elementos HTML configurados');
}

// Montar payload para pré-inscrição
function montarPayloadPreInscricao(total) {
    log('📦 MONTANDO PAYLOAD PRÉ-INSCRIÇÃO...');
    
    const modalidade = window.dadosInscricao?.modalidades?.[0] || {};
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];
    
    const payload = {
        evento_id: window.dadosInscricao?.eventoId || 1,
        modalidades: window.dadosInscricao?.modalidades || [],
        produtos_extras: produtosExtras,
        ficha: window.dadosInscricao?.ficha || {},
        valor_total: total,
        cupom: window.dadosInscricao?.cupomAplicado || null,
        valor_desconto: window.dadosInscricao?.valorDesconto || 0
    };
    
    log('✅ Payload pré-inscrição montado');
    return payload;
}

// Montar payload para criar preference
function montarPayloadCreatePreference(inscricaoId, total) {
    log('📦 MONTANDO PAYLOAD PREFERENCE...');
    
    const modalidade = window.dadosInscricao?.modalidades?.[0] || {};
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];
    
    const payload = {
        inscricao_id: inscricaoId,
        modalidade_nome: modalidade.nome || 'Inscrição',
        lote_numero: modalidade.lote_numero || null,
        valor_total: total,
        evento_nome: window.dadosInscricao?.evento?.nome || 'Evento',
        kit_nome: modalidade.kit_nome || null,
        produtos_extras: produtosExtras,
        cupom: window.dadosInscricao?.cupomAplicado || null,
        valor_desconto: window.dadosInscricao?.valorDesconto || 0,
        seguro: 0
    };
    
    log('✅ Payload preference montado');
    return payload;
}

// Renderizar Payment Brick
const renderPaymentBrick = async (bricksBuilder) => {
    log('🎨 RENDERIZANDO PAYMENT BRICK...');
    
    const amount = parseFloat(document.getElementById('valor_payment').value);
    const preferenceId = document.getElementById('preference_id').value;
    const usePreferenceId = document.getElementById('use_preference_id').value === 'true';
    
    log(`Amount: ${amount}`);
    log(`Preference ID: ${preferenceId}`);
    log(`Use Preference ID: ${usePreferenceId}`);
    
    const settings = {
      initialization: usePreferenceId ? {
        amount: amount,
        preferenceId: preferenceId,
      } : {
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
          log('✅ Payment Brick pronto');
        },
        onSubmit: ({ selectedPaymentMethod, formData }) => {
          log('🖱️ Submetendo pagamento...');
          log(`Método selecionado: ${selectedPaymentMethod}`);
          
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
                log('Resposta do pagamento: ' + JSON.stringify(response, null, 2));
                
                if (response && response.success && response.id) {
                  log('✅ Pagamento processado com sucesso');
                  // Aqui você pode adicionar lógica para mostrar status
                } else {
                  log('❌ Falha no pagamento');
                  reject(new Error(response?.error || 'Payment failed'));
                }
                
                resolve();
              })
              .catch((error) => {
                log(`❌ Erro no pagamento: ${error.message}`);
                reject(error);
              });
          });
        },
        onError: (error) => {
          log(`❌ Erro no Brick: ${error.message}`);
        },
      },
    };
    
    window.paymentBrickController = await bricksBuilder.create(
      "payment",
      "paymentBrick_container",
      settings
    );
    
    log('✅ Payment Brick criado');
};

// Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    log('📄 DOM CARREGADO - Iniciando...');
    
    try {
        // Verificar se dados estão disponíveis
        if (!window.dadosInscricao) {
            log('❌ Dados de inscrição não encontrados');
            return;
        }
        
        log('✅ Dados de inscrição encontrados');
        
        // Inicializar apenas o resumo e event listeners
        renderizarResumoCompra();
        updateTotalAmount();
        setupEventListeners();
        
        log('✅ Inicialização concluída');
        
    } catch (error) {
        log(`❌ Erro na inicialização: ${error.message}`);
        console.error('Erro detalhado:', error);
    }
});

// Inicializar array global de produtos extras selecionados
window.produtosExtrasSelecionados = window.dadosInscricao?.produtosExtras || [];

log('🔍 SCRIPT CARREGADO - Fim');
