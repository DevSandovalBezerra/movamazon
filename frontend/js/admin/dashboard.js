if (!window.API_BASE) {
    (function () {
        var path = window.location.pathname || '';
        var idx = path.indexOf('/frontend/');
        window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
    })();
}

function getApiUrl(endpoint) {
    const url = `${window.API_BASE}/api/${endpoint}`;
    return url;
}

async function carregarDashboard() {
    try {
        const response = await fetch(getApiUrl('admin/get_dashboard_data.php'), {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`Erro ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Erro ao carregar dados');
        }
        
        atualizarEstatisticas(data.stats);
        atualizarGraficos(data.graficos);
        atualizarDadosRecentes(data.recentes);
        
    } catch (error) {
        console.error('Erro ao carregar dashboard:', error);
        if (window.AdminUtils) {
            window.AdminUtils.handleApiError(error, 'Erro ao carregar dados do dashboard. Tente novamente.');
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao carregar dados do dashboard. Tente novamente.',
                confirmButtonText: 'OK'
            });
        } else {
            alert('Erro ao carregar dados do dashboard');
        }
    }
}

function atualizarEstatisticas(stats) {
    document.getElementById('stat-total-eventos').textContent = stats.eventos.total || 0;
    document.getElementById('stat-eventos-ativos').textContent = stats.eventos.ativos || 0;
    
    document.getElementById('stat-total-inscricoes').textContent = stats.inscricoes.total || 0;
    document.getElementById('stat-inscricoes-confirmadas').textContent = stats.inscricoes.confirmadas || 0;
    
    const receitaTotal = parseFloat(stats.receita.receita_total || 0);
    const receitaMes = parseFloat(stats.receita.receita_mes_atual || 0);
    document.getElementById('stat-receita-total').textContent = formatarMoeda(receitaTotal);
    document.getElementById('stat-receita-mes').textContent = formatarMoeda(receitaMes);
    
    document.getElementById('stat-total-organizadores').textContent = stats.organizadores.total || 0;
    document.getElementById('stat-organizadores-ativos').textContent = stats.organizadores.ativos || 0;
    
    document.getElementById('stat-total-participantes').textContent = stats.participantes.total || 0;
    document.getElementById('stat-participantes-ativos').textContent = stats.participantes.ativos || 0;
    
    document.getElementById('stat-pagamentos-aprovados').textContent = stats.pagamentos.aprovados || 0;
    document.getElementById('stat-pagamentos-pendentes').textContent = stats.pagamentos.pendentes || 0;
}

function atualizarGraficos(graficos) {
    criarGraficoReceitaMensal(graficos.receita_mensal || []);
    criarGraficoInscricoesMensal(graficos.inscricoes_mensal || []);
    criarGraficoDistEventos(graficos.dist_eventos || []);
    criarGraficoTopEventos(graficos.top_eventos || []);
}

function criarGraficoReceitaMensal(dados) {
    const canvas = document.getElementById('grafico-receita-mensal');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dados.map(d => d.mes),
            datasets: [{
                label: 'Receita (R$)',
                data: dados.map(d => d.valor),
                borderColor: '#0b4340',
                backgroundColor: 'rgba(11, 67, 64, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#f5c113',
                pointBorderColor: '#0b4340',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });
}

function criarGraficoInscricoesMensal(dados) {
    const canvas = document.getElementById('grafico-inscricoes-mensal');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dados.map(d => d.mes),
            datasets: [{
                label: 'Inscrições',
                data: dados.map(d => d.total),
                backgroundColor: '#0b4340',
                borderColor: '#1a5f5a',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

function criarGraficoDistEventos(dados) {
    const canvas = document.getElementById('grafico-dist-eventos');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    
    const cores = {
        'ativo': '#0b4340',
        'inativo': '#ad1f22',
        'rascunho': '#6b7280'
    };
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: dados.map(d => d.status || 'N/A'),
            datasets: [{
                data: dados.map(d => d.total),
                backgroundColor: dados.map(d => cores[d.status] || '#6b7280')
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function criarGraficoTopEventos(dados) {
    const canvas = document.getElementById('grafico-top-eventos');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dados.map(d => d.nome.length > 20 ? d.nome.substring(0, 20) + '...' : d.nome),
            datasets: [{
                label: 'Inscrições',
                data: dados.map(d => d.total_inscricoes || 0),
                backgroundColor: '#f5c113',
                borderColor: '#0b4340',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

function atualizarDadosRecentes(recentes) {
    renderizarEventosRecentes(recentes.eventos || []);
    renderizarInscricoesRecentes(recentes.inscricoes || []);
    renderizarPagamentosRecentes(recentes.pagamentos || []);
}

function renderizarEventosRecentes(eventos) {
    const container = document.getElementById('eventos-recentes-container');
    if (!container) return;
    
    if (eventos.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">Nenhum evento recente</p>';
        return;
    }
    
    container.innerHTML = eventos.map(evento => {
        const dataEvento = evento.data_realizacao || evento.data_inicio;
        const dataFormatada = dataEvento ? new Date(dataEvento).toLocaleDateString('pt-BR') : 'N/A';
        const imagem = evento.imagem && typeof window.getEventImageUrl === 'function'
            ? window.getEventImageUrl(evento.imagem) : (evento.imagem ? `../../assets/img/eventos/${evento.imagem}` : '../../assets/img/logo.png');
        
        return `
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <img src="${imagem}" alt="${evento.nome}" class="w-12 h-12 object-cover rounded-lg">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${escapeHtml(evento.nome)}</p>
                    <p class="text-xs text-gray-500">${dataFormatada}</p>
                    <p class="text-xs text-gray-600 mt-1">
                        <span class="font-semibold">${evento.total_inscricoes || 0}</span> inscrições
                    </p>
                </div>
            </div>
        `;
    }).join('');
}

function renderizarInscricoesRecentes(inscricoes) {
    const container = document.getElementById('inscricoes-recentes-container');
    if (!container) return;
    
    if (inscricoes.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">Nenhuma inscrição recente</p>';
        return;
    }
    
    container.innerHTML = inscricoes.map(inscricao => {
        const dataFormatada = inscricao.data_inscricao ? new Date(inscricao.data_inscricao).toLocaleDateString('pt-BR') : 'N/A';
        const statusClass = inscricao.status_pagamento === 'pago' ? 'admin-badge-success' : 
                           inscricao.status_pagamento === 'pendente' ? 'admin-badge-warning' : 
                           'admin-badge-info';
        
        return `
            <div class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${escapeHtml(inscricao.participante_nome)}</p>
                        <p class="text-xs text-gray-600 truncate">${escapeHtml(inscricao.evento_nome)}</p>
                    </div>
                    <span class="${statusClass} ml-2">${inscricao.status_pagamento || inscricao.status}</span>
                </div>
                <p class="text-xs text-gray-500">${dataFormatada}</p>
            </div>
        `;
    }).join('');
}

function renderizarPagamentosRecentes(pagamentos) {
    const container = document.getElementById('pagamentos-recentes-container');
    if (!container) return;
    
    if (pagamentos.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">Nenhum pagamento recente</p>';
        return;
    }
    
    container.innerHTML = pagamentos.map(pagamento => {
        const dataFormatada = pagamento.data_atualizacao ? new Date(pagamento.data_atualizacao).toLocaleDateString('pt-BR') : 'N/A';
        const valor = parseFloat(pagamento.transaction_amount || 0);
        const statusClass = pagamento.status === 'approved' ? 'admin-badge-success' : 
                           pagamento.status === 'pending' ? 'admin-badge-warning' : 
                           'admin-badge-danger';
        
        return `
            <div class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">${formatarMoeda(valor)}</p>
                        <p class="text-xs text-gray-600 truncate">${escapeHtml(pagamento.participante_nome || 'N/A')}</p>
                    </div>
                    <span class="${statusClass} ml-2">${pagamento.status}</span>
                </div>
                <p class="text-xs text-gray-500">${dataFormatada}</p>
            </div>
        `;
    }).join('');
}

function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', carregarDashboard);
} else {
    carregarDashboard();
}

