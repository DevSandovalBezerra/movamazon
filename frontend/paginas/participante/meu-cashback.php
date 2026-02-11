<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
  header("Location: index.php?page=meu-cashback");
  exit;
}
?>
<link rel="stylesheet" href="../../assets/css/participante.css">
<section class="py-8 px-4 max-w-7xl mx-auto">
  <!-- Header -->
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Meu Cashback</h1>
      <p class="text-gray-600">Acompanhe seus créditos e histórico de cashback</p>
    </div>
    <button onclick="carregarCashback()" 
            class="flex items-center px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
      Atualizar
    </button>
  </div>

  <!-- Cards de Resumo -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Saldo Disponível -->
    <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
      <div class="flex items-center justify-between mb-4">
        <div class="bg-white/20 rounded-full p-3">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <span class="text-emerald-200 text-sm font-medium">Disponível</span>
      </div>
      <p class="text-sm text-emerald-100 mb-1">Saldo para usar</p>
      <p id="saldo-disponivel" class="text-3xl font-bold">R$ 0,00</p>
    </div>

    <!-- Total Acumulado -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <div class="bg-blue-100 rounded-full p-3">
          <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </div>
        <span class="text-blue-600 text-sm font-medium">Histórico</span>
      </div>
      <p class="text-sm text-gray-600 mb-1">Total acumulado</p>
      <p id="total-acumulado" class="text-3xl font-bold text-gray-900">R$ 0,00</p>
    </div>

    <!-- Total Utilizado -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <div class="bg-purple-100 rounded-full p-3">
          <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <span class="text-purple-600 text-sm font-medium">Utilizado</span>
      </div>
      <p class="text-sm text-gray-600 mb-1">Já utilizado</p>
      <p id="total-utilizado" class="text-3xl font-bold text-gray-900">R$ 0,00</p>
    </div>
  </div>

  <!-- Info Box -->
  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
    <div class="flex items-start">
      <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <div>
        <h4 class="text-blue-800 font-medium mb-1">Como funciona o Cashback?</h4>
        <p class="text-blue-700 text-sm">
          A cada inscrição paga, você recebe <strong>1% do valor da modalidade</strong> como cashback. 
          Este valor fica disponível para uso em futuras inscrições.
        </p>
      </div>
    </div>
  </div>

  <!-- Histórico -->
  <div class="bg-white rounded-xl shadow-md border border-gray-200">
    <div class="p-6 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-gray-900">Histórico de Cashback</h2>
    </div>
    
    <div id="historico-container" class="divide-y divide-gray-100">
      <div class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-emerald-500 border-t-transparent mb-4"></div>
        <p class="text-gray-600">Carregando histórico...</p>
      </div>
    </div>
  </div>
</section>

<script>
async function carregarCashback() {
  try {
    // Detectar caminho correto baseado na URL atual
    let apiUrl = '../../../api/participante/cashback/saldo.php';
    
    // Se estiver em produção ou caminho absoluto necessário
    const currentPath = window.location.pathname;
    if (currentPath.includes('/frontend/paginas/participante/')) {
      // Caminho relativo funciona
      apiUrl = '../../../api/participante/cashback/saldo.php';
    } else {
      // Tentar caminho absoluto
      const basePath = currentPath.substring(0, currentPath.indexOf('/frontend/') || 0);
      apiUrl = basePath ? `${basePath}/api/participante/cashback/saldo.php` : '/api/participante/cashback/saldo.php';
    }
    
    console.log('🔍 Tentando acessar:', apiUrl);
    
    const response = await fetch(apiUrl, {
      method: 'GET',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' }
    });
    
    if (!response.ok) {
      console.error('❌ Erro HTTP:', response.status, response.statusText);
      console.error('📍 URL tentada:', apiUrl);
      throw new Error(`Erro HTTP: ${response.status}`);
    }
    
    const data = await response.json();
    
    if (!data.success) {
      throw new Error(data.message || 'Erro ao carregar dados');
    }
    
    // Atualizar cards de resumo
    document.getElementById('saldo-disponivel').textContent = data.resumo.saldo_disponivel_formatado;
    document.getElementById('total-acumulado').textContent = data.resumo.total_acumulado_formatado;
    document.getElementById('total-utilizado').textContent = data.resumo.total_utilizado_formatado;
    
    // Renderizar histórico
    renderizarHistorico(data.historico);
    
  } catch (error) {
    console.error('Erro ao carregar cashback:', error);
    document.getElementById('historico-container').innerHTML = `
      <div class="text-center py-12">
        <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <p class="text-gray-600">Erro ao carregar dados de cashback</p>
        <button onclick="carregarCashback()" class="mt-4 text-emerald-600 hover:text-emerald-700 font-medium">
          Tentar novamente
        </button>
      </div>
    `;
  }
}

function renderizarHistorico(historico) {
  const container = document.getElementById('historico-container');
  
  if (!historico || historico.length === 0) {
    container.innerHTML = `
      <div class="text-center py-12">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-gray-600 font-medium mb-2">Nenhum cashback ainda</h3>
        <p class="text-gray-500 text-sm">Faça inscrições em eventos para acumular cashback!</p>
        <a href="../../public/index.php" class="inline-block mt-4 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
          Ver Eventos
        </a>
      </div>
    `;
    return;
  }
  
  let html = '';
  
  historico.forEach(item => {
    const statusClasses = {
      'disponivel': 'bg-green-100 text-green-800',
      'utilizado': 'bg-purple-100 text-purple-800',
      'expirado': 'bg-gray-100 text-gray-800',
      'pendente': 'bg-yellow-100 text-yellow-800'
    };
    
    const statusIcons = {
      'disponivel': `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>`,
      'utilizado': `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
      'expirado': `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
      'pendente': `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
    };
    
    html += `
      <div class="p-4 sm:p-6 hover:bg-gray-50 transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-start gap-4">
            <div class="bg-emerald-100 rounded-full p-2 flex-shrink-0">
              <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h4 class="font-medium text-gray-900">${item.evento_nome || 'Evento'}</h4>
              <p class="text-sm text-gray-500 mt-1">
                Inscrição: ${item.valor_inscricao_formatado} • ${item.percentual}% de cashback
              </p>
              <p class="text-xs text-gray-400 mt-1">
                ${item.data_credito_formatada}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-4 sm:text-right">
            <div>
              <p class="text-lg font-bold text-emerald-600">+${item.valor_cashback_formatado}</p>
              <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full ${statusClasses[item.status] || 'bg-gray-100 text-gray-800'}">
                ${statusIcons[item.status] || ''}
                ${item.status_texto}
              </span>
            </div>
          </div>
        </div>
      </div>
    `;
  });
  
  container.innerHTML = html;
}

// Carregar ao abrir a página
document.addEventListener('DOMContentLoaded', carregarCashback);
</script>

