(() => {
    if (!document.getElementById('termos-table-body')) {
        return;
    }

    if (!window.API_BASE) {
        const path = window.location.pathname || '';
        const idx = path.indexOf('/frontend/');
        window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
        console.log('[TERMOS] API_BASE definido:', window.API_BASE, '| Pathname:', path);
    }

    const api = (endpoint, params = '') => {
        const sep = params ? `?${params}` : '';
        const url = `${window.API_BASE}/api/${endpoint}${sep}`;
        console.log('[TERMOS] Chamando API:', url);
        return url;
    };

    const state = {
        termos: [],
        currentId: null,
        filters: {
            status: '',
            tipo: '',
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
        tableBody: document.getElementById('termos-table-body'),
        loading: document.getElementById('termos-loading'),
        empty: document.getElementById('termos-empty'),
        pagination: document.getElementById('termos-pagination'),
        filtroStatus: document.getElementById('filtro-status'),
        filtroTipo: document.getElementById('filtro-tipo'),
        campoBusca: document.getElementById('campo-busca'),
        btnNovo: document.getElementById('btn-novo-termo'),
        modal: document.getElementById('modal-termo'),
        modalTitulo: document.getElementById('modal-termo-titulo'),
        modalVisualizar: document.getElementById('modal-visualizar-termo'),
        modalVisualizarTitulo: document.getElementById('modal-visualizar-titulo'),
        modalVisualizarConteudo: document.getElementById('modal-visualizar-conteudo'),
        btnSalvar: document.getElementById('btn-salvar-termo'),
        campos: {
            id: document.getElementById('termo-id'),
            titulo: document.getElementById('termo-titulo'),
            versao: document.getElementById('termo-versao'),
            tipo: document.getElementById('termo-tipo'),
            conteudo: document.getElementById('termo-conteudo'),
            ativo: document.getElementById('termo-ativo')
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

    // Carregar termos
    const carregarTermos = async () => {
        toggleLoading(true);
        toggleEmpty(false);

        const params = new URLSearchParams({
            page: state.pagination.page,
            per_page: state.pagination.perPage
        });

        if (state.filters.status !== '') {
            params.append('status', state.filters.status);
        }

        if (state.filters.tipo !== '') {
            params.append('tipo', state.filters.tipo);
        }

        if (state.filters.search) {
            params.append('search', state.filters.search);
        }

        try {
            const response = await fetch(api('admin/termos-inscricao/list.php', params.toString()));

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Resposta não é JSON. Tipo: ${contentType}, Conteúdo: ${text.substring(0, 200)}`);
            }

            const data = await response.json();

            if (data.success) {
                state.termos = data.data || [];
                state.pagination.total = data.pagination?.total || 0;
                state.pagination.totalPages = data.pagination?.total_pages || 0;

                renderizarTermos();
                renderizarPaginacao();
            } else {
                showMessage('error', data.message || 'Erro ao carregar termos');
            }
        } catch (error) {
            console.error('Erro ao carregar termos:', error);
            showMessage('error', 'Erro ao carregar termos: ' + error.message);
        } finally {
            toggleLoading(false);
        }
    };

    // Renderizar termos na tabela
    const renderizarTermos = () => {
        if (!els.tableBody) return;

        if (state.termos.length === 0) {
            els.tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Nenhum termo encontrado</td></tr>';
            toggleEmpty(true);
            return;
        }

        toggleEmpty(false);

        els.tableBody.innerHTML = state.termos.map(termo => {
            const statusBadge = termo.ativo
                ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>'
                : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inativo</span>';

            const tipoLabel = { inscricao: 'Inscrição', anamnese: 'Anamnese', treino: 'Treino' }[termo.tipo] || termo.tipo || 'Inscrição';

            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${termo.id}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <div class="font-medium">${termo.titulo || 'Sem título'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${tipoLabel}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${termo.versao || '1.0'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(termo.data_criacao).toLocaleDateString('pt-BR')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <button onclick="termosAdmin.visualizar(${termo.id})" class="text-blue-600 hover:text-blue-900" title="Visualizar">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="termosAdmin.editar(${termo.id})" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="termosAdmin.toggleStatus(${termo.id}, ${termo.ativo ? 'false' : 'true'})" class="${termo.ativo ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'}" title="${termo.ativo ? 'Desativar' : 'Ativar'}">
                                <i class="fas fa-${termo.ativo ? 'ban' : 'check'}"></i>
                            </button>
                            <button onclick="termosAdmin.deletar(${termo.id})" class="text-red-600 hover:text-red-900" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    };

    // Renderizar paginação
    const renderizarPaginacao = () => {
        if (!els.pagination) return;

        if (state.pagination.totalPages <= 1) {
            els.pagination.innerHTML = '';
            return;
        }

        let html = '<div class="flex items-center justify-between w-full">';
        html += `<div class="text-sm text-gray-700">Mostrando ${((state.pagination.page - 1) * state.pagination.perPage) + 1} a ${Math.min(state.pagination.page * state.pagination.perPage, state.pagination.total)} de ${state.pagination.total}</div>`;
        html += '<div class="flex items-center gap-2">';

        // Botão anterior
        html += `<button onclick="termosAdmin.paginaAnterior()" ${state.pagination.page === 1 ? 'disabled' : ''} class="px-3 py-1 text-sm border rounded ${state.pagination.page === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'}">Anterior</button>`;

        // Páginas
        const startPage = Math.max(1, state.pagination.page - 2);
        const endPage = Math.min(state.pagination.totalPages, state.pagination.page + 2);

        if (startPage > 1) {
            html += `<button onclick="termosAdmin.irParaPagina(1)" class="px-3 py-1 text-sm border rounded hover:bg-gray-50">1</button>`;
            if (startPage > 2) html += '<span class="px-2">...</span>';
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<button onclick="termosAdmin.irParaPagina(${i})" class="px-3 py-1 text-sm border rounded ${i === state.pagination.page ? 'bg-primary-600 text-white' : 'hover:bg-gray-50'}">${i}</button>`;
        }

        if (endPage < state.pagination.totalPages) {
            if (endPage < state.pagination.totalPages - 1) html += '<span class="px-2">...</span>';
            html += `<button onclick="termosAdmin.irParaPagina(${state.pagination.totalPages})" class="px-3 py-1 text-sm border rounded hover:bg-gray-50">${state.pagination.totalPages}</button>`;
        }

        // Botão próximo
        html += `<button onclick="termosAdmin.paginaProxima()" ${state.pagination.page === state.pagination.totalPages ? 'disabled' : ''} class="px-3 py-1 text-sm border rounded ${state.pagination.page === state.pagination.totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'}">Próximo</button>`;

        html += '</div></div>';
        els.pagination.innerHTML = html;
    };

    // Abrir modal para criar
    const abrirModalCriar = () => {
        state.currentId = null;
        els.modalTitulo.textContent = 'Novo Termo';
        els.campos.id.value = '';
        els.campos.titulo.value = '';
        els.campos.versao.value = '1.0';
        if (els.campos.tipo) els.campos.tipo.value = 'inscricao';
        els.campos.conteudo.value = '';
        els.campos.ativo.checked = true;
        openModal('modal-termo');
    };

    // Editar termo
    const editar = async (id) => {
        try {
            const response = await fetch(api(`admin/termos-inscricao/get.php?id=${id}`));
            const data = await response.json();

            if (data.success && data.data) {
                const termo = data.data;
                state.currentId = termo.id;
                els.modalTitulo.textContent = 'Editar Termo';
                els.campos.id.value = termo.id;
                els.campos.titulo.value = termo.titulo || '';
                els.campos.versao.value = termo.versao || '1.0';
                if (els.campos.tipo) els.campos.tipo.value = termo.tipo || 'inscricao';
                els.campos.conteudo.value = termo.conteudo || '';
                els.campos.ativo.checked = termo.ativo;
                openModal('modal-termo');
            } else {
                showMessage('error', data.message || 'Erro ao carregar termo');
            }
        } catch (error) {
            console.error('Erro ao carregar termo:', error);
            showMessage('error', 'Erro ao carregar termo');
        }
    };

    // Visualizar termo
    const visualizar = async (id) => {
        try {
            const response = await fetch(api(`admin/termos-inscricao/get.php?id=${id}`));
            const data = await response.json();

            if (data.success && data.data) {
                const termo = data.data;
                els.modalVisualizarTitulo.textContent = termo.titulo || 'Visualizar Termo';
                const tipoLabel = { inscricao: 'Inscrição', anamnese: 'Anamnese', treino: 'Treino' }[termo.tipo] || termo.tipo || 'Inscrição';
                els.modalVisualizarConteudo.innerHTML = `<div class="mb-4"><strong>Tipo:</strong> ${tipoLabel} &nbsp;|&nbsp; <strong>Versão:</strong> ${termo.versao || '1.0'}</div><div class="border-t pt-4 mt-4">${termo.conteudo || ''}</div>`;
                openModal('modal-visualizar-termo');
            } else {
                showMessage('error', data.message || 'Erro ao carregar termo');
            }
        } catch (error) {
            console.error('Erro ao carregar termo:', error);
            showMessage('error', 'Erro ao carregar termo');
        }
    };

    // Salvar termo
    const salvar = async () => {
        const isEdit = !!state.currentId;
        const payload = {
            titulo: els.campos.titulo.value.trim(),
            conteudo: els.campos.conteudo.value.trim(),
            versao: els.campos.versao.value.trim() || '1.0',
            tipo: els.campos.tipo ? els.campos.tipo.value : 'inscricao',
            ativo: els.campos.ativo.checked
        };

        if (isEdit) {
            payload.id = state.currentId;
        }

        if (!payload.titulo || !payload.conteudo) {
            showMessage('error', 'Preencha todos os campos obrigatórios');
            return;
        }

        try {
            const endpoint = isEdit ? 'admin/termos-inscricao/update.php' : 'admin/termos-inscricao/create.php';
            const method = isEdit ? 'PUT' : 'POST';

            const response = await fetch(api(endpoint), {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                showMessage('success', `Termo ${isEdit ? 'atualizado' : 'criado'} com sucesso`);
                closeModal('modal-termo');
                carregarTermos();
            } else {
                showMessage('error', data.message || `Erro ao ${isEdit ? 'atualizar' : 'criar'} termo`);
            }
        } catch (error) {
            console.error('Erro ao salvar termo:', error);
            showMessage('error', `Erro ao ${isEdit ? 'atualizar' : 'criar'} termo`);
        }
    };

    // Toggle status
    const toggleStatus = async (id, novoStatus) => {
        try {
            const response = await fetch(api('admin/termos-inscricao/toggle_status.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, ativo: novoStatus })
            });

            const data = await response.json();

            if (data.success) {
                showMessage('success', data.message || 'Status alterado com sucesso');
                carregarTermos();
            } else {
                showMessage('error', data.message || 'Erro ao alterar status');
            }
        } catch (error) {
            console.error('Erro ao alterar status:', error);
            showMessage('error', 'Erro ao alterar status');
        }
    };

    // Deletar termo
    const deletar = async (id) => {
        if (!confirm('Tem certeza que deseja excluir este termo?')) {
            return;
        }

        try {
            const response = await fetch(api(`admin/termos-inscricao/delete.php?id=${id}`), {
                method: 'DELETE'
            });

            const data = await response.json();

            if (data.success) {
                showMessage('success', 'Termo excluído com sucesso');
                carregarTermos();
            } else {
                showMessage('error', data.message || 'Erro ao excluir termo');
            }
        } catch (error) {
            console.error('Erro ao excluir termo:', error);
            showMessage('error', 'Erro ao excluir termo');
        }
    };

    // Paginação
    const irParaPagina = (page) => {
        state.pagination.page = page;
        carregarTermos();
    };

    const paginaAnterior = () => {
        if (state.pagination.page > 1) {
            irParaPagina(state.pagination.page - 1);
        }
    };

    const paginaProxima = () => {
        if (state.pagination.page < state.pagination.totalPages) {
            irParaPagina(state.pagination.page + 1);
        }
    };

    // Event listeners
    if (els.btnNovo) {
        els.btnNovo.addEventListener('click', abrirModalCriar);
    }

    if (els.btnSalvar) {
        els.btnSalvar.addEventListener('click', salvar);
    }

    if (els.campoBusca) {
        let searchTimeout;
        els.campoBusca.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                state.filters.search = e.target.value;
                state.pagination.page = 1;
                carregarTermos();
            }, 500);
        });
    }

    if (els.filtroStatus) {
        els.filtroStatus.addEventListener('change', (e) => {
            state.filters.status = e.target.value;
            state.pagination.page = 1;
            carregarTermos();
        });
    }

    if (els.filtroTipo) {
        els.filtroTipo.addEventListener('change', (e) => {
            state.filters.tipo = e.target.value;
            state.pagination.page = 1;
            carregarTermos();
        });
    }

    // Exportar funções para uso global
    window.termosAdmin = {
        editar,
        visualizar,
        toggleStatus,
        deletar,
        irParaPagina,
        paginaAnterior,
        paginaProxima
    };

    // Inicializar
    carregarTermos();
})();
