<?php
$pageTitle = 'Estoque';
?>

<div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
  <div class="flex justify-between items-center mb-4 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Controle de Estoque</h1>
    <div class="flex space-x-2">
      <button id="atualizarEstoqueBtn" class="bg-green-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-green-700 transition text-xs sm:text-sm">
        <i class="fas fa-sync mr-2"></i>Atualizar Estoque
      </button>
      <button id="exportarEstoqueBtn" class="bg-blue-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-blue-700 transition text-xs sm:text-sm">
        <i class="fas fa-download mr-2"></i>Exportar
      </button>
    </div>
  </div>

  <!-- Filtros -->
  <div class="bg-gray-50 rounded-lg p-3 sm:p-4 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
        <select id="filtroTipo" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
          <option value="">Todos os tipos</option>
          <option value="camisetas">Camisetas</option>
          <option value="produtos">Produtos Extras</option>
        </select>
      </div>
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
          <option value="disponivel">Disponível</option>
          <option value="estoque_baixo">Estoque Baixo</option>
          <option value="esgotado">Esgotado</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
        <input type="text" id="busca" placeholder="Nome do item..." class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="border-b border-gray-200 mb-4 sm:mb-6">
    <nav class="-mb-px flex space-x-8">
      <button id="tabCamisetas" class="border-b-2 border-blue-500 text-blue-600 py-2 px-1 text-xs sm:text-sm font-medium">
        Camisetas
      </button>
      <button id="tabProdutos" class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-2 px-1 text-xs sm:text-sm font-medium">
        Produtos Extras
      </button>
    </nav>
  </div>

  <!-- Conteúdo das Tabs -->
  <div id="conteudoCamisetas" class="tab-content">
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200 text-xs sm:text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modalidade</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamanho</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade Inicial</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Retiradas</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque Atual</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody id="camisetasTable" class="bg-white divide-y divide-gray-200">
          <!-- Dados carregados via JavaScript -->
        </tbody>
      </table>
    </div>
  </div>

  <div id="conteudoProdutos" class="tab-content hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200 text-xs sm:text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modalidade</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade Inicial</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendidas</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque Atual</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receita</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody id="produtosTable" class="bg-white divide-y divide-gray-200">
          <!-- Dados carregados via JavaScript -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Paginação -->
  <div class="flex justify-between items-center mt-4 sm:mt-6">
    <div class="text-sm text-gray-700">
      Mostrando <span id="inicio">0</span> a <span id="fim">0</span> de <span id="total">0</span> itens
    </div>
    <div class="flex space-x-2">
      <button id="anterior" class="px-2 sm:px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-xs sm:text-sm">Anterior</button>
      <button id="proximo" class="px-2 sm:px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-xs sm:text-sm">Próximo</button>
    </div>
  </div>
</div>

