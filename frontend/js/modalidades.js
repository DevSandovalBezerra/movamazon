if (window.getApiBase) { window.getApiBase(); }
// VariÃƒÆ’Ã‚Â¡veis globais
let modalidades = [];
let modalidadesFiltradas = [];
let eventos = [];
let categorias = [];
let eventoSelecionado = null;

function atualizarBotaoGerenciarCategorias(eventoId) {
    const btn = document.getElementById('btnGerenciarCategorias');
    if (!btn) return;

    const habilitar = !!(eventoId && eventoId.toString().trim());
    btn.disabled = !habilitar;

    if (habilitar) {
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
        btn.classList.add('hover:bg-green-700');
    } else {
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        btn.classList.remove('hover:bg-green-700');
    }
}

// InicializaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
document.addEventListener('DOMContentLoaded', function() {
    console.log('ÃƒÂ°Ã…Â¸Ã…Â¡Ã¢â€šÂ¬ modalidades.js - DOM carregado, iniciando...');
    carregarEventos();
    configurarEventListeners();
    atualizarBotaoGerenciarCategorias(document.getElementById('filtroEvento')?.value || '');
    console.log('ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ modalidades.js - InicializaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o concluÃƒÆ’Ã‚Â­da');
});

// Configurar event listeners
function configurarEventListeners() {
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Â§ modalidades.js - Configurando event listeners...');
    
    // Evento selecionado
    const filtroEvento = document.getElementById('filtroEvento');
    if (filtroEvento) {
        console.log('ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ modalidades.js - Filtro evento encontrado, adicionando listener');
        filtroEvento.addEventListener('change', function() {
            const eventoId = this.value;
            console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ modalidades.js - Filtro evento alterado para:', eventoId);
            atualizarBotaoGerenciarCategorias(eventoId);
            
            if (eventoId) {
                eventoSelecionado = eventoId;
                console.log('ÃƒÂ°Ã…Â¸Ã…Â½Ã‚Â¯ modalidades.js - Evento selecionado:', eventoId);
                document.getElementById('filtroCategoria').disabled = false;
                carregarCategoriasDoEvento(eventoId);
                carregarModalidades(eventoId);
            } else {
                eventoSelecionado = null;
                console.log('ÃƒÂ¢Ã‚ÂÃ…â€™ modalidades.js - Nenhum evento selecionado');
                document.getElementById('filtroCategoria').disabled = true;
                categorias = [];
                preencherSelectCategorias();
                preencherFiltroCategorias();
                mostrarSelecionarFiltros();
            }
        });
    } else {
        console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ modalidades.js - Filtro evento NÃƒÆ’Ã†â€™O encontrado!');
    }

    // Categoria selecionada
    const filtroCategoria = document.getElementById('filtroCategoria');
    if (filtroCategoria) {
        console.log('ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ modalidades.js - Filtro categoria encontrado, adicionando listener');
        filtroCategoria.addEventListener('change', function() {
            const categoriaId = this.value;
            console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ modalidades.js - Filtro categoria alterado para:', categoriaId);
            if (eventoSelecionado) {
                aplicarFiltros();
            }
        });
    } else {
        console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ modalidades.js - Filtro categoria NÃƒÆ’Ã†â€™O encontrado!');
    }
    
    console.log('ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ modalidades.js - Event listeners configurados');
}

// Carregar eventos do organizador
async function carregarEventos() {
    console.log('ÃƒÂ°Ã…Â¸Ã…Â¡Ã¢â€šÂ¬ modalidades.js - Iniciando carregamento de eventos');
    try {
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ modalidades.js - Fazendo requisiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para API eventos');
        const response = await fetch((window.API_BASE || '/api') + '/organizador/eventos/list.php');
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ modalidades.js - Status da resposta:', response.status);
        
        const data = await response.json();
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ modalidades.js - Dados recebidos:', data);
        
        if (data.success) {
            console.log('ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ modalidades.js - Sucesso! Eventos carregados:', data.data.eventos.length);
            eventos = data.data.eventos;
            preencherSelectEventos();
        } else {
            console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ modalidades.js - Erro na resposta:', data.error);
            Swal.fire('Erro', data.error || 'Erro ao carregar eventos', 'error');
        }
    } catch (error) {
        console.error('ÃƒÂ°Ã…Â¸Ã¢â‚¬â„¢Ã‚Â¥ modalidades.js - Erro ao carregar eventos:', error);
        Swal.fire('Erro', 'Erro ao carregar eventos', 'error');
    }
}

