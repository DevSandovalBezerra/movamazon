if (window.getApiBase) { window.getApiBase(); }
// =====================================================
// GESTÃO DE TEMPLATES DE KIT - JAVASCRIPT
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
// INICIALIZAÇÃO
// =====================================================

document.addEventListener('DOMContentLoaded', function () {
    carregarTemplates();
    carregarProdutos();
    configurarEventListeners();
});

function configurarEventListeners() {
    // Botão novo template
    document.getElementById('btnNovoTemplate').addEventListener('click', abrirModalTemplate);

    // Filtros
    document.getElementById('filtroNome').addEventListener('input', aplicarFiltros);
    document.getElementById('filtroDescricao').addEventListener('input', aplicarFiltros);
    document.getElementById('filtroStatus').addEventListener('change', aplicarFiltros);

    // Paginação
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

    // Formulário
    document.getElementById('formTemplate').addEventListener('submit', salvarTemplate);

    // Preview de foto
    document.getElementById('template_foto').addEventListener('change', previewFotoTemplate);

    // Cálculo automático de preços
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
        console.error('Erro na requisição:', error);

        // SweetAlert de erro de rede
        Swal.fire({
            icon: 'error',
            title: 'Erro de Conexão!',
            text: 'Erro ao carregar templates. Verifique sua conexão.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#EF4444'
        });
    } finally {
        ocultarLoading();
    }
}

async function carregarProdutos() {
    console.log(' DEBUG kits-templates.js - Iniciando carregamento de produtos...');

    try {
        const url = (window.API_BASE || '/api') + '/organizador/produtos/list.php';
        console.log(' DEBUG kits-templates.js - URL da API:', url);

        const response = await fetch(url);
        console.log(' DEBUG kits-templates.js - Response status:', response.status);

        const data = await response.json();
        console.log(' DEBUG kits-templates.js - Dados recebidos:', data);

        if (data.success) {
            produtos = data.data;
            console.log(' DEBUG kits-templates.js - Produtos carregados:', produtos.length);
            console.log(' DEBUG kits-templates.js - Primeiro produto:', produtos[0]);
        } else {
            console.error(' DEBUG kits-templates.js - Erro na API:', data.error);
        }
    } catch (error) {
        console.error(' DEBUG kits-templates.js - Erro ao carregar produtos:', error);
    }
}

