// =====================================================
// GESTÃO DE PRODUTOS EXTRAS - JAVASCRIPT
// =====================================================

let produtosExtras = [];
let produtos = [];
let paginaAtual = 1;
let itensPorPagina = 6;
let filtros = {
    evento: '',
    categoria: '',
    status: ''
};

// =====================================================
// INICIALIZAÇÃO
// =====================================================

document.addEventListener('DOMContentLoaded', function () {
    carregarProdutos();
    configurarEventListeners();

    // Verificar se há evento pré-selecionado
    const eventoSelecionado = document.getElementById('filtroEvento').value;
    if (eventoSelecionado) {
        filtros.evento = eventoSelecionado;
        carregarProdutosExtras();
    } else {
        mostrarMensagemInicial();
    }
});

function configurarEventListeners() {
    // Botão novo produto extra
    document.getElementById('btnNovoProdutoExtra').addEventListener('click', abrirModalProdutoExtra);

    // Filtros
    document.getElementById('filtroEvento').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroCategoria').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroStatus').addEventListener('change', aplicarFiltros);

    // Paginação
    document.getElementById('btn-anterior').addEventListener('click', () => {
        if (paginaAtual > 1) {
            paginaAtual--;
            renderizarProdutosExtras();
        }
    });

    document.getElementById('btn-proximo').addEventListener('click', () => {
        const totalPaginas = Math.ceil(produtosExtras.length / itensPorPagina);
        if (paginaAtual < totalPaginas) {
            paginaAtual++;
            renderizarProdutosExtras();
        }
    });

    // Formulário
    document.getElementById('formProdutoExtra').addEventListener('submit', salvarProdutoExtra);
}

// =====================================================
// CARREGAMENTO DE DADOS
// =====================================================

async function carregarProdutosExtras() {
    // Verificar se há evento selecionado
    const eventoId = filtros.evento || document.getElementById('filtroEvento').value;

    if (!eventoId) {
        mostrarMensagemInicial();
        return;
    }

    mostrarLoading();

    try {
        const response = await fetch(`../../../api/organizador/produtos-extras/list.php?evento_id=${eventoId}`);
        const data = await response.json();

        if (data.success) {
            produtosExtras = data.data;
            aplicarFiltrosLocais();
        } else {
            console.error('Erro na API:', data.error);
            mostrarErro('Erro ao carregar produtos extras: ' + data.error);
        }
    } catch (error) {
        console.error('Erro na requisição:', error);
        mostrarErro('Erro ao carregar produtos extras');
    } finally {
        ocultarLoading();
    }
}

function aplicarFiltrosLocais() {
    let produtosFiltrados = produtosExtras.filter(produtoExtra => {
        const matchEvento = !filtros.evento || produtoExtra.evento_id == filtros.evento;
        const matchCategoria = !filtros.categoria || produtoExtra.categoria === filtros.categoria;
        const matchStatus = !filtros.status ||
            (filtros.status === 'ativo' && produtoExtra.ativo) ||
            (filtros.status === 'inativo' && !produtoExtra.ativo);

        return matchEvento && matchCategoria && matchStatus;
    });

    paginaAtual = 1;
    renderizarProdutosExtras(produtosFiltrados);
}

async function carregarProdutos() {
    try {
        const response = await fetch('../../../api/organizador/produtos-extras/get_produtos.php');
        const data = await response.json();

        if (data.success) {
            produtos = data.data;
            preencherSelectProdutos();
        } else {
            console.error('Erro ao carregar produtos:', data.error);
        }
    } catch (error) {
        console.error('Erro ao carregar produtos:', error);
    }
}

function preencherSelectProdutos() {
    const select = document.getElementById('produto_id');
    if (!select) return;

    // Limpar select mantendo apenas a primeira opção
    select.innerHTML = '<option value="">Selecione um produto</option>';

    produtos.forEach(produto => {
        const option = document.createElement('option');
        option.value = produto.id;

        // Tratar casos onde nome ou preco podem ser undefined/null
        const nome = produto.nome || 'Produto sem nome';
        const preco = produto.preco ? parseFloat(produto.preco).toFixed(2) : '0.00';

        option.textContent = `${nome} - R$ ${preco}`;
        select.appendChild(option);
    });
}

