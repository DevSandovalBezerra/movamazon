if (window.getApiBase) { window.getApiBase(); }
/**
 * API: Eventos para Participante
 * Busca eventos públicos próximos para exibir no dashboard
 */

const API_BASE = (window.API_BASE || '/api') + '/evento';

/**
 * Busca eventos próximos (próximos 3 meses)
 * @param {number} limit - Limite de eventos a retornar (padrão: 5)
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
        console.log(' Chamando API de eventos:', url);
        
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        console.log(' Resposta HTTP eventos:', response.status, response.statusText);

        if (!response.ok) {
            const errorText = await response.text();
            console.error(' Erro HTTP eventos:', response.status, errorText);
            throw new Error(`Erro HTTP: ${response.status} - ${errorText}`);
        }

        const data = await response.json();
        console.log(' Dados de eventos recebidos:', data);
        
        if (!data.success) {
            throw new Error(data.message || 'Erro ao buscar eventos');
        }

        // Limitar quantidade e retornar apenas os próximos
        const eventos = (data.eventos || []).slice(0, limit);
        console.log(`... ${eventos.length} eventos processados (limite: ${limit})`);

        return {
            success: true,
            eventos: eventos
        };
    } catch (error) {
        console.error(' Erro ao buscar próximos eventos:', error);
        return {
            success: false,
            message: error.message || 'Erro ao conectar com o servidor',
            eventos: []
        };
    }
}

