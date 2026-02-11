(() => {
    if (!document.getElementById('logs-container')) {
        return;
    }

    if (!window.API_BASE) {
        const path = window.location.pathname || '';
        const idx = path.indexOf('/frontend/');
        window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
    }

    const api = (endpoint, params = '') => {
        const sep = params ? `?${params}` : '';
        return `${window.API_BASE}/api/${endpoint}${sep}`;
    };

    const state = {
        logs: [],
        stats: {
            total_errors: 0,
            total_warnings: 0,
            total_info: 0,
            total_success: 0
        },
        filters: {
            nivel: '',
            acao: '',
            inscricao_id: '',
            busca: '',
            data_inicio: '',
            data_fim: '',
            periodo: ''
        },
        pagination: {
            page: 1,
            perPage: 20,
            total: 0,
            totalPages: 0
        },
        acoes: []
    };

    const els = {
        container: document.getElementById('logs-container'),
        loading: document.getElementById('logs-loading'),
        empty: document.getElementById('logs-empty'),
        pagination: document.getElementById('logs-pagination'),
        statErrors: document.getElementById('stat-errors'),
        statWarnings: document.getElementById('stat-warnings'),
        statInfo: document.getElementById('stat-info'),
        statSuccess: document.getElementById('stat-success'),
        filtroNivel: document.getElementById('filtro-nivel'),
        filtroAcao: document.getElementById('filtro-acao'),
        filtroBusca: document.getElementById('filtro-busca'),
        filtroPeriodo: document.getElementById('filtro-periodo'),
        filtroDataInicio: document.getElementById('filtro-data-inicio'),
        filtroDataFim: document.getElementById('filtro-data-fim'),
        filtroDataInicioWrapper: document.getElementById('filtro-data-inicio-wrapper'),
        filtroDataFimWrapper: document.getElementById('filtro-data-fim-wrapper'),
        filtroInscricaoId: document.getElementById('filtro-inscricao-id'),
        btnAplicarFiltros: document.getElementById('btn-aplicar-filtros'),
        btnLimparFiltros: document.getElementById('btn-limpar-filtros'),
        btnLimparLogs: document.getElementById('btn-limpar-logs'),
        modalDetalhes: document.getElementById('modal-detalhes-log'),
        modalDetalhesContent: document.getElementById('modal-detalhes-content'),
        modalLimpar: document.getElementById('modal-limpar-logs'),
        limpezaTipo: document.getElementById('limpeza-tipo'),
        limpezaOpcoes: document.getElementById('limpeza-opcoes'),
        limpezaPreview: document.getElementById('limpeza-preview'),
        limpezaPreviewContent: document.getElementById('limpeza-preview-content'),
        limpezaConfirmarTodos: document.getElementById('limpeza-confirmar-todos'),
        limpezaConfirmarCheckbox: document.getElementById('limpeza-confirmar-checkbox'),
        btnLimpezaPreview: document.getElementById('btn-limpeza-preview'),
        btnLimpezaExecutar: document.getElementById('btn-limpeza-executar')
    };

    // Usar função comum do AdminUtils se disponível
    const showMessage = (type, message) => {
        if (window.AdminUtils) {
            window.AdminUtils.showMessage(type, message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: type, title: message, timer: 3000, showConfirmButton: false });
        } else {
            alert(message);
        }
    };

    const toggleLoading = (show) => {
        if (els.loading) els.loading.classList.toggle('hidden', !show);
    };

    const toggleEmpty = (show) => {
        if (els.empty) els.empty.classList.toggle('hidden', !show);
    };

    const openModal = (id) => {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    };

    const closeModal = (id) => {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    };

    // Fechar modais ao clicar fora
    document.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', () => closeModal(el.getAttribute('data-close-modal')));
    });

    const getNivelColor = (nivel) => {
        const colors = {
            'ERROR': { bg: 'bg-red-50', border: 'border-red-500', text: 'text-red-700', icon: 'fa-exclamation-circle' },
            'WARNING': { bg: 'bg-yellow-50', border: 'border-yellow-500', text: 'text-yellow-700', icon: 'fa-exclamation-triangle' },
            'INFO': { bg: 'bg-blue-50', border: 'border-blue-500', text: 'text-blue-700', icon: 'fa-info-circle' },
            'SUCCESS': { bg: 'bg-green-50', border: 'border-green-500', text: 'text-green-700', icon: 'fa-check-circle' }
        };
        return colors[nivel] || colors['INFO'];
    };

    const formatDate = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    };

    const renderLogCard = (log) => {
        const color = getNivelColor(log.nivel);
        const mensagem = log.mensagem || 'Sem mensagem';
        const mensagemTruncada = mensagem.length > 150 ? mensagem.substring(0, 150) + '...' : mensagem;
        
        return `
            <div class="border rounded-lg p-4 ${color.bg} ${color.border} border-l-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <i class="fas ${color.icon} ${color.text} text-xl"></i>
                        <span class="font-semibold ${color.text}">${log.nivel}</span>
                        <span class="text-gray-500">|</span>
                        <span class="text-sm font-medium text-gray-700">${log.acao}</span>
                    </div>
                    <span class="text-xs text-gray-500">${log.created_at_formatted || formatDate(log.created_at)}</span>
                </div>
                
                <div class="mb-2">
                    <p class="text-sm text-gray-700">${mensagemTruncada}</p>
                </div>
                
                <div class="flex flex-wrap gap-2 text-xs text-gray-600 mb-2">
                    ${log.inscricao_id ? `<span><i class="fas fa-clipboard-list"></i> Inscrição: #${log.inscricao_id}</span>` : ''}
                    ${log.payment_id ? `<span><i class="fas fa-credit-card"></i> Pagamento: ${log.payment_id}</span>` : ''}
                    ${log.evento_nome ? `<span><i class="fas fa-calendar"></i> ${log.evento_nome}</span>` : ''}
                    ${log.usuario_nome ? `<span><i class="fas fa-user"></i> ${log.usuario_nome}</span>` : ''}
                    ${log.valor_total ? `<span><i class="fas fa-dollar-sign"></i> R$ ${parseFloat(log.valor_total).toFixed(2)}</span>` : ''}
                </div>
                
                <div class="flex gap-2 mt-3">
                    <button onclick="window.problemasInscricoes?.verDetalhes(${log.id})" class="btn-secondary text-xs py-1 px-3">
                        <i class="fas fa-eye w-3 h-3"></i> Ver Detalhes
                    </button>
                    ${log.inscricao_id ? `<a href="/frontend/paginas/organizador/inscricoes/detalhes.php?id=${log.inscricao_id}" target="_blank" class="btn-secondary text-xs py-1 px-3">
                        <i class="fas fa-external-link-alt w-3 h-3"></i> Ver Inscrição
                    </a>` : ''}
                </div>
            </div>
        `;
    };

    const renderPagination = () => {
        if (state.pagination.totalPages <= 1) {
            els.pagination.innerHTML = '';
            return;
        }

        const { page, totalPages, total } = state.pagination;
        const pages = [];
        
        // Primeira página
        if (page > 1) {
            pages.push(`<button onclick="window.problemasInscricoes?.irParaPagina(1)" class="px-3 py-1 border rounded hover:bg-gray-100">1</button>`);
            if (page > 2) pages.push('<span class="px-2">...</span>');
        }
        
        // Páginas ao redor da atual
        for (let i = Math.max(1, page - 1); i <= Math.min(totalPages, page + 1); i++) {
            pages.push(`<button onclick="window.problemasInscricoes?.irParaPagina(${i})" class="px-3 py-1 border rounded ${i === page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100'}">${i}</button>`);
        }
        
        // Última página
        if (page < totalPages) {
            if (page < totalPages - 1) pages.push('<span class="px-2">...</span>');
            pages.push(`<button onclick="window.problemasInscricoes?.irParaPagina(${totalPages})" class="px-3 py-1 border rounded hover:bg-gray-100">${totalPages}</button>`);
        }

        els.pagination.innerHTML = `
            <div class="text-sm text-gray-700">
                Mostrando ${((page - 1) * state.pagination.perPage) + 1} - ${Math.min(page * state.pagination.perPage, total)} de ${total} registros
            </div>
            <div class="flex gap-1">
                <button onclick="window.problemasInscricoes?.irParaPagina(${page - 1})" ${page === 1 ? 'disabled' : ''} class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                ${pages.join('')}
                <button onclick="window.problemasInscricoes?.irParaPagina(${page + 1})" ${page === totalPages ? 'disabled' : ''} class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        `;
    };

    const carregarLogs = async () => {
        toggleLoading(true);
        toggleEmpty(false);

        try {
            const params = new URLSearchParams({
                page: state.pagination.page,
                per_page: state.pagination.perPage
            });

            if (state.filters.nivel) params.append('nivel', state.filters.nivel);
            if (state.filters.acao) params.append('acao', state.filters.acao);
            if (state.filters.inscricao_id) params.append('inscricao_id', state.filters.inscricao_id);
            if (state.filters.busca) params.append('busca', state.filters.busca);
            if (state.filters.data_inicio) params.append('data_inicio', state.filters.data_inicio);
            if (state.filters.data_fim) params.append('data_fim', state.filters.data_fim);

            const response = await fetch(api('admin/logs_inscricoes/list.php', params.toString()), {
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`Erro ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Erro ao carregar logs');
            }

            state.logs = data.logs || [];
            state.pagination = data.pagination || state.pagination;
            state.stats = data.stats || state.stats;

            atualizarStats();
            renderLogs();
            renderPagination();

        } catch (error) {
            console.error('Erro ao carregar logs:', error);
            if (window.AdminUtils) {
                window.AdminUtils.handleApiError(error, 'Erro ao carregar logs');
            } else {
                showMessage('error', 'Erro ao carregar logs: ' + error.message);
            }
        } finally {
            toggleLoading(false);
        }
    };

    const atualizarStats = () => {
        if (els.statErrors) els.statErrors.textContent = state.stats.total_errors || 0;
        if (els.statWarnings) els.statWarnings.textContent = state.stats.total_warnings || 0;
        if (els.statInfo) els.statInfo.textContent = state.stats.total_info || 0;
        if (els.statSuccess) els.statSuccess.textContent = state.stats.total_success || 0;
    };

    const renderLogs = () => {
        if (!els.container) return;

        if (state.logs.length === 0) {
            toggleEmpty(true);
            els.container.innerHTML = '';
            return;
        }

        toggleEmpty(false);
        els.container.innerHTML = state.logs.map(renderLogCard).join('');
    };

    const verDetalhes = async (logId) => {
        try {
            const response = await fetch(api(`admin/logs_inscricoes/detail.php?id=${logId}`), {
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`Erro ${response.status}`);
            }

            const data = await response.json();

            if (!data.success || !data.log) {
                throw new Error('Log não encontrado');
            }

            const log = data.log;
            const color = getNivelColor(log.nivel);

            let html = `
                <div class="space-y-4">
                    <div class="border rounded-lg p-4 ${color.bg} ${color.border} border-l-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas ${color.icon} ${color.text} text-xl"></i>
                            <span class="font-semibold ${color.text} text-lg">${log.nivel}</span>
                            <span class="text-gray-500">|</span>
                            <span class="font-medium">${log.acao}</span>
                        </div>
                        <p class="text-sm text-gray-700 mb-2">${log.mensagem || 'Sem mensagem'}</p>
                        <p class="text-xs text-gray-500">${log.created_at_formatted || formatDate(log.created_at)}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-700 mb-2">Informações Básicas</h3>
                            <dl class="space-y-1 text-sm">
                                ${log.inscricao_id ? `<dt class="font-medium">Inscrição ID:</dt><dd class="text-gray-600">${log.inscricao_id}</dd>` : ''}
                                ${log.payment_id ? `<dt class="font-medium">Payment ID:</dt><dd class="text-gray-600">${log.payment_id}</dd>` : ''}
                                ${log.usuario_id ? `<dt class="font-medium">Usuário ID:</dt><dd class="text-gray-600">${log.usuario_id}</dd>` : ''}
                                ${log.evento_id ? `<dt class="font-medium">Evento ID:</dt><dd class="text-gray-600">${log.evento_id}</dd>` : ''}
                                ${log.modalidade_id ? `<dt class="font-medium">Modalidade ID:</dt><dd class="text-gray-600">${log.modalidade_id}</dd>` : ''}
                            </dl>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700 mb-2">Detalhes</h3>
                            <dl class="space-y-1 text-sm">
                                ${log.evento_nome ? `<dt class="font-medium">Evento:</dt><dd class="text-gray-600">${log.evento_nome}</dd>` : ''}
                                ${log.usuario_nome ? `<dt class="font-medium">Usuário:</dt><dd class="text-gray-600">${log.usuario_nome}</dd>` : ''}
                                ${log.usuario_email ? `<dt class="font-medium">Email:</dt><dd class="text-gray-600">${log.usuario_email}</dd>` : ''}
                                ${log.modalidade_nome ? `<dt class="font-medium">Modalidade:</dt><dd class="text-gray-600">${log.modalidade_nome}</dd>` : ''}
                                ${log.valor_total ? `<dt class="font-medium">Valor Total:</dt><dd class="text-gray-600">R$ ${parseFloat(log.valor_total).toFixed(2)}</dd>` : ''}
                                ${log.forma_pagamento ? `<dt class="font-medium">Forma Pagamento:</dt><dd class="text-gray-600">${log.forma_pagamento}</dd>` : ''}
                                ${log.status_pagamento ? `<dt class="font-medium">Status Pagamento:</dt><dd class="text-gray-600">${log.status_pagamento}</dd>` : ''}
                            </dl>
                        </div>
                    </div>

                    ${log.dados_contexto && Object.keys(log.dados_contexto).length > 0 ? `
                        <div>
                            <h3 class="font-semibold text-gray-700 mb-2">Dados de Contexto</h3>
                            <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto max-h-64">${JSON.stringify(log.dados_contexto, null, 2)}</pre>
                        </div>
                    ` : ''}

                    ${log.ip || log.user_agent ? `
                        <div>
                            <h3 class="font-semibold text-gray-700 mb-2">Informações Técnicas</h3>
                            <dl class="space-y-1 text-sm">
                                ${log.ip ? `<dt class="font-medium">IP:</dt><dd class="text-gray-600">${log.ip}</dd>` : ''}
                                ${log.user_agent ? `<dt class="font-medium">User Agent:</dt><dd class="text-gray-600 text-xs">${log.user_agent}</dd>` : ''}
                            </dl>
                        </div>
                    ` : ''}

                    ${log.logs_relacionados && log.logs_relacionados.length > 0 ? `
                        <div>
                            <h3 class="font-semibold text-gray-700 mb-2">Logs Relacionados (${log.logs_relacionados.length})</h3>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                ${log.logs_relacionados.map(rel => {
                                    const relColor = getNivelColor(rel.nivel);
                                    return `
                                        <div class="border rounded p-2 ${relColor.bg} ${relColor.border} border-l-2">
                                            <div class="flex items-center gap-2">
                                                <i class="fas ${relColor.icon} ${relColor.text}"></i>
                                                <span class="font-medium text-sm ${relColor.text}">${rel.nivel}</span>
                                                <span class="text-xs text-gray-500">${rel.acao}</span>
                                                <span class="text-xs text-gray-400 ml-auto">${rel.created_at_formatted || formatDate(rel.created_at)}</span>
                                            </div>
                                            <p class="text-xs text-gray-600 mt-1">${rel.mensagem || 'Sem mensagem'}</p>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;

            els.modalDetalhesContent.innerHTML = html;
            openModal('modal-detalhes-log');

        } catch (error) {
            console.error('Erro ao carregar detalhes:', error);
            if (window.AdminUtils) {
                window.AdminUtils.handleApiError(error, 'Erro ao carregar detalhes');
            } else {
                showMessage('error', 'Erro ao carregar detalhes: ' + error.message);
            }
        }
    };

    const aplicarFiltros = () => {
        state.filters.nivel = els.filtroNivel?.value || '';
        state.filters.acao = els.filtroAcao?.value || '';
        state.filters.inscricao_id = els.filtroInscricaoId?.value || '';
        state.filters.busca = els.filtroBusca?.value || '';
        
        const periodo = els.filtroPeriodo?.value || '';
        if (periodo === 'hoje') {
            const hoje = new Date().toISOString().split('T')[0];
            state.filters.data_inicio = hoje;
            state.filters.data_fim = hoje;
        } else if (periodo === '7dias') {
            const data = new Date();
            data.setDate(data.getDate() - 7);
            state.filters.data_inicio = data.toISOString().split('T')[0];
            state.filters.data_fim = new Date().toISOString().split('T')[0];
        } else if (periodo === '30dias') {
            const data = new Date();
            data.setDate(data.getDate() - 30);
            state.filters.data_inicio = data.toISOString().split('T')[0];
            state.filters.data_fim = new Date().toISOString().split('T')[0];
        } else if (periodo === 'custom') {
            state.filters.data_inicio = els.filtroDataInicio?.value || '';
            state.filters.data_fim = els.filtroDataFim?.value || '';
        } else {
            state.filters.data_inicio = '';
            state.filters.data_fim = '';
        }
        
        state.filters.periodo = periodo;
        state.pagination.page = 1;
        carregarLogs();
    };

    const limparFiltros = () => {
        if (els.filtroNivel) els.filtroNivel.value = '';
        if (els.filtroAcao) els.filtroAcao.value = '';
        if (els.filtroInscricaoId) els.filtroInscricaoId.value = '';
        if (els.filtroBusca) els.filtroBusca.value = '';
        if (els.filtroPeriodo) els.filtroPeriodo.value = '';
        if (els.filtroDataInicio) els.filtroDataInicio.value = '';
        if (els.filtroDataFim) els.filtroDataFim.value = '';
        
        state.filters = {
            nivel: '',
            acao: '',
            inscricao_id: '',
            busca: '',
            data_inicio: '',
            data_fim: '',
            periodo: ''
        };
        
        state.pagination.page = 1;
        carregarLogs();
    };

    const irParaPagina = (page) => {
        if (page < 1 || page > state.pagination.totalPages) return;
        state.pagination.page = page;
        carregarLogs();
    };

    // Limpeza de logs
    const atualizarOpcoesLimpeza = () => {
        const tipo = els.limpezaTipo?.value || '';
        const opcoes = els.limpezaOpcoes;
        
        // Esconder todas as opções
        opcoes?.querySelectorAll('[id^="opcao-"]').forEach(el => {
            el.classList.add('hidden');
        });
        
        els.limpezaOpcoes?.classList.add('hidden');
        els.limpezaPreview?.classList.add('hidden');
        els.limpezaConfirmarTodos?.classList.add('hidden');
        els.btnLimpezaExecutar?.setAttribute('disabled', 'disabled');
        
        if (!tipo) return;
        
        els.limpezaOpcoes?.classList.remove('hidden');
        
        switch (tipo) {
            case 'periodo':
                document.getElementById('opcao-periodo')?.classList.remove('hidden');
                break;
            case 'nivel':
                document.getElementById('opcao-nivel')?.classList.remove('hidden');
                break;
            case 'acao':
                document.getElementById('opcao-acao')?.classList.remove('hidden');
                break;
            case 'inscricao':
                document.getElementById('opcao-inscricao')?.classList.remove('hidden');
                break;
            case 'manter_ultimos':
                document.getElementById('opcao-manter-ultimos')?.classList.remove('hidden');
                break;
            case 'periodo_especifico':
                document.getElementById('opcao-periodo-especifico')?.classList.remove('hidden');
                break;
            case 'todos':
                els.limpezaConfirmarTodos?.classList.remove('hidden');
                break;
        }
    };

    const executarPreviewLimpeza = async () => {
        const tipo = els.limpezaTipo?.value;
        if (!tipo) {
            showMessage('warning', 'Selecione um tipo de limpeza');
            return;
        }

        const payload = { tipo, preview: true };

        switch (tipo) {
            case 'periodo':
                const dias = parseInt(document.getElementById('limpeza-periodo-dias')?.value);
                if (!dias || dias < 1) {
                    showMessage('warning', 'Informe o número de dias');
                    return;
                }
                payload.periodo_dias = dias;
                break;
            case 'nivel':
                payload.nivel = document.getElementById('limpeza-nivel')?.value;
                break;
            case 'acao':
                payload.acao = document.getElementById('limpeza-acao')?.value;
                if (!payload.acao) {
                    showMessage('warning', 'Informe a ação');
                    return;
                }
                break;
            case 'inscricao':
                const inscId = parseInt(document.getElementById('limpeza-inscricao-id')?.value);
                if (!inscId || inscId < 1) {
                    showMessage('warning', 'Informe o ID da inscrição');
                    return;
                }
                payload.inscricao_id = inscId;
                break;
            case 'manter_ultimos':
                const manter = parseInt(document.getElementById('limpeza-manter-ultimos')?.value);
                if (!manter || manter < 1) {
                    showMessage('warning', 'Informe o número de dias');
                    return;
                }
                payload.manter_ultimos_dias = manter;
                break;
            case 'periodo_especifico':
                payload.data_inicio = document.getElementById('limpeza-data-inicio')?.value;
                payload.data_fim = document.getElementById('limpeza-data-fim')?.value;
                if (!payload.data_inicio || !payload.data_fim) {
                    showMessage('warning', 'Informe data início e fim');
                    return;
                }
                break;
            case 'todos':
                if (!els.limpezaConfirmarCheckbox?.checked) {
                    showMessage('warning', 'Confirme que deseja deletar todos os logs');
                    return;
                }
                payload.confirmar = true;
                break;
        }

        try {
            const response = await fetch(api('admin/logs_inscricoes/delete.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Erro ao executar preview');
            }

            const { registros_afetados, detalhes } = data;
            
            let html = `<p class="font-semibold text-yellow-800 mb-2">Serão deletados: <strong>${registros_afetados}</strong> registro(s)</p>`;
            
            if (detalhes.por_nivel) {
                html += '<div class="mt-2 space-y-1">';
                Object.entries(detalhes.por_nivel).forEach(([nivel, total]) => {
                    html += `<p class="text-sm">• ${nivel}: ${total} registro(s)</p>`;
                });
                html += '</div>';
            }
            
            if (detalhes.periodo) {
                html += `<p class="text-sm mt-2">Período: ${detalhes.periodo.mais_antigo ? formatDate(detalhes.periodo.mais_antigo) : 'N/A'} - ${detalhes.periodo.mais_recente ? formatDate(detalhes.periodo.mais_recente) : 'N/A'}</p>`;
            }

            els.limpezaPreviewContent.innerHTML = html;
            els.limpezaPreview?.classList.remove('hidden');
            els.btnLimpezaExecutar?.removeAttribute('disabled');

        } catch (error) {
            console.error('Erro no preview:', error);
            if (window.AdminUtils) {
                window.AdminUtils.handleApiError(error, 'Erro ao executar preview');
            } else {
                showMessage('error', 'Erro ao executar preview: ' + error.message);
            }
        }
    };

    const executarLimpeza = async () => {
        const tipo = els.limpezaTipo?.value;
        if (!tipo) {
            showMessage('warning', 'Selecione um tipo de limpeza');
            return;
        }

        if (tipo === 'todos' && !els.limpezaConfirmarCheckbox?.checked) {
            showMessage('warning', 'Confirme que deseja deletar todos os logs');
            return;
        }

        // Usar função comum de confirmação se disponível
        if (window.AdminUtils) {
            const result = await window.AdminUtils.showConfirm(
                'Confirmar Deleção',
                'Esta ação não pode ser desfeita. Deseja continuar?',
                'Sim, deletar',
                'Cancelar',
                'warning'
            );
            if (!result.isConfirmed) return;
        } else if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: 'Confirmar Deleção',
                text: 'Esta ação não pode ser desfeita. Deseja continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sim, deletar',
                cancelButtonText: 'Cancelar'
            });
            if (!result.isConfirmed) return;
        }

        const payload = { tipo, preview: false };

        switch (tipo) {
            case 'periodo':
                payload.periodo_dias = parseInt(document.getElementById('limpeza-periodo-dias')?.value);
                break;
            case 'nivel':
                payload.nivel = document.getElementById('limpeza-nivel')?.value;
                break;
            case 'acao':
                payload.acao = document.getElementById('limpeza-acao')?.value;
                break;
            case 'inscricao':
                payload.inscricao_id = parseInt(document.getElementById('limpeza-inscricao-id')?.value);
                break;
            case 'manter_ultimos':
                payload.manter_ultimos_dias = parseInt(document.getElementById('limpeza-manter-ultimos')?.value);
                break;
            case 'periodo_especifico':
                payload.data_inicio = document.getElementById('limpeza-data-inicio')?.value;
                payload.data_fim = document.getElementById('limpeza-data-fim')?.value;
                break;
            case 'todos':
                payload.confirmar = true;
                break;
        }

        try {
            const response = await fetch(api('admin/logs_inscricoes/delete.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Erro ao deletar logs');
            }

            if (window.AdminUtils) {
                window.AdminUtils.showSuccess(data.mensagem || `${data.registros_deletados} registro(s) deletado(s) com sucesso`);
            } else {
                showMessage('success', data.mensagem || `${data.registros_deletados} registro(s) deletado(s) com sucesso`);
            }
            closeModal('modal-limpar-logs');
            carregarLogs();

        } catch (error) {
            console.error('Erro ao deletar:', error);
            if (window.AdminUtils) {
                window.AdminUtils.handleApiError(error, 'Erro ao deletar logs');
            } else {
                showMessage('error', 'Erro ao deletar logs: ' + error.message);
            }
        }
    };

    // Event listeners
    if (els.filtroPeriodo) {
        els.filtroPeriodo.addEventListener('change', (e) => {
            const periodo = e.target.value;
            if (periodo === 'custom') {
                els.filtroDataInicioWrapper?.classList.remove('hidden');
                els.filtroDataFimWrapper?.classList.remove('hidden');
            } else {
                els.filtroDataInicioWrapper?.classList.add('hidden');
                els.filtroDataFimWrapper?.classList.add('hidden');
            }
        });
    }

    if (els.btnAplicarFiltros) {
        els.btnAplicarFiltros.addEventListener('click', aplicarFiltros);
    }

    if (els.btnLimparFiltros) {
        els.btnLimparFiltros.addEventListener('click', limparFiltros);
    }

    if (els.btnLimparLogs) {
        els.btnLimparLogs.addEventListener('click', () => {
            els.limpezaTipo.value = '';
            atualizarOpcoesLimpeza();
            openModal('modal-limpar-logs');
        });
    }

    if (els.limpezaTipo) {
        els.limpezaTipo.addEventListener('change', atualizarOpcoesLimpeza);
    }

    if (els.btnLimpezaPreview) {
        els.btnLimpezaPreview.addEventListener('click', executarPreviewLimpeza);
    }

    if (els.btnLimpezaExecutar) {
        els.btnLimpezaExecutar.addEventListener('click', executarLimpeza);
    }

    // Carregar ações disponíveis
    const carregarAcoes = async () => {
        try {
            const response = await fetch(api('admin/logs_inscricoes/list.php?per_page=1'), {
                credentials: 'same-origin'
            });
            const data = await response.json();
            if (data.success && data.logs) {
                const acoesUnicas = [...new Set(data.logs.map(l => l.acao))].sort();
                if (els.filtroAcao) {
                    acoesUnicas.forEach(acao => {
                        const option = document.createElement('option');
                        option.value = acao;
                        option.textContent = acao;
                        els.filtroAcao.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Erro ao carregar ações:', error);
        }
    };

    // Inicialização
    const init = () => {
        carregarAcoes();
        carregarLogs();
    };

    // Exportar funções para uso global
    window.problemasInscricoes = {
        verDetalhes,
        irParaPagina,
        aplicarFiltros,
        limparFiltros
    };

    // Inicializar quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

