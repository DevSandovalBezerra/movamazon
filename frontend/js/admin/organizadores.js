(() => {
    if (!document.getElementById('organizadores-table-body')) {
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
        organizadores: [],
        currentId: null,
        pendingAction: null,
        filters: {
            status: '',
            regiao: '',
            search: ''
        },
        pagination: {
            page: 1,
            perPage: 20,
            total: 0,
            totalPages: 0
        }
    };

    const els = {
        tableBody: document.getElementById('organizadores-table-body'),
        loading: document.getElementById('organizadores-loading'),
        empty: document.getElementById('organizadores-empty'),
        pagination: document.getElementById('organizadores-pagination'),
        filtroStatus: document.getElementById('filtro-status'),
        filtroRegiao: document.getElementById('filtro-regiao'),
        campoBusca: document.getElementById('campo-busca'),
        btnNovo: document.getElementById('btn-novo-organizador'),
        modal: document.getElementById('modal-organizador'),
        modalTitulo: document.getElementById('modal-organizador-titulo'),
        modalDetalhes: document.getElementById('modal-detalhes'),
        detalhesContent: document.getElementById('organizador-detalhes'),
        modalConfirm: document.getElementById('modal-confirmacao'),
        confirmTitulo: document.getElementById('modal-confirmacao-titulo'),
        confirmTexto: document.getElementById('modal-confirmacao-texto'),
        btnConfirmar: document.getElementById('btn-confirmar-acao'),
        btnSalvar: document.getElementById('btn-salvar-organizador'),
        btnEditarDetalhes: document.getElementById('btn-editar-detalhes'),
        campos: {
            nome: document.getElementById('org-nome'),
            email: document.getElementById('org-email'),
            telefone: document.getElementById('org-telefone'),
            celular: document.getElementById('org-celular'),
            empresa: document.getElementById('org-empresa'),
            regiao: document.getElementById('org-regiao'),
            modalidade: document.getElementById('org-modalidade'),
            quantidadeEventos: document.getElementById('org-quantidade-eventos'),
            regulamento: document.getElementById('org-regulamento')
        }
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

    document.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', () => closeModal(el.getAttribute('data-close-modal')));
    });

    const escapeHtml = (str) => (str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const formatDate = (dateStr) => {
        if (!dateStr) return '—';
        const date = new Date(dateStr);
        return date.toLocaleDateString('pt-BR');
    };

    const renderTable = () => {
        if (!els.tableBody) return;
        els.tableBody.innerHTML = '';
        if (!state.organizadores.length) {
            toggleEmpty(true);
            return;
        }
        toggleEmpty(false);

        const frag = document.createDocumentFragment();
        state.organizadores.forEach((org) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 transition-colors';
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${org.id}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${escapeHtml(org.nome_completo)}</div>
                    <div class="text-sm text-gray-500">${escapeHtml(org.email)}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${escapeHtml(org.email)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${escapeHtml(org.empresa || '—')}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${escapeHtml(org.regiao || '—')}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${org.total_eventos || 0}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${org.status === 'ativo' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}">
                        ${org.status === 'ativo' ? 'Ativo' : 'Inativo'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${formatDate(org.data_cadastro)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        <button class="text-brand-green hover:text-[#065f5a] transition-colors" data-acao="detalhes" data-id="${org.id}" title="Ver detalhes">
                            <i class="fas fa-eye w-4 h-4"></i>
                        </button>
                        <button class="text-blue-600 hover:text-blue-800 transition-colors" data-acao="editar" data-id="${org.id}" title="Editar">
                            <i class="fas fa-edit w-4 h-4"></i>
                        </button>
                        <button class="text-orange-600 hover:text-orange-800 transition-colors" data-acao="toggle" data-id="${org.id}" title="${org.status === 'ativo' ? 'Desativar' : 'Ativar'}">
                            <i class="fas ${org.status === 'ativo' ? 'fa-ban' : 'fa-check'} w-4 h-4"></i>
                        </button>
                        <button class="text-purple-600 hover:text-purple-800 transition-colors" data-acao="reset" data-id="${org.id}" title="Resetar senha">
                            <i class="fas fa-key w-4 h-4"></i>
                        </button>
                    </div>
                </td>
            `;
            frag.appendChild(tr);
        });
        els.tableBody.appendChild(frag);
    };

    const renderPagination = () => {
        if (!els.pagination || state.pagination.totalPages <= 1) {
            if (els.pagination) els.pagination.innerHTML = '';
            return;
        }

        const { page, totalPages, total } = state.pagination;
        els.pagination.innerHTML = `
            <div class="text-sm text-gray-600">
                Mostrando ${((page - 1) * state.pagination.perPage) + 1} a ${Math.min(page * state.pagination.perPage, total)} de ${total}
            </div>
            <div class="flex gap-2">
                <button class="btn-secondary ${page === 1 ? 'opacity-50 cursor-not-allowed' : ''}" 
                        data-page="${page - 1}" ${page === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i> Anterior
                </button>
                <span class="px-4 py-2 text-sm text-gray-700">
                    Página ${page} de ${totalPages}
                </span>
                <button class="btn-secondary ${page === totalPages ? 'opacity-50 cursor-not-allowed' : ''}" 
                        data-page="${page + 1}" ${page === totalPages ? 'disabled' : ''}>
                    Próxima <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        `;

        els.pagination.querySelectorAll('button[data-page]').forEach(btn => {
            btn.addEventListener('click', () => {
                const newPage = parseInt(btn.getAttribute('data-page'));
                if (newPage >= 1 && newPage <= totalPages) {
                    state.pagination.page = newPage;
                    carregarOrganizadores();
                }
            });
        });
    };

    const carregarOrganizadores = async () => {
        toggleLoading(true);
        try {
            const params = [];
            if (state.filters.status) params.push(`status=${encodeURIComponent(state.filters.status)}`);
            if (state.filters.regiao) params.push(`regiao=${encodeURIComponent(state.filters.regiao)}`);
            if (state.filters.search) params.push(`search=${encodeURIComponent(state.filters.search)}`);
            params.push(`page=${state.pagination.page}`);
            params.push(`per_page=${state.pagination.perPage}`);

            const response = await fetch(api('admin/organizadores/list.php', params.join('&')), { credentials: 'same-origin' });
            if (!response.ok) {
                throw new Error('Erro ao carregar organizadores');
            }
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Erro ao carregar organizadores');
            }

            state.organizadores = data.data || [];
            if (data.pagination) {
                state.pagination = { ...state.pagination, ...data.pagination };
            }
            renderTable();
            renderPagination();
        } catch (error) {
            console.error(error);
            showMessage('error', 'Não foi possível carregar os organizadores');
        } finally {
            toggleLoading(false);
        }
    };

    const limparFormulario = () => {
        state.currentId = null;
        els.modalTitulo.textContent = 'Novo Organizador';
        Object.values(els.campos).forEach(campo => {
            if (campo) campo.value = '';
        });
    };

    const preencherFormulario = (org) => {
        state.currentId = org.id;
        els.modalTitulo.textContent = `Editar Organizador #${org.id}`;
        if (els.campos.nome) els.campos.nome.value = org.nome_completo || '';
        if (els.campos.email) els.campos.email.value = org.email || '';
        if (els.campos.telefone) els.campos.telefone.value = org.telefone || '';
        if (els.campos.celular) els.campos.celular.value = org.celular || '';
        if (els.campos.empresa) els.campos.empresa.value = org.empresa || '';
        if (els.campos.regiao) els.campos.regiao.value = org.regiao || '';
        if (els.campos.modalidade) els.campos.modalidade.value = org.modalidade_esportiva || '';
        if (els.campos.quantidadeEventos) els.campos.quantidadeEventos.value = org.quantidade_eventos || '';
        if (els.campos.regulamento) els.campos.regulamento.value = org.regulamento || '';
    };

    const salvarOrganizador = async () => {
        const payload = {
            nome_completo: els.campos.nome.value.trim(),
            email: els.campos.email.value.trim(),
            telefone: els.campos.telefone.value.trim(),
            celular: els.campos.celular.value.trim(),
            empresa: els.campos.empresa.value.trim(),
            regiao: els.campos.regiao.value,
            modalidade_esportiva: els.campos.modalidade.value.trim(),
            quantidade_eventos: els.campos.quantidadeEventos.value.trim(),
            regulamento: els.campos.regulamento.value
        };

        if (!payload.nome_completo || !payload.email || !payload.empresa || !payload.regiao || !payload.modalidade_esportiva) {
            showMessage('error', 'Preencha todos os campos obrigatórios');
            return;
        }

        if (state.currentId) {
            payload.id = state.currentId;
        }

        try {
            els.btnSalvar.disabled = true;
            const endpoint = state.currentId ? 'update' : 'create';
            const response = await fetch(api(`admin/organizadores/${endpoint}.php`), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erro ao salvar');
            }
            showMessage('success', data.message || 'Salvo com sucesso!');
            closeModal('modal-organizador');
            await carregarOrganizadores();
        } catch (error) {
            console.error(error);
            showMessage('error', error.message || 'Falha ao salvar organizador');
        } finally {
            els.btnSalvar.disabled = false;
        }
    };

    const abrirDetalhes = async (id) => {
        try {
            const response = await fetch(api('admin/organizadores/get.php', `id=${id}`), { credentials: 'same-origin' });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erro ao carregar detalhes');
            }

            const org = data.data;
            els.detalhesContent.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Dados Pessoais</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong>Nome:</strong> ${escapeHtml(org.nome_completo)}</div>
                            <div><strong>Email:</strong> ${escapeHtml(org.email)}</div>
                            <div><strong>Telefone:</strong> ${escapeHtml(org.telefone || '—')}</div>
                            <div><strong>Celular:</strong> ${escapeHtml(org.celular || '—')}</div>
                            <div><strong>Status:</strong> <span class="badge ${org.status === 'ativo' ? 'badge-success' : 'badge-muted'}">${org.status}</span></div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Dados do Organizador</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong>Empresa:</strong> ${escapeHtml(org.empresa || '—')}</div>
                            <div><strong>Região:</strong> ${escapeHtml(org.regiao || '—')}</div>
                            <div><strong>Modalidade:</strong> ${escapeHtml(org.modalidade_esportiva || '—')}</div>
                            <div><strong>Quantidade Eventos:</strong> ${escapeHtml(org.quantidade_eventos || '—')}</div>
                            <div><strong>Regulamento:</strong> ${escapeHtml(org.regulamento || '—')}</div>
                        </div>
                    </div>
                </div>
                ${org.eventos && org.eventos.length > 0 ? `
                    <div class="mt-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Eventos (${org.eventos.length})</h4>
                        <div class="space-y-2">
                            ${org.eventos.map(e => `
                                <div class="text-sm p-2 bg-gray-50 rounded">
                                    <strong>${escapeHtml(e.nome)}</strong> - 
                                    <span class="badge">${escapeHtml(e.status)}</span>
                                    ${e.data_realizacao ? ` - ${formatDate(e.data_realizacao)}` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
            `;
            state.currentId = id;
            openModal('modal-detalhes');
        } catch (error) {
            console.error(error);
            showMessage('error', 'Não foi possível carregar os detalhes');
        }
    };

    const toggleStatus = async (id) => {
        try {
            const response = await fetch(api('admin/organizadores/toggle_status.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ id })
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erro ao alterar status');
            }
            showMessage('success', 'Status atualizado!');
            await carregarOrganizadores();
        } catch (error) {
            console.error(error);
            showMessage('error', error.message || 'Falha ao alterar status');
        }
    };

    const resetPassword = async (id) => {
        try {
            const response = await fetch(api('admin/organizadores/reset_password.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ id })
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erro ao resetar senha');
            }
            const senha = data?.data?.senha_temporaria || '';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Senha resetada',
                    html: senha ? `
                        <div class="space-y-3">
                            <p>Nova senha temporária gerada:</p>
                            <div class="flex items-center gap-2 justify-center">
                                <code class="px-3 py-2 bg-gray-100 rounded text-lg">${senha}</code>
                                <button id="copy-temp-password" class="btn-table-primary">Copiar</button>
                            </div>
                            <p class="text-xs text-gray-500">Também enviada por e-mail.</p>
                        </div>
                    ` : 'Nova senha gerada e enviada por e-mail!',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
                setTimeout(() => {
                    const btnCopy = document.getElementById('copy-temp-password');
                    if (btnCopy && senha) {
                        btnCopy.addEventListener('click', async () => {
                            try {
                                await navigator.clipboard.writeText(senha);
                                showMessage('success', 'Senha copiada');
                            } catch (_) {
                                showMessage('error', 'Não foi possível copiar');
                            }
                        });
                    }
                }, 0);
            } else {
                showMessage('success', 'Nova senha: ' + senha);
            }
        } catch (error) {
            console.error(error);
            showMessage('error', error.message || 'Falha ao resetar senha');
        }
    };

    if (els.btnNovo) {
        els.btnNovo.addEventListener('click', () => {
            limparFormulario();
            openModal('modal-organizador');
        });
    }

    if (els.btnSalvar) {
        els.btnSalvar.addEventListener('click', salvarOrganizador);
    }

    if (els.btnEditarDetalhes) {
        els.btnEditarDetalhes.addEventListener('click', () => {
            closeModal('modal-detalhes');
            if (state.currentId) {
                abrirDetalhes(state.currentId).then(() => {
                    const org = state.organizadores.find(o => o.id === state.currentId);
                    if (org) {
                        preencherFormulario(org);
                        openModal('modal-organizador');
                    }
                });
            }
        });
    }

    if (els.campoBusca) {
        let searchTimeout;
        els.campoBusca.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                state.filters.search = e.target.value.trim();
                state.pagination.page = 1;
                carregarOrganizadores();
            }, 500);
        });
        
        els.campoBusca.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                state.filters.search = els.campoBusca.value.trim();
                state.pagination.page = 1;
                carregarOrganizadores();
            }
        });
    }

    if (els.filtroStatus) {
        els.filtroStatus.addEventListener('change', () => {
            state.filters.status = els.filtroStatus.value;
            state.pagination.page = 1;
            carregarOrganizadores();
        });
    }

    if (els.filtroRegiao) {
        els.filtroRegiao.addEventListener('change', () => {
            state.filters.regiao = els.filtroRegiao.value;
            state.pagination.page = 1;
            carregarOrganizadores();
        });
    }

    if (els.tableBody) {
        els.tableBody.addEventListener('click', (event) => {
            const btn = event.target.closest('button[data-acao]');
            if (!btn) return;
            const id = parseInt(btn.getAttribute('data-id'));
            const acao = btn.getAttribute('data-acao');

            if (acao === 'detalhes') {
                abrirDetalhes(id);
            } else if (acao === 'editar') {
                const org = state.organizadores.find(o => o.id === id);
                if (org) {
                    preencherFormulario(org);
                    openModal('modal-organizador');
                }
            } else if (acao === 'toggle') {
                const org = state.organizadores.find(o => o.id === id);
                els.confirmTitulo.textContent = org.status === 'ativo' ? 'Desativar Organizador' : 'Ativar Organizador';
                els.confirmTexto.textContent = `Tem certeza que deseja ${org.status === 'ativo' ? 'desativar' : 'ativar'} este organizador?`;
                state.pendingAction = () => toggleStatus(id);
                openModal('modal-confirmacao');
            } else if (acao === 'reset') {
                els.confirmTitulo.textContent = 'Resetar Senha';
                els.confirmTexto.textContent = 'Uma nova senha temporária será gerada e enviada por e-mail. Deseja continuar?';
                state.pendingAction = () => resetPassword(id);
                openModal('modal-confirmacao');
            }
        });
    }

    if (els.btnConfirmar) {
        els.btnConfirmar.addEventListener('click', () => {
            if (state.pendingAction) {
                state.pendingAction();
                state.pendingAction = null;
            }
            closeModal('modal-confirmacao');
        });
    }

    const carregarRegioes = async () => {
        try {
            const response = await fetch(api('admin/organizadores/list.php', 'per_page=1000'), { credentials: 'same-origin' });
            const data = await response.json();
            if (data.success && data.data) {
                const regioes = [...new Set(data.data.map(org => org.regiao).filter(Boolean))].sort();
                if (els.filtroRegiao) {
                    regioes.forEach(regiao => {
                        const option = document.createElement('option');
                        option.value = regiao;
                        option.textContent = regiao;
                        els.filtroRegiao.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Erro ao carregar regiões:', error);
        }
    };

    carregarRegioes();
    carregarOrganizadores();
})();

