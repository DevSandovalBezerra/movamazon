if (window.getApiBase) { window.getApiBase(); }
// =====================================================
// GESTÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O DE TEMPLATES DE KIT - JAVASCRIPT
// =====================================================

let templates = [];
let produtos = [];
let paginaAtual = 1;
let itensPorPagina = 6;
let filtros = {
    nome: '',
    descricao: '',
    status: ''
};

function buildKitAssetUrl(path) {
    if (window.buildAssetUrl) {
        return window.buildAssetUrl(path);
    }
    const apiBase = window.API_BASE || '/api';
    const appBase = apiBase.replace(/\/api\/?$/, '');
    const clean = String(path || '').replace(/^\/+/, '');
    return (appBase ? appBase + '/' : '/') + clean;
}

// =====================================================
// INICIALIZAÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O
// =====================================================

document.addEventListener('DOMContentLoaded', function () {
    carregarTemplates();
    carregarProdutos();
    configurarEventListeners();
});

function configurarEventListeners() {
    // BotÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o novo template
    document.getElementById('btnNovoTemplate').addEventListener('click', abrirModalTemplate);

    // Filtros
    document.getElementById('filtroNome').addEventListener('input', aplicarFiltros);
    document.getElementById('filtroDescricao').addEventListener('input', aplicarFiltros);
    document.getElementById('filtroStatus').addEventListener('change', aplicarFiltros);

    // PaginaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
    document.getElementById('btn-anterior').addEventListener('click', () => {
        if (paginaAtual > 1) {
            paginaAtual--;
            renderizarTemplates();
        }
    });

    document.getElementById('btn-proximo').addEventListener('click', () => {
        const totalPaginas = Math.ceil(templates.length / itensPorPagina);
        if (paginaAtual < totalPaginas) {
            paginaAtual++;
            renderizarTemplates();
        }
    });

    // FormulÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
    document.getElementById('formTemplate').addEventListener('submit', salvarTemplate);

    // Preview de foto
    document.getElementById('template_foto').addEventListener('change', previewFotoTemplate);

    // CÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lculo automÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡tico de preÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§os
    document.getElementById('template_preco_base').addEventListener('input', calcularMargem);
}

// =====================================================
// CARREGAMENTO DE DADOS
// =====================================================

// Carregar eventos do organizador
async function carregarEventos() {
    try {
        const response = await fetch((window.API_BASE || '/api') + '/organizador/eventos/list.php');
        const data = await response.json();

        if (data.success) {
            eventos = data.data.eventos;
            preencherSelectEventos();
        } else {
            console.error('Erro ao carregar eventos:', data.error);
        }
    } catch (error) {
        console.error('Erro ao carregar eventos:', error);
    }
}

// Preencher select de eventos
function preencherSelectEventos() {
    const filtroSelect = document.getElementById('filtroEvento');
    if (filtroSelect) {
        filtroSelect.innerHTML = '<option value="">Todos os eventos</option>';
        eventos.forEach(evento => {
            const option = document.createElement('option');
            option.value = evento.id;
            option.textContent = evento.nome;
            filtroSelect.appendChild(option);
        });
    }

    const formSelect = document.getElementById('template_evento');
    if (formSelect) {
        formSelect.innerHTML = '<option value="">Template Global</option>';
        eventos.forEach(evento => {
            const option = document.createElement('option');
            option.value = evento.id;
            option.textContent = evento.nome;
            formSelect.appendChild(option);
        });
    }
}

async function carregarTemplates() {
    mostrarLoading();

    try {
        const response = await fetch((window.API_BASE || '/api') + '/organizador/kits-templates/list.php');
        const data = await response.json();

        if (data.success) {
            templates = data.data;
            renderizarTemplates();

            atualizarResumo();
        } else {
            // SweetAlert de erro
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao carregar templates: ' + data.error,
                confirmButtonText: 'OK',
                confirmButtonColor: '#EF4444'
            });
        }
    } catch (error) {
        console.error('Erro na requisiÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o:', error);

        // SweetAlert de erro de rede
        Swal.fire({
            icon: 'error',
            title: 'Erro de ConexÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o!',
            text: 'Erro ao carregar templates. Verifique sua conexÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#EF4444'
        });
    } finally {
        ocultarLoading();
    }
}

