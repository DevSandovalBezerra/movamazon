<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    header('Location: ../../auth/login.php');
    exit();
}

require_once '../../../api/db.php';
require_once '../../../api/helpers/organizador_context.php';

// Buscar eventos do organizador
$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];

$stmt = $pdo->prepare("SELECT id, nome, data_inicio FROM eventos WHERE (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL ORDER BY data_inicio DESC");
$stmt->execute([$organizador_id, $usuario_id]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;

$pageTitle = 'Termos de Inscrição';
?>

<!-- Navegação Sequencial -->
<div class="mb-4 sm:mb-6 flex gap-2 sm:gap-4">
    <a href="?page=questionario" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
        ← Questionário
    </a>
    <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
        6- Termos de Inscrição
    </span>
    <a href="?page=produtos" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
        Produtos →
    </a>
</div>

<!-- Cabeçalho -->
<div class="mb-6 sm:mb-8">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 mb-2">
                <a href="../index.php" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
                <span class="text-gray-400">/</span>
                <span class="font-semibold">Termos de Inscrição</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Termos de Inscrição</h1>
            <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Gerencie os termos e condições dos seus eventos</p>
        </div>
        <button onclick="abrirModalCriar()" class="bg-brand-green hover:bg-green-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg flex items-center text-sm">
            <i class="fas fa-plus mr-2"></i>
            Novo Termo
        </button>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
            <select id="filtroEvento" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
                <option value="">Todos os eventos</option>
                <?php foreach ($eventos as $evento) { ?>
                    <option value="<?php echo $evento['id']; ?>" <?php if ($evento['id'] == $evento_id) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($evento['nome']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Modalidade</label>
            <select id="filtroModalidade" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm" disabled>
                <option value="">Todas as modalidades</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <select id="filtroTipo" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
                <option value="">Todos os tipos</option>
                <option value="geral">Geral</option>
                <option value="modalidade">Modalidade</option>
                <option value="regulamento">Regulamento</option>
                <option value="privacidade">Política de Privacidade</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select id="filtroStatus" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
                <option value="">Todos</option>
                <option value="1">Ativo</option>
                <option value="0">Inativo</option>
            </select>
        </div>
    </div>

    <div class="flex items-end">
        <button onclick="aplicarFiltros()" class="btn-primary w-full text-xs sm:text-sm py-2 sm:py-2.5">
            <i class="fas fa-search mr-2"></i>
            Filtrar
        </button>
    </div>
</div>

<!-- Loading -->
<div id="loading" class="text-center py-4 sm:py-6" style="display: none;">
    <div class="inline-block animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-brand-green"></div>
    <p class="mt-1 sm:mt-2 text-gray-600 text-xs sm:text-sm">Carregando termos...</p>
</div>

<!-- Tabela de Termos -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Título
                    </th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Evento
                    </th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Modalidade
                    </th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipo
                    </th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Versão
                    </th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-right text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody id="termosTableBody" class="bg-white divide-y divide-gray-200">
                <!-- Termos serão carregados aqui via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Lista de Termos -->
<div id="termos-container" class="space-y-4">
    <!-- Termos serão carregados aqui -->
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
        <p class="text-red-600 mb-2 sm:mb-4 text-sm">Erro ao carregar termos.</p>
        <button onclick="carregarTermos()" class="btn-primary text-xs sm:text-sm py-2 sm:py-2.5">
            <i class="fas fa-redo mr-2"></i>
            Tentar novamente
        </button>
    </div>
</div>

<!-- Modal Criar/Editar Termo -->
<div id="modalTermo" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b">
                <h3 id="modalTitulo" class="text-lg font-semibold text-gray-900">Novo Termo</h3>
                <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                <form id="formTermo">
                    <input type="hidden" id="termoId" name="id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Evento *</label>
                            <select id="eventoId" name="evento_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required onchange="carregarModalidadesModal(this.value)">
                                <option value="">Selecione um evento</option>
                                <?php foreach ($eventos as $evento) { ?>
                                    <option value="<?php echo $evento['id']; ?>">
                                        <?php echo htmlspecialchars($evento['nome']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Modalidade</label>
                            <select id="modalidadeId" name="modalidade_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                <option value="">Termo geral (todas as modalidades)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                            <input type="text" id="titulo" name="titulo" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Versão</label>
                            <input type="text" id="versao" name="versao" class="w-full border border-gray-300 rounded-lg px-3 py-2" value="1.0">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conteúdo *</label>
                        <textarea id="conteudo" name="conteudo" class="w-full border border-gray-300 rounded-lg px-3 py-2" rows="15" required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="ativo" name="ativo" class="mr-2" checked>
                            <span class="text-sm text-gray-700">Termo ativo</span>
                        </label>
                    </div>
                </form>
            </div>

            <div class="flex items-center justify-end space-x-3 p-6 border-t bg-gray-50">
                <button onclick="fecharModal()" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button onclick="salvarTermo()" class="px-4 py-2 bg-brand-green text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-save mr-2"></i>
                    Salvar
                </button>
            </div>
        </div>
    </div>
</div>

    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
    <script src="../../js/termos-inscricao.js"></script>
