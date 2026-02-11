// =====================================================
// GESTÃO DE KITS DO EVENTO - JAVASCRIPT
// =====================================================

// Função global para selecionar/deselecionar todas as modalidades (definida no início para garantir disponibilidade)
function selecionarTodasModalidades() {
    const container = document.getElementById('kit-modalidades-container');
    const checkboxes = container.querySelectorAll('.modalidade-checkbox');
    
    if (checkboxes.length === 0) return;
    
    const todasMarcadas = Array.from(checkboxes).every(cb => cb.checked);
    
    // Se todas estão marcadas, desmarcar todas. Caso contrário, marcar todas
    checkboxes.forEach(cb => {
        cb.checked = !todasMarcadas;
    });
    
    atualizarTextoBotaoSelecionarTodas();
}

// Garantir que a função esteja disponível globalmente
window.selecionarTodasModalidades = selecionarTodasModalidades;

// Função para montar o caminho da imagem do kit (igual ao template)
function montarCaminhoImagemKit(nome, ext = 'png') {
    // Remove espaços extras e caracteres especiais do nome
    let nomeFormatado = nome.trim().replace(/\s+/g, ' ');
    // Codifica para URL
    nomeFormatado = encodeURIComponent(nomeFormatado);
    return `/frontend/assets/img/kits/${nomeFormatado}.${ext}`;
}

function getBasePath() {
    const pathname = (window.location.pathname || '').replace(/\\/g, '/');
    const origin = window.location.origin; // http://movamazon.com.br ou http://localhost
    
    // Encontra 'frontend' no pathname
    const idx = pathname.indexOf('frontend');
    
    if (idx !== -1) {
        // Retorna URL completa: origin + caminho até e incluindo 'frontend'
        // Exemplo: http://movamazon.com.br/frontend
        // Exemplo: http://localhost/movamazon/frontend
        return origin + pathname.substring(0, idx + 'frontend'.length);
    }
    
    // Fallback: caminho relativo baseado na profundidade
    const pathParts = pathname.split('/').filter(p => p && p !== 'index.php');
    const depth = pathParts.length;
    return '../'.repeat(Math.max(0, depth - 1)) || '../../';
}

function encodeFilename(filename) {
    if (!filename) return filename;
    return filename.includes('%') ? filename : encodeURIComponent(filename);
}

function resolverImagemKit(fotoKit) {
    if (!fotoKit) return '';
    if (fotoKit.startsWith('http')) return fotoKit;

    const basePath = getBasePath();
    const cleaned = fotoKit.replace(/^\/+/, '');
    
    // Se basePath é URL completa (começa com http:// ou https://)
    if (basePath.startsWith('http://') || basePath.startsWith('https://')) {
        // URL completa: adiciona /assets/img/kits/...
        const separator = basePath.endsWith('/') ? '' : '/';
        return `${basePath}${separator}assets/img/kits/${encodeFilename(cleaned)}`;
    }
    
    // Se basePath é caminho relativo (../../)
    if (basePath.startsWith('../') || basePath.startsWith('./')) {
        return `${basePath}assets/img/kits/${encodeFilename(cleaned)}`;
    }
    
    // Fallback: assume caminho absoluto relativo à raiz
    return `/frontend/assets/img/kits/${encodeFilename(cleaned)}`;
}

function resolverImagemKitAlternativa(fotoKit) {
    if (!fotoKit) return '';
    const basePath = getBasePath();
    const cleaned = fotoKit.replace(/^\/+/, '');
    const lower = cleaned.toLowerCase();

    if (lower.endsWith('.jpeg') || lower.endsWith('.jpg')) {
        const semExt = cleaned.replace(/\.(jpeg|jpg)$/i, '');
        // Garante que não tenha barra dupla
        const separator = basePath && !basePath.endsWith('/') ? '/' : '';
        return `${basePath}${separator}frontend/assets/img/kits/${encodeFilename(semExt)}.png`;
    }

    return '';
}

let kits = [];
let templates = [];
let eventos = [];
let modalidades = [];
let produtos = [];
let paginaAtual = 1;
let itensPorPagina = 6;
let filtros = {
    evento: '',
    modalidade: '',
    status: ''
};

// =====================================================
// INICIALIZAÇÃO
// =====================================================

document.addEventListener('DOMContentLoaded', function () {
    try {
        carregarKits();
    } catch (e) {
        console.error('[carregarKits] Erro:', e);
    }
    try {
        carregarDadosIniciais();
    } catch (e) {
        console.error('[carregarDadosIniciais] Erro:', e);
    }
    try {
        configurarEventListeners();
    } catch (e) {
        console.error('[configurarEventListeners] Erro:', e);
    }
});

