<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../../api/db.php';
require_once '../../../api/helpers/organizador_context.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    header('Location: ../../../auth/login.php');
    exit();
}

// Buscar eventos do organizador
$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];

$stmt = $pdo->prepare("SELECT id, nome, data_inicio FROM eventos WHERE (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL ORDER BY data_inicio DESC");
$stmt->execute([$organizador_id, $usuario_id]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;

$pageTitle = 'Produtos Extras';
$pageSubtitle = 'Gerencie produtos extras dos seus eventos';
$currentPage = 'produtos-extras';

// Conteúdo da página
ob_start();
?>

<!-- Navegação Simples -->
<div class="mb-4 sm:mb-6 flex gap-2 sm:gap-4">
    <a href="?page=camisas" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
        ← Camisas
    </a>
    <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
        11- Produtos Extras
    </span>
    <a href="?page=programacao" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
        Programação →
    </a>
</div>

<div class="flex items-center justify-between mb-4 sm:mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Produtos Extras</h1>
        <p class="text-gray-600 mt-1 text-sm sm:text-base">Gerencie produtos extras dos seus eventos</p>
    </div>
    <button id="btnNovoProdutoExtra" class="btn-primary">
        <i class="fas fa-plus mr-2"></i>
        Novo Produto Extra
    </button>
</div>

<!-- Filtros -->
<div class="card mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Evento</label>
            <select id="filtroEvento" class="w-full px-2 py-2 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                <option value="">Selecione um evento</option>
                <?php foreach ($eventos as $evento) { ?>
                    <option value="<?php echo $evento['id']; ?>" <?php if ($evento['id'] == $evento_id) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($evento['nome']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
            <select id="filtroCategoria" class="w-full px-2 py-2 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                <option value="">Todas as categorias</option>
                <option value="vestuario">Vestuário</option>
                <option value="acessorio">Acessório</option>
                <option value="seguro">Seguro</option>
                <option value="outros">Outros</option>
            </select>
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

<!-- Resumo -->
<div class="card mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
        <div class="text-center">
            <div class="bg-blue-100 text-blue-800 rounded-lg p-3 sm:p-4">
                <div class="text-2xl font-bold" id="total-produtos-extras">0</div>
                <div class="text-sm">Total de Produtos</div>
            </div>
        </div>
        <div class="text-center">
            <div class="bg-green-100 text-green-800 rounded-lg p-3 sm:p-4">
                <div class="text-2xl font-bold" id="produtos-ativos">0</div>
                <div class="text-sm">Ativos</div>
            </div>
        </div>
        <div class="text-center">
            <div class="bg-yellow-100 text-yellow-800 rounded-lg p-3 sm:p-4">
                <div class="text-2xl font-bold" id="valor-total">R$ 0,00</div>
                <div class="text-sm">Valor Total</div>
            </div>
        </div>
        <div class="text-center">
            <div class="bg-purple-100 text-purple-800 rounded-lg p-3 sm:p-4">
                <div class="text-2xl font-bold" id="categorias-unicas">0</div>
                <div class="text-sm">Categorias</div>
            </div>
        </div>
    </div>
</div>

<!-- Loading -->
<div id="loading" class="text-center py-4 sm:py-6" style="display: none;">
    <div class="inline-block animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-primary-600"></div>
    <p class="mt-1 sm:mt-2 text-gray-600 text-xs sm:text-sm">Carregando produtos extras...</p>
</div>

<!-- Lista de Produtos Extras -->
<div id="produtos-extras-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
    <!-- Produtos extras serão carregados aqui -->
</div>

<!-- Paginação -->
<div id="paginacao" class="flex justify-center items-center mt-4 sm:mt-8 space-x-2 sm:space-x-4" style="display: none;">
    <button id="btn-anterior" class="px-2 sm:px-4 py-1.5 sm:py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm">
        <i class="fas fa-chevron-left mr-2"></i>
        Anterior
    </button>
    <span id="info-paginacao" class="text-gray-600 text-xs sm:text-sm"></span>
    <button id="btn-proximo" class="px-2 sm:px-4 py-1.5 sm:py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm">
        Próximo
        <i class="fas fa-chevron-right ml-2"></i>
    </button>
</div>

<!-- Modal Produto Extra -->
<div id="modalProdutoExtra" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 id="modalProdutoExtraTitulo" class="text-xl font-bold text-gray-900">Novo Produto Extra</h3>
                    <button onclick="fecharModalProdutoExtra()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="formProdutoExtra">
                    <input type="hidden" id="produto_extra_id" name="id">

                    <!-- Informações Básicas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Evento *</label>
                            <select id="produto_extra_evento_id" name="evento_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                                <option value="">Selecione um evento</option>
                                <?php foreach ($eventos as $evento) { ?>
                                    <option value="<?php echo $evento['id']; ?>" <?php if ($evento['id'] == $evento_id) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($evento['nome']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Produto Extra *</label>
                            <input type="text" id="produto_extra_nome" name="nome" placeholder="Ex: Kit Camisa + Medalha" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Valor Total (R$) *</label>
                            <input type="number" id="produto_extra_valor" name="valor" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                        </div>
                        <div></div>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" id="produto_extra_disponivel_venda" name="disponivel_venda" class="mr-2" checked>
                            <label for="produto_extra_disponivel_venda" class="text-sm font-medium text-gray-700">Disponível para venda separada</label>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                        <textarea id="produto_extra_descricao" name="descricao" rows="3" placeholder="Descreva o produto extra..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                    </div>

                    <!-- Seleção de Produtos -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Produtos Incluídos *</label>

                        <!-- Adicionar Produto -->
                        <div class="flex items-center space-x-4 mb-4">
                            <select id="produto_id" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Selecione um produto</option>
                                <!-- Produtos serão carregados aqui -->
                            </select>
                            <button type="button" onclick="adicionarProduto()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i>
                                Adicionar
                            </button>
                        </div>

                        <!-- Produtos Selecionados -->
                        <div id="produtos-selecionados" class="space-y-2 min-h-[100px] border border-gray-200 rounded-lg p-4">
                            <p class="text-gray-500 text-sm">Nenhum produto selecionado</p>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="fecharModalProdutoExtra()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            <span id="btnSalvarProdutoExtraTexto">Salvar Produto Extra</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../../js/produtos-extras.js?v=<?php echo time(); ?>"></script>

<?php
$pageContent = ob_get_clean();
echo $pageContent;
?>
