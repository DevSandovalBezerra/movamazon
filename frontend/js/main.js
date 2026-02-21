
/**
 * Main: Inicialização da Aplicação
 * Ponto de entrada principal que orquestra todos os módulos
 */

import { debounce } from './utils/debounce.js';
import { carregarEstados, carregarCidades } from './api/estadosCidades.js';
import { carregarEventos } from './api/eventos.js';
if (window.getApiBase) { window.getApiBase(); }

/**
 * Obtém valores dos filtros do formulário
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
 * Inicializa a aplicação quando o DOM estiver pronto
 */
function inicializar() {
    console.log('🚀 DOM carregado, iniciando...');
    console.log('🔍 Verificando elementos na página...');

    // Verificar todos os elementos importantes
    const selectEstado = document.getElementById('filtro-estado');
    const selectCidade = document.getElementById('filtro-cidade');
    const btnAplicarFiltros = document.getElementById('btn-aplicar-filtros');
    const filtroMesAnoInicio = document.getElementById('filtro-mes-ano-inicio');
    const filtroMesAnoFim = document.getElementById('filtro-mes-ano-fim');

    console.log('🔍 Elementos encontrados:', {
        selectEstado: !!selectEstado,
        selectCidade: !!selectCidade,
        btnAplicarFiltros: !!btnAplicarFiltros,
        filtroMesAnoInicio: !!filtroMesAnoInicio,
        filtroMesAnoFim: !!filtroMesAnoFim
    });

    if (selectEstado) {
        console.log('📋 Select estado encontrado:', selectEstado);
        console.log('📋 Select estado opções iniciais:', selectEstado.options.length);
    } else {
        console.error('❌ Select estado NÃO encontrado!');
        console.log('🔍 Todos os selects na página:', document.querySelectorAll('select'));
    }

    // Carregar estados primeiro
    console.log('🔄 Iniciando carregamento de estados...');
    carregarEstados().then(() => {
        // Carregar eventos iniciais após estados carregarem
        console.log('⏳ Estados carregados, carregando eventos...');
        setTimeout(() => {
            carregarEventos();
        }, 500);
    });

    // Listeners com debounce
    if (selectEstado) {
        selectEstado.addEventListener('change', function () {
            const uf = this.value;
            console.log('📤 Estado alterado para:', uf);
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
            console.log('🔍 Aplicando filtros...');
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
