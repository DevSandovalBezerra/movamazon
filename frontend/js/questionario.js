// Variáveis globais
let questionario = [];
let questionarioFiltrado = [];
let eventos = [];
let eventoSelecionado = null;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarEventos();
    configurarEventListeners();
});

// Configurar event listeners
function configurarEventListeners() {
    // Evento selecionado
    document.getElementById('filtroEvento').addEventListener('change', function() {
        const eventoId = this.value;
        if (eventoId) {
            eventoSelecionado = eventoId;
            document.getElementById('filtroTipo').disabled = false;
            carregarQuestionario(eventoId);
        } else {
            eventoSelecionado = null;
            document.getElementById('filtroTipo').disabled = true;
            mostrarSelecionarFiltros();
        }
    });
}

// Carregar eventos do organizador
async function carregarEventos() {
    console.log('🚀 questionario.js - Iniciando carregamento de eventos');
    try {
        console.log('📡 questionario.js - Fazendo requisição para API eventos');
        const response = await fetch('../../../api/organizador/eventos/list.php');
        console.log('📡 questionario.js - Status da resposta:', response.status);
        
        const data = await response.json();
        console.log('📡 questionario.js - Dados recebidos:', data);
        
        if (data.success) {
            console.log('✅ questionario.js - Sucesso! Eventos carregados:', data.data.eventos.length);
            eventos = data.data.eventos;
            preencherSelectEventos();
        } else {
            console.error('❌ questionario.js - Erro na resposta:', data.error);
            Swal.fire('Erro', data.error || 'Erro ao carregar eventos', 'error');
        }
    } catch (error) {
        console.error('💥 questionario.js - Erro ao carregar eventos:', error);
        Swal.fire('Erro', 'Erro ao carregar eventos', 'error');
    }
}

// Preencher select de eventos
function preencherSelectEventos() {
    console.log('🔧 questionario.js - Preenchendo select de eventos');
    const select = document.getElementById('filtroEvento');
    select.innerHTML = '<option value="">Selecione um evento</option>';
    
    console.log('📋 questionario.js - Eventos para preencher:', eventos);
    eventos.forEach(evento => {
        const option = document.createElement('option');
        option.value = evento.id;
        option.textContent = evento.nome;
        select.appendChild(option);
    });
    console.log('✅ questionario.js - Select de eventos preenchido com', eventos.length, 'eventos');
}

