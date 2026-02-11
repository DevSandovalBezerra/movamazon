// Variáveis globais
let eventos = [];
let eventoSelecionado = null;
let locais = [];
let localEditando = null;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Iniciando sistema de retirada de kits');
    configurarEventListeners();
    carregarEventos();
});

// Configurar event listeners
function configurarEventListeners() {
    // Filtros
    document.getElementById('filtroEvento').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroStatus').addEventListener('change', aplicarFiltros);
    
    // Botão novo local
    document.getElementById('btnNovoLocal').addEventListener('click', () => abrirModalLocal());
    
    // Formulário do modal
    document.getElementById('formLocal').addEventListener('submit', salvarLocal);
}

// Estados da interface
function mostrarEstadoInicial() {
    document.getElementById('estado-inicial').classList.remove('hidden');
    document.getElementById('estado-filtrado').classList.add('hidden');
    document.getElementById('estado-vazio').classList.add('hidden');
}

function mostrarEstadoFiltrado() {
    document.getElementById('estado-inicial').classList.add('hidden');
    document.getElementById('estado-filtrado').classList.remove('hidden');
    document.getElementById('estado-vazio').classList.add('hidden');
}

function mostrarEstadoVazio() {
    document.getElementById('estado-inicial').classList.add('hidden');
    document.getElementById('estado-filtrado').classList.add('hidden');
    document.getElementById('estado-vazio').classList.remove('hidden');
}

// Carregar eventos
async function carregarEventos() {
    try {
        console.log('📥 Carregando eventos...');
        const response = await fetch('../../../api/organizador/eventos/list.php');
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📊 Resposta da API eventos:', data);
        
        if (data.success) {
            eventos = data.data.eventos;
            console.log('📋 Eventos carregados:', eventos);
            preencherSelectEventos();
            console.log(`✅ ${eventos.length} eventos carregados`);
        } else {
            throw new Error(data.message || data.error || 'Erro ao carregar eventos');
        }
    } catch (error) {
        console.error('💥 Erro ao carregar eventos:', error);
        Swal.fire('Erro', 'Erro ao carregar eventos: ' + error.message, 'error');
    }
}

function preencherSelectEventos() {
    console.log('🎯 Preenchendo select de eventos...');
    const select = document.getElementById('filtroEvento');
    if (!select) {
        console.error('❌ Elemento filtroEvento não encontrado');
        return;
    }
    
    select.innerHTML = '<option value="">Selecione um evento</option>';
    
    if (!eventos || eventos.length === 0) {
        console.log('📭 Nenhum evento disponível');
        return;
    }
    
    eventos.forEach(evento => {
        const option = document.createElement('option');
        option.value = evento.id;
        option.textContent = evento.nome;
        select.appendChild(option);
        console.log(`✅ Adicionado evento: ${evento.nome} (ID: ${evento.id})`);
    });
    
    console.log(`✅ Select preenchido com ${eventos.length} eventos`);
}

// Aplicar filtros
function aplicarFiltros() {
    const eventoId = document.getElementById('filtroEvento').value;
    
    if (!eventoId) {
        mostrarEstadoInicial();
        return;
    }
    
    eventoSelecionado = eventoId;
    carregarLocais(eventoId);
}

// Carregar locais do evento
async function carregarLocais(eventoId) {
    try {
        console.log('📥 Carregando locais para evento:', eventoId);
        const response = await fetch(`../../../api/organizador/retirada-kits/get.php?evento_id=${eventoId}`);
        const data = await response.json();
        
        if (data.success) {
            locais = data.data || [];
            console.log(`✅ ${locais.length} locais carregados`);
            
            // Aplicar filtro de status se houver
            const statusFiltro = document.getElementById('filtroStatus').value;
            if (statusFiltro !== '') {
                locais = locais.filter(local => local.ativo == statusFiltro);
            }
            
            if (locais.length === 0) {
                mostrarEstadoVazio();
            } else {
                mostrarEstadoFiltrado();
                renderizarLocais();
            }
        } else {
            throw new Error(data.error || 'Erro ao carregar locais');
        }
    } catch (error) {
        console.error('💥 Erro ao carregar locais:', error);
        Swal.fire('Erro', 'Erro ao carregar locais: ' + error.message, 'error');
        locais = [];
        mostrarEstadoVazio();
    }
}

// Renderizar lista de locais
function renderizarLocais() {
    const container = document.getElementById('locais-container');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (locais.length === 0) {
        mostrarEstadoVazio();
        return;
    }
    
    locais.forEach((local, index) => {
        const card = criarCardLocal(local, index);
        container.appendChild(card);
    });
}

