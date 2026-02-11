<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    header('Location: ../../auth/login.php');
    exit();
}
?>
<!-- Conteúdo da página de Cupons de Desconto -->
<div class="w-full">
    <!-- Navegação Sequencial -->
    <div class="mb-4 sm:mb-6 flex gap-2 sm:gap-4">
        <a href="?page=lotes-inscricao" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
            ← Lotes de Inscrição
        </a>
        <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
            4- Cupons de Desconto
        </span>
        <a href="?page=questionario" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
            Questionário →
        </a>
    </div>

    <!-- Cabeçalho -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div class="flex-1">
                <div class="flex items-center space-x-2 mb-2">
                    <a href="../index.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                    <span class="text-gray-400">/</span>
                    <span class="font-semibold">Cupons de Desconto</span>
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900">Cupons de Desconto</h1>
                <p class="text-gray-600 mt-2 text-sm sm:text-base">Gerencie os cupons de desconto dos seus eventos</p>
            </div>
            <button id="btn-novo-cupom" class="bg-brand-green hover:bg-green-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg flex items-center justify-center w-full sm:w-auto transition">
                <i class="fas fa-plus mr-2"></i>
                <span class="hidden sm:inline">Novo Cupom</span>
                <span class="sm:hidden">Novo</span>
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
                <select id="filtro-evento" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Selecione um evento</option>
                </select>
            </div>
            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="filtro-status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Todos</option>
                    <option value="ativo">Ativos</option>
                    <option value="cancelado">Cancelados</option>
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Período</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <input type="date" id="filtro-inicio" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Início">
                    <input type="date" id="filtro-fim" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Fim">
                </div>
            </div>
            <div class="flex items-end sm:col-span-2 lg:col-span-1">
                <button type="submit" form="filtros-cupom" class="w-full bg-brand-green hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center justify-center transition">
                    <i class="fas fa-search mr-2"></i>
                    <span class="hidden sm:inline">Filtrar</span>
                    <span class="sm:hidden">Filtrar</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Estado Inicial - Selecione um Evento -->
    <div id="estado-inicial" class="bg-blue-50 rounded-lg border border-blue-200 p-6 sm:p-8 lg:p-12 text-center">
        <div class="max-w-md mx-auto">
            <div class="text-blue-500 mb-4">
                <i class="fas fa-filter text-3xl sm:text-4xl lg:text-6xl"></i>
            </div>
            <h3 class="text-lg sm:text-xl lg:text-2xl font-semibold text-blue-900 mb-2">Selecione um Evento</h3>
            <p class="text-blue-600 text-sm sm:text-base">Escolha um evento para gerenciar os cupons de desconto.</p>
        </div>
    </div>

    <!-- Estado Filtrado - Tabela de Cupons -->
    <div id="estado-filtrado" class="hidden">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 sm:px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                            <th class="px-2 sm:px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                            <th class="px-2 sm:px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                            <th class="px-2 sm:px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-2 sm:px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                            <th class="px-2 sm:px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-2 sm:px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usos</th>
                            <th class="px-2 sm:px-3 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-cupons" class="bg-white divide-y divide-gray-200">
                    </tbody>
                </table>
            </div>
            <div id="msg-vazio" class="text-center text-gray-500 py-8 hidden">Nenhum cupom/remessa encontrada.</div>
        </div>
    </div>
</div>