function configurarEventListeners() {
    // Botões
    const btnAplicarTemplate = document.getElementById('btnAplicarTemplate');
    if (!btnAplicarTemplate) console.warn('[DOM] btnAplicarTemplate não encontrado');
    else btnAplicarTemplate.addEventListener('click', abrirModalAplicarTemplate);
    /*  const btnNovoKit = document.getElementById('btnNovoKit');
     if (!btnNovoKit) console.warn('[DOM] btnNovoKit não encontrado');
     else btnNovoKit.addEventListener('click', abrirModalKit); */

    // Botão selecionar todas modalidades (usar delegação de eventos para funcionar mesmo após recarregar modalidades)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('#btn-selecionar-todas-modalidades');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof selecionarTodasModalidades === 'function') {
                selecionarTodasModalidades();
            } else {
                console.error('Função selecionarTodasModalidades não está disponível');
            }
        }
    });

    // Filtros
    const filtroEvento = document.getElementById('filtroEvento');
    if (!filtroEvento) console.warn('[DOM] filtroEvento não encontrado');
    else filtroEvento.addEventListener('change', function () {
        const eventoId = this.value;
        if (eventoId) {
            carregarModalidadesFiltro(eventoId).then(() => {
                aplicarFiltros();
            });
        } else {
            const filtroModalidade = document.getElementById('filtroModalidade');
            if (!filtroModalidade) console.warn('[DOM] filtroModalidade não encontrado');
            else {
                filtroModalidade.innerHTML = '<option value="">Todas as modalidades</option>';
                filtroModalidade.disabled = true;
            }
            // Se não há evento selecionado, mostrar todos os kits
            paginaAtual = 1;
            renderizarKits();
            atualizarResumo(kits);
        }
    });
    const filtroModalidade = document.getElementById('filtroModalidade');
    if (!filtroModalidade) console.warn('[DOM] filtroModalidade não encontrado');
    else filtroModalidade.addEventListener('change', aplicarFiltros);
    const filtroStatus = document.getElementById('filtroStatus');
    if (!filtroStatus) console.warn('[DOM] filtroStatus não encontrado');
    else filtroStatus.addEventListener('change', aplicarFiltros);

    // Paginação
    const btnAnterior = document.getElementById('btn-anterior');
    if (!btnAnterior) console.warn('[DOM] btn-anterior não encontrado');
    else btnAnterior.addEventListener('click', () => {
        if (paginaAtual > 1) {
            paginaAtual--;
            renderizarKits();
        }
    });

    const btnProximo = document.getElementById('btn-proximo');
    if (!btnProximo) console.warn('[DOM] btn-proximo não encontrado');
    else btnProximo.addEventListener('click', () => {
        const totalPaginas = Math.ceil(kits.length / itensPorPagina);
        if (paginaAtual < totalPaginas) {
            paginaAtual++;
            renderizarKits();
        }
    });

    // Formulários
    const formAplicarTemplate = document.getElementById('formAplicarTemplate');
    if (!formAplicarTemplate) console.warn('[DOM] formAplicarTemplate não encontrado');
    else formAplicarTemplate.addEventListener('submit', aplicarTemplate);
    const formKit = document.getElementById('formKit');
    if (!formKit) console.warn('[DOM] formKit não encontrado');
    else formKit.addEventListener('submit', salvarKit);

    // Reaplicar template no kit (edição)
    const btnReaplicarTemplateKit = document.getElementById('btnReaplicarTemplateKit');
    if (!btnReaplicarTemplateKit) console.warn('[DOM] btnReaplicarTemplateKit não encontrado');
    else btnReaplicarTemplateKit.addEventListener('click', reaplicarTemplateNoKit);

    // Eventos dependentes
    const eventoIdEl = document.getElementById('evento_id');
    if (!eventoIdEl) console.warn('[DOM] evento_id não encontrado');
    else eventoIdEl.addEventListener('change', carregarModalidadesEvento);
    const kitEventoIdEl = document.getElementById('kit_evento_id');
    if (!kitEventoIdEl) console.warn('[DOM] kit_evento_id não encontrado');
    else kitEventoIdEl.addEventListener('change', carregarModalidadesKit);
}

// =====================================================
// CARREGAMENTO DE DADOS
// =====================================================

