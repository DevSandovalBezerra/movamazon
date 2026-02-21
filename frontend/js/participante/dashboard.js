
/**
 * Dashboard do Participante
 * Gerencia o carregamento e exibiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de dados do dashboard
 */

import { getInscricoes } from '../api/participante.js';
import { getProximosEventos } from '../api/eventosParticipante.js';
import { getCache, setCache } from '../utils/cache.js';
if (window.getApiBase) { window.getApiBase(); }

const CACHE_KEY_INSCRICOES = 'dashboard_inscricoes';
const CACHE_KEY_EVENTOS = 'dashboard_eventos';

/**
 * Formata data para exibiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
 * @param {string} dateString - Data no formato YYYY-MM-DD
 * @returns {string} Data formatada como DD/MM/YYYY
 */
function formatarData(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString + 'T00:00:00');
    return date.toLocaleDateString('pt-BR');
}

/**
 * Formata status de pagamento para exibiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
 * @param {string} status - Status do pagamento
 * @returns {string} Status formatado
 */
function formatarStatusPagamento(status) {
    const statusMap = {
        'pago': 'Confirmado',
        'pendente': 'Pendente',
        'cancelado': 'Cancelado',
        'reembolsado': 'Reembolsado'
    };
    return statusMap[status] || status;
}

/**
 * Formata status da inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para exibiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
 * @param {string} status - Status da inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
 * @returns {string} Status formatado
 */
function formatarStatus(status) {
    const statusMap = {
        'confirmado': 'Confirmado',
        'pendente': 'Pendente',
        'cancelado': 'Cancelado'
    };
    return statusMap[status] || status;
}

/**
 * ObtÃƒÆ’Ã‚Â©m URL da imagem do evento ou placeholder
 * @param {string} imagem - Caminho da imagem
 * @returns {string} URL da imagem
 */
function getEventoImagem(imagem) {
    if (imagem) {
        if (imagem.startsWith('http://') || imagem.startsWith('https://')) {
            return imagem;
        }
        if (imagem.startsWith('/')) {
            return imagem;
        }
        return `../../../${imagem}`;
    }
    return '../../../assets/img/default-event.jpg';
}

/**
 * Renderiza cards de estatÃƒÆ’Ã‚Â­sticas
 * @param {Object} stats - EstatÃƒÆ’Ã‚Â­sticas calculadas
 */
function renderizarEstatisticas(stats) {
    const { inscricoesAtivas, proximosEventos, kitsPendentes, pagamentosOk } = stats;

    const statCards = [
        { id: 'inscricoes-ativas', value: inscricoesAtivas, label: 'InscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes Ativas' },
        { id: 'proximos-eventos-count', value: proximosEventos, label: 'PrÃƒÆ’Ã‚Â³ximos Eventos' },
        { id: 'kits-pendentes', value: kitsPendentes, label: 'Kits Pendentes' },
        { id: 'pagamentos-ok', value: pagamentosOk, label: 'Pagamentos OK' }
    ];

    statCards.forEach(stat => {
        const element = document.getElementById(stat.id);
        if (element) {
            element.textContent = stat.value;
            console.log(`ÃƒÂ¢Ã…â€œââ‚¬Â¦ Atualizado ${stat.id}: ${stat.value}`);
        } else {
            console.warn(`ÃƒÂ¢Ã…Â¡Ã‚Â ÃƒÂ¯Ã‚Â¸Ã‚Â Elemento nÃƒÆ’Ã‚Â£o encontrado: ${stat.id}`);
        }
    });
}

/**
 * Renderiza lista de inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes recentes
 * @param {Array} inscricoes - Array de inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes
 */
