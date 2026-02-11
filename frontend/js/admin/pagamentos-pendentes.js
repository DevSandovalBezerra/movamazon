/**
 * JavaScript para página de Pagamentos Pendentes (Admin)
 */

const API_BASE = '../../../api/admin';

let currentOffset = 0;
let currentLimit = 50;
let selectedIds = new Set();
let currentFilters = {
    horas_minimas: 24,
    evento_id: null
};

document.addEventListener('DOMContentLoaded', function() {
    carregarPagamentos();
    carregarEventos();
    
    // Event listeners
    document.getElementById('btn-aplicar-filtros')?.addEventListener('click', aplicarFiltros);
    document.getElementById('select-all')?.addEventListener('change', toggleSelectAll);
    document.getElementById('btn-sincronizar-todos')?.addEventListener('click', sincronizarSelecionados);
    
    // Fechar modais
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.getAttribute('data-close-modal');
            document.getElementById(modalId)?.classList.add('hidden');
        });
    });
});

function carregarPagamentos() {
    const loading = document.getElementById('pagamentos-loading');
    const container = document.getElementById('pagamentos-container');
    const empty = document.getElementById('pagamentos-empty');
    
    loading.classList.remove('hidden');
    container.innerHTML = '';
    empty.classList.add('hidden');
    
    const params = new URLSearchParams({
        horas_minimas: currentFilters.horas_minimas,
        limite: currentLimit,
        offset: currentOffset
    });
    
    if (currentFilters.evento_id) {
        params.append('evento_id', currentFilters.evento_id);
    }
    
    fetch(`${API_BASE}/get_pagamentos_pendentes.php?${params}`)
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            
            if (!data.success) {
                mostrarErro('Erro ao carregar pagamentos: ' + (data.message || 'Erro desconhecido'));
                return;
            }
            
            // Atualizar estatísticas
            if (data.stats) {
                document.getElementById('stat-total-pendentes').textContent = data.stats.total_pendentes || 0;
                document.getElementById('stat-pendentes-24h').textContent = data.stats.pendentes_24h || 0;
                document.getElementById('stat-pendentes-72h').textContent = data.stats.pendentes_72h || 0;
                document.getElementById('stat-valor-total').textContent = 
                    'R$ ' + (data.stats.valor_total_pendente || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
            
            if (data.data.length === 0) {
                empty.classList.remove('hidden');
                return;
            }
            
            // Renderizar pagamentos
            data.data.forEach(pagamento => {
                const card = criarCardPagamento(pagamento);
                container.appendChild(card);
            });
            
            // Paginação
            atualizarPaginacao(data.pagination);
        })
        .catch(error => {
            loading.classList.add('hidden');
            mostrarErro('Erro ao carregar pagamentos: ' + error.message);
        });
}

function criarCardPagamento(pagamento) {
    const div = document.createElement('div');
    div.className = 'p-4 hover:bg-gray-50 transition-colors';
    div.dataset.inscricaoId = pagamento.inscricao_id;
    
    const horasPendente = pagamento.horas_pendente || 0;
    const badgeClass = horasPendente >= 72 ? 'bg-red-100 text-red-800' : 
                      horasPendente >= 24 ? 'bg-orange-100 text-orange-800' : 
                      'bg-yellow-100 text-yellow-800';
    
    div.innerHTML = `
        <div class="flex items-center gap-4">
            <input type="checkbox" class="checkbox-pagamento rounded" 
                   data-inscricao-id="${pagamento.inscricao_id}"
                   data-payment-id="${pagamento.payment_id || ''}">
            <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <span class="font-semibold text-gray-900">Inscrição #${pagamento.inscricao_id}</span>
                        <span class="ml-2 text-sm text-gray-500">${pagamento.evento_nome}</span>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-medium ${badgeClass}">
                        ${horasPendente}h pendente
                    </span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Participante:</span>
                        <div>${pagamento.usuario_nome}</div>
                        <div class="text-xs text-gray-500">${pagamento.usuario_email}</div>
                    </div>
                    <div>
                        <span class="font-medium">Valor:</span>
                        <div class="text-green-600 font-semibold">R$ ${parseFloat(pagamento.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
                    </div>
                    <div>
                        <span class="font-medium">Data Inscrição:</span>
                        <div>${formatarData(pagamento.data_inscricao)}</div>
                    </div>
                    <div>
                        <span class="font-medium">Payment ID:</span>
                        <div class="text-xs font-mono">${pagamento.payment_id || 'N/A'}</div>
                    </div>
                </div>
            </div>
            <button class="btn-primary btn-sincronizar-individual" 
                    data-inscricao-id="${pagamento.inscricao_id}"
                    data-payment-id="${pagamento.payment_id || ''}">
                <i class="fas fa-sync-alt w-4 h-4"></i>
                Sincronizar
            </button>
        </div>
    `;
    
    // Event listener para checkbox
    const checkbox = div.querySelector('.checkbox-pagamento');
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            selectedIds.add(pagamento.inscricao_id);
        } else {
            selectedIds.delete(pagamento.inscricao_id);
        }
        atualizarContadorSelecionados();
    });
    
    // Event listener para sincronizar individual
    const btnSync = div.querySelector('.btn-sincronizar-individual');
    btnSync.addEventListener('click', () => sincronizarPagamento(pagamento.inscricao_id, pagamento.payment_id));
    
    return div;
}