async function carregarKits() {
    mostrarLoading();

    try {
        console.log('🔍 DEBUG kits-evento.js - Carregando kits...');
        const response = await fetch('../../../api/organizador/kits-evento/list.php');
        console.log('🔍 DEBUG kits-evento.js - Response status:', response.status);
        const data = await response.json();
        console.log('🔍 DEBUG kits-evento.js - Dados recebidos:', data);

        if (data.success) {
            kits = data.data;
            console.log('🔍 DEBUG kits-evento.js - Kits carregados:', kits.length);

            // Verificar se há evento_id na URL ou no filtro
            const urlParams = new URLSearchParams(window.location.search);
            const eventoIdUrl = urlParams.get('evento_id');
            const filtroEvento = document.getElementById('filtroEvento');
            
            if (eventoIdUrl && filtroEvento) {
                filtroEvento.value = eventoIdUrl;
                setTimeout(() => {
                    carregarModalidadesFiltro(eventoIdUrl).then(() => {
                        aplicarFiltros();
                    });
                }, 100);
            } else if (filtroEvento && filtroEvento.value) {
                aplicarFiltros();
            } else {
                // Se não há filtro, mostrar todos os kits
                paginaAtual = 1;
                renderizarKits();
                atualizarResumo(kits);
            }
        } else {
            console.error('Erro na API:', data.error);
            mostrarErro('Erro ao carregar kits: ' + data.error);
        }
    } catch (error) {
        console.error('Erro na requisição:', error);
        mostrarErro('Erro ao carregar kits');
    } finally {
        ocultarLoading();
    }
}

async function carregarDadosIniciais() {
    try {
        // Carregar eventos
        const responseEventos = await fetch('../../../api/organizador/eventos/list.php');
        const dataEventos = await responseEventos.json();
        if (dataEventos.success) {
            eventos = dataEventos.data.eventos; // Corrigido: acessar data.data.eventos
            preencherSelectEventos();
        }

        // Carregar templates
        const responseTemplates = await fetch('../../../api/organizador/kits-templates/list.php');
        const dataTemplates = await responseTemplates.json();
        if (dataTemplates.success) {
            templates = dataTemplates.data;
            preencherSelectTemplates();
        }

        // Carregar produtos
        const responseProdutos = await fetch('../../../api/organizador/produtos/list.php');
        const dataProdutos = await responseProdutos.json();
        if (dataProdutos.success) {
            produtos = dataProdutos.data;
        }
    } catch (error) {
        console.error('Erro ao carregar dados iniciais:', error);
    }
}

function preencherSelectEventos() {
    const selectFiltro = document.getElementById('filtroEvento');
    const selectModal = document.getElementById('evento_id');
    const selectKit = document.getElementById('kit_evento_id');

    // Limpar selects mantendo apenas a primeira opção
    selectFiltro.innerHTML = '<option value="">Todos os eventos</option>';
    selectModal.innerHTML = '<option value="">Selecione um evento</option>';
    selectKit.innerHTML = '<option value="">Selecione um evento</option>';

    eventos.forEach(evento => {
        const option = document.createElement('option');
        option.value = evento.id;
        option.textContent = evento.nome;

        selectFiltro.appendChild(option.cloneNode(true));
        selectModal.appendChild(option.cloneNode(true));
        selectKit.appendChild(option.cloneNode(true));
    });
}

function preencherSelectTemplates() {
    const select = document.getElementById('template_id');

    templates.forEach(template => {
        const option = document.createElement('option');
        option.value = template.id;
        option.textContent = `${template.nome} - R$ ${parseFloat(template.preco_base).toFixed(2)}`;
        select.appendChild(option);
    });
}

function preencherSelectTemplatesKitEdicao() {
    const select = document.getElementById('kit_template_id');
    if (!select) return;

    // Reset mantendo placeholder
    select.innerHTML = '<option value="">Selecione um template</option>';

    templates.forEach(template => {
        const option = document.createElement('option');
        option.value = template.id;
        option.textContent = `${template.nome} - R$ ${parseFloat(template.preco_base).toFixed(2)}`;
        select.appendChild(option);
    });
}

async function carregarModalidadesEvento() {
    const eventoId = document.getElementById('evento_id').value;

    if (!eventoId) return;

    try {
        const response = await fetch(`../../../api/organizador/modalidades/list.php?evento_id=${eventoId}`);

        const data = await response.json();


        if (data.success) {
            modalidades = data.modalidades;

            preencherModalidades();
        } else {
            console.warn('[carregarModalidadesEvento] Falha ao carregar modalidades:', data);
        }
    } catch (error) {
        console.error('Erro ao carregar modalidades:', error);
    }
}


