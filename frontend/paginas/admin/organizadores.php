<?php
$activePage = 'organizadores';
?>

<div class="w-full space-y-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Gerenciamento de Organizadores</h2>
            <p class="text-gray-600">Gerencie usuários organizadores e seus eventos</p>
        </div>
        <button id="btn-novo-organizador" class="btn-primary flex items-center gap-2">
            <i class="fas fa-plus w-4 h-4"></i>
            Novo Organizador
        </button>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <div class="admin-input-wrapper">
                    <i class="fas fa-search admin-input-icon"></i>
                    <input type="text" id="campo-busca" placeholder="Nome, email ou empresa..." class="admin-input admin-input--with-icon">
                </div>
            </div>
            <div class="min-w-32">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="filtro-status" class="admin-input w-full">
                    <option value="">Todos</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </select>
            </div>
            <div class="min-w-32">
                <label class="block text-sm font-medium text-gray-700 mb-2">Região</label>
                <select id="filtro-regiao" class="admin-input w-full">
                    <option value="">Todas as regiões</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabela de Organizadores -->
    <div class="admin-card overflow-hidden">
        <div id="organizadores-loading" class="admin-loading">
            <div class="admin-spinner"></div>
            <span>Carregando organizadores...</span>
        </div>
        <div id="organizadores-empty" class="hidden py-10 text-center text-gray-500">
            Nenhum organizador encontrado.
        </div>
        <div class="overflow-x-auto scrollbar-custom">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Empresa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Região</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Eventos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Cadastro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody id="organizadores-table-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                            Carregando organizadores...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="organizadores-pagination" class="mt-4 flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50"></div>
    </div>
</div>

<!-- Modal Criar/Editar -->
<div id="modal-organizador" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header do Modal -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 id="modal-organizador-titulo" class="text-xl font-bold text-secondary-black">Novo Organizador</h2>
            <button data-close-modal="modal-organizador" class="text-secondary-dark-gray hover:text-secondary-black">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Conteúdo do Modal -->
        <form id="form-organizador" class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Nome Completo *</label>
                    <input type="text" id="org-nome" name="nome_completo" required class="input-primary w-full" placeholder="Nome completo">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Email *</label>
                    <input type="email" id="org-email" name="email" required class="input-primary w-full" placeholder="email@exemplo.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Telefone</label>
                    <input type="text" id="org-telefone" name="telefone" class="input-primary w-full" placeholder="(00) 0000-0000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Celular</label>
                    <input type="text" id="org-celular" name="celular" class="input-primary w-full" placeholder="(00) 00000-0000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Empresa *</label>
                    <input type="text" id="org-empresa" name="empresa" required class="input-primary w-full" placeholder="Nome da empresa">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Região (UF) *</label>
                    <select id="org-regiao" name="regiao" required class="input-primary w-full">
                                <option value="">Selecione</option>
                                <option value="AC">AC</option>
                                <option value="AL">AL</option>
                                <option value="AP">AP</option>
                                <option value="AM">AM</option>
                                <option value="BA">BA</option>
                                <option value="CE">CE</option>
                                <option value="DF">DF</option>
                                <option value="ES">ES</option>
                                <option value="GO">GO</option>
                                <option value="MA">MA</option>
                                <option value="MT">MT</option>
                                <option value="MS">MS</option>
                                <option value="MG">MG</option>
                                <option value="PA">PA</option>
                                <option value="PB">PB</option>
                                <option value="PR">PR</option>
                                <option value="PE">PE</option>
                                <option value="PI">PI</option>
                                <option value="RJ">RJ</option>
                                <option value="RN">RN</option>
                                <option value="RS">RS</option>
                                <option value="RO">RO</option>
                                <option value="RR">RR</option>
                                <option value="SC">SC</option>
                                <option value="SP">SP</option>
                                <option value="SE">SE</option>
                                <option value="TO">TO</option>
                            </select>
                        </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Modalidade Esportiva *</label>
                    <input type="text" id="org-modalidade" name="modalidade_esportiva" required class="input-primary w-full" placeholder="Ex: Corrida de Rua">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Quantidade de Eventos</label>
                    <input type="text" id="org-quantidade-eventos" name="quantidade_eventos" class="input-primary w-full" placeholder="Ex: 1, 2-4, 5+">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Regulamento</label>
                    <select id="org-regulamento" name="regulamento" class="input-primary w-full">
                        <option value="">Selecione</option>
                        <option value="Sim, Tenho Regulamento do Evento">Sim, Tenho Regulamento do Evento</option>
                        <option value="Não, Preciso de Ajuda">Não, Preciso de Ajuda</option>
                    </select>
                </div>
            </div>
            
            <!-- Footer do Modal -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" data-close-modal="modal-organizador" class="btn-secondary">Cancelar</button>
                <button type="submit" id="btn-salvar-organizador" class="btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detalhes -->
<div id="modal-detalhes" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" data-close-modal="modal-detalhes"></div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <!-- Header do Modal -->
            <div class="bg-gradient-to-r from-[#0b4340] to-[#1a5f5a] px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Detalhes do Organizador</h3>
                        <p class="text-sm text-white text-opacity-90">Informações completas</p>
                    </div>
                </div>
                <button data-close-modal="modal-detalhes" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Conteúdo do Modal -->
            <div class="bg-white px-6 py-4 max-h-[70vh] overflow-y-auto">
                <div id="organizador-detalhes" class="space-y-4 text-sm"></div>
            </div>

            <!-- Footer do Modal -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3">
                <button data-close-modal="modal-detalhes" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    Fechar
                </button>
                <button id="btn-editar-detalhes" class="px-6 py-2 bg-[#0b4340] hover:bg-[#065f5a] text-white font-medium rounded-lg transition-colors flex items-center space-x-2">
                    <i class="fas fa-edit w-4 h-4"></i>
                    <span>Editar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação -->
<div id="modal-confirmacao" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" data-close-modal="modal-confirmacao"></div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <!-- Header do Modal -->
            <div class="bg-white px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900" id="modal-confirmacao-titulo">Confirmar ação</h3>
            </div>

            <!-- Conteúdo do Modal -->
            <div class="bg-white px-6 py-4">
                <p id="modal-confirmacao-texto" class="text-gray-600"></p>
            </div>

            <!-- Footer do Modal -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3">
                <button data-close-modal="modal-confirmacao" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    Cancelar
                </button>
                <button id="btn-confirmar-acao" class="btn-primary">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="../../js/admin/organizadores.js"></script>

