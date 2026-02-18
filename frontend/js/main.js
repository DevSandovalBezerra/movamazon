
/**
 * Main: InicializaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o da AplicaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
 * Ponto de entrada principal que orquestra todos os mÃƒÆ’Ã‚Â³dulos
 */

import { debounce } from './utils/debounce.js';
import { carregarEstados, carregarCidades } from './api/estadosCidades.js';
import { carregarEventos } from './api/eventos.js';
if (window.getApiBase) { window.getApiBase(); }

/**
 * ObtÃƒÆ’Ã‚Â©m valores dos filtros do formulÃƒÆ’Ã‚Â¡rio
 * @returns {Object} Objeto com valores dos filtros
 */
function obterFiltros() {
    const cidade = document.getElementById('filtro-cidade')?.value || '';
    const mesAnoDe = document.getElementById('filtro-mes-ano-inicio')?.value || '';
    const mesAnoAte = document.getElementById('filtro-mes-ano-fim')?.value || '';
    
    return { cidade, mesAnoDe, mesAnoAte };
}

/**
 * Aplica filtros com debounce
 */
const aplicarFiltrosComDebounce = debounce(() => {
    const { cidade, mesAnoDe, mesAnoAte } = obterFiltros();
    carregarEventos(cidade, mesAnoDe, mesAnoAte);
}, 300);

/**
 * Inicializa a aplicaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o quando o DOM estiver pronto
 */
function inicializar() {
    console.log('ÃƒÂ°Ã…Â¸Ã…Â¡Ã¢â€šÂ¬ DOM carregado, iniciando...');
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Â Verificando elementos na pÃƒÆ’Ã‚Â¡gina...');

    // Verificar todos os elementos importantes
    const selectEstado = document.getElementById('filtro-estado');
    const selectCidade = document.getElementById('filtro-cidade');
    const btnAplicarFiltros = document.getElementById('btn-aplicar-filtros');
    const filtroMesAnoInicio = document.getElementById('filtro-mes-ano-inicio');
    const filtroMesAnoFim = document.getElementById('filtro-mes-ano-fim');

    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Â Elementos encontrados:', {
        selectEstado: !!selectEstado,
        selectCidade: !!selectCidade,
        btnAplicarFiltros: !!btnAplicarFiltros,
        filtroMesAnoInicio: !!filtroMesAnoInicio,
        filtroMesAnoFim: !!filtroMesAnoFim
    });

    if (selectEstado) {
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã¢â‚¬Â¹ Select estado encontrado:', selectEstado);
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã¢â‚¬Â¹ Select estado opÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes iniciais:', selectEstado.options.length);
    } else {
        console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ Select estado NÃƒÆ’Ã†â€™O encontrado!');
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Â Todos os selects na pÃƒÆ’Ã‚Â¡gina:', document.querySelectorAll('select'));
    }

    // Carregar estados primeiro
    console.log('ÃƒÂ°Ã…Â¸Ã…â€™Ã‚Â Iniciando carregamento de estados...');
    carregarEstados().then(() => {
        // Carregar eventos iniciais apÃƒÆ’Ã‚Â³s estados carregarem
        console.log('ÃƒÂ¢Ã‚ÂÃ‚Â° Estados carregados, carregando eventos...');
        setTimeout(() => {
            carregarEventos();
        }, 500);
    });

    // Listeners com debounce
    if (selectEstado) {
        selectEstado.addEventListener('change', function () {
            const uf = this.value;
            console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ¢â‚¬Å¾ Estado alterado para:', uf);
            carregarCidades(uf);
            // Aplicar filtros automaticamente quando estado mudar
            aplicarFiltrosComDebounce();
        });
    }

    if (selectCidade) {
        selectCidade.addEventListener('change', aplicarFiltrosComDebounce);
    }

    if (filtroMesAnoInicio) {
        filtroMesAnoInicio.addEventListener('change', aplicarFiltrosComDebounce);
    }

    if (filtroMesAnoFim) {
        filtroMesAnoFim.addEventListener('change', aplicarFiltrosComDebounce);
    }

    if (btnAplicarFiltros) {
        btnAplicarFiltros.addEventListener('click', function (e) {
            e.preventDefault();
            console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Â Aplicando filtros...');
            const { cidade, mesAnoDe, mesAnoAte } = obterFiltros();
            carregarEventos(cidade, mesAnoDe, mesAnoAte);
        });
    }
}

// Inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializar);
} else {
    inicializar();
}