async function carregarModalidadesKit(marcarIds = []) {
    const eventoId = document.getElementById('kit_evento_id').value;
    const container = document.getElementById('kit-modalidades-container');
    const btnSelecionarTodas = document.getElementById('btn-selecionar-todas-modalidades');
    
    container.innerHTML = '<div class="text-gray-400 text-sm">Carregando modalidades...</div>';
    if (btnSelecionarTodas) btnSelecionarTodas.style.display = 'none';

    if (!eventoId) {
        container.innerHTML = '<div class="text-gray-400 text-sm">Selecione um evento para ver as modalidades.</div>';
        if (btnSelecionarTodas) btnSelecionarTodas.style.display = 'none';
        return;
    }

    try {
        const response = await fetch(`../../../api/organizador/modalidades/list.php?evento_id=${eventoId}`);
        const data = await response.json();
        container.innerHTML = '';

        if (data.success && Array.isArray(data.modalidades) && data.modalidades.length > 0) {
            // Normalizar IDs para comparação (todos como strings e números)
            const marcarIdsStr = marcarIds.map(id => String(id));
            const marcarIdsNum = marcarIds.map(id => Number(id));
            
            console.log('🔍 DEBUG - IDs para marcar:', marcarIdsStr);
            console.log('🔍 DEBUG - Modalidades recebidas:', data.modalidades.map(m => ({ id: m.id, idStr: String(m.id) })));
            
            data.modalidades.forEach(modalidade => {
                const div = document.createElement('div');
                div.className = 'flex items-center space-x-2';
                const modalidadeIdStr = String(modalidade.id);
                const modalidadeIdNum = Number(modalidade.id);
                // Verificar tanto como string quanto como número
                const isChecked = marcarIdsStr.includes(modalidadeIdStr) || 
                                 marcarIdsNum.includes(modalidadeIdNum) ||
                                 marcarIds.includes(modalidade.id);
                
                console.log(`🔍 DEBUG - Modalidade ${modalidade.id}: isChecked=${isChecked}`);
                
                div.innerHTML = `
                    <input type="checkbox" name="modalidades[]" id="kit_modalidade_${modalidade.id}" value="${modalidade.id}" class="modalidade-checkbox rounded border-gray-300 text-primary-600 focus:ring-primary-500" ${isChecked ? 'checked' : ''}>
                    <label for="kit_modalidade_${modalidade.id}" class="text-sm text-gray-700">${modalidade.categoria_nome} - ${modalidade.nome}</label>
                `;
                container.appendChild(div);
            });

            // Mostrar botão "Selecionar todas" se houver modalidades
            if (btnSelecionarTodas) {
                btnSelecionarTodas.style.display = 'block';
                atualizarTextoBotaoSelecionarTodas();
            }
        } else {
            container.innerHTML = '<div class="text-gray-400 text-sm">Nenhuma modalidade encontrada para este evento.</div>';
            if (btnSelecionarTodas) btnSelecionarTodas.style.display = 'none';
        }
    } catch (error) {
        console.error('Erro ao carregar modalidades:', error);
        container.innerHTML = '<div class="text-red-500 text-sm">Erro ao carregar modalidades.</div>';
        if (btnSelecionarTodas) btnSelecionarTodas.style.display = 'none';
    }
}

function atualizarTextoBotaoSelecionarTodas() {
    const container = document.getElementById('kit-modalidades-container');
    const checkboxes = container.querySelectorAll('.modalidade-checkbox');
    const btnSelecionarTodas = document.getElementById('btn-selecionar-todas-modalidades');
    const textoBtn = document.getElementById('btn-selecionar-todas-texto');
    
    if (!btnSelecionarTodas || checkboxes.length === 0) return;
    
    const todasMarcadas = Array.from(checkboxes).every(cb => cb.checked);
    const algumasMarcadas = Array.from(checkboxes).some(cb => cb.checked) && !todasMarcadas;
    
    const novoTexto = todasMarcadas ? 'Deselecionar todas' : 'Selecionar todas';
    
    if (textoBtn) {
        textoBtn.textContent = novoTexto;
    } else {
        btnSelecionarTodas.innerHTML = `<i class="fas fa-check-square mr-1"></i>${novoTexto}`;
    }
}




function preencherModalidades() {
    const container = document.getElementById('modalidades-container');
    container.innerHTML = '';

    modalidades.forEach(modalidade => {
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-2';
        div.innerHTML = `
            <input type="checkbox" id="modalidade_${modalidade.id}" value="${modalidade.id}" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
            <label for="modalidade_${modalidade.id}" class="text-sm text-gray-700">${modalidade.categoria_nome} - ${modalidade.nome}</label>
        `;
        container.appendChild(div);
    });
}

// =====================================================
// RENDERIZAÇÃO
// =====================================================

function mostrarMensagemInicial() {
    const container = document.getElementById('kits-container');
    container.innerHTML = `
        <div class="col-span-full text-center py-12">
            <i class="fas fa-calendar-alt text-gray-400 text-4xl mb-4"></i>
            <p class="text-gray-500 text-lg">Selecione um evento para visualizar os kits</p>
            <p class="text-gray-400">Use o filtro acima para escolher um evento específico</p>
        </div>
    `;

    // Zerar resumo inicial
    atualizarResumo([]);
}

