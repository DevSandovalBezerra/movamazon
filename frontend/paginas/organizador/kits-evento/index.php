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

$pageTitle = 'Kits do Evento';
$pageSubtitle = 'Gerencie kits dos seus eventos';
$currentPage = 'kits-evento';

// Conteúdo da página
ob_start();
?>

<!-- Header da página -->
<div class="mb-4 sm:mb-6">
    <!-- Navegação Sequencial -->
    <div class="mb-4 sm:mb-6 flex gap-2 sm:gap-4">
        <a href="?page=kits-templates" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
            ← Templates de Kit
        </a>
        <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
            8- Kits do Evento
        </span>
        <a href="?page=retirada-kits" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
            Retirada de Kits →
        </a>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Kits do Evento</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Gerencie kits dos seus eventos</p>
        </div>
        <div class="flex space-x-2">
            <button id="btnAplicarTemplate" class="btn-primary text-xs sm:text-sm py-2 sm:py-2.5">
                <i class="fas fa-magic mr-2"></i>
                Aplicar Template
            </button>
            <!-- <button id="btnNovoKit" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>
            Criar Manual
        </button> -->
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Evento</label>
                <select id="filtroEvento" class="w-full px-2 py-2 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                    <option value="">Todos os eventos</option>
                    <?php foreach ($eventos as $evento) { ?>
                        <option value="<?php echo $evento['id']; ?>" <?php if ($evento['id'] == $evento_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($evento['nome']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Modalidade</label>
                <select id="filtroModalidade" class="w-full px-2 py-2 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" disabled>
                    <option value="">Todas as modalidades</option>
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
        <h3 class="text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Resumo dos Kits</h3>
        <div id="resumo-kits" class="grid grid-cols-1 md:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
            <div class="text-center p-3 sm:p-4 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600" id="total-kits">0</div>
                <div class="text-sm text-gray-600">Total de Kits</div>
            </div>
            <div class="text-center p-3 sm:p-4 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600" id="kits-ativos">0</div>
                <div class="text-sm text-gray-600">Kits Ativos</div>
            </div>
            <div class="text-center p-3 sm:p-4 bg-yellow-50 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600" id="valor-total">R$ 0,00</div>
                <div class="text-sm text-gray-600">Valor Total</div>
            </div>
            <div class="text-center p-3 sm:p-4 bg-purple-50 rounded-lg">
                <div class="text-2xl font-bold text-purple-600" id="modalidades-com-kit">0</div>
                <div class="text-sm text-gray-600">Modalidades com Kit</div>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div id="loading" class="text-center py-4 sm:py-6" style="display: none;">
        <div class="inline-block animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-primary-600"></div>
        <p class="mt-1 sm:mt-2 text-gray-600 text-xs sm:text-sm">Carregando kits...</p>
    </div>

    <!-- Lista de Kits -->
    <div id="kits-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
        <!-- Kits serão carregados aqui -->
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
            <p class="text-red-600 mb-2 sm:mb-4 text-sm">Erro ao carregar kits.</p>
            <button onclick="carregarKits()" class="btn-primary text-xs sm:text-sm py-2 sm:py-2.5">
                <i class="fas fa-redo mr-2"></i>
                Tentar novamente
            </button>
        </div>
    </div>

    <!-- Modal Aplicar Template -->
    <div id="modalAplicarTemplate" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Aplicar Template</h2>
                        <button onclick="fecharModalAplicarTemplate()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="formAplicarTemplate" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Template *</label>
                            <select id="template_id" name="template_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                                <option value="">Selecione um template</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Evento *</label>
                            <select id="evento_id" name="evento_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                                <option value="">Selecione um evento</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Modalidades *</label>
                            <div id="modalidades-container" class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-3">
                                <!-- Modalidades serão carregadas aqui -->
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-6 border-t">
                            <button type="button" onclick="fecharModalAplicarTemplate()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                                Aplicar Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Kit -->
    <div id="modalKit" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900" id="modalKitTitulo">Novo Kit</h2>
                        <button onclick="fecharModalKit()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="formKit" class="space-y-6">
                        <input type="hidden" id="kit_id" name="kit_id">

                        <!-- Seleção de Evento e Modalidade -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Evento *</label>
                                <select id="kit_evento_id" name="kit_evento_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                                    <option value="">Selecione um evento</option>
                                </select>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Modalidades *</label>
                                    <button type="button" id="btn-selecionar-todas-modalidades" class="px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors shadow-sm hover:shadow-md">
                                        <i class="fas fa-check-square mr-1"></i><span id="btn-selecionar-todas-texto">Selecionar todas</span>
                                    </button>
                                </div>
                                <div id="kit-modalidades-container" class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-3">
                                    <!-- Modalidades serão carregadas aqui -->
                                </div>
                            </div>
                        </div>

                        <!-- Template do Kit (reaplicar dados do template no kit existente) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template (opcional)</label>
                                <select id="kit_template_id" name="kit_template_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">Selecione um template</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Reaplica apenas os dados do template (produtos/foto/valor/disponibilidade).</p>
                            </div>
                            <div class="flex items-end">
                                <button type="button" id="btnReaplicarTemplateKit" class="w-full px-4 py-2 border border-primary-600 text-primary-700 hover:bg-primary-50 rounded-lg transition-colors">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    Reaplicar Template
                                </button>
                            </div>
                        </div>

                        <!-- Informações do Kit -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Kit *</label>
                                <input type="text" id="kit_nome" name="kit_nome" placeholder="Ex: Kit Completo, Kit Básico" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valor (R$) *</label>
                                <input type="number" id="kit_valor" name="kit_valor" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                            <textarea id="kit_descricao" name="kit_descricao" rows="3" placeholder="Descreva o kit..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                        </div>

                        <!-- Disponibilidade para Venda -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" id="kit_disponivel_venda" name="kit_disponivel_venda" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="ml-2 text-sm text-gray-700">Disponível para venda individual</span>
                            </label>
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-end space-x-3 pt-6 border-t">
                            <button type="button" onclick="fecharModalKit()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                                <span id="btnSalvarKitTexto">Criar Kit</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/kits-evento.js"></script>

    <?php
    $pageContent = ob_get_clean();
    echo $pageContent;
    ?>
