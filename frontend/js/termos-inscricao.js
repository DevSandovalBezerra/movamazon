if (window.getApiBase) { window.getApiBase(); }
// Termos de InscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o - JavaScript
console.log('Termos de InscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o - JavaScript carregado');

let editor = null;

function abrirModalCriar() {
    console.log('Abrindo modal para criar termo');

    // Limpar formulÃƒÆ’Ã‚Â¡rio
    document.getElementById('formTermo').reset();
    document.getElementById('termoId').value = '';
    document.getElementById('modalTitulo').textContent = 'Novo Termo';
    document.getElementById('ativo').checked = true;

    // Inicializar CKEditor se ainda nÃƒÆ’Ã‚Â£o foi inicializado e se estiver disponÃƒÆ’Ã‚Â­vel
    if (!editor && typeof ClassicEditor !== 'undefined') {
        ClassicEditor
            .create(document.querySelector('#conteudo'))
            .then(newEditor => {
                editor = newEditor;
                console.log('CKEditor inicializado com sucesso');
            })
            .catch(error => {
                console.error('Erro ao inicializar CKEditor:', error);
                editor = null;
            });
    } else if (typeof ClassicEditor === 'undefined') {
        console.warn('CKEditor nÃƒÆ’Ã‚Â£o estÃƒÆ’Ã‚Â¡ disponÃƒÆ’Ã‚Â­vel, usando textarea simples');
    }

    // Mostrar modal
    document.getElementById('modalTermo').classList.remove('hidden');
}

function fecharModal() {
    console.log('Fechando modal');
    document.getElementById('modalTermo').classList.add('hidden');

    // Limpar editor se existir
    if (editor) {
        editor.setData('');
    }
}

function salvarTermo() {
    console.log('Salvando termo');

    // Determinar se ÃƒÆ’Ã‚Â© criaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o ou ediÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
    const termoId = document.getElementById('termoId').value;
    const isEdit = termoId && termoId !== '';

    // Preparar dados
    const modalidadeId = document.getElementById('modalidadeId').value;
    const data = {
        evento_id: document.getElementById('eventoId').value,
        modalidade_id: modalidadeId && modalidadeId !== '' ? modalidadeId : null,
        titulo: document.getElementById('titulo').value,
        versao: document.getElementById('versao').value || '1.0',
        ativo: document.getElementById('ativo').checked ? 1 : 0
    };

    // Adicionar conteÃƒÆ’Ã‚Âºdo do editor ou textarea
    if (editor) {
        data.conteudo = editor.getData();
    } else {
        data.conteudo = document.getElementById('conteudo').value;
    }

    // Adicionar ID se for ediÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
    if (isEdit) {
        data.id = termoId;
    }

    console.log('Dados do formulÃƒÆ’Ã‚Â¡rio:', data);

    const endpoint = isEdit ? 'update.php' : 'create.php';
    const method = 'POST';

    console.log(`${isEdit ? 'Editando' : 'Criando'} termo via ${endpoint}`);

    // Mostrar loading
    document.getElementById('loading').style.display = 'block';

    fetch(`${window.API_BASE || '/api'}/organizador/termos-inscricao/${endpoint}`, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('loading').style.display = 'none';

            if (data.success) {
                Swal.fire('Sucesso', `Termo ${isEdit ? 'atualizado' : 'criado'} com sucesso!`, 'success');
                fecharModal();
                carregarTermos(); // Recarregar lista
            } else {
                Swal.fire('Erro', data.message || `Erro ao ${isEdit ? 'atualizar' : 'criar'} termo`, 'error');
            }
        })
        .catch(error => {
            document.getElementById('loading').style.display = 'none';
            console.error('Erro:', error);
            Swal.fire('Erro', `Erro ao ${isEdit ? 'atualizar' : 'criar'} termo`, 'error');
        });
}