<!-- Modal de Criação/Edição -->
<div id="modal-cupom" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden p-2 sm:p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl max-h-screen overflow-y-auto">
        <div class="p-4 sm:p-6 lg:p-8">
            <button id="btn-fechar-modal" class="absolute top-2 right-2 text-gray-400 hover:text-red-600 text-2xl">&times;</button>
            <h2 id="modal-titulo" class="text-lg sm:text-xl font-bold mb-6">CUPONS DE DESCONTO</h2>
            <p class="text-gray-600 mb-6">Crie/Edite seus cupons aqui.</p>

            <!-- Gerador de Código -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-2">Cupom individual</h3>
                <p class="text-sm text-blue-600 mb-4">(clique em gerar código ou forneça um de até 40 caracteres)</p>
                <div class="flex gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código do Cupom:</label>
                        <input type="text" id="cupom-codigo" class="w-full border border-gray-300 rounded-lg px-3 py-2 font-mono" maxlength="40" placeholder="Código do cupom">
                    </div>
                    <div class="flex items-end">
                        <button id="btn-gerar-codigo" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-magic mr-2"></i>Gerar código
                        </button>
                    </div>
                </div>
                <div id="loading-geracao" class="hidden text-center mt-2">
                    <div class="inline-block w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
            </div>

            <!-- Informações do Cupom -->
            <div class="border-t pt-6">
                <h3 class="font-semibold text-gray-800 mb-4">INFORMAÇÕES DO CUPOM</h3>
                <form id="form-cupom" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Título cupom</label>
                            <input type="text" id="cupom-titulo" class="border rounded px-3 py-2 w-full" maxlength="250" placeholder="máx de 250 caracteres" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tipo do valor:</label>
                            <select id="cupom-tipo-valor" class="border rounded px-3 py-2 w-full" required>
                                <option value="">Selecione</option>
                                <option value="percentual">Percentual (%)</option>
                                <option value="valor_real">Valor Real (R$)</option>
                                <option value="preco_fixo">Preço Fixo</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Valor de desconto sobre o preço vigente:
                                <i class="fas fa-question-circle text-blue-500 ml-1" title="Informações sobre o desconto"></i>
                            </label>
                            <div class="relative">
                                <input type="text" id="cupom-valor" class="border rounded px-3 py-2 w-full pr-8" placeholder="Selecione o tipo primeiro" required>
                                <div id="valor-icon" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                    <i class="fas fa-percentage" id="percent-icon" style="display: none;"></i>
                                    <i class="fas fa-dollar-sign" id="money-icon" style="display: none;"></i>
                                </div>
                            </div>
                            <p id="valor-help" class="text-xs text-gray-500 mt-1"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de cupom:</label>
                            <select id="cupom-tipo-desconto" class="border rounded px-3 py-2 w-full" required>
                                <option value="">Selecione</option>
                                <option value="ambos">Ambos</option>
                                <option value="web">Web</option>
                                <option value="mobile">Mobile</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Data de início:</label>
                            <input type="date" id="cupom-inicio" class="border rounded px-3 py-2 w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Válido até:</label>
                            <input type="date" id="cupom-fim" class="border rounded px-3 py-2 w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Quantas vezes poderá ser utilizado?</label>
                            <input type="number" id="cupom-max-uso" class="border rounded px-3 py-2 w-full" min="1" value="1" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Evento</label>
                            <select id="cupom-evento" class="border rounded px-3 py-2 w-full" required>
                                <option value="">Selecione</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                            <select id="cupom-status" class="border rounded px-3 py-2 w-full" required>
                                <option value="ativo">Ativo</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <label class="flex items-center">
                            <input type="checkbox" id="cupom-habilitar-produtos" class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Habilitar o mesmo desconto para produtos pagos</span>
                        </label>
                    </div>
                    <p class="text-xs text-blue-600">* Opção válida apenas para cupons do tipo Percentual</p>

                    <div class="flex justify-between mt-6">
                        <button type="button" id="btn-voltar" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition">Voltar</button>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition">Salvar</button>
                    </div>
                    <input type="hidden" id="cupom-id">
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Aviso de Responsabilidade -->
<div id="modal-aviso" class="fixed inset-0 z-60 flex items-center justify-center bg-black bg-opacity-40 hidden p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <div class="bg-blue-600 text-white p-4 rounded-t-lg flex justify-between items-center">
            <h3 class="font-bold text-lg">ATENÇÃO</h3>
            <button id="btn-fechar-aviso" class="text-white hover:text-gray-200 text-xl">&times;</button>
        </div>
        <div class="p-6">
            <p class="text-gray-700 mb-6">
                Você está criando um cupom que poderá ser usado para qualquer um de seus eventos abertos e com vagas disponíveis.
            </p>
            <div class="flex justify-end space-x-3">
                <button id="btn-cancelar-aviso" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">Cancelar ação</button>
                <button id="btn-confirmar-aviso" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">Estou ciente</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição de Status -->
<div id="modal-status" class="fixed inset-0 z-60 flex items-center justify-center bg-black bg-opacity-40 hidden p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <div class="bg-blue-600 text-white p-4 rounded-t-lg flex justify-between items-center">
            <h3 class="font-bold text-lg">EDITAR STATUS</h3>
            <button id="btn-fechar-status" class="text-white hover:text-gray-200 text-xl">&times;</button>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status do Cupom:</label>
                <select id="status-cupom" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="ativo">Ativo</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button id="btn-cancelar-status" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition">Cancelar</button>
                <button id="btn-salvar-status" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">Salvar</button>
            </div>
            <input type="hidden" id="cupom-id-status">
        </div>
    </div>
</div>

<script src="../../js/cupons-remessa.js"></script>
