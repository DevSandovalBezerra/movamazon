<?php
/**
 * Painel de Monitoramento - Aderencia, Alertas e Progresso
 */
$assessoria_id = $_SESSION['assessoria_id'] ?? null;
$atleta_detalhe_id = (int) ($_GET['atleta_id'] ?? 0);
?>

<?php if (!$atleta_detalhe_id): ?>
<!-- ===== VISAO GERAL ===== -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Monitoramento</h1>
    <p class="text-gray-500 mt-1">Acompanhe a aderencia e o progresso dos seus atletas</p>
</div>

<!-- Cards de resumo -->
<div id="monitor-loading" class="text-center py-8 text-gray-500">Carregando metricas...</div>
<div id="monitor-cards" class="hidden grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Atletas Ativos</p>
        <p id="card-atletas" class="text-2xl font-bold text-gray-900">0</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Aderencia Geral</p>
        <p id="card-aderencia" class="text-2xl font-bold text-purple-700">0%</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">PSE Medio (30d)</p>
        <p id="card-pse" class="text-2xl font-bold text-gray-900">0</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Alertas (7d)</p>
        <p id="card-alertas" class="text-2xl font-bold text-red-600">0</p>
    </div>
</div>

<!-- Duas colunas: Aderencia por atleta + Alertas recentes -->
<div id="monitor-content" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Aderencia por atleta -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Aderencia por Atleta</h3>
        <div id="aderencia-list" class="space-y-3"></div>
        <div id="aderencia-empty" class="hidden text-center text-sm text-gray-400 py-4">Nenhum atleta vinculado</div>
    </div>

    <!-- Alertas recentes -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Alertas Recentes</h3>
        <div id="alertas-list" class="space-y-3"></div>
        <div id="alertas-empty" class="hidden text-center text-sm text-gray-400 py-4">Nenhum alerta nos ultimos 7 dias</div>
    </div>
</div>

<?php else: ?>
<!-- ===== DETALHE DO ATLETA ===== -->
<div class="mb-6 flex items-center gap-4">
    <a href="?page=monitoramento" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h1 id="atleta-detalhe-nome" class="text-2xl font-bold text-gray-900">Carregando...</h1>
        <p class="text-gray-500 text-sm">Progresso detalhado</p>
    </div>
</div>

<!-- Filtro periodo -->
<div class="flex gap-2 mb-6">
    <button onclick="loadAtletaDetalhe(7)" class="periodo-btn px-3 py-1.5 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50">7 dias</button>
    <button onclick="loadAtletaDetalhe(30)" class="periodo-btn px-3 py-1.5 rounded-lg text-sm bg-purple-600 text-white">30 dias</button>
    <button onclick="loadAtletaDetalhe(60)" class="periodo-btn px-3 py-1.5 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50">60 dias</button>
    <button onclick="loadAtletaDetalhe(90)" class="periodo-btn px-3 py-1.5 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50">90 dias</button>
</div>

<!-- Metricas do atleta -->
<div id="atleta-metricas" class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Treinos Realizados</p>
        <p id="m-realizados" class="text-xl font-bold text-gray-900">0</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Aderencia</p>
        <p id="m-aderencia" class="text-xl font-bold text-purple-700">0%</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">PSE Medio</p>
        <p id="m-pse" class="text-xl font-bold text-gray-900">0</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Duracao Media</p>
        <p id="m-duracao" class="text-xl font-bold text-gray-900">0min</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Alertas</p>
        <p id="m-alertas" class="text-xl font-bold text-red-600">0</p>
    </div>
</div>

<!-- Grafico PSE -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Evolucao PSE</h3>
    <div style="height: 200px;">
        <canvas id="pse-chart"></canvas>
    </div>
</div>

<!-- Historico -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Historico de Progresso</h3>
    <div id="historico-list" class="space-y-2"></div>
    <div id="historico-empty" class="hidden text-center text-sm text-gray-400 py-4">Nenhum progresso registrado</div>
</div>

<!-- Modal Feedback -->
<div id="modal-feedback" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Adicionar Feedback</h3>
        <input type="hidden" id="feedback-progresso-id">
        <textarea id="feedback-texto" rows="3" placeholder="Seu feedback para o atleta..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500 mb-4"></textarea>
        <div class="flex gap-3">
            <button onclick="fecharModalFeedback()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
            <button onclick="enviarFeedback()" id="btn-feedback" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold">Enviar</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>const ATLETA_DETALHE_ID = <?= $atleta_detalhe_id ?>;</script>
<?php endif; ?>

<script src="../../../js/assessoria/monitoramento.js"></script>
