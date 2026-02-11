<?php
$activePage = 'problemas-inscricoes';
?>

<div class="w-full space-y-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Problemas com Inscrições</h2>
            <p class="text-gray-600">Visualização otimizada de logs e problemas relacionados a inscrições e pagamentos</p>
        </div>
        <button id="btn-limpar-logs" class="btn-primary flex items-center gap-2 bg-red-600 hover:bg-red-700">
            <i class="fas fa-trash-alt w-4 h-4"></i>
            Limpar Logs
        </button>
    </div>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card ERROR -->
        <div class="admin-card bg-red-50 border-l-4 border-red-500 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-600 mb-2">Erros</p>
                    <p class="text-3xl font-bold text-red-700 leading-none" id="stat-errors">0</p>
                </div>
                <div class="bg-red-100 rounded-full p-4 ml-4 flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Card WARNING -->
        <div class="admin-card bg-yellow-50 border-l-4 border-yellow-500 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-yellow-600 mb-2">Avisos</p>
                    <p class="text-3xl font-bold text-yellow-700 leading-none" id="stat-warnings">0</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-4 ml-4 flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Card INFO -->
        <div class="admin-card bg-blue-50 border-l-4 border-blue-500 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-600 mb-2">Informações</p>
                    <p class="text-3xl font-bold text-blue-700 leading-none" id="stat-info">0</p>
                </div>
                <div class="bg-blue-100 rounded-full p-4 ml-4 flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Card SUCCESS -->
        <div class="admin-card bg-green-50 border-l-4 border-green-500 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-600 mb-2">Sucessos</p>
                    <p class="text-3xl font-bold text-green-700 leading-none" id="stat-success">0</p>
                </div>
                <div class="bg-green-100 rounded-full p-4 ml-4 flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <div class="admin-input-wrapper">
                    <i class="fas fa-search admin-input-icon"></i>
                    <input type="text" id="filtro-busca" placeholder="Buscar em mensagens ou ações..." class="admin-input admin-input--with-icon">
                </div>
            </div>
            <div class="min-w-32">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nível</label>
                <select id="filtro-nivel" class="admin-input w-full">
                    <option value="">Todos</option>
                    <option value="ERROR">Erro</option>
                    <option value="WARNING">Aviso</option>
                    <option value="INFO">Info</option>
                    <option value="SUCCESS">Sucesso</option>
                </select>
            </div>
            <div class="min-w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">Ação</label>
                <select id="filtro-acao" class="admin-input w-full">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="min-w-32">
                <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                <select id="filtro-periodo" class="admin-input w-full">
                    <option value="">Todos</option>
                    <option value="hoje">Hoje</option>
                    <option value="7dias">Últimos 7 dias</option>
                    <option value="30dias">Últimos 30 dias</option>
                    <option value="custom">Personalizado</option>
                </select>
            </div>
            <div class="min-w-32 hidden" id="filtro-data-inicio-wrapper">
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Início</label>
                <input type="date" id="filtro-data-inicio" class="admin-input w-full">
            </div>
            <div class="min-w-32 hidden" id="filtro-data-fim-wrapper">
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Fim</label>
                <input type="date" id="filtro-data-fim" class="admin-input w-full">
            </div>
            <div class="min-w-32">
                <label class="block text-sm font-medium text-gray-700 mb-2">Inscrição ID</label>
                <input type="number" id="filtro-inscricao-id" placeholder="ID da inscrição" class="admin-input w-full">
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <button id="btn-aplicar-filtros" class="btn-primary">
                <i class="fas fa-filter w-4 h-4"></i>
                Aplicar Filtros
            </button>
            <button id="btn-limpar-filtros" class="btn-secondary">
                <i class="fas fa-times w-4 h-4"></i>
                Limpar
            </button>
        </div>
    </div>

    <!-- Lista de Logs -->
    <div class="admin-card overflow-hidden">
        <div id="logs-loading" class="admin-loading">
            <div class="admin-spinner"></div>
            <span>Carregando logs...</span>
        </div>
        <div id="logs-empty" class="hidden py-10 text-center text-gray-500">
            Nenhum log encontrado com os filtros aplicados.
        </div>
        <div id="logs-container" class="space-y-4 p-6">
            <!-- Logs serão inseridos aqui via JavaScript -->
        </div>
        <div id="logs-pagination" class="mt-4 flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50"></div>
    </div>
