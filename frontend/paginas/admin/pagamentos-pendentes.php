<?php
$activePage = 'pagamentos-pendentes';
?>

<div class="w-full space-y-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Pagamentos Pendentes</h2>
            <p class="text-gray-600">Gerenciar pagamentos pendentes e sincronizar com Mercado Pago</p>
        </div>
        <button id="btn-sincronizar-todos" class="btn-primary flex items-center gap-2">
            <i class="fas fa-sync-alt w-4 h-4"></i>
            Sincronizar Selecionados
        </button>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="admin-card bg-yellow-50 border-l-4 border-yellow-500 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-yellow-600 mb-2">Total Pendentes</p>
                    <p class="text-3xl font-bold text-yellow-700 leading-none" id="stat-total-pendentes">0</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-4 ml-4 flex-shrink-0">
                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="admin-card bg-orange-50 border-l-4 border-orange-500 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-orange-600 mb-2">Pendentes > 24h</p>
                    <p class="text-3xl font-bold text-orange-700 leading-none" id="stat-pendentes-24h">0</p>
                </div>
                <div class="bg-orange-100 rounded-full p-4 ml-4 flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="admin-card bg-red-50 border-l-4 border-red-500 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-600 mb-2">Pendentes > 72h</p>
                    <p class="text-3xl font-bold text-red-700 leading-none" id="stat-pendentes-72h">0</p>
                </div>
                <div class="bg-red-100 rounded-full p-4 ml-4 flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="admin-card bg-blue-50 border-l-4 border-blue-500 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-600 mb-2">Valor Total</p>
                    <p class="text-3xl font-bold text-blue-700 leading-none" id="stat-valor-total">R$ 0</p>
                </div>
                <div class="bg-blue-100 rounded-full p-4 ml-4 flex-shrink-0">
                    <i class="fas fa-dollar-sign text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="min-w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">Horas Mínimas</label>
                <select id="filtro-horas" class="admin-input w-full">
                    <option value="2">Mais de 2 horas</option>
                    <option value="24" selected>Mais de 24 horas</option>
                    <option value="48">Mais de 48 horas</option>
                    <option value="72">Mais de 72 horas</option>
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

    <!-- Lista de Pagamentos -->
    <div class="admin-card overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <input type="checkbox" id="select-all" class="rounded">
                <span class="text-sm text-gray-600">Selecionar todos</span>
            </div>
            <div id="selected-count" class="text-sm text-gray-600">0 selecionados</div>
        </div>
        
        <div id="pagamentos-loading" class="admin-loading">
            <div class="admin-spinner"></div>
            <span>Carregando pagamentos pendentes...</span>
        </div>
        
        <div id="pagamentos-empty" class="hidden py-10 text-center text-gray-500">
            Nenhum pagamento pendente encontrado com os filtros aplicados.
        </div>
        
        <div id="pagamentos-container" class="divide-y divide-gray-200">
            <!-- Pagamentos serão inseridos aqui via JavaScript -->
        </div>
        
        <div id="pagamentos-pagination" class="mt-4 flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50"></div>
    </div>
</div>

<script src="../../js/admin/pagamentos-pendentes.js"></script>
