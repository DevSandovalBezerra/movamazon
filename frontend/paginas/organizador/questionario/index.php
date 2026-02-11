<?php
$pageTitle = 'Questionário';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - MovAmazon</title>
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
                <a href="?page=cupons-remessa" class="bg-brand-green hover:bg-green-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
                    ← Cupons de Desconto
                </a>
                <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
                    5- Questionário
                </span>
                <a href="?page=produtos" class="bg-brand-green hover:bg-green-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
                    Produtos →
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
                            <span class="font-semibold">Questionário</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Questionário do Evento</h1>
                        <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Gerencie o questionário dos seus eventos</p>
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
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select id="filtroTipo" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm" disabled>
                            <option value="">Todos os tipos</option>
                            <option value="texto_aberto">Texto aberto</option>
                            <option value="radio">Radio</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="dropdown">Dropdown</option>
                            <option value="upload">Upload</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="aplicarFiltros()" class="w-full bg-brand-green hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg flex items-center justify-center text-xs sm:text-sm">
                            <i class="fas fa-search mr-2"></i>
                            Aplicar Filtros
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estados de Loading e Erro -->
            <div id="loading" class="hidden">
                <div class="flex items-center justify-center py-6 sm:py-12">
                    <div class="animate-spin rounded-full h-6 w-6 sm:h-12 sm:w-12 border-b-2 border-brand-green"></div>
                    <span class="ml-2 sm:ml-3 text-gray-600 text-xs sm:text-sm">Carregando questionário...</span>
                </div>
            </div>

            <div id="error-questionario" class="hidden">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 sm:p-6 text-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl sm:text-2xl mb-2"></i>
                    <h3 class="text-red-800 font-semibold mb-1 text-sm sm:text-base">Erro ao carregar questionário</h3>
                    <p class="text-red-600 text-xs sm:text-sm" id="error-message">Ocorreu um erro inesperado.</p>
                    <button onclick="carregarQuestionario()" class="mt-3 sm:mt-4 bg-red-600 hover:bg-red-700 text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm">
                        Tentar Novamente
                    </button>
                </div>
            </div>

            <div id="sem-perguntas" class="hidden">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 sm:p-12 text-center">
                    <i class="fas fa-question-circle text-gray-400 text-3xl sm:text-4xl mb-3 sm:mb-4"></i>
                    <h3 class="text-gray-600 font-semibold mb-2">Nenhuma pergunta encontrada</h3>
                    <p class="text-gray-500 mb-3 sm:mb-4 text-sm">Selecione um evento para ver as perguntas ou criar novas.</p>
                    <button onclick="abrirModal()" class="bg-brand-green hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Criar Primeira Pergunta
                    </button>
                </div>
            </div>

            <div id="selecionar-filtros" class="bg-green-50 border border-green-200 rounded-lg p-6 sm:p-12 text-center">
                <i class="fas fa-filter text-brand-green text-3xl sm:text-4xl mb-3 sm:mb-4"></i>
                <h3 class="text-brand-green font-semibold mb-1 sm:mb-2 text-sm sm:text-base">Selecione um Evento</h3>
                <p class="text-green-600 mb-3 sm:mb-4 text-xs sm:text-sm">Escolha um evento para gerenciar o questionário.</p>
            </div>

            <!-- Container do Questionário -->
            <div id="questionario-container" class="hidden">
                <div class="flex items-center justify-between mb-4 sm:mb-6">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Perguntas do Questionário</h2>
                    <button onclick="abrirModal()" class="bg-brand-green hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg flex items-center text-xs sm:text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Nova Pergunta
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ordem
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pergunta/Campo
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipo
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Obrigatório
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Modalidades
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-right text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="questionario-tbody" class="bg-white divide-y divide-gray-200">
                                <!-- Perguntas/campos serão renderizadas aqui -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Criar/Editar Pergunta -->
    <div id="modalPergunta" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900" id="modalTitle">Nova Pergunta</h2>
                        <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="formPergunta" class="space-y-6">
                        <input type="hidden" id="perguntaId">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Texto da Pergunta/Campo *</label>
                            <input type="text" id="textoPergunta" maxlength="300" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                        </div>

                        <!-- Classificação da pergunta -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Classificação da Pergunta *</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-blue-400 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                    <input type="radio" name="classificacao" id="classificacaoEvento" value="evento" checked class="w-4 h-4 text-blue-600">
                                    <div>
                                        <i class="fas fa-calendar-alt text-blue-600 mr-1"></i>
                                        <span class="font-medium text-gray-800">Sobre o Evento</span>
                                        <p class="text-xs text-gray-500">Perguntas sobre o evento, participação anterior, etc.</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-green-400 transition-colors has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                    <input type="radio" name="classificacao" id="classificacaoAtleta" value="atleta" class="w-4 h-4 text-green-600">
                                    <div>
                                        <i class="fas fa-user-alt text-green-600 mr-1"></i>
                                        <span class="font-medium text-gray-800">Dados do Atleta</span>
                                        <p class="text-xs text-gray-500">Contato de emergência, nome no peito, etc.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Resposta *</label>
                                <select id="tipoResposta" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                    <option value="">Selecione</option>
                                    <option value="texto_aberto">Texto aberto</option>
                                    <option value="radio">Opções (Radio)</option>
                                    <option value="checkbox">Opções (Checkbox)</option>
                                    <option value="dropdown">Lista de opções (Dropdown)</option>
                                    <option value="upload">Upload de arquivo</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Obrigatório?</label>
                                <select id="obrigatorio" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                    <option value="0">Não</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status Site</label>
                                <select id="statusSite" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                    <option value="publicada">Publicada</option>
                                    <option value="rascunho">Rascunho</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Modalidades *</label>
                            <div id="modalidades-container" class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-3">
                                <!-- Modalidades serão carregadas via JS -->
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                            <input type="text" id="observacoesPergunta" maxlength="200" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                    </form>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3">
                    <button onclick="fecharModal()" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button onclick="salvarPergunta()" class="px-4 py-2 bg-brand-green text-white rounded-lg hover:bg-green-700 flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/utils/modalidades-selector.js"></script>
    <script src="../../js/questionario.js"></script>
</body>

</html>

 
