/**
 * Sistema de Logs e Monitoramento MovAmazon
 * Monitora o funcionamento do sistema e gera logs detalhados
 */

class SistemaLogs {
    constructor() {
        this.logs = [];
        this.maxLogs = 1000;
        this.niveis = {
            DEBUG: 0,
            INFO: 1,
            WARN: 2,
            ERROR: 3,
            CRITICAL: 4
        };
        this.nivelAtual = this.niveis.INFO;
        this.inicializar();
    }

    inicializar() {
        console.log('📝 Sistema de Logs inicializado');

        // Interceptar erros globais
        this.interceptarErros();

        // Interceptar logs do console
        this.interceptarConsole();

        // Monitorar performance
        this.monitorarPerformance();

        // Monitorar erros de rede
        this.monitorarRede();
    }

    interceptarErros() {
        // Interceptar erros JavaScript
        window.addEventListener('error', (event) => {
            this.log('ERROR', 'Erro JavaScript', {
                mensagem: event.message,
                arquivo: event.filename,
                linha: event.lineno,
                coluna: event.colno,
                stack: event.error ? .stack
            });
        });

        // Interceptar promessas rejeitadas
        window.addEventListener('unhandledrejection', (event) => {
            this.log('ERROR', 'Promessa Rejeitada', {
                motivo: event.reason,
                promessa: event.promise
            });
        });
    }

    interceptarConsole() {
        const consoleOriginal = {
            log: console.log,
            warn: console.warn,
            error: console.error,
            info: console.info
        };

        // Sobrescrever métodos do console
        console.log = (...args) => {
            this.log('INFO', 'Console Log', {
                args
            });
            consoleOriginal.log.apply(console, args);
        };

        console.warn = (...args) => {
            this.log('WARN', 'Console Warn', {
                args
            });
            consoleOriginal.warn.apply(console, args);
        };

        console.error = (...args) => {
            this.log('ERROR', 'Console Error', {
                args
            });
            consoleOriginal.error.apply(console, args);
        };

        console.info = (...args) => {
            this.log('INFO', 'Console Info', {
                args
            });
            consoleOriginal.info.apply(console, args);
        };
    }