function renderizarInscricoes(inscricoes) {
    const container = document.getElementById('inscricoes-recentes');
    if (!container) return;

    if (inscricoes.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-600">VocÃƒÆ’Ã‚Âª ainda nÃƒÆ’Ã‚Â£o tem inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes</p>
                <p class="text-sm text-gray-500 mt-1">Explore os eventos disponÃƒÆ’Ã‚Â­veis e faÃƒÆ’Ã‚Â§a sua primeira inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o!</p>
            </div>
        `;
        return;
    }

    const inscricoesLimitadas = inscricoes.slice(0, 5);
    
    container.innerHTML = inscricoesLimitadas.map(inscricao => {
        const imagem = getEventoImagem(inscricao.evento_imagem);
        const statusPagamento = formatarStatusPagamento(inscricao.status_pagamento);
        const statusClass = inscricao.status_pagamento === 'pago' ? 'text-green-600' : 'text-yellow-600';
        
        return `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center flex-1 min-w-0">
                    <img src="${imagem}" alt="${inscricao.evento_nome}" 
                         class="w-12 h-12 object-cover rounded-lg flex-shrink-0" 
                         onerror="this.src='../../../assets/img/default-event.jpg'">
                    <div class="ml-4 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${inscricao.evento_nome}</p>
                        <p class="text-sm text-gray-600">${inscricao.modalidade_nome || 'N/A'}</p>
                        <p class="text-xs text-gray-500">${formatarData(inscricao.evento_data)}</p>
                    </div>
                </div>
                <div class="text-right ml-4 flex-shrink-0">
                    <p class="text-sm font-medium ${statusClass}">${statusPagamento}</p>
                    <p class="text-xs text-gray-500">${inscricao.numero_inscricao || 'N/A'}</p>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Renderiza lista de prÃƒÆ’Ã‚Â³ximos eventos
 * @param {Array} eventos - Array de eventos
 */
function renderizarProximosEventos(eventos) {
    const container = document.getElementById('proximos-eventos');
    if (!container) return;

    if (eventos.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-600">Nenhum evento prÃƒÆ’Ã‚Â³ximo encontrado</p>
            </div>
        `;
        return;
    }

    const eventosLimitados = eventos.slice(0, 5);
    
    container.innerHTML = eventosLimitados.map(evento => {
        const imagem = getEventoImagem(evento.imagem);
        const dataFormatada = evento.data_formatada || formatarData(evento.data_evento);
        
        return `
            <div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
                 onclick="window.location.href='../../public/detalhes-evento.php?id=${evento.id}'">
                <img src="${imagem}" alt="${evento.nome}" 
                     class="w-12 h-12 object-cover rounded-lg flex-shrink-0"
                     onerror="this.src='../../../assets/img/default-event.jpg'">
                <div class="ml-4 flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${evento.nome}</p>
                    <p class="text-sm text-gray-600">${dataFormatada}</p>
                    <p class="text-xs text-gray-500">${evento.local_formatado || evento.cidade || ''}</p>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Calcula estatÃƒÆ’Ã‚Â­sticas das inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes
 * @param {Array} inscricoes - Array de inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes
 * @returns {Object} Objeto com estatÃƒÆ’Ã‚Â­sticas
 */
function calcularEstatisticas(inscricoes) {
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);

    const inscricoesAtivas = inscricoes.filter(i => {
        const dataEvento = new Date(i.evento_data + 'T00:00:00');
        return dataEvento >= hoje && i.status !== 'cancelado';
    }).length;

    const kitsPendentes = inscricoes.filter(i => {
        return i.status_pagamento === 'pago' && (!i.kit_nome || i.kit_nome === '');
    }).length;

    const pagamentosOk = inscricoes.filter(i => {
        return i.status_pagamento === 'pago';
    }).length;

    return {
        inscricoesAtivas,
        proximosEventos: 0, // SerÃƒÆ’Ã‚Â¡ atualizado depois com eventos
        kitsPendentes,
        pagamentosOk
    };
}

/**
 * Atualiza contador de prÃƒÆ’Ã‚Â³ximos eventos
 * @param {number} count - Quantidade de eventos
 */
function atualizarContadorEventos(count) {
    const element = document.getElementById('proximos-eventos-count');
    if (element) {
        element.textContent = count;
    }
}

/**
 * Mostra skeleton screens de loading
 */
function mostrarLoading() {
    const skeleton = `
        <div class="animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
        </div>
    `;

    document.getElementById('inscricoes-recentes')?.insertAdjacentHTML('beforebegin', skeleton);
    document.getElementById('proximos-eventos')?.insertAdjacentHTML('beforebegin', skeleton);
}

/**
 * Remove skeleton screens
 */
function removerLoading() {
    document.querySelectorAll('.animate-pulse').forEach(el => el.remove());
}

/**
 * Carrega dados do dashboard
 * @param {boolean} forceRefresh - ForÃƒÆ’Ã‚Â§a atualizaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o ignorando cache
 */
export async function carregarDashboard(forceRefresh = false) {
    try {
        console.log('ÃƒÂ°Ã…Â¸ââ‚¬Âââ‚¬Å¾ Iniciando carregamento do dashboard...');
        
        // Mostrar loading states
        mostrarLoading();

        // Buscar inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes (com cache)
        let inscricoes = forceRefresh ? null : getCache(CACHE_KEY_INSCRICOES);
        
        if (!inscricoes) {
            console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“Ã‚Â¡ Buscando inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes da API...');
            const result = await getInscricoes();
            console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“Ã…Â  Resultado da API de inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes:', result);
            
            if (result.success) {
                inscricoes = result.inscricoes || [];
                console.log(`ÃƒÂ¢Ã…â€œââ‚¬Â¦ ${inscricoes.length} inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes encontradas`);
                setCache(CACHE_KEY_INSCRICOES, inscricoes);
            } else {
                console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ Erro ao buscar inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes:', result.message);
                throw new Error(result.message || 'Erro ao carregar inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes');
            }
        } else {
            console.log(`ÃƒÂ°Ã…Â¸ââ‚¬â„¢Ã‚Â¾ Usando ${inscricoes.length} inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes do cache`);
        }

        // Buscar prÃƒÆ’Ã‚Â³ximos eventos (com cache)
        let eventos = forceRefresh ? null : getCache(CACHE_KEY_EVENTOS);
        
        if (!eventos) {
            console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“Ã‚Â¡ Buscando eventos da API...');
            const result = await getProximosEventos(5);
            console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“Ã…Â  Resultado da API de eventos:', result);
            
            if (result.success) {
                eventos = result.eventos || [];
                console.log(`ÃƒÂ¢Ã…â€œââ‚¬Â¦ ${eventos.length} eventos encontrados`);
                setCache(CACHE_KEY_EVENTOS, eventos);
            } else {
                console.warn('ÃƒÂ¢Ã…Â¡Ã‚Â ÃƒÂ¯Ã‚Â¸Ã‚Â Erro ao carregar eventos:', result.message);
                eventos = [];
            }
        } else {
            console.log(`ÃƒÂ°Ã…Â¸ââ‚¬â„¢Ã‚Â¾ Usando ${eventos.length} eventos do cache`);
        }

        // Calcular estatÃƒÆ’Ã‚Â­sticas
        console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“Ã‹â€  Calculando estatÃƒÆ’Ã‚Â­sticas...');
        const stats = calcularEstatisticas(inscricoes);
        stats.proximosEventos = eventos.length;
        console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“Ã…Â  EstatÃƒÆ’Ã‚Â­sticas calculadas:', stats);

        // Renderizar componentes
        removerLoading();
        renderizarEstatisticas(stats);
        atualizarContadorEventos(eventos.length);
        renderizarInscricoes(inscricoes);
        renderizarProximosEventos(eventos);
        
        console.log('ÃƒÂ¢Ã…â€œââ‚¬Â¦ Dashboard carregado com sucesso!');

    } catch (error) {
        console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ Erro ao carregar dashboard:', error);
        removerLoading();
        
        // Mostrar mensagem de erro
        const errorContainer = document.getElementById('dashboard-error');
        if (errorContainer) {
            errorContainer.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-red-800">${error.message}</p>
                    </div>
                    <button onclick="location.reload()" 
                            class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
                        Tentar novamente
                    </button>
                </div>
            `;
            errorContainer.classList.remove('hidden');
        }
    }
}

/**
 * Inicializa o dashboard quando a pÃƒÆ’Ã‚Â¡gina carrega
 */
export function initDashboard() {
    console.log('ÃƒÂ°Ã…Â¸Ã…Â¡ââ€šÂ¬ Inicializando dashboard...');
    console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“ââ‚¬Å¾ DOM readyState:', document.readyState);
    
    if (document.readyState === 'loading') {
        console.log('ÃƒÂ¢Ã‚ÂÃ‚Â³ Aguardando DOMContentLoaded...');
        document.addEventListener('DOMContentLoaded', () => {
            console.log('ÃƒÂ¢Ã…â€œââ‚¬Â¦ DOM carregado, iniciando dashboard...');
            carregarDashboard();
        });
    } else {
        console.log('ÃƒÂ¢Ã…â€œââ‚¬Â¦ DOM jÃƒÆ’Ã‚Â¡ carregado, iniciando dashboard...');
        carregarDashboard();
    }
}

