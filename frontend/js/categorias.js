if (window.getApiBase) { window.getApiBase(); }
// Gerenciador de Categorias - MovAmazon
let categoriasData = [];
let categoriaEditando = null;

// ===== FUNÃƒÆ’ââ‚¬Â¡ÃƒÆ’ââ‚¬Â¢ES DE CONTROLE DA JANELA CORTINA =====

function abrirPanelCategorias() {
    const panel = document.getElementById('categoriasPanel');
    const overlay = document.getElementById('categoriasOverlay');

    // Mostrar overlay e panel
    overlay.classList.remove('hidden');
    panel.classList.remove('translate-x-full');
    panel.classList.add('abrir');

    // Carregar categorias
    carregarCategorias();

    // Configurar eventos dos toggles
    configurarToggles();

    // Configurar contadores de caracteres
    configurarContadores();

    // Configurar validaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de idade
    configurarValidacaoIdade();
}

function fecharPanelCategorias() {
    const panel = document.getElementById('categoriasPanel');
    const overlay = document.getElementById('categoriasOverlay');

    // Fechar panel
    panel.classList.add('translate-x-full');
    panel.classList.remove('abrir');

    // Ocultar overlay apÃƒÆ’Ã‚Â³s animaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
    setTimeout(() => {
        overlay.classList.add('hidden');
    }, 300);

    // Limpar formulÃƒÆ’Ã‚Â¡rio
    limparFormCategoria();
}

// ===== FUNÃƒÆ’ââ‚¬Â¡ÃƒÆ’ââ‚¬Â¢ES DE TOGGLE =====

function configurarToggles() {
    // Toggle de status
    const statusToggle = document.getElementById('statusCategoria');
    const statusLabel = document.getElementById('statusLabel');

    statusToggle.addEventListener('change', function () {
        statusLabel.textContent = this.checked ? 'Ativo' : 'Inativo';
    });

    // Toggle de exibiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o geral
    const exibirGeralToggle = document.getElementById('exibirInscricaoGeral');
    exibirGeralToggle.addEventListener('change', function () {
        // LÃƒÆ’Ã‚Â³gica adicional se necessÃƒÆ’Ã‚Â¡rio
    });

    // Toggle de exibiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o grupos
    const exibirGruposToggle = document.getElementById('exibirInscricaoGrupos');
    exibirGruposToggle.addEventListener('change', function () {
        // LÃƒÆ’Ã‚Â³gica adicional se necessÃƒÆ’Ã‚Â¡rio
    });
}

// ===== FUNÃƒÆ’ââ‚¬Â¡ÃƒÆ’ââ‚¬Â¢ES DE CONTADORES =====

function configurarContadores() {
    const nomeInput = document.getElementById('nomeCategoria');
    const descricaoInput = document.getElementById('descricaoCategoria');
    const contadorNome = document.getElementById('contadorNome');
    const contadorDescricao = document.getElementById('contadorDescricao');

    nomeInput.addEventListener('input', function () {
        const restantes = 350 - this.value.length;
        contadorNome.textContent = restantes;
        contadorNome.className = restantes < 50 ? 'text-red-400' : 'text-gray-400';
    });

    descricaoInput.addEventListener('input', function () {
        const restantes = 450 - this.value.length;
        contadorDescricao.textContent = restantes;
        contadorDescricao.className = restantes < 50 ? 'text-red-400' : 'text-gray-400';
    });
}

// ===== FUNÃƒÆ’ââ‚¬Â¡ÃƒÆ’ââ‚¬Â¢ES DE VALIDAÃƒÆ’ââ‚¬Â¡ÃƒÆ’Ã†â€™O DE IDADE =====