// Criar card de local
function criarCardLocal(local, index) {
    const card = document.createElement('div');
    card.className = 'bg-white border border-gray-200 rounded-lg p-4 sm:p-6 hover:shadow-md transition-shadow';
    
    const statusClass = local.ativo ? 'green' : 'gray';
    const statusText = local.ativo ? 'Ativo' : 'Inativo';
    
    const dataInicioFormatada = formatarData(local.data_inicio);
    const dataFimFormatada = formatarData(local.data_fim);
    
    card.innerHTML = `
        <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
                <h4 class="text-lg font-semibold text-gray-900 mb-1">Local ${index + 1}: ${local.local || 'Sem nome'}</h4>
                <p class="text-sm text-gray-600">${dataInicioFormatada} até ${dataFimFormatada}</p>
            </div>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-${statusClass}-100 text-${statusClass}-800">
                ${statusText}
            </span>
        </div>
        
        <div class="space-y-2 text-sm mb-4">
            ${local.documentos_necessarios ? `
                <div>
                    <span class="font-medium text-gray-700">Documentos Necessários:</span>
                    <p class="text-gray-600 mt-1">${local.documentos_necessarios}</p>
                </div>
            ` : ''}
            
            ${local.instrucoes ? `
                <div>
                    <span class="font-medium text-gray-700">Instruções:</span>
                    <p class="text-gray-600 mt-1">${local.instrucoes}</p>
                </div>
            ` : ''}
        </div>
        
        <div class="flex justify-end space-x-2 pt-4 border-t">
            <button onclick="editarLocal(${local.id})" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition-colors">
                <i class="fas fa-edit mr-1"></i>Editar
            </button>
            <button onclick="toggleStatusLocal(${local.id}, ${local.ativo ? 0 : 1})" class="px-3 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg text-sm transition-colors">
                <i class="fas fa-${local.ativo ? 'pause' : 'play'} mr-1"></i>${local.ativo ? 'Desativar' : 'Ativar'}
            </button>
            <button onclick="excluirLocal(${local.id})" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition-colors">
                <i class="fas fa-trash mr-1"></i>Excluir
            </button>
        </div>
    `;
    
    return card;
}

