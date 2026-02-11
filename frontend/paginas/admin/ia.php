<?php
$activePage = 'ia';
?>

<div class="w-full space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-1">Inteligência Artificial</h2>
            <p class="text-gray-600">Configure provedores de IA para geração automática de treinos</p>
        </div>
        <button id="btn-test-all" class="btn-secondary flex items-center gap-2">
            <i class="fas fa-plug"></i>
            <span class="hidden sm:inline">Testar Todos</span>
        </button>
    </div>

    <!-- AI Providers Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- OpenAI Card -->
        <div class="bg-white rounded-xl border-2 border-purple-200 hover:border-purple-400 transition-all overflow-hidden">
            <div class="bg-gradient-to-br from-purple-500 to-purple-700 p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-brain text-3xl"></i>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="status-dot-openai" class="w-3 h-3 rounded-full bg-gray-300"></span>
                        <span id="status-text-openai" class="text-xs font-medium opacity-90">Desconhecido</span>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-1">OpenAI</h3>
                <p class="text-sm opacity-90">GPT-4, GPT-3.5 Turbo</p>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">API Key:</span>
                    <span id="openai-key-status" class="text-gray-400">Não configurado</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Modelo:</span>
                    <span id="openai-model-display" class="font-medium text-gray-900">-</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Temperature:</span>
                    <span id="openai-temp-display" class="font-medium text-gray-900">-</span>
                </div>
                
                <div class="pt-4 border-t flex gap-2">
                    <button class="btn-config-provider flex-1 text-sm py-2" data-provider="openai">
                        <i class="fas fa-cog mr-1"></i> Configurar
                    </button>
                    <button class="btn-test-provider p-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors" data-provider="openai" title="Testar">
                        <i class="fas fa-plug"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Anthropic Card -->
        <div class="bg-white rounded-xl border-2 border-orange-200 hover:border-orange-400 transition-all overflow-hidden">
            <div class="bg-gradient-to-br from-orange-500 to-orange-700 p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-robot text-3xl"></i>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="status-dot-anthropic" class="w-3 h-3 rounded-full bg-gray-300"></span>
                        <span id="status-text-anthropic" class="text-xs font-medium opacity-90">Desconhecido</span>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-1">Anthropic</h3>
                <p class="text-sm opacity-90">Claude 3.5 Sonnet</p>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">API Key:</span>
                    <span id="anthropic-key-status" class="text-gray-400">Não configurado</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Modelo:</span>
                    <span id="anthropic-model-display" class="font-medium text-gray-900">-</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Temperature:</span>
                    <span id="anthropic-temp-display" class="font-medium text-gray-900">-</span>
                </div>
                
                <div class="pt-4 border-t flex gap-2">
                    <button class="btn-config-provider flex-1 text-sm py-2" data-provider="anthropic">
                        <i class="fas fa-cog mr-1"></i> Configurar
                    </button>
                    <button class="btn-test-provider p-2 bg-orange-100 text-orange-700 rounded-lg hover:bg-orange-200 transition-colors" data-provider="anthropic" title="Testar">
                        <i class="fas fa-plug"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Google Gemini Card -->
        <div class="bg-white rounded-xl border-2 border-blue-200 hover:border-blue-400 transition-all overflow-hidden">
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-gem text-3xl"></i>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="status-dot-gemini" class="w-3 h-3 rounded-full bg-gray-300"></span>
                        <span id="status-text-gemini" class="text-xs font-medium opacity-90">Desconhecido</span>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-1">Google Gemini</h3>
                <p class="text-sm opacity-90">Gemini Pro, Gemini Ultra</p>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">API Key:</span>
                    <span id="gemini-key-status" class="text-gray-400">Não configurado</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Modelo:</span>
                    <span id="gemini-model-display" class="font-medium text-gray-900">-</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Temperature:</span>
                    <span id="gemini-temp-display" class="font-medium text-gray-900">-</span>
                </div>
                
                <div class="pt-4 border-t flex gap-2">
                    <button class="btn-config-provider flex-1 text-sm py-2" data-provider="google">
                        <i class="fas fa-cog mr-1"></i> Configurar
                    </button>
                    <button class="btn-test-provider p-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors" data-provider="google" title="Testar">
                        <i class="fas fa-plug"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Configurações Gerais -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cog text-green-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Configurações Globais</h3>
                    <p class="text-sm text-gray-600">Defina o provedor ativo e parâmetros gerais</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Provedor Ativo</label>
                    <select id="ai-provedor-ativo" class="input-primary w-full">
                        <option value="openai">OpenAI (GPT)</option>
                        <option value="anthropic">Anthropic (Claude)</option>
                        <option value="google">Google (Gemini)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Provedor usado para geração de treinos</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Timeout (segundos)</label>
                    <input type="number" id="ai-timeout" class="input-primary w-full" min="30" max="300" value="120">
                    <p class="text-xs text-gray-500 mt-1">Tempo máximo de espera por resposta</p>
                </div>
            </div>
            
            <div class="flex justify-end mt-6">
                <button id="btn-save-geral" class="btn-primary">
                    <i class="fas fa-save mr-2"></i> Salvar Configurações
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Configurar Provider -->
<div id="modal-config-ai" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div id="modal-ai-icon" class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-brain text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-secondary-black" id="modal-ai-titulo">Configurar OpenAI</h2>
                    <p class="text-sm text-gray-500">Configure chave de API e parâmetros</p>
                </div>
            </div>
            <button data-close-modal="modal-config-ai" class="text-secondary-dark-gray hover:text-secondary-black">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-5">
            <input type="hidden" id="modal-provider-type">
            
            <div>
                <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Chave API *</label>
                <input type="password" id="modal-api-key" class="input-primary w-full" placeholder="sk-...">
                <p class="text-xs text-gray-500 mt-1">Chave secreta fornecida pelo provedor</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Modelo</label>
                    <select id="modal-ai-model" class="input-primary w-full">
                        <!-- Populated dynamically -->
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Max Tokens</label>
                    <input type="number" id="modal-max-tokens" class="input-primary w-full" min="100" max="32000" value="8000">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-dark-gray mb-2">
                    Temperature: <span id="temp-value" class="font-bold text-purple-600">0.5</span>
                </label>
                <input type="range" id="modal-temperature" class="w-full" min="0" max="2" step="0.1" value="0.5">
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>Determinístico (0)</span>
                    <span>Criativo (2)</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Prompt Base para Treinos</label>
                <textarea id="modal-prompt-base" class="input-primary w-full" rows="4" placeholder="Você é um profissional de Educação Física..."></textarea>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-end space-x-3 bg-gray-50">
            <button data-close-modal="modal-config-ai" class="btn-secondary">Cancelar</button>
            <button id="btn-save-provider" class="btn-primary">Salvar Configurações</button>
        </div>
    </div>
</div>

<script src="../../js/admin/ia.js"></script>

