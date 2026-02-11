<?php
$pageTitle = 'Retirada de Kits';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
  <!-- Navegação Sequencial -->
  <div class="mb-4 sm:mb-6 flex gap-2 sm:gap-4">
    <a href="?page=kits-evento" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
      ← Kits do Evento
    </a>
    <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
      9- Retirada de Kits
    </span>
    <a href="?page=camisas" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
      Camisas →
    </a>
  </div>

  <div class="flex justify-between items-center mb-4 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Retirada de Kits</h1>
    <button id="btnNovoLocal" class="bg-green-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-green-700 transition text-xs sm:text-sm">
      <i class="fas fa-plus mr-2"></i>Novo Local
    </button>
  </div>

  <!-- Filtros -->
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3 lg:gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
        <select id="filtroEvento" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
          <option value="">Selecione um evento</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="filtroStatus" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
          <option value="">Todos os status</option>
          <option value="1">Ativo</option>
          <option value="0">Inativo</option>
        </select>
      </div>
      <div class="flex items-end">
        <button onclick="aplicarFiltros()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 rounded-lg flex items-center justify-center text-xs sm:text-sm">
          <i class="fas fa-search mr-2"></i>
          Aplicar Filtros
        </button>
      </div>
    </div>
  </div>

  <!-- Estado Inicial -->
  <div id="estado-inicial" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-12 text-center">
    <div class="text-gray-400 mb-4">
      <i class="fas fa-calendar-alt text-4xl sm:text-6xl"></i>
    </div>
    <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2">Selecione um Evento</h3>
    <p class="text-gray-500 text-sm sm:text-base">Escolha um evento para gerenciar os locais de retirada de kits.</p>
  </div>

  <!-- Estado Filtrado - Lista de Locais -->
  <div id="estado-filtrado" class="hidden">
    <div id="locais-container" class="space-y-4">
      <!-- Cards de locais serão inseridos aqui via JavaScript -->
    </div>
  </div>

  <!-- Estado Vazio -->
  <div id="estado-vazio" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-12 text-center">
    <div class="text-gray-400 mb-4">
      <i class="fas fa-map-marker-alt text-4xl sm:text-6xl"></i>
    </div>
    <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2">Nenhum Local Cadastrado</h3>
    <p class="text-gray-500 mb-6 text-sm sm:text-base">Não há locais de retirada cadastrados para este evento.</p>
    <button onclick="abrirModalLocal()" class="bg-green-600 hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg flex items-center mx-auto text-xs sm:text-sm">
      <i class="fas fa-plus mr-2"></i>
      Cadastrar Primeiro Local
    </button>
  </div>
</div>

<!-- Modal de Criação/Edição -->
<div id="modalLocal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 id="modalTitulo" class="text-2xl font-bold text-gray-900">Novo Local de Retirada</h3>
          <button onclick="fecharModalLocal()" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <form id="formLocal" class="space-y-6">
          <input type="hidden" id="localId" name="local_id">
          <input type="hidden" id="modalEventoId" name="evento_id">

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Informações Básicas -->
            <div class="space-y-4">
              <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">Informações Básicas</h4>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Local de Retirada *</label>
                <input type="text" id="modalLocalRetirada" name="local" placeholder="Ex: Shopping Center, Rua das Flores, 123" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data de Início *</label>
                <input type="datetime-local" id="modalDataInicio" name="data_inicio" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data de Fim *</label>
                <input type="datetime-local" id="modalDataFim" name="data_fim" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
              </div>

              <div class="flex items-center">
                <input type="checkbox" id="modalAtivoRetirada" name="ativo" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                <label for="modalAtivoRetirada" class="ml-2 block text-sm text-gray-900">Retirada ativa</label>
              </div>
            </div>

            <!-- Documentos e Instruções -->
            <div class="space-y-4">
              <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">Documentos e Instruções</h4>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Documentos Necessários</label>
                <textarea id="modalDocumentosNecessarios" name="documentos_necessarios" rows="4" placeholder="Ex: RG ou CPF, comprovante de inscrição, etc." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Instruções de Retirada</label>
                <textarea id="modalInstrucoesRetirada" name="instrucoes" rows="4" placeholder="Ex: Apresentar documento com foto, chegar com 15 min de antecedência, etc." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
              </div>
            </div>
          </div>

          <!-- Botões -->
          <div class="flex justify-end space-x-3 pt-6 border-t">
            <button type="button" onclick="fecharModalLocal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
              Cancelar
            </button>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
              <i class="fas fa-save mr-2"></i>Salvar Local
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="../../js/retirada-kits.js"></script>