// Carregar categorias disponÃƒÆ’Ã‚Â­veis do evento (nome ÃƒÆ’Ã‚Âºnico para evitar conflito com categorias.js)
async function carregarCategoriasDoEvento(eventoId) {
    try {
        if (!eventoId) {
            console.warn('ÃƒÂ¢Ã…Â¡Ã‚Â ÃƒÂ¯Ã‚Â¸Ã‚Â modalidades.js - carregarCategorias chamado sem eventoId');
            return;
        }
        console.log('ÃƒÂ°Ã…Â¸Ã…Â¡Ã¢â€šÂ¬ modalidades.js - Iniciando carregamento de categorias do evento', eventoId);
        const response = await fetch(`${window.API_BASE || '/api'}/categoria/list_public.php?evento_id=${encodeURIComponent(eventoId)}`);
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ modalidades.js - Status da resposta categorias:', response.status);
        
        const data = await response.json();
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ modalidades.js - Dados categorias recebidos:', data);
        
        if (data.success) {
            console.log('ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ modalidades.js - Categorias carregadas:', data.categorias.length);
            categorias = data.categorias;
            preencherSelectCategorias();
            preencherFiltroCategorias();
        } else {
            console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ modalidades.js - Erro na resposta categorias:', data.message);
        }
    } catch (error) {
        console.error('ÃƒÂ°Ã…Â¸Ã¢â‚¬â„¢Ã‚Â¥ modalidades.js - Erro ao carregar categorias:', error);
    }
}

// Ouvir atualizaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de categorias disparada pelo painel de categorias
document.addEventListener('categorias-atualizadas', function(e) {
    try {
        const evtId = e?.detail?.eventoId;
        if (eventoSelecionado && (!evtId || evtId.toString() === eventoSelecionado.toString())) {
            carregarCategoriasDoEvento(eventoSelecionado);
        }
    } catch (err) {
        console.error('modalidades.js - erro ao tratar categorias-atualizadas:', err);
    }
});

// Preencher select de eventos
function preencherSelectEventos() {
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Â§ modalidades.js - Preenchendo select de eventos');
    const select = document.getElementById('filtroEvento');
    
    if (!select) {
        console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ modalidades.js - Select de eventos NÃƒÆ’Ã†â€™O encontrado!');
        return;
    }
    
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã¢â‚¬Â¹ modalidades.js - Eventos para preencher:', eventos);
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã¢â‚¬Â¹ modalidades.js - Quantidade de eventos:', eventos.length);
    
    select.innerHTML = '<option value="">Selecione um evento</option>';
    
    eventos.forEach((evento, index) => {
        console.log(`ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â modalidades.js - Adicionando evento ${index + 1}:`, evento);
        const option = document.createElement('option');
        option.value = evento.id;
        option.textContent = evento.nome;
        select.appendChild(option);
    });
    
    console.log('ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ modalidades.js - Select de eventos preenchido com', eventos.length, 'eventos');
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Â modalidades.js - HTML do select:', select.innerHTML);
    atualizarBotaoGerenciarCategorias(select.value || '');
}

// Preencher select de categorias no modal
function preencherSelectCategorias() {
    const select = document.getElementById('categoriaId');
    select.innerHTML = '<option value="">Selecione uma categoria...</option>';
    
    categorias.forEach(categoria => {
        const option = document.createElement('option');
        option.value = categoria.id;
        option.textContent = categoria.nome;
        select.appendChild(option);
    });
}

// Preencher filtro de categorias
function preencherFiltroCategorias() {
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Â§ modalidades.js - Preenchendo filtro de categorias');
    const select = document.getElementById('filtroCategoria');
    
    if (!select) {
        console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ modalidades.js - Filtro categoria NÃƒÆ’Ã†â€™O encontrado!');
        return;
    }
    
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã¢â‚¬Â¹ modalidades.js - Categorias para preencher:', categorias);
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã¢â‚¬Â¹ modalidades.js - Quantidade de categorias:', categorias.length);
    
    select.innerHTML = '<option value="">Todas as categorias</option>';
    
    categorias.forEach((categoria, index) => {
        console.log(`ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â modalidades.js - Adicionando categoria ${index + 1}:`, categoria);
        const option = document.createElement('option');
        option.value = categoria.id;
        option.textContent = categoria.nome;
        select.appendChild(option);
    });
    
    console.log('ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ modalidades.js - Filtro de categorias preenchido com', categorias.length, 'categorias');
}

