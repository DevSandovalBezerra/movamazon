const API_BASE = '../../../../api/assessoria';

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatDate(dateStr) {
    if (!dateStr) return '--';
    const d = new Date(dateStr);
    return d.toLocaleDateString('pt-BR');
}

const statusColors = {
    rascunho: 'bg-gray-100 text-gray-700',
    ativo: 'bg-green-100 text-green-700',
    encerrado: 'bg-red-100 text-red-700',
    publicado: 'bg-blue-100 text-blue-700',
    arquivado: 'bg-yellow-100 text-yellow-700'
};

// =============================================
// LISTAGEM DE PROGRAMAS (pagina programas.php)
// =============================================

async function loadProgramas() {
    const loading = document.getElementById('programas-loading');
    const empty = document.getElementById('programas-empty');
    const list = document.getElementById('programas-list');
    if (!loading) return;

    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    list.classList.add('hidden');

    const status = document.getElementById('filtro-status')?.value || '';
    const tipo = document.getElementById('filtro-tipo')?.value || '';
    let url = `${API_BASE}/programas/list.php?`;
    if (status) url += `status=${status}&`;
    if (tipo) url += `tipo=${tipo}&`;

    try {
        const resp = await fetch(url);
        const data = await resp.json();
        loading.classList.add('hidden');

        if (!data.success || !data.programas?.length) {
            empty.classList.remove('hidden');
            return;
        }

        list.innerHTML = data.programas.map(p => `
            <a href="?page=programa-detalhe&programa_id=${p.id}" 
               class="block bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-purple-200 transition-all">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="text-base font-semibold text-gray-900 line-clamp-1">${escapeHtml(p.titulo)}</h3>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium ${statusColors[p.status] || 'bg-gray-100 text-gray-700'}">
                        ${ucfirst(p.status)}
                    </span>
                </div>
                <div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        ${ucfirst(p.tipo)}
                    </span>
                    ${p.evento_titulo ? `<span>| ${escapeHtml(p.evento_titulo)}</span>` : ''}
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-400">
                        ${p.data_inicio ? formatDate(p.data_inicio) : ''} 
                        ${p.data_fim ? '- ' + formatDate(p.data_fim) : ''}
                    </span>
                    <span class="inline-flex items-center gap-1 text-purple-600 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        ${p.total_atletas} atleta(s)
                    </span>
                </div>
            </a>
        `).join('');
        list.classList.remove('hidden');
    } catch (err) {
        loading.classList.add('hidden');
        empty.classList.remove('hidden');
        console.error('Erro ao carregar programas:', err);
    }
}

// Modal criar/editar
function abrirModalPrograma(progData) {
    document.getElementById('modal-programa').classList.remove('hidden');
    document.getElementById('programa-feedback').innerHTML = '';

    if (progData) {
        document.getElementById('modal-programa-titulo').textContent = 'Editar Programa';
        document.getElementById('prog-id').value = progData.id;
        document.getElementById('prog-titulo').value = progData.titulo || '';
        document.getElementById('prog-tipo').value = progData.tipo || 'continuo';
        document.getElementById('prog-data-inicio').value = progData.data_inicio || '';
        document.getElementById('prog-data-fim').value = progData.data_fim || '';
        document.getElementById('prog-objetivo').value = progData.objetivo || '';
        document.getElementById('prog-metodologia').value = progData.metodologia || '';
        if (progData.evento_id) {
            document.getElementById('prog-evento').value = progData.evento_id;
        }
        toggleEventoField();
    } else {
        document.getElementById('modal-programa-titulo').textContent = 'Novo Programa';
        document.getElementById('form-programa').reset();
        document.getElementById('prog-id').value = '';
    }
}

function fecharModalPrograma() {
    document.getElementById('modal-programa').classList.add('hidden');
}

function toggleEventoField() {
    const tipo = document.getElementById('prog-tipo').value;
    const campo = document.getElementById('campo-evento');
    if (campo) {
        campo.classList.toggle('hidden', tipo !== 'evento');
    }
}