function configurarValidacaoIdade() {
    const idadeMin = document.getElementById('idadeMin');
    const idadeMax = document.getElementById('idadeMax');

    // Validar idade mÃƒÆ’Ã‚Â­nima nÃƒÆ’Ã‚Â£o pode ser maior que mÃƒÆ’Ã‚Â¡xima
    idadeMin.addEventListener('change', function () {
        const min = parseInt(this.value) || 0;
        const max = parseInt(idadeMax.value) || 100;

        if (min > max && max > 0) {
            idadeMax.value = min;
        }
    });

    // Validar idade mÃƒÆ’Ã‚Â¡xima nÃƒÆ’Ã‚Â£o pode ser menor que mÃƒÆ’Ã‚Â­nima
    idadeMax.addEventListener('change', function () {
        const max = parseInt(this.value) || 100;
        const min = parseInt(idadeMin.value) || 0;

        if (max < min && min > 0) {
            idadeMin.value = max;
        }
    });
}



// ===== FUNÃƒÆ’ââ‚¬Â¡ÃƒÆ’ââ‚¬Â¢ES DE FORMULÃƒÆ’Ã‚ÂRIO =====

function limparFormCategoria() {
    document.getElementById('formCategoria').reset();
    document.getElementById('categoriaIdEdit').value = '';
    document.getElementById('formCategoriaTitulo').textContent = 'Nova Categoria';

    // Resetar contadores
    document.getElementById('contadorNome').textContent = '350';
    document.getElementById('contadorDescricao').textContent = '450';

    // Resetar campos de idade para valores padrÃƒÆ’Ã‚Â£o
    document.getElementById('idadeMin').value = '0';
    document.getElementById('idadeMax').value = '100';

    categoriaEditando = null;
}

function editarCategoria(categoria) {
    categoriaEditando = categoria;

    // Preencher formulÃƒÆ’Ã‚Â¡rio
    document.getElementById('categoriaIdEdit').value = categoria.id;
    document.getElementById('nomeCategoria').value = categoria.nome;
    document.getElementById('statusCategoria').checked = categoria.ativo;
    document.getElementById('descricaoCategoria').value = categoria.descricao || '';
    document.getElementById('exibirInscricaoGeral').checked = categoria.exibir_inscricao_geral;
    document.getElementById('exibirInscricaoGrupos').checked = categoria.exibir_inscricao_grupos;
    document.getElementById('tituloLinkOculto').value = categoria.titulo_link_oculto || '';

    // Configurar campos de idade
    document.getElementById('idadeMin').value = categoria.idade_min || 0;
    document.getElementById('idadeMax').value = categoria.idade_max || 100;

    // Configurar tipo de pÃƒÆ’Ã‚Âºblico
    document.getElementById('tipoPublico').value = categoria.tipo_publico || 'ambos';

    // Configurar desconto para idosos
    document.getElementById('descontoIdoso').checked = categoria.desconto_idoso || false;

    // Atualizar tÃƒÆ’Ã‚Â­tulo
    document.getElementById('formCategoriaTitulo').textContent = 'Editar Categoria';

    // Atualizar contadores
    document.getElementById('contadorNome').textContent = 350 - categoria.nome.length;
    document.getElementById('contadorDescricao').textContent = 450 - (categoria.descricao?.length || 0);
}

// ===== FUNÃƒÆ’ââ‚¬Â¡ÃƒÆ’ââ‚¬Â¢ES DE CRUD =====

