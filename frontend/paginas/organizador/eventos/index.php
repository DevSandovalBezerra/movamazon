<?php
$pageTitle = 'Meus Eventos';
$pageSubtitle = 'Gerencie todos os seus eventos';
$currentPage = 'eventos';

// Conteúdo da página
ob_start();
?>

<!-- Navegação Simples -->
<div class="mb-4 sm:mb-6 flex gap-2 sm:gap-4">
    <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
        1- Meus Eventos
    </span>
    <a href="?page=modalidades" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
        Modalidades →
    </a>
</div>

<div class="flex items-center justify-between mb-4 sm:mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Meus Eventos</h1>
        <p class="text-gray-600 mt-1">Gerencie todos os seus eventos</p>
    </div>
    <a href="?page=criar-evento" class="btn-primary">
        <i class="fas fa-plus mr-2"></i>
        Novo Evento
    </a>
</div>

<!-- Filtros -->
<div class="card mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select id="filtro-status" class="w-full px-2 py-2 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-green focus:border-brand-green text-sm">
                <option value="">Todos</option>
                <option value="ativo">Ativo</option>
                <option value="pausado">Pausado</option>
                <option value="cancelado">Cancelado</option>
                <option value="rascunho">Rascunho</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
            <select id="filtro-data" class="w-full px-2 py-2 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-green focus:border-brand-green text-sm">
                <option value="">Todas</option>
                <option value="hoje">Hoje</option>
                <option value="semana">Esta Semana</option>
                <option value="mes">Este Mês</option>
                <option value="ano">Este Ano</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input type="text" id="busca" placeholder="Nome do evento..." class="w-full px-2 py-2 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-green focus:border-brand-green text-sm">
        </div>

        <div class="flex items-end">
            <button onclick="aplicarFiltros()" class="btn-primary w-full text-xs sm:text-sm py-2 sm:py-2.5">
                <i class="fas fa-search mr-2"></i>
                Filtrar
            </button>
        </div>
    </div>
</div>

<!-- Loading -->
<div id="loading" class="text-center py-4 sm:py-6" style="display: none;">
    <div class="inline-block animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-brand-green"></div>
    <p class="mt-1 sm:mt-2 text-gray-600 text-xs sm:text-sm">Carregando eventos...</p>
</div>

<!-- Lista de Eventos -->
<div id="eventos-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
    <!-- Eventos serão carregados aqui -->
</div>

<!-- Paginação -->
<div id="paginacao" class="mt-4 sm:mt-6 flex justify-center" style="display: none;">
    <nav class="flex items-center space-x-2">
        <button id="btn-anterior" class="px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-chevron-left mr-1"></i>
            Anterior
        </button>

        <div id="paginas" class="flex items-center space-x-1 text-xs sm:text-sm">
            <!-- Páginas serão geradas aqui -->
        </div>

        <button id="btn-proximo" class="px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Próximo
            <i class="fas fa-chevron-right ml-1"></i>
        </button>
    </nav>
</div>

<!-- Erro -->
<div id="error-message" class="text-center py-4 sm:py-6" style="display: none;">
    <div class="card p-3 sm:p-4">
        <i class="fas fa-exclamation-triangle text-red-500 text-3xl sm:text-4xl mb-2 sm:mb-4"></i>
        <p class="text-red-600 mb-2 sm:mb-4 text-sm">Erro ao carregar eventos.</p>
        <button onclick="carregarEventos()" class="btn-primary text-xs sm:text-sm py-2 sm:py-2.5">
            <i class="fas fa-redo mr-2"></i>
            Tentar novamente
        </button>
    </div>
</div>

<script src="../../js/utils/eventImageUrl.js"></script>
<script src="../../js/organizador-eventos.js"></script>

<?php
$pageContent = ob_get_clean();
echo $pageContent;
?>