async function carregarProdutos() {
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Iniciando carregamento de produtos...');

    try {
        const url = (window.API_BASE || '/api') + '/organizador/produtos/list.php';
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - URL da API:', url);

        const response = await fetch(url);
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Response status:', response.status);

        const data = await response.json();
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Dados recebidos:', data);

        if (data.success) {
            produtos = data.data;
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produtos carregados:', produtos.length);
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Primeiro produto:', produtos[0]);
        } else {
            console.error('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Erro na API:', data.error);
        }
    } catch (error) {
        console.error('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Erro ao carregar produtos:', error);
    }
}

// =====================================================
// RENDERIZAÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O
// =====================================================

function renderizarTemplates(templatesParaRenderizar = null) {
    const container = document.getElementById('templates-container');
    const templatesParaUsar = templatesParaRenderizar || templates;
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const fim = inicio + itensPorPagina;
    const templatesPaginados = templatesParaUsar.slice(inicio, fim);

    container.innerHTML = '';

    if (templatesPaginados.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-12">
                <i class="fas fa-box-open text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500 text-lg">Nenhum template encontrado</p>
                <p class="text-gray-400">Crie seu primeiro template para comeÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ar</p>
            </div>
        `;
        return;
    }

    templatesPaginados.forEach(template => {
        const card = criarCardTemplate(template);
        container.appendChild(card);
    });

    atualizarPaginacao();
}

function criarCardTemplate(template) {
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow border border-gray-200';

    const statusClass = template.ativo ? 'green' : 'red';
    const statusText = template.ativo ? 'Ativo' : 'Inativo';
    const disponivelVenda = template.disponivel_venda ?
        '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">DisponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­vel para venda</span>' :
        '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Apenas em kit</span>';

    // Corrige o caminho da imagem do kit
    let fotoSrc = '';
    if (template.foto_kit) {
        // Usa resolverCaminhoFotoKit para garantir caminho correto
        fotoSrc = resolverCaminhoFotoKit(template.foto_kit);
    }
    const fotoHtml = fotoSrc ?
        `<img src="${fotoSrc}" alt="${template.nome}" class="w-[300] h-[150] object-contain rounded-t-lg">` :
        `<div class="w-full h-32 bg-gray-200 rounded-t-lg flex items-center justify-center">
            <i class="fas fa-box text-gray-400 text-2xl"></i>
        </div>`;

    card.innerHTML = `
        ${fotoHtml}
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">${template.nome}</h3>
                    <p class="text-sm text-gray-500">${template.descricao || 'Sem descriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o'}</p>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${statusClass}-100 text-${statusClass}-800">
                    ${statusText}
                </span>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">PreÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o Base:</span>
                    <span class="font-semibold text-green-600">R$ ${parseFloat(template.preco_base).toFixed(2)}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Produtos:</span>
                    <span class="font-semibold text-blue-600">${template.total_produtos || 0} ${template.total_produtos === 1 ? 'item' : 'itens'}</span>
                </div>
                ${disponivelVenda}
            </div>
            
            <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                <button onclick="editarTemplate(${template.id})" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                    <i class="fas fa-edit mr-1"></i>Editar
                </button>
                <button onclick="duplicarTemplate(${template.id})" class="text-green-600 hover:text-green-900 text-sm font-medium">
                    <i class="fas fa-copy mr-1"></i>Duplicar
                </button>
                <button onclick="excluirTemplate(${template.id})" class="text-red-600 hover:text-red-900 text-sm font-medium">
                    <i class="fas fa-trash mr-1"></i>Excluir
                </button>
            </div>
        </div>
    `;

    return card;
}

// =====================================================
// FILTROS E PAGINAÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O
// =====================================================

function aplicarFiltros() {
    filtros.nome = document.getElementById('filtroNome').value.toLowerCase();
    filtros.descricao = document.getElementById('filtroDescricao').value.toLowerCase();
    filtros.status = document.getElementById('filtroStatus').value;

    // Aplicar filtros localmente
    let templatesFiltrados = templates.slice();

    if (filtros.nome) {
        templatesFiltrados = templatesFiltrados.filter(template =>
            template.nome.toLowerCase().includes(filtros.nome)
        );
    }

    if (filtros.descricao) {
        templatesFiltrados = templatesFiltrados.filter(template =>
            template.descricao && template.descricao.toLowerCase().includes(filtros.descricao)
        );
    }

    if (filtros.status) {
        const isAtivo = filtros.status === 'ativo';
        templatesFiltrados = templatesFiltrados.filter(template =>
            template.ativo === (isAtivo ? 1 : 0)
        );
    }

    renderizarTemplates(templatesFiltrados);
}

function atualizarPaginacao() {
    const totalPaginas = Math.ceil(templates.length / itensPorPagina);
    const inicio = (paginaAtual - 1) * itensPorPagina + 1;
    const fim = Math.min(paginaAtual * itensPorPagina, templates.length);

    // Atualizar botÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes
    document.getElementById('btn-anterior').disabled = paginaAtual === 1;
    document.getElementById('btn-proximo').disabled = paginaAtual === totalPaginas;

    // Mostrar/ocultar paginaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
    const paginacao = document.getElementById('paginacao');
    if (totalPaginas > 1) {
        paginacao.style.display = 'flex';
    } else {
        paginacao.style.display = 'none';
    }
}

// =====================================================
// MODAL TEMPLATE
// =====================================================
// FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o para montar o caminho correto da imagem do kit
function montarCaminhoImagemKit(nome, extension = 'png') {
    // Usa encodeURIComponent para tratar espaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§os e acentos
    const nomeFormatado = encodeURIComponent(nome);
    const filename = `kit_template_${nomeFormatado}.${extension}`;
    return buildKitAssetUrl(`frontend/assets/img/kits/${filename}`);
}

function getBasePath() {
    const pathname = (window.location.pathname || '').replace(/\\/g, '/');
    const origin = window.location.origin; // http://movamazon.com.br ou http://localhost
    
    // Encontra 'frontend' no pathname
    const idx = pathname.indexOf('frontend');
    
    if (idx !== -1) {
        // Retorna URL completa: origin + caminho atÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© e incluindo 'frontend'
        // Exemplo: http://movamazon.com.br/frontend
        // Exemplo: http://localhost/movamazon/frontend
        return origin + pathname.substring(0, idx + 'frontend'.length);
    }
    
    // Fallback: caminho relativo baseado na profundidade
    const pathParts = pathname.split('/').filter(p => p && p !== 'index.php');
    const depth = pathParts.length;
    return '../'.repeat(Math.max(0, depth - 1)) || '../../';
}

function resolverCaminhoFotoKit(caminho) {
    if (!caminho) return '';
    if (caminho.startsWith('http')) return caminho;
    const cleaned = caminho.replace(/^\/+/, '');
    return buildKitAssetUrl(`frontend/assets/img/kits/${encodeURIComponent(cleaned)}`);
}

function abrirModalTemplate(template = null) {
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Abrindo modal template');
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Template:', template);
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produtos carregados:', produtos.length);

    const modal = document.getElementById('modalTemplate');
    const titulo = document.getElementById('modalTemplateTitulo');
    const btnTexto = document.getElementById('btnSalvarTemplateTexto');
    const form = document.getElementById('formTemplate');

    if (template) {
        titulo.textContent = 'Editar Template';
        btnTexto.textContent = 'Atualizar Template';
        preencherFormularioTemplate(template);
    } else {
        titulo.textContent = 'Novo Template';
        btnTexto.textContent = 'Criar Template';
        form.reset();
        document.getElementById('template_id').value = '';
        document.getElementById('preview_foto_template').classList.add('hidden');
        limparProdutosTemplate();
    }

    modal.classList.remove('hidden');
}

function fecharModalTemplate() {
    document.getElementById('modalTemplate').classList.add('hidden');
}

function preencherFormularioTemplate(template) {
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Preenchendo formulÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio template:', template);

    const idElement = document.getElementById('template_id');
    const nomeElement = document.getElementById('template_nome');
    const descricaoElement = document.getElementById('template_descricao');
    const precoElement = document.getElementById('template_preco_base');
    const disponivelElement = document.getElementById('template_disponivel_venda');
    if (idElement) idElement.value = template.id || '';
    if (nomeElement) nomeElement.value = template.nome || '';
    if (descricaoElement) descricaoElement.value = template.descricao || '';
    if (precoElement) precoElement.value = template.preco_base || '';
    if (disponivelElement) disponivelElement.checked = template.disponivel_venda || false;

    if (template.foto_kit) {
        const imgElement = document.getElementById('preview_img_template');
        const previewElement = document.getElementById('preview_foto_template');
        const resolvedSrc = resolverCaminhoFotoKit(template.foto_kit);
        if (imgElement) imgElement.src = resolvedSrc;
        if (previewElement) previewElement.classList.remove('hidden');
    }

    // Carregar produtos do template
    if (template.id) {
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Chamando carregarProdutosTemplate com ID:', template.id);
        carregarProdutosTemplate(template.id);
    } else {
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Template sem ID, nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o carregando produtos');
    }
}

function previewFotoTemplate(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview_foto_template');
    const img = document.getElementById('preview_img_template');

    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
}

// =====================================================
// GESTÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O DE PRODUTOS DO TEMPLATE
// =====================================================

function adicionarProdutoTemplate() {
    const container = document.getElementById('produtos-template-container');
    const produtoIndex = container.children.length;

    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Adicionando produto template');
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produtos disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­veis:', produtos);
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Quantidade de produtos:', produtos.length);

    const produtoDiv = document.createElement('div');
    produtoDiv.className = 'flex items-center space-x-4 p-4 border border-gray-200 rounded-lg';
    produtoDiv.innerHTML = `
        <div class="flex-1">
            <select class="produto-select w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                <option value="">Selecione um produto</option>
                ${produtos.map(p => `<option value="${p.id}" data-preco="${p.preco || 0}">${p.nome} - R$ ${parseFloat(p.preco || 0).toFixed(2)}</option>`).join('')}
            </select>
        </div>
        <div class="w-24">
            <input type="number" class="produto-quantidade w-full px-3 py-2 border border-gray-300 rounded-lg" value="1" min="1" required>
        </div>
        <div class="w-24">
            <input type="number" class="produto-ordem w-full px-3 py-2 border border-gray-300 rounded-lg" value="${produtoIndex + 1}" min="1" required>
        </div>
        <button type="button" onclick="removerProdutoTemplate(this)" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200" title="Remover produto">
            <i class="fas fa-trash"></i>
        </button>
    `;

    container.appendChild(produtoDiv);

    // Adicionar event listeners para cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lculo automÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡tico
    const select = produtoDiv.querySelector('.produto-select');
    const quantidade = produtoDiv.querySelector('.produto-quantidade');
    const ordem = produtoDiv.querySelector('.produto-ordem');

    select.addEventListener('change', calcularCustoProdutos);
    quantidade.addEventListener('input', calcularCustoProdutos);
    ordem.addEventListener('input', calcularCustoProdutos);
}

function removerProdutoTemplate(button) {
    const produtoDiv = button.closest('div');
    const select = produtoDiv.querySelector('.produto-select');
    const produtoNome = select.selectedOptions[0] ? select.selectedOptions[0].text : 'produto';

    Swal.fire({
        title: 'Remover Produto',
        text: `Deseja remover "${produtoNome}" do template?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, remover!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            produtoDiv.remove();
            calcularCustoProdutos();

            Swal.fire(
                'Removido!',
                'Produto removido do template.',
                'success'
            );
        }
    });
}