document.getElementById('form-programa')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-salvar-programa');
    const feedback = document.getElementById('programa-feedback');
    btn.disabled = true;
    btn.textContent = 'Salvando...';
    feedback.innerHTML = '';

    const id = document.getElementById('prog-id').value;
    const data = {
        titulo: document.getElementById('prog-titulo').value.trim(),
        tipo: document.getElementById('prog-tipo').value,
        evento_id: document.getElementById('prog-evento')?.value || null,
        data_inicio: document.getElementById('prog-data-inicio').value || null,
        data_fim: document.getElementById('prog-data-fim').value || null,
        objetivo: document.getElementById('prog-objetivo').value.trim(),
        metodologia: document.getElementById('prog-metodologia').value.trim()
    };

    if (id) data.id = parseInt(id);

    const url = id ? `${API_BASE}/programas/update.php` : `${API_BASE}/programas/create.php`;

    try {
        const resp = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await resp.json();

        if (result.success) {
            fecharModalPrograma();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: result.message, timer: 1500, showConfirmButton: false });
            }
            if (result.programa_id && !id) {
                setTimeout(() => {
                    window.location.href = `?page=programa-detalhe&programa_id=${result.programa_id}`;
                }, 1500);
            } else {
                setTimeout(() => loadProgramas(), 1500);
            }
        } else {
            feedback.innerHTML = `<p class="text-red-600">${result.message}</p>`;
        }
    } catch (err) {
        feedback.innerHTML = '<p class="text-red-600">Erro de conexao.</p>';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Salvar';
    }
});

// =============================================
// DETALHE DO PROGRAMA (programa_detalhe.php)
// =============================================

function showProgTab(tab) {
    document.querySelectorAll('.progpanel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('[id^="progtab-"]').forEach(t => {
        t.classList.remove('border-purple-600', 'text-purple-700');
        t.classList.add('border-transparent', 'text-gray-500');
    });

    const panel = document.getElementById(`progpanel-${tab}`);
    const tabBtn = document.getElementById(`progtab-${tab}`);
    if (panel) panel.classList.remove('hidden');
    if (tabBtn) {
        tabBtn.classList.remove('border-transparent', 'text-gray-500');
        tabBtn.classList.add('border-purple-600', 'text-purple-700');
    }

    if (tab === 'atletas') loadProgAtletas();
    if (tab === 'planos') loadProgPlanos();
}

// --- Atletas do programa ---

async function loadProgAtletas() {
    const loading = document.getElementById('prog-atletas-loading');
    const empty = document.getElementById('prog-atletas-empty');
    const list = document.getElementById('prog-atletas-list');
    if (!loading || typeof PROGRAMA_ID === 'undefined') return;

    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    list.classList.add('hidden');

    try {
        const resp = await fetch(`${API_BASE}/programas/get.php?id=${PROGRAMA_ID}`);
        const data = await resp.json();
        loading.classList.add('hidden');

        if (!data.success) throw new Error(data.message);

        const atletas = data.atletas.filter(a => a.vinculo_status === 'ativo');
        document.getElementById('atletas-count').textContent = atletas.length;

        if (!atletas.length) {
            empty.classList.remove('hidden');
            return;
        }

        list.innerHTML = atletas.map(a => `
            <div class="flex items-center justify-between bg-white rounded-lg border border-gray-100 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-purple-100 rounded-full flex items-center justify-center text-purple-700 font-semibold text-sm">
                        ${escapeHtml(a.nome_completo.charAt(0))}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">${escapeHtml(a.nome_completo)}</p>
                        <p class="text-xs text-gray-400">${escapeHtml(a.email)}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    ${PROGRAMA_STATUS !== 'encerrado' ? `
                    <button onclick="abrirModalGerarPlano(${a.usuario_id}, '${escapeHtml(a.nome_completo)}')" 
                            class="text-xs px-2 py-1 bg-green-50 text-green-600 hover:bg-green-100 rounded transition-colors" title="Gerar plano">
                        Gerar Plano
                    </button>
                    <button onclick="removerAtletaPrograma(${a.usuario_id}, '${escapeHtml(a.nome_completo)}')" 
                            class="text-xs px-2 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded transition-colors" title="Remover">
                        Remover
                    </button>
                    ` : ''}
                </div>
            </div>
        `).join('');
        list.classList.remove('hidden');

        // Atualizar planos count
        document.getElementById('planos-count').textContent = data.planos?.length || 0;
    } catch (err) {
        loading.classList.add('hidden');
        empty.classList.remove('hidden');
        console.error(err);
    }
}

