<?php
$pageTitle = 'Financeiro';
?>

<div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Financeiro</h1>
      <p class="text-sm text-gray-600 mt-1">Visao geral e operacoes de repasses, estornos e chargebacks.</p>
    </div>
    <button id="finRefreshBtn" class="btn-primary text-sm">
      <i class="fas fa-sync-alt mr-2"></i>Atualizar
    </button>
  </div>

  <div id="finAlert" class="hidden mb-4 rounded-lg border px-4 py-3 text-sm"></div>

  <div class="bg-gray-50 rounded-lg p-4 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="sm:col-span-2">
        <label for="finEventoId" class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
        <select id="finEventoId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
          <option value="">Selecione um evento</option>
        </select>
      </div>
      <div class="flex items-end">
        <button id="finAplicarEventoBtn" class="w-full btn-primary text-sm">
          <i class="fas fa-filter mr-2"></i>Aplicar Evento
        </button>
      </div>
      <div class="flex items-end">
        <button id="finLimparEventoBtn" class="w-full btn-secondary text-sm">
          <i class="fas fa-times mr-2"></i>Limpar
        </button>
      </div>
    </div>
  </div>

  <div id="finCards" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 sm:gap-4 mb-6">
    <div class="card p-4">
      <p class="text-xs text-gray-500 uppercase tracking-wide">Receita inscricoes</p>
      <p id="finCardReceita" class="text-xl font-bold text-gray-900 mt-2">R$ 0,00</p>
    </div>
    <div class="card p-4">
      <p class="text-xs text-gray-500 uppercase tracking-wide">Ja repassado</p>
      <p id="finCardRepassado" class="text-xl font-bold text-gray-900 mt-2">R$ 0,00</p>
    </div>
    <div class="card p-4">
      <p class="text-xs text-gray-500 uppercase tracking-wide">A liberar</p>
      <p id="finCardALiberar" class="text-xl font-bold text-gray-900 mt-2">R$ 0,00</p>
    </div>
    <div class="card p-4">
      <p class="text-xs text-gray-500 uppercase tracking-wide">Debitos</p>
      <p id="finCardDebitos" class="text-xl font-bold text-gray-900 mt-2">R$ 0,00</p>
    </div>
    <div class="card p-4">
      <p class="text-xs text-gray-500 uppercase tracking-wide">Saldo disponivel</p>
      <p id="finCardSaldo" class="text-xl font-bold text-gray-900 mt-2">R$ 0,00</p>
    </div>
  </div>

  <div class="mb-4 border-b border-gray-200">
    <nav class="-mb-px flex flex-wrap gap-2" aria-label="Tabs">
      <button type="button" data-fin-tab="repasses" class="fin-tab-btn border-b-2 px-3 py-2 text-sm font-medium">Repasses</button>
      <button type="button" data-fin-tab="estornos" class="fin-tab-btn border-b-2 px-3 py-2 text-sm font-medium">Estornos</button>
      <button type="button" data-fin-tab="chargebacks" class="fin-tab-btn border-b-2 px-3 py-2 text-sm font-medium">Chargebacks</button>
    </nav>
  </div>

  <div class="bg-gray-50 rounded-lg p-4 mb-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
      <div>
        <label for="finStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="finStatus" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
          <option value="">Todos</option>
        </select>
      </div>
      <div>
        <label for="finDtIni" class="block text-sm font-medium text-gray-700 mb-1">Data inicio</label>
        <input type="date" id="finDtIni" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label for="finDtFim" class="block text-sm font-medium text-gray-700 mb-1">Data fim</label>
        <input type="date" id="finDtFim" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
      </div>
      <div class="lg:col-span-2">
        <label for="finBusca" class="block text-sm font-medium text-gray-700 mb-1">Busca</label>
        <input type="text" id="finBusca" placeholder="ID, inscricao, payment_id, motivo..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label for="finPerPage" class="block text-sm font-medium text-gray-700 mb-1">Por pagina</label>
        <select id="finPerPage" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
          <option value="10">10</option>
          <option value="20" selected>20</option>
          <option value="50">50</option>
        </select>
      </div>
    </div>
    <div class="flex flex-wrap gap-2 mt-3">
      <button id="finAplicarFiltrosBtn" class="btn-primary text-sm">
        <i class="fas fa-search mr-2"></i>Aplicar Filtros
      </button>
      <button id="finLimparFiltrosBtn" class="btn-secondary text-sm">
        <i class="fas fa-eraser mr-2"></i>Limpar Filtros
      </button>
    </div>
  </div>

  <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead id="finTableHead" class="bg-gray-50"></thead>
        <tbody id="finTableBody" class="bg-white divide-y divide-gray-100"></tbody>
      </table>
    </div>
  </div>

  <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mt-4">
    <div class="text-sm text-gray-700">
      Mostrando <span id="finInicio">0</span> a <span id="finFim">0</span> de <span id="finTotal">0</span> registros
    </div>
    <div class="flex gap-2">
      <button id="finAnterior" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">Anterior</button>
      <button id="finProximo" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">Proximo</button>
    </div>
  </div>
</div>

<script src="../../js/organizador/financeiro.js"></script>