function renderizarKits(kitsParaRenderizar = kits) {
    const container = document.getElementById('kits-container');
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const fim = inicio + itensPorPagina;
    const kitsPaginados = kitsParaRenderizar.slice(inicio, fim);

    container.innerHTML = '';

    if (kitsPaginados.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-12">
                <i class="fas fa-gift text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500 text-lg">Nenhum kit encontrado</p>
                <p class="text-gray-400">Crie seu primeiro kit ou aplique um template</p>
            </div>
        `;
        return;
    }

    kitsPaginados.forEach(kit => {
        const card = criarCardKit(kit);
        container.appendChild(card);
    });

    atualizarPaginacao(kitsParaRenderizar.length);
}

function criarCardKit(kit) {
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow border border-gray-200';

    const statusClass = kit.ativo ? 'green' : 'red';
    const statusText = kit.ativo ? 'Ativo' : 'Inativo';
    const disponivelVenda = kit.disponivel_venda ?
        '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Disponível para venda</span>' :
        '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Apenas em kit</span>';

    const fotoSrc = resolverImagemKit(kit.foto_kit);
    const fotoAltSrc = resolverImagemKitAlternativa(kit.foto_kit);
    const basePath = getBasePath();
    const separator = basePath && !basePath.endsWith('/') ? '/' : '';
    const placeholderSrc = `${basePath}${separator}frontend/assets/img/kits/placeholder.png`;
    let fotoHtml = fotoSrc ?
        `<img src="${fotoSrc}" alt="${kit.nome}" class="w-full h-32 object-cover rounded-t-lg"${fotoAltSrc ? ` data-alt-src="${fotoAltSrc}"` : ''} onerror="if(this.dataset.altSrc){this.src=this.dataset.altSrc;delete this.dataset.altSrc;return;}this.onerror=null;this.src='${placeholderSrc}';">` :
        `<div class="w-full h-32 bg-gray-200 rounded-t-lg flex items-center justify-center">
            <i class="fas fa-gift text-gray-400 text-2xl"></i>
        </div>`;

    card.innerHTML = `
        ${fotoHtml}
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">${kit.nome}</h3>
                    <p class="text-sm text-gray-500">${kit.evento_nome}</p>
                    <p class="text-sm text-gray-500">${kit.modalidade_nome}</p>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${statusClass}-100 text-${statusClass}-800">
                    ${statusText}
                </span>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Valor:</span>
                    <span class="font-semibold text-green-600">R$ ${parseFloat(kit.valor).toFixed(2)}</span>
                </div>
                ${disponivelVenda}
            </div>
            
            <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                <button onclick="editarKit(${kit.id})" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                    <i class="fas fa-edit mr-1"></i>Editar
                </button>
                <button onclick="excluirKit(${kit.id})" class="text-red-600 hover:text-red-900 text-sm font-medium">
                    <i class="fas fa-trash mr-1"></i>Excluir
                </button>
            </div>
        </div>
    `;

    return card;
}

// =====================================================
// FILTROS E PAGINAÇÃO
// =====================================================

function filtrarPorEvento() {
    const eventoId = document.getElementById('filtroEvento').value;
    const selectModalidade = document.getElementById('filtroModalidade');

    if (eventoId) {
        // Carregar modalidades do evento
        carregarModalidadesFiltro(eventoId).then(() => {
            selectModalidade.disabled = false;
            aplicarFiltros();
        });
    } else {
        selectModalidade.disabled = true;
        selectModalidade.innerHTML = '<option value="">Todas as modalidades</option>';
        aplicarFiltros();
    }
}


function aplicarFiltros() {
    const eventoId = document.getElementById('filtroEvento').value;
    const modalidadeId = document.getElementById('filtroModalidade').value;
    const status = document.getElementById('filtroStatus').value;

    // Aplicar filtros aos kits (NÃO sobrescrever a lista global)
    let kitsFiltrados = kits.slice();

    if (eventoId) {
        kitsFiltrados = kitsFiltrados.filter(kit => kit.evento_id == eventoId);
    }

    if (modalidadeId) {
        kitsFiltrados = kitsFiltrados.filter(kit => kit.modalidade_evento_id == modalidadeId);
    }

    if (status) {
        if (status === 'ativo') {
            kitsFiltrados = kitsFiltrados.filter(kit => kit.ativo === true || kit.ativo === 1);
        } else if (status === 'inativo') {
            kitsFiltrados = kitsFiltrados.filter(kit => kit.ativo === false || kit.ativo === 0);
        }
    }

    paginaAtual = 1;
    renderizarKits(kitsFiltrados);
    atualizarResumo(kitsFiltrados);
}

function atualizarPaginacao(totalKits = kits.length) {
    const totalPaginas = Math.ceil(totalKits / itensPorPagina);
    const inicio = (paginaAtual - 1) * itensPorPagina + 1;
    const fim = Math.min(paginaAtual * itensPorPagina, totalKits);

    // Atualizar botões
    document.getElementById('btn-anterior').disabled = paginaAtual === 1;
    document.getElementById('btn-proximo').disabled = paginaAtual === totalPaginas;

    // Mostrar/ocultar paginação
    const paginacao = document.getElementById('paginacao');
    if (totalPaginas > 1) {
        paginacao.style.display = 'flex';
    } else {
        paginacao.style.display = 'none';
    }
}

// =====================================================
// MODAIS
// =====================================================


function abrirModalAplicarTemplate() {
    document.getElementById('modalAplicarTemplate').classList.remove('hidden');
    // Buscar modalidades do evento selecionado
    const eventoId = document.getElementById('evento_id').value;
    const container = document.getElementById('modalidades-container');
    container.innerHTML = '<div class="text-gray-400 text-sm">Carregando modalidades...</div>';

    if (eventoId) {
        fetch(`../../../api/organizador/modalidades/list.php?evento_id=${eventoId}`)
            .then(res => res.json())
            .then(data => {
                console.log("Dados recebidos na modal:", data); // DEBUG
                container.innerHTML = '';

                if (data.success && Array.isArray(data.modalidades) && data.modalidades.length > 0) {
                    data.modalidades.forEach((modalidade) => {
                        const div = document.createElement('div');
                        div.className = 'flex items-center space-x-2';
                        div.innerHTML = `
                            <input type="checkbox" name="modalidades[]" id="modalidade_${modalidade.id}" value="${modalidade.id}" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <label for="modalidade_${modalidade.id}" class="text-sm text-gray-700">${modalidade.categoria_nome} - ${modalidade.nome}</label>
                        `;
                        container.appendChild(div);
                    });
                } else {
                    container.innerHTML = '<div class="text-gray-400 text-sm">Nenhuma modalidade encontrada para este evento.</div>';
                }
            })
            .catch(() => {
                container.innerHTML = '<div class="text-red-500 text-sm">Erro ao carregar modalidades.</div>';
            });
    } else {
        container.innerHTML = '<div class="text-gray-400 text-sm">Selecione um evento para ver as modalidades.</div>';
    }
}


function fecharModalAplicarTemplate() {
    document.getElementById('modalAplicarTemplate').classList.add('hidden');
    document.getElementById('formAplicarTemplate').reset();
}

// Variável global para armazenar modalidades do kit em edição
let modalidadesKitEdicao = [];

function abrirModalKit(kit = null) {
    preencherSelectEventos(); // Garante que o select de eventos está sempre atualizado
    preencherSelectTemplatesKitEdicao(); // Garante que o select de templates está sempre atualizado
    const modal = document.getElementById('modalKit');
    const titulo = document.getElementById('modalKitTitulo');
    const btnTexto = document.getElementById('btnSalvarKitTexto');
    const form = document.getElementById('formKit');
    const selectEvento = document.getElementById('kit_evento_id');
    
    // Limpar modalidades de edição anterior
    modalidadesKitEdicao = [];
    
    // Remover listeners anteriores para evitar duplicação
    const novoSelectEvento = selectEvento.cloneNode(true);
    selectEvento.parentNode.replaceChild(novoSelectEvento, selectEvento);
    
    // Adicionar listener para recarregar modalidades ao trocar evento
    const novoSelect = document.getElementById('kit_evento_id');
    let eventoAnterior = kit ? kit.evento_id : null;
    novoSelect.addEventListener('change', function() {
        const eventoAtual = this.value;
        // Se há modalidades salvas da edição e o evento não mudou, usar elas
        if (modalidadesKitEdicao.length > 0 && eventoAnterior && eventoAtual == eventoAnterior) {
            carregarModalidadesKit(modalidadesKitEdicao);
        } else {
            // Se mudou o evento, limpar modalidades salvas
            modalidadesKitEdicao = [];
            carregarModalidadesKit();
        }
        eventoAnterior = eventoAtual;
    });
    
    // Adicionar listener delegado no modal para atualizar texto do botão quando checkboxes mudarem
    // Usar delegação de eventos no modal para funcionar mesmo após recarregar modalidades
    modal.addEventListener('change', function(e) {
        if (e.target.classList.contains('modalidade-checkbox')) {
            atualizarTextoBotaoSelecionarTodas();
        }
    });
    
    if (kit) {
        titulo.textContent = 'Editar Kit';
        btnTexto.textContent = 'Atualizar Kit';
        preencherFormularioKit(kit);
    } else {
        titulo.textContent = 'Novo Kit';
        btnTexto.textContent = 'Criar Kit';
        form.reset();
        document.getElementById('kit_id').value = '';
        const selectTemplate = document.getElementById('kit_template_id');
        if (selectTemplate) selectTemplate.value = '';
        carregarModalidadesKit();
    }
    modal.classList.remove('hidden');
}

function fecharModalKit() {
    document.getElementById('modalKit').classList.add('hidden');
}

function preencherFormularioKit(kit) {
    document.getElementById('kit_id').value = kit.id;
    document.getElementById('kit_evento_id').value = kit.evento_id;
    document.getElementById('kit_nome').value = kit.nome;
    document.getElementById('kit_valor').value = kit.valor;
    document.getElementById('kit_descricao').value = kit.descricao || '';
    document.getElementById('kit_disponivel_venda').checked = kit.disponivel_venda;
    const selectTemplate = document.getElementById('kit_template_id');
    if (selectTemplate) {
        selectTemplate.value = kit.kit_template_id ? String(kit.kit_template_id) : '';
    }
    
    // Converter modalidades para array de IDs (normalizar para números e strings)
    let modalidadesIds = [];
    if (Array.isArray(kit.modalidades) && kit.modalidades.length > 0) {
        modalidadesIds = kit.modalidades.map(id => {
            // Normalizar: converter para número primeiro, depois para string
            const numId = Number(id);
            return isNaN(numId) ? String(id) : String(numId);
        });
    }
    
    console.log('🔍 DEBUG preencherFormularioKit - Kit modalidades originais:', kit.modalidades);
    console.log('🔍 DEBUG preencherFormularioKit - Modalidades normalizadas:', modalidadesIds);
    
    // Salvar modalidades globalmente para uso no listener do select
    modalidadesKitEdicao = modalidadesIds;
    
    // Carregar modalidades após um pequeno delay para garantir que o select foi atualizado
    setTimeout(() => {
        carregarModalidadesKit(modalidadesIds);
    }, 100);
}

async function reaplicarTemplateNoKit() {
    const kitId = document.getElementById('kit_id')?.value;
    const templateId = document.getElementById('kit_template_id')?.value;

    if (!kitId) {
        mostrarErro('Para reaplicar um template, primeiro selecione um kit existente (modo edição).');
        return;
    }
    if (!templateId) {
        mostrarErro('Selecione um template para reaplicar.');
        return;
    }

    const template = templates.find(t => String(t.id) === String(templateId));
    const templateNome = template?.nome || 'Template selecionado';

    const result = await Swal.fire({
        title: 'Reaplicar Template?',
        text: `Isso vai sobrescrever no kit apenas os dados do template (produtos, foto, valor e disponibilidade). Template: ${templateNome}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Sim, reaplicar',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch('../../../api/organizador/kits-evento/reaplicar-template.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                kit_id: Number(kitId),
                template_id: Number(templateId)
            })
        });

        const data = await response.json();

        if (data.success) {
            // Atualizar campos sobrescritos no formulário (sem mexer em nome/descrição/modalidades)
            const valorEl = document.getElementById('kit_valor');
            const disponivelEl = document.getElementById('kit_disponivel_venda');
            if (valorEl && data.data && typeof data.data.valor !== 'undefined') {
                valorEl.value = data.data.valor;
            } else if (valorEl && template && typeof template.preco_base !== 'undefined') {
                valorEl.value = template.preco_base;
            }
            if (disponivelEl && data.data && typeof data.data.disponivel_venda !== 'undefined') {
                disponivelEl.checked = !!data.data.disponivel_venda;
            } else if (disponivelEl && template && typeof template.disponivel_venda !== 'undefined') {
                disponivelEl.checked = !!template.disponivel_venda;
            }

            await carregarKits();
            Swal.fire({
                icon: 'success',
                title: 'Template reaplicado!',
                text: data.message || 'Template reaplicado com sucesso.',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            mostrarErro('Erro: ' + (data.error || 'Falha ao reaplicar template'));
        }
    } catch (error) {
        console.error('Erro ao reaplicar template:', error);
        mostrarErro('Erro ao reaplicar template');
    }
}


