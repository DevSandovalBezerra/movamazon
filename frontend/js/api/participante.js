if (window.getApiBase) { window.getApiBase(); }
/**
 * API: Participante
 * FunÃƒÂ§ÃƒÂµes para interagir com APIs do portal do participante
 */

const API_BASE = (window.API_BASE || '/api') + '/participante';

/**
 * Busca todas as inscriÃƒÂ§ÃƒÂµes do usuÃƒÂ¡rio logado
 * @returns {Promise<Object>} Objeto com success e inscricoes ou erro
 */
export async function getInscricoes() {
    try {
        const url = `${API_BASE}/get_inscricoes.php`;
        console.log('Ã°Å¸Å’Â Chamando API:', url);
        
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        console.log('Ã°Å¸â€œÂ¡ Resposta HTTP:', response.status, response.statusText);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Ã¢ÂÅ’ Erro HTTP:', response.status, errorText);
            
            if (response.status === 403) {
                throw new Error('Acesso negado. FaÃƒÂ§a login novamente.');
            }
            throw new Error(`Erro HTTP: ${response.status} - ${errorText}`);
        }

        const data = await response.json();
        console.log('Ã°Å¸â€œÂ¦ Dados recebidos:', data);
        
        if (!data.success) {
            throw new Error(data.message || 'Erro ao buscar inscriÃƒÂ§ÃƒÂµes');
        }

        return {
            success: true,
            inscricoes: data.inscricoes || []
        };
    } catch (error) {
        console.error('Ã¢ÂÅ’ Erro ao buscar inscriÃƒÂ§ÃƒÂµes:', error);
        return {
            success: false,
            message: error.message || 'Erro ao conectar com o servidor',
            inscricoes: []
        };
    }
}

/**
 * Busca dados completos de uma inscriÃƒÂ§ÃƒÂ£o especÃƒÂ­fica
 * @param {number} inscricaoId - ID da inscriÃƒÂ§ÃƒÂ£o
 * @returns {Promise<Object>} Objeto com success e inscricao ou erro
 */
export async function getInscricao(inscricaoId) {
    try {
        const response = await fetch(`${API_BASE}/get_inscricao.php?inscricao_id=${inscricaoId}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            if (response.status === 403) {
                throw new Error('Acesso negado.');
            }
            if (response.status === 404) {
                throw new Error('InscriÃƒÂ§ÃƒÂ£o nÃƒÂ£o encontrada.');
            }
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Erro ao buscar inscriÃƒÂ§ÃƒÂ£o');
        }

        return {
            success: true,
            inscricao: data.inscricao
        };
    } catch (error) {
        console.error('Erro ao buscar inscriÃƒÂ§ÃƒÂ£o:', error);
        return {
            success: false,
            message: error.message || 'Erro ao conectar com o servidor'
        };
    }
}

/**
 * Gera URL do QR Code para uma inscriÃƒÂ§ÃƒÂ£o
 * @param {string} numeroInscricao - NÃƒÂºmero da inscriÃƒÂ§ÃƒÂ£o
 * @returns {string} URL do QR Code
 */
export function getQRCodeUrl(numeroInscricao) {
    return `${API_BASE}/generate_qr.php?data=${encodeURIComponent(numeroInscricao)}`;
}

