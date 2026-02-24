if (window.getApiBase) { window.getApiBase(); }
let inscricoes = [];
let inscricoesOriginais = []; // MantÒ©m dados originais para filtros locais
let paginaAtual = 1;
let itensPorPagina = 10;
let eventos = [];

document.addEventListener('DOMContentLoaded', function () {
  console.log(' DOMContentLoaded - Iniciando página de inscrições');

  // Carregar eventos primeiro para os filtros
  carregarEventos().then(() => {
    carregarInscricoes();
  });

  // Event listeners para filtros
  const filtroEvento = document.getElementById('filtroEvento');
  const filtroStatus = document.getElementById('filtroStatus');
  const filtroStatusPagamento = document.getElementById('filtroStatusPagamento');
  const busca = document.getElementById('busca');

  if (filtroEvento) filtroEvento.addEventListener('change', filtrarInscricoes);
  if (filtroStatus) filtroStatus.addEventListener('change', filtrarInscricoes);
  if (filtroStatusPagamento) filtroStatusPagamento.addEventListener('change', filtrarInscricoes);
  if (busca) busca.addEventListener('input', filtrarInscricoes);

  // Event listeners para paginação
  const btnAnterior = document.getElementById('anterior');
  const btnProximo = document.getElementById('proximo');

  if (btnAnterior) {
    btnAnterior.addEventListener('click', () => {
      if (paginaAtual > 1) {
        paginaAtual--;
        renderizarTabela();
      }
    });
  }

  if (btnProximo) {
    btnProximo.addEventListener('click', () => {
      const totalPaginas = Math.ceil(inscricoes.length / itensPorPagina);
      if (paginaAtual < totalPaginas) {
        paginaAtual++;
        renderizarTabela();
      }
    });
  }

  // Exportar
  const exportarBtn = document.getElementById('exportarBtn');
  if (exportarBtn) {
    exportarBtn.addEventListener('click', function () {
      if (inscricoes.length === 0) {
        showSwalError('Nada para exportar', 'Não hÒ¡ inscrições na lista para exportar.');
        return;
      }
      const csvContent = "data:text/csv;charset=utf-8," +
        "Nome,Email,Evento,Modalidade,Valor,Status,Status Pagamento,Data Inscrição\n" +
        inscricoes.map(i =>
          `"${i.participante_nome || ''}","${i.participante_email || ''}","${i.evento_nome || ''}","${i.modalidade_nome || ''}","${i.valor_formatado || ''}","${i.status || ''}","${i.status_pagamento || ''}","${i.data_inscricao_formatada || ''}"`
        ).join('\n');

      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", "inscricoes.csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      showSwalSuccess('Exportado', 'Arquivo inscricoes.csv foi baixado.');
    });
  }
});

// Carregar eventos para os filtros
async function carregarEventos() {
  try {
    console.log(' Carregando eventos para filtros');
    const response = await fetch((window.API_BASE || '/api') + '/admin/eventos/list.php');
    const data = await response.json();

    if (data.success) {
      eventos = data.data || [];
      console.log(' Eventos carregados:', eventos.length);

      // Preencher filtro de eventos
      const selectEvento = document.getElementById('filtroEvento');
      selectEvento.innerHTML = '<option value="">Todos os eventos</option>';

      eventos.forEach(evento => {
        const option = document.createElement('option');
        option.value = evento.id;
        option.textContent = evento.nome;
        selectEvento.appendChild(option);
      });
    } else {
      console.error(' Erro ao carregar eventos:', data.message);
    }
  } catch (error) {
    console.error('"¥ Erro na requisição de eventos:', error);
  }
}

async function carregarInscricoes(eventoId = null, aplicarFiltrosAPI = false) {
  try {
    console.log(' Carregando inscrições - Evento ID:', eventoId, 'Aplicar filtros API:', aplicarFiltrosAPI);

    let url = (window.API_BASE || '/api') + '/admin/inscricoes/list.php';
    const params = new URLSearchParams();
    
    if (eventoId) {
      params.append('evento_id', eventoId);
    }
    
    // SÒ³ aplicar filtros na API se evento estiver selecionado
    if (aplicarFiltrosAPI && eventoId) {
      const status = document.getElementById('filtroStatus')?.value;
      const statusPagamento = document.getElementById('filtroStatusPagamento')?.value;
      const busca = document.getElementById('busca')?.value;
      
      if (status) params.append('status', status);
      if (statusPagamento) params.append('status_pagamento', statusPagamento);
      if (busca) params.append('busca', busca);
    }
    
    if (params.toString()) {
      url += '?' + params.toString();
    }

    console.log(' URL da requisição:', url);

    const response = await fetch(url);
    console.log(' Response status:', response.status);

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const responseText = await response.text();
    console.log(' Response text:', responseText);

    if (!responseText.trim()) {
      throw new Error('Resposta vazia do servidor');
    }

    const data = JSON.parse(responseText);
    console.log(' Data parsed:', data);

    if (data.success) {
      inscricoes = data.data || [];
      inscricoesOriginais = data.data || []; // Manter cÒ³pia dos dados originais
      console.log(' Inscrições carregadas:', inscricoes.length);
      paginaAtual = 1;
      renderizarTabela();
    } else {
      console.error(' Erro ao carregar inscrições:', data.message);
      mostrarErro('Erro ao carregar inscrições: ' + (data.message || 'Erro desconhecido'));
    }
  } catch (error) {
    console.error('"¥ Erro na requisição de inscrições:', error);
    mostrarErro('Erro ao carregar inscrições: ' + error.message);
  }
}

