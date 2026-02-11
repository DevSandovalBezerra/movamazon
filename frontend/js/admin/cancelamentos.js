/**
 * JavaScript para página de Cancelamentos (Admin)
 */

const API_BASE = '../../../api/admin';

let currentOffset = 0;
let currentLimit = 50;
let currentFilters = {
    status: 'pendente',
    evento_id: null
};

document.addEventListener('DOMContentLoaded', function() {
    carregarCancelamentos();
    carregarEventos();
    
    // Event listeners
    document.getElementById('btn-aplicar-filtros')?.addEventListener('click', aplicarFiltros);
    
    // Fechar modais
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.getAttribute('data-close-modal');
            document.getElementById(modalId)?.classList.add('hidden');
        });
    });
});

function carregarCancelamentos() {
    const loading = document.getElementById('cancelamentos-loading');
    const container = document.getElementById('cancelamentos-container');
    const empty = document.getElementById('cancelamentos-empty');
    
    loading.classList.remove('hidden');
    container.innerHTML = '';
    empty.classList.add('hidden');
    
    const params = new URLSearchParams({
        status: currentFilters.status,
        limite: currentLimit,
        offset: currentOffset
    });
    
    if (currentFilters.evento_id) {
        params.append('evento_id', currentFilters.evento_id);
    }
    
    fetch(`${API_BASE}/get_cancelamentos.php?${params}`)
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            
            if (!data.success) {
                mostrarErro('Erro ao carregar cancelamentos: ' + (data.message || 'Erro desconhecido'));
                return;
            }
            
            // Atualizar estatísticas
            if (data.stats) {
                document.getElementById('stat-pendentes').textContent = data.stats.pendentes || 0;
                document.getElementById('stat-aprovadas').textContent = data.stats.aprovadas || 0;
                document.getElementById('stat-rejeitadas').textContent = data.stats.rejeitadas || 0;
                document.getElementById('stat-total').textContent = data.stats.total || 0;
            }
            
            if (data.data.length === 0) {
                empty.classList.remove('hidden');
                return;
            }
            
            // Renderizar cancelamentos
            data.data.forEach(cancelamento => {
                const card = criarCardCancelamento(cancelamento);
                container.appendChild(card);
            });
            
            // Paginação
            atualizarPaginacao(data.pagination);
        })
        .catch(error => {
            loading.classList.add('hidden');
            mostrarErro('Erro ao carregar cancelamentos: ' + error.message);
        });
}

