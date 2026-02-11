<?php
$pageTitle = 'Produtos';
$pageSubtitle = 'Gerencie todos os produtos dos seus eventos';
$currentPage = 'produtos';

// Conteúdo da página
ob_start();
?>

<!-- CSS Responsive -->
<link rel="stylesheet" href="../../../frontend/assets/css/organizador-responsive.css">

<!-- Container com overflow e responsividade -->
<div class="organizador-container h-full overflow-y-auto px-4 py-6">
    <!-- Navegação Sequencial -->
    <div class="organizador-nav-tabs mb-6 flex gap-4">
        <a href="?page=questionario"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
            ← Questionário
        </a>
        <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
            6- Produtos
        </span>
        <a href="?page=kits-templates"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
            Templates de Kit →
        </a>
    </div>

    <!-- Header da página -->
    <div class="organizador-header flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestão de Produtos</h1>
            <p class="text-gray-600 mt-1">Gerencie todos os produtos dos seus eventos</p>
        </div>
        <button id="btnNovoProduto" class="organizador-btn btn-primary">
            <i class="fas fa-plus mr-2"></i>
            Novo Produto
        </button>
    </div>

    <!-- Filtros -->
    <div class="organizador-filters card mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
                <input type="text" id="filtroNome" placeholder="Buscar por nome..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                <input type="text" id="filtroDescricao" placeholder="Buscar por descrição..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="filtroStatus"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Todos</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="aplicarFiltros()" class="btn-primary w-full">
                    <i class="fas fa-search mr-2"></i>
                    Filtrar
                </button>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div id="loading" class="text-center py-8" style="display: none;">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        <p class="mt-2 text-gray-600">Carregando produtos...</p>
    </div>

    <!-- Lista de Produtos -->
    <div id="produtos-container" class="organizador-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Produtos serão carregados aqui -->
    </div>

    <!-- Paginação -->
    <div id="paginacao" class="mt-6 flex justify-center" style="display: none;">
        <nav class="flex items-center space-x-2">
            <button id="btn-anterior"
                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-chevron-left mr-1"></i>
                Anterior
            </button>

            <div id="paginas" class="flex items-center space-x-1">
                <!-- Páginas serão geradas aqui -->
            </div>

            <button id="btn-proximo"
                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Próximo
                <i class="fas fa-chevron-right ml-1"></i>
            </button>
        </nav>
    </div>

    <!-- Erro -->
    <div id="error-message" class="text-center py-8" style="display: none;">
        <div class="card">
            <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
            <p class="text-red-600 mb-4">Erro ao carregar produtos.</p>
            <button onclick="carregarProdutos()" class="btn-primary">
                <i class="fas fa-redo mr-2"></i>
                Tentar novamente
            </button>
        </div>
    </div>

    <!-- Modal Produto -->
    <div id="modalProduto" class="organizador-modal fixed inset-0 z-50 hidden">
        <div class="flex min-h-screen items-center justify-center bg-black/60 px-4 py-4">
            <div class="w-full max-w-2xl rounded-lg bg-white shadow-2xl max-h-[95vh] flex flex-col">
                <div class="flex h-full flex-col">
                    <div class="flex items-center justify-between border-b px-5 py-3">
                        <h2 class="text-lg font-semibold text-gray-900" id="modalProdutoTitulo">Novo Produto</h2>
                        <button type="button" onclick="fecharModalProduto()"
                            class="text-gray-400 transition-colors hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto px-5 py-4" style="max-height: calc(95vh - 120px);">
                        <div id="alertaProduto" class="hidden mb-4"></div>

                        <form id="formProduto" class="space-y-3" enctype="multipart/form-data">
                            <input type="hidden" id="produto_id" name="produto_id">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                                <input type="text" id="produto_nome" name="produto_nome"
                                    placeholder="Nome do produto"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                <textarea id="produto_descricao" name="produto_descricao" rows="2"
                                    placeholder="Descrição..."
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Preço (R$) *</label>
                                    <input type="number" id="produto_preco" name="produto_preco" step="0.01" min="0"
                                        placeholder="0.00"
                                        class="w-full px-3 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                        required>
                                </div>
                                <div class="flex items-end">
                                    <label class="flex items-center">
                                        <input type="checkbox" id="produto_disponivel_venda" name="produto_disponivel_venda"
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        <span class="ml-2 text-sm text-gray-700">Disponível para venda</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Foto</label>
                                <input type="file" id="produto_foto" name="produto_foto" accept="image/*"
                                    class="w-full text-sm">
                                <div id="preview_foto" class="mt-2 hidden">
                                    <img id="preview_img" src="" alt="Preview"
                                        class="w-24 h-24 object-cover rounded">
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Footer da Modal -->
                    <div class="bg-gray-50 px-5 py-3 border-t flex justify-end space-x-2">
                        <button type="button" onclick="fecharModalProduto()"
                            class="px-4 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Cancelar</button>
                        <button type="submit" form="formProduto" id="btnSalvarProduto"
                            class="px-4 py-1.5 text-sm text-white bg-brand-green rounded hover:bg-green-700">
                            <span id="btnSalvarProdutoTexto">Salvar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../../frontend/js/produtos.js"></script>
</div>

<?php
$pageContent = ob_get_clean();
echo $pageContent;
?>

<style>
    .btn {
        padding: 10px 15px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    /* Estilo customizado para scroll na modal */
    #modalProduto .overflow-y-auto {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }

    #modalProduto .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }

    #modalProduto .overflow-y-auto::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 3px;
    }

    #modalProduto .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }

    #modalProduto .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }
</style>