// Formatar data
function formatarData(dataString) {
    if (!dataString) return '';
    
    const data = new Date(dataString);
    return data.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Abrir modal para criar novo local
function abrirModalLocal() {
    if (!eventoSelecionado) {
        Swal.fire('Atenção', 'Selecione um evento primeiro.', 'warning');
        return;
    }
    
    localEditando = null;
    document.getElementById('modalTitulo').textContent = 'Novo Local de Retirada';
    document.getElementById('localId').value = '';
    document.getElementById('modalEventoId').value = eventoSelecionado;
    limparFormularioModal();
    document.getElementById('modalLocal').classList.remove('hidden');
}

// Abrir modal para editar local
async function editarLocal(localId) {
    try {
        const local = locais.find(l => l.id == localId);
        if (!local) {
            Swal.fire('Erro', 'Local não encontrado', 'error');
            return;
        }
        
        localEditando = local;
        document.getElementById('modalTitulo').textContent = 'Editar Local de Retirada';
        document.getElementById('localId').value = local.id;
        document.getElementById('modalEventoId').value = local.evento_id;
        
        preencherFormularioModal(local);
        document.getElementById('modalLocal').classList.remove('hidden');
    } catch (error) {
        console.error('Erro ao editar local:', error);
        Swal.fire('Erro', 'Erro ao carregar dados do local', 'error');
    }
}

// Preencher formulário do modal
function preencherFormularioModal(local) {
    document.getElementById('modalLocalRetirada').value = local.local || '';
    
    // Converter data_retirada + horario para datetime-local
    if (local.data_retirada && local.horario_inicio) {
        const dataInicio = new Date(local.data_retirada + 'T' + local.horario_inicio);
        document.getElementById('modalDataInicio').value = formatarParaDateTimeLocal(dataInicio);
    }
    
    if (local.data_retirada && local.horario_fim) {
        const dataFim = new Date(local.data_retirada + 'T' + local.horario_fim);
        document.getElementById('modalDataFim').value = formatarParaDateTimeLocal(dataFim);
    }
    
    document.getElementById('modalDocumentosNecessarios').value = local.documentos_necessarios || '';
    document.getElementById('modalInstrucoesRetirada').value = local.instrucoes || '';
    document.getElementById('modalAtivoRetirada').checked = local.ativo == 1 || local.ativo === true;
}

// Formatar data para datetime-local
function formatarParaDateTimeLocal(data) {
    const ano = data.getFullYear();
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const dia = String(data.getDate()).padStart(2, '0');
    const hora = String(data.getHours()).padStart(2, '0');
    const minuto = String(data.getMinutes()).padStart(2, '0');
    return `${ano}-${mes}-${dia}T${hora}:${minuto}`;
}

// Limpar formulário do modal
function limparFormularioModal() {
    document.getElementById('modalLocalRetirada').value = '';
    document.getElementById('modalDataInicio').value = '';
    document.getElementById('modalDataFim').value = '';
    document.getElementById('modalDocumentosNecessarios').value = '';
    document.getElementById('modalInstrucoesRetirada').value = '';
    document.getElementById('modalAtivoRetirada').checked = true;
}

// Fechar modal
function fecharModalLocal() {
    document.getElementById('modalLocal').classList.add('hidden');
    localEditando = null;
    limparFormularioModal();
}

// Salvar local (criar ou atualizar)
async function salvarLocal(e) {
    e.preventDefault();
    
    if (!eventoSelecionado) {
        Swal.fire('Atenção', 'Selecione um evento primeiro.', 'warning');
        return;
    }
    
    const localId = document.getElementById('localId').value;
    const local = document.getElementById('modalLocalRetirada').value;
    const dataInicio = document.getElementById('modalDataInicio').value;
    const dataFim = document.getElementById('modalDataFim').value;
    const documentos = document.getElementById('modalDocumentosNecessarios').value;
    const instrucoes = document.getElementById('modalInstrucoesRetirada').value;
    const ativo = document.getElementById('modalAtivoRetirada').checked;
    
    if (!local || !dataInicio || !dataFim) {
        Swal.fire('Atenção', 'Preencha pelo menos o local e as datas de início e fim.', 'warning');
        return;
    }
    
    if (new Date(dataInicio) >= new Date(dataFim)) {
        Swal.fire('Atenção', 'A data de fim deve ser posterior à data de início.', 'warning');
        return;
    }
    
    try {
        const url = localId ? 
            '../../../api/organizador/retirada-kits/update.php' : 
            '../../../api/organizador/retirada-kits/create.php';
        
        const payload = {
            evento_id: eventoSelecionado,
            local: local,
            data_inicio: dataInicio.replace('T', ' '),
            data_fim: dataFim.replace('T', ' '),
            documentos_necessarios: documentos,
            instrucoes: instrucoes,
            ativo: ativo
        };
        
        if (localId) {
            payload.id = localId;
        }
        
        console.log('💾 Salvando local...', payload);
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire('Sucesso', localId ? 'Local atualizado com sucesso!' : 'Local criado com sucesso!', 'success');
            fecharModalLocal();
            await carregarLocais(eventoSelecionado);
        } else {
            Swal.fire('Erro', data.error || data.message || 'Erro ao salvar local.', 'error');
        }
    } catch (error) {
        console.error('💥 Erro ao salvar local:', error);
        Swal.fire('Erro', 'Erro ao salvar local.', 'error');
    }
}

// Excluir local
async function excluirLocal(localId) {
    const result = await Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir este local de retirada?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    });
    
    if (result.isConfirmed) {
        try {
            const response = await fetch('../../../api/organizador/retirada-kits/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: localId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire('Sucesso', 'Local excluído com sucesso!', 'success');
                await carregarLocais(eventoSelecionado);
            } else {
                Swal.fire('Erro', data.error || 'Erro ao excluir local.', 'error');
            }
        } catch (error) {
            console.error('Erro ao excluir local:', error);
            Swal.fire('Erro', 'Erro ao excluir local.', 'error');
        }
    }
}

// Toggle status do local
async function toggleStatusLocal(localId, novoStatus) {
    try {
        const response = await fetch('../../../api/organizador/retirada-kits/toggle-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                id: localId, 
                ativo: novoStatus == 1 
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire('Sucesso', novoStatus == 1 ? 'Local ativado com sucesso!' : 'Local desativado com sucesso!', 'success');
            await carregarLocais(eventoSelecionado);
        } else {
            Swal.fire('Erro', data.error || 'Erro ao alterar status.', 'error');
        }
    } catch (error) {
        console.error('Erro ao alterar status:', error);
        Swal.fire('Erro', 'Erro ao alterar status do local.', 'error');
    }
}
