/**
 * Utilitário: Cache Local
 * Gerencia cache no localStorage com expiração
 */

const CACHE_PREFIX = 'movamazon_';
const CACHE_EXPIRATION = 5 * 60 * 1000; // 5 minutos

/**
 * Salva dados no cache com timestamp
 * @param {string} key - Chave do cache
 * @param {*} data - Dados a serem salvos
 */
export function setCache(key, data) {
    try {
        const cacheData = {
            data: data,
            timestamp: Date.now()
        };
        localStorage.setItem(`${CACHE_PREFIX}${key}`, JSON.stringify(cacheData));
    } catch (error) {
        console.warn('Erro ao salvar no cache:', error);
    }
}

/**
 * Recupera dados do cache se ainda válidos
 * @param {string} key - Chave do cache
 * @returns {*|null} Dados do cache ou null se expirado/inexistente
 */
export function getCache(key) {
    try {
        const cached = localStorage.getItem(`${CACHE_PREFIX}${key}`);
        if (!cached) return null;

        const cacheData = JSON.parse(cached);
        const now = Date.now();
        
        if (now - cacheData.timestamp > CACHE_EXPIRATION) {
            localStorage.removeItem(`${CACHE_PREFIX}${key}`);
            return null;
        }

        return cacheData.data;
    } catch (error) {
        console.warn('Erro ao recuperar do cache:', error);
        return null;
    }
}

/**
 * Limpa um item específico do cache
 * @param {string} key - Chave do cache
 */
export function clearCache(key) {
    try {
        localStorage.removeItem(`${CACHE_PREFIX}${key}`);
    } catch (error) {
        console.warn('Erro ao limpar cache:', error);
    }
}

/**
 * Limpa todo o cache do MovAmazon
 */
export function clearAllCache() {
    try {
        const keys = Object.keys(localStorage);
        keys.forEach(key => {
            if (key.startsWith(CACHE_PREFIX)) {
                localStorage.removeItem(key);
            }
        });
    } catch (error) {
        console.warn('Erro ao limpar todo o cache:', error);
    }
}