function criarCardCancelamento(cancelamento) {
    const div = document.createElement('div');
    div.className = 'p-4 hover:bg-gray-50 transition-colors';
    
    const statusClass = {
        'pendente': 'bg-yellow-100 text-yellow-800',
        'aprovada': 'bg-green-100 text-green-800',
        'rejeitada': 'bg-red-100 text-red-800',
        'processada': 'bg-blue-100 text-blue-800'
    }[cancelamento.status] || 'bg-gray-100 text-gray-800';
    
    const diasAteEvento = cancelamento.dias_ate_evento || 0;
    const urgente = diasAteEvento > 0 && diasAteEvento <= 7;
    
    div.innerHTML = `
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-semibold text-gray-900">Solicitação #${cancelamento.id}</span>
                    <span class="px-2 py-1 rounded text-xs font-medium ${statusClass}">
                        ${cancelamento.status.charAt(0).toUpperCase() + cancelamento.status.slice(1)}
                    </span>
                    ${urgente ? '<span class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">Urgente</span>' : ''}
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-2">
                    <div>
                        <span class="font-medium">Evento:</span>
                        <div>${cancelamento.evento_nome}</div>
                        ${diasAteEvento > 0 ? `<div class="text-xs text-gray-500">Em ${diasAteEvento} dias</div>` : ''}
                    </div>
                    <div>
                        <span class="font-medium">Participante:</span>
                        <div>${cancelamento.usuario_nome}</div>
                        <div class="text-xs text-gray-500">${cancelamento.usuario_email}</div>
                    </div>
                    <div>
                        <span class="font-medium">Valor:</span>
                        <div class="text-green-600 font-semibold">R$ ${parseFloat(cancelamento.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
                        ${cancelamento.valor_reembolso ? `<div class="text-xs text-blue-600">Reembolso: R$ ${parseFloat(cancelamento.valor_reembolso).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>` : ''}
                    </div>
                    <div>
                        <span class="font-medium">Data Solicitação:</span>
                        <div>${formatarData(cancelamento.data_solicitacao)}</div>
                    </div>
                </div>
                <div class="mt-2 p-3 bg-gray-50 rounded text-sm">
                    <span class="font-medium">Motivo:</span>
                    <div class="text-gray-700">${cancelamento.motivo || 'Não informado'}</div>
                </div>
                ${cancelamento.motivo_rejeicao ? `
                    <div class="mt-2 p-3 bg-red-50 rounded text-sm">
                        <span class="font-medium text-red-800">Motivo da Rejeição:</span>
                        <div class="text-red-700">${cancelamento.motivo_rejeicao}</div>
                    </div>
                ` : ''}
            </div>
            ${cancelamento.status === 'pendente' ? `
                <div class="flex flex-col gap-2">
                    <button class="btn-primary btn-aprovar" data-solicitacao-id="${cancelamento.id}">
                        <i class="fas fa-check w-4 h-4"></i>
                        Aprovar
                    </button>
                    <button class="btn-secondary bg-red-600 hover:bg-red-700 btn-rejeitar" data-solicitacao-id="${cancelamento.id}">
                        <i class="fas fa-times w-4 h-4"></i>
                        Rejeitar
                    </button>
                </div>
            ` : ''}
        </div>
    `;
    
    // Event listeners para botões
    if (cancelamento.status === 'pendente') {
        div.querySelector('.btn-aprovar')?.addEventListener('click', () => processarCancelamento(cancelamento.id, 'aprovar'));
        div.querySelector('.btn-rejeitar')?.addEventListener('click', () => processarCancelamento(cancelamento.id, 'rejeitar'));
    }
    
    return div;
}

function processarCancelamento(solicitacaoId, acao) {
    if (acao === 'rejeitar') {
        Swal.fire({
            title: 'Rejeitar Cancelamento?',
            input: 'textarea',
            inputLabel: 'Motivo da Rejeição',
            inputPlaceholder: 'Informe o motivo da rejeição...',
            inputAttributes: {
                'aria-label': 'Motivo da rejeição'
            },
            showCancelButton: true,
            confirmButtonText: 'Rejeitar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'O motivo da rejeição é obrigatório';
                }
            }
        }).then(result => {
            if (result.isConfirmed) {
                enviarProcessamento(solicitacaoId, 'rejeitar', result.value);
            }
        });
    } else {
        Swal.fire({
            title: 'Aprovar Cancelamento?',
            html: `
                <p>Deseja aprovar esta solicitação de cancelamento?</p>
                <p class="text-sm text-gray-600 mt-2">O reembolso será processado automaticamente se o pagamento foi aprovado.</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, aprovar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                enviarProcessamento(solicitacaoId, 'aprovar');
            }
        });
    }
}

function enviarProcessamento(solicitacaoId, acao, motivoRejeicao = '') {
    const payload = {
        solicitacao_id: solicitacaoId,
        acao: acao
    };
    
    if (acao === 'rejeitar') {
        payload.motivo_rejeicao = motivoRejeicao;
    }
    
    Swal.fire({
        title: 'Processando...',
        text: 'Aguarde enquanto processamos a solicitação.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`${API_BASE}/processar_cancelamento.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: acao === 'aprovar' ? 'Cancelamento Aprovado!' : 'Cancelamento Rejeitado!',
                text: data.message,
                confirmButtonText: 'OK'
            }).then(() => {
                carregarCancelamentos();
            });
        } else {
            throw new Error(data.message || 'Erro ao processar cancelamento');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: error.message || 'Não foi possível processar o cancelamento.',
            confirmButtonText: 'OK'
        });
    });
}

function aplicarFiltros() {
    currentFilters.status = document.getElementById('filtro-status').value;
    currentFilters.evento_id = document.getElementById('filtro-evento').value || null;
    currentOffset = 0;
    carregarCancelamentos();
}

function carregarEventos() {
    // Implementar se necessário
}

function atualizarPaginacao(pagination) {
    const paginationDiv = document.getElementById('cancelamentos-pagination');
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
                    onclick="currentOffset = ${Math.max(0, pagination.offset - pagination.limit)}; carregarCancelamentos();">
                <i class="fas fa-chevron-left w-4 h-4"></i> Anterior
            </button>
            <button class="btn-secondary ${!pagination.has_more ? 'opacity-50 cursor-not-allowed' : ''}" 
                    ${!pagination.has_more ? 'disabled' : ''} 
                    onclick="currentOffset = ${pagination.offset + pagination.limit}; carregarCancelamentos();">
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
