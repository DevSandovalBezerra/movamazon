if (window.getApiBase) { window.getApiBase(); }
let chartModalidades = null;
let chartReceita = null;
let chartTendencia = null;
let chartStatus = null;
let chartFormasPagamento = null;

const coresModalidades = [
    'rgba(59, 130, 246, 0.8)',   // azul
    'rgba(16, 185, 129, 0.8)',   // verde
    'rgba(245, 158, 11, 0.8)',   // amarelo
    'rgba(239, 68, 68, 0.8)',    // vermelho
    'rgba(139, 92, 246, 0.8)',   // roxo
    'rgba(236, 72, 153, 0.8)',   // rosa
    'rgba(20, 184, 166, 0.8)',   // turquesa
    'rgba(251, 146, 60, 0.8)',   // laranja
];

const coresBordasModalidades = [
    'rgba(59, 130, 246, 1)',
    'rgba(16, 185, 129, 1)',
    'rgba(245, 158, 11, 1)',
    'rgba(239, 68, 68, 1)',
    'rgba(139, 92, 246, 1)',
    'rgba(236, 72, 153, 1)',
    'rgba(20, 184, 166, 1)',
    'rgba(251, 146, 60, 1)',
];

export async function carregarGraficos() {
    console.log('📊 Iniciando carregamento de gráficos...');
    
    try {
        // Verificar se os elementos existem antes de carregar
        const canvasTendencia = document.getElementById('canvas-tendencia');
        const canvasReceita = document.getElementById('canvas-receita');
        const canvasStatus = document.getElementById('canvas-status');
        const canvasModalidades = document.getElementById('canvas-modalidades');
        const canvasFormasPagamento = document.getElementById('canvas-formas-pagamento');
        
        if (!canvasTendencia && !canvasReceita && !canvasStatus && !canvasModalidades && !canvasFormasPagamento) {
            console.log(' Elementos dos gráficos ainda não estão prontos, aguardando...');
            setTimeout(() => carregarGraficos(), 200);
            return;
        }
        
        const response = await fetch((window.API_BASE || '/api') + '/organizador/get_dashboard_charts.php');
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Erro ao carregar dados dos gráficos');
        }
        
        console.log('... Dados dos gráficos carregados:', data);
        
        // Criar gráficos de forma assíncrona para não travar
        requestAnimationFrame(() => {
            criarGraficoTendencia(data.receitaPorPeriodo || []);
            criarGraficoReceita(data.receitaPorPeriodo || []);
            criarGraficoStatus(data.distribuicaoPorStatus || {});
            criarGraficoModalidades(data.inscricoesPorModalidade || []);
            criarGraficoFormasPagamento(data.formasPagamento || []);
        });
        
    } catch (error) {
        console.error(' Erro ao carregar gráficos:', error);
        mostrarErroGraficos(error.message);
    }
}

function criarGraficoTendencia(dados) {
    const canvas = document.getElementById('canvas-tendencia');
    const container = document.getElementById('grafico-tendencia');
    
    if (!canvas) {
        console.error(' Canvas para gráfico de tendência não encontrado');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    if (chartTendencia) {
        chartTendencia.destroy();
    }
    
    if (!dados || dados.length === 0) {
        if (container) {
            container.innerHTML = '<p class="text-xs sm:text-sm text-center text-gray-500">Nenhum dado disponível</p>';
        }
        return;
    }
    
    const labels = dados.map(item => {
        const data = new Date(item.dia);
        return data.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    });
    
    const confirmadas = dados.map(item => parseInt(item.inscricoes_confirmadas || 0));
    const pendentes = dados.map(item => parseInt(item.inscricoes_pendentes || 0));
    const canceladas = dados.map(item => parseInt(item.inscricoes_canceladas || 0));
    
    canvas.style.display = 'block';
    if (container) {
        container.style.display = 'none';
    }
    
    chartTendencia = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Confirmadas',
                    data: confirmadas,
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Pendentes',
                    data: pendentes,
                    borderColor: 'rgba(245, 158, 11, 1)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Canceladas',
                    data: canceladas,
                    borderColor: 'rgba(239, 68, 68, 1)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderDash: [5, 5],
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantidade de Inscrições'
                    }
                }
            }
        }
    });
    
    console.log('... Gráfico de tendência criado');
}

function criarGraficoModalidades(dados) {
    const canvas = document.getElementById('canvas-modalidades');
    const container = document.getElementById('grafico-modalidades');
    
    if (!canvas) {
        console.error(' Canvas para gráfico de modalidades não encontrado');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    if (chartModalidades) {
        chartModalidades.destroy();
    }
    
    if (!dados || dados.length === 0) {
        if (container) {
            container.innerHTML = '<p class="text-xs sm:text-sm text-center text-gray-500">Nenhuma inscrição por modalidade</p>';
        }
        return;
    }
    
    const labels = dados.map(item => item.nome);
    const confirmadas = dados.map(item => parseInt(item.confirmadas || 0));
    const pendentes = dados.map(item => parseInt(item.pendentes_pagamento || 0));
    const canceladas = dados.map(item => parseInt(item.canceladas || 0));
    
    canvas.style.display = 'block';
    if (container) {
        container.style.display = 'none';
    }
    
    chartModalidades = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Confirmadas',
                    data: confirmadas,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Pendentes',
                    data: pendentes,
                    backgroundColor: 'rgba(245, 158, 11, 0.8)',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Canceladas',
                    data: canceladas,
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                x: {
                    stacked: true,
                    beginAtZero: true
                },
                y: {
                    stacked: true
                }
            }
        }
    });
    
    console.log('... Gráfico de modalidades criado');
}