    monitorarPerformance() {
        // Monitorar tempo de carregamento da página
        window.addEventListener('load', () => {
            const performance = window.performance;
            if (performance) {
                const timing = performance.timing;
                const tempoCarregamento = timing.loadEventEnd - timing.navigationStart;

                this.log('INFO', 'Performance - Página Carregada', {
                    tempoCarregamento: `${tempoCarregamento}ms`,
                    domContentLoaded: timing.domContentLoadedEventEnd - timing.navigationStart,
                    firstPaint: performance.getEntriesByType('paint')[0] ? .startTime
                });
            }
        });

        // Monitorar mudanças de performance
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (entry.entryType === 'navigation') {
                        this.log('INFO', 'Performance - Navegação', {
                            tipo: entry.type,
                            tempo: `${entry.duration}ms`
                        });
                    }
                });
            });

            try {
                observer.observe({
                    entryTypes: ['navigation']
                });
            } catch (e) {
                this.log('WARN', 'Performance Observer não suportado', {
                    erro: e.message
                });
            }
        }
    }

    monitorarRede() {
        // Interceptar requisições fetch
        const fetchOriginal = window.fetch;
        window.fetch = async (...args) => {
            const inicio = performance.now();
            const url = args[0];

            try {
                const response = await fetchOriginal(...args);
                const fim = performance.now();
                const tempo = fim - inicio;

                this.log('INFO', 'API Request', {
                    url: typeof url === 'string' ? url : url.url,
                    metodo: args[1] ? .method || 'GET',
                    status: response.status,
                    tempo: `${tempo.toFixed(2)}ms`,
                    sucesso: response.ok
                });

                return response;
            } catch (error) {
                const fim = performance.now();
                const tempo = fim - inicio;

                this.log('ERROR', 'API Request Failed', {
                    url: typeof url === 'string' ? url : url.url,
                    metodo: args[1] ? .method || 'GET',
                    erro: error.message,
                    tempo: `${tempo.toFixed(2)}ms`
                });

                throw error;
            }
        };
    }

    log(nivel, mensagem, dados = {}) {
        if (this.niveis[nivel] < this.nivelAtual) return;

        const logEntry = {
            timestamp: new Date().toISOString(),
            nivel,
            mensagem,
            dados,
            url: window.location.href,
            userAgent: navigator.userAgent,
            sessao: this.gerarIdSessao()
        };

        this.logs.push(logEntry);

        // Manter apenas os últimos logs
        if (this.logs.length > this.maxLogs) {
            this.logs.shift();
        }

        // Log no console com formatação
        this.logParaConsole(logEntry);

        // Enviar para servidor se for erro crítico
        if (nivel === 'CRITICAL') {
            this.enviarLogParaServidor(logEntry);
        }
    }

    logParaConsole(logEntry) {
        const cores = {
            DEBUG: 'color: #6B7280',
            INFO: 'color: #3B82F6',
            WARN: 'color: #F59E0B',
            ERROR: 'color: #EF4444',
            CRITICAL: 'color: #DC2626'
        };

        const estilo = `font-weight: bold; ${cores[logEntry.nivel] || ''}`;

        console.group(`%c${logEntry.nivel} ${logEntry.mensagem}`, estilo);
        console.log('Timestamp:', logEntry.timestamp);
        console.log('URL:', logEntry.url);
        console.log('Dados:', logEntry.dados);
        console.groupEnd();
    }

    gerarIdSessao() {
        if (!this.idSessao) {
            this.idSessao = 'sessao_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }
        return this.idSessao;
    }

    async enviarLogParaServidor(logEntry) {
        try {
            await fetch('/api/logs/error.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(logEntry)
            });
        } catch (error) {
            console.error('Erro ao enviar log para servidor:', error);
        }
    }

    // Métodos públicos para logging
    debug(mensagem, dados = {}) {
        this.log('DEBUG', mensagem, dados);
    }

    info(mensagem, dados = {}) {
        this.log('INFO', mensagem, dados);
    }

    warn(mensagem, dados = {}) {
        this.log('WARN', mensagem, dados);
    }

    error(mensagem, dados = {}) {
        this.log('ERROR', mensagem, dados);
    }

    critical(mensagem, dados = {}) {
        this.log('CRITICAL', mensagem, dados);
    }

    // Obter logs filtrados
    obterLogs(filtros = {}) {
        let logsFiltrados = [...this.logs];

        if (filtros.nivel) {
            logsFiltrados = logsFiltrados.filter(log => log.nivel === filtros.nivel);
        }

        if (filtros.mensagem) {
            logsFiltrados = logsFiltrados.filter(log =>
                log.mensagem.toLowerCase().includes(filtros.mensagem.toLowerCase())
            );
        }

        if (filtros.dataInicio) {
            logsFiltrados = logsFiltrados.filter(log =>
                new Date(log.timestamp) >= new Date(filtros.dataInicio)
            );
        }

        if (filtros.dataFim) {
            logsFiltrados = logsFiltrados.filter(log =>
                new Date(log.timestamp) <= new Date(filtros.dataFim)
            );
        }

        return logsFiltrados;
    }

    // Exportar logs
    exportarLogs(formato = 'json') {
        if (formato === 'json') {
            const dados = JSON.stringify(this.logs, null, 2);
            const blob = new Blob([dados], {
                type: 'application/json'
            });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = `logs_movamazon_${new Date().toISOString().split('T')[0]}.json`;
            a.click();

            URL.revokeObjectURL(url);
        } else if (formato === 'csv') {
            const csv = this.converterParaCSV();
            const blob = new Blob([csv], {
                type: 'text/csv'
            });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = `logs_movamazon_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();

            URL.revokeObjectURL(url);
        }
    }

    converterParaCSV() {
        if (this.logs.length === 0) return '';

        const headers = ['Timestamp', 'Nível', 'Mensagem', 'URL', 'Dados'];
        const linhas = [headers.join(',')];

        this.logs.forEach(log => {
            const linha = [
                log.timestamp,
                log.nivel,
                `"${log.mensagem}"`,
                log.url,
                `"${JSON.stringify(log.dados).replace(/"/g, '""')}"`
            ];
            linhas.push(linha.join(','));
        });

        return linhas.join('\n');
    }

    // Limpar logs antigos
    limparLogsAntigos(dias = 7) {
        const dataLimite = new Date();
        dataLimite.setDate(dataLimite.getDate() - dias);

        const logsAntigos = this.logs.filter(log =>
            new Date(log.timestamp) < dataLimite
        );

        this.logs = this.logs.filter(log =>
            new Date(log.timestamp) >= dataLimite
        );

        this.info('Logs antigos removidos', {
            logsRemovidos: logsAntigos.length,
            dataLimite: dataLimite.toISOString()
        });

        return logsAntigos.length;
    }

    // Estatísticas dos logs
    obterEstatisticas() {
        const estatisticas = {
            total: this.logs.length,
            porNivel: {},
            porMensagem: {},
            tempoMedio: 0
        };

        // Contar por nível
        this.logs.forEach(log => {
            estatisticas.porNivel[log.nivel] = (estatisticas.porNivel[log.nivel] || 0) + 1;
        });

        // Contar por mensagem
        this.logs.forEach(log => {
            estatisticas.porMensagem[log.mensagem] = (estatisticas.porMensagem[log.mensagem] || 0) + 1;
        });

        return estatisticas;
    }
}

// Inicializar sistema de logs quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    window.sistemaLogs = new SistemaLogs();

    // Log de inicialização
    window.sistemaLogs.info('Sistema MovAmazon inicializado', {
        versao: '1.0.0',
        ambiente: window.location.hostname === 'localhost' ? 'desenvolvimento' : 'producao',
        timestamp: new Date().toISOString()
    });
});

// Exportar para uso global
window.SistemaLogs = SistemaLogs;