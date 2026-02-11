<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoramento do Sistema - MovAmazon</title>
    <link rel="stylesheet" href="../../assets/css/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <h1 class="text-2xl font-bold text-gray-900">Monitoramento do Sistema</h1>
                    <div class="flex space-x-3">
                        <button onclick="exportarLogs()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                            Exportar Logs
                        </button>
                        <button onclick="limparLogsAntigos()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm">
                            Limpar Logs Antigos
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Cards de Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total de Logs</p>
                            <p id="total-logs" class="text-2xl font-bold text-gray-900">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Logs INFO</p>
                            <p id="logs-info" class="text-2xl font-bold text-gray-900">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Logs WARN</p>
                            <p id="logs-warn" class="text-2xl font-bold text-gray-900">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Logs ERROR</p>
                            <p id="logs-error" class="text-2xl font-bold text-gray-900">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Logs por Nível</h3>
                    <canvas id="chartNiveis" width="400" height="200"></canvas>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Logs por Mensagem</h3>
                    <canvas id="chartMensagens" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Logs</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nível</label>
                        <select id="filtroNivel" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">Todos</option>
                            <option value="DEBUG">DEBUG</option>
                            <option value="INFO">INFO</option>
                            <option value="WARN">WARN</option>
                            <option value="ERROR">ERROR</option>
                            <option value="CRITICAL">CRITICAL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Início</label>
                        <input type="date" id="filtroDataInicio" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Fim</label>
                        <input type="date" id="filtroDataFim" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mensagem</label>
                        <input type="text" id="filtroMensagem" placeholder="Buscar mensagem..." class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                </div>
                <div class="mt-4">
                    <button onclick="aplicarFiltros()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
                        Aplicar Filtros
                    </button>
                    <button onclick="limparFiltros()" class="ml-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm">
                        Limpar Filtros
                    </button>
                </div>
            </div>

            <!-- Tabela de Logs -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Logs do Sistema</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mensagem</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dados</th>
                            </tr>
                        </thead>
                        <tbody id="logsTable" class="bg-white divide-y divide-gray-200">
                            <!-- Logs serão carregados aqui -->
                        </tbody>
                    </table>
                </div>
                <div id="loadingLogs" class="text-center py-8 text-gray-500">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2">Carregando logs...</p>
                </div>
                <div id="emptyLogs" class="text-center py-8 text-gray-500 hidden">
                    <p>Nenhum log encontrado</p>
                </div>
            </div>
        </main>
    </div>

    <script src="../../js/validacoes.js"></script>
    <script src="../../js/testes-fluxo.js"></script>
    <script src="../../js/sistema-logs.js"></script>
    <script>
        let logs = [];
        let logsFiltrados = [];
        let chartNiveis = null;
        let chartMensagens = null;

        // Inicializar quando o DOM estiver pronto
        document.addEventListener('DOMContentLoaded', function() {
            carregarLogs();
            atualizarEstatisticas();
            criarGraficos();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('filtroNivel').addEventListener('change', aplicarFiltros);
            document.getElementById('filtroDataInicio').addEventListener('change', aplicarFiltros);
            document.getElementById('filtroDataFim').addEventListener('change', aplicarFiltros);
            document.getElementById('filtroMensagem').addEventListener('input', aplicarFiltros);
        }

        function carregarLogs() {
            if (!window.sistemaLogs) {
                setTimeout(carregarLogs, 1000);
                return;
            }

            logs = window.sistemaLogs.obterLogs();
            logsFiltrados = [...logs];
            renderizarLogs();
            atualizarEstatisticas();
        }

        function renderizarLogs() {
            const tbody = document.getElementById('logsTable');
            const loadingMessage = document.getElementById('loadingLogs');
            const emptyMessage = document.getElementById('emptyLogs');

            loadingMessage.classList.add('hidden');

            if (logsFiltrados.length === 0) {
                emptyMessage.classList.remove('hidden');
                tbody.innerHTML = '';
                return;
            }

            emptyMessage.classList.add('hidden');

            tbody.innerHTML = logsFiltrados.slice(-100).map(log => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${new Date(log.timestamp).toLocaleString('pt-BR')}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                            log.nivel === 'DEBUG' ? 'bg-gray-100 text-gray-800' :
                            log.nivel === 'INFO' ? 'bg-blue-100 text-blue-800' :
                            log.nivel === 'WARN' ? 'bg-yellow-100 text-yellow-800' :
                            log.nivel === 'ERROR' ? 'bg-red-100 text-red-800' :
                            'bg-purple-100 text-purple-800'
                        }">
                            ${log.nivel}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        ${log.mensagem}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        ${log.url ? log.url.split('/').pop() : '-'}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <button onclick="mostrarDetalhesLog('${log.timestamp}')" class="text-blue-600 hover:text-blue-900">
                            Ver Detalhes
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function aplicarFiltros() {
            const nivel = document.getElementById('filtroNivel').value;
            const dataInicio = document.getElementById('filtroDataInicio').value;
            const dataFim = document.getElementById('filtroDataFim').value;
            const mensagem = document.getElementById('filtroMensagem').value;

            logsFiltrados = window.sistemaLogs.obterLogs({
                nivel: nivel || undefined,
                dataInicio: dataInicio || undefined,
                dataFim: dataFim || undefined,
                mensagem: mensagem || undefined
            });

            renderizarLogs();
        }

        function limparFiltros() {
            document.getElementById('filtroNivel').value = '';
            document.getElementById('filtroDataInicio').value = '';
            document.getElementById('filtroDataFim').value = '';
            document.getElementById('filtroMensagem').value = '';

            logsFiltrados = [...logs];
            renderizarLogs();
        }

        function atualizarEstatisticas() {
            if (!window.sistemaLogs) return;

            const stats = window.sistemaLogs.obterEstatisticas();

            document.getElementById('total-logs').textContent = stats.total;
            document.getElementById('logs-info').textContent = stats.porNivel.INFO || 0;
            document.getElementById('logs-warn').textContent = stats.porNivel.WARN || 0;
            document.getElementById('logs-error').textContent = (stats.porNivel.ERROR || 0) + (stats.porNivel.CRITICAL || 0);
        }

        function criarGraficos() {
            // Gráfico de níveis
            const ctxNiveis = document.getElementById('chartNiveis').getContext('2d');
            chartNiveis = new Chart(ctxNiveis, {
                type: 'doughnut',
                data: {
                    labels: ['DEBUG', 'INFO', 'WARN', 'ERROR', 'CRITICAL'],
                    datasets: [{
                        data: [0, 0, 0, 0, 0],
                        backgroundColor: [
                            '#6B7280',
                            '#3B82F6',
                            '#F59E0B',
                            '#EF4444',
                            '#DC2626'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Gráfico de mensagens
            const ctxMensagens = document.getElementById('chartMensagens').getContext('2d');
            chartMensagens = new Chart(ctxMensagens, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Frequência',
                        data: [],
                        backgroundColor: '#3B82F6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            atualizarGraficos();
        }

        function atualizarGraficos() {
            if (!window.sistemaLogs) return;

            const stats = window.sistemaLogs.obterEstatisticas();

            // Atualizar gráfico de níveis
            chartNiveis.data.datasets[0].data = [
                stats.porNivel.DEBUG || 0,
                stats.porNivel.INFO || 0,
                stats.porNivel.WARN || 0,
                stats.porNivel.ERROR || 0,
                stats.porNivel.CRITICAL || 0
            ];
            chartNiveis.update();

            // Atualizar gráfico de mensagens (top 10)
            const mensagensOrdenadas = Object.entries(stats.porMensagem)
                .sort(([, a], [, b]) => b - a)
                .slice(0, 10);

            chartMensagens.data.labels = mensagensOrdenadas.map(([msg]) => msg.length > 20 ? msg.substring(0, 20) + '...' : msg);
            chartMensagens.data.datasets[0].data = mensagensOrdenadas.map(([, count]) => count);
            chartMensagens.update();
        }

        function mostrarDetalhesLog(timestamp) {
            const log = logs.find(l => l.timestamp === timestamp);
            if (!log) return;

            Swal.fire({
                title: 'Detalhes do Log',
                html: `
                    <div class="text-left text-sm">
                        <p><strong>Timestamp:</strong> ${new Date(log.timestamp).toLocaleString('pt-BR')}</p>
                        <p><strong>Nível:</strong> ${log.nivel}</p>
                        <p><strong>Mensagem:</strong> ${log.mensagem}</p>
                        <p><strong>URL:</strong> ${log.url}</p>
                        <p><strong>User Agent:</strong> ${log.userAgent}</p>
                        <p><strong>Sessão:</strong> ${log.sessao}</p>
                        <p><strong>Dados:</strong></p>
                        <pre class="text-xs bg-gray-100 p-2 rounded mt-2 overflow-auto">${JSON.stringify(log.dados, null, 2)}</pre>
                    </div>
                `,
                width: '600px',
                confirmButtonText: 'Fechar'
            });
        }

        function exportarLogs() {
            if (!window.sistemaLogs) return;

            Swal.fire({
                title: 'Exportar Logs',
                text: 'Escolha o formato de exportação:',
                showCancelButton: true,
                confirmButtonText: 'JSON',
                cancelButtonText: 'CSV',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.sistemaLogs.exportarLogs('json');
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    window.sistemaLogs.exportarLogs('csv');
                }
            });
        }

        function limparLogsAntigos() {
            Swal.fire({
                title: 'Limpar Logs Antigos',
                text: 'Quantos dias de logs antigos deseja remover?',
                input: 'number',
                inputValue: 7,
                inputMin: 1,
                inputMax: 30,
                showCancelButton: true,
                confirmButtonText: 'Limpar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && window.sistemaLogs) {
                    const dias = parseInt(result.value);
                    const logsRemovidos = window.sistemaLogs.limparLogsAntigos(dias);

                    Swal.fire({
                        icon: 'success',
                        title: 'Logs Removidos',
                        text: `${logsRemovidos} logs antigos foram removidos.`
                    });

                    carregarLogs();
                    atualizarGraficos();
                }
            });
        }

        // Atualizar dados a cada 5 segundos
        setInterval(() => {
            if (window.sistemaLogs) {
                carregarLogs();
                atualizarGraficos();
            }
        }, 5000);
    </script>
</body>

</html>
