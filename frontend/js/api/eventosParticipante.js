if (window.getApiBase) { window.getApiBase(); }
/**
 * API: Eventos para Participante
 * Busca eventos pÃƒÂºblicos prÃƒÂ³ximos para exibir no dashboard
 */

const API_BASE = (window.API_BASE || '/api') + '/evento';

/**
 * Busca eventos prÃƒÂ³ximos (prÃƒÂ³ximos 3 meses)
 * @param {number} limit - Limite de eventos a retornar (padrÃƒÂ£o: 5)
 * @returns {Promise<Object>} Objeto com success e eventos ou erro
 */
export async function getProximosEventos(limit = 5) {
    try {
        const hoje = new Date();
        const tresMesesDepois = new Date();
        tresMesesDepois.setMonth(hoje.getMonth() + 3);

        const dataInicio = hoje.toISOString().split('T')[0];
        const dataFim = tresMesesDepois.toISOString().split('T')[0];

        const url = `${API_BASE}/list_public.php?data_realizacao_de=${dataInicio}&data_realizacao_ate=${dataFim}`;
        console.log('Ã°Å¸Å’Â Chamando API de eventos:', url);
        
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        console.log('Ã°Å¸â€œÂ¡ Resposta HTTP eventos:', response.status, response.statusText);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('âÂÅ’ Erro HTTP eventos:', response.status, errorText);
            throw new Error(`Erro HTTP: ${response.status} - ${errorText}`);
        }

        const data = await response.json();
        console.log('Ã°Å¸â€œÂ¦ Dados de eventos recebidos:', data);
        
        if (!data.success) {
            throw new Error(data.message || 'Erro ao buscar eventos');
        }

        // Limitar quantidade e retornar apenas os prÃƒÂ³ximos
        const eventos = (data.eventos || []).slice(0, limit);
        console.log(`âÅ“â€¦ ${eventos.length} eventos processados (limite: ${limit})`);

        return {
            success: true,
            eventos: eventos
        };
    } catch (error) {
        console.error('âÂÅ’ Erro ao buscar prÃƒÂ³ximos eventos:', error);
        return {
            success: false,
            message: error.message || 'Erro ao conectar com o servidor',
            eventos: []
        };
    }
}