function limparProdutosTemplate() {
    document.getElementById('produtos-template-container').innerHTML = '';
    atualizarContadorProdutos();
}

async function carregarProdutosTemplate(templateId) {
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Carregando produtos do template ID:', templateId);

    // Aguardar produtos serem carregados se necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
    if (produtos.length === 0) {
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produtos nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o carregados, aguardando...');
        await carregarProdutos();
    }

    try {
        const url = `${window.API_BASE || '/api'}/organizador/kits-templates/get-produtos-template.php?id=${templateId}&t=${Date.now()}`;
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - URL da API:', url);
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - URL completa:', window.location.origin + '/movamazonas' + url);

        const response = await fetch(url);
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Response status:', response.status);

        const data = await response.json();
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Dados recebidos:', data);

        if (data.success) {
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produtos encontrados:', data.data.length);
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produtos:', data.data);

            limparProdutosTemplate();
            data.data.forEach(produto => {
                console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Adicionando produto:', produto);
                adicionarProdutoTemplateComDados(produto);
            });
            calcularCustoProdutos();

            // Se o preÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o base estiver vazio, calcular automaticamente
            const precoBaseElement = document.getElementById('template_preco_base');
            if (precoBaseElement && (precoBaseElement.value === '' || parseFloat(precoBaseElement.value) === 0)) {
                setTimeout(() => {
                    calcularPrecoBaseAutomatico();
                }, 100);
            }
        } else {
            console.error('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Erro na API:', data.error);
        }
    } catch (error) {
        console.error('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Erro ao carregar produtos do template:', error);
    }
}

