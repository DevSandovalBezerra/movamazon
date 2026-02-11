<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once '../../../api/db.php';
require_once '../../../api/helpers/organizador_context.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
  header('Location: ../../../auth/login.php');
  exit();
}

// Buscar eventos do organizador
$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];

$stmt = $pdo->prepare("SELECT id, nome, data_inicio FROM eventos WHERE (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL ORDER BY data_inicio DESC");
$stmt->execute([$organizador_id, $usuario_id]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;

$pageTitle = 'Pagamentos';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pagamentos - MovAmazon</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="../../assets/css/custom.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50">
  <!-- Layout será carregado pelo index.php principal -->

  <div class="p-4 sm:p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Cabeçalho -->
      <div class="mb-6 sm:mb-8">
        <div class="flex items-center justify-between">
          <div>
            <div class="flex items-center space-x-2 mb-2">
              <a href="../index.php" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left"></i> Dashboard
              </a>
              <span class="text-gray-400">/</span>
              <span class="font-semibold">Pagamentos</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Formas de Pagamentos</h1>
            <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Configure as formas de pagamento dos seus eventos</p>
          </div>
          <button onclick="abrirModalCriar()" class="bg-brand-green hover:bg-green-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg flex items-center text-sm">
            <i class="fas fa-plus mr-2"></i>
            Novo Pagamento
          </button>
        </div>
      </div>

      <!-- Filtros -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
            <select id="filtroEvento" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
              <option value="">Todos os eventos</option>
              <?php foreach ($eventos as $evento) { ?>
                <option value="<?php echo $evento['id']; ?>" <?php if ($evento['id'] == $evento_id) echo 'selected'; ?>>
                  <?php echo htmlspecialchars($evento['nome']); ?>
                </option>
              <?php } ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Público</label>
            <select id="filtroTipoPublico" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
              <option value="">Todos</option>
              <option value="comunidade_academica">Comunidade Acadêmica</option>
              <option value="publico_geral">Público Geral</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select id="filtroStatus" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
              <option value="1">Ativos</option>
              <option value="0">Inativos</option>
            </select>
          </div>
          <div class="flex items-end">
            <button onclick="aplicarFiltros()" class="w-full bg-brand-green hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg flex items-center justify-center text-xs sm:text-sm">
              <i class="fas fa-search mr-2"></i>
              Filtrar
            </button>
          </div>
        </div>
      </div>
      <!-- Formulário de Informações Gerais do Evento -->


      <!-- Cards de Formas de Pagamento -->
      <div id="pagamentosGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
        <!-- Cards serão carregados via JavaScript -->
      </div>

      <!-- Paginação -->
      <div class="flex justify-between items-center mt-4 sm:mt-6">
        <div class="text-sm text-gray-700">
          Mostrando <span id="inicio">0</span> a <span id="fim">0</span> de <span id="total">0</span> formas
        </div>
        <div class="flex space-x-2">
          <button id="anterior" class="px-2 sm:px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-xs sm:text-sm">Anterior</button>
          <button id="proximo" class="px-2 sm:px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-xs sm:text-sm">Próximo</button>
        </div>
      </div>
    </div>

    <!-- Modal Adicionar/Editar Forma de Pagamento -->
    <div id="modalPagamento" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 id="modalTitle" class="text-lg font-semibold">Adicionar Forma de Pagamento</h3>
              <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
              </button>
            </div>

            <form id="formPagamento">
              <input type="hidden" id="pagamentoId">

              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
                  <select id="eventoSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                    <option value="">Selecione um evento</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Forma</label>
                  <input type="text" id="nomePagamento" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                  <textarea id="descricaoPagamento" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                    <input type="number" id="ordemPagamento" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parcelamento Máximo</label>
                    <input type="number" id="parcelamentoMaximo" min="1" value="1" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Taxa (%)</label>
                  <input type="number" id="taxaPagamento" step="0.01" min="0" value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>

                <div class="flex items-center">
                  <input type="checkbox" id="ativoPagamento" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                  <label for="ativoPagamento" class="ml-2 block text-sm text-gray-900">Forma de pagamento ativa</label>
                </div>
              </div>

              <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="fecharModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                  Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                  Salvar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script>
      let pagamentos = [];
      let paginaAtual = 1;
      let itensPorPagina = 9;

      document.addEventListener('DOMContentLoaded', function() {
        carregarPagamentos();
        carregarEventos();

        // Event listeners para filtros
        document.getElementById('filtroEvento').addEventListener('change', filtrarPagamentos);
        document.getElementById('filtroStatus').addEventListener('change', filtrarPagamentos);
        document.getElementById('busca').addEventListener('input', filtrarPagamentos);

        // Event listeners para paginação
        document.getElementById('anterior').addEventListener('click', () => {
          if (paginaAtual > 1) {
            paginaAtual--;
            renderizarGrid();
          }
        });

        document.getElementById('proximo').addEventListener('click', () => {
          const totalPaginas = Math.ceil(pagamentos.length / itensPorPagina);
          if (paginaAtual < totalPaginas) {
            paginaAtual++;
            renderizarGrid();
          }
        });

        // Event listener para formulário
        document.getElementById('formPagamento').addEventListener('submit', salvarPagamento);
      });

      async function carregarPagamentos() {
        try {
          const response = await fetch('../../api/organizador/pagamentos/list.php');
          const data = await response.json();

          if (data.success) {
            pagamentos = data.data;
            renderizarGrid();
          } else {
            console.error('Erro ao carregar pagamentos:', data.error);
          }
        } catch (error) {
          console.error('Erro na requisição:', error);
        }
      }

      function renderizarGrid() {
        const grid = document.getElementById('pagamentosGrid');
        const inicio = (paginaAtual - 1) * itensPorPagina;
        const fim = inicio + itensPorPagina;
        const pagamentosPaginados = pagamentos.slice(inicio, fim);

        grid.innerHTML = '';

        pagamentosPaginados.forEach(pagamento => {
          const card = document.createElement('div');
          card.className = 'bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow';

          card.innerHTML = `
      <div class="flex justify-between items-start mb-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">${pagamento.nome}</h3>
          <p class="text-sm text-gray-600">${pagamento.evento_nome}</p>
          <p class="text-xs text-gray-500">${pagamento.data_evento_formatada}</p>
        </div>
        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-${pagamento.ativo_class}-100 text-${pagamento.ativo_class}-800">
          ${pagamento.ativo_texto}
        </span>
      </div>
      
      <div class="space-y-3">
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">Ordem:</span>
          <span class="font-semibold">${pagamento.ordem}</span>
        </div>
        
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">Parcelamento:</span>
          <span class="font-semibold">${pagamento.parcelamento_texto}</span>
        </div>
        
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">Taxa:</span>
          <span class="font-semibold">${pagamento.taxa}%</span>
        </div>
        
        ${pagamento.descricao ? `
          <div class="pt-3 border-t border-gray-200">
            <span class="text-sm font-medium text-gray-600">Descrição:</span>
            <p class="text-sm text-gray-600 mt-1">${pagamento.descricao}</p>
          </div>
        ` : ''}
      </div>
      
      <div class="flex justify-end space-x-2 mt-4 pt-4 border-t border-gray-200">
        <button onclick="editarPagamento(${pagamento.id})" class="text-blue-600 hover:text-blue-900 text-sm">
          <i class="fas fa-edit mr-1"></i>Editar
        </button>
        <button onclick="excluirPagamento(${pagamento.id})" class="text-red-600 hover:text-red-900 text-sm">
          <i class="fas fa-trash mr-1"></i>Excluir
        </button>
      </div>
    `;

          grid.appendChild(card);
        });

        // Atualizar informações de paginação
        document.getElementById('inicio').textContent = inicio + 1;
        document.getElementById('fim').textContent = Math.min(fim, pagamentos.length);
        document.getElementById('total').textContent = pagamentos.length;
      }

      function filtrarPagamentos() {
        const filtroEvento = document.getElementById('filtroEvento').value;
        const filtroStatus = document.getElementById('filtroStatus').value;
        const busca = document.getElementById('busca').value.toLowerCase();

        // Recarregar dados originais
        carregarPagamentos().then(() => {
          // Aplicar filtros
          pagamentos = pagamentos.filter(pagamento => {
            const matchEvento = !filtroEvento || pagamento.evento_id == filtroEvento;
            const matchStatus = !filtroStatus || pagamento.ativo_class === filtroStatus;
            const matchBusca = !busca || pagamento.nome.toLowerCase().includes(busca);

            return matchEvento && matchStatus && matchBusca;
          });

          paginaAtual = 1;
          renderizarGrid();
        });
      }

      async function carregarEventos() {
        try {
          const response = await fetch('../../api/organizador/eventos/list.php');
          const data = await response.json();

          if (data.success) {
            const selectEvento = document.getElementById('filtroEvento');
            const selectEventoModal = document.getElementById('eventoSelect');

            data.data.forEach(evento => {
              // Para filtro
              const option = document.createElement('option');
              option.value = evento.id;
              option.textContent = evento.nome;
              selectEvento.appendChild(option);

              // Para modal
              const optionModal = document.createElement('option');
              optionModal.value = evento.id;
              optionModal.textContent = evento.nome;
              selectEventoModal.appendChild(optionModal);
            });
          }
        } catch (error) {
          console.error('Erro ao carregar eventos:', error);
        }
      }

      function abrirModal(pagamento = null) {
        const modal = document.getElementById('modalPagamento');
        const title = document.getElementById('modalTitle');
        const form = document.getElementById('formPagamento');

        if (pagamento) {
          title.textContent = 'Editar Forma de Pagamento';
          document.getElementById('pagamentoId').value = pagamento.id;
          document.getElementById('eventoSelect').value = pagamento.evento_id;
          document.getElementById('nomePagamento').value = pagamento.nome;
          document.getElementById('descricaoPagamento').value = pagamento.descricao || '';
          document.getElementById('ordemPagamento').value = pagamento.ordem;
          document.getElementById('parcelamentoMaximo').value = pagamento.parcelamento_maximo;
          document.getElementById('taxaPagamento').value = pagamento.taxa;
          document.getElementById('ativoPagamento').checked = pagamento.ativo;
        } else {
          title.textContent = 'Adicionar Forma de Pagamento';
          form.reset();
          document.getElementById('pagamentoId').value = '';
          document.getElementById('ativoPagamento').checked = true;
        }

        modal.classList.remove('hidden');
      }

      function fecharModal() {
        document.getElementById('modalPagamento').classList.add('hidden');
      }

      async function salvarPagamento(e) {
        e.preventDefault();

        const pagamentoId = document.getElementById('pagamentoId').value;
        const eventoId = document.getElementById('eventoSelect').value;
        const nome = document.getElementById('nomePagamento').value;
        const descricao = document.getElementById('descricaoPagamento').value;
        const ordem = document.getElementById('ordemPagamento').value;
        const parcelamentoMaximo = document.getElementById('parcelamentoMaximo').value;
        const taxa = document.getElementById('taxaPagamento').value;
        const ativo = document.getElementById('ativoPagamento').checked;

        if (!eventoId || !nome || !ordem || !parcelamentoMaximo || taxa === '') {
          alert('Por favor, preencha todos os campos obrigatórios');
          return;
        }

        try {
          const url = pagamentoId ? '../../api/organizador/pagamentos/update.php' : '../../api/organizador/pagamentos/create.php';
          const method = pagamentoId ? 'PUT' : 'POST';

          const response = await fetch(url, {
            method: method,
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              id: pagamentoId,
              evento_id: eventoId,
              nome: nome,
              descricao: descricao,
              ordem: ordem,
              parcelamento_maximo: parcelamentoMaximo,
              taxa: taxa,
              ativo: ativo
            })
          });

          const data = await response.json();

          if (data.success) {
            fecharModal();
            carregarPagamentos();
            alert(pagamentoId ? 'Forma de pagamento atualizada com sucesso!' : 'Forma de pagamento criada com sucesso!');
          } else {
            alert('Erro: ' + data.error);
          }
        } catch (error) {
          console.error('Erro ao salvar forma de pagamento:', error);
          alert('Erro ao salvar forma de pagamento');
        }
      }

      function editarPagamento(id) {
        const pagamento = pagamentos.find(p => p.id === id);
        if (pagamento) {
          abrirModal(pagamento);
        }
      }

      function excluirPagamento(id) {
        if (confirm('Tem certeza que deseja excluir esta forma de pagamento?')) {
          // Implementar exclusão
          alert('Funcionalidade de exclusão será implementada');
        }
      }

      // Event listener para botão adicionar
      document.getElementById('adicionarPagamentoBtn').addEventListener('click', () => {
        abrirModal();
      });
    </script>
