<?php
$pageTitle = 'Relatórios';
?>

<div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
  <div class="flex justify-between items-center mb-4 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Relatórios</h1>
    <div class="flex space-x-2">
      <button id="exportarRelatorioBtn" class="bg-green-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-green-700 transition text-xs sm:text-sm">
        <i class="fas fa-download mr-2"></i>Exportar
      </button>
    </div>
  </div>

  <!-- Filtros -->
  <div class="bg-gray-50 rounded-lg p-3 sm:p-4 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3 lg:gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Relatório</label>
        <select id="tipoRelatorio" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
          <option value="inscricoes">Inscrições</option>
          <option value="receita">Receita</option>
          <option value="publico">Público</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
        <select id="filtroEvento" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
          <option value="">Todos os eventos</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Período</label>
        <select id="filtroPeriodo" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
          <option value="">Todos os períodos</option>
          <option value="hoje">Hoje</option>
          <option value="semana">Última semana</option>
          <option value="mes">Último mês</option>
          <option value="ano">Último ano</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Resumo -->
  <div id="resumoRelatorio" class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-6">
    <!-- Cards de resumo serão carregados via JavaScript -->
  </div>

  <!-- Gráficos -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 lg:gap-6 mb-6">
    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Evolução de Inscrições</h3>
      <canvas id="graficoInscricoes" width="400" height="200"></canvas>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Receita por Modalidade</h3>
      <canvas id="graficoReceita" width="400" height="200"></canvas>
    </div>
  </div>

  <!-- Tabela de Dados -->
  <div class="bg-white border border-gray-200 rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">Dados Detalhados</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs sm:text-sm">
        <thead class="bg-gray-50">
          <tr id="cabecalhoTabela">
            <!-- Cabeçalhos serão carregados via JavaScript -->
          </tr>
        </thead>
        <tbody id="dadosTabela" class="bg-white divide-y divide-gray-200">
          <!-- Dados serão carregados via JavaScript -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Paginação -->
  <div class="flex justify-between items-center mt-4 sm:mt-6">
    <div class="text-sm text-gray-700">
      Mostrando <span id="inicio">0</span> a <span id="fim">0</span> de <span id="total">0</span> registros
    </div>
    <div class="flex space-x-2">
      <button id="anterior" class="px-2 sm:px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-xs sm:text-sm">Anterior</button>
      <button id="proximo" class="px-2 sm:px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-xs sm:text-sm">Próximo</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  let dadosRelatorio = [];
  let resumoRelatorio = {};
  let paginaAtual = 1;
  let itensPorPagina = 10;
  let tipoRelatorioAtual = 'inscricoes';

  document.addEventListener('DOMContentLoaded', function() {
    carregarEventos();
    carregarRelatorio();

    // Event listeners para filtros
    document.getElementById('tipoRelatorio').addEventListener('change', carregarRelatorio);
    document.getElementById('filtroEvento').addEventListener('change', carregarRelatorio);
    document.getElementById('filtroPeriodo').addEventListener('change', carregarRelatorio);

    // Event listeners para paginação
    document.getElementById('anterior').addEventListener('click', () => {
      if (paginaAtual > 1) {
        paginaAtual--;
        renderizarTabela();
      }
    });

    document.getElementById('proximo').addEventListener('click', () => {
      const totalPaginas = Math.ceil(dadosRelatorio.length / itensPorPagina);
      if (paginaAtual < totalPaginas) {
        paginaAtual++;
        renderizarTabela();
      }
    });
  });

  async function carregarRelatorio() {
    tipoRelatorioAtual = document.getElementById('tipoRelatorio').value;
    const eventoId = document.getElementById('filtroEvento').value;
    const periodo = document.getElementById('filtroPeriodo').value;

    try {
      const url = `../../api/organizador/relatorios/${tipoRelatorioAtual}.php`;
      const params = new URLSearchParams();
      if (eventoId) params.append('evento_id', eventoId);
      if (periodo) params.append('periodo', periodo);

      const response = await fetch(`${url}?${params}`);
      const data = await response.json();

      if (data.success) {
        dadosRelatorio = data.data;
        resumoRelatorio = data.resumo || {};
        renderizarResumo();
        renderizarTabela();
        renderizarGraficos();
      } else {
        console.error('Erro ao carregar relatório:', data.error);
      }
    } catch (error) {
      console.error('Erro na requisição:', error);
    }
  }

  function renderizarResumo() {
    const container = document.getElementById('resumoRelatorio');
    container.innerHTML = '';

    const cards = [];

    if (tipoRelatorioAtual === 'inscricoes') {
      cards.push({
        titulo: 'Total de Eventos',
        valor: resumoRelatorio.total_eventos || 0,
        icone: 'fas fa-calendar',
        cor: 'blue'
      }, {
        titulo: 'Total de Inscritos',
        valor: resumoRelatorio.total_inscritos || 0,
        icone: 'fas fa-users',
        cor: 'green'
      }, {
        titulo: 'Receita Total',
        valor: resumoRelatorio.total_receita_formatado || 'R$ 0,00',
        icone: 'fas fa-dollar-sign',
        cor: 'yellow'
      }, {
        titulo: 'Média por Evento',
        valor: resumoRelatorio.media_inscritos_por_evento || 0,
        icone: 'fas fa-chart-line',
        cor: 'purple'
      });
    } else if (tipoRelatorioAtual === 'receita') {
      cards.push({
        titulo: 'Receita Inscrições',
        valor: resumoRelatorio.total_receita_inscricoes_formatado || 'R$ 0,00',
        icone: 'fas fa-ticket-alt',
        cor: 'blue'
      }, {
        titulo: 'Receita Produtos',
        valor: resumoRelatorio.total_receita_produtos_formatado || 'R$ 0,00',
        icone: 'fas fa-shopping-cart',
        cor: 'green'
      }, {
        titulo: 'Receita Total',
        valor: resumoRelatorio.total_receita_geral_formatado || 'R$ 0,00',
        icone: 'fas fa-dollar-sign',
        cor: 'yellow'
      }, {
        titulo: 'Média por Inscrição',
        valor: resumoRelatorio.media_por_inscricao_formatado || 'R$ 0,00',
        icone: 'fas fa-chart-line',
        cor: 'purple'
      });
    }

    cards.forEach(card => {
      const div = document.createElement('div');
      div.className = 'bg-white border border-gray-200 rounded-lg p-6';
      div.innerHTML = `
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div class="w-8 h-8 bg-${card.cor}-100 rounded-full flex items-center justify-center">
            <i class="${card.icone} text-${card.cor}-600"></i>
          </div>
        </div>
        <div class="ml-4">
          <p class="text-sm font-medium text-gray-500">${card.titulo}</p>
          <p class="text-2xl font-semibold text-gray-900">${card.valor}</p>
        </div>
      </div>
    `;
      container.appendChild(div);
    });
  }

  function renderizarTabela() {
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const fim = inicio + itensPorPagina;
    const dadosPaginados = dadosRelatorio.slice(inicio, fim);

    const cabecalho = document.getElementById('cabecalhoTabela');
    const tbody = document.getElementById('dadosTabela');

    // Definir cabeçalhos baseado no tipo de relatório
    let cabecalhos = [];
    if (tipoRelatorioAtual === 'inscricoes') {
      cabecalhos = ['Evento', 'Modalidade', 'Inscritos', 'Confirmados', 'Pendentes', 'Cancelados', 'Receita', 'Taxa Confirmação'];
    } else if (tipoRelatorioAtual === 'receita') {
      cabecalhos = ['Evento', 'Modalidade', 'Inscrições', 'Receita Inscrições', 'Receita Produtos', 'Receita Total'];
    }

    cabecalho.innerHTML = cabecalhos.map(h => `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${h}</th>`).join('');

    tbody.innerHTML = '';

    dadosPaginados.forEach(item => {
      const tr = document.createElement('tr');
      let celulas = [];

      if (tipoRelatorioAtual === 'inscricoes') {
        celulas = [
          `<td class="px-6 py-4 whitespace-nowrap">
          <div class="text-sm text-gray-900">${item.evento_nome}</div>
          <div class="text-sm text-gray-500">${item.data_evento_formatada}</div>
        </td>`,
          `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.modalidade_nome}</td>`,
          `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.total_inscritos}</td>`,
          `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.inscritos_confirmados}</td>`,
          `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.inscritos_pendentes}</td>`,
          `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.inscritos_cancelados}</td>`,
          `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.receita_formatada}</td>`,
          `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.taxa_confirmacao}%</td>`
        ];
      } else if (tipoRelatorioAtual === 'receita') {
        celulas = [
          `<td class="px-6 py-4 whitespace-nowrap">
          <div class="text-sm text-gray-900">${item.evento_nome}</div>
          <div class="text-sm text-gray-500">${item.data_evento_formatada}</div>
        </td>`,
          `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.modalidade_nome}</td>`,
          `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.inscricoes_confirmadas}</td>`,
          `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.receita_inscricoes_formatada}</td>`,
          `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.receita_produtos_formatada}</td>`,
          `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.receita_total_formatada}</td>`
        ];
      }

      tr.innerHTML = celulas.join('');
      tbody.appendChild(tr);
    });

    // Atualizar informações de paginação
    document.getElementById('inicio').textContent = inicio + 1;
    document.getElementById('fim').textContent = Math.min(fim, dadosRelatorio.length);
    document.getElementById('total').textContent = dadosRelatorio.length;
  }

  function renderizarGraficos() {
    // Gráfico de inscrições
    const ctxInscricoes = document.getElementById('graficoInscricoes').getContext('2d');
    new Chart(ctxInscricoes, {
      type: 'line',
      data: {
        labels: dadosRelatorio.map(item => item.evento_nome),
        datasets: [{
          label: 'Inscritos',
          data: dadosRelatorio.map(item => item.total_inscritos),
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.1
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Gráfico de receita
    const ctxReceita = document.getElementById('graficoReceita').getContext('2d');
    new Chart(ctxReceita, {
      type: 'doughnut',
      data: {
        labels: dadosRelatorio.map(item => item.modalidade_nome),
        datasets: [{
          data: dadosRelatorio.map(item => parseFloat(item.receita_total.replace('R$ ', '').replace(',', ''))),
          backgroundColor: [
            'rgb(59, 130, 246)',
            'rgb(16, 185, 129)',
            'rgb(245, 158, 11)',
            'rgb(239, 68, 68)',
            'rgb(139, 92, 246)'
          ]
        }]
      },
      options: {
        responsive: true
      }
    });
  }

  async function carregarEventos() {
    try {
      const response = await fetch('../../api/organizador/eventos/list.php');
      const data = await response.json();

      if (data.success) {
        const selectEvento = document.getElementById('filtroEvento');

        data.data.forEach(evento => {
          const option = document.createElement('option');
          option.value = evento.id;
          option.textContent = evento.nome;
          selectEvento.appendChild(option);
        });
      }
    } catch (error) {
      console.error('Erro ao carregar eventos:', error);
    }
  }

  // Event listener para exportar relatório
  document.getElementById('exportarRelatorioBtn').addEventListener('click', function() {
    const tipo = tipoRelatorioAtual;
    const headers = tipo === 'inscricoes' ? ['Evento', 'Modalidade', 'Inscritos', 'Confirmados', 'Pendentes', 'Cancelados', 'Receita', 'Taxa Confirmação'] : ['Evento', 'Modalidade', 'Inscrições', 'Receita Inscrições', 'Receita Produtos', 'Receita Total'];

    const csvContent = "data:text/csv;charset=utf-8," + headers.join(',') + "\n" +
      dadosRelatorio.map(item => {
        if (tipo === 'inscricoes') {
          return `"${item.evento_nome}","${item.modalidade_nome}","${item.total_inscritos}","${item.inscritos_confirmados}","${item.inscritos_pendentes}","${item.inscritos_cancelados}","${item.receita_formatada}","${item.taxa_confirmacao}%"`;
        } else {
          return `"${item.evento_nome}","${item.modalidade_nome}","${item.inscricoes_confirmadas}","${item.receita_inscricoes_formatada}","${item.receita_produtos_formatada}","${item.receita_total_formatada}"`;
        }
      }).join('\n');

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `relatorio_${tipo}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  });
</script>