// =====================================================
// CRUD OPERATIONS
// =====================================================

async function aplicarTemplate(e) {
    e.preventDefault();

    const templateId = document.getElementById('template_id').value;
    const eventoId = document.getElementById('evento_id').value;
    const modalidadesSelecionadas = Array.from(document.querySelectorAll('#modalidades-container input[type="checkbox"]:checked'))
        .map(cb => cb.value);

    if (!templateId || !eventoId || modalidadesSelecionadas.length === 0) {
        mostrarErro('Por favor, preencha todos os campos obrigatórios');
        return;
    }

    try {
        const response = await fetch('../../../api/organizador/kits-evento/aplicar-template.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                template_id: templateId,
                evento_id: eventoId,
                modalidades: modalidadesSelecionadas
            })
        });

        const data = await response.json();

        if (data.success) {
            fecharModalAplicarTemplate();
            carregarKits();
            mostrarSucesso(`Template aplicado com sucesso a ${modalidadesSelecionadas.length} modalidade(s)!`);
        } else {
            mostrarErro('Erro: ' + data.error);
        }
    } catch (error) {
        console.error('Erro ao aplicar template:', error);
        mostrarErro('Erro ao aplicar template');
    }
}

async function salvarKit(e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('nome', document.getElementById('kit_nome').value);
    formData.append('descricao', document.getElementById('kit_descricao').value);
    formData.append('valor', document.getElementById('kit_valor').value);
    formData.append('evento_id', document.getElementById('kit_evento_id').value);
    formData.append('disponivel_venda', document.getElementById('kit_disponivel_venda').checked ? '1' : '0');

    // Coletar modalidades marcadas
    const modalidadesSelecionadas = Array.from(document.querySelectorAll('#kit-modalidades-container input[type=checkbox]:checked')).map(cb => cb.value);
    formData.append('modalidades', JSON.stringify(modalidadesSelecionadas));

    const kitId = document.getElementById('kit_id').value;
    if (kitId) {
        formData.append('id', kitId);
    }


    try {
        const url = kitId ? '../../../api/organizador/kits-evento/update.php' : '../../../api/organizador/kits-evento/create.php';
        const method = kitId ? 'POST' : 'POST';

        const response = await fetch(url, {
            method: method,
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            fecharModalKit();
            carregarKits();
            mostrarSucesso(kitId ? 'Kit atualizado com sucesso!' : 'Kit criado com sucesso!');
        } else {
            mostrarErro('Erro: ' + data.error);
        }
    } catch (error) {
        console.error('Erro ao salvar kit:', error);
        mostrarErro('Erro ao salvar kit');
    }
}

async function editarKit(id) {
    const kit = kits.find(k => k.id === id);
    if (kit) {
        abrirModalKit(kit);
    }
}

async function excluirKit(id) {
    const result = await Swal.fire({
        title: 'Tem certeza?',
        text: 'Deseja realmente excluir este kit? Esta ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        const response = await fetch('../../../api/organizador/kits-evento/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: id
            })
        });

        const data = await response.json();

        if (data.success) {
            kits = kits.filter(k => k.id !== id);
            const eventoId = document.getElementById('filtroEvento').value;
            if (eventoId) {
                aplicarFiltros();
            } else {
                paginaAtual = 1;
                renderizarKits();
                atualizarResumo();
            }
            mostrarSucesso('Kit excluído com sucesso!');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: data.error || 'Erro ao excluir kit'
            });
        }
    } catch (error) {
        console.error('Erro ao excluir kit:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao excluir kit'
        });
    }
}

