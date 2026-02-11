let participantes = [];
let participantesOriginais = []; // Mantém dados da API para aplicar filtros locais
let paginaAtual = 1;
let itensPorPagina = 10;
let eventos = [];

document.addEventListener('DOMContentLoaded', function () {
  console.log('🚀 DOMContentLoaded - Iniciando página de participantes');

  // Carregar eventos primeiro para os filtros
  carregarEventos().then(() => {
    carregarParticipantes();
  });

  // Event listeners para filtros
  const filtroEvento = document.getElementById('filtroEvento');
  const filtroStatus = document.getElementById('filtroStatus');
  const busca = document.getElementById('busca');

  if (filtroEvento) filtroEvento.addEventListener('change', filtrarParticipantes);
  if (filtroStatus) filtroStatus.addEventListener('change', filtrarParticipantes);
  if (busca) busca.addEventListener('input', filtrarParticipantes);

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
      const totalPaginas = Math.ceil(participantes.length / itensPorPagina);
      if (paginaAtual < totalPaginas) {
        paginaAtual++;
        renderizarTabela();
      }
    });
  }
});

// Carregar eventos para os filtros
async function carregarEventos() {
  try {
    console.log('📡 Carregando eventos para filtros');
    const response = await fetch('../../../api/organizador/eventos/list.php');
    const data = await response.json();

    if (data.success) {
      eventos = data.data.eventos || [];
      console.log('✅ Eventos carregados:', eventos.length);

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
      console.error('❌ Erro ao carregar eventos:', data.message);
    }
  } catch (error) {
    console.error('💥 Erro na requisição de eventos:', error);
  }
}

async function carregarParticipantes(eventoId = null) {
  try {
    console.log('📡 Carregando participantes - Evento ID:', eventoId);

    let url = '../../../api/organizador/participantes/list.php';
    if (eventoId) {
      url += `?evento_id=${eventoId}`;
    }

    console.log('🌐 URL da requisição:', url);

    const response = await fetch(url);
    console.log('📊 Response status:', response.status);

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const responseText = await response.text();
    console.log('📄 Response text:', responseText);

    if (!responseText.trim()) {
      throw new Error('Resposta vazia do servidor');
    }

    const data = JSON.parse(responseText);
    console.log('📋 Data parsed:', data);

    if (data.success) {
      participantesOriginais = data.data || [];
      aplicarFiltrosLocais();
      console.log('✅ Participantes carregados:', participantesOriginais.length);
    } else {
      console.error('❌ Erro ao carregar participantes:', data.message);
      mostrarErro('Erro ao carregar participantes: ' + (data.message || 'Erro desconhecido'));
    }
  } catch (error) {
    console.error('💥 Erro na requisição de participantes:', error);
    mostrarErro('Erro ao carregar participantes: ' + error.message);
  }
}

/**
 * Aplica filtros de Status e Busca em participantesOriginais e atualiza a tabela
 */
function aplicarFiltrosLocais() {
  const filtroStatus = document.getElementById('filtroStatus');
  const busca = document.getElementById('busca');
  const status = filtroStatus ? filtroStatus.value : '';
  const buscaVal = busca ? busca.value.trim().toLowerCase() : '';

  participantes = participantesOriginais.filter(p => {
    const matchStatus = !status || p.status === status;
    const matchBusca = !buscaVal ||
      (p.participante_nome && p.participante_nome.toLowerCase().includes(buscaVal)) ||
      (p.participante_email && p.participante_email.toLowerCase().includes(buscaVal)) ||
      (p.numero_inscricao && String(p.numero_inscricao).toLowerCase().includes(buscaVal));
    return matchStatus && matchBusca;
  });
  paginaAtual = 1;
  renderizarTabela();
}

