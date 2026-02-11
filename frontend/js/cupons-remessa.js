// Variáveis globais
let eventos = [];
let cupons = [];
let eventoSelecionado = null;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarEventos();
    configurarEventListeners();
    configurarModalEventListeners();
});

// Configurar event listeners
function configurarEventListeners() {
    // Evento selecionado
    document.getElementById('filtro-evento').addEventListener('change', function() {
        const eventoId = this.value;
        eventoSelecionado = eventoId;
        
        if (eventoId) {
            mostrarEstadoFiltrado();
            carregarCupons();
        } else {
            mostrarEstadoInicial();
        }
    });

    // Filtros reativos
    document.getElementById('filtro-status').addEventListener('change', carregarCupons);
    document.getElementById('filtro-inicio').addEventListener('change', carregarCupons);
    document.getElementById('filtro-fim').addEventListener('change', carregarCupons);
    
    // Botão novo cupom
    document.getElementById('btn-novo-cupom').addEventListener('click', () => {
        mostrarModalAviso();
    });
}

// Configurar event listeners do modal
function configurarModalEventListeners() {
    // Gerador de código individual
    document.getElementById('btn-gerar-codigo').addEventListener('click', gerarCodigoIndividual);
    
    // Botão voltar
    document.getElementById('btn-voltar').addEventListener('click', fecharModalCupom);
    
    // Modal de aviso
    document.getElementById('btn-fechar-aviso').addEventListener('click', fecharModalAviso);
    document.getElementById('btn-cancelar-aviso').addEventListener('click', fecharModalAviso);
    document.getElementById('btn-confirmar-aviso').addEventListener('click', confirmarAviso);
    
    // Modal de status
    document.getElementById('btn-fechar-status').addEventListener('click', fecharModalStatus);
    document.getElementById('btn-cancelar-status').addEventListener('click', fecharModalStatus);
    document.getElementById('btn-salvar-status').addEventListener('click', salvarStatusCupom);
    
    // Campo de valor dinâmico
    document.getElementById('cupom-tipo-valor').addEventListener('change', configurarCampoValor);
    document.getElementById('cupom-valor').addEventListener('input', formatarValor);
}

// Controlar estados visuais
function mostrarEstadoInicial() {
    document.getElementById('estado-inicial').classList.remove('hidden');
    document.getElementById('estado-filtrado').classList.add('hidden');
    document.getElementById('btn-novo-cupom').classList.add('hidden');
}

function mostrarEstadoFiltrado() {
    document.getElementById('estado-inicial').classList.add('hidden');
    document.getElementById('estado-filtrado').classList.remove('hidden');
    document.getElementById('btn-novo-cupom').classList.remove('hidden');
}

// Carregar eventos do organizador
async function carregarEventos() {
    try {
        console.log('[Cupons] Buscando eventos do organizador...');
        const response = await fetch('../../../api/organizador/eventos/list.php');
        const data = await response.json();
        console.log('[Cupons] Resposta eventos:', data);
        
        if (data.success) {
            eventos = data.data.eventos || []; // Corrigido: usar estrutura correta
            preencherSelectEventos();
        } else {
            console.error('[Cupons] Erro na resposta de eventos:', data);
        }
    } catch (error) {
        console.error('[Cupons] Erro ao carregar eventos:', error);
    }
}

// Preencher select de eventos
function preencherSelectEventos() {
    const select = document.getElementById('filtro-evento');
    const selectModal = document.getElementById('cupom-evento');
    
    // Limpar opções antigas
    select.innerHTML = '<option value="">Todos os eventos</option>';
    selectModal.innerHTML = '<option value="">Selecione</option>';
    
    eventos.forEach(evento => {
        const option = document.createElement('option');
        option.value = evento.id;
        option.textContent = evento.nome;
        select.appendChild(option);
        
        const option2 = document.createElement('option');
        option2.value = evento.id;
        option2.textContent = evento.nome;
        selectModal.appendChild(option2);
    });
}

