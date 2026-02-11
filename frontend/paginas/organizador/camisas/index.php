<?php
// 1. Verificação de sessão ANTES de qualquer output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Verificar autenticação ANTES de qualquer output
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    header('Location: ../../auth/login.php');
    exit();
}

$pageTitle = 'Camisas do Evento';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - MovAmazon</title>
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/custom.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50">

    <div class="p-4 sm:p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Navegação Sequencial -->
            <div class="mb-4 sm:mb-6 flex gap-2 sm:gap-4">
                <a href="?page=retirada-kits" class="bg-brand-green hover:bg-green-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
                    ← Retirada de Kits
                </a>
                <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
                    10- Camisas
                </span>
                <a href="?page=produtos-extras" class="bg-brand-green hover:bg-green-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
                    Produtos Extras →
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
                            <span class="font-semibold">Camisas</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Camisas do Evento</h1>
                        <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Gerencie os tamanhos e quantidades de camisas dos seus eventos</p>
                    </div>
                    <button onclick="abrirModalCriar()" class="bg-brand-green hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg flex items-center text-xs sm:text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Adicionar Tamanho
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3 lg:gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
                        <select id="filtroEvento" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
                            <option value="">Selecione um evento</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="filtroStatus" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
                            <option value="">Todos os status</option>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="aplicarFiltros()" class="w-full bg-brand-green hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg flex items-center justify-center text-xs sm:text-sm">
                            <i class="fas fa-search mr-2"></i>
                            Aplicar Filtros
                        </button>
                    </div>
                </div>

                <!-- Contador de Camisas vs Vagas -->
                <div id="contador-camisas" class="hidden bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-brand-green" id="total-camisas">0</div>
                                <div class="text-sm text-brand-green">Camisas</div>
                            </div>
                            <div class="text-brand-green">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-700" id="limite-vagas">0</div>
                                <div class="text-sm text-gray-600">Vagas do Evento</div>
                            </div>
                            <div class="text-brand-green">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold" id="disponivel">0</div>
                                <div class="text-sm" id="disponivel-label">Disponível</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold" id="percentual">0%</div>
                            <div class="text-sm text-gray-600">Utilizado</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="barra-progresso" class="bg-brand-green h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado Inicial -->
            <div id="estado-inicial" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-tshirt text-4xl sm:text-6xl"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2">Selecione um Evento</h3>
                <p class="text-gray-500 text-sm sm:text-base">Escolha um evento para visualizar e gerenciar os tamanhos de camisas disponíveis.</p>
            </div>

            <!-- Estado Filtrado -->
            <div id="estado-filtrado" class="hidden">
                <!-- Tabela de Camisas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Tamanhos de Camisas</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Tamanho</th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade Inicial</th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Vendidas</th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Disponíveis</th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Reservadas</th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-camisas" class="bg-white divide-y divide-gray-200">
                                <!-- Dados serão carregados dinamicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Estado Vazio -->
            <div id="estado-vazio" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-tshirt text-4xl sm:text-6xl"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2">Nenhum Tamanho Encontrado</h3>
                <p class="text-gray-500 mb-6 text-sm sm:text-base">Não foram encontrados tamanhos de camisas para os filtros selecionados.</p>
                <button onclick="abrirModalCriar()" class="bg-brand-green hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg flex items-center mx-auto text-xs sm:text-sm">
                    <i class="fas fa-plus mr-2"></i>
                    Adicionar Primeiro Tamanho
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para Criar/Editar Camisa -->
    <div id="modal-camisa" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="bg-brand-green text-white p-4 rounded-t-lg flex justify-between items-center">
                <h3 id="modal-titulo" class="font-bold text-lg">ADICIONAR TAMANHO</h3>
                <button onclick="fecharModal()" class="text-white hover:text-gray-200 text-xl">&times;</button>
            </div>
            <div class="p-6">
                <form id="form-camisa">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tamanho:</label>
                        <select id="camisa-tamanho" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                            <option value="">Selecione o tamanho</option>
                            <option value="PP">PP</option>
                            <option value="P">P</option>
                            <option value="M">M</option>
                            <option value="G">G</option>
                            <option value="GG">GG</option>
                            <option value="XG">XG</option>
                            <option value="XXG">XXG</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade Inicial:</label>
                        <input type="number" id="camisa-quantidade" class="w-full border border-gray-300 rounded-lg px-3 py-2" min="0" required>
                        <div id="info-limite" class="text-sm text-gray-500 mt-1 hidden">
                            <span id="disponivel-texto"></span> de <span id="limite-texto"></span> vagas disponíveis
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status:</label>
                        <select id="camisa-status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>
                    <div class="flex justify-between">
                        <button type="button" onclick="fecharModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition">Cancelar</button>
                        <button type="submit" class="bg-brand-green hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">Salvar</button>
                    </div>
                    <input type="hidden" id="camisa-id">
                    <input type="hidden" id="camisa-evento-id">
                </form>
            </div>
        </div>
    </div>

    <script src="../../js/camisas.js"></script>
</body>

</html>
