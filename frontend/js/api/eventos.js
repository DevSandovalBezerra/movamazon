/**
 * API: Eventos
 * Funções para carregar e gerenciar eventos
 */

import { atualizarContadorEventos } from '../ui/counter.js';
import { renderizarCard } from '../components/eventCard.js';

/**
 * Carrega eventos com filtros de cidade e período
 * @param {string} cidade - Nome da cidade (opcional)
 * @param {string} mesAnoDe - Mês/ano inicial (opcional)
 * @param {string} mesAnoAte - Mês/ano final (opcional)
 */
export async function carregarEventos(cidade = '', mesAnoDe = '', mesAnoAte = '') {
    console.log('🔍 Filtros aplicados:', {
        cidade,
        mesAnoDe,
        mesAnoAte
    });

    // Mostrar spinner de carregamento
    atualizarContadorEventos('carregando');

    let url = '../../../api/evento/list_public.php';
    const params = [];
    if (cidade) params.push('cidade=' + encodeURIComponent(cidade));
    if (mesAnoDe) params.push('mes_ano_de=' + encodeURIComponent(mesAnoDe));
    if (mesAnoAte) params.push('mes_ano_ate=' + encodeURIComponent(mesAnoAte));
    if (params.length) url += '?' + params.join('&');

    console.log('🌐 URL da requisição:', url);

    try {
        const response = await fetch(url);
        console.log('📡 Resposta da API de eventos:', response.status);
        
        const data = await response.json();
        console.log('📊 Dados de eventos recebidos:', data);
        
        const container = document.getElementById('eventos-dinamicos');
        if (!container) {
            console.error('❌ Container de eventos não encontrado');
            atualizarContadorEventos('erro');
            return;
        }
        container.innerHTML = '';

        if (!data.success || !data.eventos || data.eventos.length === 0) {
            console.log('⚠️ Nenhum evento encontrado');
            atualizarContadorEventos('vazio');
            return;
        }

        console.log(`✅ ${data.eventos.length} eventos encontrados`);
        atualizarContadorEventos('sucesso', data.eventos);

        data.eventos.forEach((evento, index) => {
            const card = renderizarCard(evento, index);
            container.appendChild(card);
        });
    } catch (error) {
        console.error('💥 Erro ao carregar eventos:', error);
        atualizarContadorEventos('erro');
    }
}


