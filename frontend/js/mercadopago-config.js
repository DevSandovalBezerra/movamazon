// Utilitário para buscar configuração do Mercado Pago dinamicamente
// Remove necessidade de hardcode de public keys

// Garantir que API_BASE está definido
if (!window.API_BASE) {
    (function () {
        var path = window.location.pathname || '';
        var idx = path.indexOf('/frontend/');
        window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
    })();
}

// Função para construir URLs usando API_BASE
function getApiUrl(endpoint) {
    const url = `${window.API_BASE}/api/${endpoint}`;
    return url;
}

// ✅ Função para limpar cache do MercadoPago (útil para debug)
function clearMercadoPagoCache() {
    sessionStorage.removeItem('mp_config');
    console.log('✅ Cache do MercadoPago limpo');
}

// Buscar configuração do Mercado Pago do servidor
async function getMercadoPagoConfig() {
    // Verificar cache em sessionStorage
    const cached = sessionStorage.getItem('mp_config');
    if (cached) {
        try {
            const config = JSON.parse(cached);
            // ✅ Verificar se cache tem public_key válida E está dentro do prazo
            if (config.public_key && config.public_key !== '' && Date.now() - config.timestamp < 3600000) {
                console.log('✅ Usando configuração do cache:', { 
                    environment: config.environment, 
                    has_key: !!config.public_key 
                });
                return config;
            } else {
                // Cache inválido ou public_key vazia - limpar
                console.log('🔄 Cache inválido ou expirado, buscando nova configuração...');
                sessionStorage.removeItem('mp_config');
            }
        } catch (e) {
            // Cache inválido, limpar e continuar
            sessionStorage.removeItem('mp_config');
        }
    }
    
    // Buscar do servidor
    try {
        const url = getApiUrl('mercadolivre/get_public_key.php');
        console.log('🔍 Buscando configuração em:', url);
        
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📥 Resposta do servidor:', data);
        
        if (data.success) {
            // ✅ Verificar se public_key está presente
            if (!data.public_key || data.public_key === '') {
                console.error('❌ Servidor retornou public_key vazia!');
                console.error('📋 Verifique no .env: APP_Public_Key ou APP_Public_Keyee');
                throw new Error('Public key não configurada no servidor. Verifique o arquivo .env');
            }
            
            const config = {
                public_key: data.public_key,
                environment: data.environment,
                is_production: data.is_production,
                has_valid_tokens: data.has_valid_tokens,
                timestamp: Date.now()
            };
            
            // Salvar no cache apenas se public_key válida
            sessionStorage.setItem('mp_config', JSON.stringify(config));
            console.log('✅ Configuração obtida com sucesso:', { 
                environment: config.environment, 
                has_key: !!config.public_key,
                has_valid_tokens: config.has_valid_tokens 
            });
            
            return config;
        } else {
            throw new Error(data.message || data.error || 'Resposta inválida do servidor');
        }
    } catch (error) {
        // Limpar cache em caso de erro
        sessionStorage.removeItem('mp_config');
        console.error('❌ Erro ao obter configuração do Mercado Pago:', error);
        throw new Error('Erro ao obter configuração do Mercado Pago: ' + error.message);
    }
}

// Expor função de limpar cache globalmente (para debug)
window.clearMercadoPagoCache = clearMercadoPagoCache;

