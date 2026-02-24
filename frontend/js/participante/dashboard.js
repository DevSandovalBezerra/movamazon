
/**
 * Dashboard do Participante
 * Gerencia o carregamento e exibição de dados do dashboard
 */

import { getInscricoes } from '../api/participante.js';
import { getProximosEventos } from '../api/eventosParticipante.js';
import { getCache, setCache } from '../utils/cache.js';
if (window.getApiBase) { window.getApiBase(); }

const CACHE_KEY_INSCRICOES = 'dashboard_inscricoes';
const CACHE_KEY_EVENTOS = 'dashboard_eventos';

/**
 * Formata data para exibição
 * @param {string} dateString - Data no formato YYYY-MM-DD
 * @returns {string} Data formatada como DD/MM/YYYY
 */
function formatarData(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString + 'T00:00:00');
    return date.toLocaleDateString('pt-BR');
}

/**
 * Formata status de pagamento para exibição
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
 * Formata status da inscrição para exibição
 * @param {string} status - Status da inscrição
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
 * Obtém URL da imagem do evento ou placeholder
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
 * Renderiza cards de estatísticas
 * @param {Object} stats - Estatísticas calculadas
 */
function renderizarEstatisticas(stats) {
    const { inscricoesAtivas, proximosEventos, kitsPendentes, pagamentosOk } = stats;

    const statCards = [
        { id: 'inscricoes-ativas', value: inscricoesAtivas, label: 'Inscrições Ativas' },
        { id: 'proximos-eventos-count', value: proximosEventos, label: 'Próximos Eventos' },
        { id: 'kits-pendentes', value: kitsPendentes, label: 'Kits Pendentes' },
        { id: 'pagamentos-ok', value: pagamentosOk, label: 'Pagamentos OK' }
    ];

    statCards.forEach(stat => {
        const element = document.getElementById(stat.id);
        if (element) {
            element.textContent = stat.value;
            console.log(`... Atualizado ${stat.id}: ${stat.value}`);
        } else {
            console.warn(`  Elemento não encontrado: ${stat.id}`);
        }
    });
}

/**
 * Renderiza lista de inscrições recentes
 * @param {Array} inscricoes - Array de inscrições
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
                <p class="text-gray-600">Você ainda não tem inscrições</p>
                <p class="text-sm text-gray-500 mt-1">Explore os eventos disponíveis e faça sua primeira inscrição!</p>
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
 * Renderiza lista de próximos eventos
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
                <p class="text-gray-600">Nenhum evento próximo encontrado</p>
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
 * Calcula estatísticas das inscrições
 * @param {Array} inscricoes - Array de inscrições
 * @returns {Object} Objeto com estatísticas
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
        proximosEventos: 0, // Será atualizado depois com eventos
        kitsPendentes,
        pagamentosOk
    };
}

/**
 * Atualiza contador de próximos eventos
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
 * @param {boolean} forceRefresh - Força atualização ignorando cache
 */
export async function carregarDashboard(forceRefresh = false) {
    try {
        console.log(' Iniciando carregamento do dashboard...');
        
        // Mostrar loading states
        mostrarLoading();

        // Buscar inscrições (com cache)
        let inscricoes = forceRefresh ? null : getCache(CACHE_KEY_INSCRICOES);
        
        if (!inscricoes) {
            console.log(' Buscando inscrições da API...');
            const result = await getInscricoes();
            console.log('  Resultado da API de inscrições:', result);
            
            if (result.success) {
                inscricoes = result.inscricoes || [];
                console.log(`... ${inscricoes.length} inscrições encontradas`);
                setCache(CACHE_KEY_INSCRICOES, inscricoes);
            } else {
                console.error(' Erro ao buscar inscrições:', result.message);
                throw new Error(result.message || 'Erro ao carregar inscrições');
            }
        } else {
            console.log(` Usando ${inscricoes.length} inscrições do cache`);
        }

        // Buscar próximos eventos (com cache)
        let eventos = forceRefresh ? null : getCache(CACHE_KEY_EVENTOS);
        
        if (!eventos) {
            console.log(' Buscando eventos da API...');
            const result = await getProximosEventos(5);
            console.log('  Resultado da API de eventos:', result);
            
            if (result.success) {
                eventos = result.eventos || [];
                console.log(`... ${eventos.length} eventos encontrados`);
                setCache(CACHE_KEY_EVENTOS, eventos);
            } else {
                console.warn('  Erro ao carregar eventos:', result.message);
                eventos = [];
            }
        } else {
            console.log(` Usando ${eventos.length} eventos do cache`);
        }

        // Calcular estatísticas
        console.log('  Calculando estatísticas...');
        const stats = calcularEstatisticas(inscricoes);
        stats.proximosEventos = eventos.length;
        console.log('  Estatísticas calculadas:', stats);

        // Renderizar componentes
        removerLoading();
        renderizarEstatisticas(stats);
        atualizarContadorEventos(eventos.length);
        renderizarInscricoes(inscricoes);
        renderizarProximosEventos(eventos);
        
        console.log('... Dashboard carregado com sucesso!');

    } catch (error) {
        console.error(' Erro ao carregar dashboard:', error);
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
 * Inicializa o dashboard quando a página carrega
 */
export function initDashboard() {
    console.log(' Inicializando dashboard...');
    console.log(' DOM readyState:', document.readyState);
    
    if (document.readyState === 'loading') {
        console.log(' Aguardando DOMContentLoaded...');
        document.addEventListener('DOMContentLoaded', () => {
            console.log('... DOM carregado, iniciando dashboard...');
            carregarDashboard();
        });
    } else {
        console.log('... DOM já carregado, iniciando dashboard...');
        carregarDashboard();
    }
}