async function salvarCategoria() {
    const form = document.getElementById('formCategoria');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData();
    formData.append('nome', document.getElementById('nomeCategoria').value);
    formData.append('ativo', document.getElementById('statusCategoria').checked ? 1 : 0);
    formData.append('descricao', document.getElementById('descricaoCategoria').value);
    formData.append('exibir_inscricao_geral', document.getElementById('exibirInscricaoGeral').checked ? 1 : 0);
    formData.append('exibir_inscricao_grupos', document.getElementById('exibirInscricaoGrupos').checked ? 1 : 0);
    formData.append('titulo_link_oculto', document.getElementById('tituloLinkOculto').value);

    // Campos da tabela existente
    formData.append('tipo_publico', document.getElementById('tipoPublico').value);
    formData.append('idade_min', document.getElementById('idadeMin').value || 0);
    formData.append('idade_max', document.getElementById('idadeMax').value || 100);
    formData.append('desconto_idoso', document.getElementById('descontoIdoso').checked ? 1 : 0);

    // Escopo por evento: obter do seletor de eventos da pÃƒÆ’Ã‚Â¡gina
    const eventoSelect = document.getElementById('filtroEvento');
    const eventoId = parseInt(eventoSelect?.value || '0', 10);
    if (!eventoId) {
        Swal.fire({
            title: 'AtenÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o',
            text: 'Selecione um evento antes de salvar a categoria.',
            icon: 'warning',
            confirmButtonColor: '#F59E0B'
        });
        return;
    }
    formData.append('evento_id', eventoId);

    // ID para ediÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
    if (categoriaEditando) {
        formData.append('id', categoriaEditando.id);
    }

    try {
        const url = categoriaEditando ?
            (window.API_BASE || '/api') + '/categoria/update.php' :
            (window.API_BASE || '/api') + '/categoria/create.php';

        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                title: 'Sucesso! ÃƒÂ°Ã…Â¸Ã…Â½ââ‚¬Â°',
                text: categoriaEditando ? 'Categoria atualizada com sucesso!' : 'Categoria criada com sucesso!',
                icon: 'success',
                confirmButtonColor: '#10B981'
            });

            limparFormCategoria();
            carregarCategorias();
            try {
                const eventoSelect = document.getElementById('filtroEvento');
                const eventoId = parseInt(eventoSelect?.value || '0', 10);
                document.dispatchEvent(new CustomEvent('categorias-atualizadas', {
                    detail: {
                        eventoId
                    }
                }));
            } catch (e) {
                console.warn('categorias.js - nÃƒÆ’Ã‚Â£o foi possÃƒÆ’Ã‚Â­vel disparar evento de atualizaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de categorias', e);
            }
        } else {
            throw new Error(data.message || 'Erro ao salvar categoria');
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
            title: 'Erro! ÃƒÂ¢Ã‚ÂÃ…â€™',
            text: error.message || 'Ocorreu um erro ao salvar a categoria',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
}

async function excluirCategoria(categoriaId) {
    const result = await Swal.fire({
        title: 'Confirmar ExclusÃƒÆ’Ã‚Â£o',
        text: 'Tem certeza que deseja excluir esta categoria? Esta aÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o nÃƒÆ’Ã‚Â£o pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch((window.API_BASE || '/api') + '/categoria/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: categoriaId
                })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    title: 'ExcluÃƒÆ’Ã‚Â­da! ÃƒÂ¢Ã…â€œââ‚¬Â¦',
                    text: 'Categoria excluÃƒÆ’Ã‚Â­da com sucesso!',
                    icon: 'success',
                    confirmButtonColor: '#10B981'
                });

                carregarCategorias();
                try {
                    const eventoSelect = document.getElementById('filtroEvento');
                    const eventoId = parseInt(eventoSelect?.value || '0', 10);
                    document.dispatchEvent(new CustomEvent('categorias-atualizadas', {
                        detail: {
                            eventoId
                        }
                    }));
                } catch (e) {
                    console.warn('categorias.js - nÃƒÆ’Ã‚Â£o foi possÃƒÆ’Ã‚Â­vel disparar evento apÃƒÆ’Ã‚Â³s exclusÃƒÆ’Ã‚Â£o', e);
                }
            } else {
                throw new Error(data.message || 'Erro ao excluir categoria');
            }
        } catch (error) {
            console.error('Erro:', error);
            Swal.fire({
                title: 'Erro! ÃƒÂ¢Ã‚ÂÃ…â€™',
                text: error.message || 'Ocorreu um erro ao excluir a categoria',
                icon: 'error',
                confirmButtonColor: '#EF4444'
            });
        }
    }
}

