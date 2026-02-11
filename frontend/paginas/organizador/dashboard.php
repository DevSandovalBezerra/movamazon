<?php
// Conteúdo principal do dashboard do organizador
?>
<!-- Loading -->
<div id="loading" class="text-center py-4 sm:py-6 lg:py-8">
    <div class="inline-block animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-brand-green"></div>
    <p class="mt-1 sm:mt-2 text-gray-600 text-xs sm:text-sm lg:text-base">Carregando dados...</p>
</div>

<!-- Conteúdo -->
<div id="dashboard-content" style="display: none;">
    <!-- Card Tutorial -->
    <div class="card p-4 sm:p-5 lg:p-6 mb-4 sm:mb-6 lg:mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-1">
                Primeiro acesso? Veja o passo a passo para criar seu evento
            </h2>
            <p class="text-sm sm:text-base text-gray-600">
                Use o menu numerado à esquerda e siga o guia completo para configurar seu evento com segurança.
            </p>
        </div>
        <a href="?page=tutorial-evento" class="btn-primary inline-flex items-center gap-2 mt-1 sm:mt-0">
            <i class="fas fa-graduation-cap"></i>
            Tutorial: Como criar meu evento
        </a>
    </div>
    <!-- Métricas Principais -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6 lg:mb-8">
        <!-- Card 1: Inscrições Confirmadas -->
        <div class="card p-4 sm:p-5 lg:p-6 hover:shadow-lg transition-shadow cursor-pointer" onclick="toggleCardDetails('inscricoes-details')">
            <div class="flex items-center justify-between mb-3">
                <div class="flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1">Inscrições Confirmadas</p>
                    <p id="inscricoes-confirmadas" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-green-600">0</p>
                    <p id="inscricoes-variacao" class="text-xs sm:text-sm text-gray-500 mt-1"></p>
                </div>
                <div class="p-3 sm:p-4 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl sm:text-2xl"></i>
                </div>
            </div>
            <div id="inscricoes-details" class="hidden mt-3 pt-3 border-t border-gray-200">
                <div class="space-y-2 text-xs sm:text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Confirmadas e Pagas:</span>
                        <span id="inscricoes-confirmadas-pagas" class="font-semibold text-green-600">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Pendentes de Pagamento:</span>
                        <span id="inscricoes-pendentes-pagamento" class="font-semibold text-yellow-600">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Pendentes de Confirmação:</span>
                        <span id="inscricoes-pendentes-confirmacao" class="font-semibold text-gray-500">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Canceladas:</span>
                        <span id="inscricoes-canceladas" class="font-semibold text-red-600">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Receita Confirmada -->
        <div class="card p-4 sm:p-5 lg:p-6 hover:shadow-lg transition-shadow cursor-pointer" onclick="toggleCardDetails('receita-details')">
            <div class="flex items-center justify-between mb-3">
                <div class="flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1">Receita Confirmada</p>
                    <p id="receita-confirmada" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-green-600">R$ 0,00</p>
                    <p id="receita-variacao" class="text-xs sm:text-sm text-gray-500 mt-1"></p>
                </div>
                <div class="p-3 sm:p-4 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-dollar-sign text-xl sm:text-2xl"></i>
                </div>
            </div>
            <div id="receita-details" class="hidden mt-3 pt-3 border-t border-gray-200">
                <div class="space-y-2 text-xs sm:text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Receita Pendente:</span>
                        <span id="receita-pendente" class="font-semibold text-yellow-600">R$ 0,00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Receita Cancelada:</span>
                        <span id="receita-cancelada" class="font-semibold text-red-600">R$ 0,00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Mês Atual:</span>
                        <span id="receita-mes-atual" class="font-semibold text-gray-900">R$ 0,00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Taxa de Conversão -->
        <div class="card p-4 sm:p-5 lg:p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1">Taxa de Conversão</p>
                    <p id="taxa-conversao" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-600">0%</p>
                </div>
                <div class="p-3 sm:p-4 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-percentage text-xl sm:text-2xl"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="taxa-conversao-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Inscrições que se tornaram pagas</p>
            </div>
        </div>

        <!-- Card 4: Eventos Ativos -->
        <div class="card p-4 sm:p-5 lg:p-6 hover:shadow-lg transition-shadow cursor-pointer" onclick="toggleCardDetails('eventos-details')">
            <div class="flex items-center justify-between mb-3">
                <div class="flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1">Eventos Ativos</p>
                    <p id="total-eventos" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900">0</p>
                </div>
                <div class="p-3 sm:p-4 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-calendar-alt text-xl sm:text-2xl"></i>
                </div>
            </div>
            <div id="eventos-details" class="hidden mt-3 pt-3 border-t border-gray-200">
                <div class="space-y-2 text-xs sm:text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Completos:</span>
                        <span id="eventos-completos" class="font-semibold text-green-600">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Incompletos:</span>
                        <span id="eventos-incompletos" class="font-semibold text-yellow-600">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Gráficos de Análise Temporal -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6 lg:mb-8">
        <div class="card p-4 sm:p-5 lg:p-6">
            <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 mb-4 text-center">Tendência de Inscrições</h3>
            <div class="relative h-64 sm:h-72 lg:h-80">
                <div id="grafico-tendencia" class="absolute inset-0 flex items-center justify-center text-gray-500">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-brand-green mb-2"></div>
                        <p class="text-xs sm:text-sm">Carregando gráfico...</p>
                    </div>
                </div>
                <canvas id="canvas-tendencia" style="display: none;"></canvas>
            </div>
        </div>
        <div class="card p-4 sm:p-5 lg:p-6">
            <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 mb-4 text-center">Receita ao Longo do Tempo</h3>
            <div class="relative h-64 sm:h-72 lg:h-80">
                <div id="grafico-receita" class="absolute inset-0 flex items-center justify-center text-gray-500">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-brand-green mb-2"></div>
                        <p class="text-xs sm:text-sm">Carregando gráfico...</p>
                    </div>
                </div>
                <canvas id="canvas-receita" style="display: none;"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráficos de Distribuição -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6 lg:mb-8">
        <div class="card p-4 sm:p-5 lg:p-6">
            <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 mb-4 text-center">Distribuição por Status</h3>
            <div class="relative h-64 sm:h-72 lg:h-80">
                <div id="grafico-status" class="absolute inset-0 flex items-center justify-center text-gray-500">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-brand-green mb-2"></div>
                        <p class="text-xs sm:text-sm">Carregando gráfico...</p>
                    </div>
                </div>
                <canvas id="canvas-status" style="display: none;"></canvas>
            </div>
        </div>
        <div class="card p-4 sm:p-5 lg:p-6">
            <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 mb-4 text-center">Inscrições por Modalidade</h3>
            <div class="relative h-64 sm:h-72 lg:h-80">
                <div id="grafico-modalidades" class="absolute inset-0 flex items-center justify-center text-gray-500">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-brand-green mb-2"></div>
                        <p class="text-xs sm:text-sm">Carregando gráfico...</p>
                    </div>
                </div>
                <canvas id="canvas-modalidades" style="display: none;"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico de Formas de Pagamento -->
    <div class="grid grid-cols-1 mb-4 sm:mb-6 lg:mb-8">
        <div class="card p-4 sm:p-5 lg:p-6">
            <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 mb-4 text-center">Formas de Pagamento</h3>
            <div class="relative h-64 sm:h-72">
                <div id="grafico-formas-pagamento" class="absolute inset-0 flex items-center justify-center text-gray-500">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-brand-green mb-2"></div>
                        <p class="text-xs sm:text-sm">Carregando gráfico...</p>
                    </div>
                </div>
                <canvas id="canvas-formas-pagamento" style="display: none;"></canvas>
            </div>
        </div>
    </div>
    <!-- Lista de Eventos -->
    <div class="card p-2 sm:p-3 lg:p-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-center mb-2 sm:mb-3 lg:mb-4 xl:mb-6 gap-1 sm:gap-2 lg:gap-0">
            <h3 class="text-sm sm:text-base lg:text-lg xl:text-xl font-semibold text-gray-900 text-center">Seus Eventos</h3>
            <!--  <a href="eventos/criar.php" class="btn-primary">
                <i class="fas fa-plus mr-2"></i>
                Novo Evento
            </a> -->
        </div>
        <div id="eventos-lista" class="space-y-2 sm:space-y-3 lg:space-y-4">
            <!-- Eventos serão carregados aqui -->
        </div>
    </div>
    <!-- Atividades Recentes -->
    <div class="card p-2 sm:p-3 lg:p-4 mt-3 sm:mt-4 lg:mt-6 xl:mt-8">
        <h3 class="text-sm sm:text-base lg:text-lg xl:text-xl font-semibold text-gray-900 mb-2 sm:mb-3 lg:mb-4 xl:mb-6 text-center">Atividades Recentes</h3>
        <div id="atividades-recentes" class="space-y-1 sm:space-y-2 lg:space-y-3">
            <!-- Atividades serão carregadas aqui -->
        </div>
    </div>
