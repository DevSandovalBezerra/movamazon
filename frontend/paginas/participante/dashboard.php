<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
  header("Location: index.php?page=dashboard");
  exit;
}

$userName = $_SESSION['user_name'] ?? 'Usuário';
?>
<link rel="stylesheet" href="../../assets/css/participante.css">
<section class="py-8 px-4 max-w-7xl mx-auto">
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-3">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Bem-vindo, <?php echo htmlspecialchars($userName); ?>!</h1>
      <p class="text-gray-600">Acompanhe suas inscrições e próximos eventos</p>
    </div>
    <button onclick="window.dashboardRefresh && window.dashboardRefresh()" 
            class="flex items-center px-4 py-3 text-base text-gray-600 hover:text-gray-900 transition-colors touch-target">
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
      Atualizar
    </button>
  </div>

  <div id="dashboard-error" class="hidden"></div>

  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border border-gray-200 stat-card">
      <p class="text-sm font-medium text-gray-600">Inscrições Ativas</p>
      <p id="inscricoes-ativas" class="text-2xl font-bold text-gray-900">-</p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border border-gray-200 stat-card">
      <p class="text-sm font-medium text-gray-600">Próximos Eventos</p>
      <p id="proximos-eventos-count" class="text-2xl font-bold text-gray-900">-</p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border border-gray-200 stat-card">
      <p class="text-sm font-medium text-gray-600">Kits Pendentes</p>
      <p id="kits-pendentes" class="text-2xl font-bold text-gray-900">-</p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border border-gray-200 stat-card">
      <p class="text-sm font-medium text-gray-600">Pagamentos OK</p>
      <p id="pagamentos-ok" class="text-2xl font-bold text-gray-900">-</p>
    </div>
    <!-- Card de Cashback -->
    <a href="?page=meu-cashback" class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg shadow-md p-4 sm:p-6 border border-emerald-400 stat-card hover:from-emerald-600 hover:to-green-700 transition-all cursor-pointer group">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-emerald-100">Meu Cashback</p>
          <p id="saldo-cashback" class="text-2xl font-bold text-white">R$ 0,00</p>
        </div>
        <div class="bg-white/20 rounded-full p-2 group-hover:bg-white/30 transition-colors">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
      <p class="text-xs text-emerald-200 mt-2 flex items-center">
        <span>Ver detalhes</span>
        <svg class="w-3 h-3 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
      </p>
    </a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Minhas Inscrições Recentes</h3>
      <div id="inscricoes-recentes" class="space-y-4">
        <div class="text-center py-8">
          <div class="spinner mx-auto mb-4"></div>
          <p class="text-gray-600">Carregando inscrições...</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Próximos Eventos</h3>
      <div id="proximos-eventos" class="space-y-4">
        <div class="text-center py-8">
          <div class="spinner mx-auto mb-4"></div>
          <p class="text-gray-600">Carregando eventos...</p>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border border-gray-200 mt-6 sm:mt-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
      <a href="../../public/index.php" 
         class="flex items-center justify-center p-4 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors touch-target">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M3 6h18M3 12h18M3 18h18" />
        </svg>
        Ver Todos os Eventos
      </a>
      <a href="?page=minhas-inscricoes" 
         class="flex items-center justify-center p-4 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors touch-target">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M12 17l-5-5m0 0l5-5m-5 5h12" />
        </svg>
        Minhas Inscrições
      </a>
      <a href="?page=meus-treinos" 
         class="flex items-center justify-center p-4 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition-colors touch-target">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Meus Treinos
      </a>
    </div>
  </div>
</section>

