<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header("Location: index.php?page=convites-assessoria");
    exit;
}
?>

<section class="py-6 px-4 max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Convites de Assessoria</h1>
        <p class="text-gray-600 mt-1">Gerencie os convites recebidos de assessorias de corrida</p>
    </div>

    <!-- Convites pendentes -->
    <div id="convites-pendentes-section" class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Pendentes</h2>
        <div id="convites-pendentes-loading" class="text-center py-6 text-gray-500">Carregando...</div>
        <div id="convites-pendentes-empty" class="hidden text-center py-6">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <p class="text-gray-500">Nenhum convite pendente</p>
        </div>
        <div id="convites-pendentes-list" class="hidden space-y-4"></div>
    </div>

    <!-- Historico -->
    <div>
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Historico</h2>
        <div id="convites-historico-loading" class="text-center py-6 text-gray-500">Carregando...</div>
        <div id="convites-historico-empty" class="hidden text-center py-4 text-gray-400 text-sm">Nenhum historico</div>
        <div id="convites-historico-list" class="hidden space-y-3"></div>
    </div>
</section>

<script>
const CONVITES_API = '../../../../api/participante/convites';

async function loadConvitesParticipante() {
    // Pendentes
    try {
        const resp = await fetch(`${CONVITES_API}/list.php?status=pendente`);
        const data = await resp.json();

        document.getElementById('convites-pendentes-loading').classList.add('hidden');

        if (!data.success || !data.convites.length) {
            document.getElementById('convites-pendentes-empty').classList.remove('hidden');
        } else {
            const container = document.getElementById('convites-pendentes-list');
            container.innerHTML = data.convites.map(c => `
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <span class="text-purple-700 font-bold text-xs">${(c.assessoria_nome || 'A').charAt(0).toUpperCase()}</span>
                                </div>
                                <h3 class="font-semibold text-gray-900">${escapeHtml(c.assessoria_nome)}</h3>
                            </div>
                            <p class="text-sm text-gray-500 ml-10">Enviado por ${escapeHtml(c.enviado_por_nome)}</p>
                            ${c.assessoria_cidade ? `<p class="text-xs text-gray-400 ml-10">${escapeHtml(c.assessoria_cidade)}${c.assessoria_uf ? '/' + c.assessoria_uf : ''}</p>` : ''}
                            ${c.mensagem ? `<p class="text-sm text-gray-600 mt-2 ml-10 italic">"${escapeHtml(c.mensagem)}"</p>` : ''}
                            <p class="text-xs text-gray-400 mt-2 ml-10">Recebido em ${new Date(c.criado_em).toLocaleDateString('pt-BR')}</p>
                        </div>
                        <div class="flex gap-2 ml-10 sm:ml-0">
                            <button onclick="responderConvite(${c.id}, 'recusar')" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                                Recusar
                            </button>
                            <button onclick="responderConvite(${c.id}, 'aceitar')" 
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold transition-colors">
                                Aceitar
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
            container.classList.remove('hidden');
        }
    } catch (err) {
        document.getElementById('convites-pendentes-loading').innerHTML = '<p class="text-red-500">Erro ao carregar</p>';
    }

    // Historico
    try {
        const resp = await fetch(`${CONVITES_API}/list.php?status=todos`);
        const data = await resp.json();

        document.getElementById('convites-historico-loading').classList.add('hidden');

        const historico = (data.convites || []).filter(c => c.status !== 'pendente');

        if (!historico.length) {
            document.getElementById('convites-historico-empty').classList.remove('hidden');
        } else {
            const container = document.getElementById('convites-historico-list');
            const statusMap = {
                'aceito': { class: 'text-green-600', label: 'Aceito' },
                'recusado': { class: 'text-red-600', label: 'Recusado' },
                'expirado': { class: 'text-gray-500', label: 'Expirado' },
                'cancelado': { class: 'text-gray-500', label: 'Cancelado' }
            };

            container.innerHTML = historico.map(c => {
                const s = statusMap[c.status] || { class: 'text-gray-500', label: c.status };
                return `<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-700">${escapeHtml(c.assessoria_nome)}</p>
                        <p class="text-xs text-gray-400">${new Date(c.criado_em).toLocaleDateString('pt-BR')}</p>
                    </div>
                    <span class="text-xs font-medium ${s.class}">${s.label}</span>
                </div>`;
            }).join('');
            container.classList.remove('hidden');
        }
    } catch (err) {
        document.getElementById('convites-historico-loading').innerHTML = '<p class="text-red-500">Erro</p>';
    }
}

async function responderConvite(id, resposta) {
    const textoAcao = resposta === 'aceitar' ? 'aceitar este convite' : 'recusar este convite';

    if (typeof Swal !== 'undefined') {
        const r = await Swal.fire({
            title: `Deseja ${textoAcao}?`,
            icon: resposta === 'aceitar' ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: resposta === 'aceitar' ? '#7C3AED' : '#EF4444',
            confirmButtonText: resposta === 'aceitar' ? 'Aceitar' : 'Recusar',
            cancelButtonText: 'Voltar'
        });
        if (!r.isConfirmed) return;
    } else {
        if (!confirm(`Deseja ${textoAcao}?`)) return;
    }

    try {
        const resp = await fetch(`${CONVITES_API}/responder.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ convite_id: id, resposta: resposta })
        });
        const data = await resp.json();

        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: resposta === 'aceitar' ? 'success' : 'info',
                    title: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
            setTimeout(() => loadConvitesParticipante(), 1500);
        } else {
            alert(data.message || 'Erro ao responder');
        }
    } catch (err) {
        alert('Erro de conexao');
    }
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', loadConvitesParticipante);
</script>