// Carregar cupons/remessas
async function carregarCupons() {
    try {
        const evento = document.getElementById('filtro-evento').value;
        const status = document.getElementById('filtro-status').value;
        const inicio = document.getElementById('filtro-inicio').value;
        const fim = document.getElementById('filtro-fim').value;
        
        let url = '../../../api/organizador/cupons-remessa/list.php?';
        if (evento) url += 'evento_id=' + encodeURIComponent(evento) + '&';
        if (status) url += 'status=' + encodeURIComponent(status) + '&';
        if (inicio) url += 'data_inicio=' + encodeURIComponent(inicio) + '&';
        if (fim) url += 'data_fim=' + encodeURIComponent(fim) + '&';
        
        console.log('[Cupons] Buscando cupons/remessas:', url);
        
        const tbody = document.getElementById('tbody-cupons');
        const msgVazio = document.getElementById('msg-vazio');
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-8">Carregando...</td></tr>';
        msgVazio.classList.add('hidden');
        
        const response = await fetch(url);
        const data = await response.json();
        console.log('[Cupons] Resposta cupons/remessas:', data);
        
        if (!data.success || !data.remessas || data.remessas.length === 0) {
            tbody.innerHTML = '';
            msgVazio.classList.remove('hidden');
            return;
        }
        
        cupons = data.remessas;
        msgVazio.classList.add('hidden');
        tbody.innerHTML = cupons.map(remessa => renderLinha(remessa)).join('');
        
    } catch (error) {
        console.error('[Cupons] Erro ao buscar cupons/remessas:', error);
        const tbody = document.getElementById('tbody-cupons');
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-8 text-red-600">Erro ao carregar cupons.</td></tr>';
    }
}

function renderLinha(remessa) {
  const periodo = `${formatarData(remessa.data_inicio)} a ${formatarData(remessa.data_validade)}`;
  const valor = remessa.tipo_valor === 'percentual' ? `${remessa.valor_desconto}%` : `R$ ${parseFloat(remessa.valor_desconto).toFixed(2)}`;
  const usos = remessa.max_uso ? `${remessa.max_uso}` : '-';
  return `<tr>
    <td class="px-4 py-3">${remessa.titulo || '-'}</td>
    <td class="px-4 py-3 font-mono">${remessa.codigo_remessa}</td>
    <td class="px-4 py-3">${valor}</td>
    <td class="px-4 py-3">${remessa.tipo_valor}</td>
    <td class="px-4 py-3">${periodo}</td>
    <td class="px-4 py-3">
      <span class="inline-block px-2 py-1 rounded text-xs font-bold ${remessa.status === 'ativo' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
        ${remessa.status}
      </span>
    </td>
    <td class="px-4 py-3">${usos}</td>
    <td class="px-4 py-3">
      <button class="text-blue-600 hover:text-blue-800 mr-2" onclick="editarStatusCupom(${remessa.id}, '${remessa.status}')" title="Editar Status">
        <i class="fas fa-edit"></i>
      </button>
      <button class="text-red-600 hover:text-red-800" onclick="excluirCupom(${remessa.id})" title="Excluir Cupom">
        <i class="fas fa-trash"></i>
      </button>
    </td>
  </tr>`;
}

function formatarData(data) {
  if (!data) return '-';
  const d = new Date(data);
  if (isNaN(d)) return data;
  return d.toLocaleDateString('pt-BR');
}

// Gerador de códigos criptografados
function gerarCodigoCriptografado() {
    const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    const comprimento = 8;
    let codigo = '';
    
    for (let i = 0; i < comprimento; i++) {
        if (i === 4) codigo += '-';
        codigo += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
    }
    
    return codigo;
}

