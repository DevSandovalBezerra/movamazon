const API_BASE = '../../../../api/assessoria';

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function formatDate(d) {
    if (!d) return '--';
    return new Date(d + 'T00:00:00').toLocaleDateString('pt-BR');
}

// =============================================
// VISAO GERAL (monitoramento sem atleta_id)
// =============================================

async function loadResumo() {
    const loading = document.getElementById('monitor-loading');
    const cards = document.getElementById('monitor-cards');
    const content = document.getElementById('monitor-content');
    if (!loading) return;

    try {
        const resp = await fetch(`${API_BASE}/monitoramento/resumo.php`);
        const data = await resp.json();
        loading.classList.add('hidden');

        if (!data.success) return;

        const r = data.resumo;
        document.getElementById('card-atletas').textContent = r.total_atletas;
        document.getElementById('card-aderencia').textContent = r.aderencia_geral + '%';
        document.getElementById('card-pse').textContent = r.pse_medio;
        document.getElementById('card-alertas').textContent = r.alertas;

        // Cor PSE
        const pseEl = document.getElementById('card-pse');
        if (r.pse_medio >= 8) pseEl.classList.add('text-red-600');
        else if (r.pse_medio >= 6) pseEl.classList.add('text-yellow-600');
        else pseEl.classList.add('text-green-600');

        cards.classList.remove('hidden');
        content.classList.remove('hidden');

        // Aderencia por atleta
        const adList = document.getElementById('aderencia-list');
        const adEmpty = document.getElementById('aderencia-empty');

        if (data.atletas_aderencia?.length) {
            adList.innerHTML = data.atletas_aderencia.map(a => {
                const barColor = a.aderencia >= 70 ? 'bg-green-500' : a.aderencia >= 40 ? 'bg-yellow-500' : 'bg-red-500';
                return `
                <a href="?page=monitoramento&atleta_id=${a.usuario_id}" class="block hover:bg-gray-50 rounded-lg p-2 transition-colors">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-900">${escapeHtml(a.nome)}</span>
                        <span class="text-xs font-semibold ${a.aderencia >= 70 ? 'text-green-600' : a.aderencia >= 40 ? 'text-yellow-600' : 'text-red-600'}">${a.aderencia}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="${barColor} h-2 rounded-full transition-all" style="width: ${a.aderencia}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">${a.treinos_realizados}/${a.treinos_previstos} treinos</p>
                </a>`;
            }).join('');
        } else {
            adEmpty.classList.remove('hidden');
        }

        // Alertas recentes
        const alList = document.getElementById('alertas-list');
        const alEmpty = document.getElementById('alertas-empty');

        if (data.alertas_recentes?.length) {
            alList.innerHTML = data.alertas_recentes.map(a => {
                const motivos = [];
                if ((a.percepcao_esforco ?? 0) >= 9) motivos.push(`PSE ${a.percepcao_esforco}/10`);
                if (a.mal_estar_observado === 'sim') motivos.push('Mal-estar');
                if ((a.glicemia_pre_treino ?? 0) > 250) motivos.push(`Glicemia pre: ${a.glicemia_pre_treino}`);
                if ((a.glicemia_pos_treino ?? 0) > 250) motivos.push(`Glicemia pos: ${a.glicemia_pos_treino}`);

                return `
                <div class="flex items-start gap-3 p-2 bg-red-50 rounded-lg">
                    <div class="w-2 h-2 bg-red-500 rounded-full mt-1.5 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">${escapeHtml(a.atleta_nome)}</p>
                        <p class="text-xs text-red-600 font-medium">${motivos.join(' | ')}</p>
                        <p class="text-xs text-gray-400">${formatDate(a.data_realizado)} ${a.treino_nome ? '- ' + escapeHtml(a.treino_nome) : ''}</p>
                        ${a.sinais_alerta_observados ? `<p class="text-xs text-gray-500 mt-1">${escapeHtml(a.sinais_alerta_observados)}</p>` : ''}
                    </div>
                </div>`;
            }).join('');
        } else {
            alEmpty.classList.remove('hidden');
        }
    } catch (err) {
        loading.classList.add('hidden');
        console.error('Erro ao carregar monitoramento:', err);
    }
}

// =============================================
// DETALHE DO ATLETA
// =============================================

let pseChart = null;