<!-- Modal Atualizar Estoque -->
<div id="modalEstoque" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg max-w-md w-full">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Atualizar Estoque</h3>
          <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <form id="formEstoque">
          <input type="hidden" id="itemId">
          <input type="hidden" id="itemTipo">

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade Atual</label>
              <input type="number" id="quantidadeAtual" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Observação</label>
              <textarea id="observacao" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
            </div>
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="fecharModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
              Cancelar
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              Atualizar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  let estoqueCamisetas = [];
  let estoqueProdutos = [];
  let paginaAtual = 1;
  let itensPorPagina = 10;
  let tabAtual = 'camisetas';

  document.addEventListener('DOMContentLoaded', function() {
    carregarEstoque();
    carregarEventos();

    // Event listeners para filtros
    document.getElementById('filtroTipo').addEventListener('change', filtrarEstoque);
    document.getElementById('filtroEvento').addEventListener('change', filtrarEstoque);
    document.getElementById('filtroStatus').addEventListener('change', filtrarEstoque);
    document.getElementById('busca').addEventListener('input', filtrarEstoque);

    // Event listeners para tabs
    document.getElementById('tabCamisetas').addEventListener('click', () => {
      tabAtual = 'camisetas';
      atualizarTabs();
      renderizarTabela();
    });

    document.getElementById('tabProdutos').addEventListener('click', () => {
      tabAtual = 'produtos';
      atualizarTabs();
      renderizarTabela();
    });

    // Event listeners para paginação
    document.getElementById('anterior').addEventListener('click', () => {
      if (paginaAtual > 1) {
        paginaAtual--;
        renderizarTabela();
      }
    });

    document.getElementById('proximo').addEventListener('click', () => {
      const dados = tabAtual === 'camisetas' ? estoqueCamisetas : estoqueProdutos;
      const totalPaginas = Math.ceil(dados.length / itensPorPagina);
      if (paginaAtual < totalPaginas) {
        paginaAtual++;
        renderizarTabela();
      }
    });

    // Event listener para formulário
    document.getElementById('formEstoque').addEventListener('submit', atualizarEstoque);
  });

  async function carregarEstoque() {
    try {
      // Carregar estoque de camisetas
      const responseCamisetas = await fetch('../../api/organizador/estoque/camisetas.php');
      const dataCamisetas = await responseCamisetas.json();

      if (dataCamisetas.success) {
        estoqueCamisetas = dataCamisetas.data;
      }

      // Carregar estoque de produtos
      const responseProdutos = await fetch('../../api/organizador/estoque/produtos.php');
      const dataProdutos = await responseProdutos.json();

      if (dataProdutos.success) {
        estoqueProdutos = dataProdutos.data;
      }

      renderizarTabela();
    } catch (error) {
      console.error('Erro na requisição:', error);
    }
  }

  function renderizarTabela() {
    const dados = tabAtual === 'camisetas' ? estoqueCamisetas : estoqueProdutos;
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const fim = inicio + itensPorPagina;
    const dadosPaginados = dados.slice(inicio, fim);

    if (tabAtual === 'camisetas') {
      renderizarTabelaCamisetas(dadosPaginados);
    } else {
      renderizarTabelaProdutos(dadosPaginados);
    }

    // Atualizar informações de paginação
    document.getElementById('inicio').textContent = inicio + 1;
    document.getElementById('fim').textContent = Math.min(fim, dados.length);
    document.getElementById('total').textContent = dados.length;
  }

  function renderizarTabelaCamisetas(camisetas) {
    const tbody = document.getElementById('camisetasTable');
    tbody.innerHTML = '';

    camisetas.forEach(item => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">${item.evento_nome}</div>
        <div class="text-sm text-gray-500">${item.data_evento_formatada}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.modalidade_nome}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.tamanho}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.quantidade_inicial}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.quantidade_retirada}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.estoque_atual}</td>
      <td class="px-6 py-4 whitespace-nowrap">
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-${item.status_class}-100 text-${item.status_class}-800">
          ${item.status}
        </span>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <button onclick="abrirModalEstoque('${item.id}', 'camisetas', ${item.estoque_atual})" class="text-blue-600 hover:text-blue-900">
          <i class="fas fa-edit"></i>
        </button>
      </td>
    `;
      tbody.appendChild(tr);
    });
  }

  function renderizarTabelaProdutos(produtos) {
    const tbody = document.getElementById('produtosTable');
    tbody.innerHTML = '';

    produtos.forEach(item => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">${item.evento_nome}</div>
        <div class="text-sm text-gray-500">${item.data_evento_formatada}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.modalidade_nome}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.produto_nome}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.preco_formatado}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.quantidade_inicial}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.quantidade_vendida}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.estoque_atual}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.receita_formatada}</td>
      <td class="px-6 py-4 whitespace-nowrap">
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-${item.status_class}-100 text-${item.status_class}-800">
          ${item.status}
        </span>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <button onclick="abrirModalEstoque('${item.id}', 'produtos', ${item.estoque_atual})" class="text-blue-600 hover:text-blue-900">
          <i class="fas fa-edit"></i>
        </button>
      </td>
    `;
      tbody.appendChild(tr);
    });
  }

  function atualizarTabs() {
    const tabCamisetas = document.getElementById('tabCamisetas');
    const tabProdutos = document.getElementById('tabProdutos');
    const conteudoCamisetas = document.getElementById('conteudoCamisetas');
    const conteudoProdutos = document.getElementById('conteudoProdutos');

    if (tabAtual === 'camisetas') {
      tabCamisetas.classList.add('border-blue-500', 'text-blue-600');
      tabCamisetas.classList.remove('border-transparent', 'text-gray-500');
      tabProdutos.classList.add('border-transparent', 'text-gray-500');
      tabProdutos.classList.remove('border-blue-500', 'text-blue-600');
      conteudoCamisetas.classList.remove('hidden');
      conteudoProdutos.classList.add('hidden');
    } else {
      tabProdutos.classList.add('border-blue-500', 'text-blue-600');
      tabProdutos.classList.remove('border-transparent', 'text-gray-500');
      tabCamisetas.classList.add('border-transparent', 'text-gray-500');
      tabCamisetas.classList.remove('border-blue-500', 'text-blue-600');
      conteudoProdutos.classList.remove('hidden');
      conteudoCamisetas.classList.add('hidden');
    }
  }

  function filtrarEstoque() {
    const filtroTipo = document.getElementById('filtroTipo').value;
    const filtroEvento = document.getElementById('filtroEvento').value;
    const filtroStatus = document.getElementById('filtroStatus').value;
    const busca = document.getElementById('busca').value.toLowerCase();

    // Recarregar dados originais
    carregarEstoque().then(() => {
      // Aplicar filtros
      if (filtroTipo === 'camisetas' || filtroTipo === '') {
        estoqueCamisetas = estoqueCamisetas.filter(item => {
          const matchEvento = !filtroEvento || item.evento_id == filtroEvento;
          const matchStatus = !filtroStatus || item.status === filtroStatus;
          const matchBusca = !busca || item.tamanho.toLowerCase().includes(busca);

          return matchEvento && matchStatus && matchBusca;
        });
      }

      if (filtroTipo === 'produtos' || filtroTipo === '') {
        estoqueProdutos = estoqueProdutos.filter(item => {
          const matchEvento = !filtroEvento || item.evento_id == filtroEvento;
          const matchStatus = !filtroStatus || item.status === filtroStatus;
          const matchBusca = !busca || item.produto_nome.toLowerCase().includes(busca);

          return matchEvento && matchStatus && matchBusca;
        });
      }

      paginaAtual = 1;
      renderizarTabela();
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

  function abrirModalEstoque(id, tipo, quantidadeAtual) {
    document.getElementById('itemId').value = id;
    document.getElementById('itemTipo').value = tipo;
    document.getElementById('quantidadeAtual').value = quantidadeAtual;
    document.getElementById('observacao').value = '';

    document.getElementById('modalEstoque').classList.remove('hidden');
  }

  function fecharModal() {
    document.getElementById('modalEstoque').classList.add('hidden');
  }

  async function atualizarEstoque(e) {
    e.preventDefault();

    const id = document.getElementById('itemId').value;
    const tipo = document.getElementById('itemTipo').value;
    const quantidadeAtual = document.getElementById('quantidadeAtual').value;
    const observacao = document.getElementById('observacao').value;

    if (!id || !quantidadeAtual) {
      alert('Por favor, preencha todos os campos obrigatórios');
      return;
    }

    try {
      const response = await fetch('../../api/organizador/estoque/atualizar.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          id: id,
          tipo: tipo,
          quantidade_atual: quantidadeAtual,
          observacao: observacao
        })
      });

      const data = await response.json();

      if (data.success) {
        fecharModal();
        carregarEstoque();
        alert('Estoque atualizado com sucesso!');
      } else {
        alert('Erro: ' + data.error);
      }
    } catch (error) {
      console.error('Erro ao atualizar estoque:', error);
      alert('Erro ao atualizar estoque');
    }
  }

  // Event listeners para botões
  document.getElementById('atualizarEstoqueBtn').addEventListener('click', () => {
    carregarEstoque();
  });

  document.getElementById('exportarEstoqueBtn').addEventListener('click', () => {
    const dados = tabAtual === 'camisetas' ? estoqueCamisetas : estoqueProdutos;
    const tipo = tabAtual === 'camisetas' ? 'camisetas' : 'produtos';

    const csvContent = "data:text/csv;charset=utf-8," +
      "Evento,Modalidade," + (tipo === 'camisetas' ? 'Tamanho' : 'Produto') + ",Quantidade Inicial," +
      (tipo === 'camisetas' ? 'Retiradas' : 'Vendidas') + ",Estoque Atual,Status\n" +
      dados.map(item =>
        `"${item.evento_nome}","${item.modalidade_nome}","${tipo === 'camisetas' ? item.tamanho : item.produto_nome}","${item.quantidade_inicial}","${tipo === 'camisetas' ? item.quantidade_retirada : item.quantidade_vendida}","${item.estoque_atual}","${item.status}"`
      ).join('\n');

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `estoque_${tipo}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  });
</script>
