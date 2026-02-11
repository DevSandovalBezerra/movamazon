<?php
$activePage = 'dashboard';
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Administrativo</h1>
        <p class="text-gray-600 mt-2">Visão geral da plataforma MovAmazon</p>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4 mb-6">
        <!-- Total de Eventos -->
        <div class="stats-card">
            <div class="stats-card-icon bg-blue-100">
                <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-label">Total de Eventos</div>
                <div class="stats-card-value" id="stat-total-eventos">0</div>
                <div class="text-xs text-gray-500 mt-1">
                    <span id="stat-eventos-ativos">0</span> ativos
                </div>
            </div>
        </div>

        <!-- Total de Inscrições -->
        <div class="stats-card">
            <div class="stats-card-icon bg-green-100">
                <i class="fas fa-clipboard-list text-green-600 text-xl"></i>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-label">Total de Inscrições</div>
                <div class="stats-card-value" id="stat-total-inscricoes">0</div>
                <div class="text-xs text-gray-500 mt-1">
                    <span id="stat-inscricoes-confirmadas">0</span> confirmadas
                </div>
            </div>
        </div>

        <!-- Receita Total -->
        <div class="stats-card">
            <div class="stats-card-icon bg-yellow-100">
                <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-label">Receita Total</div>
                <div class="stats-card-value text-yellow-600" id="stat-receita-total">R$ 0</div>
                <div class="text-xs text-gray-500 mt-1">
                    Mês atual: <span id="stat-receita-mes">R$ 0</span>
                </div>
            </div>
        </div>

        <!-- Organizadores -->
        <div class="stats-card">
            <div class="stats-card-icon bg-purple-100">
                <i class="fas fa-users-cog text-purple-600 text-xl"></i>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-label">Organizadores</div>
                <div class="stats-card-value" id="stat-total-organizadores">0</div>
                <div class="text-xs text-gray-500 mt-1">
                    <span id="stat-organizadores-ativos">0</span> ativos
                </div>
            </div>
        </div>

        <!-- Participantes -->
        <div class="stats-card">
            <div class="stats-card-icon bg-indigo-100">
                <i class="fas fa-users text-indigo-600 text-xl"></i>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-label">Participantes</div>
                <div class="stats-card-value" id="stat-total-participantes">0</div>
                <div class="text-xs text-gray-500 mt-1">
                    <span id="stat-participantes-ativos">0</span> ativos
                </div>
            </div>
        </div>

        <!-- Pagamentos -->
        <div class="stats-card">
            <div class="stats-card-icon bg-pink-100">
                <i class="fas fa-credit-card text-pink-600 text-xl"></i>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-label">Pagamentos</div>
                <div class="stats-card-value" id="stat-pagamentos-aprovados">0</div>
                <div class="text-xs text-gray-500 mt-1">
                    <span id="stat-pagamentos-pendentes">0</span> pendentes
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Gráfico Receita Mensal -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="fas fa-chart-line admin-card-icon bg-[#0b4340] text-[#f5c113]"></i>
                    Receita Mensal (Últimos 6 Meses)
                </h3>
            </div>
            <div class="h-64">
                <canvas id="grafico-receita-mensal"></canvas>
            </div>
        </div>

        <!-- Gráfico Inscrições Mensais -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="fas fa-chart-bar admin-card-icon bg-[#0b4340] text-[#f5c113]"></i>
                    Inscrições por Mês
                </h3>
            </div>
            <div class="h-64">
                <canvas id="grafico-inscricoes-mensal"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Distribuição de Eventos por Status -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="fas fa-pie-chart admin-card-icon bg-[#0b4340] text-[#f5c113]"></i>
                    Eventos por Status
                </h3>
            </div>
            <div class="h-64">
                <canvas id="grafico-dist-eventos"></canvas>
            </div>
        </div>

        <!-- Top 5 Eventos -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="fas fa-trophy admin-card-icon bg-[#0b4340] text-[#f5c113]"></i>
                    Top 5 Eventos por Inscrições
                </h3>
            </div>
            <div class="h-64">
                <canvas id="grafico-top-eventos"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabelas de Dados Recentes -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Eventos Recentes -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="fas fa-calendar admin-card-icon bg-blue-100 text-blue-600"></i>
                    Eventos Recentes
                </h3>
            </div>
            <div id="eventos-recentes-container" class="space-y-3">
                <div class="admin-loading">
                    <div class="admin-spinner"></div>
                </div>
            </div>
        </div>

        <!-- Inscrições Recentes -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="fas fa-clipboard-list admin-card-icon bg-green-100 text-green-600"></i>
                    Inscrições Recentes
                </h3>
            </div>
            <div id="inscricoes-recentes-container" class="space-y-3">
                <div class="admin-loading">
                    <div class="admin-spinner"></div>
                </div>
            </div>
        </div>

        <!-- Pagamentos Recentes -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="fas fa-dollar-sign admin-card-icon bg-yellow-100 text-yellow-600"></i>
                    Pagamentos Recentes
                </h3>
            </div>
            <div id="pagamentos-recentes-container" class="space-y-3">
                <div class="admin-loading">
                    <div class="admin-spinner"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="../../js/utils/eventImageUrl.js"></script>
<script src="../../js/admin/dashboard.js"></script>