// Carregar modalidades do evento
async function carregarModalidades(eventoId) {
    console.log('ÃƒÂ°Ã…Â¸Ã…Â¡Ã¢â€šÂ¬ modalidades.js - Carregando modalidades para evento ID:', eventoId);
    try {
        mostrarLoading();
        
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ modalidades.js - Fazendo requisiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para modalidades');
        const url = `${window.API_BASE || '/api'}/organizador/modalidades/list.php?evento_id=${eventoId}`;
        console.log('ÃƒÂ°Ã…Â¸Ã…â€™Ã‚Â modalidades.js - URL da requisiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o:', url);
        
        const response = await fetch(url);
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ modalidades.js - Status da resposta modalidades:', response.status);
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ modalidades.js - Headers da resposta:', response.headers);
        
        const data = await response.json();
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ modalidades.js - Dados modalidades recebidos:', data);
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¡ modalidades.js - Estrutura da resposta:', Object.keys(data));
        
        if (data.success) {
            console.log('ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ modalidades.js - Modalidades carregadas:', data.modalidades?.length || 0);
            console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã¢â‚¬Â¹ modalidades.js - Array de modalidades:', data.modalidades);
            modalidades = data.modalidades || [];
            modalidadesFiltradas = [...modalidades];
            console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬â„¢Ã‚Â¾ modalidades.js - Modalidades armazenadas:', modalidades.length);
            renderizarModalidades();
        } else {
            console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ modalidades.js - Erro na resposta modalidades:', data.message);
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('ÃƒÂ°Ã…Â¸Ã¢â‚¬â„¢Ã‚Â¥ modalidades.js - Erro ao carregar modalidades:', error);
        console.error('ÃƒÂ°Ã…Â¸Ã¢â‚¬â„¢Ã‚Â¥ modalidades.js - Stack trace:', error.stack);
        mostrarErro('Erro ao carregar modalidades: ' + error.message);
    } finally {
        ocultarLoading();
    }
}

// Renderizar modalidades na tabela
function renderizarModalidades() {
    console.log('ÃƒÂ°Ã…Â¸Ã…Â½Ã‚Â¨ modalidades.js - Renderizando modalidades...');
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã…Â  modalidades.js - Modalidades para renderizar:', modalidadesFiltradas.length);
    
    const tbody = document.getElementById('modalidades-tbody');
    if (!tbody) {
        console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ modalidades.js - Tbody NÃƒÆ’Ã†â€™O encontrado!');
        return;
    }
    
    if (modalidadesFiltradas.length === 0) {
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â­ modalidades.js - Nenhuma modalidade para renderizar, mostrando estado vazio');
        mostrarSemModalidades();
        return;
    }
    
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â modalidades.js - Gerando HTML para', modalidadesFiltradas.length, 'modalidades');
    tbody.innerHTML = modalidadesFiltradas.map(modalidade => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div>
                    <div class="text-sm font-medium text-gray-900">${modalidade.nome}</div>
                    ${modalidade.descricao ? `<div class="text-sm text-gray-500">${modalidade.descricao}</div>` : ''}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                    modalidade.tipo_publico === 'comunidade_academica' ? 'bg-green-100 text-green-800' :
                    modalidade.tipo_publico === 'publico_geral' ? 'bg-blue-100 text-blue-800' :
                    'bg-purple-100 text-purple-800'
                }">
                    ${modalidade.categoria_nome}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${modalidade.distancia || '-'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                    modalidade.tipo_prova === 'corrida' ? 'bg-red-100 text-red-800' :
                    modalidade.tipo_prova === 'caminhada' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-purple-100 text-purple-800'
                }">
                    ${getTipoProvaLabel(modalidade.tipo_prova)}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button onclick="editarModalidade(${modalidade.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button onclick="excluirModalidade(${modalidade.id}, '${modalidade.nome}')" class="text-red-600 hover:text-red-900">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </td>
        </tr>
    `).join('');
    
    mostrarModalidadesContainer();
}

function getTipoProvaLabel(tipo) {
    const labels = {
        'corrida': 'Corrida',
        'caminhada': 'Caminhada',
        'ambos': 'Ambos'
    };
    return labels[tipo] || tipo;
}

// Mostrar loading
function mostrarLoading() {
    console.log('ÃƒÂ¢Ã‚ÂÃ‚Â³ modalidades.js - Mostrando loading...');
    const loading = document.getElementById('loading');
    if (loading) {
        loading.classList.remove('hidden');
        console.log('ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ modalidades.js - Loading mostrado');
    } else {
        console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ modalidades.js - Elemento loading NÃƒÆ’Ã†â€™O encontrado!');
    }
    
    // Ocultar outros estados
    ['error-modalidades', 'sem-modalidades', 'selecionar-filtros', 'modalidades-container'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.classList.add('hidden');
        } else {
            console.warn(`ÃƒÂ¢Ã…Â¡Ã‚Â ÃƒÂ¯Ã‚Â¸Ã‚Â modalidades.js - Elemento ${id} NÃƒÆ’Ã†â€™O encontrado`);
        }
    });
}

// Ocultar loading
function ocultarLoading() {
    document.getElementById('loading').classList.add('hidden');
}

// Mostrar erro
function mostrarErro(message) {
    document.getElementById('error-message').textContent = message;
    document.getElementById('error-modalidades').classList.remove('hidden');
    document.getElementById('loading').classList.add('hidden');
    document.getElementById('sem-modalidades').classList.add('hidden');
    document.getElementById('selecionar-filtros').classList.add('hidden');
    document.getElementById('modalidades-container').classList.add('hidden');
}

// Mostrar sem modalidades
function mostrarSemModalidades() {
    document.getElementById('sem-modalidades').classList.remove('hidden');
    document.getElementById('loading').classList.add('hidden');
    document.getElementById('error-modalidades').classList.add('hidden');
    document.getElementById('selecionar-filtros').classList.add('hidden');
    document.getElementById('modalidades-container').classList.add('hidden');
}

// Mostrar container de modalidades
function mostrarModalidadesContainer() {
    document.getElementById('modalidades-container').classList.remove('hidden');
    document.getElementById('loading').classList.add('hidden');
    document.getElementById('error-modalidades').classList.add('hidden');
    document.getElementById('sem-modalidades').classList.add('hidden');
    document.getElementById('selecionar-filtros').classList.add('hidden');
}

// Mostrar selecionar filtros
function mostrarSelecionarFiltros() {
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Â modalidades.js - Mostrando selecionar filtros...');
    const selecionarFiltros = document.getElementById('selecionar-filtros');
    if (selecionarFiltros) {
        selecionarFiltros.classList.remove('hidden');
        console.log('ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ modalidades.js - Selecionar filtros mostrado');
    } else {
        console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ modalidades.js - Elemento selecionar-filtros NÃƒÆ’Ã†â€™O encontrado!');
    }
    
    // Ocultar outros estados
    ['loading', 'error-modalidades', 'sem-modalidades', 'modalidades-container'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.classList.add('hidden');
        } else {
            console.warn(`ÃƒÂ¢Ã…Â¡Ã‚Â ÃƒÂ¯Ã‚Â¸Ã‚Â modalidades.js - Elemento ${id} NÃƒÆ’Ã†â€™O encontrado`);
        }
    });
}

// Abrir modal para criar modalidade
function abrirModalCriar() {
    if (!eventoSelecionado) {
        Swal.fire('AtenÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o', 'Selecione um evento primeiro', 'warning');
        return;
    }
    // Garantir categorias atualizadas para o evento selecionado
    try { carregarCategoriasDoEvento(eventoSelecionado); } catch (e) { console.warn('NÃƒÆ’Ã‚Â£o foi possÃƒÆ’Ã‚Â­vel atualizar categorias antes da modal.', e); }
    
    document.getElementById('modalTitulo').textContent = 'Criar Nova Modalidade';
    document.getElementById('formModalidade').reset();
    document.getElementById('modalidadeId').value = '';
    document.getElementById('eventoId').value = eventoSelecionado;
    
    const modal = document.getElementById('modalModalidade');
    modal.classList.remove('hidden');
}

// Abrir modal para editar modalidade
async function editarModalidade(id) {
    try {
        const response = await fetch(`${window.API_BASE || '/api'}/organizador/modalidades/get.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const modalidade = data.modalidade;
            
            document.getElementById('modalTitulo').textContent = 'Editar Modalidade';
            document.getElementById('modalidadeId').value = modalidade.id;
            document.getElementById('eventoId').value = eventoSelecionado;
            document.getElementById('nomeModalidade').value = modalidade.nome;
            document.getElementById('categoriaId').value = modalidade.categoria.id;
            document.getElementById('descricao').value = modalidade.descricao || '';
            document.getElementById('distancia').value = modalidade.distancia || '';
            document.getElementById('tipoProva').value = modalidade.tipo_prova;
            document.getElementById('limiteVagas').value = modalidade.limite_vagas || '';
            
            const modal = document.getElementById('modalModalidade');
            modal.classList.remove('hidden');
        } else {
            Swal.fire('Erro', 'Erro ao carregar dados da modalidade', 'error');
        }
    } catch (error) {
        console.error('Erro ao carregar modalidade:', error);
        Swal.fire('Erro', 'Erro ao carregar modalidade', 'error');
    }
}