function criarGraficoReceita(dados) {
    const canvas = document.getElementById('canvas-receita');
    const container = document.getElementById('grafico-receita');
    
    if (!canvas) {
        console.error(' Canvas para gráfico de receita não encontrado');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    if (chartReceita) {
        chartReceita.destroy();
    }
    
    if (!dados || dados.length === 0) {
        if (container) {
            container.innerHTML = '<p class="text-xs sm:text-sm text-center text-gray-500">Nenhum dado de receita disponível</p>';
        }
        return;
    }
    
    const labels = dados.map(item => {
        const data = new Date(item.dia);
        return data.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    });
    
    const receitaConfirmada = dados.map(item => parseFloat(item.receita_confirmada || 0));
    const receitaPendente = dados.map(item => parseFloat(item.receita_pendente || 0));
    
    canvas.style.display = 'block';
    if (container) {
        container.style.display = 'none';
    }
    
    chartReceita = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Receita Confirmada',
                    data: receitaConfirmada,
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.3)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Receita Pendente',
                    data: receitaPendente,
                    borderColor: 'rgba(245, 158, 11, 1)',
                    backgroundColor: 'rgba(245, 158, 11, 0.2)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('pt-BR', {
                                    style: 'currency',
                                    currency: 'BRL'
                                }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    },
                    title: {
                        display: true,
                        text: 'Receita (R$)'
                    }
                }
            }
        }
    });
    
    console.log('... Gráfico de receita criado');
}

function criarGraficoStatus(dados) {
    const canvas = document.getElementById('canvas-status');
    const container = document.getElementById('grafico-status');
    
    if (!canvas) {
        console.error(' Canvas para gráfico de status não encontrado');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    if (chartStatus) {
        chartStatus.destroy();
    }
    
    if (!dados || Object.keys(dados).length === 0) {
        if (container) {
            container.innerHTML = '<p class="text-xs sm:text-sm text-center text-gray-500">Nenhum dado disponível</p>';
        }
        return;
    }
    
    const labels = ['Confirmadas e Pagas', 'Confirmadas Pendentes', 'Pendentes de Confirmação', 'Canceladas'];
    const valores = [
        parseInt(dados.confirmadas_pagas || 0),
        parseInt(dados.confirmadas_pendentes || 0),
        parseInt(dados.pendentes_confirmacao || 0),
        parseInt(dados.canceladas || 0)
    ];
    
    const cores = [
        'rgba(16, 185, 129, 0.8)',
        'rgba(245, 158, 11, 0.8)',
        'rgba(156, 163, 175, 0.8)',
        'rgba(239, 68, 68, 0.8)'
    ];
    
    canvas.style.display = 'block';
    if (container) {
        container.style.display = 'none';
    }
    
    chartStatus = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                label: 'Inscrições',
                data: valores,
                backgroundColor: cores,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    
    console.log('... Gráfico de status criado');
}

function criarGraficoFormasPagamento(dados) {
    const canvas = document.getElementById('canvas-formas-pagamento');
    const container = document.getElementById('grafico-formas-pagamento');
    
    if (!canvas) {
        console.error(' Canvas para gráfico de formas de pagamento não encontrado');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    if (chartFormasPagamento) {
        chartFormasPagamento.destroy();
    }
    
    if (!dados || dados.length === 0) {
        if (container) {
            container.innerHTML = '<p class="text-xs sm:text-sm text-center text-gray-500">Nenhum dado disponível</p>';
        }
        return;
    }
    
    const labels = dados.map(item => {
        const forma = item.forma_pagamento || 'Outros';
        return forma.charAt(0).toUpperCase() + forma.slice(1);
    });
    const valores = dados.map(item => parseInt(item.total || 0));
    const receitas = dados.map(item => parseFloat(item.valor_total || 0));
    
    canvas.style.display = 'block';
    if (container) {
        container.style.display = 'none';
    }
    
    chartFormasPagamento = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Quantidade',
                data: valores,
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Receita (R$)',
                data: receitas,
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (label.includes('Receita')) {
                                    label += new Intl.NumberFormat('pt-BR', {
                                        style: 'currency',
                                        currency: 'BRL'
                                    }).format(context.parsed.y);
                                } else {
                                    label += context.parsed.y;
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantidade'
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    },
                    title: {
                        display: true,
                        text: 'Receita (R$)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
    
    console.log('... Gráfico de formas de pagamento criado');
}


function mostrarErroGraficos(mensagem) {
    console.error(' Erro nos gráficos:', mensagem);
    
    const containers = [
        'grafico-modalidades',
        'grafico-receita',
        'grafico-tendencia',
        'grafico-status',
        'grafico-formas-pagamento'
    ];
    
    containers.forEach(containerId => {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <p class="text-gray-500 text-sm">Erro ao carregar gráfico: ${mensagem}</p>
                </div>
            `;
        }
    });
}

export function destruirGraficos() {
    if (chartModalidades) {
        chartModalidades.destroy();
        chartModalidades = null;
    }
    if (chartReceita) {
        chartReceita.destroy();
        chartReceita = null;
    }
    if (chartTendencia) {
        chartTendencia.destroy();
        chartTendencia = null;
    }
    if (chartStatus) {
        chartStatus.destroy();
        chartStatus = null;
    }
    if (chartFormasPagamento) {
        chartFormasPagamento.destroy();
        chartFormasPagamento = null;
    }
}
