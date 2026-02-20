<?php
/**
 * Visao 360 do Atleta
 */
$assessoria_id = $_SESSION['assessoria_id'] ?? null;
$atleta_id = (int) ($_GET['id'] ?? 0);

if (!$atleta_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">ID do atleta nao informado.</div>';
    return;
}

// Buscar dados do atleta
$atleta = null;
$inscricoes = [];
$programas = [];

try {
    $stmt = $pdo->prepare("
        SELECT aa.*, u.nome_completo, u.email, u.telefone, u.documento, u.data_nascimento,
               u.cidade, u.uf, u.genero,
               ass.nome_completo as assessor_nome
        FROM assessoria_atletas aa
        JOIN usuarios u ON aa.atleta_usuario_id = u.id
        LEFT JOIN usuarios ass ON aa.assessor_usuario_id = ass.id
        WHERE aa.assessoria_id = ? AND aa.atleta_usuario_id = ?
        LIMIT 1
    ");
    $stmt->execute([$assessoria_id, $atleta_id]);
    $atleta = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($atleta) {
        // Inscricoes
        $stmt = $pdo->prepare("
            SELECT i.id, i.status_pagamento, i.created_at,
                   e.titulo as evento_titulo, e.data_inicio as evento_data
            FROM inscricoes i
            JOIN eventos e ON i.evento_id = e.id
            WHERE i.usuario_id = ?
            ORDER BY e.data_inicio DESC LIMIT 10
        ");
        $stmt->execute([$atleta_id]);
        $inscricoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Programas
        $stmt = $pdo->prepare("
            SELECT pa.status, p.titulo, p.tipo, p.data_inicio, p.data_fim
            FROM assessoria_programa_atletas pa
            JOIN assessoria_programas p ON pa.programa_id = p.id
            WHERE pa.atleta_usuario_id = ? AND p.assessoria_id = ?
        ");
        $stmt->execute([$atleta_id, $assessoria_id]);
        $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("[ATLETA_DETALHE] Erro: " . $e->getMessage());
}

if (!$atleta) {
    echo '<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-700">Atleta nao encontrado nesta assessoria.</div>';
    return;
}
?>

<!-- Header com voltar -->
<div class="mb-6 flex items-center gap-4">
    <a href="?page=atletas" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($atleta['nome_completo']) ?></h1>
        <p class="text-gray-500 text-sm"><?= htmlspecialchars($atleta['email']) ?></p>
    </div>
    <span class="ml-auto px-3 py-1 rounded-full text-xs font-medium 
        <?= $atleta['status'] === 'ativo' ? 'bg-green-100 text-green-700' : ($atleta['status'] === 'pausado' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?>">
        <?= ucfirst($atleta['status']) ?>
    </span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Perfil -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wider">Perfil</h3>
        <div class="space-y-3 text-sm">
            <div><span class="text-gray-500">Telefone:</span> <span class="font-medium"><?= htmlspecialchars($atleta['telefone'] ?? '--') ?></span></div>
            <div><span class="text-gray-500">Documento:</span> <span class="font-medium"><?= htmlspecialchars($atleta['documento'] ?? '--') ?></span></div>
            <div><span class="text-gray-500">Nascimento:</span> <span class="font-medium"><?= $atleta['data_nascimento'] ? date('d/m/Y', strtotime($atleta['data_nascimento'])) : '--' ?></span></div>
            <div><span class="text-gray-500">Genero:</span> <span class="font-medium"><?= htmlspecialchars($atleta['genero'] ?? '--') ?></span></div>
            <div><span class="text-gray-500">Cidade:</span> <span class="font-medium"><?= htmlspecialchars(($atleta['cidade'] ?? '') . ($atleta['uf'] ? '/' . $atleta['uf'] : '')) ?: '--' ?></span></div>
        </div>
        <hr class="my-4">
        <div class="space-y-3 text-sm">
            <div><span class="text-gray-500">Assessor:</span> <span class="font-medium"><?= htmlspecialchars($atleta['assessor_nome'] ?? 'Nao atribuido') ?></span></div>
            <div><span class="text-gray-500">Vinculado em:</span> <span class="font-medium"><?= $atleta['data_inicio'] ? date('d/m/Y', strtotime($atleta['data_inicio'])) : '--' ?></span></div>
            <div><span class="text-gray-500">Origem:</span> <span class="font-medium"><?= ucfirst(str_replace('_', ' ', $atleta['origem'])) ?></span></div>
        </div>
    </div>

    <!-- Inscricoes em eventos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wider">Inscricoes em Eventos</h3>
        <?php if (empty($inscricoes)): ?>
            <p class="text-sm text-gray-400">Nenhuma inscricao encontrada.</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($inscricoes as $i): ?>
            <div class="flex items-start justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($i['evento_titulo']) ?></p>
                    <p class="text-xs text-gray-500"><?= $i['evento_data'] ? date('d/m/Y', strtotime($i['evento_data'])) : '' ?></p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full 
                    <?= in_array($i['status_pagamento'], ['approved', 'pago', 'paid', 'confirmado', 'aprovado']) ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                    <?= ucfirst($i['status_pagamento'] ?? 'pendente') ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Programas -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wider">Programas</h3>
        <?php if (empty($programas)): ?>
            <p class="text-sm text-gray-400">Nenhum programa vinculado.</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($programas as $p): ?>
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($p['titulo']) ?></p>
                <p class="text-xs text-gray-500">
                    <?= ucfirst($p['tipo']) ?> | 
                    <?= $p['data_inicio'] ? date('d/m/Y', strtotime($p['data_inicio'])) : '' ?>
                    <?= $p['data_fim'] ? ' - ' . date('d/m/Y', strtotime($p['data_fim'])) : '' ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($atleta['observacoes']): ?>
<div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wider">Observacoes</h3>
    <p class="text-sm text-gray-600"><?= nl2br(htmlspecialchars($atleta['observacoes'])) ?></p>
</div>
<?php endif; ?>