// =====================================================
// RENDERIZAÇÃO
// =====================================================

function mostrarMensagemInicial() {
    const container = document.getElementById('produtos-extras-container');
    container.innerHTML = `
        <div class="col-span-full text-center py-12">
            <i class="fas fa-calendar-alt text-gray-400 text-4xl mb-4"></i>
            <p class="text-gray-500 text-lg">Selecione um evento para visualizar os produtos extras</p>
            <p class="text-gray-400">Use o filtro acima para escolher um evento específico</p>
        </div>
    `;

    // Garantir que o container esteja visível
    container.style.display = 'grid';

    // Zerar resumo inicial
    atualizarResumo([]);
}

function renderizarProdutosExtras(produtosParaRenderizar = produtosExtras) {
    const container = document.getElementById('produtos-extras-container');
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const fim = inicio + itensPorPagina;
    const produtosPaginados = produtosParaRenderizar.slice(inicio, fim);

    container.innerHTML = '';

    if (produtosPaginados.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-12">
                <i class="fas fa-gift text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500 text-lg">Nenhum produto extra encontrado</p>
                <p class="text-gray-400">Crie seu primeiro produto extra para este evento</p>
            </div>
        `;
        return;
    }

    produtosPaginados.forEach(produtoExtra => {
        const card = criarCardProdutoExtra(produtoExtra);
        container.appendChild(card);
    });

    atualizarPaginacao(produtosParaRenderizar.length);
    atualizarResumo(produtosParaRenderizar);
}

function criarCardProdutoExtra(produtoExtra) {
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow border border-gray-200';

    const statusText = produtoExtra.ativo ? 'Ativo' : 'Inativo';
    const statusColor = produtoExtra.ativo ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100';

    card.innerHTML = `
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">${produtoExtra.nome}</h3>
                    <p class="text-gray-600 text-sm mb-2">${produtoExtra.descricao || 'Sem descrição'}</p>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span class="flex items-center">
                            <i class="fas fa-tag mr-1"></i>
                            ${produtoExtra.categoria}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-calendar mr-1"></i>
                            ${produtoExtra.evento_nome}
                        </span>
                    </div>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-medium ${statusColor}">
                    ${statusText}
                </span>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="text-lg font-bold text-primary-600">
                    R$ ${parseFloat(produtoExtra.valor).toFixed(2)}
                </div>
                <div class="flex space-x-2">
                    <button onclick="editarProdutoExtra(${produtoExtra.id})" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="excluirProdutoExtra(${produtoExtra.id})" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    return card;
}

// =====================================================
// FILTROS E PAGINAÇÃO
// =====================================================

function aplicarFiltros() {
    filtros.evento = document.getElementById('filtroEvento').value;
    filtros.categoria = document.getElementById('filtroCategoria').value;
    filtros.status = document.getElementById('filtroStatus').value;

    // Se não há evento selecionado, mostrar mensagem inicial
    if (!filtros.evento) {
        mostrarMensagemInicial();
        return;
    }

    // Se há evento selecionado, carregar produtos extras
    carregarProdutosExtras();
}

function atualizarPaginacao(totalProdutos = produtosExtras.length) {
    const totalPaginas = Math.ceil(totalProdutos / itensPorPagina);
    const inicio = (paginaAtual - 1) * itensPorPagina + 1;
    const fim = Math.min(paginaAtual * itensPorPagina, totalProdutos);

    const paginacao = document.getElementById('paginacao');
    const btnAnterior = document.getElementById('btn-anterior');
    const btnProximo = document.getElementById('btn-proximo');
    const paginas = document.getElementById('paginas');

    if (totalPaginas <= 1) {
        paginacao.style.display = 'none';
        return;
    }

    paginacao.style.display = 'flex';
    btnAnterior.disabled = paginaAtual === 1;
    btnProximo.disabled = paginaAtual === totalPaginas;

    paginas.innerHTML = '';
    for (let i = 1; i <= totalPaginas; i++) {
        const btn = document.createElement('button');
        btn.className = `px-3 py-2 text-sm font-medium rounded-lg ${
            i === paginaAtual
                ? 'bg-primary-600 text-white'
                : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'
        }`;
        btn.textContent = i;
        btn.onclick = () => {
            paginaAtual = i;
            renderizarProdutosExtras();
        };
        paginas.appendChild(btn);
    }
}

function atualizarResumo(produtosParaResumo = produtosExtras) {
    const totalProdutos = produtosParaResumo.length;
    const produtosAtivos = produtosParaResumo.filter(p => p.ativo).length;
    const valorTotal = produtosParaResumo.reduce((sum, p) => sum + parseFloat(p.valor), 0);
    const categoriasUnicas = new Set(produtosParaResumo.map(p => p.categoria)).size;

    // Verificar se os elementos existem antes de tentar acessá-los
    const totalProdutosEl = document.getElementById('total-produtos');
    const produtosAtivosEl = document.getElementById('produtos-ativos');
    const valorTotalEl = document.getElementById('valor-total');
    const totalCategoriasEl = document.getElementById('total-categorias');

    if (totalProdutosEl) totalProdutosEl.textContent = totalProdutos;
    if (produtosAtivosEl) produtosAtivosEl.textContent = produtosAtivos;
    if (valorTotalEl) valorTotalEl.textContent = `R$ ${valorTotal.toFixed(2)}`;
    if (totalCategoriasEl) totalCategoriasEl.textContent = categoriasUnicas;
}

// =====================================================
// MODAL E FORMULÁRIO
// =====================================================

function abrirModalProdutoExtra(produtoExtra = null) {
    const modal = document.getElementById('modalProdutoExtra');
    const titulo = document.getElementById('modalProdutoExtraTitulo');
    const btnTexto = document.getElementById('btnSalvarProdutoExtraTexto');
    const form = document.getElementById('formProdutoExtra');

    if (produtoExtra && produtoExtra.id) {
        titulo.textContent = 'Editar Produto Extra';
        btnTexto.textContent = 'Atualizar Produto Extra';
        preencherFormularioProdutoExtra(produtoExtra);
    } else {
        titulo.textContent = 'Novo Produto Extra';
        btnTexto.textContent = 'Criar Produto Extra';
        form.reset();
        document.getElementById('produto_extra_id').value = '';
        limparProdutosSelecionados();
    }

    modal.classList.remove('hidden');
}

function fecharModalProdutoExtra() {
    document.getElementById('modalProdutoExtra').classList.add('hidden');
}

function preencherFormularioProdutoExtra(produtoExtra) {
    // Validação robusta para evitar undefined
    if (!produtoExtra || typeof produtoExtra !== 'object') {
        console.error('❌ ERRO - produtoExtra é inválido:', produtoExtra);
        return;
    }

    document.getElementById('produto_extra_id').value = produtoExtra.id || '';
    document.getElementById('produto_extra_evento_id').value = produtoExtra.evento_id || '';
    document.getElementById('produto_extra_nome').value = produtoExtra.nome || '';
    document.getElementById('produto_extra_descricao').value = produtoExtra.descricao || '';
    document.getElementById('produto_extra_valor').value = produtoExtra.valor || '';
    document.getElementById('produto_extra_disponivel_venda').checked = Boolean(produtoExtra.disponivel_venda);

    // Carregar produtos do produto extra usando API específica
    if (produtoExtra.id) {
        carregarProdutosProdutoExtra(produtoExtra.id);
    } else {
        limparProdutosSelecionados();
    }
}

function limparProdutosSelecionados() {
    const container = document.getElementById('produtos-selecionados');
    container.innerHTML = '';
}

async function carregarProdutosProdutoExtra(produtoExtraId) {
    // Aguardar produtos serem carregados se necessário
    if (produtos.length === 0) {
        await carregarProdutos();
    }

    try {
        const url = `../../../api/organizador/produtos-extras/get-produtos.php?id=${produtoExtraId}&t=${Date.now()}`;
        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
            limparProdutosSelecionados();
            data.data.forEach(produto => {
                adicionarProdutoSelecionadoComDados(produto);
            });
        } else {
            console.error('Erro na API:', data.error);
        }
    } catch (error) {
        console.error('Erro ao carregar produtos do produto extra:', error);
    }
}