function adicionarProdutoTemplateComDados(produto) {
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Adicionando produto com dados:', produto);
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produtos disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­veis:', produtos.length);

    const container = document.getElementById('produtos-template-container');
    if (!container) {
        console.error('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Container nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado!');
        return;
    }

    const produtoDiv = document.createElement('div');
    produtoDiv.className = 'flex items-center space-x-4 p-4 border border-gray-200 rounded-lg';

    // Verificar se o produto existe na lista de produtos disponÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­veis
    const produtoEncontrado = produtos.find(p => p.id == produto.produto_id);
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produto encontrado na lista:', produtoEncontrado);

    produtoDiv.innerHTML = `
        <div class="flex-1">
            <select class="produto-select w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                <option value="">Selecione um produto</option>
                ${produtos.map(p => `<option value="${p.id}" data-preco="${p.preco || 0}" ${p.id == produto.produto_id ? 'selected' : ''}>${p.nome} - R$ ${parseFloat(p.preco || 0).toFixed(2)}</option>`).join('')}
            </select>
        </div>
        <div class="w-24">
            <input type="number" class="produto-quantidade w-full px-3 py-2 border border-gray-300 rounded-lg" value="${produto.quantidade}" min="1" required>
        </div>
        <div class="w-24">
            <input type="number" class="produto-ordem w-full px-3 py-2 border border-gray-300 rounded-lg" value="${produto.ordem}" min="1" required>
        </div>
        <button type="button" onclick="removerProdutoTemplate(this)" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200" title="Remover produto">
            <i class="fas fa-trash"></i>
        </button>
    `;

    container.appendChild(produtoDiv);
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produto adicionado ao DOM');

    // Adicionar event listeners
    const select = produtoDiv.querySelector('.produto-select');
    const quantidade = produtoDiv.querySelector('.produto-quantidade');
    const ordem = produtoDiv.querySelector('.produto-ordem');

    select.addEventListener('change', calcularCustoProdutos);
    quantidade.addEventListener('input', calcularCustoProdutos);
    ordem.addEventListener('input', calcularCustoProdutos);
}