function abrirModalAddAtleta() {
    document.getElementById('modal-add-atleta').classList.remove('hidden');
}

function fecharModalAddAtleta() {
    document.getElementById('modal-add-atleta').classList.add('hidden');
}

async function confirmarAddAtletas() {
    const checkboxes = document.querySelectorAll('input[name="atleta_add[]"]:checked');
    const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));

    if (!ids.length) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'warning', title: 'Selecione ao menos um atleta', timer: 2000, showConfirmButton: false });
        }
        return;
    }

    const btn = document.getElementById('btn-add-atletas');
    btn.disabled = true;
    btn.textContent = 'Adicionando...';

    try {
        const resp = await fetch(`${API_BASE}/programas/atletas/add.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ programa_id: PROGRAMA_ID, atleta_ids: ids })
        });
        const data = await resp.json();

        if (data.success) {
            fecharModalAddAtleta();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
            }
            setTimeout(() => location.reload(), 1600);
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert('Erro de conexao');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Adicionar';
    }
}

async function removerAtletaPrograma(atletaId, nome) {
    let confirmar = true;
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: 'Remover atleta?',
            text: `Deseja remover ${nome} deste programa?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            confirmButtonText: 'Remover',
            cancelButtonText: 'Cancelar'
        });
        confirmar = result.isConfirmed;
    } else {
        confirmar = confirm(`Remover ${nome} deste programa?`);
    }
    if (!confirmar) return;

    try {
        const resp = await fetch(`${API_BASE}/programas/atletas/remove.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ programa_id: PROGRAMA_ID, atleta_usuario_id: atletaId })
        });
        const data = await resp.json();
        if (data.success) {
            loadProgAtletas();
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert('Erro de conexao');
    }
}

// --- Planos do programa ---

async function loadProgPlanos() {
    const loading = document.getElementById('prog-planos-loading');
    const empty = document.getElementById('prog-planos-empty');
    const list = document.getElementById('prog-planos-list');
    if (!loading || typeof PROGRAMA_ID === 'undefined') return;

    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    list.classList.add('hidden');

    try {
        const resp = await fetch(`${API_BASE}/planos/list.php?programa_id=${PROGRAMA_ID}`);
        const data = await resp.json();
        loading.classList.add('hidden');

        if (!data.success || !data.planos?.length) {
            empty.classList.remove('hidden');
            return;
        }

        document.getElementById('planos-count').textContent = data.planos.length;

        list.innerHTML = data.planos.map(p => `
            <div class="bg-white rounded-lg border border-gray-100 p-4">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-sm font-medium text-gray-900">${escapeHtml(p.atleta_nome)}</p>
                        <p class="text-xs text-gray-400">
                            ${escapeHtml(p.foco_primario || '')} | ${p.total_treinos} treinos | v${p.versao}
                        </p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium ${statusColors[p.status] || 'bg-gray-100 text-gray-700'}">
                        ${ucfirst(p.status)}
                    </span>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-400">
                    <span>Gerado em ${formatDate(p.data_criacao_plano)}</span>
                    <div class="flex gap-2">
                        ${p.status === 'rascunho' ? `
                        <button onclick="publicarPlano(${p.id})" class="px-2 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded transition-colors">
                            Publicar
                        </button>` : ''}
                        ${p.status === 'publicado' ? `
                        <button onclick="arquivarPlano(${p.id})" class="px-2 py-1 bg-yellow-50 text-yellow-600 hover:bg-yellow-100 rounded transition-colors">
                            Arquivar
                        </button>` : ''}
                        ${p.publicado_em ? `<span class="text-green-500">Publicado ${formatDate(p.publicado_em)}</span>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
        list.classList.remove('hidden');
    } catch (err) {
        loading.classList.add('hidden');
        empty.classList.remove('hidden');
        console.error(err);
    }
}

// --- Gerar plano ---

function abrirModalGerarPlano(atletaId, nome) {
    document.getElementById('gerar-atleta-id').value = atletaId;
    document.getElementById('gerar-atleta-nome').textContent = nome;
    document.getElementById('gerar-feedback').innerHTML = '';
    document.getElementById('modal-gerar-plano').classList.remove('hidden');
}

function fecharModalGerarPlano() {
    document.getElementById('modal-gerar-plano').classList.add('hidden');
}

async function confirmarGerarPlano() {
    const btn = document.getElementById('btn-gerar-plano');
    const feedback = document.getElementById('gerar-feedback');
    btn.disabled = true;
    btn.textContent = 'Gerando... (pode demorar ate 2min)';
    feedback.innerHTML = '<p class="text-blue-600">Aguarde, a IA esta gerando o plano de treino...</p>';

    const payload = {
        programa_id: PROGRAMA_ID,
        atleta_id: parseInt(document.getElementById('gerar-atleta-id').value),
        dias_semana: parseInt(document.getElementById('gerar-dias').value) || 5,
        semanas: parseInt(document.getElementById('gerar-semanas').value) || 0,
        foco: document.getElementById('gerar-foco').value.trim(),
        metodologia: document.getElementById('gerar-metodologia').value.trim(),
        observacoes: document.getElementById('gerar-obs').value.trim()
    };

    try {
        const resp = await fetch(`${API_BASE}/planos/generate.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await resp.json();

        if (data.success) {
            fecharModalGerarPlano();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: data.message, timer: 2000, showConfirmButton: false });
            }
            setTimeout(() => {
                showProgTab('planos');
            }, 2000);
        } else {
            feedback.innerHTML = `<p class="text-red-600">${data.message}</p>`;
        }
    } catch (err) {
        feedback.innerHTML = '<p class="text-red-600">Erro de conexao com o servidor.</p>';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Gerar com IA';
    }
}

