const API_BASE = '../../../../api/assessoria';
let debounceTimer = null;

// === ABAS ===
function showAtletasTab(tab) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('[id^="tab-"]').forEach(t => {
        t.classList.remove('border-purple-600', 'text-purple-700');
        t.classList.add('border-transparent', 'text-gray-500');
    });

    document.getElementById('panel-' + tab).classList.remove('hidden');
    const activeTab = document.getElementById('tab-' + tab);
    activeTab.classList.add('border-purple-600', 'text-purple-700');
    activeTab.classList.remove('border-transparent', 'text-gray-500');

    if (tab === 'meus') loadAtletas();
    if (tab === 'convites') loadConvites();
}

// === MEUS ATLETAS ===
async function loadAtletas() {
    const loading = document.getElementById('atletas-loading');
    const empty = document.getElementById('atletas-empty');
    const list = document.getElementById('atletas-list');
    const tbody = document.getElementById('atletas-tbody');

    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    list.classList.add('hidden');

    try {
        const resp = await fetch(`${API_BASE}/atletas/list.php`);
        const data = await resp.json();
        loading.classList.add('hidden');

        if (!data.success || !data.atletas.length) {
            empty.classList.remove('hidden');
            return;
        }

        tbody.innerHTML = data.atletas.map(a => {
            const statusClass = a.status === 'ativo' ? 'bg-green-100 text-green-700'
                : a.status === 'pausado' ? 'bg-yellow-100 text-yellow-700'
                : 'bg-red-100 text-red-700';
            const data_inicio = a.data_inicio ? new Date(a.data_inicio).toLocaleDateString('pt-BR') : '--';

            return `<tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900">${escapeHtml(a.nome_completo)}</td>
                <td class="px-4 py-3 text-gray-600">${escapeHtml(a.email)}</td>
                <td class="px-4 py-3 text-gray-600">${a.assessor_nome ? escapeHtml(a.assessor_nome) : '<span class="text-gray-400">--</span>'}</td>
                <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">${ucfirst(a.status)}</span></td>
                <td class="px-4 py-3 text-gray-500">${data_inicio}</td>
                <td class="px-4 py-3 text-center">
                    <a href="?page=atleta-detalhe&id=${a.usuario_id}" class="text-purple-600 hover:text-purple-800 text-xs font-medium">Ver</a>
                </td>
            </tr>`;
        }).join('');

        list.classList.remove('hidden');
    } catch (err) {
        loading.innerHTML = '<p class="text-red-500">Erro ao carregar atletas</p>';
    }
}

// === BUSCAR ATLETA PARA CONVITE ===
document.getElementById('busca-atleta')?.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    const q = this.value.trim();

    if (q.length < 2) {
        document.getElementById('busca-resultados').innerHTML = '';
        document.getElementById('busca-vazio').classList.add('hidden');
        return;
    }

    debounceTimer = setTimeout(() => buscarAtletas(q), 400);
});

async function buscarAtletas(q) {
    const container = document.getElementById('busca-resultados');
    const vazio = document.getElementById('busca-vazio');

    container.innerHTML = '<p class="text-gray-400 text-sm py-2">Buscando...</p>';
    vazio.classList.add('hidden');

    try {
        const resp = await fetch(`${API_BASE}/atletas/buscar.php?q=${encodeURIComponent(q)}`);
        const data = await resp.json();

        if (!data.success || !data.atletas.length) {
            container.innerHTML = '';
            vazio.classList.remove('hidden');
            return;
        }

        container.innerHTML = data.atletas.map(a => {
            let statusLabel = '';
            let btnHtml = '';

            if (a.vinculo_status === 'vinculado') {
                statusLabel = '<span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full">Ja vinculado</span>';
            } else if (a.vinculo_status === 'convite_pendente') {
                statusLabel = '<span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full">Convite pendente</span>';
            } else {
                btnHtml = `<button onclick="abrirModalConvite(${a.id}, '${escapeHtml(a.nome_completo)}')" 
                    class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded-lg font-medium transition-colors">
                    Convidar
                </button>`;
            }

            return `<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                <div>
                    <p class="font-medium text-gray-900 text-sm">${escapeHtml(a.nome_completo)}</p>
                    <p class="text-xs text-gray-500">${escapeHtml(a.email)}</p>
                </div>
                <div class="flex items-center gap-2">
                    ${statusLabel}
                    ${btnHtml}
                </div>
            </div>`;
        }).join('');
    } catch (err) {
        container.innerHTML = '<p class="text-red-500 text-sm">Erro na busca</p>';
    }
}

// === CONVITE ===
function abrirModalConvite(atletaId, atletaNome) {
    document.getElementById('convite-atleta-id').value = atletaId;
    document.getElementById('convite-atleta-nome').textContent = atletaNome;
    document.getElementById('convite-mensagem').value = '';
    document.getElementById('convite-feedback').innerHTML = '';
    document.getElementById('modal-convite').classList.remove('hidden');
}