// Carregar questionário do evento
async function carregarQuestionario(eventoId) {
    console.log('🚀 questionario.js - Carregando questionário para evento ID:', eventoId);
    try {
        mostrarLoading();
        
        console.log('📡 questionario.js - Fazendo requisição para questionário');
        const response = await fetch(`../../../api/organizador/questionario/list.php?evento_id=${eventoId}`);
        console.log('📡 questionario.js - Status da resposta questionário:', response.status);
        
        const data = await response.json();
        console.log('📡 questionario.js - Dados questionário recebidos:', data);
        
        if (data.success) {
            console.log('✅ questionario.js - Questionário carregado:', data.data.length);
            questionario = data.data;
            questionarioFiltrado = [...questionario];
            renderizarQuestionario();
        } else {
            console.error('❌ questionario.js - Erro na resposta questionário:', data.message);
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('💥 questionario.js - Erro ao carregar questionário:', error);
        mostrarErro('Erro ao carregar questionário: ' + error.message);
    } finally {
        ocultarLoading();
    }
}

// Renderizar lista de perguntas/campos
function renderizarQuestionario() {
    const tbody = document.getElementById('questionario-tbody');
    
    if (questionarioFiltrado.length === 0) {
        mostrarSemPerguntas();
        return;
    }
    
    tbody.innerHTML = '';
    
    questionarioFiltrado.forEach(pergunta => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        
        // Determinar badge de classificação
        const classificacao = pergunta.classificacao || 'evento';
        const badgeClassificacao = classificacao === 'atleta' 
            ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800"><i class="fas fa-user-alt mr-1"></i>Atleta</span>'
            : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800"><i class="fas fa-calendar-alt mr-1"></i>Evento</span>';
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="flex items-center space-x-1">
                    <button onclick="moverPergunta(${pergunta.id}, 'up')" class="text-gray-400 hover:text-blue-600">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <span class="mx-2">${pergunta.ordem}</span>
                    <button onclick="moverPergunta(${pergunta.id}, 'down')" class="text-gray-400 hover:text-blue-600">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
                <div class="flex flex-col gap-1">
                    <span>${pergunta.texto}</span>
                    <div class="mt-1">${badgeClassificacao}</div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                    ${pergunta.tipo_resposta || 'N/A'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${pergunta.obrigatorio ? 'Sim' : 'Não'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-medium rounded-full ${pergunta.status_site === 'publicada' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                    ${pergunta.status_site === 'publicada' ? 'Publicada' : 'Rascunho'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${(pergunta.modalidades || []).map(m => m.nome_modalidade).join(', ')}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button onclick="editarPergunta(${pergunta.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="excluirPergunta(${pergunta.id}, '${pergunta.texto}')" class="text-red-600 hover:text-red-900">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
    
    mostrarContainerQuestionario();
}

// Mostrar estados
function mostrarSelecionarFiltros() {
    document.getElementById('selecionar-filtros').classList.remove('hidden');
    document.getElementById('questionario-container').classList.add('hidden');
    document.getElementById('sem-perguntas').classList.add('hidden');
    document.getElementById('error-questionario').classList.add('hidden');
}

function mostrarContainerQuestionario() {
    document.getElementById('selecionar-filtros').classList.add('hidden');
    document.getElementById('questionario-container').classList.remove('hidden');
    document.getElementById('sem-perguntas').classList.add('hidden');
    document.getElementById('error-questionario').classList.add('hidden');
}

function mostrarSemPerguntas() {
    document.getElementById('selecionar-filtros').classList.add('hidden');
    document.getElementById('questionario-container').classList.add('hidden');
    document.getElementById('sem-perguntas').classList.remove('hidden');
    document.getElementById('error-questionario').classList.add('hidden');
}

function mostrarErro(mensagem) {
    document.getElementById('error-message').textContent = mensagem;
    document.getElementById('selecionar-filtros').classList.add('hidden');
    document.getElementById('questionario-container').classList.add('hidden');
    document.getElementById('sem-perguntas').classList.add('hidden');
    document.getElementById('error-questionario').classList.remove('hidden');
}

function mostrarLoading() {
    document.getElementById('loading').classList.remove('hidden');
    document.getElementById('selecionar-filtros').classList.add('hidden');
    document.getElementById('questionario-container').classList.add('hidden');
    document.getElementById('sem-perguntas').classList.add('hidden');
    document.getElementById('error-questionario').classList.add('hidden');
}

function ocultarLoading() {
    document.getElementById('loading').classList.add('hidden');
}

// Aplicar filtros
function aplicarFiltros() {
    if (!eventoSelecionado) {
        Swal.fire('Atenção', 'Selecione um evento primeiro', 'warning');
        return;
    }
    
    const tipo = document.getElementById('filtroTipo').value;
    
    questionarioFiltrado = questionario.filter(item => {
        return !tipo || item.tipo_resposta === tipo;
    });
    
    renderizarQuestionario();
}

// Abrir modal para criar nova pergunta
function abrirModal() {
    if (!eventoSelecionado) {
        Swal.fire('Atenção', 'Selecione um evento primeiro', 'warning');
        return;
    }
    
    document.getElementById('modalTitle').textContent = 'Nova Pergunta';
    document.getElementById('formPergunta').reset();
    document.getElementById('perguntaId').value = '';
    
    // Reset classificação para valor padrão
    const radioEvento = document.getElementById('classificacaoEvento');
    if (radioEvento) radioEvento.checked = true;
    
    carregarModalidadesSelect();
    
    const modal = document.getElementById('modalPergunta');
    modal.classList.remove('hidden');
}

// Abrir modal para editar pergunta
function editarPergunta(id) {
    const pergunta = questionario.find(p => p.id == id);
    if (!pergunta) return;
    
    document.getElementById('modalTitle').textContent = 'Editar Pergunta';
    document.getElementById('perguntaId').value = pergunta.id;
    document.getElementById('textoPergunta').value = pergunta.texto;
    document.getElementById('tipoResposta').value = pergunta.tipo_resposta || '';
    document.getElementById('obrigatorio').value = pergunta.obrigatorio ? '1' : '0';
    document.getElementById('statusSite').value = pergunta.status_site || 'publicada';
    document.getElementById('observacoesPergunta').value = pergunta.observacoes || '';
    
    // Preencher classificação
    const classificacao = pergunta.classificacao || 'evento';
    const radioEvento = document.getElementById('classificacaoEvento');
    const radioAtleta = document.getElementById('classificacaoAtleta');
    if (classificacao === 'atleta' && radioAtleta) {
        radioAtleta.checked = true;
    } else if (radioEvento) {
        radioEvento.checked = true;
    }
    
    carregarModalidadesSelect(pergunta.modalidades ? pergunta.modalidades.map(m => m.id) : []);
    
    const modal = document.getElementById('modalPergunta');
    modal.classList.remove('hidden');
}

// Fechar modal
function fecharModal() {
    const modal = document.getElementById('modalPergunta');
    modal.classList.add('hidden');
}

// Carregar modalidades usando checkboxes
function carregarModalidadesSelect(selecionadas = []) {
    fetch(`../../../api/organizador/modalidades/list.php?evento_id=${eventoSelecionado}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.modalidades) {
                renderizarModalidadesCheckboxes('modalidades-container', data.modalidades, selecionadas);
            } else {
                const container = document.getElementById('modalidades-container');
                if (container) {
                    container.innerHTML = '<div class="text-gray-400 text-sm">Nenhuma modalidade encontrada para este evento.</div>';
                }
            }
        })
        .catch(error => {
            console.error('Erro ao carregar modalidades:', error);
            const container = document.getElementById('modalidades-container');
            if (container) {
                container.innerHTML = '<div class="text-red-500 text-sm">Erro ao carregar modalidades.</div>';
            }
        });
}

// Salvar pergunta
async function salvarPergunta() {
    try {
        const perguntaId = document.getElementById('perguntaId').value;
        
        const validacao = validarSelecaoModalidades('modalidades-container', 1);
        if (!validacao.valido) {
            Swal.fire('Atenção', validacao.mensagem, 'warning');
            return;
        }
        
        const modalidades = obterModalidadesSelecionadas('modalidades-container');
        
        // Obter classificação selecionada
        const classificacaoRadio = document.querySelector('input[name="classificacao"]:checked');
        const classificacao = classificacaoRadio ? classificacaoRadio.value : 'evento';
        
        const dados = {
            texto: document.getElementById('textoPergunta').value,
            tipo: document.getElementById('tipoResposta').value === 'pergunta' ? 'pergunta' : 'campo',
            tipo_resposta: document.getElementById('tipoResposta').value,
            classificacao: classificacao,
            obrigatorio: parseInt(document.getElementById('obrigatorio').value),
            status_site: document.getElementById('statusSite').value,
            evento_id: parseInt(eventoSelecionado),
            modalidades: modalidades
        };
        
        // Validar campos
        if (!dados.texto || !dados.tipo_resposta || modalidades.length === 0) {
            Swal.fire('Atenção', 'Todos os campos obrigatórios devem ser preenchidos', 'warning');
            return;
        }
        
        let url, message, method;
        
        if (perguntaId) {
            dados.id = parseInt(perguntaId);
            url = '../../../api/organizador/questionario/update.php';
            method = 'PUT';
            message = 'Pergunta atualizada com sucesso';
        } else {
            url = '../../../api/organizador/questionario/create.php';
            method = 'POST';
            message = 'Pergunta criada com sucesso';
        }
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire('Sucesso', message, 'success');
            fecharModal();
            carregarQuestionario(eventoSelecionado);
        } else {
            Swal.fire('Erro', data.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao salvar pergunta:', error);
        Swal.fire('Erro', 'Erro ao salvar pergunta', 'error');
    }
}

// Mover ordem da pergunta
async function moverPergunta(id, direcao) {
    try {
        const response = await fetch('../../../api/organizador/questionario/move.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: parseInt(id),
                direcao: direcao
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            carregarQuestionario(eventoSelecionado);
        } else {
            Swal.fire('Erro', data.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao mover pergunta:', error);
        Swal.fire('Erro', 'Erro ao alterar ordem', 'error');
    }
}

// Excluir pergunta
async function excluirPergunta(id, texto) {
    try {
        const result = await Swal.fire({
            title: 'Confirmar exclusão',
            text: `Deseja realmente excluir a pergunta "${texto}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
            const response = await fetch('../../../api/organizador/questionario/delete.php?id=' + id, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire('Sucesso', 'Pergunta excluída com sucesso', 'success');
                carregarQuestionario(eventoSelecionado);
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        }
    } catch (error) {
        console.error('Erro ao excluir pergunta:', error);
        Swal.fire('Erro', 'Erro ao excluir pergunta', 'error');
    }
}