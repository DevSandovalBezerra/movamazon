if (window.getApiBase) { window.getApiBase(); }
// UtilitÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio para buscar configuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o do Mercado Pago dinamicamente
// Remove necessidade de hardcode de public keys

// Garantir que API_BASE estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ definido
if (!window.API_BASE) {
    (function () {
        var path = window.location.pathname || '';
        var idx = path.indexOf('/frontend/');
        window.API_BASE = idx > 0 ? path.slice(0, idx) + '/api' : '/api';
    })();
}

// FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o para construir URLs usando API_BASE
function getApiUrl(endpoint) {
    const url = `${window.API_BASE}/${endpoint}`;
    return url;
}

// ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o para limpar cache do MercadoPago (ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºtil para debug)
function clearMercadoPagoCache() {
    sessionStorage.removeItem('mp_config');
    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Cache do MercadoPago limpo');
}

// Buscar configuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o do Mercado Pago do servidor
async function getMercadoPagoConfig() {
    // Verificar cache em sessionStorage
    const cached = sessionStorage.getItem('mp_config');
    if (cached) {
        try {
            const config = JSON.parse(cached);
            // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Verificar se cache tem public_key vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lida E estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ dentro do prazo
            if (config.public_key && config.public_key !== '' && Date.now() - config.timestamp < 3600000) {
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Usando configuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o do cache:', { 
                    environment: config.environment, 
                    has_key: !!config.public_key 
                });
                return config;
            } else {
                // Cache invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido ou public_key vazia - limpar
                console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾ Cache invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido ou expirado, buscando nova configuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o...');
                sessionStorage.removeItem('mp_config');
            }
        } catch (e) {
            // Cache invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido, limpar e continuar
            sessionStorage.removeItem('mp_config');
        }
    }
    
    // Buscar do servidor
    try {
        const url = getApiUrl('mercadolivre/get_public_key.php');
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Buscando configuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o em:', url);
        
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Resposta do servidor:', data);
        
        if (data.success) {
            // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Verificar se public_key estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ presente
            if (!data.public_key || data.public_key === '') {
                console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Servidor retornou public_key vazia!');
                console.error('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ Verifique no .env: APP_Public_Key ou APP_Public_Keyee');
                throw new Error('Public key nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o configurada no servidor. Verifique o arquivo .env');
            }
            
            const config = {
                public_key: data.public_key,
                environment: data.environment,
                is_production: data.is_production,
                has_valid_tokens: data.has_valid_tokens,
                timestamp: Date.now()
            };
            
            // Salvar no cache apenas se public_key vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lida
            sessionStorage.setItem('mp_config', JSON.stringify(config));
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ ConfiguraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o obtida com sucesso:', { 
                environment: config.environment, 
                has_key: !!config.public_key,
                has_valid_tokens: config.has_valid_tokens 
            });
            
            return config;
        } else {
            throw new Error(data.message || data.error || 'Resposta invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lida do servidor');
        }
    } catch (error) {
        // Limpar cache em caso de erro
        sessionStorage.removeItem('mp_config');
        console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦Ã¢â‚¬â„¢ Erro ao obter configuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o do Mercado Pago:', error);
        throw new Error('Erro ao obter configuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o do Mercado Pago: ' + error.message);
    }
}

// Expor funÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o de limpar cache globalmente (para debug)
window.clearMercadoPagoCache = clearMercadoPagoCache;

