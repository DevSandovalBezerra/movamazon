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

$pageTitle = 'Modalidades do Evento';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> MovAmazon</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/custom.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Estilos para os toggles customizados */
        .dot {
            transition: all 0.3s ease-in-out;
        }

        input:checked+label .dot {
            transform: translateX(16px);
        }

        input:checked+label>div {
            background-color: #10B981;
        }

        /* Animações para a janela cortina */
        #categoriasPanel {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #categoriasPanel.abrir {
            transform: translateX(0);
        }

        /* Estilos para os cards de categoria */
        .categoria-card {
            transition: all 0.2s ease-in-out;
        }

        .categoria-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Animações de entrada */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .slide-in-right {
            animation: slideInRight 0.3s ease-out;
        }
    </style>
</head>

<body class="bg-gray-50">

    <div class="p-4 sm:p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Navegação Simples -->
            <div class="mb-4 sm:mb-6 flex gap-2 sm:gap-4">
                <a href="?page=eventos" class="bg-brand-green hover:bg-green-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
                    ← Meus Eventos
                </a>
                <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
                    2- Modalidades
                </span>
                <a href="?page=lotes-inscricao" class="bg-brand-green hover:bg-green-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
                    Lotes de Inscrição →
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
                            <span class="font-semibold">Modalidades</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Modalidades do Evento</h1>
                        <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Gerencie as modalidades dos seus eventos</p>
                    </div>
                    <div class=" space-x-3">
                        <button id="btnGerenciarCategorias" onclick="abrirPanelCategorias()" disabled class="bg-brand-green text-white px-3 sm:px-4 py-2 rounded-lg flex items-center transition-colors text-xs sm:text-sm opacity-50 cursor-not-allowed">
                            <i class="fas fa-tags mr-2"></i>
                            Gerenciar e criar Categorias do evento
                        </button>
                        <details>Crie as categorias antes das modalidades</details>
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

                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                        <select id="filtroCategoria" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm" disabled>
                            <option value="">Todas as categorias</option>
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
                    <span class="ml-2 sm:ml-3 text-gray-600 text-xs sm:text-sm">Carregando modalidades...</span>
                </div>
            </div>

            <div id="error-modalidades" class="hidden">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 sm:p-6 text-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl sm:text-2xl mb-2"></i>
                    <h3 class="text-red-800 font-semibold mb-1 text-sm sm:text-base">Erro ao carregar modalidades</h3>
                    <p class="text-red-600 text-xs sm:text-sm" id="error-message">Ocorreu um erro inesperado.</p>
                    <button onclick="carregarModalidades()" class="mt-3 sm:mt-4 bg-red-600 hover:bg-red-700 text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm">
                        Tentar Novamente
                    </button>
                </div>
            </div>

            <div id="sem-modalidades" class="hidden">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 sm:p-12 text-center">
                    <i class="fas fa-running text-gray-400 text-3xl sm:text-4xl mb-3 sm:mb-4"></i>
                    <h3 class="text-gray-600 font-semibold mb-2">Nenhuma modalidade encontrada</h3>
                    <p class="text-gray-500 mb-3 sm:mb-4 text-sm">Selecione um evento para ver as modalidades ou criar novas.</p>
                    <button onclick="abrirModalCriar()" class="bg-brand-green hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Criar Primeira Modalidade
                    </button>
                </div>
            </div>

            <div id="selecionar-filtros" class="bg-green-50 border border-green-200 rounded-lg p-12 text-center">
                <i class="fas fa-filter text-brand-green text-4xl mb-4"></i>
                <h3 class="text-brand-green font-semibold mb-2">Selecione um Evento</h3>
                <p class="text-green-600 mb-4">Escolha um evento para gerenciar as modalidades.</p>
            </div>

            <!-- Container das Modalidades -->
            <div id="modalidades-container" class="hidden">
                <div class="flex items-center justify-between mb-4 sm:mb-6">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Modalidades do Evento</h2>
                    <button onclick="abrirModalCriar()" class="bg-brand-green hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg flex items-center text-xs sm:text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Nova Modalidade
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Modalidade
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Categoria
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Distância
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipo
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-right text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="modalidades-tbody" class="bg-white divide-y divide-gray-200">
                                <!-- Modalidades serão renderizadas aqui -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Criar/Editar Modalidade -->
    <div id="modalModalidade" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900" id="modalTitulo">Criar Nova Modalidade</h2>
                        <button onclick="fecharModalModalidade()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="formModalidade" class="space-y-6">
                        <input type="hidden" id="modalidadeId">
                        <input type="hidden" id="eventoId">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Modalidade *</label>
                            <input type="text" id="nomeModalidade"
                                placeholder="Ex: CORRIDA 10KM, CORRIDA 5KM, CORRIDA 21KM"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categoria *</label>
                            <select id="categoriaId" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                <option value="">Selecione uma categoria...</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                            <textarea id="descricao" rows="3" placeholder="Descrição da modalidade..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Distância</label>
                                <input type="text" id="distancia" placeholder="Ex: 10KM, 5KM, 21KM"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Prova *</label>
                                <select id="tipoProva" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                    <option value="corrida">Corrida</option>
                                    <option value="caminhada">Caminhada</option>
                                    <option value="ambos">Ambos</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Limite de Vagas</label>
                            <input type="number" id="limiteVagas" placeholder="Número máximo de participantes"
                                min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                    </form>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3">
                    <button onclick="fecharModalModalidade()" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button onclick="salvarModalidade()" class="px-4 py-2 bg-brand-green text-white rounded-lg hover:bg-green-700 flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Janela Cortina - Gerenciar Categorias -->
    <div id="categoriasPanel" class="fixed inset-y-0 right-0 w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-50">
        <!-- Header da Janela -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold">Criar e Gerenciar Categorias</h2>
                <button onclick="fecharPanelCategorias()" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <p class="text-blue-100 text-sm">Organize as categorias dos seus eventos</p>
        </div>

        <!-- Conteúdo da Janela -->
        <div class="h-full overflow-y-auto">
            <!-- Formulário Criar/Editar Categoria -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900" id="formCategoriaTitulo">Nova Categoria</h3>
                    <button id="btnLimparForm" onclick="limparFormCategoria()" class="text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-eraser mr-1"></i>Limpar
                    </button>
                </div>

                <form id="formCategoria" class="space-y-4">
                    <input type="hidden" id="categoriaIdEdit">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nome da Categoria <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="nomeCategoria"
                                placeholder="Ex: KIT BÁSICO, KIT COMPLETO, KIT PREMIUM"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-20 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                maxlength="350" required>
                            <div class="absolute right-2 top-2 text-xs text-gray-400">
                                <span id="contadorNome">350</span> chars
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700">Status</label>
                        <label for="statusCategoria" class="relative inline-flex items-center cursor-pointer select-none">
                            <input id="statusCategoria" type="checkbox" class="sr-only peer" role="switch" aria-checked="true" checked>
                            <div class="w-14 h-8 rounded-full ring-1 ring-inset ring-gray-300 bg-gray-200 transition-colors peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500 peer-checked:bg-emerald-500"></div>
                            <svg class="pointer-events-none absolute left-1 top-1.5 h-5 w-5 text-gray-400 transition-opacity duration-200 peer-checked:opacity-0" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M3 7h8M3 7l3-3M3 7l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg class="pointer-events-none absolute right-1 top-1.5 h-5 w-5 text-white opacity-0 transition-opacity duration-200 peer-checked:opacity-100" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293A1 1 0 003.293 10.707l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd" />
                            </svg>
                            <span class="absolute top-0.5 left-0.5 w-7 h-7 bg-white rounded-full shadow-sm transform transition-transform duration-300 peer-checked:translate-x-6"></span>
                            <span id="statusLabel" class="ml-3 inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 transition-colors peer-checked:bg-emerald-100 peer-checked:text-emerald-700">Ativo</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Público</label>
                        <input type="text" id="tipoPublico"
                            placeholder="Ex: público geral, comunidade acadêmica"
                            maxlength="50"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Idade Mínima</label>
                            <input type="number" id="idadeMin"
                                placeholder="0" min="0" max="100"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Idade Máxima</label>
                            <input type="number" id="idadeMax"
                                placeholder="100" min="0" max="150"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700">Desconto para Idosos</label>
                        <label for="descontoIdoso" class="relative inline-flex items-center cursor-pointer select-none">
                            <input id="descontoIdoso" type="checkbox" class="sr-only peer">
                            <div class="w-14 h-8 rounded-full ring-1 ring-inset ring-gray-300 bg-gray-200 transition-colors peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500 peer-checked:bg-emerald-500"></div>
                            <svg class="pointer-events-none absolute left-1 top-1.5 h-5 w-5 text-gray-400 transition-opacity duration-200 peer-checked:opacity-0" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M3 7h8M3 7l3-3M3 7l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg class="pointer-events-none absolute right-1 top-1.5 h-5 w-5 text-white opacity-0 transition-opacity duration-200 peer-checked:opacity-100" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293A1 1 0 003.293 10.707l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd" />
                            </svg>
                            <span class="absolute top-0.5 left-0.5 w-7 h-7 bg-white rounded-full shadow-sm transform transition-transform duration-300 peer-checked:translate-x-6"></span>
                            <span class="ml-3 text-sm font-medium text-gray-700">Aplicar desconto</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Descrição da Categoria
                        </label>
                        <div class="relative">
                            <textarea id="descricaoCategoria" rows="4"
                                placeholder="Descreva os benefícios e características desta categoria..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-20 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                maxlength="450"></textarea>
                            <div class="absolute right-2 bottom-2 text-xs text-gray-400">
                                <span id="contadorDescricao">450</span> chars
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700">Exibir no módulo de inscrição geral?</label>
                        <label for="exibirInscricaoGeral" class="relative inline-flex items-center cursor-pointer select-none">
                            <input id="exibirInscricaoGeral" type="checkbox" class="sr-only peer" checked>
                            <div class="w-14 h-8 rounded-full ring-1 ring-inset ring-gray-300 bg-gray-200 transition-colors peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500 peer-checked:bg-emerald-500"></div>
                            <svg class="pointer-events-none absolute left-1 top-1.5 h-5 w-5 text-gray-400 transition-opacity duration-200 peer-checked:opacity-0" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M3 7h8M3 7l3-3M3 7l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg class="pointer-events-none absolute right-1 top-1.5 h-5 w-5 text-white opacity-0 transition-opacity duration-200 peer-checked:opacity-100" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293A1 1 0 003.293 10.707l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd" />
                            </svg>
                            <span class="absolute top-0.5 left-0.5 w-7 h-7 bg-white rounded-full shadow-sm transform transition-transform duration-300 peer-checked:translate-x-6"></span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700">Exibir no módulo de inscrição de grupos?</label>
                        <label for="exibirInscricaoGrupos" class="relative inline-flex items-center cursor-pointer select-none">
                            <input id="exibirInscricaoGrupos" type="checkbox" class="sr-only peer" checked>
                            <div class="w-14 h-8 rounded-full ring-1 ring-inset ring-gray-300 bg-gray-200 transition-colors peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500 peer-checked:bg-emerald-500"></div>
                            <svg class="pointer-events-none absolute left-1 top-1.5 h-5 w-5 text-gray-400 transition-opacity duration-200 peer-checked:opacity-0" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M3 7h8M3 7l3-3M3 7l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg class="pointer-events-none absolute right-1 top-1.5 h-5 w-5 text-white opacity-0 transition-opacity duration-200 peer-checked:opacity-100" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293A1 1 0 003.293 10.707l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd" />
                            </svg>
                            <span class="absolute top-0.5 left-0.5 w-7 h-7 bg-white rounded-full shadow-sm transform transition-transform duration-300 peer-checked:translate-x-6"></span>
                        </label>
                    </div>



                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ocultar categoria?</label>
                        <input type="text" id="tituloLinkOculto"
                            placeholder="Se sim insira o titulo do link oculto:"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="button" onclick="salvarCategoria()"
                            class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-3 px-4 rounded-lg font-medium transition-all duration-200 transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>
                            Salvar Categoria
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lista de Categorias -->
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Categorias Existentes</h3>
                    <div class="text-sm text-gray-500" id="totalCategorias">0 categorias</div>
                </div>

                <div id="listaCategorias" class="space-y-3">
                    <!-- Categorias serão renderizadas aqui -->
                </div>

                <div id="semCategorias" class="text-center py-8 text-gray-500">
                    <i class="fas fa-tags text-4xl mb-3"></i>
                    <p>Nenhuma categoria criada ainda</p>
                    <p class="text-sm">Crie sua primeira categoria acima</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay para a janela cortina -->
    <div id="categoriasOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

    <script src="../../js/modalidades.js"></script>
    <script src="../../js/categorias.js"></script>
</body>

</html>
