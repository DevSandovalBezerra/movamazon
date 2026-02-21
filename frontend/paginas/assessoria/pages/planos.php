<?php
/**
 * Visao geral de todos os Planos de Treino da assessoria
 */
$assessoria_id = $_SESSION['assessoria_id'] ?? null;
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Planos de Treino</h1>
    <p class="text-gray-500 mt-1">Todos os planos gerados pela assessoria</p>
</div>

<!-- Filtros -->
<div class="flex flex-wrap gap-3 mb-6">
    <select id="filtro-plano-status" onchange="loadTodosPlanos()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
        <option value="">Todos os status</option>
        <option value="rascunho">Rascunho</option>
        <option value="publicado">Publicado</option>
        <option value="arquivado">Arquivado</option>
    </select>
</div>

<div id="all-planos-loading" class="text-center py-8 text-gray-500">Carregando...</div>
<div id="all-planos-empty" class="hidden text-center py-8">
    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
    </svg>
    <p class="text-gray-500">Nenhum plano de treino gerado.</p>
    <p class="text-sm text-gray-400 mt-1">Gere planos a partir de um programa.</p>
</div>
<div id="all-planos-list" class="hidden space-y-3"></div>

<script>
const API_PLANOS = '../../../../api/assessoria';

function escHtml(s) {
    if (!s) return '';
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

const sColors = {
    rascunho: 'bg-gray-100 text-gray-700',
    publicado: 'bg-blue-100 text-blue-700',
    arquivado: 'bg-yellow-100 text-yellow-700'
};

async function loadTodosPlanos() {
    const loading = document.getElementById('all-planos-loading');
    const empty = document.getElementById('all-planos-empty');
    const list = document.getElementById('all-planos-list');
    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    list.classList.add('hidden');

    try {
        const resp = await fetch(`${API_PLANOS}/planos/list.php`);
        const data = await resp.json();
        loading.classList.add('hidden');

        let planos = data.planos || [];
        const statusFiltro = document.getElementById('filtro-plano-status').value;
        if (statusFiltro) {
            planos = planos.filter(p => p.status === statusFiltro);
        }

        if (!planos.length) {
            empty.classList.remove('hidden');
            return;
        }

        list.innerHTML = planos.map(p => `
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">${escHtml(p.atleta_nome)}</p>
                        <p class="text-xs text-gray-400">
                            Programa: ${escHtml(p.programa_titulo || 'Sem programa')} | ${p.total_treinos} treinos | v${p.versao}
                        </p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium ${sColors[p.status] || 'bg-gray-100 text-gray-700'}">
                        ${p.status ? p.status.charAt(0).toUpperCase() + p.status.slice(1) : ''}
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-400 mt-3">
                    <span>${p.foco_primario ? escHtml(p.foco_primario) : ''}</span>
                    <span>${p.metodologia ? escHtml(p.metodologia) : ''}</span>
                    <span class="ml-auto">${p.data_criacao_plano ? new Date(p.data_criacao_plano).toLocaleDateString('pt-BR') : ''}</span>
                </div>
                <div class="flex justify-end gap-2 mt-3">
                    ${p.status === 'rascunho' ? `<button onclick="pubPlano(${p.id})" class="text-xs px-3 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded transition-colors">Publicar</button>` : ''}
                    ${p.status === 'publicado' ? `<button onclick="arqPlano(${p.id})" class="text-xs px-3 py-1 bg-yellow-50 text-yellow-600 hover:bg-yellow-100 rounded transition-colors">Arquivar</button>` : ''}
                    ${p.programa_id ? `<a href="?page=programa-detalhe&programa_id=${p.programa_id}" class="text-xs px-3 py-1 bg-purple-50 text-purple-600 hover:bg-purple-100 rounded transition-colors">Ver Programa</a>` : ''}
                </div>
            </div>
        `).join('');
        list.classList.remove('hidden');
    } catch (err) {
        loading.classList.add('hidden');
        empty.classList.remove('hidden');
    }
}

async function pubPlano(id) {
    try {
        const r = await fetch(`${API_PLANOS}/planos/publish.php`, {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({plano_id: id, acao: 'publicar'})
        });
        const d = await r.json();
        if (d.success) loadTodosPlanos();
        else alert(d.message);
    } catch(e) { alert('Erro'); }
}

async function arqPlano(id) {
    try {
        const r = await fetch(`${API_PLANOS}/planos/publish.php`, {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({plano_id: id, acao: 'arquivar'})
        });
        const d = await r.json();
        if (d.success) loadTodosPlanos();
    } catch(e) { alert('Erro'); }
}

document.addEventListener('DOMContentLoaded', loadTodosPlanos);
</script>
