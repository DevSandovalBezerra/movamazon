<?php
$pageTitle = 'Participantes';
?>

<div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
  <div class="flex justify-between items-center mb-4 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Participantes</h1>
    <div class="flex space-x-2">
      <button id="exportarBtn" class="bg-green-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-green-700 transition text-xs sm:text-sm">
        <i class="fas fa-download mr-2"></i>Exportar
      </button>
      <button id="enviarComunicadoBtn" class="bg-blue-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-blue-700 transition text-xs sm:text-sm">
        <i class="fas fa-envelope mr-2"></i>Enviar Comunicado
      </button>
    </div>
  </div>

  <!-- Filtros -->
  <div class="bg-gray-50 rounded-lg p-3 sm:p-4 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
        <select id="filtroEvento" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
          <option value="">Todos os eventos</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="filtroStatus" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
          <option value="">Todos os status</option>
          <option value="confirmada">Confirmada</option>
          <option value="pendente">Pendente</option>
          <option value="cancelada">Cancelada</option>
        </select>
      </div>
      <!-- <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Modalidade</label>
        <select id="filtroModalidade" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">Todas as modalidades</option>
        </select>
      </div> -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
        <input type="text" id="busca" placeholder="Nome ou email..." class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
      </div>
    </div>
  </div>

  <!-- Tabela -->
  <div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200 text-xs sm:text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Participante</th>
          <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Nº Inscrição</th>
          <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
          <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
          <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Status Pagamento</th>
          <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Data Inscrição</th>
          <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
        </tr>
      </thead>
      <tbody id="participantesTable" class="bg-white divide-y divide-gray-200">
        <!-- Dados carregados via JavaScript -->
      </tbody>
    </table>
  </div>

  <!-- Paginação -->
  <div class="flex justify-between items-center mt-4 sm:mt-6">
    <div class="text-sm text-gray-700">
      Mostrando <span id="inicio">0</span> a <span id="fim">0</span> de <span id="total">0</span> participantes
    </div>
    <div class="flex space-x-2">
      <button id="anterior" class="px-2 sm:px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-xs sm:text-sm">Anterior</button>
      <button id="proximo" class="px-2 sm:px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-xs sm:text-sm">Próximo</button>
    </div>
  </div>
</div>

<!-- Modal de Detalhes -->
<div id="modalDetalhes" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Detalhes do Participante</h3>
          <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div id="modalContent">
          <!-- Conteúdo carregado via JavaScript -->
        </div>
      </div>
    </div>
  </div>
</div>

<script>

</script>
<script src="../../js/participantes.js"></script>
