<?php
// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Lotes de Inscrição - MovAmazon</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/custom.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50">
    <!-- Layout será carregado pelo index.php principal -->

    <div class="p-4 sm:p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Navegação Simples -->
            <div class="mb-4 sm:mb-6 flex gap-2 sm:gap-4">
                <a href="?page=modalidades" class="bg-brand-green hover:bg-green-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
                    ← Modalidades
                </a>
                <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
                    3- Lotes de Inscrição
                </span>
                <a href="?page=cupons-remessa" class="bg-brand-green hover:bg-green-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
                    Cupons de Desconto →
                </a>
            </div>

            <!-- Cabeçalho -->
            <div class="mb-6 sm:mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center space-x-2 mb-2">
                            <a href="../index.php" class="text-brand-green hover:text-green-700">
                                <i class="fas fa-arrow-left"></i> Dashboard
                            </a>
                            <span class="text-gray-400">/</span>
                            <span class="font-semibold">Lotes de Inscrição</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Gerenciar Lotes de Inscrição</h1>
                        <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Configure os lotes de inscrição dos seus eventos</p>
                    </div>
                    <button onclick="abrirModalCriar()" class="bg-brand-green hover:bg-green-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg flex items-center text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Novo Lote
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Público</label>
                        <select id="filtroTipoPublico" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
                            <option value="">Todos</option>
                            <option value="comunidade_academica">Comunidade Acadêmica</option>
                            <option value="publico_geral">Público Geral</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="filtroStatus" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
                            <option value="1">Ativos</option>
                            <option value="0">Inativos</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="aplicarFiltros()" class="w-full bg-brand-green hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg flex items-center justify-center text-xs sm:text-sm">
                            <i class="fas fa-search mr-2"></i>
                            Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estados de Loading e Erro -->
            <div id="loading-lotes" class="hidden">
                <div class="flex items-center justify-center py-6 sm:py-12">
                    <div class="animate-spin rounded-full h-6 w-6 sm:h-12 sm:w-12 border-b-2 border-brand-green"></div>
                    <span class="ml-2 sm:ml-3 text-gray-600 text-xs sm:text-sm">Carregando lotes...</span>
                </div>
            </div>

            <div id="error-lotes" class="hidden">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 sm:p-6 text-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl sm:text-2xl mb-2"></i>
                    <h3 class="text-red-800 font-semibold mb-1 text-sm sm:text-base">Erro ao carregar lotes</h3>
                    <p class="text-red-600 text-xs sm:text-sm" id="error-message">Ocorreu um erro inesperado.</p>
                    <button onclick="carregarLotes()" class="mt-3 sm:mt-4 bg-red-600 hover:bg-red-700 text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm">
                        Tentar Novamente
                    </button>
                </div>
            </div>

            <div id="sem-lotes" class="hidden">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 sm:p-12 text-center">
                    <i class="fas fa-tags text-gray-400 text-3xl sm:text-4xl mb-3 sm:mb-4"></i>
                    <h3 class="text-gray-600 font-semibold mb-2">Nenhum lote encontrado</h3>
                    <p class="text-gray-500 mb-3 sm:mb-4 text-sm">Crie seu primeiro lote de inscrição para começar.</p>
                    <button onclick="abrirModalCriar()" class="bg-brand-green hover:bg-green-700 text-white px-4 sm:px-6 py-2 rounded-lg text-xs sm:text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Criar Primeiro Lote
                    </button>
                </div>
            </div>

            <!-- Tabela de Lotes -->
            <div id="tabela-lotes" class="hidden">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Lote
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Evento
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Preço
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Período
                                    </th>


                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody-lotes" class="bg-white divide-y divide-gray-200">
                                <!-- Dados serão inseridos via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Criação/Edição -->
    <div id="modal-lote" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-start justify-center min-h-screen p-4 overflow-y-auto">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                <div class="p-6 flex flex-col h-full overflow-y-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-900" id="modal-title">Novo Lote de Inscrição</h3>
                        <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="form-lote" class="space-y-6">
                        <input type="hidden" id="lote-id" name="id">

                        <!-- Etapa 1: Evento e Modalidades -->
                        <div id="etapa-1" class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">1. Seleção do Evento e Modalidades</h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Evento *</label>
                                    <select id="evento-id" name="evento_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                        <option value="">Selecione um evento</option>
                                        <?php foreach ($eventos as $evento) { ?>
                                            <option value="<?php echo $evento['id']; ?>">
                                                <?php echo htmlspecialchars($evento['nome']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Número do Lote *</label>
                                    <input type="number" id="numero-lote" name="numero_lote" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Modalidades *</label>
                                <div id="modalidades-container" class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-3">
                                    <p class="text-gray-500 text-sm">Selecione um evento para ver as modalidades disponíveis</p>
                                </div>
                            </div>
                        </div>

                        <!-- Etapa 2: Configuração do Lote -->
                        <div id="etapa-2" class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">2. Configuração do Lote</h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Tipo de público agora vem da categoria da modalidade -->

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Preço (R$) *</label>
                                    <input type="number" id="preco" name="preco" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Preço por Extenso</label>
                                <input type="text" id="preco-extenso" name="preco_por_extenso" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Ex: Sessenta e cinco reais">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de Início *</label>
                                    <input type="date" id="data-inicio" name="data_inicio" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de Fim *</label>
                                    <input type="date" id="data-fim" name="data_fim" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Vagas Disponíveis</label>
                                    <input type="number" id="vagas-disponiveis" name="vagas_disponiveis" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Idade Mínima</label>
                                    <input type="number" id="idade-min" name="idade_min" min="0" max="120" value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Idade Máxima</label>
                                    <input type="number" id="idade-max" name="idade_max" min="0" max="120" value="100" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                </div>
                            </div>
                        </div>

                        <!-- Etapa 3: Taxas e Configurações -->
                        <div id="etapa-3" class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">3. Taxas e Configurações</h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Taxa de Serviço (R$)</label>
                                    <input type="number" id="taxa-servico" name="taxa_servico" step="0.01" min="0" value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Quem Paga a Taxa</label>
                                    <select id="quem-paga-taxa" name="quem_paga_taxa" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                        <option value="participante">Participante</option>
                                        <option value="organizador">Organizador</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="desconto-idoso" name="desconto_idoso" class="h-4 w-4 text-brand-green border-gray-300 rounded">
                                <label for="desconto-idoso" class="ml-2 block text-sm text-gray-900">
                                    Aplicar desconto para idosos
                                </label>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-between pt-6 border-t mt-4 bg-white">
                            <button type="button" onclick="fecharModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg">
                                Cancelar
                            </button>
                            <button type="submit" class="bg-brand-green hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                                <span id="btn-salvar-text">Salvar Lote</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/utils/modalidades-selector.js"></script>
    <script src="../../js/lotes-inscricao.js"></script>
</body>

</html>