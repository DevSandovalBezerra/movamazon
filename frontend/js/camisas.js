// Variáveis globais
let camisas = [];
let eventos = [];
let eventoSelecionado = null;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Iniciando sistema de camisas');
    configurarEventListeners();
    carregarEventos();
});

// Configurar event listeners
function configurarEventListeners() {
    // Filtros
    document.getElementById('filtroEvento').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroStatus').addEventListener('change', aplicarFiltros);
    
    // Modal
    document.getElementById('form-camisa').addEventListener('submit', salvarCamisa);
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
            eventos = data.data.eventos; // Corrigido: usar estrutura original data.data.eventos
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

// Carregar camisas
async function carregarCamisas() {
    const eventoId = document.getElementById('filtroEvento').value;
    const status = document.getElementById('filtroStatus').value;
    
    if (!eventoId) {
        mostrarEstadoInicial();
        return;
    }
    
    try {
        console.log('📥 Carregando camisas...');
        const params = new URLSearchParams();
        params.append('evento_id', eventoId);
        if (status) params.append('ativo', status);
        
        const response = await fetch(`../../../api/organizador/camisas/list.php?${params}`);
        const data = await response.json();
        
        if (data.success) {
            camisas = data.camisas;
            if (camisas.length > 0) {
                renderizarCamisas();
                mostrarEstadoFiltrado();
            } else {
                mostrarEstadoVazio();
            }
            console.log(`✅ ${camisas.length} camisas carregadas`);
            
            // Carregar contador de camisas
            await carregarContadorCamisas(eventoId);
        } else {
            throw new Error(data.message || 'Erro ao carregar camisas');
        }
    } catch (error) {
        console.error('💥 Erro ao carregar camisas:', error);
        Swal.fire('Erro', 'Erro ao carregar camisas.', 'error');
    }
}

// Carregar contador de camisas
async function carregarContadorCamisas(eventoId) {
    try {
        console.log('📊 Carregando contador de camisas...');
        const response = await fetch(`../../../api/organizador/camisas/total.php?evento_id=${eventoId}`);
        const data = await response.json();
        
        if (data.success) {
            atualizarContadorCamisas(data);
            console.log('✅ Contador atualizado');
        } else {
            console.error('❌ Erro ao carregar contador:', data.message);
        }
    } catch (error) {
        console.error('💥 Erro ao carregar contador:', error);
    }
}

// Atualizar contador de camisas
function atualizarContadorCamisas(data) {
    const contador = document.getElementById('contador-camisas');
    const totalCamisas = document.getElementById('total-camisas');
    const limiteVagas = document.getElementById('limite-vagas');
    const disponivel = document.getElementById('disponivel');
    const disponivelLabel = document.getElementById('disponivel-label');
    const percentual = document.getElementById('percentual');
    const barraProgresso = document.getElementById('barra-progresso');
    
    // Atualizar valores
    totalCamisas.textContent = data.total_camisas;
    limiteVagas.textContent = data.limite_vagas;
    disponivel.textContent = data.disponivel;
    percentual.textContent = data.percentual + '%';
    
    // Atualizar barra de progresso
    barraProgresso.style.width = data.percentual + '%';
    
    // Definir cores baseado no status
    if (data.disponivel < 0) {
        // Excedeu o limite
        disponivel.classList.remove('text-green-600', 'text-yellow-600');
        disponivel.classList.add('text-red-600');
        disponivelLabel.textContent = 'Excedido';
        barraProgresso.classList.remove('bg-blue-600', 'bg-yellow-500');
        barraProgresso.classList.add('bg-red-500');
    } else if (data.percentual >= 90) {
        // Próximo do limite
        disponivel.classList.remove('text-green-600', 'text-red-600');
        disponivel.classList.add('text-yellow-600');
        disponivelLabel.textContent = 'Disponível';
        barraProgresso.classList.remove('bg-blue-600', 'bg-red-500');
        barraProgresso.classList.add('bg-yellow-500');
    } else {
        // Normal
        disponivel.classList.remove('text-yellow-600', 'text-red-600');
        disponivel.classList.add('text-green-600');
        disponivelLabel.textContent = 'Disponível';
        barraProgresso.classList.remove('bg-yellow-500', 'bg-red-500');
        barraProgresso.classList.add('bg-blue-600');
    }
    
    // Mostrar contador
    contador.classList.remove('hidden');
}

function renderizarCamisas() {
    const tbody = document.getElementById('tabela-camisas');
    tbody.innerHTML = '';
    
    camisas.forEach(camisa => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${camisa.tamanho}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${camisa.quantidade_inicial}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${camisa.quantidade_vendida}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${camisa.quantidade_disponivel}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${camisa.quantidade_reservada}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${camisa.ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${camisa.ativo ? 'Ativo' : 'Inativo'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <button onclick="editarCamisa(${camisa.id})" class="text-blue-600 hover:text-blue-900">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="excluirCamisa(${camisa.id})" class="text-red-600 hover:text-red-900">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Aplicar filtros
function aplicarFiltros() {
    const eventoId = document.getElementById('filtroEvento').value;
    const contador = document.getElementById('contador-camisas');
    
    // Esconder contador se não há evento selecionado
    if (!eventoId) {
        contador.classList.add('hidden');
        mostrarEstadoInicial();
        return;
    }
    
    carregarCamisas();
}

// Modal functions
function abrirModalCriar() {
    const eventoId = document.getElementById('filtroEvento').value;
    if (!eventoId) {
        Swal.fire('Atenção', 'Selecione um evento primeiro.', 'warning');
        return;
    }
    
    document.getElementById('modal-titulo').textContent = 'ADICIONAR TAMANHO';
    document.getElementById('camisa-id').value = '';
    document.getElementById('camisa-evento-id').value = eventoId;
    document.getElementById('camisa-tamanho').value = '';
    document.getElementById('camisa-quantidade').value = '';
    document.getElementById('camisa-status').value = '1';
    
    // Carregar informações de limite
    carregarInfoLimite(eventoId);
    
    document.getElementById('modal-camisa').classList.remove('hidden');
}

// Carregar informações de limite para o modal
async function carregarInfoLimite(eventoId) {
    try {
        const response = await fetch(`../../../api/organizador/camisas/total.php?evento_id=${eventoId}`);
        const data = await response.json();
        
        if (data.success) {
            const infoLimite = document.getElementById('info-limite');
            const disponivelTexto = document.getElementById('disponivel-texto');
            const limiteTexto = document.getElementById('limite-texto');
            
            disponivelTexto.textContent = data.disponivel;
            limiteTexto.textContent = data.limite_vagas;
            
            // Definir cor baseado no status
            if (data.disponivel < 0) {
                infoLimite.className = 'text-sm text-red-500 mt-1';
            } else if (data.disponivel < 10) {
                infoLimite.className = 'text-sm text-yellow-500 mt-1';
            } else {
                infoLimite.className = 'text-sm text-gray-500 mt-1';
            }
            
            infoLimite.classList.remove('hidden');
        }
    } catch (error) {
        console.error('💥 Erro ao carregar informações de limite:', error);
    }
}

function editarCamisa(id) {
    console.log('📝 Editando camisa:', id);
    fetch(`../../../api/organizador/camisas/get.php?id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.camisa) {
                preencherModalEdicao(data.camisa);
            } else {
                Swal.fire('Erro', 'Não foi possível carregar os dados.', 'error');
            }
        })
        .catch(err => {
            console.error('💥 Erro ao buscar dados para edição:', err);
            Swal.fire('Erro', 'Erro ao carregar dados.', 'error');
        });
}

function preencherModalEdicao(camisa) {
    document.getElementById('modal-titulo').textContent = 'EDITAR TAMANHO';
    document.getElementById('camisa-id').value = camisa.id;
    document.getElementById('camisa-evento-id').value = camisa.evento_id;
    document.getElementById('camisa-tamanho').value = camisa.tamanho;
    document.getElementById('camisa-quantidade').value = camisa.quantidade_inicial;
    document.getElementById('camisa-status').value = camisa.ativo ? '1' : '0';
    document.getElementById('modal-camisa').classList.remove('hidden');
}

function fecharModal() {
    document.getElementById('modal-camisa').classList.add('hidden');
}

function salvarCamisa(e) {
    e.preventDefault();
    
    const id = document.getElementById('camisa-id').value;
    const eventoId = document.getElementById('camisa-evento-id').value;
    const tamanho = document.getElementById('camisa-tamanho').value;
    const quantidade = document.getElementById('camisa-quantidade').value;
    const ativo = document.getElementById('camisa-status').value;
    
    if (!tamanho || !quantidade) {
        Swal.fire('Atenção', 'Preencha todos os campos obrigatórios.', 'warning');
        return;
    }
    
    const dados = {
        evento_id: eventoId,
        tamanho: tamanho,
        quantidade_inicial: parseInt(quantidade),
        ativo: parseInt(ativo)
    };
    
    if (id) {
        dados.id = id;
    }
    
    const url = id ? '../../../api/organizador/camisas/update.php' : '../../../api/organizador/camisas/create.php';
    const method = id ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sucesso', id ? 'Tamanho atualizado com sucesso!' : 'Tamanho criado com sucesso!', 'success');
            fecharModal();
            carregarCamisas();
        } else {
            Swal.fire('Erro', data.message || 'Erro ao salvar tamanho.', 'error');
        }
    })
    .catch(err => {
        console.error('💥 Erro ao salvar camisa:', err);
        Swal.fire('Erro', 'Erro ao salvar tamanho.', 'error');
    });
}

function excluirCamisa(id) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        text: 'Tem certeza que deseja excluir este tamanho? Esta ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../../../api/organizador/camisas/delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Sucesso', 'Tamanho excluído com sucesso!', 'success');
                    carregarCamisas();
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao excluir tamanho.', 'error');
                }
            })
            .catch(err => {
                console.error('💥 Erro ao excluir camisa:', err);
                Swal.fire('Erro', 'Erro ao excluir tamanho.', 'error');
            });
        }
    });
} 