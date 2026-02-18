if (window.getApiBase) { window.getApiBase(); }
// DEBUG DETALHADO - Identificar Loop Real
console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â SCRIPT CARREGADO - InÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­cio');

// ConfiguraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o do Mercado Pago
const mp = new MercadoPago('TEST-08778670-bce3-4b7f-9641-be7d9103032e');
const bricksBuilder = mp.bricks();

console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â MERCADO PAGO INICIALIZADO');

// Base dinÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢mico para APIs
if (!window.API_BASE) {
  (function() {
    var path = window.location.pathname || '';
    var idx = path.indexOf('/frontend/');
    window.API_BASE = idx > 0 ? path.slice(0, idx) + '/api' : '/api';
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â API_BASE definido:', window.API_BASE);
  })();
}

function getApiUrl(endpoint) {
    const url = `${window.API_BASE}/${endpoint}`;
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â getApiUrl:', url);
    return url;
}

// FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o para log
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
    log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€šÃ‚Â§Ãƒâ€šÃ‚Â® CALCULANDO TOTAL...');
    
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
    log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â RENDERIZANDO RESUMO...');
    
    const container = document.getElementById('resumo-compra');
    if (!container) {
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Container resumo-compra nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado');
        return;
    }
    
    log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Container encontrado');
    
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
    log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Resumo renderizado');
}

// Atualizar valor total
function updateTotalAmount() {
    log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢Ãƒâ€šÃ‚Â° ATUALIZANDO VALOR TOTAL...');
    
    const totalElement = document.getElementById('total-geral');
    if (!totalElement) {
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Elemento total-geral nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado');
        return;
    }
    
    const total = calcularTotal();
    totalElement.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
    
    log(`ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Valor total atualizado: R$ ${total.toFixed(2).replace('.', ',')}`);
}

// Setup de event listeners
function setupEventListeners() {
    log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â½Ãƒâ€šÃ‚Â¯ CONFIGURANDO EVENT LISTENERS...');
    
    const btnPagar = document.getElementById('btn-finalizar-compra');
    if (!btnPagar) {
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o btn-finalizar-compra nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado');
        return;
    }
    
    log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado');
    
    if (btnPagar.hasAttribute('data-listener-added')) {
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Event listener jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ foi adicionado');
        return;
    }
    
    btnPagar.setAttribute('data-listener-added', 'true');
    log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Event listener adicionado');
    
    btnPagar.addEventListener('click', async function(e) {
        e.preventDefault();
        log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“Ãƒâ€šÃ‚Â±ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â BOTÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O CLICADO - Iniciando pagamento...');
        
        // Mostrar container do formulÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
        const container = document.getElementById('formulario-mercadopago');
        if (container) {
            container.classList.remove('hidden');
            log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Container do formulÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio mostrado');
        } else {
            log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Container do formulÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado');
        }
        
        // Verificar se jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ foi inicializado
        if (window.paymentInitialized) {
            log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Pagamento jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ foi inicializado');
            return;
        }
        
        window.paymentInitialized = true;
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Flag de inicializaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o definida');
        
        // Inicializar pagamento
        await inicializarPagamento();
    });
}

// Inicializar pagamento
async function inicializarPagamento() {
    log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â¡ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ INICIALIZANDO PAGAMENTO...');
    
    if (window.paymentBrickController) {
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Payment Brick jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ foi inicializado');
        return;
    }
    
    try {
        const total = calcularTotal();
        
        if (total <= 0) {
            throw new Error('Valor total invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido');
        }
        
        log(`ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Valor total vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido: R$ ${total}`);
        
        // Criar prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o se necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
        let inscricaoId = window.dadosInscricao?.inscricaoId;
        if (!inscricaoId) {
            log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â Criando prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o...');
            inscricaoId = await criarPreInscricao(total);
        } else {
            log(`ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ InscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o ID jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ existe: ${inscricaoId}`);
        }
        
        // Criar preference
        log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â½Ãƒâ€šÃ‚Â¯ Criando preference...');
        const preferenceId = await criarPreference(inscricaoId, total);
        
        // Configurar elementos HTML necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rios
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Configurando elementos HTML...');
        configurarElementosHTML(total, preferenceId);
        
        // Renderizar o Brick
        log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â½Ãƒâ€šÃ‚Â¨ Renderizando Payment Brick...');
        await renderPaymentBrick(bricksBuilder);
        
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Payment Brick renderizado com sucesso!');
        
    } catch (error) {
        log(`ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro ao inicializar pagamento: ${error.message}`);
        console.error('Erro detalhado:', error);
    }
}

