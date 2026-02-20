<?php
/**
 * Gestao de Atletas da Assessoria
 * 3 abas: Meus Atletas | Enviar Convite | Convites Pendentes
 */
$assessoria_id = $_SESSION['assessoria_id'] ?? null;
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Atletas</h1>
    <p class="text-gray-500 mt-1">Gerencie os atletas vinculados a sua assessoria</p>
</div>

<!-- Abas -->
<div class="flex border-b mb-6">
    <button onclick="showAtletasTab('meus')" id="tab-meus" class="px-4 py-2.5 text-sm font-medium border-b-2 border-purple-600 text-purple-700 transition-all">
        Meus Atletas
    </button>
    <button onclick="showAtletasTab('convidar')" id="tab-convidar" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">
        Enviar Convite
    </button>
    <button onclick="showAtletasTab('convites')" id="tab-convites" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all">
        Convites <span id="convites-badge" class="hidden ml-1 px-1.5 py-0.5 bg-purple-100 text-purple-700 text-xs rounded-full"></span>
    </button>
</div>

<!-- ===== ABA 1: MEUS ATLETAS ===== -->
<div id="panel-meus" class="tab-panel">
    <div id="atletas-loading" class="text-center py-8 text-gray-500">Carregando...</div>
    <div id="atletas-empty" class="hidden text-center py-8">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <p class="text-gray-500">Nenhum atleta vinculado ainda.</p>
        <p class="text-sm text-gray-400 mt-1">Envie convites para adicionar atletas.</p>
    </div>
    <div id="atletas-list" class="hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Atleta</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Assessor</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Desde</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Acoes</th>
                        </tr>
                    </thead>
                    <tbody id="atletas-tbody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===== ABA 2: ENVIAR CONVITE ===== -->
<div id="panel-convidar" class="tab-panel hidden">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-2xl">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Buscar Atleta na Plataforma</h3>
        <div class="space-y-4">
            <div class="relative">
                <input type="text" id="busca-atleta" placeholder="Digite nome, email ou documento..."
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500 pr-10">
                <svg class="w-5 h-5 text-gray-400 absolute right-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <p class="text-xs text-gray-400">Minimo 2 caracteres para buscar</p>

            <div id="busca-resultados" class="space-y-2"></div>
            <div id="busca-vazio" class="hidden text-center py-4 text-gray-400 text-sm">Nenhum atleta encontrado</div>
        </div>
    </div>

    <!-- Modal de mensagem do convite -->
    <div id="modal-convite" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Enviar Convite</h3>
            <p class="text-sm text-gray-500 mb-4">Para: <span id="convite-atleta-nome" class="font-medium text-gray-700"></span></p>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem (opcional)</label>
                <textarea id="convite-mensagem" rows="3" placeholder="Ex: Gostaria de convidar voce para nossa assessoria..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>
            <input type="hidden" id="convite-atleta-id">
            <div class="flex gap-3">
                <button onclick="fecharModalConvite()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button onclick="confirmarConvite()" id="btn-enviar-convite" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold">Enviar</button>
            </div>
            <div id="convite-feedback" class="text-center text-sm mt-3"></div>
        </div>
    </div>
</div>

<!-- ===== ABA 3: CONVITES ===== -->
<div id="panel-convites" class="tab-panel hidden">
    <div id="convites-loading" class="text-center py-8 text-gray-500">Carregando...</div>
    <div id="convites-empty" class="hidden text-center py-8">
        <p class="text-gray-500">Nenhum convite enviado.</p>
    </div>
    <div id="convites-list" class="hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Atleta</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Enviado por</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Data</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Acoes</th>
                        </tr>
                    </thead>
                    <tbody id="convites-tbody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="../../../js/assessoria/atletas.js"></script>
