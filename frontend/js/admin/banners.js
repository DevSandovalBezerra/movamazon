(() => {
    if (!document.getElementById('banners-lista')) {
        return;
    }

    if (!window.API_BASE) {
        const path = window.location.pathname || '';
        const idx = path.indexOf('/frontend/');
        window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
    }

    // Função para normalizar caminho de imagem usando o caminho base do projeto
    const normalizarCaminhoImagem = (caminho) => {
        if (!caminho) return '';
        // Se já é URL completa, retornar como está
        if (caminho.startsWith('http://') || caminho.startsWith('https://')) {
            return caminho;
        }
        // Se começa com /, usar caminho base do projeto
        if (caminho.startsWith('/')) {
            return window.API_BASE + caminho;
        }
        // Se não começa com /, adicionar caminho base + /
        return window.API_BASE + '/' + caminho;
    };

    const api = (endpoint) => `${window.API_BASE}/api/${endpoint}`;

    const state = {
        banners: [],
        currentId: null,
        removeId: null
    };

    const els = {
        lista: document.getElementById('banners-lista'),
        loading: document.getElementById('banners-loading'),
        empty: document.getElementById('banners-empty'),
        btnAtualizar: document.getElementById('btn-atualizar-banners'),
        btnNovo: document.getElementById('btn-novo-banner'),
        modal: document.getElementById('modal-banner'),
        modalTitulo: document.getElementById('modal-banner-titulo'),
        campos: {
            titulo: document.getElementById('banner-titulo'),
            imagem: document.getElementById('banner-imagem'),
            imagemFile: document.getElementById('banner-imagem-file'),
            imagemPreview: document.getElementById('banner-imagem-preview'),
            imagemPreviewImg: document.getElementById('banner-imagem-preview-img'),
            descricao: document.getElementById('banner-descricao'),
            link: document.getElementById('banner-link'),
            textoBotao: document.getElementById('banner-texto-botao'),
            dataInicio: document.getElementById('banner-data-inicio'),
            dataFim: document.getElementById('banner-data-fim'),
            ativo: document.getElementById('banner-ativo'),
            targetBlank: document.getElementById('banner-target-blank')
        },
        btnSalvar: document.getElementById('btn-salvar-banner'),
        modalConfirm: document.getElementById('modal-confirmacao'),
        btnConfirmRemocao: document.getElementById('btn-confirmar-remocao'),
        modalConfirmBody: document.querySelector('#modal-confirmacao .admin-modal-body')
    };

    // Usar função comum do AdminUtils se disponível
    const showMessage = (type, message) => {
        if (window.AdminUtils) {
            window.AdminUtils.showMessage(type, message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: type, title: message, timer: 2200, showConfirmButton: false });
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
        if (modal) modal.classList.remove('hidden');
    };

    const closeModal = (id) => {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('hidden');
    };

    document.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', () => closeModal(el.getAttribute('data-close-modal')));
    });

    const limparFormulario = () => {
        state.currentId = null;
        els.modalTitulo.textContent = 'Novo banner';
        Object.values(els.campos).forEach((campo) => {
            if (campo && campo.type === 'checkbox') {
                campo.checked = campo === els.campos.ativo;
            } else if (campo && campo.value !== undefined) {
                campo.value = '';
            }
        });
        if (els.campos.imagemFile) els.campos.imagemFile.value = '';
        if (els.campos.imagemPreview) els.campos.imagemPreview.classList.add('hidden');
        if (els.campos.ativo) els.campos.ativo.checked = true;
    };

    const preencherFormulario = (banner) => {
        state.currentId = banner.id;
        els.modalTitulo.textContent = `Editar banner #${banner.id}`;
        if (els.campos.titulo) els.campos.titulo.value = banner.titulo || '';
        if (els.campos.imagem) els.campos.imagem.value = banner.imagem || '';
        if (els.campos.descricao) els.campos.descricao.value = banner.descricao || '';
        if (els.campos.link) els.campos.link.value = banner.link || '';
        if (els.campos.textoBotao) els.campos.textoBotao.value = banner.texto_botao || '';
        if (els.campos.dataInicio) els.campos.dataInicio.value = banner.data_inicio ? banner.data_inicio.replace(' ', 'T').slice(0, 16) : '';
        if (els.campos.dataFim) els.campos.dataFim.value = banner.data_fim ? banner.data_fim.replace(' ', 'T').slice(0, 16) : '';
        if (els.campos.ativo) els.campos.ativo.checked = !!Number(banner.ativo);
        if (els.campos.targetBlank) els.campos.targetBlank.checked = !!Number(banner.target_blank);
        if (els.campos.imagemFile) els.campos.imagemFile.value = '';
        // Exibir preview da imagem se existir
        if (banner.imagem && els.campos.imagemPreview && els.campos.imagemPreviewImg) {
            // Normalizar caminho da imagem usando função helper
            const imagemPath = normalizarCaminhoImagem(banner.imagem);
            // Definir src e mostrar preview
            els.campos.imagemPreviewImg.src = imagemPath;
            els.campos.imagemPreviewImg.onerror = function() {
                console.error('Erro ao carregar imagem:', imagemPath);
                this.style.display = 'none';
            };
            els.campos.imagemPreviewImg.onload = function() {
                this.style.display = 'block';
            };
            els.campos.imagemPreview.classList.remove('hidden');
        } else if (els.campos.imagemPreview) {
            els.campos.imagemPreview.classList.add('hidden');
        }
    };

    // Upload de imagem
    const uploadImagem = async (file) => {
        const formData = new FormData();
        formData.append('imagem', file);
        
        try {
            const response = await fetch(api('admin/banners/upload.php'), {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erro no upload');
            }
            return data.path;
        } catch (error) {
            console.error(error);
            showMessage('error', error.message || 'Erro ao fazer upload da imagem');
            throw error;
        }
    };

    const renderBanners = () => {
        if (!els.lista) return;
        els.lista.innerHTML = '';
        if (!state.banners.length) {
            toggleEmpty(true);
            return;
        }
        toggleEmpty(false);

        const frag = document.createDocumentFragment();
        state.banners.forEach((banner, index) => {
            const card = document.createElement('div');
            card.className = 'admin-banner-card';
            card.innerHTML = `
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="w-full lg:w-56 h-32 bg-gray-100 rounded overflow-hidden flex items-center justify-center">
                        ${banner.imagem ? (() => {
                            const imgPath = normalizarCaminhoImagem(banner.imagem);
                            const tituloEscapado = (banner.titulo || 'Banner').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                            console.log('[BANNERS] Renderizando banner:', banner.titulo, '| Caminho original:', banner.imagem, '| Caminho normalizado:', imgPath);
                            // Usar onerror simplificado - já tentamos com caminho normalizado
                            return `<img src="${imgPath}" class="w-full h-full object-cover" alt="${tituloEscapado}" loading="lazy" 
                                onerror="console.error('[BANNERS] Erro ao carregar imagem após normalização:', '${imgPath.replace(/'/g, "\\'")}'); 
                                this.onerror = null; 
                                this.parentElement.innerHTML = '<span class=\\'text-gray-400 text-sm\\'>Erro ao carregar imagem</span>';" />`;
                        })() : '<span class="text-gray-400 text-sm">Sem imagem</span>'}
                    </div>
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center gap-3">
                            <h4 class="text-lg font-semibold text-gray-900">${banner.titulo}</h4>
                            ${banner.ativo ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-muted">Inativo</span>'}
                        </div>
                        <p class="text-sm text-gray-600">${banner.descricao || ''}</p>
                        <div class="flex flex-wrap gap-3 text-xs text-gray-500">
                            ${banner.link ? `<span><i class="fas fa-link mr-1"></i>${banner.link}</span>` : ''}
                            ${banner.texto_botao ? `<span><i class="fas fa-font mr-1"></i>${banner.texto_botao}</span>` : ''}
                            ${banner.data_inicio ? `<span><i class="fas fa-clock mr-1"></i>Início: ${banner.data_inicio}</span>` : ''}
                            ${banner.data_fim ? `<span><i class="fas fa-clock mr-1"></i>Fim: ${banner.data_fim}</span>` : ''}
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 justify-between">
                        <div class="flex gap-2">
                            <button class="btn-table-primary" data-acao="editar" data-id="${banner.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-table-secondary" data-acao="toggle" data-id="${banner.id}">
                                <i class="fas ${banner.ativo ? 'fa-eye' : 'fa-eye-slash'}"></i>
                            </button>
                            <button class="btn-table-danger" data-acao="remover" data-id="${banner.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <button class="btn-table-secondary" data-acao="mover-cima" data-index="${index}" ${index === 0 ? 'disabled' : ''}>
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button class="btn-table-secondary" data-acao="mover-baixo" data-index="${index}" ${index === state.banners.length - 1 ? 'disabled' : ''}>
                                <i class="fas fa-arrow-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            frag.appendChild(card);
        });

        els.lista.appendChild(frag);
    };

    const carregarBanners = async () => {
        toggleLoading(true);
        try {
            const response = await fetch(api('admin/banners/list.php'), { credentials: 'same-origin' });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erro ao carregar');
            }
            state.banners = data.data || [];
            renderBanners();
        } catch (error) {
            console.error(error);
            showMessage('error', 'Falha ao carregar banners');
        } finally {
            toggleLoading(false);
        }
    };

    const salvarBanner = async () => {
        const body = {
            titulo: els.campos.titulo.value.trim(),
            imagem: els.campos.imagem.value.trim(),
            descricao: els.campos.descricao.value.trim(),
            link: els.campos.link.value.trim(),
            texto_botao: els.campos.textoBotao.value.trim(),
            data_inicio: els.campos.dataInicio.value || null,
            data_fim: els.campos.dataFim.value || null,
            ativo: els.campos.ativo.checked ? 1 : 0,
            target_blank: els.campos.targetBlank.checked ? 1 : 0
        };

        if (!body.titulo || !body.imagem) {
            showMessage('error', 'Título e imagem são obrigatórios.');
            return;
        }

        const endpoint = state.currentId ? 'update' : 'create';
        if (state.currentId) {
            body.id = state.currentId;
        }

        try {
            els.btnSalvar.disabled = true;
            const response = await fetch(api(`admin/banners/${endpoint}.php`), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(body)
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erro ao salvar');
            }
            showMessage('success', data.message || 'Salvo!');
            closeModal('modal-banner');
            await carregarBanners();
        } catch (error) {
            console.error(error);
            showMessage('error', error.message || 'Falha ao salvar banner');
        } finally {
            els.btnSalvar.disabled = false;
        }
    };

    const confirmarRemocao = async () => {
        if (!state.removeId) {
            console.error('[BANNERS] Tentativa de remover banner sem ID definido');
            return;
        }
        
        console.log('[BANNERS] Iniciando remoção do banner ID:', state.removeId);
        
        try {
            els.btnConfirmRemocao.disabled = true;
            els.btnConfirmRemocao.textContent = 'Removendo...';
            
            const payload = { id: state.removeId };
            console.log('[BANNERS] Enviando requisição para deletar:', payload);
            
            const response = await fetch(api('admin/banners/delete.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            
            console.log('[BANNERS] Resposta recebida - Status:', response.status);
            
            const data = await response.json();
            console.log('[BANNERS] Dados da resposta:', data);
            
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erro ao remover banner');
            }
            
            console.log('[BANNERS] ✓ Banner removido com sucesso');
            showMessage('success', data.message || 'Banner removido com sucesso');
            closeModal('modal-confirmacao');
            
            // Aguardar um pouco antes de recarregar para o usuário ver a mensagem
            setTimeout(async () => {
                await carregarBanners();
            }, 500);
        } catch (error) {
            console.error('[BANNERS] ✗ Erro ao remover banner:', error);
            showMessage('error', error.message || 'Falha ao remover banner. Verifique o console para mais detalhes.');
        } finally {
            els.btnConfirmRemocao.disabled = false;
            els.btnConfirmRemocao.textContent = 'Remover';
            state.removeId = null;
        }
    };

    const reordenar = async () => {
        const ordem = state.banners.map((b) => b.id);
        try {
            await fetch(api('admin/banners/reorder.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ ordem })
            });
        } catch (error) {
            console.error(error);
            showMessage('error', 'Erro ao atualizar ordem');
        }
    };

    const mover = async (index, direction) => {
        const newIndex = index + direction;
        if (newIndex < 0 || newIndex >= state.banners.length) return;
        const temp = state.banners[index];
        state.banners[index] = state.banners[newIndex];
        state.banners[newIndex] = temp;
        renderBanners();
        await reordenar();
    };

    const toggleAtivo = async (id) => {
        const banner = state.banners.find((b) => b.id === id);
        if (!banner) return;
        try {
            const payload = {
                id,
                titulo: banner.titulo,
                imagem: banner.imagem,
                descricao: banner.descricao,
                link: banner.link,
                texto_botao: banner.texto_botao,
                data_inicio: banner.data_inicio,
                data_fim: banner.data_fim,
                target_blank: banner.target_blank,
                ativo: banner.ativo ? 0 : 1
            };
            const response = await fetch(api('admin/banners/update.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erro ao atualizar');
            }
            banner.ativo = banner.ativo ? 0 : 1;
            renderBanners();
        } catch (error) {
            console.error(error);
            showMessage('error', 'Falha ao alterar status');
        }
    };

    // Eventos UI
    if (els.btnNovo) {
        els.btnNovo.addEventListener('click', () => {
            limparFormulario();
            openModal('modal-banner');
        });
    }

    // Event listener para upload de imagem
    if (els.campos.imagemFile) {
        els.campos.imagemFile.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            
            if (file.size > 5 * 1024 * 1024) {
                showMessage('error', 'Arquivo muito grande. Máximo: 5MB');
                e.target.value = '';
                return;
            }
            
            try {
                els.campos.imagemFile.disabled = true;
                const path = await uploadImagem(file);
                if (els.campos.imagem) els.campos.imagem.value = path;
                if (els.campos.imagemPreview && els.campos.imagemPreviewImg) {
                    // Normalizar caminho usando função helper
                    const imgPath = normalizarCaminhoImagem(path);
                    els.campos.imagemPreviewImg.src = imgPath;
                    els.campos.imagemPreviewImg.onerror = function() {
                        console.error('Erro ao carregar imagem após upload:', imgPath);
                        this.style.display = 'none';
                    };
                    els.campos.imagemPreviewImg.onload = function() {
                        this.style.display = 'block';
                    };
                    els.campos.imagemPreview.classList.remove('hidden');
                }
                showMessage('success', 'Imagem enviada com sucesso!');
            } catch (error) {
                e.target.value = '';
            } finally {
                els.campos.imagemFile.disabled = false;
            }
        });
    }

    // Preview quando colar URL ou digitar caminho
    if (els.campos.imagem) {
        els.campos.imagem.addEventListener('input', (e) => {
            const url = e.target.value.trim();
            if (url && els.campos.imagemPreview && els.campos.imagemPreviewImg) {
                // Normalizar caminho usando função helper
                const imgPath = normalizarCaminhoImagem(url);
                // Tentar carregar a imagem
                els.campos.imagemPreviewImg.src = imgPath;
                els.campos.imagemPreviewImg.onerror = function() {
                    console.error('Erro ao carregar preview da imagem:', imgPath);
                    // Não esconder o preview, mas mostrar mensagem de erro
                };
                els.campos.imagemPreviewImg.onload = function() {
                    this.style.display = 'block';
                };
                els.campos.imagemPreview.classList.remove('hidden');
            } else if (els.campos.imagemPreview) {
                els.campos.imagemPreview.classList.add('hidden');
            }
        });
    }

    if (els.btnSalvar) {
        els.btnSalvar.addEventListener('click', salvarBanner);
        console.log('[BANNERS] Event listener adicionado ao botão salvar');
    } else {
        console.error('[BANNERS] Botão salvar não encontrado!');
    }
    
    if (els.btnConfirmRemocao) {
        els.btnConfirmRemocao.addEventListener('click', confirmarRemocao);
        console.log('[BANNERS] Event listener adicionado ao botão confirmar remoção');
    } else {
        console.error('[BANNERS] Botão confirmar remoção não encontrado! ID: btn-confirmar-remocao');
    }
    
    if (els.btnAtualizar) {
        els.btnAtualizar.addEventListener('click', carregarBanners);
        console.log('[BANNERS] Event listener adicionado ao botão atualizar');
    } else {
        console.error('[BANNERS] Botão atualizar não encontrado!');
    }
    
    // Verificar se o modal existe
    if (els.modalConfirm) {
        console.log('[BANNERS] Modal de confirmação encontrado');
    } else {
        console.error('[BANNERS] Modal de confirmação não encontrado! ID: modal-confirmacao');
    }

    els.lista.addEventListener('click', (event) => {
        const btn = event.target.closest('button[data-acao]');
        if (!btn) return;
        const acao = btn.getAttribute('data-acao');
        if (acao === 'editar') {
            const id = Number(btn.getAttribute('data-id'));
            const banner = state.banners.find((b) => b.id === id);
            if (!banner) return;
            preencherFormulario(banner);
            openModal('modal-banner');
        } else if (acao === 'toggle') {
            toggleAtivo(Number(btn.getAttribute('data-id')));
        } else if (acao === 'remover') {
            const id = Number(btn.getAttribute('data-id'));
            const banner = state.banners.find((b) => b.id === id);
            
            console.log('[BANNERS] Botão remover clicado para banner ID:', id);
            console.log('[BANNERS] Banner encontrado:', banner);
            
            if (!banner) {
                console.error('[BANNERS] Banner não encontrado no state para ID:', id);
                showMessage('error', 'Banner não encontrado');
                return;
            }
            
            state.removeId = id;
            
            // Atualizar mensagem do modal com o título do banner
            if (els.modalConfirmBody) {
                els.modalConfirmBody.innerHTML = `
                    <p class="text-gray-700 font-semibold mb-2">Tem certeza que deseja remover este banner?</p>
                    <p class="text-gray-600"><strong>Título:</strong> ${banner.titulo || 'Sem título'}</p>
                    <p class="text-sm text-red-600 mt-2">⚠️ Esta ação não pode ser desfeita.</p>
                `;
            } else {
                console.warn('[BANNERS] Corpo do modal de confirmação não encontrado');
            }
            
            openModal('modal-confirmacao');
        } else if (acao === 'mover-cima') {
            mover(Number(btn.getAttribute('data-index')), -1);
        } else if (acao === 'mover-baixo') {
            mover(Number(btn.getAttribute('data-index')), 1);
        }
    });

    carregarBanners();
})();