// Carregar modalidades quando evento for selecionado no modal
function carregarModalidadesModal(eventoId) {
    console.log('Carregando modalidades para evento:', eventoId);

    const selectModalidade = document.getElementById('modalidadeId');

    if (!eventoId) {
        selectModalidade.innerHTML = '<option value="">Termo geral (todas as modalidades)</option>';
        return Promise.resolve();
    }

    return fetch(`${window.API_BASE || '/api'}/organizador/modalidades/list.php?evento_id=${eventoId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Modalidades carregadas:', data);

            let options = '<option value="">Termo geral (todas as modalidades)</option>';

            if (data.success && data.modalidades && Array.isArray(data.modalidades)) {
                data.modalidades.forEach(modalidade => {
                    const nome = modalidade.categoria_nome ? `${modalidade.categoria_nome} - ${modalidade.nome}` : modalidade.nome;
                    options += `<option value="${modalidade.id}">${nome}</option>`;
                });
            }

            selectModalidade.innerHTML = options;
            return Promise.resolve();
        })
        .catch(error => {
            console.error('Erro ao carregar modalidades:', error);
            selectModalidade.innerHTML = '<option value="">Termo geral (todas as modalidades)</option>';
            return Promise.resolve();
        });
}

function carregarTermos() {
    const eventoId = document.getElementById('filtroEvento').value;

    console.log('Carregando termos para evento:', eventoId);

    if (!eventoId) {
        mostrarMensagemInicial();
        return;
    }

    fetch(`${window.API_BASE || '/api'}/organizador/termos-inscricao/list.php?evento_id=${eventoId}`)
        .then(response => {
            console.log('Status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Dados:', data);
            if (data.success) {
                console.log('Termos encontrados:', data.termos.length);
                renderizarTabela(data.termos);
            } else {
                console.log('Erro:', data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
        });
}

function renderizarTabela(termos) {
    const tbody = document.getElementById('termosTableBody');
    if (!tbody) {
        console.log('Tbody nÃƒÆ’Ã‚Â£o encontrado');
        return;
    }

    if (termos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhum termo encontrado</td></tr>';
        return;
    }

    tbody.innerHTML = termos.map(termo => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${termo.titulo}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${termo.evento_nome || 'N/A'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${termo.modalidade_nome || 'Geral'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${termo.tipo || 'N/A'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${termo.versao || '1.0'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${termo.ativo == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${termo.ativo == 1 ? 'Ativo' : 'Inativo'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button onclick="editarTermo(${termo.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="toggleStatusTermo(${termo.id}, ${termo.ativo})" class="text-yellow-600 hover:text-yellow-900 mr-3">
                    <i class="fas fa-${termo.ativo == 1 ? 'pause' : 'play'}"></i>
                </button>
                <button onclick="excluirTermo(${termo.id})" class="text-red-600 hover:text-red-900">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para editar termo
function editarTermo(id) {
    console.log('Editando termo:', id);

    // Mostrar loading
    document.getElementById('loading').style.display = 'block';

    fetch(`${window.API_BASE || '/api'}/organizador/termos-inscricao/get.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('loading').style.display = 'none';

            if (data.success && data.termo) {
                console.log('Termo carregado:', data.termo);

                // Preencher formulÃƒÆ’Ã‚Â¡rio
                document.getElementById('termoId').value = data.termo.id || '';
                document.getElementById('eventoId').value = data.termo.evento_id || '';
                document.getElementById('titulo').value = data.termo.titulo || '';
                document.getElementById('versao').value = data.termo.versao || '1.0';
                document.getElementById('ativo').checked = data.termo.ativo == 1 || data.termo.ativo === '1';

                // Carregar modalidades do evento
                if (data.termo.evento_id) {
                    carregarModalidadesModal(data.termo.evento_id).then(() => {
                        // Aguardar um pouco para o select carregar e entÃƒÆ’Ã‚Â£o selecionar a modalidade
                        setTimeout(() => {
                            document.getElementById('modalidadeId').value = data.termo.modalidade_id || '';
                        }, 300);
                    });
                }

                // Definir conteÃƒÆ’Ã‚Âºdo no editor
                const conteudo = data.termo.conteudo || '';
                if (editor) {
                    editor.setData(conteudo);
                } else if (typeof ClassicEditor !== 'undefined') {
                    // Se o editor ainda nÃƒÆ’Ã‚Â£o foi inicializado, inicializar agora
                    ClassicEditor
                        .create(document.querySelector('#conteudo'))
                        .then(newEditor => {
                            editor = newEditor;
                            editor.setData(conteudo);
                        })
                        .catch(error => {
                            console.error('Erro ao inicializar CKEditor:', error);
                            // Se o CKEditor falhar, usar textarea normal
                            document.getElementById('conteudo').value = conteudo;
                        });
                } else {
                    // Se o CKEditor nÃƒÆ’Ã‚Â£o estiver disponÃƒÆ’Ã‚Â­vel, usar textarea normal
                    console.warn('CKEditor nÃƒÆ’Ã‚Â£o estÃƒÆ’Ã‚Â¡ disponÃƒÆ’Ã‚Â­vel, usando textarea simples');
                    document.getElementById('conteudo').value = conteudo;
                }

                // Atualizar tÃƒÆ’Ã‚Â­tulo do modal
                document.getElementById('modalTitulo').textContent = 'Editar Termo';

                // Mostrar modal
                document.getElementById('modalTermo').classList.remove('hidden');

            } else {
                const mensagem = data.message || 'Erro ao carregar termo';
                console.error('Erro na resposta:', mensagem);
                Swal.fire('Erro', mensagem, 'error');
            }
        })
        .catch(error => {
            document.getElementById('loading').style.display = 'none';
            console.error('Erro ao buscar termo:', error);
            Swal.fire('Erro', 'Erro ao carregar termo: ' + error.message, 'error');
        });
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para alternar status do termo
function toggleStatusTermo(id, statusAtual) {
    console.log('Alternando status do termo:', id, 'Status atual:', statusAtual);

    const novoStatus = statusAtual == 1 ? 0 : 1;
    const acao = novoStatus == 1 ? 'ativar' : 'desativar';

    Swal.fire({
        title: `Confirmar ${acao} termo`,
        text: `Deseja realmente ${acao} este termo?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#ef4444',
        confirmButtonText: `Sim, ${acao}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${window.API_BASE || '/api'}/organizador/termos-inscricao/toggle_status.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        ativo: novoStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Sucesso', `Termo ${acao}do com sucesso!`, 'success');
                        // Recarregar termos
                        carregarTermos();
                    } else {
                        Swal.fire('Erro', data.message || `Erro ao ${acao} termo`, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    Swal.fire('Erro', `Erro ao ${acao} termo`, 'error');
                });
        }
    });
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para excluir termo
function excluirTermo(id) {
    console.log('Excluindo termo:', id);

    Swal.fire({
        title: 'Confirmar exclusÃƒÆ’Ã‚Â£o',
        text: 'Deseja realmente excluir este termo? Esta aÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o nÃƒÆ’Ã‚Â£o pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${window.API_BASE || '/api'}/organizador/termos-inscricao/delete.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Sucesso', 'Termo excluÃƒÆ’Ã‚Â­do com sucesso!', 'success');
                        // Recarregar termos
                        carregarTermos();
                    } else {
                        Swal.fire('Erro', data.message || 'Erro ao excluir termo', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    Swal.fire('Erro', 'Erro ao excluir termo', 'error');
                });
        }
    });
}

// Carregar termos quando a pÃƒÆ’Ã‚Â¡gina carregar
document.addEventListener('DOMContentLoaded', function () {
    console.log('PÃƒÆ’Ã‚Â¡gina carregada');
    // NÃƒÆ’Ã‚Â£o carregar termos automaticamente - seguir padrÃƒÆ’Ã‚Â£o do questionÃƒÆ’Ã‚Â¡rio
    mostrarMensagemInicial();

    // Event listener para filtro de evento (igual ao questionÃƒÆ’Ã‚Â¡rio)
    document.getElementById('filtroEvento').addEventListener('change', function () {
        const eventoId = this.value;
        if (eventoId) {
            carregarTermos();
        } else {
            mostrarMensagemInicial();
        }
    });
});

function aplicarFiltros() {
    const eventoId = document.getElementById('filtroEvento').value;

    if (!eventoId) {
        mostrarMensagemInicial();
        return;
    }

    // Carregar termos apenas quando evento for selecionado
    carregarTermos();
}

function mostrarMensagemInicial() {
    const tbody = document.getElementById('termosTableBody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Selecione um evento para ver os termos</td></tr>';
    }
}
