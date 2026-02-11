/**
 * Componente: Card de Evento
 * Funções para renderizar e estilizar cards de eventos
 */

import { formatarHora, formatarLocal, getImagemEvento, getNomeOrganizador } from '../utils/formatters.js';

/**
 * Gera cores dinâmicas baseadas no nome do evento
 * @param {string} nomeEvento - Nome do evento
 * @returns {Object} Objeto com cores primária e secundária
 */
export function gerarCoresEvento(nomeEvento) {
    const cores = [
        { primaria: '#3B82F6', secundaria: '#1D4ED8' }, // Azul
        { primaria: '#10B981', secundaria: '#047857' }, // Verde
        { primaria: '#F59E0B', secundaria: '#D97706' }, // Amarelo
        { primaria: '#EF4444', secundaria: '#DC2626' }, // Vermelho
        { primaria: '#8B5CF6', secundaria: '#7C3AED' }, // Roxo
        { primaria: '#EC4899', secundaria: '#DB2777' }, // Rosa
        { primaria: '#06B6D4', secundaria: '#0891B2' }, // Ciano
        { primaria: '#84CC16', secundaria: '#65A30D' }, // Lima
    ];

    // Usar o hash do nome para escolher cores consistentes
    let hash = 0;
    for (let i = 0; i < nomeEvento.length; i++) {
        hash = nomeEvento.charCodeAt(i) + ((hash << 5) - hash);
    }

    const index = Math.abs(hash) % cores.length;
    return cores[index];
}

/**
 * Renderiza um card de evento moderno
 * @param {Object} evento - Dados do evento
 * @param {number} index - Índice do evento (para animação)
 * @returns {HTMLElement} Elemento DOM do card
 */
export function renderizarCard(evento, index) {
    console.log(`🎨 Criando card moderno para evento: ${evento.nome}`);

    // Gerar cores dinâmicas baseadas no nome do evento
    const cores = gerarCoresEvento(evento.nome);

    // Monta o card do evento moderno
    const card = document.createElement('div');
    card.className = 'bg-white rounded-xl shadow-lg hover:shadow-xl hover:border hover:border-green-500 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden animate-fade-in';
    card.style.animationDelay = `${0.1 + (index * 0.1)}s`;

    const imagemUrl = getImagemEvento(evento.imagem);
    const localFormatado = evento.local || formatarLocal(evento.cidade, evento.estado);
    const horaFormatada = formatarHora(evento.hora_inicio) || '--:--';
    const nomeOrganizador = getNomeOrganizador(evento);

    card.innerHTML = `
        <!-- Seção Visual Superior (60-70% do card) -->
        <div class="relative h-48 overflow-hidden bg-gray-200">
            <!-- Imagem de fundo com lazy loading -->
            <img 
                src="${imagemUrl}" 
                alt="${evento.nome}"
                loading="lazy"
                class="w-full h-full object-cover event-card-image"
                data-loaded="false"
                onload="this.setAttribute('data-loaded', 'true')"
                onerror="this.src='https://placehold.co/640x360?text=Evento'"
            >
        </div>

        <!-- Seção de Informações Inferior (30-40% do card) -->
        <div class="p-4 bg-white">
            <!-- Título do evento -->
            <h3 class="font-bold text-lg text-gray-800 mb-3 truncate" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${evento.nome}</h3>
            
            <!-- Informações principais -->
            <div class="space-y-3 mb-4">
                <!-- Data e Hora -->
                <div class="flex items-center space-x-2 text-sm text-gray-700">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="font-medium">${evento.data_formatada || 'Data não informada'}</span>
                    <span class="text-gray-400">•</span>
                    <span>${horaFormatada}</span>
                </div>
                
                <!-- Localização -->
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

            <!-- Informações secundárias -->
            <div class="flex justify-between items-center text-xs text-gray-500 mb-4">
                <div class="flex items-center space-x-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>${evento.inscritos || 0}/${evento.limite_vagas || 0} inscritos</span>
                </div>

                <div class="text-right">
                    <div class="font-medium text-gray-700 organizador-nome">${nomeOrganizador}</div>
                </div>

            </div>

            <!-- Botão de ação -->
            <a href="detalhes-evento.php?id=${evento.id}" 
               class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-3 sm:py-3 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 active:from-green-700 active:to-green-800 transition-all duration-200 text-sm sm:text-sm text-center block shadow-md hover:shadow-lg touch-manipulation min-h-[44px] flex items-center justify-center">
                Inscrições Abertas
            </a>
        </div>
    `;

    console.log(`✅ Card moderno criado para: ${evento.nome}`);
    return card;
}

