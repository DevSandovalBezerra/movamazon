<?php
$pageTitle = 'Templates de Kit';
$pageSubtitle = 'Gerencie templates de kits reutilizáveis';
$currentPage = 'kits-templates';

// Conteúdo da página
ob_start();
?>

<!-- Header da página -->
<div class="mb-4 sm:mb-6">
    <!-- Navegação Sequencial -->
    <div class="mb-4 sm:mb-6 flex gap-2 sm:gap-4">
        <a href="?page=produtos" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
            ← Produtos
        </a>
        <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
            7- Templates de Kit
        </span>
        <a href="?page=kits-evento" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
            Kits do Evento →
        </a>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Templates de Kit</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Gerencie templates de kits reutilizáveis</p>
        </div>
        <button id="btnNovoTemplate" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>
            Novo Template
        </button>
    </div>

    <!-- Filtros -->
    <div class="card mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
                <input type="text" id="filtroNome" placeholder="Buscar por nome..." class="w-full px-2 py-2 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                <input type="text" id="filtroDescricao" placeholder="Buscar por descrição..." class="w-full px-2 py-2 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="filtroStatus" class="w-full px-2 py-2 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                    <option value="">Todos</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </select>
            </div>

            <div class="flex items-end">
                <button onclick="aplicarFiltros()" class="btn-primary w-full text-xs sm:text-sm py-2 sm:py-2.5">
                    <i class="fas fa-search mr-2"></i>
                    Filtrar
                </button>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div id="loading" class="text-center py-4 sm:py-6" style="display: none;">
        <div class="inline-block animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-primary-600"></div>
        <p class="mt-1 sm:mt-2 text-gray-600 text-xs sm:text-sm">Carregando templates...</p>
    </div>

    <!-- Lista de Templates -->
    <div id="templates-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
        <!-- Templates serão carregados aqui -->
    </div>

    <!-- Paginação -->
    <div id="paginacao" class="mt-4 sm:mt-6 flex justify-center" style="display: none;">
        <nav class="flex items-center space-x-2">
            <button id="btn-anterior" class="px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-chevron-left mr-1"></i>
                Anterior
            </button>

            <div id="paginas" class="flex items-center space-x-1 text-xs sm:text-sm">
                <!-- Páginas serão geradas aqui -->
            </div>

            <button id="btn-proximo" class="px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Próximo
                <i class="fas fa-chevron-right ml-1"></i>
            </button>
        </nav>
    </div>

    <!-- Erro -->
    <div id="error-message" class="text-center py-4 sm:py-6" style="display: none;">
        <div class="card p-3 sm:p-4">
            <i class="fas fa-exclamation-triangle text-red-500 text-3xl sm:text-4xl mb-2 sm:mb-4"></i>
            <p class="text-red-600 mb-2 sm:mb-4 text-sm">Erro ao carregar templates.</p>
            <button onclick="carregarTemplates()" class="btn-primary text-xs sm:text-sm py-2 sm:py-2.5">
                <i class="fas fa-redo mr-2"></i>
                Tentar novamente
            </button>
        </div>
    </div>

    <!-- Modal Template -->
    <div id="modalTemplate" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900" id="modalTemplateTitulo">Novo Template</h2>
                        <button onclick="fecharModalTemplate()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="formTemplate" class="space-y-6">
                        <input type="hidden" id="template_id" name="template_id">

                        <!-- Informações Básicas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Template *</label>
                                <input type="text" id="template_nome" name="template_nome" placeholder="Ex: Kit Atleta, Kit Promocional" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Preço Base (R$) *</label>
                                <input type="number" id="template_preco_base" name="template_preco_base" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                            <textarea id="template_descricao" name="template_descricao" rows="3" placeholder="Descreva o template..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                        </div>

                        <!-- Foto do Kit -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto do Kit</label>
                            <input type="file" id="template_foto" name="template_foto" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <div id="preview_foto_template" class="mt-2 hidden">
                                <img id="preview_img_template" src="../../assets/img/kits/placeholder.png" alt="Preview" class="w-32 h-32 object-cover rounded-lg">
                            </div>
                        </div>

                        <!-- Disponibilidade para Venda -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" id="template_disponivel_venda" name="template_disponivel_venda" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="ml-2 text-sm text-gray-700">Disponível para venda individual</span>
                            </label>
                        </div>

                        <!-- Produtos do Template -->
                        <div class="border-t pt-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Produtos do Template</h3>
                                <span id="contador-produtos" class="text-sm text-gray-500">0 produtos</span>
                            </div>
                            <div id="produtos-template-container" class="space-y-4">
                                <!-- Produtos serão adicionados aqui -->
                            </div>
                            <button type="button" onclick="adicionarProdutoTemplate()" class="mt-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-plus mr-2"></i>
                                Adicionar Produto
                            </button>
                        </div>

                        <!-- Resumo de Preços -->
                        <div class="border-t pt-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Resumo de Preços</h3>
                                <button type="button" onclick="calcularPrecoBaseAutomatico()" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
                                    <i class="fas fa-calculator mr-2"></i>
                                    Calcular Preço Base
                                </button>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Custo Total dos Produtos</label>
                                        <p id="custo_produtos" class="text-lg font-semibold text-gray-900">R$ 0,00</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Preço Base</label>
                                        <p id="preco_base_display" class="text-lg font-semibold text-blue-600">R$ 0,00</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Margem</label>
                                        <p id="margem" class="text-lg font-semibold text-green-600">R$ 0,00</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-end space-x-3 pt-6 border-t">
                            <button type="button" onclick="fecharModalTemplate()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                                <span id="btnSalvarTemplateTexto">Criar Template</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/kits-templates.js"></script>

    <?php
    $pageContent = ob_get_clean();
    echo $pageContent;
    ?>