function adicionarProdutoSelecionadoComDados(produto) {
    const container = document.getElementById('produtos-selecionados');
    if (!container) {
        console.error('Container não encontrado!');
        return;
    }

    // Verificar se o produto existe na lista de produtos disponíveis
    const produtoEncontrado = produtos.find(p => p.id == produto.produto_id);

    if (!produtoEncontrado) {
        console.error('Produto não encontrado na lista global:', produto.produto_id);
        return;
    }

    const item = document.createElement('div');
    item.className = 'flex items-center justify-between p-2 bg-gray-50 rounded-lg';
    item.innerHTML = `
        <span class="text-sm">${produto.produto_nome} - R$ ${parseFloat(produto.produto_preco || 0).toFixed(2)}</span>
        <button type="button" onclick="removerProdutoSelecionado(this)" class="text-red-600 hover:text-red-800">
            <i class="fas fa-times"></i>
        </button>
    `;

    container.appendChild(item);
}

async function carregarProdutosSelecionados(produtosSelecionados) {
    const container = document.getElementById('produtos-selecionados');
    container.innerHTML = '';

    if (produtosSelecionados.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm">Nenhum produto selecionado</p>';
        return;
    }

    produtosSelecionados.forEach(produto => {
        const item = document.createElement('div');
        item.className = 'flex items-center justify-between p-2 bg-gray-50 rounded-lg';
        item.innerHTML = `
            <span class="text-sm">${produto.nome} - R$ ${parseFloat(produto.preco || 0).toFixed(2)}</span>
            <button type="button" onclick="removerProdutoSelecionado(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(item);
    });
}

function adicionarProduto() {
    const select = document.getElementById('produto_id');
    const produtoId = select.value;

    if (!produtoId) {
        showWarning('Selecione um produto para adicionar');
        return;
    }

    const produto = produtos.find(p => p.id == produtoId);
    if (!produto) {
        showError('Produto não encontrado');
        return;
    }

    // Verificar se já foi adicionado
    const container = document.getElementById('produtos-selecionados');
    const jaExiste = Array.from(container.children).some(item => {
        const span = item.querySelector('span');
        if (!span) return false;
        const textoCompleto = span.textContent;
        const nomeProduto = textoCompleto.split(' - R$')[0];
        return nomeProduto === produto.nome;
    });

    if (jaExiste) {
        showWarning('Este produto já foi adicionado');
        return;
    }

    const item = document.createElement('div');
    item.className = 'flex items-center justify-between p-2 bg-gray-50 rounded-lg produto-selecionado';
    item.innerHTML = `
        <span class="text-sm">${produto.nome} - R$ ${parseFloat(produto.preco || 0).toFixed(2)}</span>
        <button type="button" onclick="removerProdutoSelecionado(this)" class="text-red-600 hover:text-red-800">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(item);

    select.value = '';
}

function removerProdutoSelecionado(button) {
    button.parentElement.remove();
}

// 🎯 FUNÇÃO REMOVIDA - SEM VALIDAÇÃO DE CONTADOR

function coletarProdutosSelecionados() {
    const container = document.getElementById('produtos-selecionados');
    const produtosSelecionados = [];

    // Ultra simples: pegar todos os spans que têm texto com "- R$"
    Array.from(container.children).forEach(item => {
        const span = item.querySelector('span');
        if (span && span.textContent.includes('- R$')) {
            const texto = span.textContent;
            const nomeProduto = texto.split(' - R$')[0];
            const produto = produtos.find(p => p.nome === nomeProduto);
            if (produto) {
                produtosSelecionados.push(produto);
            }
        }
    });

    return produtosSelecionados;
}

// =====================================================
// SALVAR E EXCLUIR
// =====================================================

async function salvarProdutoExtra(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const produtoId = formData.get('id');

    // Validação dos campos obrigatórios (produtos são opcionais na edição)
    const eventoId = formData.get('evento_id');
    const nome = formData.get('nome');
    const valor = formData.get('valor');

    if (!eventoId || !nome || !valor || parseFloat(valor) <= 0) {
        showWarning('Evento, nome e valor são obrigatórios e o valor deve ser maior que zero.');
        return;
    }

    // Coletar produtos selecionados (pode estar vazio para edições simples)
    const produtosSelecionados = coletarProdutosSelecionados();
    formData.append('produtos', JSON.stringify(produtosSelecionados));

    try {
        const url = produtoId ? '../../../api/organizador/produtos-extras/update.php' : '../../../api/organizador/produtos-extras/create.php';
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            fecharModalProdutoExtra();
            carregarProdutosExtras();
            showSuccess(produtoId ? 'Produto extra atualizado com sucesso!' : 'Produto extra criado com sucesso!');
        } else {
            showError(data.error);
        }
    } catch (error) {
        console.error('Erro:', error);
        showError('Erro ao salvar produto extra');
    }
}

async function editarProdutoExtra(id) {
    try {
        const response = await fetch(`../../../api/organizador/produtos-extras/get.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            abrirModalProdutoExtra(data.data);
        } else {
            showError('Erro ao carregar produto extra: ' + data.error);
        }
    } catch (error) {
        console.error('Erro:', error);
        showError('Erro ao carregar produto extra');
    }
}

async function excluirProdutoExtra(id) {
    console.log('🔍 DEBUG - Iniciando exclusão do produto extra ID:', id);

    const result = await Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir este produto extra?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) {
        console.log('🔍 DEBUG - Exclusão cancelada pelo usuário');
        return;
    }

    console.log('🔍 DEBUG - Usuário confirmou exclusão, enviando requisição...');

    try {
        const url = `../../../api/organizador/produtos-extras/delete.php?id=${id}`;
        console.log('🔍 DEBUG - URL da exclusão:', url);

        const response = await fetch(url, {
            method: 'DELETE'
        });

        console.log('🔍 DEBUG - Response status:', response.status);
        const data = await response.json();
        console.log('🔍 DEBUG - Dados recebidos:', data);

        if (data.success) {
            console.log('🔍 DEBUG - Exclusão bem-sucedida, recarregando lista...');
            carregarProdutosExtras();
            showSuccess('Produto extra excluído com sucesso!');
        } else {
            console.error('🔍 DEBUG - Erro na exclusão:', data.error);
            showError(data.error);
        }
    } catch (error) {
        console.error('🔍 DEBUG - Erro na requisição:', error);
        showError('Erro ao excluir produto extra');
    }
}

// =====================================================
// UTILITÁRIOS E SWEETALERT
// =====================================================

function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: message,
        timer: 3000,
        showConfirmButton: false
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: message
    });
}

function showWarning(message) {
    Swal.fire({
        icon: 'warning',
        title: 'Atenção!',
        text: message
    });
}

function showInfo(message) {
    Swal.fire({
        icon: 'info',
        title: 'Informação',
        text: message
    });
}

function mostrarLoading() {
    const loading = document.getElementById('loading');
    const container = document.getElementById('produtos-extras-container');

    if (loading) loading.style.display = 'block';
    if (container) container.style.display = 'none';
}

function ocultarLoading() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('produtos-extras-container').style.display = 'grid';
}

function mostrarErro(mensagem) {
    document.getElementById('error-message').style.display = 'block';
    document.getElementById('produtos-extras-container').style.display = 'none';
    document.getElementById('loading').style.display = 'none';
}