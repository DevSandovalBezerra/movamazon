let eventoId = 0;
let modalidadeId = 0;
let lotesData = [];
let modalidadesData = [];
let editandoLote = false;

// Variáveis de paginação
let paginaAtual = 1;
let itensPorPagina = 9;
let lotesFiltrados = [];

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Event listener para mudança de evento
    document.getElementById('filtroEvento').addEventListener('change', function() {
        eventoId = this.value;
        if (eventoId) {
            carregarModalidades(eventoId);
        } else {
            document.getElementById('filtroModalidade').innerHTML = '<option value="">Selecione uma modalidade</option>';
            document.getElementById('filtroModalidade').disabled = true;
            mostrarSelecionarFiltros();
        }
    });

    // Event listener para mudança de modalidade
    document.getElementById('filtroModalidade').addEventListener('change', function() {
        modalidadeId = this.value;
    });

    // Event listener para mudança de itens por página
    document.getElementById('itens-por-pagina').addEventListener('change', function() {
        itensPorPagina = parseInt(this.value);
        paginaAtual = 1;
        atualizarPaginacao();
    });
});

// Função para carregar modalidades
async function carregarModalidades(eventoId) {
    try {
        const response = await fetch(`../../../api/organizador/modalidades/list.php?evento_id=${eventoId}`);
        const data = await response.json();

        if (data.success) {
            modalidadesData = data.modalidades;
            preencherSelectModalidades();
            document.getElementById('filtroModalidade').disabled = false;
        } else {
            throw new Error(data.message || 'Erro ao carregar modalidades');
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire('Erro', error.message, 'error');
    }
}

// Função para preencher select de modalidades
function preencherSelectModalidades() {
    const select = document.getElementById('filtroModalidade');
    select.innerHTML = '<option value="">Todas as modalidades</option>';
    
    modalidadesData.forEach(modalidade => {
        const option = document.createElement('option');
        option.value = modalidade.id;
        option.textContent = `${modalidade.nome_categoria} - ${modalidade.nome_modalidade}`;
        select.appendChild(option);
    });
}

// Função para aplicar filtros
function aplicarFiltros() {
    if (!eventoId) {
        Swal.fire('Atenção', 'Selecione um evento primeiro', 'warning');
        return;
    }

    carregarLotes();
}

// Função para carregar lotes
async function carregarLotes() {
    try {
        mostrarLoading();
        
        const url = `../../../api/organizador/lotes/list.php?evento_id=${eventoId}${modalidadeId ? `&modalidade_id=${modalidadeId}` : ''}`;
        console.log('🔍 Buscando lotes:', url);
        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
            lotesData = data.lotes;
            
            console.log('📊 Debug API Response:', {
                total_lotes: data.total_lotes,
                lotes_encontrados: data.lotes_encontrados,
                lotes_recebidos: data.lotes.length,
                lotes: data.lotes
            });
            
            // Atualizar contadores
            document.getElementById('contador-total').textContent = 
                `(Lotes do evento: ${data.total_lotes})`;
            
            renderizarLotes(lotesData);
        } else {
            throw new Error(data.message || 'Erro ao carregar lotes');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarErro(error.message);
    }
}

// Função para renderizar lotes
function renderizarLotes(lotes) {
    lotesFiltrados = lotes;
    paginaAtual = 1;
    atualizarPaginacao();
}

// Função para atualizar paginação
function atualizarPaginacao() {
    const totalLotes = lotesFiltrados.length;
    const totalPaginas = Math.ceil(totalLotes / itensPorPagina);
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const fim = inicio + itensPorPagina;
    const lotesPagina = lotesFiltrados.slice(inicio, fim);

    console.log('🔍 Debug Paginação:', {
        totalLotes: totalLotes,
        totalPaginas: totalPaginas,
        paginaAtual: paginaAtual,
        itensPorPagina: itensPorPagina,
        inicio: inicio,
        fim: fim,
        lotesPagina: lotesPagina.length
    });

    // Atualizar informações
    const textoPagina = totalLotes > 0 ? 
        `Mostrando ${inicio + 1}-${Math.min(fim, totalLotes)} de ${totalLotes} lotes` :
        'Nenhum lote encontrado';
    document.getElementById('info-paginacao').textContent = textoPagina;

    // Renderizar lotes da página atual
    const container = document.getElementById('lotes-grid');
    container.innerHTML = '';
    
    if (lotesPagina.length === 0) {
        mostrarSemLotes();
        return;
    }

    lotesPagina.forEach(lote => {
        const card = criarCardLote(lote);
        container.appendChild(card);
    });

    // Atualizar controles de paginação
    atualizarControlesPaginacao(totalPaginas);
    mostrarLotes();
}

// Função para atualizar controles de paginação
function atualizarControlesPaginacao(totalPaginas) {
    const btnAnterior = document.getElementById('btn-anterior');
    const btnProximo = document.getElementById('btn-proximo');
    const numerosPagina = document.getElementById('numeros-pagina');

    // Habilitar/desabilitar botões
    btnAnterior.disabled = paginaAtual <= 1;
    btnProximo.disabled = paginaAtual >= totalPaginas;

    // Gerar números de página
    numerosPagina.innerHTML = '';
    
    const inicio = Math.max(1, paginaAtual - 2);
    const fim = Math.min(totalPaginas, paginaAtual + 2);

    for (let i = inicio; i <= fim; i++) {
        const btn = document.createElement('button');
        btn.className = `px-3 py-2 border rounded-lg ${i === paginaAtual ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-700 border-gray-300 hover:bg-gray-50'}`;
        btn.textContent = i;
        btn.onclick = () => irParaPagina(i);
        numerosPagina.appendChild(btn);
    }
}

// Funções de navegação
function paginaAnterior() {
    if (paginaAtual > 1) {
        paginaAtual--;
        atualizarPaginacao();
    }
}

function paginaProximo() {
    const totalPaginas = Math.ceil(lotesFiltrados.length / itensPorPagina);
    if (paginaAtual < totalPaginas) {
        paginaAtual++;
        atualizarPaginacao();
    }
}

function irParaPagina(pagina) {
    paginaAtual = pagina;
    atualizarPaginacao();
}

// Função para criar card de lote
function criarCardLote(lote) {
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow';
    
    // Gerar HTML dos preços
    let precosHtml = '';
    if (lote.precos && lote.precos.length > 0) {
        precosHtml = `
            <div class="mt-4 pt-4 border-t border-gray-100">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Preços:</h4>
                <div class="space-y-2">
                    ${lote.precos.map(preco => `
                        <div class="flex justify-between text-xs bg-gray-50 p-2 rounded">
                            <div>
                                <span class="font-medium">${preco.data_inicio} a ${preco.data_fim}</span>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-green-600">R$ ${preco.preco}</div>
                                ${preco.taxa_ticket_sports ? `<div class="text-xs text-gray-500">Taxa: R$ ${preco.taxa_ticket_sports}</div>` : ''}
                                ${preco.desconto_percentual ? `<div class="text-xs text-blue-600">Desconto: ${preco.desconto_percentual}</div>` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    card.innerHTML = `
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tags text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 text-lg">${lote.categoria_lote}</h3>
                        <p class="text-sm text-gray-600">${lote.modalidade_nome}</p>
                        <p class="text-xs text-gray-500">${lote.categoria_modalidade}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="editarLote(${lote.id})" class="text-blue-600 hover:text-blue-800 p-2 hover:bg-blue-50 rounded">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="excluirLote(${lote.id})" class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-blue-50 p-3 rounded-lg">
                    <div class="text-xs text-blue-600 font-medium">Faixa Etária</div>
                    <div class="text-sm font-semibold">${lote.idade_min} - ${lote.idade_max} anos</div>
                </div>
                <div class="bg-green-50 p-3 rounded-lg">
                    <div class="text-xs text-green-600 font-medium">Vagas</div>
                    <div class="text-sm font-semibold">${lote.limite_vagas ? lote.limite_vagas : 'Ilimitado'}</div>
                </div>
            </div>
            
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Preço:</span>
                    <span class="font-semibold text-lg text-green-600">R$ ${lote.preco_minimo} - R$ ${lote.preco_maximo}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Desconto idoso:</span>
                    <span class="font-medium ${lote.desconto_idoso ? 'text-green-600' : 'text-gray-500'}">${lote.desconto_idoso ? 'Sim' : 'Não'}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total de preços:</span>
                    <span class="font-medium text-blue-600">${lote.total_precos}</span>
                </div>
            </div>
            
            ${precosHtml}
        </div>
    `;
    
    return card;
}

// Funções de estado
function mostrarLoading() {
    document.getElementById('loading-lotes').classList.remove('hidden');
    document.getElementById('lotes-container').classList.add('hidden');
    document.getElementById('error-lotes').classList.add('hidden');
    document.getElementById('sem-lotes').classList.add('hidden');
    document.getElementById('selecionar-filtros').classList.add('hidden');
}

function mostrarLotes() {
    document.getElementById('loading-lotes').classList.add('hidden');
    document.getElementById('lotes-container').classList.remove('hidden');
    document.getElementById('error-lotes').classList.add('hidden');
    document.getElementById('sem-lotes').classList.add('hidden');
    document.getElementById('selecionar-filtros').classList.add('hidden');
}

function mostrarErro(mensagem) {
    document.getElementById('loading-lotes').classList.add('hidden');
    document.getElementById('lotes-container').classList.add('hidden');
    document.getElementById('error-lotes').classList.remove('hidden');
    document.getElementById('error-message').textContent = mensagem;
    document.getElementById('sem-lotes').classList.add('hidden');
    document.getElementById('selecionar-filtros').classList.add('hidden');
}

function mostrarSemLotes() {
    document.getElementById('loading-lotes').classList.add('hidden');
    document.getElementById('lotes-container').classList.add('hidden');
    document.getElementById('error-lotes').classList.add('hidden');
    document.getElementById('sem-lotes').classList.remove('hidden');
    document.getElementById('selecionar-filtros').classList.add('hidden');
}

function mostrarSelecionarFiltros() {
    document.getElementById('loading-lotes').classList.add('hidden');
    document.getElementById('lotes-container').classList.add('hidden');
    document.getElementById('error-lotes').classList.add('hidden');
    document.getElementById('sem-lotes').classList.add('hidden');
    document.getElementById('selecionar-filtros').classList.remove('hidden');
}

// Funções do modal
function abrirModalCriarLote() {
    if (!eventoId) {
        Swal.fire('Atenção', 'Selecione um evento primeiro', 'warning');
        return;
    }

    editandoLote = false;
    document.getElementById('modalTitulo').textContent = 'Criar Novo Lote';
    document.getElementById('btnSalvarTexto').textContent = 'Criar Lote';
    document.getElementById('formLote').reset();
    document.getElementById('lote_id').value = '';
    limparPrecos();
    
    // Preencher select de modalidades no modal
    const selectModalidade = document.getElementById('modalidade_id');
    selectModalidade.innerHTML = '<option value="">Selecione uma modalidade</option>';
    modalidadesData.forEach(modalidade => {
        const option = document.createElement('option');
        option.value = modalidade.id;
        option.textContent = `${modalidade.nome_categoria} - ${modalidade.nome_modalidade}`;
        selectModalidade.appendChild(option);
    });
    
    document.getElementById('modalLote').classList.remove('hidden');
}

function fecharModalLote() {
    document.getElementById('modalLote').classList.add('hidden');
}

function limparPrecos() {
    document.getElementById('precos-container').innerHTML = '';
}

function adicionarPreco() {
    const container = document.getElementById('precos-container');
    const precoId = Date.now();
    
    const precoHtml = `
        <div class="border border-gray-200 rounded-lg p-4" data-preco-id="${precoId}">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">Preço ${container.children.length + 1}</h4>
                <button type="button" onclick="removerPreco(${precoId})" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                    <input type="date" name="precos[${precoId}][data_inicio]" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                    <input type="date" name="precos[${precoId}][data_fim]" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preço (R$)</label>
                    <input type="number" name="precos[${precoId}][preco]" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Taxa Ticket Sports (R$)</label>
                    <input type="number" name="precos[${precoId}][taxa_ticket_sports]" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desconto (%)</label>
                    <input type="number" name="precos[${precoId}][desconto_percentual]" step="0.1" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', precoHtml);
}

function removerPreco(precoId) {
    const elemento = document.querySelector(`[data-preco-id="${precoId}"]`);
    if (elemento) {
        elemento.remove();
    }
}

// Função para editar lote
async function editarLote(loteId) {
    try {
        const response = await fetch(`../../../api/organizador/lotes/get.php?lote_id=${loteId}`);
        const data = await response.json();

        if (data.success) {
            preencherFormularioLote(data.lote);
            editandoLote = true;
            document.getElementById('modalTitulo').textContent = 'Editar Lote';
            document.getElementById('btnSalvarTexto').textContent = 'Atualizar Lote';
            document.getElementById('modalLote').classList.remove('hidden');
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire('Erro', error.message, 'error');
    }
}

// Função para preencher formulário
function preencherFormularioLote(lote) {
    document.getElementById('lote_id').value = lote.id;
    document.getElementById('modalidade_id').value = lote.modalidade_id;
    document.getElementById('categoria_modalidade').value = lote.categoria_lote;
    document.getElementById('idade_min').value = lote.idade_min;
    document.getElementById('idade_max').value = lote.idade_max;
    document.getElementById('limite_vagas').value = lote.limite_vagas || '';
    document.getElementById('desconto_idoso').checked = lote.desconto_idoso;

    // Preencher preços
    limparPrecos();
    lote.precos.forEach(preco => {
        adicionarPreco();
        const ultimoPreco = document.querySelector('#precos-container > div:last-child');
        if (ultimoPreco) {
            ultimoPreco.querySelector('input[name*="[data_inicio]"]').value = preco.data_inicio;
            ultimoPreco.querySelector('input[name*="[data_fim]"]').value = preco.data_fim;
            ultimoPreco.querySelector('input[name*="[preco]"]').value = preco.preco.replace(',', '.');
            ultimoPreco.querySelector('input[name*="[taxa_ticket_sports]"]').value = preco.taxa_ticket_sports ? preco.taxa_ticket_sports.replace(',', '.') : '';
            ultimoPreco.querySelector('input[name*="[desconto_percentual]"]').value = preco.desconto_percentual ? preco.desconto_percentual.replace('%', '') : '';
        }
    });
}

// Função para excluir lote
async function excluirLote(loteId) {
    const result = await Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir este lote?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            const formData = new FormData();
            formData.append('lote_id', loteId);

            const response = await fetch('../../../api/organizador/lotes/delete.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire('Sucesso', 'Lote excluído com sucesso', 'success');
                carregarLotes();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            Swal.fire('Erro', error.message, 'error');
        }
    }
}

// Event listener para o formulário
document.getElementById('formLote').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        const formData = new FormData(this);
        
        // Processar preços
        const precos = [];
        const precosElements = document.querySelectorAll('#precos-container > div');
        precosElements.forEach(element => {
            const dataInicio = element.querySelector('input[name*="[data_inicio]"]').value;
            const dataFim = element.querySelector('input[name*="[data_fim]"]').value;
            const preco = element.querySelector('input[name*="[preco]"]').value;
            const taxaTicketSports = element.querySelector('input[name*="[taxa_ticket_sports]"]').value;
            const descontoPercentual = element.querySelector('input[name*="[desconto_percentual]"]').value;

            if (dataInicio && dataFim && preco) {
                precos.push({
                    data_inicio: dataInicio,
                    data_fim: dataFim,
                    preco: preco,
                    taxa_ticket_sports: taxaTicketSports || null,
                    desconto_percentual: descontoPercentual || null
                });
            }
        });

        formData.append('precos', JSON.stringify(precos));

        const url = editandoLote ? '../../../api/organizador/lotes/update.php' : '../../../api/organizador/lotes/create.php';
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire('Sucesso', data.message, 'success');
            fecharModalLote();
            carregarLotes();
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire('Erro', error.message, 'error');
    }
});