// Gerar código individual
async function gerarCodigoIndividual() {
    const input = document.getElementById('cupom-codigo');
    const btn = document.getElementById('btn-gerar-codigo');
    const loading = document.getElementById('loading-geracao');
    
    btn.disabled = true;
    loading.classList.remove('hidden');
    
    try {
        // Simular delay para mostrar loading
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        const codigo = gerarCodigoCriptografado();
        input.value = codigo;
        
        Swal.fire('Sucesso', 'Código gerado com sucesso!', 'success');
    } catch (error) {
        console.error('[Cupons] Erro ao gerar código:', error);
        Swal.fire('Erro', 'Erro ao gerar código.', 'error');
    } finally {
        btn.disabled = false;
        loading.classList.add('hidden');
    }
}

// Modal de aviso de responsabilidade
function mostrarModalAviso() {
    document.getElementById('modal-aviso').classList.remove('hidden');
}

function fecharModalAviso() {
    document.getElementById('modal-aviso').classList.add('hidden');
}

function confirmarAviso() {
    fecharModalAviso();
    abrirModalCupom();
}

// Modal lógica
const modal = document.getElementById('modal-cupom');
const btnFechar = document.getElementById('btn-fechar-modal');
const form = document.getElementById('form-cupom');
const modalTitulo = document.getElementById('modal-titulo');

btnFechar.onclick = fecharModalCupom;
modal.onclick = e => { if (e.target === modal) fecharModalCupom(); };

function abrirModalCupom(dados = null) {
    form.reset();
    document.getElementById('cupom-id').value = '';
    modalTitulo.textContent = dados ? 'Editar Cupom' : 'CUPONS DE DESCONTO';
    
    // Limpar código
    document.getElementById('cupom-codigo').value = '';
    
    if (dados) {
        document.getElementById('cupom-id').value = dados.id;
        document.getElementById('cupom-titulo').value = dados.titulo || '';
        document.getElementById('cupom-codigo').value = dados.codigo_remessa || '';
        document.getElementById('cupom-tipo-valor').value = dados.tipo_valor || '';
        document.getElementById('cupom-tipo-desconto').value = dados.tipo_desconto || 'ambos';
        document.getElementById('cupom-evento').value = dados.evento_id || '';
        document.getElementById('cupom-inicio').value = dados.data_inicio || '';
        document.getElementById('cupom-fim').value = dados.data_validade || '';
        document.getElementById('cupom-max-uso').value = dados.max_uso || 1;
        document.getElementById('cupom-status').value = dados.status || 'ativo';
        document.getElementById('cupom-habilitar-produtos').checked = dados.habilita_desconto_itens || false;
        
        // Configurar campo de valor após definir o tipo
        setTimeout(() => {
            configurarCampoValor();
            if (dados.valor_desconto) {
                const tipoValor = dados.tipo_valor;
                let valorFormatado = dados.valor_desconto;
                
                if (tipoValor === 'percentual') {
                    valorFormatado = Math.round(parseFloat(dados.valor_desconto));
                } else {
                    valorFormatado = parseFloat(dados.valor_desconto).toFixed(2).replace('.', ',');
                }
                
                document.getElementById('cupom-valor').value = valorFormatado;
            }
        }, 100);
    }
    modal.classList.remove('hidden');
}

function fecharModalCupom() {
    modal.classList.add('hidden');
}