// Criar prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
async function criarPreInscricao(total) {
    log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â CRIANDO PRÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°-INSCRIÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O...');
    
    const payload = montarPayloadPreInscricao(total);
    log('Payload prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o: ' + JSON.stringify(payload, null, 2));
    
    const response = await fetch(getApiUrl('inscricao/precreate.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    const result = await response.json();
    log('Resposta prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o: ' + JSON.stringify(result, null, 2));
    
    if (!result?.success) {
        throw new Error(result?.message || 'Falha ao preparar inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o');
    }
    
    const inscricaoId = result.inscricao_id;
    if (!window.dadosInscricao) window.dadosInscricao = {};
    window.dadosInscricao.inscricaoId = inscricaoId;
    
    log(`ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ PrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o criada: ${inscricaoId}`);
    return inscricaoId;
}

// Criar preference
async function criarPreference(inscricaoId, total) {
    log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â½Ãƒâ€šÃ‚Â¯ CRIANDO PREFERENCE...');
    
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
        throw new Error(result?.error || 'Falha ao criar preferÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia');
    }
    
    log(`ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Preference criada: ${result.preference_id}`);
    return result.preference_id;
}

// Configurar elementos HTML necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rios
function configurarElementosHTML(total, preferenceId) {
    log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â CONFIGURANDO ELEMENTOS HTML...');
    
    // Criar ou atualizar elementos necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rios
    let valorElement = document.getElementById('valor_payment');
    if (!valorElement) {
        valorElement = document.createElement('input');
        valorElement.type = 'hidden';
        valorElement.id = 'valor_payment';
        document.body.appendChild(valorElement);
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Elemento valor_payment criado');
    }
    valorElement.value = total;
    
    let preferenceElement = document.getElementById('preference_id');
    if (!preferenceElement) {
        preferenceElement = document.createElement('input');
        preferenceElement.type = 'hidden';
        preferenceElement.id = 'preference_id';
        document.body.appendChild(preferenceElement);
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Elemento preference_id criado');
    }
    preferenceElement.value = preferenceId;
    
    let usePreferenceElement = document.getElementById('use_preference_id');
    if (!usePreferenceElement) {
        usePreferenceElement = document.createElement('input');
        usePreferenceElement.type = 'hidden';
        usePreferenceElement.id = 'use_preference_id';
        document.body.appendChild(usePreferenceElement);
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Elemento use_preference_id criado');
    }
    usePreferenceElement.value = 'true';
    
    // Atualizar display do valor
    const valorDisplay = document.getElementById('valor-display');
    if (valorDisplay) {
        valorDisplay.textContent = total.toFixed(2).replace('.', ',');
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Display do valor atualizado');
    }
    
    log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Elementos HTML configurados');
}

// Montar payload para prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
function montarPayloadPreInscricao(total) {
    log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¦ MONTANDO PAYLOAD PRÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°-INSCRIÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O...');
    
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
    
    log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Payload prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©-inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o montado');
    return payload;
}

// Montar payload para criar preference
function montarPayloadCreatePreference(inscricaoId, total) {
    log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¦ MONTANDO PAYLOAD PREFERENCE...');
    
    const modalidade = window.dadosInscricao?.modalidades?.[0] || {};
    const produtosExtras = window.dadosInscricao?.produtosExtras || [];
    
    const payload = {
        inscricao_id: inscricaoId,
        modalidade_nome: modalidade.nome || 'InscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o',
        lote_numero: modalidade.lote_numero || null,
        valor_total: total,
        evento_nome: window.dadosInscricao?.evento?.nome || 'Evento',
        kit_nome: modalidade.kit_nome || null,
        produtos_extras: produtosExtras,
        cupom: window.dadosInscricao?.cupomAplicado || null,
        valor_desconto: window.dadosInscricao?.valorDesconto || 0,
        seguro: 0
    };
    
    log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Payload preference montado');
    return payload;
}

// Renderizar Payment Brick
const renderPaymentBrick = async (bricksBuilder) => {
    log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â½Ãƒâ€šÃ‚Â¨ RENDERIZANDO PAYMENT BRICK...');
    
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
          log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Payment Brick pronto');
        },
        onSubmit: ({ selectedPaymentMethod, formData }) => {
          log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“Ãƒâ€šÃ‚Â±ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Submetendo pagamento...');
          log(`MÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©todo selecionado: ${selectedPaymentMethod}`);
          
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
                  log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Pagamento processado com sucesso');
                  // Aqui vocÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âª pode adicionar lÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³gica para mostrar status
                } else {
                  log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Falha no pagamento');
                  reject(new Error(response?.error || 'Payment failed'));
                }
                
                resolve();
              })
              .catch((error) => {
                log(`ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro no pagamento: ${error.message}`);
                reject(error);
              });
          });
        },
        onError: (error) => {
          log(`ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro no Brick: ${error.message}`);
        },
      },
    };
    
    window.paymentBrickController = await bricksBuilder.create(
      "payment",
      "paymentBrick_container",
      settings
    );
    
    log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Payment Brick criado');
};

// Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾ DOM CARREGADO - Iniciando...');
    
    try {
        // Verificar se dados estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­veis
        if (!window.dadosInscricao) {
            log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Dados de inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrados');
            return;
        }
        
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Dados de inscriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrados');
        
        // Inicializar apenas o resumo e event listeners
        renderizarResumoCompra();
        updateTotalAmount();
        setupEventListeners();
        
        log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ InicializaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o concluÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­da');
        
    } catch (error) {
        log(`ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro na inicializaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o: ${error.message}`);
        console.error('Erro detalhado:', error);
    }
});

// Inicializar array global de produtos extras selecionados
window.produtosExtrasSelecionados = window.dadosInscricao?.produtosExtras || [];

log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â SCRIPT CARREGADO - Fim');