function showSwalSuccess(title, text) {
  if (typeof Swal !== 'undefined') {
    return Swal.fire({ icon: 'success', title: title || 'Sucesso', text: text || '', timer: 2500, showConfirmButton: false });
  }
  alert((title || 'Sucesso') + (text ? '\n' + text : ''));
}

function showSwalError(title, text) {
  if (typeof Swal !== 'undefined') {
    return Swal.fire({ icon: 'error', title: title || 'Erro', text: text || '' });
  }
  alert((title || 'Erro') + (text ? '\n' + text : ''));
}

async function showSwalConfirm(title, text) {
  if (typeof Swal !== 'undefined') {
    const result = await Swal.fire({
      title: title || 'Confirmar',
      text: text || '',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#0b4340',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Sim',
      cancelButtonText: 'Cancelar'
    });
    return result.isConfirmed;
  }
  return confirm((title || 'Confirmar') + (text ? '\n' + text : ''));
}

function renderizarTabela() {
  const tbody = document.getElementById('inscricoesTable');
  const inicio = (paginaAtual - 1) * itensPorPagina;
  const fim = inicio + itensPorPagina;
  const inscricoesPaginadas = inscricoes.slice(inicio, fim);

  tbody.innerHTML = '';

  if (inscricoes.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
          <i class="fas fa-clipboard-list text-4xl mb-2"></i>
          <p>Nenhuma inscrição encontrada</p>
        </td>
      </tr>
    `;
    return;
  }

  inscricoesPaginadas.forEach(inscricao => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
        <div>
          <div class="text-sm font-medium text-gray-900">${inscricao.participante_nome || 'N/A'}</div>
          <div class="text-sm text-gray-500">${inscricao.participante_email || 'N/A'}</div>
        </div>
      </td>
      <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
        <div class="text-sm text-gray-900">${inscricao.evento_nome || 'N/A'}</div>
        <div class="text-sm text-gray-500">${inscricao.data_evento_formatada || 'N/A'}</div>
      </td>
      <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-sm text-gray-900">${inscricao.modalidade_nome || 'N/A'}</td>
      <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-sm text-gray-900">${inscricao.valor_formatado || 'N/A'}</td>
      <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getBadgeClass(inscricao.status_class)}">
          ${inscricao.status || 'N/A'}
        </span>
      </td>
      <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getBadgeClass(inscricao.status_pagamento_class)}">
          ${inscricao.status_pagamento || 'N/A'}
        </span>
      </td>
      <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-sm text-gray-500">${inscricao.data_inscricao_formatada || 'N/A'}</td>
      <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-sm font-medium">
        <button onclick="verDetalhes(${inscricao.id})" class="text-blue-600 hover:text-blue-900 mr-3" title="Ver Detalhes">
          <i class="fas fa-eye"></i>
        </button>
        <button onclick="sincronizarPagamento(${inscricao.id})" class="text-green-600 hover:text-green-900" title="Sincronizar Pagamento">
          <i class="fas fa-sync-alt"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  // Atualizar informações de paginação
  document.getElementById('inicio').textContent = inscricoes.length > 0 ? inicio + 1 : 0;
  document.getElementById('fim').textContent = Math.min(fim, inscricoes.length);
  document.getElementById('total').textContent = inscricoes.length;
}

function filtrarInscricoes() {
  console.log(' Aplicando filtros de inscrições');

  const filtroEvento = document.getElementById('filtroEvento').value;
  const filtroStatus = document.getElementById('filtroStatus').value;
  const filtroStatusPagamento = document.getElementById('filtroStatusPagamento').value;
  const busca = document.getElementById('busca').value.toLowerCase();

  console.log(' Filtros aplicados:', {
    filtroEvento,
    filtroStatus,
    filtroStatusPagamento,
    busca
  });

  // Se hÒ¡ filtro de evento, recarregar dados da API
  if (filtroEvento) {
    carregarInscricoes(filtroEvento, true);
  } else {
    // Aplicar filtros locais nos dados originais
    // Se não hÒ¡ dados carregados, carregar primeiro
    if (inscricoesOriginais.length === 0) {
      carregarInscricoes();
      return;
    }

    // Aplicar filtros locais nos dados originais
    inscricoes = inscricoesOriginais.filter(inscricao => {
      const matchStatus = !filtroStatus || inscricao.status === filtroStatus;
      const matchStatusPagamento = !filtroStatusPagamento || inscricao.status_pagamento === filtroStatusPagamento;
      const matchBusca = !busca ||
        (inscricao.participante_nome && inscricao.participante_nome.toLowerCase().includes(busca)) ||
        (inscricao.participante_email && inscricao.participante_email.toLowerCase().includes(busca)) ||
        (inscricao.numero_inscricao && inscricao.numero_inscricao.toLowerCase().includes(busca));

      return matchStatus && matchStatusPagamento && matchBusca;
    });

    paginaAtual = 1;
    renderizarTabela();
  }
}

function verDetalhes(inscricaoId) {
  const inscricao = inscricoes.find(i => i.id === inscricaoId);
  if (inscricao) {
    const modalContent = document.getElementById('modalContent');
    modalContent.innerHTML = `
      <div class="space-y-4">
        <div>
          <h4 class="font-semibold text-gray-900">Informações Pessoais</h4>
          <p><strong>Nome:</strong> ${inscricao.participante_nome || 'N/A'}</p>
          <p><strong>Email:</strong> ${inscricao.participante_email || 'N/A'}</p>
        </div>
        <div>
          <h4 class="font-semibold text-gray-900">Inscrição</h4>
          <p><strong>Evento:</strong> ${inscricao.evento_nome || 'N/A'}</p>
          <p><strong>Modalidade:</strong> ${inscricao.modalidade_nome || 'N/A'}</p>
          <p><strong>Valor:</strong> ${inscricao.valor_formatado || 'N/A'}</p>
          <p><strong>Status:</strong> ${inscricao.status || 'N/A'}</p>
          <p><strong>Status Pagamento:</strong> ${inscricao.status_pagamento || 'N/A'}</p>
          <p><strong>Número de Inscrição:</strong> ${inscricao.numero_inscricao || 'N/A'}</p>
          <p><strong>Protocolo:</strong> ${inscricao.protocolo || 'N/A'}</p>
          <p><strong>Data de Inscrição:</strong> ${inscricao.data_inscricao_formatada || 'N/A'}</p>
        </div>
      </div>
    `;

    document.getElementById('modalDetalhes').classList.remove('hidden');
  }
}

function fecharModal() {
  document.getElementById('modalDetalhes').classList.add('hidden');
}

async function sincronizarPagamento(inscricaoId) {
  const ok = await showSwalConfirm('Sincronizar pagamento', 'Deseja sincronizar o status de pagamento desta inscrição com o Mercado Pago?');
  if (!ok) return;

  if (typeof Swal !== 'undefined') {
    Swal.fire({ title: 'Sincronizando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
  }

  try {
    const response = await fetch((window.API_BASE || '/api') + '/admin/sync_payment_status.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        inscricao_id: inscricaoId
      })
    });

    const data = await response.json();

    if (typeof Swal !== 'undefined') Swal.close();

    if (data.success) {
      await showSwalSuccess('Sucesso', 'Status sincronizado com sucesso!');
      const filtroEvento = document.getElementById('filtroEvento').value;
      carregarInscricoes(filtroEvento || null);
    } else {
      await showSwalError('Erro ao sincronizar', data.message || 'Erro desconhecido');
    }
  } catch (error) {
    if (typeof Swal !== 'undefined') Swal.close();
    console.error('Erro ao sincronizar pagamento:', error);
    await showSwalError('Erro', 'Erro ao sincronizar pagamento: ' + error.message);
  }
}

function getBadgeClass(statusClass) {
  const classes = {
    'success': 'bg-green-100 text-green-800',
    'warning': 'bg-yellow-100 text-yellow-800',
    'danger': 'bg-red-100 text-red-800',
    'info': 'bg-blue-100 text-blue-800',
    'secondary': 'bg-gray-100 text-gray-800'
  };
  return classes[statusClass] || classes['secondary'];
}

function mostrarErro(mensagem) {
  console.error(' Erro:', mensagem);
  if (typeof Swal !== 'undefined') {
    Swal.fire({ icon: 'error', title: 'Erro ao carregar inscrições', text: mensagem });
  }
  const tbody = document.getElementById('inscricoesTable');
  if (tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" class="px-6 py-4 text-center text-red-500">
          <i class="fas fa-exclamation-triangle text-4xl mb-2"></i>
          <p class="font-semibold">Erro ao carregar inscrições</p>
          <p class="text-sm">${mensagem}</p>
        </td>
      </tr>
    `;
  }
  document.getElementById('inicio').textContent = '0';
  document.getElementById('fim').textContent = '0';
  document.getElementById('total').textContent = '0';
}