// =====================================================
// CÃƒÆ’Ã†â€™Ãƒâ€šÃ‚ÂLCULO DE PREÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡OS
// =====================================================

function calcularCustoProdutos() {
    const {
        custoTotal,
        quantidadeTotal
    } = calcularSomaTotal();

    const custoElement = document.getElementById('custo_produtos');
    if (custoElement) {
        custoElement.textContent = `R$ ${custoTotal.toFixed(2)}`;
        calcularMargem();
    }

    // Atualizar contador de produtos
    atualizarContadorProdutos();

    // Log para debug
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - CÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lculo atualizado:', {
        custoTotal: custoTotal.toFixed(2),
        quantidadeTotal: quantidadeTotal,
        produtosCalculados: quantidadeTotal
    });

    // Verificar se deve calcular preÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o base automaticamente
    const precoBaseElement = document.getElementById('template_preco_base');
    if (precoBaseElement && precoBaseElement.value === '' && custoTotal > 0) {
        // Se preÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o base estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ vazio e hÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ custo, calcular automaticamente
        calcularPrecoBaseAutomatico();
    }
}

function atualizarContadorProdutos() {
    const container = document.getElementById('produtos-template-container');
    const contador = document.getElementById('contador-produtos');

    if (container && contador) {
        const total = container.children.length;
        contador.textContent = `${total} produto${total !== 1 ? 's' : ''}`;
    }
}

function calcularMargem() {
    const precoBase = parseFloat(document.getElementById('template_preco_base').value) || 0;
    const custoProdutos = parseFloat(document.getElementById('custo_produtos').textContent.replace('R$ ', '')) || 0;
    const margem = precoBase - custoProdutos;

    document.getElementById('preco_base_display').textContent = `R$ ${precoBase.toFixed(2)}`;
    document.getElementById('margem').textContent = `R$ ${margem.toFixed(2)}`;
    document.getElementById('margem').className = `text-lg font-semibold ${margem >= 0 ? 'text-green-600' : 'text-red-600'}`;
}

