(() => {
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
        categoria: '',
        search: '',
        data: [],
        categorias: [],
        current: null
    };

    const els = {
        busca: document.getElementById('campo-busca'),
        btnAtualizar: document.getElementById('btn-atualizar-configs'),
        loading: document.getElementById('config-loading'),
        empty: document.getElementById('config-empty'),
        grid: document.getElementById('config-grid'),
        categoryChips: document.getElementById('category-chips'),
        totalConfigs: document.getElementById('total-configs'),
        
        // Status indicators
        statusOpenAI: document.getElementById('status-openai'),
        statusOpenAIText: document.getElementById('status-openai-text'),
        statusAnthropic: document.getElementById('status-anthropic'),
        statusAnthropicText: document.getElementById('status-anthropic-text'),
        statusGemini: document.getElementById('status-gemini'),
        statusGeminiText: document.getElementById('status-gemini-text'),
        statusTreino: document.getElementById('status-treino'),
        statusTreinoText: document.getElementById('status-treino-text'),
        
        // Modal
        modal: document.getElementById('modal-config'),
        modalChave: document.getElementById('modal-config-chave'),
        modalChaveDisplay: document.getElementById('modal-config-chave-display'),
        modalDescricao: document.getElementById('modal-config-descricao'),
        modalInputWrapper: document.getElementById('modal-config-input-wrapper'),
        modalUpdated: document.getElementById('modal-config-updated'),
        modalIcon: document.getElementById('modal-config-icon'),
        btnSalvar: document.getElementById('btn-salvar-config'),
        
        // Modal Histórico
        modalHistorico: document.getElementById('modal-historico'),
        historicoLista: document.getElementById('historico-lista')
    };

    if (!els.grid) return;

    const getCategoryIcon = (categoria) => {
        const icons = {
            'AI': { icon: 'fa-brain', color: 'purple' },
            'Sistema': { icon: 'fa-cog', color: 'blue' },
            'Pagamento': { icon: 'fa-credit-card', color: 'green' },
            'Email': { icon: 'fa-envelope', color: 'red' },
            'Integrações': { icon: 'fa-plug', color: 'yellow' }
        };
        return icons[categoria] || { icon: 'fa-cog', color: 'gray' };
    };

    const toggleLoading = (show) => {
        els.loading?.classList.toggle('hidden', !show);
    };

    const toggleEmpty = (show) => {
        els.empty?.classList.toggle('hidden', !show);
    };

    // Usar função comum do AdminUtils se disponível
    const showMessage = (type, message) => {
        if (window.AdminUtils) {
            window.AdminUtils.showMessage(type, message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: type, title: message, timer: 2500, showConfirmButton: false });
        } else {
            alert(message);
        }
    };

    const formatValue = (valor, tipo) => {
        if (valor === null || valor === undefined) {
            return '<span class="text-gray-400 text-sm">não configurado</span>';
        }
        if (String(valor).length > 100) {
            return `<span class="text-sm text-gray-600">${escapeHtml(String(valor).substring(0, 100))}...</span>`;
        }
        switch (tipo) {
            case 'boolean':
                return valor 
                    ? '<span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">Ativo</span>' 
                    : '<span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">Inativo</span>';
            default:
                return `<span class="text-sm text-gray-900">${escapeHtml(String(valor))}</span>`;
        }
    };

    const escapeHtml = (str) => (str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const renderCategoryChips = () => {
        if (!els.categoryChips) return;
        
        const chips = state.categorias.map(cat => {
            const isActive = state.categoria === cat;
            return `<button class="category-chip ${isActive ? 'active' : ''}" data-category="${cat}">${cat}</button>`;
        }).join('');
        
        els.categoryChips.innerHTML = `
            <button class="category-chip ${state.categoria === '' ? 'active' : ''}" data-category="">Todas</button>
            ${chips}
        `;
    };

    const renderCards = () => {
        if (!els.grid) return;
        els.grid.innerHTML = '';
        
        const filtered = state.data.filter(cfg => {
            const matchCategoria = !state.categoria || cfg.categoria === state.categoria;
            const matchSearch = !state.search || 
                cfg.chave.toLowerCase().includes(state.search.toLowerCase()) ||
                (cfg.descricao && cfg.descricao.toLowerCase().includes(state.search.toLowerCase()));
            return matchCategoria && matchSearch;
        });

        if (!filtered.length) {
            toggleEmpty(true);
            els.grid.classList.add('hidden');
            return;
        }

        toggleEmpty(false);
        els.grid.classList.remove('hidden');

        const frag = document.createDocumentFragment();
        filtered.forEach(cfg => {
            const { icon, color } = getCategoryIcon(cfg.categoria);
            const card = document.createElement('div');
            card.className = 'bg-white rounded-lg border border-gray-200 p-5 hover:shadow-md transition-shadow';
            card.innerHTML = `
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-start gap-3 flex-1">
                        <div class="w-10 h-10 bg-${color}-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas ${icon} text-${color}-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 mb-1 truncate">${escapeHtml(cfg.chave)}</h3>
                            <p class="text-xs text-gray-500">${cfg.descricao || 'Sem descrição'}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded">${escapeHtml(cfg.categoria)}</span>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-3 mb-3">
                    <p class="text-xs text-gray-500 mb-1">Valor atual:</p>
                    <div class="break-words">${formatValue(cfg.valor, cfg.tipo)}</div>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">
                        <i class="fas fa-clock mr-1"></i>
                        ${cfg.updated_at ? new Date(cfg.updated_at).toLocaleDateString('pt-BR') : 'N/A'}
                    </span>
                    <div class="flex gap-2">
                        <button class="btn-editar-config px-3 py-1.5 text-xs bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors" data-chave="${escapeHtml(cfg.chave)}">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </button>
                        <button class="btn-historico-config p-1.5 text-gray-400 hover:text-gray-600 transition-colors" data-chave="${escapeHtml(cfg.chave)}" title="Ver histórico">
                            <i class="fas fa-history"></i>
                        </button>
                    </div>
                </div>
            `;
            frag.appendChild(card);
        });
        els.grid.appendChild(frag);
        
        // Update total
        if (els.totalConfigs) {
            els.totalConfigs.textContent = state.data.length;
        }
    };

    const checkAPIStatus = async () => {
        const configs = state.data;
        
        // Check OpenAI
        const openaiKey = configs.find(c => c.chave === 'ai.openai.api_key')?.valor;
        if (openaiKey && openaiKey !== 'n/d') {
            setStatus('openai', 'connected', 'Configurado');
        } else {
            setStatus('openai', 'disconnected', 'Não configurado');
        }
        
        // Check Anthropic
        const anthropicKey = configs.find(c => c.chave === 'ai.anthropic.api_key')?.valor;
        if (anthropicKey && anthropicKey !== 'n/d') {
            setStatus('anthropic', 'connected', 'Configurado');
        } else {
            setStatus('anthropic', 'disconnected', 'Não configurado');
        }
        
        // Check Gemini
        const geminiKey = configs.find(c => c.chave === 'ai.google.api_key')?.valor;
        if (geminiKey && geminiKey !== 'n/d') {
            setStatus('gemini', 'connected', 'Configurado');
        } else {
            setStatus('gemini', 'disconnected', 'Não configurado');
        }
    };

    const setStatus = (provider, status, text) => {
        const statusEl = els[`status${provider.charAt(0).toUpperCase() + provider.slice(1)}`];
        const textEl = els[`status${provider.charAt(0).toUpperCase() + provider.slice(1)}Text`];
        
        if (statusEl && textEl) {
            if (status === 'connected') {
                statusEl.className = 'w-2 h-2 rounded-full bg-green-500 animate-pulse';
                textEl.className = 'text-sm font-medium text-green-600';
            } else if (status === 'warning') {
                statusEl.className = 'w-2 h-2 rounded-full bg-yellow-500 animate-pulse';
                textEl.className = 'text-sm font-medium text-yellow-600';
            } else {
                statusEl.className = 'w-2 h-2 rounded-full bg-gray-300';
                textEl.className = 'text-sm font-medium text-gray-500';
            }
            textEl.textContent = text;
        }
    };

    const carregarConfiguracoes = async () => {
        toggleLoading(true);
        try {
            const resp = await fetch(api('admin/config/list.php'));
            const data = await resp.json();
            if (data.success) {
                state.data = data.data || [];
                state.categorias = [...new Set(state.data.map(c => c.categoria))].sort();
                renderCategoryChips();
                renderCards();
                checkAPIStatus();
            } else {
                showMessage('error', data.message || 'Erro ao carregar configurações');
            }
        } catch (err) {
            console.error(err);
            showMessage('error', 'Erro ao carregar configurações');
        } finally {
            toggleLoading(false);
        }
    };

    const abrirModalEdicao = (chave) => {
        const cfg = state.data.find(c => c.chave === chave);
        if (!cfg) return;

        state.current = cfg;
        els.modalChave.value = cfg.chave;
        els.modalChaveDisplay.textContent = cfg.chave;
        els.modalDescricao.textContent = cfg.descricao || 'Sem descrição';
        els.modalUpdated.textContent = cfg.updated_at 
            ? new Date(cfg.updated_at).toLocaleString('pt-BR')
            : 'Nunca';

        const { icon, color } = getCategoryIcon(cfg.categoria);
        els.modalIcon.className = `w-10 h-10 bg-${color}-100 rounded-lg flex items-center justify-center`;
        els.modalIcon.innerHTML = `<i class="fas ${icon} text-${color}-600"></i>`;

        let inputHtml = '';
        switch (cfg.tipo) {
            case 'boolean':
                const checked = cfg.valor ? 'checked' : '';
                inputHtml = `
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Valor</label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="modal-config-valor" ${checked} class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="text-sm text-gray-700">Ativo</span>
                    </label>
                `;
                break;
            case 'number':
                inputHtml = `
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Valor</label>
                    <input type="number" id="modal-config-valor" value="${cfg.valor || ''}" class="input-primary w-full">
                `;
                break;
            case 'json':
                const jsonStr = typeof cfg.valor === 'object' ? JSON.stringify(cfg.valor, null, 2) : cfg.valor;
                inputHtml = `
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Valor (JSON)</label>
                    <textarea id="modal-config-valor" rows="6" class="input-primary w-full font-mono text-sm">${jsonStr || ''}</textarea>
                `;
                break;
            default:
                inputHtml = `
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Valor</label>
                    <input type="text" id="modal-config-valor" value="${cfg.valor || ''}" class="input-primary w-full">
                `;
        }

        els.modalInputWrapper.innerHTML = inputHtml;
        els.modal.classList.remove('hidden');
    };

    const salvarConfiguracao = async () => {
        if (!state.current) return;

        const inputEl = document.getElementById('modal-config-valor');
        if (!inputEl) return;

        let valor;
        if (state.current.tipo === 'boolean') {
            valor = inputEl.checked;
        } else if (state.current.tipo === 'json') {
            try {
                valor = JSON.parse(inputEl.value);
            } catch {
                showMessage('error', 'JSON inválido');
                return;
            }
        } else if (state.current.tipo === 'number') {
            valor = parseFloat(inputEl.value) || 0;
        } else {
            valor = inputEl.value;
        }

        try {
            const resp = await fetch(api('admin/config/update.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ key: state.current.chave, value: valor })
            });

            const data = await resp.json();
            if (data.success) {
                showMessage('success', 'Configuração atualizada!');
                els.modal.classList.add('hidden');
                await carregarConfiguracoes();
            } else {
                showMessage('error', data.message || 'Erro ao salvar');
            }
        } catch (err) {
            console.error(err);
            showMessage('error', 'Erro ao salvar configuração');
        }
    };

    const carregarHistorico = async (chave) => {
        if (!els.historicoLista) return;
        els.historicoLista.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
        els.modalHistorico.classList.remove('hidden');

        try {
            const resp = await fetch(api('admin/config/historico.php', `chave=${encodeURIComponent(chave)}`));
            const data = await resp.json();
            
            if (data.success && data.data && data.data.length) {
                els.historicoLista.innerHTML = data.data.map(h => `
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user-circle text-gray-400"></i>
                                <span class="text-sm font-medium text-gray-900">${escapeHtml(h.usuario_nome || 'Sistema')}</span>
                            </div>
                            <span class="text-xs text-gray-500">
                                ${new Date(h.data_alteracao).toLocaleString('pt-BR')}
                            </span>
                        </div>
                        <div class="ml-6">
                            <div class="text-xs text-gray-600">
                                <strong>Valor anterior:</strong> 
                                <span class="font-mono bg-white px-2 py-1 rounded">${escapeHtml(String(h.valor_antigo))}</span>
                            </div>
                            <div class="text-xs text-gray-600 mt-1">
                                <strong>Novo valor:</strong> 
                                <span class="font-mono bg-white px-2 py-1 rounded">${escapeHtml(String(h.valor_novo))}</span>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                els.historicoLista.innerHTML = '<p class="text-center text-gray-500 py-8">Nenhum histórico encontrado</p>';
            }
        } catch (err) {
            console.error(err);
            els.historicoLista.innerHTML = '<p class="text-center text-red-500 py-8">Erro ao carregar histórico</p>';
        }
    };

    // Event Listeners
    els.busca?.addEventListener('input', (e) => {
        state.search = e.target.value;
        renderCards();
    });

    els.btnAtualizar?.addEventListener('click', carregarConfiguracoes);

    document.addEventListener('click', (e) => {
        if (e.target.closest('.category-chip')) {
            const btn = e.target.closest('.category-chip');
            state.categoria = btn.dataset.category;
            renderCategoryChips();
            renderCards();
        }

        if (e.target.closest('.btn-editar-config')) {
            const btn = e.target.closest('.btn-editar-config');
            abrirModalEdicao(btn.dataset.chave);
        }

        if (e.target.closest('.btn-historico-config')) {
            const btn = e.target.closest('.btn-historico-config');
            carregarHistorico(btn.dataset.chave);
        }

        if (e.target.closest('[data-close-modal]')) {
            const modalId = e.target.closest('[data-close-modal]').dataset.closeModal;
            document.getElementById(modalId)?.classList.add('hidden');
        }
    });

    els.btnSalvar?.addEventListener('click', salvarConfiguracao);

    // Init
    carregarConfiguracoes();
})();
