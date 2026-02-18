
/**
 * API: Eventos
 * FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes para carregar e gerenciar eventos
 */

import { atualizarContadorEventos } from '../ui/counter.js';
import { renderizarCard } from '../components/eventCard.js';
if (window.getApiBase) { window.getApiBase(); }

/**
 * Carrega eventos com filtros de cidade e perÃƒÆ’Ã‚Â­odo
 * @param {string} cidade - Nome da cidade (opcional)
 * @param {string} mesAnoDe - MÃƒÆ’Ã‚Âªs/ano inicial (opcional)
 * @param {string} mesAnoAte - MÃƒÆ’Ã‚Âªs/ano final (opcional)
 */
export async function carregarEventos(cidade = '', mesAnoDe = '', mesAnoAte = '') {
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Â Filtros aplicados:', {
        cidade,
        mesAnoDe,
        mesAnoAte
    });

    // Mostrar spinner de carregamento
    atualizarContadorEventos('carregando');

    let url = (window.API_BASE || '/api') + '/evento/list_public.php';
    const params = [];
    if (cidade) params.push('cidade=' + encodeURIComponent(cidade));
    if (mesAnoDe) params.push('mes_ano_de=' + encodeURIComponent(mesAnoDe));
    if (mesAnoAte) params.push('mes_ano_ate=' + encodeURIComponent(mesAnoAte));
    if (params.length) url += '?' + params.join('&');

    console.log('ÃƒÂ°Ã…Â¸Ã…â€™Ã‚Â URL da requisiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o:', url);

    try {
        const response = await fetch(url);
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ Resposta da API de eventos:', response.status);
        
        const data = await response.json();
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã…Â  Dados de eventos recebidos:', data);
        
        const container = document.getElementById('eventos-dinamicos');
        if (!container) {
            console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ Container de eventos nÃƒÆ’Ã‚Â£o encontrado');
            atualizarContadorEventos('erro');
            return;
        }
        container.innerHTML = '';

        if (!data.success || !data.eventos || data.eventos.length === 0) {
            console.log('ÃƒÂ¢Ã…Â¡Ã‚Â ÃƒÂ¯Ã‚Â¸Ã‚Â Nenhum evento encontrado');
            atualizarContadorEventos('vazio');
            return;
        }

        console.log(`ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ ${data.eventos.length} eventos encontrados`);
        atualizarContadorEventos('sucesso', data.eventos);

        data.eventos.forEach((evento, index) => {
            const card = renderizarCard(evento, index);
            container.appendChild(card);
        });
    } catch (error) {
        console.error('ÃƒÂ°Ã…Â¸Ã¢â‚¬â„¢Ã‚Â¥ Erro ao carregar eventos:', error);
        atualizarContadorEventos('erro');
    }
}


