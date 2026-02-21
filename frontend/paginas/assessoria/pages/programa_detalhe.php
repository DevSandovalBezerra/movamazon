<?php
/**
 * Detalhe do Programa - Atletas vinculados e Planos gerados
 */
$assessoria_id = $_SESSION['assessoria_id'] ?? null;
$programa_id = (int) ($_GET['programa_id'] ?? 0);

if (!$programa_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">ID do programa nao informado.</div>';
    return;
}

// Buscar dados do programa
$programa = null;
try {
    $stmt = $pdo->prepare("
        SELECT p.*, e.titulo as evento_titulo, 
               COALESCE(e.data_realizacao, e.data_inicio) as evento_data
        FROM assessoria_programas p
        LEFT JOIN eventos e ON p.evento_id = e.id
        WHERE p.id = ? AND p.assessoria_id = ?
        LIMIT 1
    ");
    $stmt->execute([$programa_id, $assessoria_id]);
    $programa = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("[PROGRAMA_DETALHE] Erro: " . $e->getMessage());
}

if (!$programa) {
    echo '<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-700">Programa nao encontrado.</div>';
    return;
}

// Atletas da assessoria disponiveis (para adicionar ao programa)
$atletas_disponiveis = [];
try {
    $stmt = $pdo->prepare("
        SELECT aa.atleta_usuario_id as id, u.nome_completo, u.email
        FROM assessoria_atletas aa
        JOIN usuarios u ON aa.atleta_usuario_id = u.id
        WHERE aa.assessoria_id = ? AND aa.status = 'ativo'
          AND aa.atleta_usuario_id NOT IN (
              SELECT pa.atleta_usuario_id FROM assessoria_programa_atletas pa 
              WHERE pa.programa_id = ? AND pa.status = 'ativo'
          )
        ORDER BY u.nome_completo ASC
    ");
    $stmt->execute([$assessoria_id, $programa_id]);
    $atletas_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("[PROGRAMA_DETALHE] Erro atletas: " . $e->getMessage());
}

$statusColors = [
    'rascunho' => 'bg-gray-100 text-gray-700',
    'ativo' => 'bg-green-100 text-green-700',
    'encerrado' => 'bg-red-100 text-red-700'
];
$statusColor = $statusColors[$programa['status']] ?? 'bg-gray-100 text-gray-700';
?>

<!-- Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div class="flex items-start gap-4">
        <a href="?page=programas" class="mt-1 p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($programa['titulo']) ?></h1>
            <div class="flex flex-wrap items-center gap-2 mt-1">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $statusColor ?>"><?= ucfirst($programa['status']) ?></span>
                <span class="text-xs text-gray-400"><?= ucfirst($programa['tipo']) ?></span>
                <?php if ($programa['evento_titulo']): ?>
                <span class="text-xs text-gray-400">| <?= htmlspecialchars($programa['evento_titulo']) ?></span>
                <?php endif; ?>
                <?php if ($programa['data_inicio']): ?>
                <span class="text-xs text-gray-400">| <?= date('d/m/Y', strtotime($programa['data_inicio'])) ?><?= $programa['data_fim'] ? ' - ' . date('d/m/Y', strtotime($programa['data_fim'])) : '' ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="flex gap-2">
        <?php if ($programa['status'] === 'rascunho'): ?>
        <button onclick="ativarPrograma()" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">Ativar</button>
        <?php endif; ?>
        <?php if ($programa['status'] === 'ativo'): ?>
        <button onclick="encerrarPrograma()" class="px-3 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-sm font-medium transition-colors">Encerrar</button>
        <?php endif; ?>
    </div>
</div>

<?php if ($programa['objetivo']): ?>
<div class="bg-purple-50 border border-purple-100 rounded-lg p-4 mb-6">
    <p class="text-sm text-purple-800"><strong>Objetivo:</strong> <?= htmlspecialchars($programa['objetivo']) ?></p>
    <?php if ($programa['metodologia']): ?>
    <p class="text-sm text-purple-700 mt-1"><strong>Metodologia:</strong> <?= htmlspecialchars($programa['metodologia']) ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Abas -->
<div class="flex border-b mb-6">
    <button onclick="showProgTab('atletas')" id="progtab-atletas" class="px-4 py-2.5 text-sm font-medium border-b-2 border-purple-600 text-purple-700 transition-all">
        Atletas <span id="atletas-count" class="ml-1 px-1.5 py-0.5 bg-purple-100 text-purple-700 text-xs rounded-full">0</span>
    </button>
    <button onclick="showProgTab('planos')" id="progtab-planos" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">
        Planos de Treino <span id="planos-count" class="ml-1 px-1.5 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">0</span>
    </button>
</div>

<!-- ABA ATLETAS -->
<div id="progpanel-atletas" class="progpanel">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-sm font-semibold text-gray-700">Atletas do Programa</h3>
        <?php if (!empty($atletas_disponiveis) && $programa['status'] !== 'encerrado'): ?>
        <button onclick="abrirModalAddAtleta()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-medium transition-colors">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Adicionar Atletas
        </button>
        <?php endif; ?>
    </div>
    <div id="prog-atletas-loading" class="text-center py-6 text-gray-400 text-sm">Carregando...</div>
    <div id="prog-atletas-empty" class="hidden text-center py-6 text-gray-400 text-sm">Nenhum atleta neste programa.</div>
    <div id="prog-atletas-list" class="hidden space-y-2"></div>
</div>

<!-- ABA PLANOS -->
<div id="progpanel-planos" class="progpanel hidden">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-sm font-semibold text-gray-700">Planos Gerados</h3>
    </div>
    <div id="prog-planos-loading" class="text-center py-6 text-gray-400 text-sm">Carregando...</div>
    <div id="prog-planos-empty" class="hidden text-center py-6 text-gray-400 text-sm">Nenhum plano gerado ainda.</div>
    <div id="prog-planos-list" class="hidden space-y-3"></div>
</div>

<!-- Modal Adicionar Atletas -->
<div id="modal-add-atleta" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Adicionar Atletas ao Programa</h3>
            <button onclick="fecharModalAddAtleta()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <div class="space-y-2 max-h-60 overflow-y-auto mb-4" id="atletas-checkbox-list">
            <?php foreach ($atletas_disponiveis as $ad): ?>
            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                <input type="checkbox" name="atleta_add[]" value="<?= $ad['id'] ?>" class="rounded text-purple-600 focus:ring-purple-500">
                <div>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($ad['nome_completo']) ?></p>
                    <p class="text-xs text-gray-400"><?= htmlspecialchars($ad['email']) ?></p>
                </div>
            </label>
            <?php endforeach; ?>
            <?php if (empty($atletas_disponiveis)): ?>
            <p class="text-sm text-gray-400 text-center py-4">Todos os atletas ja estao neste programa.</p>
            <?php endif; ?>
        </div>
        <div class="flex gap-3">
            <button onclick="fecharModalAddAtleta()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
            <button onclick="confirmarAddAtletas()" id="btn-add-atletas" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold">Adicionar</button>
        </div>
    </div>
</div>

<!-- Modal Gerar Plano -->
<div id="modal-gerar-plano" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Gerar Plano de Treino</h3>
            <button onclick="fecharModalGerarPlano()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <p class="text-sm text-gray-500 mb-4">Atleta: <span id="gerar-atleta-nome" class="font-medium text-gray-700"></span></p>
        <input type="hidden" id="gerar-atleta-id">
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dias/semana</label>
                    <input type="number" id="gerar-dias" value="5" min="2" max="7"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Semanas</label>
                    <input type="number" id="gerar-semanas" value="0" min="0" max="52" placeholder="Auto"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
                    <p class="text-xs text-gray-400 mt-0.5">0 = calcular automaticamente</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Foco principal</label>
                <input type="text" id="gerar-foco" placeholder="Ex: Base aerobica, Velocidade..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Metodologia</label>
                <input type="text" id="gerar-metodologia" placeholder="Ex: Periodizacao linear..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                <textarea id="gerar-obs" rows="2" placeholder="Orientacoes adicionais para a IA..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="fecharModalGerarPlano()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button onclick="confirmarGerarPlano()" id="btn-gerar-plano" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold">
                    Gerar com IA
                </button>
            </div>
            <div id="gerar-feedback" class="text-center text-sm"></div>
        </div>
    </div>
</div>

<script>
const PROGRAMA_ID = <?= $programa_id ?>;
const PROGRAMA_STATUS = '<?= $programa['status'] ?>';
</script>
<script src="../../../js/assessoria/programas.js"></script>