// --- Publicar / Arquivar plano ---

async function publicarPlano(planoId) {
    let confirmar = true;
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: 'Publicar plano?',
            text: 'O plano ficara visivel para o atleta.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#7C3AED',
            confirmButtonText: 'Publicar',
            cancelButtonText: 'Cancelar'
        });
        confirmar = result.isConfirmed;
    }
    if (!confirmar) return;

    try {
        const resp = await fetch(`${API_BASE}/planos/publish.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ plano_id: planoId, acao: 'publicar' })
        });
        const data = await resp.json();
        if (data.success) {
            loadProgPlanos();
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert('Erro de conexao');
    }
}

async function arquivarPlano(planoId) {
    try {
        const resp = await fetch(`${API_BASE}/planos/publish.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ plano_id: planoId, acao: 'arquivar' })
        });
        const data = await resp.json();
        if (data.success) {
            loadProgPlanos();
        }
    } catch (err) {
        alert('Erro de conexao');
    }
}

// --- Ativar / Encerrar programa ---

async function ativarPrograma() {
    try {
        const resp = await fetch(`${API_BASE}/programas/update.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: PROGRAMA_ID, status: 'ativo' })
        });
        const data = await resp.json();
        if (data.success) location.reload();
        else alert(data.message);
    } catch (err) {
        alert('Erro de conexao');
    }
}

async function encerrarPrograma() {
    let confirmar = true;
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: 'Encerrar programa?',
            text: 'Os atletas nao poderao mais receber novos planos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            confirmButtonText: 'Encerrar',
            cancelButtonText: 'Cancelar'
        });
        confirmar = result.isConfirmed;
    }
    if (!confirmar) return;

    try {
        const resp = await fetch(`${API_BASE}/programas/update.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: PROGRAMA_ID, status: 'encerrado' })
        });
        const data = await resp.json();
        if (data.success) location.reload();
        else alert(data.message);
    } catch (err) {
        alert('Erro de conexao');
    }
}

// =============================================
// INICIALIZACAO
// =============================================

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('programas-loading')) {
        loadProgramas();
    }
    if (typeof PROGRAMA_ID !== 'undefined') {
        loadProgAtletas();
    }
});