// ===== FUNÃƒÆ’ââ‚¬Â¡ÃƒÆ’ââ‚¬Â¢ES DE CARREGAMENTO =====

async function carregarCategorias() {
    try {
        const eventoSelect = document.getElementById('filtroEvento');
        const eventoId = parseInt(eventoSelect?.value || '0', 10);
        if (!eventoId) {
            // Sem evento selecionado, nÃƒÆ’Ã‚Â£o chama API
            categoriasData = [];
            renderizarCategorias();
            return;
        }
        const response = await fetch(`${window.API_BASE || '/api'}/categoria/list.php?evento_id=${encodeURIComponent(eventoId)}`);
        const data = await response.json();

        if (data.success) {
            categoriasData = data.categorias || [];
            renderizarCategorias();
        } else {
            throw new Error(data.message || 'Erro ao carregar categorias');
        }
    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
        Swal.fire({
            title: 'Erro! ÃƒÂ¢Ã‚ÂÃ…â€™',
            text: 'NÃƒÆ’Ã‚Â£o foi possÃƒÆ’Ã‚Â­vel carregar as categorias',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
}

function renderizarCategorias() {
    const container = document.getElementById('listaCategorias');
    const semCategorias = document.getElementById('semCategorias');
    const totalCategorias = document.getElementById('totalCategorias');

    if (!categoriasData || categoriasData.length === 0) {
        container.innerHTML = '';
        semCategorias.classList.remove('hidden');
        totalCategorias.textContent = '0 categorias';
        return;
    }

    semCategorias.classList.add('hidden');
    totalCategorias.textContent = `${categoriasData.length} categoria${categoriasData.length > 1 ? 's' : ''}`;

    container.innerHTML = categoriasData.map(categoria => `
        <div class="categoria-card bg-white rounded-lg border border-gray-200 p-4 hover:border-blue-300">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <h4 class="font-semibold text-gray-900">${categoria.nome}</h4>
                        <span class="px-2 py-1 text-xs rounded-full ${categoria.ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${categoria.ativo ? 'Ativo' : 'Inativo'}
                        </span>
                    </div>
                    
                    ${categoria.descricao ? `<p class=\"text-sm text-gray-600 mb-2\">${categoria.descricao}</p>` : ''}
                    
                                         <div class=\"flex items-center gap-4 text-xs text-gray-500\">
                         <span class="flex items-center gap-1">
                             <i class="fas fa-users"></i>
                             ${categoria.tipo_publico || 'ÃƒÂ¢ââ€šÂ¬ââ‚¬Â'}
                         </span>
                         <span class="flex items-center gap-1">
                             <i class="fas fa-birthday-cake"></i>
                             ${categoria.idade_min || 0}-${categoria.idade_max || 100} anos
                         </span>
                         <span class="flex items-center gap-1">
                             <i class="fas fa-eye"></i>
                             ${categoria.exibir_inscricao_geral ? 'Geral' : 'NÃƒÆ’Ã‚Â£o'}, ${categoria.exibir_inscricao_grupos ? 'Grupos' : 'NÃƒÆ’Ã‚Â£o'}
                         </span>
                     </div>
                </div>
                
                <div class="flex items-center gap-2 ml-4">
                    <button onclick="editarCategoria(${JSON.stringify(categoria).replace(/"/g, '&quot;')})" 
                            class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="excluirCategoria(${categoria.id})" 
                            class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// ===== INICIALIZAÃƒÆ’ââ‚¬Â¡ÃƒÆ’Ã†â€™O =====

document.addEventListener('DOMContentLoaded', function () {
    // Fechar panel ao clicar no overlay
    document.getElementById('categoriasOverlay').addEventListener('click', fecharPanelCategorias);

    // Fechar panel com ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            fecharPanelCategorias();
        }
    });
});
