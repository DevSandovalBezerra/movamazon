<?php
$activePage = 'solicitacoes';
?>

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Solicitações de Eventos</h1>
            <p class="text-gray-600">Analise os pedidos enviados pela landing e gere os responsáveis.</p>
        </div>
        <div class="flex gap-3">
            <select id="filtro-status" class="admin-input w-48">
                <option value="">Todos os status</option>
                <option value="novo">Novo</option>
                <option value="em_analise">Em análise</option>
                <option value="aprovado">Aprovado</option>
                <option value="recusado">Recusado</option>
            </select>
            <button id="btn-recarregar-solicitacoes" class="btn-secondary flex items-center gap-2">
                <i class="fas fa-sync"></i> Atualizar
            </button>
        </div>
    </div>

    <div class="admin-card">
        <div id="solicitacoes-loading" class="admin-loading">
            <div class="admin-spinner"></div>
            <span>Carregando solicitações...</span>
        </div>
        <div id="solicitacoes-empty" class="hidden py-10 text-center text-gray-500">
            Nenhuma solicitação encontrada.
        </div>
        <div id="solicitacoes-lista" class="space-y-4"></div>
    </div>
</div>

<!-- Modal Detalhes -->
<div id="modal-solicitacao" class="admin-modal hidden">
    <div class="admin-modal-overlay" data-close-modal="modal-solicitacao"></div>
    <div class="admin-modal-content max-w-3xl">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Detalhes da solicitação</h3>
            <button class="admin-modal-close" data-close-modal="modal-solicitacao">&times;</button>
        </div>
        <div class="admin-modal-body max-h-[70vh] overflow-y-auto">
            <div id="solicitacao-detalhes" class="space-y-4 text-sm"></div>
        </div>
        <div class="admin-modal-footer flex flex-wrap gap-3 justify-between">
            <div class="space-x-2">
                <button class="btn-secondary" data-close-modal="modal-solicitacao">Fechar</button>
            </div>
            <div class="space-x-2">
                <button id="btn-marcar-analise" class="btn-secondary">Marcar como em análise</button>
                <button id="btn-aprovar-solicitacao" class="btn-primary flex items-center gap-2">
                    <i class="fas fa-user-check"></i> Aprovar & criar responsável
                </button>
                <button id="btn-recusar-solicitacao" class="btn-danger">Recusar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação -->
<div id="modal-confirmacao-solicitacao" class="admin-modal hidden">
    <div class="admin-modal-overlay" data-close-modal="modal-confirmacao-solicitacao"></div>
    <div class="admin-modal-content max-w-md">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="modal-confirmacao-titulo">Confirmar ação</h3>
            <button class="admin-modal-close" data-close-modal="modal-confirmacao-solicitacao">&times;</button>
        </div>
        <div class="admin-modal-body">
            <p id="modal-confirmacao-texto" class="text-gray-600"></p>
        </div>
        <div class="admin-modal-footer">
            <button class="btn-secondary" data-close-modal="modal-confirmacao-solicitacao">Cancelar</button>
            <button id="btn-confirmar-acao" class="btn-primary">Confirmar</button>
        </div>
    </div>
</div>

<script src="../../js/admin/solicitacoes.js"></script>