function sincronizarPagamento(inscricaoId, paymentId) {
    const btn = event.target.closest('.btn-sincronizar-individual');
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin w-4 h-4"></i> Sincronizando...';
    
    const payload = { inscricao_id: inscricaoId };
    if (paymentId) {
        payload.payment_id = paymentId;
    }
    
    fetch(`${API_BASE}/sync_payment_status.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.data.status_mudou) {
                Swal.fire({
                    icon: 'success',
                    title: 'Status Atualizado!',
                    text: `Status mudou de "${data.data.status_anterior}" para "${data.data.status_novo}"`,
                    confirmButtonText: 'OK'
                });
                carregarPagamentos(); // Recarregar lista
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Status Verificado',
                    text: 'O status já está atualizado.',
                    confirmButtonText: 'OK'
                });
            }
        } else {
            throw new Error(data.message || 'Erro ao sincronizar');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Erro ao Sincronizar',
            text: error.message || 'Não foi possível sincronizar o pagamento.',
            confirmButtonText: 'OK'
        });
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

function sincronizarSelecionados() {
    if (selectedIds.size === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Nenhum item selecionado',
            text: 'Selecione pelo menos um pagamento para sincronizar.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    Swal.fire({
        title: 'Sincronizar Pagamentos?',
        text: `Deseja sincronizar ${selectedIds.size} pagamento(s) selecionado(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, sincronizar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            const promises = Array.from(selectedIds).map(id => {
                return fetch(`${API_BASE}/sync_payment_status.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ inscricao_id: id })
                }).then(res => res.json());
            });
            
            Promise.all(promises).then(results => {
                const sucessos = results.filter(r => r.success).length;
                const falhas = results.length - sucessos;
                
                Swal.fire({
                    icon: sucessos > 0 ? 'success' : 'error',
                    title: 'Sincronização Concluída',
                    html: `
                        <p>${sucessos} pagamento(s) sincronizado(s) com sucesso.</p>
                        ${falhas > 0 ? `<p class="text-red-600">${falhas} falha(s).</p>` : ''}
                    `,
                    confirmButtonText: 'OK'
                });
                
                selectedIds.clear();
                atualizarContadorSelecionados();
                document.getElementById('select-all').checked = false;
                carregarPagamentos();
            });
        }
    });
}

function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.checkbox-pagamento');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
        const inscricaoId = parseInt(cb.dataset.inscricaoId);
        if (selectAll.checked) {
            selectedIds.add(inscricaoId);
        } else {
            selectedIds.delete(inscricaoId);
        }
    });
    
    atualizarContadorSelecionados();
}

function atualizarContadorSelecionados() {
    const count = selectedIds.size;
    document.getElementById('selected-count').textContent = `${count} selecionado(s)`;
}

function aplicarFiltros() {
    currentFilters.horas_minimas = parseInt(document.getElementById('filtro-horas').value);
    currentFilters.evento_id = document.getElementById('filtro-evento').value || null;
    currentOffset = 0;
    selectedIds.clear();
    atualizarContadorSelecionados();
    carregarPagamentos();
}

function carregarEventos() {
    // Carregar lista de eventos para filtro
    fetch('../../../api/admin/get_dashboard_data.php')
        .then(res => res.json())
        .then(data => {
            // Implementar se necessário
        })
        .catch(() => {});
}

function atualizarPaginacao(pagination) {
    const paginationDiv = document.getElementById('pagamentos-pagination');
    if (!pagination || pagination.total <= pagination.limit) {
        paginationDiv.innerHTML = '';
        return;
    }
    
    const totalPages = Math.ceil(pagination.total / pagination.limit);
    const currentPage = Math.floor(pagination.offset / pagination.limit) + 1;
    
    paginationDiv.innerHTML = `
        <div class="text-sm text-gray-600">
            Mostrando ${pagination.offset + 1} a ${Math.min(pagination.offset + pagination.limit, pagination.total)} de ${pagination.total}
        </div>
        <div class="flex gap-2">
            <button class="btn-secondary ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}" 
                    ${currentPage === 1 ? 'disabled' : ''} 
                    onclick="currentOffset = ${Math.max(0, pagination.offset - pagination.limit)}; carregarPagamentos();">
                <i class="fas fa-chevron-left w-4 h-4"></i> Anterior
            </button>
            <button class="btn-secondary ${!pagination.has_more ? 'opacity-50 cursor-not-allowed' : ''}" 
                    ${!pagination.has_more ? 'disabled' : ''} 
                    onclick="currentOffset = ${pagination.offset + pagination.limit}; carregarPagamentos();">
                Próximo <i class="fas fa-chevron-right w-4 h-4"></i>
            </button>
        </div>
    `;
}

function formatarData(data) {
    if (!data) return 'N/A';
    const d = new Date(data);
    return d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
}

function mostrarErro(mensagem) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: mensagem
        });
    } else {
        alert(mensagem);
    }
}
