/**
 * UI: Contador de Eventos
 * Gerencia a exibição do contador de eventos na interface
 */

/**
 * Atualiza o estado do contador de eventos
 * @param {string} estado - Estado: 'carregando', 'sucesso', 'erro', 'vazio'
 * @param {Array} dados - Array de eventos (opcional, apenas para estado 'sucesso')
 */
export function atualizarContadorEventos(estado, dados = null) {
    const contador = document.getElementById('eventos-count');
    if (!contador) return;

    // Adicionar classe de fade-out para transição suave
    contador.classList.add('fade-out');

    setTimeout(() => {
        switch (estado) {
            case 'carregando':
                contador.innerHTML = `
                    <div class="flex items-center justify-center space-x-2">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-green-600"></div>
                        <span>Carregando eventos...</span>
                    </div>
                `;
                break;
            case 'sucesso':
                const total = dados ? dados.length : 0;
                contador.innerHTML = `
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>${total} eventos disponíveis</span>
                    </div>
                `;
                break;
            case 'erro':
                contador.innerHTML = `
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Erro ao carregar eventos</span>
                    </div>
                `;
                break;
            case 'vazio':
                contador.innerHTML = `
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.709M15 6.291A7.962 7.962 0 0012 5c-2.34 0-4.29 1.009-5.824 2.709"></path>
                        </svg>
                        <span>Nenhum evento encontrado</span>
                    </div>
                `;
                break;
        }

        // Remover fade-out e adicionar fade-in para transição suave
        contador.classList.remove('fade-out');
        contador.classList.add('fade-in');

        // Remover fade-in após a animação
        setTimeout(() => {
            contador.classList.remove('fade-in');
        }, 300);
    }, 150);
}


