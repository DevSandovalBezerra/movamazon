<?php
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
    <title>Gerenciar Lotes - MovAmazon</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../../assets/css/custom.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50">
    <?php include '../../../includes/organizador_layout.php'; ?>

    <div class="p-4 sm:p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Cabeçalho -->
            <div class="mb-6 sm:mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center space-x-2 mb-2">
                            <a href="../index.php" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-arrow-left"></i> Dashboard
                            </a>
                            <span class="text-gray-400">/</span>
                            <span class="font-semibold">Lotes</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Gerenciar Lotes</h1>
                        <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Gerencie os lotes de inscrição dos seus eventos</p>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3 lg:gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
                        <select id="filtroEvento" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
                            <option value="">Selecione um evento</option>
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
                            <option value="">Selecione uma modalidade</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="aplicarFiltros()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 rounded-lg flex items-center justify-center text-xs sm:text-sm">
                            <i class="fas fa-search mr-2"></i>
                            Aplicar Filtros
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estados de Loading e Erro -->
            <div id="loading-lotes" class="hidden">
                <div class="flex items-center justify-center py-6 sm:py-12">
                    <div class="animate-spin rounded-full h-6 w-6 sm:h-12 sm:w-12 border-b-2 border-blue-600"></div>
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
                    <p class="text-gray-500 mb-3 sm:mb-4 text-sm">Selecione um evento e modalidade para ver os lotes ou criar novos.</p>
                    <button onclick="abrirModalCriarLote()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Criar Primeiro Lote
                    </button>
                </div>
            </div>

            <div id="selecionar-filtros" class="bg-blue-50 border border-blue-200 rounded-lg p-6 sm:p-12 text-center">
                <i class="fas fa-filter text-blue-400 text-3xl sm:text-4xl mb-3 sm:mb-4"></i>
                <h3 class="text-blue-800 font-semibold mb-1 sm:mb-2 text-sm sm:text-base">Selecione um Evento</h3>
                <p class="text-blue-600 mb-3 sm:mb-4 text-xs sm:text-sm">Escolha um evento e modalidade para gerenciar os lotes.</p>
            </div>

            <!-- Container dos Lotes -->
            <div id="lotes-container" class="hidden">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Lotes do Evento</h2>
                    <button onclick="abrirModalCriarLote()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Novo Lote
                    </button>
                </div>

                <!-- Informações de paginação -->
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <div class="text-sm text-gray-600">
                        <span id="info-paginacao">Mostrando 0 de 0 lotes</span>
                        <span id="contador-total" class="ml-2 text-blue-600 font-medium"></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <select id="itens-por-pagina" class="border border-gray-300 rounded px-2 py-1 text-xs sm:text-sm">
                            <option value="9">9 por página</option>
                            <option value="15">15 por página</option>
                            <option value="30">30 por página</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4 lg:gap-6" id="lotes-grid">
                    <!-- Lotes serão renderizados aqui -->
                </div>

                <!-- Paginação -->
                <div id="paginacao" class="flex items-center justify-center mt-4 sm:mt-8">
                    <nav class="flex items-center space-x-2">
                        <button id="btn-anterior" onclick="paginaAnterior()" class="px-2 sm:px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm">
                            <i class="fas fa-chevron-left"></i>
                        </button>

                        <div id="numeros-pagina" class="flex items-center space-x-1 text-xs sm:text-sm">
                            <!-- Números de página serão gerados aqui -->
                        </div>

                        <button id="btn-proximo" onclick="paginaProximo()" class="px-2 sm:px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Criar/Editar Lote -->
    <div id="modalLote" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900" id="modalTitulo">Criar Novo Lote</h2>
                        <button onclick="fecharModalLote()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="formLote" class="space-y-6">
                        <input type="hidden" id="lote_id" name="lote_id">

                        <!-- Modalidade -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Modalidade *</label>
                            <select id="modalidade_id" name="modalidade_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                <option value="">Selecione uma modalidade</option>
                            </select>
                        </div>

                        <!-- Categoria do Lote -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categoria do Lote *</label>
                            <input type="text" id="categoria_modalidade" name="categoria_modalidade" placeholder="Ex: Comunidade acadêmica, Público geral" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                        </div>

                        <!-- Idades -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Idade Mínima *</label>
                                <input type="number" id="idade_min" name="idade_min" min="0" max="120" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Idade Máxima *</label>
                                <input type="number" id="idade_max" name="idade_max" min="0" max="120" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                            </div>
                        </div>

                        <!-- Limite de Vagas -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Limite de Vagas (opcional)</label>
                            <input type="number" id="limite_vagas" name="limite_vagas" min="1" placeholder="Deixe em branco para ilimitado" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>

                        <!-- Desconto Idoso -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" id="desconto_idoso" name="desconto_idoso" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Aplicar desconto para idosos</span>
                            </label>
                        </div>

                        <!-- Seção de Preços -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Preços do Lote</h3>
                            <div id="precos-container" class="space-y-4">
                                <!-- Preços serão adicionados aqui -->
                            </div>
                            <button type="button" onclick="adicionarPreco()" class="mt-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-plus mr-2"></i>
                                Adicionar Preço
                            </button>
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-end space-x-3 pt-6 border-t">
                            <button type="button" onclick="fecharModalLote()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                                <span id="btnSalvarTexto">Criar Lote</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>

    </script>
    <script src="../../js/lotes.js"></script>

</body>

</html>