<script>
  console.log('🚀 ============================================');
  console.log('🚀 Script do dashboard iniciado...');
  console.log('🚀 Timestamp:', new Date().toISOString());
  console.log('🚀 URL atual:', window.location.href);
  console.log('🚀 ============================================');
  
  // Verificar se os elementos DOM existem
  console.log('🔍 Verificando elementos DOM...');
  const inscricoesAtivasEl = document.getElementById('inscricoes-ativas');
  const proximosEventosCountEl = document.getElementById('proximos-eventos-count');
  const kitsPendentesEl = document.getElementById('kits-pendentes');
  const pagamentosOkEl = document.getElementById('pagamentos-ok');
  const inscricoesRecentesEl = document.getElementById('inscricoes-recentes');
  const proximosEventosEl = document.getElementById('proximos-eventos');
  
  console.log('🔍 Elementos encontrados:');
  console.log('  - inscricoes-ativas:', !!inscricoesAtivasEl);
  console.log('  - proximos-eventos-count:', !!proximosEventosCountEl);
  console.log('  - kits-pendentes:', !!kitsPendentesEl);
  console.log('  - pagamentos-ok:', !!pagamentosOkEl);
  console.log('  - inscricoes-recentes:', !!inscricoesRecentesEl);
  console.log('  - proximos-eventos:', !!proximosEventosEl);
  
  if (!inscricoesAtivasEl || !proximosEventosCountEl || !kitsPendentesEl || !pagamentosOkEl || !inscricoesRecentesEl || !proximosEventosEl) {
    console.error('❌ ERRO: Alguns elementos DOM não foram encontrados!');
  }
  
  async function carregarDashboard(forceRefresh = false) {
    try {
      console.log('🔄 Iniciando carregamento do dashboard...');
      
      const loadingElements = document.querySelectorAll('#inscricoes-recentes .spinner, #proximos-eventos .spinner');
      loadingElements.forEach((el) => {
        el.parentElement.remove();
      });
      
      const dashboardUrl = '../../../api/participante/get_dashboard_data.php';
      
      console.log('📡 Buscando dados do dashboard...');
      const response = await fetch(dashboardUrl, {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' }
      });
      
      if (!response.ok) {
        throw new Error(`Erro HTTP: ${response.status}`);
      }
      
      const data = await response.json();
      
      if (!data.success) {
        throw new Error(data.message || 'Erro ao carregar dados');
      }
      
      const { estatisticas, inscricoes, eventos } = data;
      
      console.log('✅ Dados recebidos:', { estatisticas, inscricoes: inscricoes.length, eventos: eventos.length });
      
      const elInscricoesAtivas = document.getElementById('inscricoes-ativas');
      const elProximosEventos = document.getElementById('proximos-eventos-count');
      const elKitsPendentes = document.getElementById('kits-pendentes');
      const elPagamentosOk = document.getElementById('pagamentos-ok');
      
      if (elInscricoesAtivas) elInscricoesAtivas.textContent = estatisticas.inscricoes_ativas || 0;
      if (elProximosEventos) elProximosEventos.textContent = estatisticas.proximos_eventos || 0;
      if (elKitsPendentes) elKitsPendentes.textContent = estatisticas.kits_pendentes || 0;
      if (elPagamentosOk) elPagamentosOk.textContent = estatisticas.pagamentos_ok || 0;
      
      // Atualizar saldo de cashback
      const elSaldoCashback = document.getElementById('saldo-cashback');
      if (elSaldoCashback && estatisticas.saldo_cashback !== undefined) {
        const saldo = parseFloat(estatisticas.saldo_cashback) || 0;
        elSaldoCashback.textContent = 'R$ ' + saldo.toFixed(2).replace('.', ',');
      }
      
      const formatarData = (dateString) => {
        if (!dateString) return '-';
        const date = new Date(dateString + 'T00:00:00');
        return date.toLocaleDateString('pt-BR');
      };
      
      const formatarStatusPagamento = (status) => {
        const statusMap = {
          'pago': 'Confirmado',
          'pendente': 'Pendente',
          'cancelado': 'Cancelado',
          'reembolsado': 'Reembolsado'
        };
        return statusMap[status] || status;
      };
      
      const getEventoImagem = (imagem) => {
        if (typeof window.getEventImageUrl === 'function') {
          if (!imagem) return '../../assets/img/default-event.jpg';
          if (imagem.startsWith('http://') || imagem.startsWith('https://') || imagem.startsWith('/')) return imagem;
          return window.getEventImageUrl(imagem);
        }
        if (imagem) {
          if (imagem.startsWith('http://') || imagem.startsWith('https://')) return imagem;
          if (imagem.startsWith('/')) return imagem;
          return '../../assets/img/eventos/' + imagem;
        }
        return '../../assets/img/default-event.jpg';
      };
      
      const inscricoesContainer = document.getElementById('inscricoes-recentes');
      if (inscricoesContainer) {
        if (inscricoes.length === 0) {
          inscricoesContainer.innerHTML = `
            <div class="text-center py-8">
              <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <p class="text-gray-600">Você ainda não tem inscrições</p>
              <p class="text-sm text-gray-500 mt-1">Explore os eventos disponíveis e faça sua primeira inscrição!</p>
            </div>
          `;
        } else {
          inscricoesContainer.innerHTML = inscricoes.map(inscricao => {
            const imagem = getEventoImagem(inscricao.evento_imagem);
            const statusPagamento = formatarStatusPagamento(inscricao.status_pagamento);
            const statusClass = inscricao.status_pagamento === 'pago' ? 'text-green-600' : 'text-yellow-600';
            
            return `
              <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center flex-1 min-w-0">
                  <img src="${imagem}" alt="${inscricao.evento_nome}" 
                       class="w-12 h-12 object-cover rounded-lg flex-shrink-0" 
                       loading="lazy"
                       onerror="this.src='../../assets/img/default-event.jpg'">
                  <div class="ml-4 flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${inscricao.evento_nome}</p>
                    <p class="text-sm text-gray-600">${inscricao.modalidade_nome || 'N/A'}</p>
                    <p class="text-xs text-gray-500">${formatarData(inscricao.evento_data)}</p>
                  </div>
                </div>
                <div class="text-right ml-4 flex-shrink-0">
                  <p class="text-sm font-medium ${statusClass}">${statusPagamento}</p>
                  <p class="text-xs text-gray-500">${inscricao.numero_inscricao || 'N/A'}</p>
                </div>
              </div>
            `;
          }).join('');
        }
      }
      
      const eventosContainer = document.getElementById('proximos-eventos');
      if (eventosContainer) {
        if (eventos.length === 0) {
          eventosContainer.innerHTML = `
            <div class="text-center py-8">
              <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <p class="text-gray-600">Nenhum evento próximo encontrado</p>
            </div>
          `;
        } else {
          eventosContainer.innerHTML = eventos.map(evento => {
            const imagem = getEventoImagem(evento.imagem);
            const dataFormatada = evento.data_formatada || formatarData(evento.data_evento);
            
            return `
              <div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
                   onclick="window.location.href='../../paginas/public/detalhes-evento.php?id=${evento.id}'">
                <img src="${imagem}" alt="${evento.nome}" 
                     class="w-12 h-12 object-cover rounded-lg flex-shrink-0"
                     loading="lazy"
                     onerror="this.src='../../assets/img/default-event.jpg'">
                <div class="ml-4 flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">${evento.nome}</p>
                  <p class="text-sm text-gray-600">${dataFormatada}</p>
                  <p class="text-xs text-gray-500">${evento.local_formatado || evento.cidade || ''}</p>
                </div>
              </div>
            `;
          }).join('');
        }
      }
      
      console.log('✅ Dashboard carregado com sucesso!');
      
    } catch (error) {
      console.error('❌ Erro ao carregar dashboard:', error);
      const errorContainer = document.getElementById('dashboard-error');
      if (errorContainer) {
        errorContainer.innerHTML = `
          <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-red-800">${error.message}</p>
            </div>
            <button onclick="location.reload()" 
                    class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
              Tentar novamente
            </button>
          </div>
        `;
        errorContainer.classList.remove('hidden');
      }
    }
  }
  
  window.dashboardRefresh = () => {
    console.log('🔄 Refresh manual acionado...');
    carregarDashboard(true);
  };
  
  // Inicializar quando o DOM estiver pronto
  console.log('⏳ Verificando estado do DOM...');
  console.log('⏳ readyState:', document.readyState);
  
  if (document.readyState === 'loading') {
    console.log('⏳ DOM ainda carregando, aguardando DOMContentLoaded...');
    document.addEventListener('DOMContentLoaded', () => {
      console.log('✅ ============================================');
      console.log('✅ DOMContentLoaded disparado!');
      console.log('✅ Iniciando carregamento do dashboard...');
      console.log('✅ ============================================');
      carregarDashboard();
    });
  } else {
    console.log('✅ ============================================');
    console.log('✅ DOM já carregado (readyState:', document.readyState, ')');
    console.log('✅ Iniciando carregamento do dashboard...');
    console.log('✅ ============================================');
    
    // Pequeno delay para garantir que todos os elementos estão prontos
    setTimeout(() => {
      console.log('⏱️ Delay de inicialização completado, iniciando...');
      carregarDashboard();
    }, 100);
  }
  
  // Fallback: tentar novamente após 1 segundo se nada acontecer
  setTimeout(() => {
    const inscricoesEl = document.getElementById('inscricoes-ativas');
    if (inscricoesEl && inscricoesEl.textContent === '-') {
      console.warn('⚠️ FALLBACK: Dashboard ainda não carregou após 1 segundo, tentando novamente...');
      carregarDashboard();
    }
  }, 1000);
</script>
