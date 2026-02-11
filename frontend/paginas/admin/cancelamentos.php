<?php
$activePage = 'cancelamentos';
?>

<div class="w-full space-y-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Solicitações de Cancelamento</h2>
            <p class="text-gray-600">Gerenciar solicitações de cancelamento de inscrições</p>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="admin-card bg-yellow-50 border-l-4 border-yellow-500 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-yellow-600 mb-2">Pendentes</p>
                    <p class="text-3xl font-bold text-yellow-700 leading-none" id="stat-pendentes">0</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-4 ml-4 flex-shrink-0">
                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="admin-card bg-green-50 border-l-4 border-green-500 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-600 mb-2">Aprovadas</p>
                    <p class="text-3xl font-bold text-green-700 leading-none" id="stat-aprovadas">0</p>
                </div>
                <div class="bg-green-100 rounded-full p-4 ml-4 flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="admin-card bg-red-50 border-l-4 border-red-500 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-600 mb-2">Rejeitadas</p>
                    <p class="text-3xl font-bold text-red-700 leading-none" id="stat-rejeitadas">0</p>
                </div>
                <div class="bg-red-100 rounded-full p-4 ml-4 flex-shrink-0">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="admin-card bg-blue-50 border-l-4 border-blue-500 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-600 mb-2">Total</p>
                    <p class="text-3xl font-bold text-blue-700 leading-none" id="stat-total">0</p>
                </div>
                <div class="bg-blue-100 rounded-full p-4 ml-4 flex-shrink-0">
                    <i class="fas fa-list text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="min-w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="filtro-status" class="admin-input w-full">
                    <option value="pendente" selected>Pendentes</option>
                    <option value="todos">Todos</option>
                    <option value="aprovada">Aprovadas</option>
                    <option value="rejeitada">Rejeitadas</option>
                    <option value="processada">Processadas</option>
                </select>
            </div>
            <div class="min-w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">Evento</label>
                <select id="filtro-evento" class="admin-input w-full">
                    <option value="">Todos os eventos</option>
                </select>
            </div>
            <div class="flex items-end">
                <button id="btn-aplicar-filtros" class="btn-primary">
                    <i class="fas fa-filter w-4 h-4"></i>
                    Aplicar Filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Lista de Cancelamentos -->
    <div class="admin-card overflow-hidden">
        <div id="cancelamentos-loading" class="admin-loading">
            <div class="admin-spinner"></div>
            <span>Carregando solicitações...</span>
        </div>
        
        <div id="cancelamentos-empty" class="hidden py-10 text-center text-gray-500">
            Nenhuma solicitação encontrada com os filtros aplicados.
        </div>
        
        <div id="cancelamentos-container" class="divide-y divide-gray-200">
            <!-- Cancelamentos serão inseridos aqui via JavaScript -->
        </div>
        
        <div id="cancelamentos-pagination" class="mt-4 flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50"></div>
    </div>
</div>

<!-- Modal de Processar Cancelamento -->
<div id="modal-processar-cancelamento" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Processar Cancelamento</h2>
            <button data-close-modal="modal-processar-cancelamento" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="modal-processar-content" class="p-6 space-y-4">
            <!-- Conteúdo será inserido via JavaScript -->
        </div>
    </div>
</div>

<script src="../../js/admin/cancelamentos.js"></script>