function fecharModalConvite() {
    document.getElementById('modal-convite').classList.add('hidden');
}

async function confirmarConvite() {
    const btn = document.getElementById('btn-enviar-convite');
    const feedback = document.getElementById('convite-feedback');
    btn.disabled = true;
    btn.textContent = 'Enviando...';
    feedback.innerHTML = '';

    const data = {
        atleta_usuario_id: parseInt(document.getElementById('convite-atleta-id').value),
        mensagem: document.getElementById('convite-mensagem').value.trim()
    };

    try {
        const resp = await fetch(`${API_BASE}/convites/enviar.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await resp.json();

        if (result.success) {
            fecharModalConvite();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Convite enviado!', text: result.message, timer: 2000, showConfirmButton: false });
            }
            // Limpar busca
            document.getElementById('busca-atleta').value = '';
            document.getElementById('busca-resultados').innerHTML = '';
        } else {
            feedback.innerHTML = `<p class="text-red-600">${result.message}</p>`;
        }
    } catch (err) {
        feedback.innerHTML = '<p class="text-red-600">Erro de conexao</p>';
    }
    btn.disabled = false;
    btn.textContent = 'Enviar';
}

// === CONVITES ENVIADOS ===
async function loadConvites() {
    const loading = document.getElementById('convites-loading');
    const empty = document.getElementById('convites-empty');
    const list = document.getElementById('convites-list');
    const tbody = document.getElementById('convites-tbody');

    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    list.classList.add('hidden');

    try {
        const resp = await fetch(`${API_BASE}/convites/list.php`);
        const data = await resp.json();
        loading.classList.add('hidden');

        if (!data.success || !data.convites.length) {
            empty.classList.remove('hidden');
            return;
        }

        // Contar pendentes para badge
        const pendentes = data.convites.filter(c => c.status === 'pendente').length;
        const badge = document.getElementById('convites-badge');
        if (pendentes > 0) {
            badge.textContent = pendentes;
            badge.classList.remove('hidden');
        }

        tbody.innerHTML = data.convites.map(c => {
            const statusMap = {
                'pendente': 'bg-yellow-100 text-yellow-700',
                'aceito': 'bg-green-100 text-green-700',
                'recusado': 'bg-red-100 text-red-700',
                'expirado': 'bg-gray-100 text-gray-500',
                'cancelado': 'bg-gray-100 text-gray-500'
            };
            const statusClass = statusMap[c.status] || 'bg-gray-100 text-gray-500';
            const dataCriado = new Date(c.criado_em).toLocaleDateString('pt-BR');

            let acoes = '';
            if (c.status === 'pendente') {
                acoes = `<button onclick="cancelarConvite(${c.id})" class="text-xs px-2 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded transition-colors">Cancelar</button>`;
            } else if (['expirado', 'recusado', 'cancelado'].includes(c.status)) {
                acoes = `<button onclick="reenviarConvite(${c.id})" class="text-xs px-2 py-1 bg-purple-50 text-purple-600 hover:bg-purple-100 rounded transition-colors">Reenviar</button>`;
            } else {
                acoes = '<span class="text-xs text-gray-400">--</span>';
            }

            return `<tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-900">${escapeHtml(c.atleta_nome)}</p>
                    <p class="text-xs text-gray-500">${escapeHtml(c.atleta_email)}</p>
                </td>
                <td class="px-4 py-3 text-gray-600">${escapeHtml(c.enviado_por_nome)}</td>
                <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">${ucfirst(c.status)}</span></td>
                <td class="px-4 py-3 text-gray-500">${dataCriado}</td>
                <td class="px-4 py-3 text-center">${acoes}</td>
            </tr>`;
        }).join('');

        list.classList.remove('hidden');
    } catch (err) {
        loading.innerHTML = '<p class="text-red-500">Erro ao carregar convites</p>';
    }
}

async function cancelarConvite(id) {
    if (typeof Swal !== 'undefined') {
        const r = await Swal.fire({ title: 'Cancelar convite?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#7C3AED', confirmButtonText: 'Sim', cancelButtonText: 'Nao' });
        if (!r.isConfirmed) return;
    }

    try {
        const resp = await fetch(`${API_BASE}/convites/cancelar.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ convite_id: id })
        });
        const data = await resp.json();
        if (data.success) loadConvites();
        else alert(data.message);
    } catch (err) { alert('Erro de conexao'); }
}

async function reenviarConvite(id) {
    try {
        const resp = await fetch(`${API_BASE}/convites/reenviar.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ convite_id: id })
        });
        const data = await resp.json();
        if (data.success) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Reenviado!', timer: 1500, showConfirmButton: false });
            loadConvites();
        } else {
            alert(data.message);
        }
    } catch (err) { alert('Erro de conexao'); }
}

// === HELPERS ===
function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function ucfirst(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    loadAtletas();
});
