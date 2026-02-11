// =====================================================
// GESTÃO DE PRODUTOS - JAVASCRIPT
// =====================================================

let produtos = [];
let paginaAtual = 1;
let itensPorPagina = 9;
let filtros = {
    nome: '',
    descricao: '',
    status: ''
};

// =====================================================
// INICIALIZAÇÃO
// =====================================================

document.addEventListener('DOMContentLoaded', function () {
    carregarProdutos();
    configurarEventListeners();
});

function configurarEventListeners() {
    // Botão novo produto
    const btnNovo = document.getElementById('btnNovoProduto');
    if (btnNovo) {
        btnNovo.addEventListener('click', abrirModalProduto);
    }

    // Filtros
    const filtroNome = document.getElementById('filtroNome');
    if (filtroNome) filtroNome.addEventListener('input', aplicarFiltros);

    const filtroDesc = document.getElementById('filtroDescricao');
    if (filtroDesc) filtroDesc.addEventListener('input', aplicarFiltros);

    const filtroStatus = document.getElementById('filtroStatus');
    if (filtroStatus) filtroStatus.addEventListener('change', aplicarFiltros);

    // Paginação
    const btnAnterior = document.getElementById('btn-anterior');
    if (btnAnterior) {
        btnAnterior.addEventListener('click', () => {
            if (paginaAtual > 1) {
                paginaAtual--;
                renderizarProdutos();
            }
        });
    }

    const btnProximo = document.getElementById('btn-proximo');
    if (btnProximo) {
        btnProximo.addEventListener('click', () => {
            const totalPaginas = Math.ceil(produtos.length / itensPorPagina);
            if (paginaAtual < totalPaginas) {
                paginaAtual++;
                renderizarProdutos();
            }
        });
    }

    // Formulário
    const formProduto = document.getElementById('formProduto');
    if (formProduto) formProduto.addEventListener('submit', salvarProduto);

    // Preview de foto
    const fotoInput = document.getElementById('produto_foto');
    if (fotoInput) fotoInput.addEventListener('change', previewFoto);
}

// =====================================================
// CARREGAMENTO DE DADOS
// =====================================================

async function carregarProdutos() {
    mostrarLoading();

    try {
        const response = await fetch('../../../api/organizador/produtos/list.php');
        const data = await response.json();

        if (data.success) {
            produtos = data.data;
            renderizarProdutos();
        } else {
            mostrarErro('Erro ao carregar produtos: ' + data.error);
        }
    } catch (error) {
        console.error('Erro na requisição:', error);
        mostrarErro('Erro ao carregar produtos');
    } finally {
        ocultarLoading();
    }
}

// =====================================================
// RENDERIZAÇÃO
// =====================================================

