<?php
/**
 * Dashboard da Assessoria
 * Exibe cards com metricas resumidas
 */

$assessoria_id = $_SESSION['assessoria_id'] ?? null;
$stats = ['atletas' => 0, 'programas' => 0, 'treinos_semana' => 0, 'aderencia' => 0];

if ($assessoria_id) {
    try {
        // Total de atletas vinculados
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM assessoria_atletas WHERE assessoria_id = ? AND status = 'ativo'");
        $stmt->execute([$assessoria_id]);
        $stats['atletas'] = (int) $stmt->fetchColumn();

        // Total de programas ativos
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM assessoria_programas WHERE assessoria_id = ? AND status = 'ativo'");
        $stmt->execute([$assessoria_id]);
        $stats['programas'] = (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("[ASSESSORIA_DASHBOARD] Erro: " . $e->getMessage());
    }
}

$assessoriaNome = '';
if ($assessoria_id) {
    try {
        $stmt = $pdo->prepare("SELECT nome_fantasia FROM assessorias WHERE id = ? LIMIT 1");
        $stmt->execute([$assessoria_id]);
        $assessoriaNome = $stmt->fetchColumn() ?: '';
    } catch (Exception $e) {}
}
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <?php if ($assessoriaNome): ?>
    <p class="text-gray-500 mt-1"><?= htmlspecialchars($assessoriaNome) ?></p>
    <?php endif; ?>
</div>

<!-- Cards de metricas -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Atletas -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900"><?= $stats['atletas'] ?></p>
        <p class="text-sm text-gray-500 mt-1">Atletas vinculados</p>
    </div>

    <!-- Programas -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900"><?= $stats['programas'] ?></p>
        <p class="text-sm text-gray-500 mt-1">Programas ativos</p>
    </div>

    <!-- Treinos da semana -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900"><?= $stats['treinos_semana'] ?></p>
        <p class="text-sm text-gray-500 mt-1">Treinos esta semana</p>
    </div>

    <!-- Aderencia -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900"><?= $stats['aderencia'] ?>%</p>
        <p class="text-sm text-gray-500 mt-1">Aderencia geral</p>
    </div>
</div>

<!-- Acoes rapidas -->
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
    <h2 class="text-lg font-bold text-gray-900 mb-4">Acoes Rapidas</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="?page=atletas" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">Convidar Atleta</p>
                <p class="text-xs text-gray-500">Enviar convite para um atleta</p>
            </div>
        </a>
        <a href="?page=programas" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">Novo Programa</p>
                <p class="text-xs text-gray-500">Criar programa de treino</p>
            </div>
        </a>
        <a href="?page=monitoramento" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-all">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">Monitoramento</p>
                <p class="text-xs text-gray-500">Acompanhar progresso</p>
            </div>
        </a>
    </div>
</div>