// Fechar modal
function fecharModalModalidade() {
    const modal = document.getElementById('modalModalidade');
    modal.classList.add('hidden');
}

// Salvar modalidade (criar ou editar)
async function salvarModalidade() {
    try {
        const modalidadeId = document.getElementById('modalidadeId').value;
        const formData = new FormData();
        
        // Adicionar dados do formulÃƒÆ’Ã‚Â¡rio
        formData.append('evento_id', document.getElementById('eventoId').value);
        formData.append('categoria_id', document.getElementById('categoriaId').value);
        formData.append('nome', document.getElementById('nomeModalidade').value);
        formData.append('descricao', document.getElementById('descricao').value);
        formData.append('distancia', document.getElementById('distancia').value);
        formData.append('tipo_prova', document.getElementById('tipoProva').value);
        formData.append('limite_vagas', document.getElementById('limiteVagas').value);
        
        // Validar campos obrigatÃƒÆ’Ã‚Â³rios
        if (!formData.get('categoria_id') || !formData.get('nome')) {
            Swal.fire('AtenÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o', 'Categoria e Nome sÃƒÆ’Ã‚Â£o campos obrigatÃƒÆ’Ã‚Â³rios', 'warning');
            return;
        }
        
        let url, message;
        
        if (modalidadeId) {
            // Editar modalidade existente
            formData.append('id', modalidadeId);
            url = (window.API_BASE || '/api') + '/organizador/modalidades/update.php';
            message = 'Modalidade atualizada com sucesso';
        } else {
            // Criar nova modalidade
            url = (window.API_BASE || '/api') + '/organizador/modalidades/create.php';
            message = 'Modalidade criada com sucesso';
        }
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire('Sucesso', message, 'success');
            fecharModalModalidade();
            carregarModalidades(eventoSelecionado);
        } else {
            Swal.fire('Erro', data.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao salvar modalidade:', error);
        Swal.fire('Erro', 'Erro ao salvar modalidade', 'error');
    }
}

// Excluir modalidade
async function excluirModalidade(id, nome) {
    try {
        const result = await Swal.fire({
            title: 'Confirmar exclusÃƒÆ’Ã‚Â£o',
            text: `Deseja realmente excluir a modalidade "${nome}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            
            const response = await fetch((window.API_BASE || '/api') + '/organizador/modalidades/delete.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire('Sucesso', 'Modalidade excluÃƒÆ’Ã‚Â­da com sucesso', 'success');
                carregarModalidades(eventoSelecionado);
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        }
    } catch (error) {
        console.error('Erro ao excluir modalidade:', error);
        Swal.fire('Erro', 'Erro ao excluir modalidade', 'error');
    }
}

// Aplicar filtros
function aplicarFiltros() {
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Â modalidades.js - Aplicando filtros...');
    console.log('ÃƒÂ°Ã…Â¸Ã…Â½Ã‚Â¯ modalidades.js - Evento selecionado:', eventoSelecionado);
    
    if (!eventoSelecionado) {
        console.warn('ÃƒÂ¢Ã…Â¡Ã‚Â ÃƒÂ¯Ã‚Â¸Ã‚Â modalidades.js - Nenhum evento selecionado');
        Swal.fire('AtenÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o', 'Selecione um evento primeiro', 'warning');
        return;
    }
    
    const categoriaId = document.getElementById('filtroCategoria').value;
    console.log('ÃƒÂ°Ã…Â¸Ã‚ÂÃ‚Â·ÃƒÂ¯Ã‚Â¸Ã‚Â modalidades.js - Categoria selecionada:', categoriaId);
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã…Â  modalidades.js - Total de modalidades antes do filtro:', modalidades.length);
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã¢â‚¬Â¹ modalidades.js - Modalidades disponÃƒÆ’Ã‚Â­veis:', modalidades);
    
    modalidadesFiltradas = modalidades.filter(item => {
        // Buscar a categoria da modalidade para fazer o filtro
        const categoria = categorias.find(cat => cat.id.toString() === item.categoria_id?.toString());
        const matchCategoria = !categoriaId || (categoria && categoria.id.toString() === categoriaId);
        
        console.log(`ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Â modalidades.js - Modalidade ${item.nome}: categoria_id ${item.categoria_id}, categoria encontrada:`, categoria, 'match?', matchCategoria);
        return matchCategoria;
    });
    
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã…Â  modalidades.js - Total de modalidades apÃƒÆ’Ã‚Â³s filtro:', modalidadesFiltradas.length);
    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã¢â‚¬Â¹ modalidades.js - Modalidades filtradas:', modalidadesFiltradas);
    
    renderizarModalidades();
} 
