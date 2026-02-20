/**
 * Componente: Card de Evento
 * Funções para renderizar e estilizar cards de eventos
 */

import * as Formatters from '../utils/formatters.js?v=20260220';
if (window.getApiBase) { window.getApiBase(); }

const corrigirMojibake = typeof Formatters.corrigirMojibake === 'function'
    ? Formatters.corrigirMojibake
    : (texto) => texto;
const formatarHora = Formatters.formatarHora;
const formatarLocal = Formatters.formatarLocal;
const getImagemEvento = Formatters.getImagemEvento;
const getNomeOrganizador = Formatters.getNomeOrganizador;

/**
 * Renderiza um card de evento moderno
 * @param {Object} evento - Dados do evento
 * @param {number} index - Índice do evento (para animação)
 * @returns {HTMLElement} Elemento DOM do card
 */
export function renderizarCard(evento, index) {
    const nomeEvento = corrigirMojibake(evento?.nome || 'Evento');
    const dataFormatada = corrigirMojibake(evento?.data_formatada || '') || 'Data não informada';
    const localFormatado = corrigirMojibake(evento?.local || '') || formatarLocal(evento?.cidade, evento?.estado);
    const horaFormatada = formatarHora(evento?.hora_inicio) || '--:--';
    const nomeOrganizador = getNomeOrganizador(evento);
    const inscritos = Number(evento?.inscritos || 0);
    const limiteVagas = Number(evento?.limite_vagas ?? evento?.limite_participantes ?? 0);
    const imagemUrl = getImagemEvento(evento?.imagem);
    const eventoId = Number(evento?.id || 0);

    const card = document.createElement('div');
    card.className = 'bg-white rounded-xl shadow-lg hover:shadow-xl hover:border hover:border-green-500 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden animate-fade-in';
    card.style.animationDelay = `${0.1 + (index * 0.1)}s`;

    card.innerHTML = `
        <div class="relative h-48 overflow-hidden bg-gray-200">
            <img
                src="${imagemUrl}"
                alt="${nomeEvento}"
                loading="lazy"
                class="w-full h-full object-cover event-card-image"
                data-loaded="false"
                onload="this.setAttribute('data-loaded', 'true')"
                onerror="this.src='https://placehold.co/640x360?text=Evento'"
            >
        </div>
        <div class="p-4 bg-white">
            <h3 class="font-bold text-lg text-gray-800 mb-3 truncate" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${nomeEvento}</h3>
            <div class="space-y-3 mb-4">
                <div class="flex items-center space-x-2 text-sm text-gray-700">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="font-medium">${dataFormatada}</span>
                    <span class="text-gray-400">•</span>
                    <span>${horaFormatada}</span>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-700">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span>${localFormatado}</span>
                </div>
            </div>
            <div class="flex justify-between items-center text-xs text-gray-500 mb-4">
                <div class="flex items-center space-x-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>${inscritos}/${limiteVagas} inscritos</span>
                </div>
                <div class="text-right">
                    <div class="font-medium text-gray-700 organizador-nome">${nomeOrganizador}</div>
                </div>
            </div>
            <a href="detalhes-evento.php?id=${eventoId}"
               class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-3 sm:py-3 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 active:from-green-700 active:to-green-800 transition-all duration-200 text-sm sm:text-sm text-center block shadow-md hover:shadow-lg touch-manipulation min-h-[44px] flex items-center justify-center">
                Inscrições Abertas
            </a>
        </div>
    `;

    return card;
}