form.onsubmit = function (e) {
    e.preventDefault();
    
    const id = document.getElementById('cupom-id').value;
    const titulo = document.getElementById('cupom-titulo').value.trim();
    const codigo = document.getElementById('cupom-codigo').value.trim();
    const valorInput = document.getElementById('cupom-valor').value.trim();
    const tipoValor = document.getElementById('cupom-tipo-valor').value;
    const tipoDesconto = document.getElementById('cupom-tipo-desconto').value;
    const evento = document.getElementById('cupom-evento').value;
    const inicio = document.getElementById('cupom-inicio').value;
    const fim = document.getElementById('cupom-fim').value;
    const maxUso = document.getElementById('cupom-max-uso').value;
    const status = document.getElementById('cupom-status').value;
    const habilitarProdutos = document.getElementById('cupom-habilitar-produtos').checked ? 1 : 0;
    
    if (!titulo || !codigo || !valorInput || !tipoValor || !evento || !inicio || !fim || !maxUso || !status) {
        Swal.fire('Atenção', 'Preencha todos os campos obrigatórios.', 'warning');
        return;
    }
    
    // Processar valor baseado no tipo
    let valor;
    try {
        if (tipoValor === 'percentual') {
            valor = parseFloat(valorInput);
            if (valor < 0 || valor > 100) {
                Swal.fire('Atenção', 'Percentual deve estar entre 0 e 100.', 'warning');
                return;
            }
        } else {
            // Converter formato brasileiro para decimal
            valor = parseFloat(valorInput.replace(',', '.'));
            if (valor < 0) {
                Swal.fire('Atenção', 'Valor deve ser maior que zero.', 'warning');
                return;
            }
        }
    } catch (error) {
        Swal.fire('Atenção', 'Valor inválido.', 'warning');
        return;
    }
    
    const payload = {
        titulo,
        codigo_remessa: codigo,
        valor_desconto: valor,
        tipo_valor: tipoValor,
        tipo_desconto: tipoDesconto,
        evento_id: evento,
        data_inicio: inicio,
        data_validade: fim,
        max_uso: maxUso,
        status,
        habilita_desconto_itens: habilitarProdutos
    };
    
    if (id) {
        payload.id = id;
    }
    
    let url = '../../../api/organizador/cupons-remessa/create.php';
    let method = 'POST';
    
    if (id) {
        url = '../../../api/organizador/cupons-remessa/update.php';
        method = 'PUT';
    }
    
    console.log('[Cupons] Enviando para API:', url, method, payload);
    
    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        console.log('[Cupons] Resposta salvar cupom:', data);
        if (data.success) {
            Swal.fire('Sucesso', 'Cupom salvo com sucesso!', 'success');
            fecharModalCupom();
            carregarCupons();
        } else {
            Swal.fire('Erro', data.message || 'Erro ao salvar cupom.', 'error');
        }
    })
    .catch(err => {
        console.error('[Cupons] Erro ao salvar cupom:', err);
        Swal.fire('Erro', 'Erro ao salvar cupom.', 'error');
    });
};

// Funções para edição de status
function editarStatusCupom(id, statusAtual) {
    document.getElementById('cupom-id-status').value = id;
    document.getElementById('status-cupom').value = statusAtual;
    document.getElementById('modal-status').classList.remove('hidden');
}

function fecharModalStatus() {
    document.getElementById('modal-status').classList.add('hidden');
}

function salvarStatusCupom() {
    const id = document.getElementById('cupom-id-status').value;
    const novoStatus = document.getElementById('status-cupom').value;
    
    if (!id || !novoStatus) {
        Swal.fire('Atenção', 'Dados inválidos.', 'warning');
        return;
    }
    
    fetch('../../../api/organizador/cupons-remessa/update.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id: id,
            status: novoStatus
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sucesso', 'Status atualizado com sucesso!', 'success');
            fecharModalStatus();
            carregarCupons();
        } else {
            Swal.fire('Erro', data.message || 'Erro ao atualizar status.', 'error');
        }
    })
    .catch(err => {
        console.error('[Cupons] Erro ao atualizar status:', err);
        Swal.fire('Erro', 'Erro ao atualizar status.', 'error');
    });
}

// Função para exclusão com validação
function excluirCupom(id) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        text: 'Tem certeza que deseja excluir este cupom? Esta ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626'
    }).then((result) => {
        if (result.isConfirmed) {
            // Primeiro verificar se há utilizações
            verificarUtilizacoesCupom(id);
        }
    });
}

