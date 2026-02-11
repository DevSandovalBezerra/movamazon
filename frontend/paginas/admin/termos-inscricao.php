<?php
$activePage = 'termos-inscricao';
?>

<div class="w-full space-y-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Termos de Inscrição</h2>
            <p class="text-gray-600">Gerencie os termos de aceite de inscrição por organizador</p>
        </div>
        <button id="btn-novo-termo" class="btn-primary flex items-center gap-2">
            <i class="fas fa-plus w-4 h-4"></i>
            Novo Termo
        </button>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <div class="admin-input-wrapper">
                    <i class="fas fa-search admin-input-icon"></i>
                    <input type="text" id="campo-busca" placeholder="Título, conteúdo ou organizador..." class="admin-input admin-input--with-icon">
                </div>
            </div>
            <div class="min-w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">Organizador</label>
                <select id="filtro-organizador" class="admin-input w-full">
                    <option value="">Todos os organizadores</option>
                </select>
            </div>
            <div class="min-w-32">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="filtro-status" class="admin-input w-full">
                    <option value="">Todos</option>
                    <option value="1">Ativo</option>
                    <option value="0">Inativo</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabela de Termos -->
    <div class="admin-card overflow-hidden">
        <div id="termos-loading" class="admin-loading">
            <div class="admin-spinner"></div>
            <span>Carregando termos...</span>
        </div>
        <div id="termos-empty" class="hidden py-10 text-center text-gray-500">
            Nenhum termo encontrado.
        </div>
        <div class="overflow-x-auto scrollbar-custom">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Título</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Organizador</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Versão</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Data Criação</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody id="termos-table-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            Carregando termos...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="termos-pagination" class="mt-4 flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50"></div>
    </div>
</div>

<!-- Modal Criar/Editar Termo -->
<div id="modal-termo" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header do Modal -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 id="modal-termo-titulo" class="text-xl font-bold text-secondary-black">Novo Termo</h2>
            <button data-close-modal="modal-termo" class="text-secondary-dark-gray hover:text-secondary-black">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Conteúdo do Modal -->
        <form id="form-termo" class="p-6 space-y-4">
            <input type="hidden" id="termo-id" name="id">

            <div>
                <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Organizador *</label>
                <select id="termo-organizador-id" name="organizador_id" required class="input-primary w-full">
                    <option value="">Selecione um organizador</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">O termo será usado em todas as corridas deste organizador</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Título *</label>
                    <input type="text" id="termo-titulo" name="titulo" required class="input-primary w-full" placeholder="Ex: Termos e Condições de Inscrição">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Versão</label>
                    <input type="text" id="termo-versao" name="versao" class="input-primary w-full" value="1.0" placeholder="1.0">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Conteúdo *</label>
                <textarea id="termo-conteudo" name="conteudo" required rows="15" class="input-primary w-full" placeholder="Digite o conteúdo dos termos e condições..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Este termo será exibido e aceito pelos participantes durante a inscrição</p>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="termo-ativo" name="ativo" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" checked>
                <label for="termo-ativo" class="ml-2 text-sm text-gray-700">Termo ativo</label>
                <p class="text-xs text-gray-500 ml-4">Ao ativar, outros termos ativos do mesmo organizador serão desativados automaticamente</p>
            </div>
        </form>

        <!-- Footer do Modal -->
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex items-center justify-end gap-3">
            <button data-close-modal="modal-termo" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancelar
            </button>
            <button id="btn-salvar-termo" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                <i class="fas fa-save mr-2"></i>
                Salvar
            </button>
        </div>
    </div>
</div>

<!-- Modal Visualizar Termo -->
<div id="modal-visualizar-termo" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 id="modal-visualizar-titulo" class="text-xl font-bold text-secondary-black">Visualizar Termo</h2>
            <button data-close-modal="modal-visualizar-termo" class="text-secondary-dark-gray hover:text-secondary-black">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <div id="modal-visualizar-conteudo" class="prose max-w-none">
                <!-- Conteúdo será inserido aqui -->
            </div>
        </div>
    </div>
</div>

<script src="../../js/admin/termos-inscricao.js"></script>

