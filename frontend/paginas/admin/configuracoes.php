<?php
$activePage = 'configuracoes';
?>

<div class="w-full space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-1">Configurações do Sistema</h2>
            <p class="text-gray-600">Gerencie integrações e parâmetros globais</p>
        </div>
        <button id="btn-atualizar-configs" class="btn-secondary flex items-center gap-2">
            <i class="fas fa-sync"></i>
            <span class="hidden sm:inline">Atualizar</span>
        </button>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">OpenAI</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span id="status-openai" class="w-2 h-2 rounded-full bg-gray-300"></span>
                        <span id="status-openai-text" class="text-sm font-medium text-gray-600">Verificando...</span>
                    </div>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-brain text-purple-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Anthropic</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span id="status-anthropic" class="w-2 h-2 rounded-full bg-gray-300"></span>
                        <span id="status-anthropic-text" class="text-sm font-medium text-gray-600">Verificando...</span>
                    </div>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-robot text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Google Gemini</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span id="status-gemini" class="w-2 h-2 rounded-full bg-gray-300"></span>
                        <span id="status-gemini-text" class="text-sm font-medium text-gray-600">Verificando...</span>
                    </div>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-gem text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Validação Treino</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span id="status-treino" class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span id="status-treino-text" class="text-sm font-medium text-gray-600">Ativa</span>
                    </div>
                </div>
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-running text-orange-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Configs</p>
                    <p id="total-configs" class="text-xl font-bold text-gray-900 mt-1">-</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cog text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Modernos -->
    <div class="bg-white rounded-lg p-4 border border-gray-200">
        <div class="flex flex-wrap gap-3 items-center">
            <div class="flex-1 min-w-64">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="campo-busca" placeholder="Buscar configuração..." class="input-primary w-full pl-10">
                </div>
            </div>
            <div class="flex gap-2" id="category-chips">
                <button class="category-chip active" data-category="">Todas</button>
            </div>
        </div>
    </div>

    <!-- Config Cards Grid -->
    <div id="config-loading" class="admin-loading">
        <div class="admin-spinner"></div>
        <span>Carregando configurações...</span>
    </div>

    <div id="config-empty" class="hidden bg-white rounded-lg p-12 text-center border border-gray-200">
        <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
        <p class="text-gray-500">Nenhuma configuração encontrada</p>
    </div>

    <div id="config-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-4"></div>
</div>

<!-- Modal Edição -->
<div id="modal-config" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div id="modal-config-icon" class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cog text-purple-600"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-secondary-black" id="modal-config-titulo">Editar Configuração</h2>
                    <p class="text-sm text-gray-500" id="modal-config-chave-display"></p>
                </div>
            </div>
            <button data-close-modal="modal-config" class="text-secondary-dark-gray hover:text-secondary-black">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-5">
            <input type="hidden" id="modal-config-chave">
            
            <div>
                <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Descrição</label>
                <p id="modal-config-descricao" class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg"></p>
            </div>

            <div id="modal-config-input-wrapper"></div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start gap-2">
                <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <strong>Última atualização:</strong>
                    <span id="modal-config-updated" class="ml-1"></span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-end space-x-3 bg-gray-50">
            <button data-close-modal="modal-config" class="btn-secondary">Cancelar</button>
            <button id="btn-salvar-config" class="btn-primary">Salvar Alterações</button>
        </div>
    </div>
</div>

<!-- Modal Histórico -->
<div id="modal-historico" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-history text-orange-600"></i>
                </div>
                <h2 class="text-xl font-bold text-secondary-black">Histórico de Alterações</h2>
            </div>
            <button data-close-modal="modal-historico" class="text-secondary-dark-gray hover:text-secondary-black">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 overflow-y-auto flex-1">
            <div id="historico-lista" class="space-y-3"></div>
        </div>
    </div>
</div>

<script src="../../js/admin/configuracoes.js"></script>