</div>
<!-- Erro -->
<div id="error-message" class="text-center py-3 sm:py-4 lg:py-6 xl:py-8" style="display: none;">
    <div class="card p-2 sm:p-3 lg:p-4">
        <i class="fas fa-exclamation-triangle text-red-500 text-xl sm:text-2xl lg:text-3xl xl:text-4xl mb-2 sm:mb-3 lg:mb-4"></i>
        <p class="text-red-600 mb-2 sm:mb-3 lg:mb-4 text-xs sm:text-sm lg:text-base">Erro ao carregar dados do dashboard.</p>
        <button onclick="carregarDashboard()" class="btn-primary text-xs sm:text-sm lg:text-base">
            <i class="fas fa-redo mr-1 sm:mr-2"></i>
            Tentar novamente
        </button>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="../../js/utils/eventImageUrl.js"></script>
<!-- Script do dashboard -->
<script src="../../js/eventos.js"></script>

<!-- Script dos gráficos -->
<script type="module">
    import { carregarGraficos } from '../../js/organizador/dashboard-charts.js';
    
    // Exportar função globalmente para ser chamada após carregar dados
    window.carregarGraficos = carregarGraficos;
    
    // Carregar gráficos apenas após o dashboard estar carregado
    // A função será chamada pelo eventos.js após os dados serem carregados
</script>

<script>
// Função para expandir/colapsar detalhes dos cards
function toggleCardDetails(cardId) {
    const details = document.getElementById(cardId);
    if (details) {
        details.classList.toggle('hidden');
    }
}
</script>
