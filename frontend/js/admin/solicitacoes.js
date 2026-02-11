(() => {
    const container = document.getElementById('solicitacoes-lista');
    if (!container) return;

    if (!window.API_BASE) {
        const path = window.location.pathname || '';
        const idx = path.indexOf('/frontend/');
        window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
    }

    const api = (endpoint) => `${window.API_BASE}/api/${endpoint}`;

    const els = {
        container,
        loading: document.getElementById('solicitacoes-loading'),
        empty: document.getElementById('solicitacoes-empty'),
        filtroStatus: document.getElementById('filtro-status'),
        btnRecarregar: document.getElementById('btn-recarregar-solicitacoes'),
        modal: document.getElementById('modal-solicitacao'),
        detalhes: document.getElementById('solicitacao-detalhes'),
        btnAnalise: document.getElementById('btn-marcar-analise'),
        btnAprovar: document.getElementById('btn-aprovar-solicitacao'),
        btnRecusar: document.getElementById('btn-recusar-solicitacao'),
        modalConfirm: document.getElementById('modal-confirmacao-solicitacao'),
        confirmTitulo: document.getElementById('modal-confirmacao-titulo'),
        confirmTexto: document.getElementById('modal-confirmacao-texto'),
        btnConfirmar: document.getElementById('btn-confirmar-acao')
    };

    const state = {
        solicitacoes: [],
        currentId: null,
        pendingAction: null
    };

    // Usar função comum do AdminUtils se disponível
    const showMessage = (type, msg) => {
        if (window.AdminUtils) {
            window.AdminUtils.showMessage(type, msg);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: type, title: msg, timer: 2200, showConfirmButton: false });
        } else {
            alert(msg);
        }
    };

    const toggleLoading = (show) => {
        els.loading?.classList.toggle('hidden', !show);
    };

    const toggleEmpty = (show) => {
        els.empty?.classList.toggle('hidden', !show);
    };

    const badgeStatus = (status) => {
        const map = {
            novo: 'badge',
            em_analise: 'badge badge-warning',
            aprovado: 'badge badge-success',
            recusado: 'badge badge-danger'
        };
        return `<span class="${map[status] || 'badge'}">${status.replace('_', ' ')}</span>`;
    };

    const renderCards = () => {
        els.container.innerHTML = '';
        if (!state.solicitacoes.length) {
            toggleEmpty(true);
            return;
        }
        toggleEmpty(false);

        const frag = document.createDocumentFragment();
        state.solicitacoes.forEach((item) => {
            const card = document.createElement('div');
            card.className = 'admin-card';
            card.innerHTML = `
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900">${item.nome_evento}</h4>
                        <p class="text-sm text-gray-500">Responsável: ${item.responsavel_nome} • ${item.responsavel_email}</p>
                        <p class="text-xs text-gray-400">Criado em: ${item.criado_em_formatado || ''}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 items-center">
                        ${badgeStatus(item.status)}
                        <button class="btn-table-primary" data-acao="detalhes" data-id="${item.id}">
                            <i class="fas fa-eye"></i> Detalhes
                        </button>
                        ${item.status !== 'aprovado' ? `
                            <button class="btn-table-secondary" data-acao="analise" data-id="${item.id}">
                                <i class="fas fa-hourglass-half text-yellow-600"></i>
                            </button>
                        ` : ''}
                        ${item.status === 'recusado' ? `
                            <button class="btn-table-danger" data-acao="deletar" data-id="${item.id}" title="Deletar solicitação">
                                <i class="fas fa-trash text-red-600"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
            frag.appendChild(card);
        });

        els.container.appendChild(frag);
    };

    const carregarSolicitacoes = async () => {
        toggleLoading(true);
        try {
            const status = els.filtroStatus?.value || '';
            const params = status ? `?status=${encodeURIComponent(status)}` : '';
            const response = await fetch(api(`admin/solicitacoes/list.php${params}`), { credentials: 'same-origin' });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Erro');
            state.solicitacoes = data.data || [];
            renderCards();
        } catch (error) {
            console.error(error);
            showMessage('error', 'Falha ao carregar solicitações');
        } finally {
            toggleLoading(false);
        }
    };

    const openModal = (id) => {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('hidden');
    };

    const closeModal = (id) => {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('hidden');
    };

    document.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', () => closeModal(el.getAttribute('data-close-modal')));
    });

    const formatLinha = (label, value) => `
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border-b pb-2">
            <span class="font-semibold text-gray-700">${label}</span>
            <span class="text-gray-600 sm:text-right">${value || '-'}</span>
        </div>
    `;

    const carregarDetalhes = async (id) => {
        try {
            const response = await fetch(api(`admin/solicitacoes/get.php?id=${id}`), { credentials: 'same-origin' });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Erro');
            const s = data.data;
            state.currentId = s.id;
            
            const getRegulamentoLink = (filename) => {
                if (!filename) return '-';
                const downloadUrl = api(`uploads/regulamentos/download.php?file=${encodeURIComponent(filename)}`);
                console.log('[SOLICITACOES] URL do regulamento:', downloadUrl, '| Arquivo:', filename);
                return `<a class="text-brand-green underline" target="_blank" href="${downloadUrl}"><i class="fas fa-file-pdf mr-1"></i>${filename}</a>`;
            };
            
            const html = `
                <div class="space-y-3">
                    <h4 class="text-lg font-semibold text-gray-900">Responsável</h4>
                    ${formatLinha('Nome', s.responsavel_nome)}
                    ${formatLinha('Email', s.responsavel_email)}
                    ${formatLinha('Telefone', s.responsavel_telefone)}
                    ${formatLinha('Documento', s.responsavel_documento)}
                    ${formatLinha('RG', s.responsavel_rg)}
                    ${formatLinha('Cargo', s.responsavel_cargo)}
                </div>
                <div class="space-y-3 pt-4">
                    <h4 class="text-lg font-semibold text-gray-900">Evento</h4>
                    ${formatLinha('Nome do evento', s.nome_evento)}
                    ${formatLinha('Cidade/UF', `${s.cidade_evento} / ${s.uf_evento}`)}
                    ${formatLinha('Data prevista', s.data_prevista || 'Não informado')}
                    ${formatLinha('Estimativa participantes', s.estimativa_participantes || 'Não informado')}
                    ${formatLinha('Modalidade', s.modalidade_esportiva)}
                    ${formatLinha('Descrição', s.descricao_evento)}
                    ${formatLinha('Necessidades', s.necessidades)}
                </div>
                <div class="space-y-3 pt-4">
                    <h4 class="text-lg font-semibold text-gray-900">Documentos</h4>
                    ${formatLinha('Regulamento', s.regulamento_status)}
                    ${formatLinha('Link regulamento', getRegulamentoLink(s.link_regulamento))}
                    ${formatLinha('Autorização', s.possui_autorizacao)}
                    ${formatLinha('Protocolo', s.link_autorizacao)}
                    ${formatLinha('Documentos extras', s.documentos_link ? `<a class="text-brand-green underline" target="_blank" href="${s.documentos_link}">${s.documentos_link}</a>` : '-') }
                </div>
                <div class="space-y-3 pt-4">
                    <h4 class="text-lg font-semibold text-gray-900">Outros</h4>
                    ${formatLinha('Indicação', s.indicacao)}
                    ${formatLinha('Preferência contato', s.preferencia_contato)}
                    ${formatLinha('Status', badgeStatus(s.status))}
                </div>
            `;

            els.detalhes.innerHTML = html;
            
            const isAprovado = s.status === 'aprovado';
            
            els.btnAnalise.style.display = isAprovado ? 'none' : '';
            els.btnAprovar.style.display = isAprovado ? 'none' : '';
            els.btnRecusar.style.display = isAprovado ? 'none' : '';
            
            els.btnAnalise.disabled = isAprovado;
            els.btnAprovar.disabled = isAprovado;
            els.btnRecusar.disabled = isAprovado;
            
            const footer = els.modal.querySelector('.admin-modal-footer .space-x-2:last-child');
            const mensagemExistente = els.modal.querySelector('.bg-green-50');
            
            if (mensagemExistente) {
                mensagemExistente.remove();
            }
            
            if (isAprovado && footer) {
                const mensagemAprovado = document.createElement('div');
                mensagemAprovado.className = 'bg-green-50 border border-green-200 rounded-lg p-4 text-center w-full';
                mensagemAprovado.innerHTML = `
                    <div class="flex items-center justify-center gap-2 text-green-700">
                        <i class="fas fa-check-circle text-xl"></i>
                        <span class="font-semibold">Esta solicitação já foi aprovada e o responsável foi criado.</span>
                    </div>
                `;
                footer.insertBefore(mensagemAprovado, footer.firstChild);
            }
            
            openModal('modal-solicitacao');
        } catch (error) {
            console.error(error);
            showMessage('error', 'Não foi possível carregar a solicitação');
        }
    };

    const atualizarStatus = async (status) => {
        if (!state.currentId) return;
        try {
            const response = await fetch(api('admin/solicitacoes/update_status.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ id: state.currentId, status })
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Erro');
            showMessage('success', 'Status atualizado');
            closeModal('modal-solicitacao');
            await carregarSolicitacoes();
        } catch (error) {
            console.error(error);
            showMessage('error', error.message || 'Falha ao atualizar status');
        }
    };

    const aprovarSolicitacao = async () => {
        if (!state.currentId) return;
        els.btnAprovar.disabled = true;
        try {
            const response = await fetch(api('admin/solicitacoes/approve.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ id: state.currentId })
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Erro ao aprovar');
            if (typeof Swal !== 'undefined') {
                const senha = data.temp_password || '';
                Swal.fire({
                    icon: 'success',
                    title: 'Organizador criado',
                    html: senha ? `
                        <div class="space-y-3">
                            <p>Senha temporária gerada:</p>
                            <div class="flex items-center gap-2 justify-center">
                                <code class="px-3 py-2 bg-gray-100 rounded text-lg">${senha}</code>
                                <button id="copy-temp-password" class="btn-table-primary">Copiar</button>
                            </div>
                        </div>
                    ` : 'Responsável criado e notificado',
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
                showMessage('success', 'Responsável criado. Senha: ' + (data.temp_password || ''));
            }
            closeModal('modal-solicitacao');
            await carregarSolicitacoes();
        } catch (error) {
            console.error(error);
            showMessage('error', error.message || 'Falha ao aprovar solicitação');
        } finally {
            els.btnAprovar.disabled = false;
        }
    };

    const deletarSolicitacao = async () => {
        if (!state.currentId) return;
        try {
            const response = await fetch(api('admin/solicitacoes/delete.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ id: state.currentId })
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Erro ao deletar');
            showMessage('success', 'Solicitação deletada com sucesso');
            closeModal('modal-confirmacao-solicitacao');
            await carregarSolicitacoes();
        } catch (error) {
            console.error(error);
            showMessage('error', error.message || 'Falha ao deletar solicitação');
        }
    };

    const confirmarAcao = (tipo) => {
        state.pendingAction = tipo;
        const map = {
            analise: {
                titulo: 'Marcar como em análise',
                texto: 'Deseja mover esta solicitação para o status "em análise"?'
            },
            recusar: {
                titulo: 'Recusar solicitação',
                texto: 'Tem certeza que deseja recusar esta solicitação?'
            },
            deletar: {
                titulo: 'Deletar solicitação',
                texto: 'Tem certeza que deseja deletar permanentemente esta solicitação? Esta ação não pode ser desfeita.'
            }
        };
        const config = map[tipo];
        if (!config) return;
        els.confirmTitulo.textContent = config.titulo;
        els.confirmTexto.textContent = config.texto;
        openModal('modal-confirmacao-solicitacao');
    };

    els.btnConfirmar.addEventListener('click', async () => {
        if (!state.pendingAction) return;
        const acao = state.pendingAction;
        state.pendingAction = null;
        
        if (acao === 'analise') {
            await atualizarStatus('em_analise');
            closeModal('modal-confirmacao-solicitacao');
        } else if (acao === 'recusar') {
            await atualizarStatus('recusado');
            closeModal('modal-confirmacao-solicitacao');
        } else if (acao === 'deletar') {
            await deletarSolicitacao();
        }
    });

    els.btnAnalise.addEventListener('click', () => confirmarAcao('analise'));
    els.btnRecusar.addEventListener('click', () => confirmarAcao('recusar'));
    els.btnAprovar.addEventListener('click', aprovarSolicitacao);

    els.filtroStatus?.addEventListener('change', carregarSolicitacoes);
    els.btnRecarregar?.addEventListener('click', carregarSolicitacoes);

    container.addEventListener('click', (event) => {
        const btn = event.target.closest('button[data-acao]');
        if (!btn) return;
        const id = Number(btn.getAttribute('data-id'));
        const acao = btn.getAttribute('data-acao');
        if (acao === 'detalhes') {
            carregarDetalhes(id);
        } else if (acao === 'analise') {
            state.currentId = id;
            confirmarAcao('analise');
        } else if (acao === 'deletar') {
            state.currentId = id;
            confirmarAcao('deletar');
        }
    });

    carregarSolicitacoes();
})();