</div>

<!-- Modal de Detalhes -->
<div id="modal-detalhes-log" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Detalhes do Log</h2>
            <button data-close-modal="modal-detalhes-log" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="modal-detalhes-content" class="p-6 space-y-4">
            <!-- Conteúdo será inserido via JavaScript -->
        </div>
    </div>
</div>

<!-- Modal de Limpeza de Logs -->
<div id="modal-limpar-logs" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">
                <i class="fas fa-trash-alt text-red-600"></i>
                Limpar Logs
            </h2>
            <button data-close-modal="modal-limpar-logs" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Limpeza</label>
                <select id="limpeza-tipo" class="admin-input w-full">
                    <option value="">Selecione uma opção...</option>
                    <option value="todos">Deletar todos os logs</option>
                    <option value="periodo">Deletar logs mais antigos que X dias</option>
                    <option value="nivel">Deletar por nível/categoria</option>
                    <option value="acao">Deletar por ação específica</option>
                    <option value="inscricao">Deletar logs de inscrição específica</option>
                    <option value="manter_ultimos">Manter apenas últimos X dias</option>
                    <option value="periodo_especifico">Deletar logs em período específico</option>
                </select>
            </div>

            <!-- Opções dinâmicas baseadas no tipo -->
            <div id="limpeza-opcoes" class="space-y-4 hidden">
                <!-- Período em dias -->
                <div id="opcao-periodo" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dias</label>
                    <input type="number" id="limpeza-periodo-dias" min="1" class="admin-input w-full" placeholder="Ex: 30">
                </div>

                <!-- Nível -->
                <div id="opcao-nivel" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nível</label>
                    <select id="limpeza-nivel" class="admin-input w-full">
                        <option value="ERROR">ERROR</option>
                        <option value="WARNING">WARNING</option>
                        <option value="INFO">INFO</option>
                        <option value="SUCCESS">SUCCESS</option>
                    </select>
                </div>

                <!-- Ação -->
                <div id="opcao-acao" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ação</label>
                    <input type="text" id="limpeza-acao" class="admin-input w-full" placeholder="Ex: VALIDACAO_CUPOM">
                </div>

                <!-- Inscrição ID -->
                <div id="opcao-inscricao" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">ID da Inscrição</label>
                    <input type="number" id="limpeza-inscricao-id" min="1" class="admin-input w-full" placeholder="Ex: 123">
                </div>

                <!-- Manter últimos dias -->
                <div id="opcao-manter-ultimos" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Manter últimos (dias)</label>
                    <input type="number" id="limpeza-manter-ultimos" min="1" class="admin-input w-full" placeholder="Ex: 90">
                </div>

                <!-- Período específico -->
                <div id="opcao-periodo-especifico" class="hidden space-y-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Início</label>
                        <input type="date" id="limpeza-data-inicio" class="admin-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Fim</label>
                        <input type="date" id="limpeza-data-fim" class="admin-input w-full">
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div id="limpeza-preview" class="hidden p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h3 class="font-semibold text-yellow-800 mb-2">Preview de Limpeza</h3>
                <div id="limpeza-preview-content" class="text-sm text-yellow-700">
                    <!-- Conteúdo do preview -->
                </div>
            </div>

            <!-- Confirmação para deletar todos -->
            <div id="limpeza-confirmar-todos" class="hidden p-4 bg-red-50 border border-red-200 rounded-lg">
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="limpeza-confirmar-checkbox" class="rounded">
                    <span class="text-sm font-medium text-red-800">Confirmo que desejo deletar TODOS os logs. Esta ação não pode ser desfeita.</span>
                </label>
            </div>

            <div class="flex gap-2 justify-end pt-4 border-t border-gray-200">
                <button id="btn-limpeza-preview" class="btn-secondary">
                    <i class="fas fa-eye w-4 h-4"></i>
                    Preview
                </button>
                <button id="btn-limpeza-executar" class="btn-primary bg-red-600 hover:bg-red-700" disabled>
                    <i class="fas fa-trash-alt w-4 h-4"></i>
                    Confirmar e Deletar
                </button>
                <button data-close-modal="modal-limpar-logs" class="btn-secondary">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="../../js/admin/problemas-inscricoes.js"></script>