function calcularPrecoBaseAutomatico() {
    const custoProdutos = parseFloat(document.getElementById('custo_produtos').textContent.replace('R$ ', '')) || 0;
    const margemPercentual = 0.30; // 30% de margem padrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o

    const precoBaseCalculado = custoProdutos * (1 + margemPercentual);

    document.getElementById('template_preco_base').value = precoBaseCalculado.toFixed(2);
    calcularMargem();

    // Feedback visual
    Swal.fire({
        icon: 'success',
        title: 'PreÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o Base Calculado!',
        text: `PreÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o base definido em R$ ${precoBaseCalculado.toFixed(2)} com margem de 30%`,
        timer: 2000,
        showConfirmButton: false
    });
}

function calcularSomaTotal() {
    const container = document.getElementById('produtos-template-container');
    if (!container) return {
        custoTotal: 0,
        quantidadeTotal: 0
    };

    const produtosDivs = container.querySelectorAll('.produto-select');
    let custoTotal = 0;
    let quantidadeTotal = 0;

    produtosDivs.forEach(select => {
        const option = select.selectedOptions[0];
        if (option && option.dataset.preco) {
            const produtoContainer = select.closest('.flex.items-center.space-x-4');
            const quantidadeElement = produtoContainer ? produtoContainer.querySelector('.produto-quantidade') : null;

            if (quantidadeElement) {
                const quantidade = parseInt(quantidadeElement.value) || 0;
                const precoUnitario = parseFloat(option.dataset.preco) || 0;
                const custoProduto = precoUnitario * quantidade;

                custoTotal += custoProduto;
                quantidadeTotal += quantidade;

                console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produto calculado:', {
                    produto: option.text,
                    quantidade: quantidade,
                    precoUnitario: precoUnitario,
                    custoProduto: custoProduto
                });
            }
        }
    });

    return {
        custoTotal,
        quantidadeTotal
    };
}

// =====================================================
// DEBUG FUNCTIONS
// =====================================================

async function verificarContagemProdutos() {
    try {
        const response = await fetch((window.API_BASE || '/api') + '/organizador/kits-templates/debug-produtos.php');
        const data = await response.json();

        if (data.success) {
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Contagem de produtos por template:');
            data.data.forEach(template => {
                console.log(`ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Template ${template.id} (${template.nome}): ${template.total_produtos} produtos`);
                if (template.produtos_lista) {
                    console.log(`ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produtos: ${template.produtos_lista}`);
                }
            });
        }
    } catch (error) {
        console.error('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Erro ao verificar contagem:', error);
    }
}

// =====================================================
// CRUD OPERATIONS
// =====================================================