// =====================================================
// RENDERIZAÇÃO
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
                <p class="text-gray-400">Crie seu primeiro template para começar</p>
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
        '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Disponível para venda</span>' :
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
                    <p class="text-sm text-gray-500">${template.descricao || 'Sem descrição'}</p>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${statusClass}-100 text-${statusClass}-800">
                    ${statusText}
                </span>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Preço Base:</span>
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
// FILTROS E PAGINAÇÃO
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
// MODAL TEMPLATE
// =====================================================
// Função para montar o caminho correto da imagem do kit
function montarCaminhoImagemKit(nome, extension = 'png') {
    // Usa encodeURIComponent para tratar espaços e acentos
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

function resolverCaminhoFotoKit(caminho) {
    if (!caminho) return '';
    if (caminho.startsWith('http')) return caminho;
    const cleaned = caminho.replace(/^\/+/, '');
    return buildKitAssetUrl(`frontend/assets/img/kits/${encodeURIComponent(cleaned)}`);
}

function abrirModalTemplate(template = null) {
    console.log(' DEBUG kits-templates.js - Abrindo modal template');
    console.log(' DEBUG kits-templates.js - Template:', template);
    console.log(' DEBUG kits-templates.js - Produtos carregados:', produtos.length);

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
    console.log(' DEBUG kits-templates.js - Preenchendo formulário template:', template);

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
        console.log(' DEBUG kits-templates.js - Chamando carregarProdutosTemplate com ID:', template.id);
        carregarProdutosTemplate(template.id);
    } else {
        console.log(' DEBUG kits-templates.js - Template sem ID, não carregando produtos');
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
// GESTÃO DE PRODUTOS DO TEMPLATE
// =====================================================

function adicionarProdutoTemplate() {
    const container = document.getElementById('produtos-template-container');
    const produtoIndex = container.children.length;

    console.log(' DEBUG kits-templates.js - Adicionando produto template');
    console.log(' DEBUG kits-templates.js - Produtos disponíveis:', produtos);
    console.log(' DEBUG kits-templates.js - Quantidade de produtos:', produtos.length);

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

    // Adicionar event listeners para cálculo automático
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
    console.log(' DEBUG kits-templates.js - Carregando produtos do template ID:', templateId);

    // Aguardar produtos serem carregados se necessário
    if (produtos.length === 0) {
        console.log(' DEBUG kits-templates.js - Produtos não carregados, aguardando...');
        await carregarProdutos();
    }

    try {
        const url = `${window.API_BASE || '/api'}/organizador/kits-templates/get-produtos-template.php?id=${templateId}&t=${Date.now()}`;
        console.log(' DEBUG kits-templates.js - URL da API:', url);
        console.log(' DEBUG kits-templates.js - URL completa:', window.location.origin + '/movamazonas' + url);

        const response = await fetch(url);
        console.log(' DEBUG kits-templates.js - Response status:', response.status);

        const data = await response.json();
        console.log(' DEBUG kits-templates.js - Dados recebidos:', data);

        if (data.success) {
            console.log(' DEBUG kits-templates.js - Produtos encontrados:', data.data.length);
            console.log(' DEBUG kits-templates.js - Produtos:', data.data);

            limparProdutosTemplate();
            data.data.forEach(produto => {
                console.log(' DEBUG kits-templates.js - Adicionando produto:', produto);
                adicionarProdutoTemplateComDados(produto);
            });
            calcularCustoProdutos();

            // Se o preço base estiver vazio, calcular automaticamente
            const precoBaseElement = document.getElementById('template_preco_base');
            if (precoBaseElement && (precoBaseElement.value === '' || parseFloat(precoBaseElement.value) === 0)) {
                setTimeout(() => {
                    calcularPrecoBaseAutomatico();
                }, 100);
            }
        } else {
            console.error(' DEBUG kits-templates.js - Erro na API:', data.error);
        }
    } catch (error) {
        console.error(' DEBUG kits-templates.js - Erro ao carregar produtos do template:', error);
    }
}

function adicionarProdutoTemplateComDados(produto) {
    console.log(' DEBUG kits-templates.js - Adicionando produto com dados:', produto);
    console.log(' DEBUG kits-templates.js - Produtos disponíveis:', produtos.length);

    const container = document.getElementById('produtos-template-container');
    if (!container) {
        console.error(' DEBUG kits-templates.js - Container não encontrado!');
        return;
    }

    const produtoDiv = document.createElement('div');
    produtoDiv.className = 'flex items-center space-x-4 p-4 border border-gray-200 rounded-lg';

    // Verificar se o produto existe na lista de produtos disponíveis
    const produtoEncontrado = produtos.find(p => p.id == produto.produto_id);
    console.log(' DEBUG kits-templates.js - Produto encontrado na lista:', produtoEncontrado);

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
    console.log(' DEBUG kits-templates.js - Produto adicionado ao DOM');

    // Adicionar event listeners
    const select = produtoDiv.querySelector('.produto-select');
    const quantidade = produtoDiv.querySelector('.produto-quantidade');
    const ordem = produtoDiv.querySelector('.produto-ordem');

    select.addEventListener('change', calcularCustoProdutos);
    quantidade.addEventListener('input', calcularCustoProdutos);
    ordem.addEventListener('input', calcularCustoProdutos);
}

// =====================================================
// CÁLCULO DE PREÇOS
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
    console.log(' DEBUG kits-templates.js - Cálculo atualizado:', {
        custoTotal: custoTotal.toFixed(2),
        quantidadeTotal: quantidadeTotal,
        produtosCalculados: quantidadeTotal
    });

    // Verificar se deve calcular preço base automaticamente
    const precoBaseElement = document.getElementById('template_preco_base');
    if (precoBaseElement && precoBaseElement.value === '' && custoTotal > 0) {
        // Se preço base está vazio e há custo, calcular automaticamente
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
    const margemPercentual = 0.30; // 30% de margem padrão

    const precoBaseCalculado = custoProdutos * (1 + margemPercentual);

    document.getElementById('template_preco_base').value = precoBaseCalculado.toFixed(2);
    calcularMargem();

    // Feedback visual
    Swal.fire({
        icon: 'success',
        title: 'Preço Base Calculado!',
        text: `Preço base definido em R$ ${precoBaseCalculado.toFixed(2)} com margem de 30%`,
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

                console.log(' DEBUG kits-templates.js - Produto calculado:', {
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
            console.log(' DEBUG kits-templates.js - Contagem de produtos por template:');
            data.data.forEach(template => {
                console.log(` DEBUG kits-templates.js - Template ${template.id} (${template.nome}): ${template.total_produtos} produtos`);
                if (template.produtos_lista) {
                    console.log(` DEBUG kits-templates.js - Produtos: ${template.produtos_lista}`);
                }
            });
        }
    } catch (error) {
        console.error(' DEBUG kits-templates.js - Erro ao verificar contagem:', error);
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

    // Validação dos campos obrigatórios
    if (!nome || !precoBase || parseFloat(precoBase) <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Campos Obrigatórios!',
            text: 'Nome e preço base são obrigatórios e o preço deve ser maior que zero.',
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

    console.log(' DEBUG kits-templates.js - Coletando produtos do formulário');
    console.log(' DEBUG kits-templates.js - Produtos encontrados no DOM:', produtosDivs.length);

    produtosDivs.forEach((select, index) => {
        console.log(' DEBUG kits-templates.js - Processando produto', index, ':', select.value);

        if (select.value) {
            // Encontrar o container do produto (div pai)
            const produtoContainer = select.closest('.flex.items-center.space-x-4');
            console.log(' DEBUG kits-templates.js - Container encontrado:', produtoContainer);

            const quantidadeElement = produtoContainer ? produtoContainer.querySelector('.produto-quantidade') : null;
            const ordemElement = produtoContainer ? produtoContainer.querySelector('.produto-ordem') : null;

            console.log(' DEBUG kits-templates.js - Elementos encontrados:', {
                quantidade: quantidadeElement ? quantidadeElement.value : '',
                ordem: ordemElement ? ordemElement.value : '',
                container: produtoContainer ? 'SIM' : 'NÃO'
            });

            if (quantidadeElement && ordemElement) {
                const produto = {
                    produto_id: select.value,
                    quantidade: quantidadeElement.value,
                    ordem: ordemElement.value
                };
                produtosData.push(produto);
                console.log(' DEBUG kits-templates.js - Produto adicionado:', produto);
            } else {
                console.error(' DEBUG kits-templates.js - Elementos não encontrados para produto:', select.value);
                console.error(' DEBUG kits-templates.js - Container:', produtoContainer);
                console.error(' DEBUG kits-templates.js - Quantidade element:', quantidadeElement);
                console.error(' DEBUG kits-templates.js - Ordem element:', ordemElement);
            }
        }
    });

    // Se não há produtos no formulário, verificar se é uma edição e manter produtos existentes
    if (produtosData.length === 0 && templateId) {
        console.log(' DEBUG kits-templates.js - Nenhum produto no formulário, mantendo produtos existentes');
        // Não enviar array vazio, deixar o backend manter produtos existentes
        formData.append('produtos', JSON.stringify([]));
    } else {
        console.log(' DEBUG kits-templates.js - Produtos finais:', produtosData);
        formData.append('produtos', JSON.stringify(produtosData));
    }

    try {
        const url = templateId ? (window.API_BASE || '/api') + '/organizador/kits-templates/update.php' : (window.API_BASE || '/api') + '/organizador/kits-templates/create.php';
        const method = templateId ? 'POST' : 'POST';

        console.log(' DEBUG kits-templates.js - URL:', url);
        console.log(' DEBUG kits-templates.js - Method:', method);
        console.log(' DEBUG kits-templates.js - FormData entries:');
        for (let [key, value] of formData.entries()) {
            console.log(' DEBUG kits-templates.js -', key, ':', value);
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
            title: 'Erro de Conexão!',
            text: 'Erro ao salvar template. Verifique sua conexão.',
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
        templateDuplicado.nome = templateDuplicado.nome + ' (Cópia)';
        templateDuplicado.id = null;
        abrirModalTemplate(templateDuplicado);
    }
}

async function excluirTemplate(id) {
    // SweetAlert de confirmação
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Confirmar Exclusão',
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
                    text: 'Template excluído com sucesso!',
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
                title: 'Erro de Conexão!',
                text: 'Erro ao excluir template. Verifique sua conexão.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#EF4444'
            });
        }
    }
}

// =====================================================
// FUNÇÕES DE RESUMO E ESTATÍSTICAS
// =====================================================

function atualizarResumo() {
    // Esta função pode ser expandida para mostrar estatísticas dos templates
    // Por enquanto, apenas garante que não há erro
    console.log('Resumo atualizado - Templates carregados:', templates.length);
}

// =====================================================
// UTILITÁRIOS
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

// Funçções antigas removidas - agora usando SweetAlert para todos os feedbacks 
