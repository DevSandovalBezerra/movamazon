<?php
/**
 * Listagem e criacao de Programas de Treino
 */
$assessoria_id = $_SESSION['assessoria_id'] ?? null;
$is_admin = ($_SESSION['assessoria_funcao'] ?? '') === 'admin';

// Buscar eventos disponiveis para select
$eventos = [];
try {
    $stmt = $pdo->prepare("
        SELECT e.id, e.titulo, COALESCE(e.data_realizacao, e.data_inicio) as data_evento
        FROM eventos e
        WHERE COALESCE(e.data_realizacao, e.data_inicio) >= CURDATE()
        ORDER BY e.data_inicio ASC
    ");
    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("[PROGRAMAS_PAGE] Erro eventos: " . $e->getMessage());
}
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Programas</h1>
        <p class="text-gray-500 mt-1">Crie e gerencie programas de treino para seus atletas</p>
    </div>
    <button onclick="abrirModalPrograma()" 
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Novo Programa
    </button>
</div>

<!-- Filtros -->
<div class="flex flex-wrap gap-3 mb-6">
    <select id="filtro-status" onchange="loadProgramas()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
        <option value="">Todos os status</option>
        <option value="rascunho">Rascunho</option>
        <option value="ativo">Ativo</option>
        <option value="encerrado">Encerrado</option>
    </select>
    <select id="filtro-tipo" onchange="loadProgramas()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
        <option value="">Todos os tipos</option>
        <option value="evento">Evento</option>
        <option value="continuo">Continuo</option>
    </select>
</div>

<!-- Lista -->
<div id="programas-loading" class="text-center py-8 text-gray-500">Carregando...</div>
<div id="programas-empty" class="hidden text-center py-8">
    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <p class="text-gray-500">Nenhum programa encontrado.</p>
    <p class="text-sm text-gray-400 mt-1">Crie um novo programa para comecar.</p>
</div>
<div id="programas-list" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>

<!-- Modal Criar/Editar Programa -->
<div id="modal-programa" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 id="modal-programa-titulo" class="text-lg font-bold text-gray-900">Novo Programa</h3>
            <button onclick="fecharModalPrograma()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <form id="form-programa" class="space-y-4">
            <input type="hidden" id="prog-id" value="">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titulo *</label>
                <input type="text" id="prog-titulo" required minlength="3"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"
                       placeholder="Ex: Preparacao Maratona 2026">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select id="prog-tipo" required onchange="toggleEventoField()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
                        <option value="continuo">Continuo</option>
                        <option value="evento">Para Evento</option>
                    </select>
                </div>
                <div id="campo-evento" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
                    <select id="prog-evento"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Selecione...</option>
                        <?php foreach ($eventos as $ev): ?>
                        <option value="<?= $ev['id'] ?>"><?= htmlspecialchars($ev['titulo']) ?> (<?= $ev['data_evento'] ? date('d/m/Y', strtotime($ev['data_evento'])) : '' ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data inicio</label>
                    <input type="date" id="prog-data-inicio"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data fim</label>
                    <input type="date" id="prog-data-fim"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Objetivo</label>
                <textarea id="prog-objetivo" rows="2" placeholder="Ex: Completar a maratona em menos de 4h"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Metodologia</label>
                <textarea id="prog-metodologia" rows="2" placeholder="Ex: Periodizacao linear com foco em base aerobica"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="fecharModalPrograma()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="submit" id="btn-salvar-programa" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold">Salvar</button>
            </div>
            <div id="programa-feedback" class="text-center text-sm"></div>
        </form>
    </div>
</div>

<script src="../../../js/assessoria/programas.js"></script>