function renderizarTabela() {
  const tbody = document.getElementById('participantesTable');
  const inicio = (paginaAtual - 1) * itensPorPagina;
  const fim = inicio + itensPorPagina;
  const participantesPaginados = participantes.slice(inicio, fim);

  tbody.innerHTML = '';

  if (participantes.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
          <i class="fas fa-users text-4xl mb-2"></i>
          <p>Nenhum participante encontrado</p>
        </td>
      </tr>
    `;
    document.getElementById('inicio').textContent = '0';
    document.getElementById('fim').textContent = '0';
    document.getElementById('total').textContent = '0';
    return;
  }

  participantesPaginados.forEach(participante => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="px-6 py-4 whitespace-nowrap">
        <div>
          <div class="text-sm font-medium text-gray-900">${participante.participante_nome || 'N/A'}</div>
          <div class="text-sm text-gray-500">${participante.participante_email || 'N/A'}</div>
        </div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${participante.numero_inscricao ? participante.numero_inscricao : ('ID ' + participante.id)}</td>
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">${participante.evento_nome || 'N/A'}</div>
        <div class="text-sm text-gray-500">${participante.data_evento_formatada || 'N/A'}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${participante.valor_formatado || 'N/A'}</td>
      <td class="px-6 py-4 whitespace-nowrap">
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getBadgeClass(participante.status_class)}">
          ${participante.status || 'N/A'}
        </span>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getBadgeClass(participante.status_pagamento_class)}">
          ${participante.status_pagamento || 'N/A'}
        </span>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${participante.data_inscricao_formatada || 'N/A'}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <button onclick="verDetalhes(${participante.id})" class="text-blue-600 hover:text-blue-900 mr-3" title="Ver Detalhes">
          <i class="fas fa-eye"></i>
        </button>
        <button onclick="sincronizarPagamento(${participante.id})" class="text-green-600 hover:text-green-900 mr-3" title="Sincronizar Pagamento">
          <i class="fas fa-sync-alt"></i>
        </button>
        <button onclick="enviarEmail('${participante.participante_email}')" class="text-green-600 hover:text-green-900" title="Enviar Email">
          <i class="fas fa-envelope"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  document.getElementById('inicio').textContent = inicio + 1;
  document.getElementById('fim').textContent = Math.min(fim, participantes.length);
  document.getElementById('total').textContent = participantes.length;
}

function filtrarParticipantes() {
  const filtroEvento = document.getElementById('filtroEvento').value;

  // Evento selecionado: recarrega da API e depois aplica Status + Busca localmente
  if (filtroEvento) {
    carregarParticipantes(filtroEvento);
  } else {
    // Todos os eventos: aplica apenas filtros locais (Status e Busca)
    if (participantesOriginais.length === 0) {
      carregarParticipantes();
      return;
    }
    aplicarFiltrosLocais();
  }
}

function verDetalhes(participanteId) {
  const participante = participantes.find(p => p.id === participanteId);
  if (participante) {
    const modalContent = document.getElementById('modalContent');
    modalContent.innerHTML = `
      <div class="space-y-4">
        <div>
          <h4 class="font-semibold text-gray-900">Informações Pessoais</h4>
          <p><strong>Nome:</strong> ${participante.participante_nome || 'N/A'}</p>
          <p><strong>Email:</strong> ${participante.participante_email || 'N/A'}</p>
        </div>
        <div>
          <h4 class="font-semibold text-gray-900">Inscrição</h4>
          <p><strong>Nº Inscrição:</strong> ${participante.numero_inscricao ? participante.numero_inscricao : ('ID ' + participante.id)}</p>
          <p><strong>Evento:</strong> ${participante.evento_nome || 'N/A'}</p>
          <p><strong>Valor:</strong> ${participante.valor_formatado || 'N/A'}</p>
          <p><strong>Status:</strong> ${participante.status || 'N/A'}</p>
          <p><strong>Data de Inscrição:</strong> ${participante.data_inscricao_formatada || 'N/A'}</p>
        </div>
      </div>
    `;

    document.getElementById('modalDetalhes').classList.remove('hidden');
  }
}

function fecharModal() {
  document.getElementById('modalDetalhes').classList.add('hidden');
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

function enviarEmail(email) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({ icon: 'info', title: 'Enviar email', text: `Funcionalidade em desenvolvimento. Email: ${email}` });
  } else {
    alert(`Enviar email para: ${email}`);
  }
}

async function sincronizarPagamento(inscricaoId) {
  const ok = await showSwalConfirm('Sincronizar pagamento', 'Deseja sincronizar o status de pagamento desta inscrição com o Mercado Pago?');
  if (!ok) return;

  if (typeof Swal !== 'undefined') {
    Swal.fire({ title: 'Sincronizando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
  }

  try {
    const response = await fetch('../../../api/organizador/participantes/sync_payment.php', {
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
      carregarParticipantes(filtroEvento || null);
    } else {
      await showSwalError('Erro ao sincronizar', data.message || 'Erro desconhecido');
    }
  } catch (error) {
    if (typeof Swal !== 'undefined') Swal.close();
    console.error('Erro ao sincronizar pagamento:', error);
    await showSwalError('Erro', 'Erro ao sincronizar pagamento: ' + error.message);
  }
}

function mostrarErro(mensagem) {
  console.error('❌ Erro:', mensagem);
  if (typeof Swal !== 'undefined') {
    Swal.fire({ icon: 'error', title: 'Erro ao carregar participantes', text: mensagem });
  }
  const tbody = document.getElementById('participantesTable');
  if (tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" class="px-6 py-4 text-center text-red-500">
          <i class="fas fa-exclamation-triangle text-4xl mb-2"></i>
          <p class="font-semibold">Erro ao carregar participantes</p>
          <p class="text-sm">${mensagem}</p>
        </td>
      </tr>
    `;
  }
  document.getElementById('inicio').textContent = '0';
  document.getElementById('fim').textContent = '0';
  document.getElementById('total').textContent = '0';
}

// Exportar dados
const exportarBtn = document.getElementById('exportarBtn');
if (exportarBtn) {
  exportarBtn.addEventListener('click', function () {
    if (participantes.length === 0) {
      showSwalError('Nada para exportar', 'Não há participantes na lista para exportar.');
      return;
    }
    const csvContent = "data:text/csv;charset=utf-8," +
      "Nome,Email,Nº Inscrição,Evento,Valor,Status,Status Pagamento,Data Inscrição\n" +
      participantes.map(p =>
        `"${p.participante_nome || ''}","${p.participante_email || ''}","${p.numero_inscricao || ''}","${p.evento_nome || ''}","${p.valor_formatado || ''}","${p.status || ''}","${p.status_pagamento || ''}","${p.data_inscricao_formatada || ''}"`
      ).join('\n');

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "participantes.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showSwalSuccess('Exportado', 'Arquivo participantes.csv foi baixado.');
  });
}

// Enviar comunicado
const enviarComunicadoBtn = document.getElementById('enviarComunicadoBtn');
if (enviarComunicadoBtn) {
  enviarComunicadoBtn.addEventListener('click', function () {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'info', title: 'Enviar comunicado', text: 'Funcionalidade de envio de comunicados será implementada em breve.' });
    } else {
      alert('Funcionalidade de envio de comunicados será implementada');
    }
  });
}