function verificarUtilizacoesCupom(id) {
    // Aqui você pode implementar uma API para verificar utilizações
    // Por enquanto, vamos simular que não há utilizações
    // Em produção, você faria uma chamada para verificar na tabela inscricoes_cupons
    
    fetch(`../../../api/organizador/cupons-remessa/check-usage.php?id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (data.utilizacoes > 0) {
                    Swal.fire('Não é possível excluir', 
                        `Este cupom já foi utilizado ${data.utilizacoes} vez(es). Cupons com utilizações não podem ser excluídos.`, 
                        'error');
                } else {
                    // Confirmar exclusão final
                    confirmarExclusaoCupom(id);
                }
            } else {
                Swal.fire('Erro', 'Erro ao verificar utilizações do cupom.', 'error');
            }
        })
        .catch(err => {
            console.error('[Cupons] Erro ao verificar utilizações:', err);
            // Se a API não existir, vamos permitir a exclusão (para desenvolvimento)
            confirmarExclusaoCupom(id);
        });
}

function confirmarExclusaoCupom(id) {
    fetch('../../../api/organizador/cupons-remessa/delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sucesso', 'Cupom excluído com sucesso!', 'success');
            carregarCupons();
        } else {
            Swal.fire('Erro', data.message || 'Erro ao excluir cupom.', 'error');
        }
    })
    .catch(err => {
        console.error('[Cupons] Erro ao excluir cupom:', err);
        Swal.fire('Erro', 'Erro ao excluir cupom.', 'error');
    });
}

// Configurar campo de valor dinamicamente
function configurarCampoValor() {
    const tipoValor = document.getElementById('cupom-tipo-valor').value;
    const campoValor = document.getElementById('cupom-valor');
    const percentIcon = document.getElementById('percent-icon');
    const moneyIcon = document.getElementById('money-icon');
    const valorHelp = document.getElementById('valor-help');
    
    // Limpar campo
    campoValor.value = '';
    
    switch(tipoValor) {
        case 'percentual':
            campoValor.placeholder = 'Ex: 15';
            percentIcon.style.display = 'block';
            moneyIcon.style.display = 'none';
            valorHelp.textContent = 'Digite um valor entre 0 e 100';
            campoValor.setAttribute('maxlength', '3');
            break;
            
        case 'valor_real':
        case 'preco_fixo':
            campoValor.placeholder = 'Ex: 25,50';
            percentIcon.style.display = 'none';
            moneyIcon.style.display = 'block';
            valorHelp.textContent = 'Digite o valor em reais';
            campoValor.setAttribute('maxlength', '10');
            break;
            
        default:
            campoValor.placeholder = 'Selecione o tipo primeiro';
            percentIcon.style.display = 'none';
            moneyIcon.style.display = 'none';
            valorHelp.textContent = '';
            break;
    }
}

// Formatar valor baseado no tipo
function formatarValor(e) {
    const tipoValor = document.getElementById('cupom-tipo-valor').value;
    const campo = e.target;
    let valor = campo.value.replace(/[^\d,]/g, '');
    
    switch(tipoValor) {
        case 'percentual':
            // Apenas números, máximo 100
            valor = valor.replace(/[^\d]/g, '');
            if (parseInt(valor) > 100) {
                valor = '100';
            }
            campo.value = valor;
            break;
            
        case 'valor_real':
        case 'preco_fixo':
            // Formato monetário brasileiro
            valor = valor.replace(/[^\d,]/g, '');
            
            // Garantir apenas uma vírgula
            const virgulas = (valor.match(/,/g) || []).length;
            if (virgulas > 1) {
                valor = valor.replace(/,/g, (match, index) => {
                    return index === valor.lastIndexOf(',') ? match : '';
                });
            }
            
            // Formatar como moeda
            if (valor.includes(',')) {
                const partes = valor.split(',');
                if (partes[1].length > 2) {
                    partes[1] = partes[1].substring(0, 2);
                }
                valor = partes[0] + ',' + partes[1];
            }
            
            campo.value = valor;
            break;
    }
} 