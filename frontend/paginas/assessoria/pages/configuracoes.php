<?php
/**
 * Configuracoes da Assessoria
 * Apenas admin pode acessar
 */
if (($_SESSION['assessoria_funcao'] ?? '') !== 'admin') {
    echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">Acesso restrito ao administrador da assessoria.</div>';
    return;
}

$assessoria_id = $_SESSION['assessoria_id'] ?? null;
$assessoria = null;

if ($assessoria_id) {
    $stmt = $pdo->prepare("SELECT * FROM assessorias WHERE id = ? LIMIT 1");
    $stmt->execute([$assessoria_id]);
    $assessoria = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$assessoria) {
    echo '<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-700">Assessoria nao encontrada.</div>';
    return;
}
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Configuracoes</h1>
    <p class="text-gray-500 mt-1">Gerencie os dados da sua assessoria</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-2xl">
    <form id="form-configuracoes" class="space-y-5">
        <input type="hidden" name="assessoria_id" value="<?= $assessoria['id'] ?>">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select name="tipo" id="cfg-tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
                    <option value="PF" <?= $assessoria['tipo'] === 'PF' ? 'selected' : '' ?>>Pessoa Fisica</option>
                    <option value="PJ" <?= $assessoria['tipo'] === 'PJ' ? 'selected' : '' ?>>Pessoa Juridica</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CPF/CNPJ</label>
                <input type="text" name="cpf_cnpj" value="<?= htmlspecialchars($assessoria['cpf_cnpj']) ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-gray-50" readonly>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
            <input type="text" name="nome_fantasia" value="<?= htmlspecialchars($assessoria['nome_fantasia']) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500" required>
        </div>

        <div id="razao-social-wrap" class="<?= $assessoria['tipo'] === 'PJ' ? '' : 'hidden' ?>">
            <label class="block text-sm font-medium text-gray-700 mb-1">Razao Social</label>
            <input type="text" name="razao_social" value="<?= htmlspecialchars($assessoria['razao_social'] ?? '') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email de Contato</label>
                <input type="email" name="email_contato" value="<?= htmlspecialchars($assessoria['email_contato'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input type="tel" name="telefone_contato" value="<?= htmlspecialchars($assessoria['telefone_contato'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site</label>
                <input type="url" name="site" value="<?= htmlspecialchars($assessoria['site'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"
                       placeholder="https://">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                <input type="text" name="instagram" value="<?= htmlspecialchars($assessoria['instagram'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"
                       placeholder="@sua_assessoria">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Endereco</label>
            <input type="text" name="endereco" value="<?= htmlspecialchars($assessoria['endereco'] ?? '') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                <input type="text" name="cidade" value="<?= htmlspecialchars($assessoria['cidade'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                <input type="text" name="uf" value="<?= htmlspecialchars($assessoria['uf'] ?? '') ?>" maxlength="2"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                <input type="text" name="cep" value="<?= htmlspecialchars($assessoria['cep'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"
                       placeholder="00000-000">
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t">
            <span class="text-xs text-gray-400">
                Status: <span class="font-medium <?= $assessoria['status'] === 'ativo' ? 'text-green-600' : 'text-yellow-600' ?>"><?= ucfirst($assessoria['status']) ?></span>
            </span>
            <button type="submit" id="btn-salvar-config"
                    class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold text-sm transition-colors">
                Salvar Alteracoes
            </button>
        </div>

        <div id="config-feedback" class="text-center text-sm"></div>
    </form>
</div>

<script>
document.getElementById('cfg-tipo')?.addEventListener('change', function() {
    const wrap = document.getElementById('razao-social-wrap');
    if (this.value === 'PJ') wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
});

document.getElementById('form-configuracoes')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-salvar-config');
    const feedback = document.getElementById('config-feedback');
    btn.disabled = true;
    btn.textContent = 'Salvando...';
    feedback.innerHTML = '';

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const resp = await fetch('../../../../api/assessoria/update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await resp.json();
        if (result.success) {
            feedback.innerHTML = '<p class="text-green-600">Dados salvos com sucesso!</p>';
        } else {
            feedback.innerHTML = `<p class="text-red-600">${result.message}</p>`;
        }
    } catch (err) {
        feedback.innerHTML = '<p class="text-red-600">Erro ao salvar. Tente novamente.</p>';
    }
    btn.disabled = false;
    btn.textContent = 'Salvar Alteracoes';
});
</script>