async function loadAtletaDetalhe(periodo = 30) {
    if (typeof ATLETA_DETALHE_ID === 'undefined') return;

    // Atualizar botoes de periodo
    document.querySelectorAll('.periodo-btn').forEach(btn => {
        btn.classList.remove('bg-purple-600', 'text-white');
        btn.classList.add('border', 'border-gray-300', 'text-gray-600');
    });
    const activeBtn = document.querySelector(`.periodo-btn[onclick="loadAtletaDetalhe(${periodo})"]`);
    if (activeBtn) {
        activeBtn.classList.add('bg-purple-600', 'text-white');
        activeBtn.classList.remove('border', 'border-gray-300', 'text-gray-600');
    }

    try {
        const resp = await fetch(`${API_BASE}/monitoramento/atleta.php?atleta_id=${ATLETA_DETALHE_ID}&periodo=${periodo}`);
        const data = await resp.json();

        if (!data.success) return;

        document.getElementById('atleta-detalhe-nome').textContent = data.atleta.nome_completo;

        const m = data.metricas;
        document.getElementById('m-realizados').textContent = m.total_realizados;
        document.getElementById('m-aderencia').textContent = m.aderencia + '%';
        document.getElementById('m-pse').textContent = m.pse_medio;
        document.getElementById('m-duracao').textContent = m.duracao_media + 'min';
        document.getElementById('m-alertas').textContent = m.total_alertas;

        // Grafico PSE
        renderPseChart(data.pse_grafico);

        // Historico
        const histList = document.getElementById('historico-list');
        const histEmpty = document.getElementById('historico-empty');

        if (data.historico?.length) {
            histEmpty.classList.add('hidden');
            histList.innerHTML = data.historico.map(h => {
                const pseColor = (h.percepcao_esforco ?? 0) >= 9 ? 'text-red-600' : (h.percepcao_esforco ?? 0) >= 7 ? 'text-yellow-600' : 'text-green-600';
                const hasAlert = (h.percepcao_esforco ?? 0) >= 9 || h.mal_estar_observado === 'sim';

                return `
                <div class="flex items-start justify-between p-3 ${hasAlert ? 'bg-red-50' : 'bg-gray-50'} rounded-lg">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-gray-900">${formatDate(h.data_realizado)}</span>
                            ${h.treino_nome ? `<span class="text-xs text-gray-400">- ${escapeHtml(h.treino_nome)}</span>` : ''}
                            ${h.fonte === 'assessor' ? '<span class="text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded">Assessor</span>' : ''}
                        </div>
                        <div class="flex flex-wrap gap-3 text-xs text-gray-500">
                            ${h.percepcao_esforco !== null ? `<span>PSE: <strong class="${pseColor}">${h.percepcao_esforco}/10</strong></span>` : ''}
                            ${h.duracao_minutos ? `<span>Duracao: ${h.duracao_minutos}min</span>` : ''}
                            ${h.mal_estar_observado === 'sim' ? '<span class="text-red-600 font-medium">Mal-estar</span>' : ''}
                        </div>
                        ${h.observacoes ? `<p class="text-xs text-gray-500 mt-1">${escapeHtml(h.observacoes)}</p>` : ''}
                        ${h.feedback_assessor ? `<p class="text-xs text-purple-600 mt-1"><strong>Feedback:</strong> ${escapeHtml(h.feedback_assessor)}</p>` : ''}
                    </div>
                    <button onclick="abrirModalFeedback(${h.id})" class="text-xs px-2 py-1 bg-purple-50 text-purple-600 hover:bg-purple-100 rounded transition-colors flex-shrink-0 ml-2" title="Feedback">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </button>
                </div>`;
            }).join('');
        } else {
            histEmpty.classList.remove('hidden');
            histList.innerHTML = '';
        }
    } catch (err) {
        console.error('Erro ao carregar detalhe:', err);
    }
}

function renderPseChart(dados) {
    const canvas = document.getElementById('pse-chart');
    if (!canvas) return;

    if (pseChart) pseChart.destroy();

    if (!dados?.length) {
        canvas.parentElement.innerHTML = '<p class="text-center text-sm text-gray-400 py-8">Sem dados de PSE para exibir</p>';
        return;
    }

    const ctx = canvas.getContext('2d');
    pseChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dados.map(d => formatDate(d.data)),
            datasets: [{
                label: 'PSE',
                data: dados.map(d => d.pse),
                borderColor: '#7C3AED',
                backgroundColor: 'rgba(124, 58, 237, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: dados.map(d => d.pse >= 9 ? '#DC2626' : d.pse >= 7 ? '#D97706' : '#059669')
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { min: 0, max: 10, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

// --- Feedback ---

function abrirModalFeedback(progressoId) {
    document.getElementById('feedback-progresso-id').value = progressoId;
    document.getElementById('feedback-texto').value = '';
    document.getElementById('modal-feedback').classList.remove('hidden');
}

function fecharModalFeedback() {
    document.getElementById('modal-feedback').classList.add('hidden');
}

async function enviarFeedback() {
    const btn = document.getElementById('btn-feedback');
    const progressoId = document.getElementById('feedback-progresso-id').value;
    const texto = document.getElementById('feedback-texto').value.trim();

    if (!texto) {
        alert('Digite um feedback');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Enviando...';

    try {
        const resp = await fetch(`${API_BASE}/progresso/feedback.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ progresso_id: parseInt(progressoId), feedback: texto })
        });
        const data = await resp.json();

        if (data.success) {
            fecharModalFeedback();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Feedback enviado!', timer: 1500, showConfirmButton: false });
            }
            setTimeout(() => loadAtletaDetalhe(), 1500);
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert('Erro de conexao');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Enviar';
    }
}

// =============================================
// INICIALIZACAO
// =============================================

document.addEventListener('DOMContentLoaded', () => {
    if (typeof ATLETA_DETALHE_ID !== 'undefined' && ATLETA_DETALHE_ID > 0) {
        loadAtletaDetalhe(30);
    } else if (document.getElementById('monitor-loading')) {
        loadResumo();
    }
});