async function salvarTemplate(e) {
    e.preventDefault();

    const formData = new FormData();
    const nome = document.getElementById('template_nome').value;
    const descricao = document.getElementById('template_descricao').value;
    const precoBase = document.getElementById('template_preco_base').value;
    const disponivelVenda = document.getElementById('template_disponivel_venda').checked ? '1' : '0';

    // ValidaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o dos campos obrigatÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³rios
    if (!nome || !precoBase || parseFloat(precoBase) <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Campos ObrigatÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³rios!',
            text: 'Nome e preÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o base sÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o obrigatÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³rios e o preÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§o deve ser maior que zero.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#EF4444'
        });
        return;
    }

    formData.append('nome', nome);
    formData.append('descricao', descricao);
    formData.append('preco_base', precoBase);
    formData.append('disponivel_venda', disponivelVenda);

    const templateId = document.getElementById('template_id').value;
    if (templateId) {
        formData.append('id', templateId);
    }

    const fotoFile = document.getElementById('template_foto').files[0];
    if (fotoFile) {
        formData.append('foto_kit', fotoFile);
    }

    // Adicionar produtos
    const container = document.getElementById('produtos-template-container');
    const produtosDivs = container.querySelectorAll('.produto-select');
    const produtosData = [];

    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Coletando produtos do formulÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio');
    console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produtos encontrados no DOM:', produtosDivs.length);

    produtosDivs.forEach((select, index) => {
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Processando produto', index, ':', select.value);

        if (select.value) {
            // Encontrar o container do produto (div pai)
            const produtoContainer = select.closest('.flex.items-center.space-x-4');
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Container encontrado:', produtoContainer);

            const quantidadeElement = produtoContainer ? produtoContainer.querySelector('.produto-quantidade') : null;
            const ordemElement = produtoContainer ? produtoContainer.querySelector('.produto-ordem') : null;

            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Elementos encontrados:', {
                quantidade: quantidadeElement ? quantidadeElement.value : '',
                ordem: ordemElement ? ordemElement.value : '',
                container: produtoContainer ? 'SIM' : 'NÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢O'
            });

            if (quantidadeElement && ordemElement) {
                const produto = {
                    produto_id: select.value,
                    quantidade: quantidadeElement.value,
                    ordem: ordemElement.value
                };
                produtosData.push(produto);
                console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produto adicionado:', produto);
            } else {
                console.error('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Elementos nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrados para produto:', select.value);
                console.error('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Container:', produtoContainer);
                console.error('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Quantidade element:', quantidadeElement);
                console.error('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Ordem element:', ordemElement);
            }
        }
    });

    // Se nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o hÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ produtos no formulÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio, verificar se ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© uma ediÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o e manter produtos existentes
    if (produtosData.length === 0 && templateId) {
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Nenhum produto no formulÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio, mantendo produtos existentes');
        // NÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o enviar array vazio, deixar o backend manter produtos existentes
        formData.append('produtos', JSON.stringify([]));
    } else {
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Produtos finais:', produtosData);
        formData.append('produtos', JSON.stringify(produtosData));
    }

    try {
        const url = templateId ? (window.API_BASE || '/api') + '/organizador/kits-templates/update.php' : (window.API_BASE || '/api') + '/organizador/kits-templates/create.php';
        const method = templateId ? 'POST' : 'POST';

        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - URL:', url);
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - Method:', method);
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js - FormData entries:');
        for (let [key, value] of formData.entries()) {
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â DEBUG kits-templates.js -', key, ':', value);
        }

        const response = await fetch(url, {
            method: method,
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            fecharModalTemplate();
            carregarTemplates();

            // SweetAlert de sucesso
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: templateId ? 'Template atualizado com sucesso!' : 'Template criado com sucesso!',
                confirmButtonText: 'OK',
                confirmButtonColor: '#10B981'
            });
        } else {
            // SweetAlert de erro
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro: ' + data.error,
                confirmButtonText: 'OK',
                confirmButtonColor: '#EF4444'
            });
        }
    } catch (error) {
        console.error('Erro ao salvar template:', error);

        // SweetAlert de erro de rede
        Swal.fire({
            icon: 'error',
            title: 'Erro de ConexÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o!',
            text: 'Erro ao salvar template. Verifique sua conexÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#EF4444'
        });
    }
}

async function editarTemplate(id) {
    const template = templates.find(t => t.id === id);
    if (template) {
        abrirModalTemplate(template);
    }
}

async function duplicarTemplate(id) {
    const template = templates.find(t => t.id === id);
    if (template) {
        const templateDuplicado = {
            ...template
        };
        templateDuplicado.nome = templateDuplicado.nome + ' (CÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³pia)';
        templateDuplicado.id = null;
        abrirModalTemplate(templateDuplicado);
    }
}

async function excluirTemplate(id) {
    // SweetAlert de confirmaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Confirmar ExclusÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o',
        text: 'Tem certeza que deseja excluir este template?',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch((window.API_BASE || '/api') + '/organizador/kits-templates/delete.php', {
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
                carregarTemplates();

                // SweetAlert de sucesso
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: 'Template excluÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­do com sucesso!',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#10B981'
                });
            } else {
                // SweetAlert de erro
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro: ' + data.error,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#EF4444'
                });
            }
        } catch (error) {
            console.error('Erro ao excluir template:', error);

            // SweetAlert de erro de rede
            Swal.fire({
                icon: 'error',
                title: 'Erro de ConexÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o!',
                text: 'Erro ao excluir template. Verifique sua conexÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#EF4444'
            });
        }
    }
}

// =====================================================
// FUNÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ES DE RESUMO E ESTATÃƒÆ’Ã†â€™Ãƒâ€šÃ‚ÂSTICAS
// =====================================================

function atualizarResumo() {
    // Esta funÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o pode ser expandida para mostrar estatÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­sticas dos templates
    // Por enquanto, apenas garante que nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o hÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ erro
    console.log('Resumo atualizado - Templates carregados:', templates.length);
}

// =====================================================
// UTILITÃƒÆ’Ã†â€™Ãƒâ€šÃ‚ÂRIOS
// =====================================================

function mostrarLoading() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('templates-container').style.display = 'none';
    document.getElementById('error-message').style.display = 'none';
}

function ocultarLoading() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('templates-container').style.display = 'grid';
}

// FunÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes antigas removidas - agora usando SweetAlert para todos os feedbacks 
