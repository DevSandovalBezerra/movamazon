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

    const PROVIDERS = {
        openai: {
            name: 'OpenAI',
            icon: 'fa-brain',
            color: 'purple',
            models: [
                { value: 'gpt-4o', label: 'GPT-4o' },
                { value: 'gpt-4-turbo', label: 'GPT-4 Turbo' },
                { value: 'gpt-4', label: 'GPT-4' },
                { value: 'gpt-3.5-turbo', label: 'GPT-3.5 Turbo' }
            ]
        },
        anthropic: {
            name: 'Anthropic',
            icon: 'fa-robot',
            color: 'orange',
            models: [
                { value: 'claude-3-5-sonnet-20241022', label: 'Claude 3.5 Sonnet' },
                { value: 'claude-3-opus-20240229', label: 'Claude 3 Opus' },
                { value: 'claude-3-sonnet-20240229', label: 'Claude 3 Sonnet' }
            ]
        },
        google: {
            name: 'Google Gemini',
            icon: 'fa-gem',
            color: 'blue',
            models: [
                { value: 'gemini-pro', label: 'Gemini Pro' },
                { value: 'gemini-ultra', label: 'Gemini Ultra' }
            ]
        }
    };

    const state = {
        configs: {},
        currentProvider: null
    };

    const els = {
        btnTestAll: document.getElementById('btn-test-all'),
        btnSaveGeral: document.getElementById('btn-save-geral'),
        provedorAtivo: document.getElementById('ai-provedor-ativo'),
        timeout: document.getElementById('ai-timeout'),
        
        // Modal
        modal: document.getElementById('modal-config-ai'),
        modalIcon: document.getElementById('modal-ai-icon'),
        modalTitulo: document.getElementById('modal-ai-titulo'),
        modalProviderType: document.getElementById('modal-provider-type'),
        modalApiKey: document.getElementById('modal-api-key'),
        modalModel: document.getElementById('modal-ai-model'),
        modalMaxTokens: document.getElementById('modal-max-tokens'),
        modalTemperature: document.getElementById('modal-temperature'),
        modalPromptBase: document.getElementById('modal-prompt-base'),
        tempValue: document.getElementById('temp-value'),
        btnSaveProvider: document.getElementById('btn-save-provider')
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

    const updateProviderStatus = (provider, hasKey, model, temp) => {
        const keyStatus = document.getElementById(`${provider}-key-status`);
        const modelDisplay = document.getElementById(`${provider}-model-display`);
        const tempDisplay = document.getElementById(`${provider}-temp-display`);
        const statusDot = document.getElementById(`status-dot-${provider}`);
        const statusText = document.getElementById(`status-text-${provider}`);

        if (keyStatus) {
            keyStatus.textContent = hasKey ? 'Configurado' : 'Não configurado';
            keyStatus.className = hasKey ? 'text-green-600 font-medium' : 'text-gray-400';
        }

        if (modelDisplay) modelDisplay.textContent = model || '-';
        if (tempDisplay) tempDisplay.textContent = temp || '-';

        if (statusDot && statusText) {
            if (hasKey) {
                statusDot.className = 'w-3 h-3 rounded-full bg-green-400';
                statusText.textContent = 'Configurado';
            } else {
                statusDot.className = 'w-3 h-3 rounded-full bg-gray-300';
                statusText.textContent = 'Não configurado';
            }
        }
    };

    const carregarConfiguracoes = async () => {
        try {
            const resp = await fetch(api('admin/config/list.php', 'categoria=AI'));
            const data = await resp.json();

            if (data.success && data.data) {
                data.data.forEach(config => {
                    state.configs[config.chave] = config.valor;
                });

                // Update OpenAI
                updateProviderStatus(
                    'openai',
                    state.configs['ai.openai.api_key'] && state.configs['ai.openai.api_key'] !== 'n/d',
                    state.configs['ai.openai.model'],
                    state.configs['ai.openai.temperature']
                );

                // Update Anthropic
                updateProviderStatus(
                    'anthropic',
                    state.configs['ai.anthropic.api_key'] && state.configs['ai.anthropic.api_key'] !== 'n/d',
                    state.configs['ai.anthropic.model'],
                    state.configs['ai.anthropic.temperature']
                );

                // Update Google
                updateProviderStatus(
                    'gemini',
                    state.configs['ai.google.api_key'] && state.configs['ai.google.api_key'] !== 'n/d',
                    state.configs['ai.google.model'],
                    state.configs['ai.google.temperature']
                );

                // Update global configs
                if (els.provedorAtivo) els.provedorAtivo.value = state.configs['ai.provedor_ativo'] || 'openai';
                if (els.timeout) els.timeout.value = state.configs['ai.timeout'] || '120';
            }
        } catch (err) {
            console.error('Erro ao carregar configurações:', err);
            showMessage('error', 'Erro ao carregar configurações');
        }
    };

    const abrirModalConfig = (provider) => {
        state.currentProvider = provider;
        const providerInfo = PROVIDERS[provider];

        // Update modal header
        els.modalIcon.className = `w-12 h-12 bg-${providerInfo.color}-100 rounded-xl flex items-center justify-center`;
        els.modalIcon.innerHTML = `<i class="fas ${providerInfo.icon} text-${providerInfo.color}-600 text-xl"></i>`;
        els.modalTitulo.textContent = `Configurar ${providerInfo.name}`;
        els.modalProviderType.value = provider;

        // Populate model options
        els.modalModel.innerHTML = providerInfo.models
            .map(m => `<option value="${m.value}">${m.label}</option>`)
            .join('');

        // Load current values
        const prefix = provider === 'google' ? 'ai.google' : `ai.${provider}`;
        els.modalApiKey.value = state.configs[`${prefix}.api_key`] || '';
        els.modalModel.value = state.configs[`${prefix}.model`] || providerInfo.models[0].value;
        els.modalMaxTokens.value = state.configs[`${prefix}.max_tokens`] || '8000';
        els.modalTemperature.value = state.configs[`${prefix}.temperature`] || '0.5';
        els.modalPromptBase.value = state.configs[`ai.prompt_treino_base`] || '';
        els.tempValue.textContent = els.modalTemperature.value;

        els.modal.classList.remove('hidden');
    };

    const salvarProviderConfig = async () => {
        const provider = state.currentProvider;
        if (!provider) return;

        const prefix = provider === 'google' ? 'ai.google' : `ai.${provider}`;
        const configs = {
            [`${prefix}.api_key`]: els.modalApiKey.value,
            [`${prefix}.model`]: els.modalModel.value,
            [`${prefix}.max_tokens`]: els.modalMaxTokens.value,
            [`${prefix}.temperature`]: els.modalTemperature.value,
            'ai.prompt_treino_base': els.modalPromptBase.value
        };

        try {
            els.btnSaveProvider.disabled = true;
            els.btnSaveProvider.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Salvando...';

            const promises = Object.entries(configs).map(([chave, valor]) =>
                fetch(api('admin/config/update.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ chave, valor })
                })
            );

            const results = await Promise.all(promises);
            const allSuccess = results.every(async r => (await r.json()).success);

            if (allSuccess) {
                showMessage('success', 'Configurações salvas com sucesso!');
                els.modal.classList.add('hidden');
                await carregarConfiguracoes();
            } else {
                showMessage('error', 'Erro ao salvar algumas configurações');
            }
        } catch (err) {
            console.error('Erro ao salvar:', err);
            showMessage('error', 'Erro ao salvar configurações');
        } finally {
            els.btnSaveProvider.disabled = false;
            els.btnSaveProvider.innerHTML = 'Salvar Configurações';
        }
    };

    const salvarConfigGerai = async () => {
        const configs = {
            'ai.provedor_ativo': els.provedorAtivo.value,
            'ai.timeout': els.timeout.value
        };

        try {
            els.btnSaveGeral.disabled = true;
            els.btnSaveGeral.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Salvando...';

            const promises = Object.entries(configs).map(([chave, valor]) =>
                fetch(api('admin/config/update.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ chave, valor })
                })
            );

            await Promise.all(promises);
            showMessage('success', 'Configurações globais salvas!');
        } catch (err) {
            console.error('Erro ao salvar:', err);
            showMessage('error', 'Erro ao salvar configurações');
        } finally {
            els.btnSaveGeral.disabled = false;
            els.btnSaveGeral.innerHTML = '<i class="fas fa-save mr-2"></i> Salvar Configurações';
        }
    };

    const testarProvider = async (provider) => {
        const btn = document.querySelector(`.btn-test-provider[data-provider="${provider}"]`);
        const statusDot = document.getElementById(`status-dot-${provider}`);
        const statusText = document.getElementById(`status-text-${provider}`);

        if (!btn) return;

        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        statusDot.className = 'w-3 h-3 rounded-full bg-yellow-400 animate-pulse';
        statusText.textContent = 'Testando...';

        try {
            const resp = await fetch(api('admin/ai/test_connection.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ provider })
            });

            const data = await resp.json();

            if (data.success) {
                statusDot.className = 'w-3 h-3 rounded-full bg-green-500 animate-pulse';
                statusText.textContent = 'Online';
                showMessage('success', `${PROVIDERS[provider].name}: Conexão bem-sucedida!`);
            } else {
                statusDot.className = 'w-3 h-3 rounded-full bg-red-500';
                statusText.textContent = 'Erro';
                showMessage('error', data.message || 'Erro ao testar conexão');
            }
        } catch (err) {
            console.error('Erro ao testar:', err);
            statusDot.className = 'w-3 h-3 rounded-full bg-red-500';
            statusText.textContent = 'Erro';
            showMessage('error', 'Erro ao testar conexão');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    };

    // Event Listeners
    els.btnTestAll?.addEventListener('click', async () => {
        await Promise.all(['openai', 'anthropic', 'google'].map(testarProvider));
    });

    els.btnSaveGeral?.addEventListener('click', salvarConfigGerai);
    els.btnSaveProvider?.addEventListener('click', salvarProviderConfig);

    els.modalTemperature?.addEventListener('input', (e) => {
        els.tempValue.textContent = e.target.value;
    });

    document.addEventListener('click', (e) => {
        if (e.target.closest('.btn-config-provider')) {
            const btn = e.target.closest('.btn-config-provider');
            abrirModalConfig(btn.dataset.provider);
        }

        if (e.target.closest('.btn-test-provider')) {
            const btn = e.target.closest('.btn-test-provider');
            testarProvider(btn.dataset.provider);
        }

        if (e.target.closest('[data-close-modal]')) {
            const modalId = e.target.closest('[data-close-modal]').dataset.closeModal;
            document.getElementById(modalId)?.classList.add('hidden');
        }
    });

    // Init
    carregarConfiguracoes();
})();
