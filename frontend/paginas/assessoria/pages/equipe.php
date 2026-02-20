<?php
/**
 * Gestao da Equipe da Assessoria
 * Apenas admin pode acessar
 */
if (($_SESSION['assessoria_funcao'] ?? '') !== 'admin') {
    echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">Acesso restrito ao administrador da assessoria.</div>';
    return;
}

$assessoria_id = $_SESSION['assessoria_id'] ?? null;
$membros = [];

if ($assessoria_id) {
    $stmt = $pdo->prepare("
        SELECT ae.id, ae.funcao, ae.status, ae.created_at,
               u.nome_completo, u.email, u.telefone
        FROM assessoria_equipe ae
        JOIN usuarios u ON ae.usuario_id = u.id
        WHERE ae.assessoria_id = ?
        ORDER BY ae.funcao ASC, u.nome_completo ASC
    ");
    $stmt->execute([$assessoria_id]);
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Equipe</h1>
        <p class="text-gray-500 mt-1">Gerencie os membros da sua assessoria</p>
    </div>
    <button onclick="abrirModalAdicionarMembro()" 
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Adicionar Membro
    </button>
</div>

<!-- Lista de membros -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <?php if (empty($membros)): ?>
    <div class="p-8 text-center text-gray-500">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <p>Nenhum membro na equipe ainda.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Nome</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Funcao</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Desde</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-600">Acoes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($membros as $m): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900"><?= htmlspecialchars($m['nome_completo']) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($m['email']) ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                            <?= $m['funcao'] === 'admin' ? 'bg-purple-100 text-purple-700' : ($m['funcao'] === 'assessor' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') ?>">
                            <?= ucfirst($m['funcao']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                            <?= $m['status'] === 'ativo' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                            <?= ucfirst($m['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500"><?= date('d/m/Y', strtotime($m['created_at'])) ?></td>
                    <td class="px-4 py-3 text-center">
                        <?php if ($m['funcao'] !== 'admin'): ?>
                        <button onclick="toggleStatusMembro(<?= $m['id'] ?>, '<?= $m['status'] === 'ativo' ? 'inativo' : 'ativo' ?>')"
                                class="text-xs px-2 py-1 rounded <?= $m['status'] === 'ativo' ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100' ?> transition-colors">
                            <?= $m['status'] === 'ativo' ? 'Desativar' : 'Ativar' ?>
                        </button>
                        <?php else: ?>
                        <span class="text-xs text-gray-400">--</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Adicionar Membro -->
<div id="modal-adicionar-membro" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Adicionar Membro</h3>
            <button onclick="fecharModalMembro()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <form id="form-adicionar-membro" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email do usuario</label>
                <input type="email" name="email" id="membro-email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"
                       placeholder="email@assessor.com">
                <p class="text-xs text-gray-400 mt-1">O usuario deve estar cadastrado no MovAmazon</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Funcao</label>
                <select name="funcao" id="membro-funcao" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
                    <option value="assessor">Assessor</option>
                    <option value="suporte">Suporte</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="fecharModalMembro()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="submit" id="btn-add-membro" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold">Adicionar</button>
            </div>
            <div id="membro-feedback" class="text-center text-sm"></div>
        </form>
    </div>
</div>

<script>
function abrirModalAdicionarMembro() {
    document.getElementById('modal-adicionar-membro').classList.remove('hidden');
}

function fecharModalMembro() {
    document.getElementById('modal-adicionar-membro').classList.add('hidden');
    document.getElementById('form-adicionar-membro').reset();
    document.getElementById('membro-feedback').innerHTML = '';
}

document.getElementById('form-adicionar-membro')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-add-membro');
    const feedback = document.getElementById('membro-feedback');
    btn.disabled = true;
    btn.textContent = 'Adicionando...';
    feedback.innerHTML = '';

    const data = {
        email: document.getElementById('membro-email').value.trim(),
        funcao: document.getElementById('membro-funcao').value
    };

    try {
        const resp = await fetch('../../../../api/assessoria/equipe/add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await resp.json();
        if (result.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Membro adicionado!', timer: 1500, showConfirmButton: false });
            }
            setTimeout(() => location.reload(), 1600);
        } else {
            feedback.innerHTML = `<p class="text-red-600">${result.message}</p>`;
            btn.disabled = false;
            btn.textContent = 'Adicionar';
        }
    } catch (err) {
        feedback.innerHTML = '<p class="text-red-600">Erro de conexao.</p>';
        btn.disabled = false;
        btn.textContent = 'Adicionar';
    }
});

async function toggleStatusMembro(membroId, novoStatus) {
    const confirmText = novoStatus === 'inativo' ? 'Deseja desativar este membro?' : 'Deseja reativar este membro?';
    
    let confirmar = true;
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: 'Confirmar',
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#7C3AED',
            confirmButtonText: 'Sim',
            cancelButtonText: 'Cancelar'
        });
        confirmar = result.isConfirmed;
    } else {
        confirmar = confirm(confirmText);
    }

    if (!confirmar) return;

    try {
        const resp = await fetch('../../../../api/assessoria/equipe/status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ membro_id: membroId, status: novoStatus })
        });
        const result = await resp.json();
        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'Erro ao alterar status');
        }
    } catch (err) {
        alert('Erro de conexao');
    }
}
</script>