function renderizarProdutos(produtosParaRenderizar = produtos) {
    const container = document.getElementById('produtos-container');
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const fim = inicio + itensPorPagina;
    const produtosPaginados = produtosParaRenderizar.slice(inicio, fim);

    container.innerHTML = '';

    if (produtosPaginados.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-12">
                <i class="fas fa-box-open text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500 text-lg">Nenhum produto encontrado</p>
                <p class="text-gray-400">Crie seu primeiro produto para começar</p>
            </div>
        `;
        return;
    }

    produtosPaginados.forEach(produto => {
        const card = criarCardProduto(produto);
        container.appendChild(card);
    });

    atualizarPaginacao(produtosParaRenderizar.length);
}

function criarCardProduto(produto) {
    const card = document.createElement('div');
    card.className = 'organizador-card bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow border border-gray-200';

    const statusClass = produto.ativo ? 'green' : 'red';
    const statusText = produto.ativo ? 'Ativo' : 'Inativo';
    const disponivelVenda = produto.disponivel_venda ?
        '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Disponível para venda</span>' :
        '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Apenas em kit</span>';

    const precoFormatado = produto.preco ? `R$ ${parseFloat(produto.preco).toFixed(2)}` : 'R$ 0,00';

    // Montar URL relativa da foto (de frontend/paginas/organizador/ para frontend/assets/)
    let fotoUrl = produto.foto_url ? '../../' + produto.foto_url : '';
    const fotoHtml = fotoUrl ?
        `<img src="${fotoUrl}" alt="${produto.nome}" class="w-full h-48 object-cover rounded-t-lg">` :
        `<div class="w-full h-48 bg-gray-200 flex items-center justify-center rounded-t-lg">
            <i class="fas fa-image text-gray-400 text-4xl"></i>
        </div>`;

    card.innerHTML = `
        ${fotoHtml}
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">${produto.nome}</h3>
                    <p class="text-lg font-bold text-green-600">${precoFormatado}</p>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${statusClass}-100 text-${statusClass}-800 ml-2">
                    ${statusText}
                </span>
            </div>
            
            ${produto.descricao ? `<p class="text-gray-600 text-sm mb-4 line-clamp-2">${produto.descricao}</p>` : ''}
            
            <div class="space-y-2 mb-4">
                ${disponivelVenda}
            </div>
            
            <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                <button onclick="editarProduto(${produto.id})" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                    <i class="fas fa-edit mr-1"></i>Editar
                </button>
                <button onclick="excluirProduto(${produto.id})" class="text-red-600 hover:text-red-900 text-sm font-medium">
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

    let produtosFiltrados = produtos.filter(produto => {
        const matchNome = !filtros.nome || produto.nome.toLowerCase().includes(filtros.nome);
        const matchDescricao = !filtros.descricao || produto.descricao.toLowerCase().includes(filtros.descricao);
        const matchStatus = !filtros.status ||
            (filtros.status === 'ativo' && produto.ativo) ||
            (filtros.status === 'inativo' && !produto.ativo);
        return matchNome && matchDescricao && matchStatus;
    });

    paginaAtual = 1;
    renderizarProdutos(produtosFiltrados);
}

function atualizarPaginacao(totalProdutos = produtos.length) {
    const totalPaginas = Math.ceil(totalProdutos / itensPorPagina);
    const inicio = (paginaAtual - 1) * itensPorPagina + 1;
    const fim = Math.min(paginaAtual * itensPorPagina, totalProdutos);

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
// MODAL PRODUTO
// =====================================================

function abrirModalProduto(produto = null) {
    const modal = document.getElementById('modalProduto');
    if (!modal) return;
    document.getElementById('modalProdutoTitulo').textContent = produto ? 'Editar Produto' : 'Novo Produto';
    preencherFormularioProduto(produto);
    modal.classList.remove('hidden');
}

function fecharModalProduto() {
    const modal = document.getElementById('modalProduto');
    if (!modal) return;
    modal.classList.add('hidden');
}

function preencherFormularioProduto(produto) {
    produto = produto || {};

    const idEl = document.getElementById('produto_id');
    const nomeEl = document.getElementById('produto_nome');
    const descEl = document.getElementById('produto_descricao');
    const precoEl = document.getElementById('produto_preco');
    const disponivelEl = document.getElementById('produto_disponivel_venda');
    const preview = document.getElementById('preview_foto');
    const previewImg = document.getElementById('preview_img');

    if (idEl) idEl.value = produto.id || '';
    if (nomeEl) nomeEl.value = produto.nome || '';
    if (descEl) descEl.value = produto.descricao || '';
    if (precoEl) precoEl.value = (produto.preco !== undefined && produto.preco !== null) ? produto.preco : '';
    if (disponivelEl) disponivelEl.checked = !!produto.disponivel_venda;

    if (preview && previewImg) {
        if (produto.foto_url) {
            // Montar URL relativa da foto
            previewImg.src = '../../' + produto.foto_url;
            preview.classList.remove('hidden');
        } else {
            previewImg.src = '';
            preview.classList.add('hidden');
        }
    }
}

function previewFoto(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview_foto');
    const img = document.getElementById('preview_img');

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
// CRUD OPERATIONS
// =====================================================

async function salvarProduto(e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('nome', document.getElementById('produto_nome').value);
    formData.append('descricao', document.getElementById('produto_descricao').value);
    formData.append('preco', document.getElementById('produto_preco').value);
    formData.append('disponivel_venda', document.getElementById('produto_disponivel_venda').checked ? '1' : '0');

    const produtoId = document.getElementById('produto_id').value;
    if (produtoId) {
        formData.append('id', produtoId);
    }

    const fotoFile = document.getElementById('produto_foto').files[0];
    if (fotoFile) {
        formData.append('foto_produto', fotoFile);
    }

    try {
        const url = produtoId ? '../../../api/organizador/produtos/update.php' : '../../../api/organizador/produtos/create.php';

        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            fecharModalProduto();
            carregarProdutos();
            mostrarSucesso(produtoId ? 'Produto atualizado com sucesso!' : 'Produto criado com sucesso!');
        } else {
            mostrarErro('Erro: ' + data.error);
        }
    } catch (error) {
        console.error('Erro ao salvar produto:', error);
        mostrarErro('Erro ao salvar produto');
    }
}

async function editarProduto(id) {
    const produto = produtos.find(p => p.id === id);
    if (produto) {
        abrirModalProduto(produto);
    }
}

async function excluirProduto(id) {
    Swal.fire({
        title: 'Tem certeza?',
        text: 'Esta ação não pode ser revertida!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch('../../../api/organizador/produtos/delete.php', {
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
                    carregarProdutos();
                    Swal.fire('Excluído!', 'Produto excluído com sucesso.', 'success');
                } else {
                    Swal.fire('Erro!', 'Erro: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Erro ao excluir produto:', error);
                Swal.fire('Erro!', 'Erro ao excluir produto', 'error');
            }
        }
    });
}

// =====================================================
// UTILITÁRIOS
// =====================================================

function mostrarLoading() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('produtos-container').style.display = 'none';
    document.getElementById('error-message').style.display = 'none';
}

function ocultarLoading() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('produtos-container').style.display = 'grid';
}

function mostrarErro(mensagem) {
    const errorDiv = document.getElementById('error-message');
    errorDiv.querySelector('p').textContent = mensagem;
    errorDiv.style.display = 'block';
    document.getElementById('produtos-container').style.display = 'none';
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

function atualizarResumo() {
    const total = produtos.length;
    const ativos = produtos.filter(p => p.ativo).length;
    const disponiveisVenda = produtos.filter(p => p.disponivel_venda).length;

    // Aqui você pode adicionar mais métricas se necessário
    console.log(`Total: ${total}, Ativos: ${ativos}, Disponíveis para venda: ${disponiveisVenda}`);
}