// =====================================================
// RESUMO E UTILITÁRIOS
// =====================================================

function atualizarResumo(kitsParaResumo = kits) {
    const total = kitsParaResumo.length;
    const ativos = kitsParaResumo.filter(k => k.ativo === true || k.ativo === 1).length;
    const valorTotal = kitsParaResumo.reduce((sum, k) => sum + parseFloat(k.valor || 0), 0);
    const modalidadesComKit = new Set(kitsParaResumo.map(k => k.modalidade_evento_id).filter(id => id)).size;

    document.getElementById('total-kits').textContent = total;
    document.getElementById('kits-ativos').textContent = ativos;
    document.getElementById('valor-total').textContent = `R$ ${valorTotal.toFixed(2)}`;
    document.getElementById('modalidades-com-kit').textContent = modalidadesComKit;
}

function mostrarLoading() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('kits-container').style.display = 'none';
    document.getElementById('error-message').style.display = 'none';
}

function ocultarLoading() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('kits-container').style.display = 'grid';
}

function mostrarErro(mensagem) {
    Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: mensagem
    });
}

function mostrarSucesso(mensagem) {
    Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: mensagem,
        timer: 2000,
        showConfirmButton: false
    });
}

// Função para carregar modalidades do filtro
async function carregarModalidadesFiltro(eventoId) {
    try {
        const response = await fetch(`../../../api/organizador/modalidades/list.php?evento_id=${eventoId}`);
        const data = await response.json();
        console.log("Dados da API:", data);

        if (data.success) {
            const select = document.getElementById('filtroModalidade');
            select.innerHTML = '<option value="">Todas as modalidades</option>';

            data.modalidades.forEach(modalidade => {
                const option = document.createElement('option');
                option.value = modalidade.id;
                option.textContent = `${modalidade.categoria_nome} - ${modalidade.nome}`;
                select.appendChild(option);
            });

            select.disabled = false;
            return Promise.resolve();
        } else {
            throw new Error(data.message || 'Erro ao carregar modalidades');
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire('Erro', error.message, 'error');
        return Promise.reject(error);
    }
}