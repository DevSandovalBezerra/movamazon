(function () {
  'use strict';

  const API_BASE = window.API_BASE || '/api';
  const ENDPOINTS = {
    eventos: `${API_BASE}/organizador/eventos/list.php`,
    visaoGeral: `${API_BASE}/financeiro/visao_geral.php`,
    repasses: `${API_BASE}/financeiro/repasses_listar.php`,
    estornos: `${API_BASE}/financeiro/estornos_listar.php`,
    chargebacks: `${API_BASE}/financeiro/chargebacks_listar.php`,
  };

  const STATUS_OPTIONS = {
    repasses: ['', 'criado', 'agendado', 'processando', 'pago', 'falhou', 'cancelado'],
    estornos: ['', 'solicitado', 'em_processamento', 'concluido', 'negado', 'falhou'],
    chargebacks: ['', 'aberto', 'em_disputa', 'ganho', 'perdido', 'cancelado'],
  };

  const state = {
    eventoId: '',
    eventos: [],
    activeTab: 'repasses',
    tabs: {
      repasses: { page: 1, per: 20, total: 0, items: [] },
      estornos: { page: 1, per: 20, total: 0, items: [] },
      chargebacks: { page: 1, per: 20, total: 0, items: [] },
    },
    loading: false,
  };

  document.addEventListener('DOMContentLoaded', initFinanceiroPage);

  async function initFinanceiroPage() {
    bindEvents();
    setStatusOptionsForTab(state.activeTab);
    setTabUi(state.activeTab);
    await loadEventos();

    const eventoIdFromUrl = new URLSearchParams(window.location.search).get('evento_id');
    if (eventoIdFromUrl) {
      const select = byId('finEventoId');
      if (select) {
        select.value = String(eventoIdFromUrl);
      }
      state.eventoId = String(eventoIdFromUrl);
      await reloadAll();
    } else {
      renderEmptyTable('Selecione um evento para carregar dados financeiros.');
      clearCards();
    }
  }

  function bindEvents() {
    byId('finAplicarEventoBtn')?.addEventListener('click', async () => {
      const eventoId = byId('finEventoId')?.value || '';
      state.eventoId = eventoId;
      resetPages();
      syncEventoInUrl(eventoId);
      await reloadAll();
    });

    byId('finLimparEventoBtn')?.addEventListener('click', () => {
      state.eventoId = '';
      const select = byId('finEventoId');
      if (select) {
        select.value = '';
      }
      resetPages();
      syncEventoInUrl('');
      clearCards();
      renderEmptyTable('Selecione um evento para carregar dados financeiros.');
      showAlert('info', 'Evento limpo. Selecione um evento para consultar.');
    });

    byId('finAplicarFiltrosBtn')?.addEventListener('click', async () => {
      state.tabs[state.activeTab].page = 1;
      await loadActiveTabList();
    });

    byId('finLimparFiltrosBtn')?.addEventListener('click', async () => {
      byId('finStatus').value = '';
      byId('finDtIni').value = '';
      byId('finDtFim').value = '';
      byId('finBusca').value = '';
      byId('finPerPage').value = '20';
      state.tabs[state.activeTab].page = 1;
      await loadActiveTabList();
    });

    byId('finRefreshBtn')?.addEventListener('click', async () => {
      await reloadAll();
    });

    byId('finAnterior')?.addEventListener('click', async () => {
      const tabState = state.tabs[state.activeTab];
      if (tabState.page > 1) {
        tabState.page -= 1;
        await loadActiveTabList();
      }
    });

    byId('finProximo')?.addEventListener('click', async () => {
      const tabState = state.tabs[state.activeTab];
      const totalPages = Math.max(1, Math.ceil(tabState.total / tabState.per));
      if (tabState.page < totalPages) {
        tabState.page += 1;
        await loadActiveTabList();
      }
    });

    document.querySelectorAll('[data-fin-tab]').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const tab = btn.getAttribute('data-fin-tab');
        if (!tab || tab === state.activeTab) return;
        state.activeTab = tab;
        setTabUi(tab);
        setStatusOptionsForTab(tab);
        state.tabs[tab].page = 1;
        await loadActiveTabList();
      });
    });
  }

  async function loadEventos() {
    try {
      const json = await fetchJson(ENDPOINTS.eventos);
      const payload = assertEnvelope(json, 'eventos');
      const eventos = Array.isArray(payload?.eventos)
        ? payload.eventos
        : Array.isArray(payload)
          ? payload
          : [];

      state.eventos = eventos.map((e) => ({
        id: String(e.id ?? ''),
        nome: String(e.nome ?? `Evento ${e.id ?? ''}`),
      })).filter((e) => e.id !== '');

      const select = byId('finEventoId');
      if (!select) return;
      select.innerHTML = '<option value="">Selecione um evento</option>';
      state.eventos.forEach((evento) => {
        const option = document.createElement('option');
        option.value = evento.id;
        option.textContent = evento.nome;
        select.appendChild(option);
      });
    } catch (err) {
      console.error('[financeiro] erro ao carregar eventos', err);
      showAlert('error', `Falha ao carregar eventos: ${err.message}`);
    }
  }

  async function reloadAll() {
    clearAlert();
    if (!state.eventoId) {
      clearCards();
      renderEmptyTable('Selecione um evento para carregar dados financeiros.');
      return;
    }

    await Promise.all([loadVisaoGeral(), loadActiveTabList()]);
  }

  async function loadVisaoGeral() {
    if (!state.eventoId) return;
    try {
      const params = new URLSearchParams({ evento_id: state.eventoId });
      const json = await fetchJson(`${ENDPOINTS.visaoGeral}?${params.toString()}`);
      const payload = assertEnvelope(json, 'visao_geral');
      const data = mapVisaoGeral(payload);
      renderCards(data);
      clearAlert();
    } catch (err) {
      console.error('[financeiro] erro ao carregar visao geral', err);
      clearCards();
      showAlert('error', `Falha ao carregar visao geral: ${err.message}`);
    }
  }

  async function loadActiveTabList() {
    if (!state.eventoId) {
      renderEmptyTable('Selecione um evento para carregar dados financeiros.');
      updatePagination(0, 0, 0);
      return;
    }

    const tab = state.activeTab;
    const tabState = state.tabs[tab];
    tabState.per = toInt(byId('finPerPage')?.value, 20);

    const params = new URLSearchParams({
      evento_id: state.eventoId,
      page: String(tabState.page),
      per: String(tabState.per),
    });

    const status = byId('finStatus')?.value || '';
    const dtIni = byId('finDtIni')?.value || '';
    const dtFim = byId('finDtFim')?.value || '';
    const busca = byId('finBusca')?.value?.trim() || '';

    if (status) params.set('status', status);
    if (dtIni) params.set('dt_ini', dtIni);
    if (dtFim) params.set('dt_fim', dtFim);
    if (busca) params.set('busca', busca);

    const endpoint = tab === 'repasses'
      ? ENDPOINTS.repasses
      : tab === 'estornos'
        ? ENDPOINTS.estornos
        : ENDPOINTS.chargebacks;

    setLoading(true);
    try {
      const json = await fetchJson(`${endpoint}?${params.toString()}`);
      const payload = assertEnvelope(json, tab);
      const mapped = mapPaginatedPayload(payload, tab);
      tabState.total = mapped.total;
      tabState.items = mapped.items;
      tabState.page = mapped.page;
      tabState.per = mapped.per;
      renderTable(tab, mapped.items);
      clearAlert();

      const start = mapped.total > 0 ? ((mapped.page - 1) * mapped.per) + 1 : 0;
      const end = mapped.total > 0 ? Math.min(mapped.total, mapped.page * mapped.per) : 0;
      updatePagination(start, end, mapped.total);
      updatePaginationButtons();
    } catch (err) {
      console.error(`[financeiro] erro ao listar ${tab}`, err);
      renderEmptyTable(`Falha ao carregar ${tab}: ${err.message}`);
      updatePagination(0, 0, 0);
      showAlert('error', `Falha ao carregar ${tab}: ${err.message}`);
    } finally {
      setLoading(false);
    }
  }

  function mapVisaoGeral(raw) {
    const cards = raw?.cards || {};
    const detalhes = raw?.detalhes || {};
    return {
      cards: {
        receitaInscricoes: toNum(cards.receita_inscricoes),
        jaRepassado: toNum(cards.ja_repassado),
        aLiberar: toNum(cards.a_liberar),
        debitos: toNum(cards.debitos),
        saldoDisponivel: toNum(cards.saldo_disponivel),
      },
      detalhes: {
        creditosLiquidados: toNum(detalhes.creditos_liquidados),
        debitosLiquidados: toNum(detalhes.debitos_liquidados),
        debitosBloqueados: toNum(detalhes.debitos_bloqueados),
      },
      geradoEm: raw?.gerado_em ? String(raw.gerado_em) : null,
    };
  }

  function mapPaginatedPayload(raw, tab) {
    const page = Math.max(1, toInt(raw?.page, 1));
    const per = Math.max(1, toInt(raw?.per, 20));
    const total = Math.max(0, toInt(raw?.total, 0));
    const sourceItems = Array.isArray(raw?.items) ? raw.items : [];

    const mapper = tab === 'repasses'
      ? mapRepasseItem
      : tab === 'estornos'
        ? mapEstornoItem
        : mapChargebackItem;

    return {
      page,
      per,
      total,
      items: sourceItems.map(mapper),
    };
  }

  function mapRepasseItem(r) {
    return {
      id: toInt(r?.id, 0),
      beneficiarioNome: String(r?.beneficiario_nome || ''),
      valorSolicitado: toNum(r?.valor_solicitado),
      valorLiquido: toNum(r?.valor_liquido),
      valorTaxa: toNum(r?.valor_taxa_repasse),
      status: String(r?.status || ''),
      agendadoPara: String(r?.agendado_para || ''),
      solicitadoEm: String(r?.solicitado_em || ''),
      processadoEm: String(r?.processado_em || ''),
      gatewayTransferId: String(r?.gateway_transfer_id || ''),
      motivoFalha: String(r?.motivo_falha || ''),
    };
  }

  function mapEstornoItem(r) {
    return {
      id: toInt(r?.id, 0),
      inscricaoId: toInt(r?.inscricao_id, 0),
      paymentId: String(r?.payment_id || ''),
      valor: toNum(r?.valor),
      status: String(r?.status || ''),
      motivo: String(r?.motivo || ''),
      solicitadoEm: String(r?.solicitado_em || ''),
      concluidoEm: String(r?.concluido_em || ''),
      gatewayRefundId: String(r?.gateway_refund_id || ''),
    };
  }

  function mapChargebackItem(r) {
    return {
      id: toInt(r?.id, 0),
      inscricaoId: toInt(r?.inscricao_id, 0),
      paymentId: String(r?.payment_id || ''),
      valor: toNum(r?.valor),
      status: String(r?.status || ''),
      motivo: String(r?.motivo || ''),
      prazoResposta: String(r?.prazo_resposta || ''),
      abertoEm: String(r?.aberto_em || ''),
      encerradoEm: String(r?.encerrado_em || ''),
    };
  }

  function renderCards(data) {
    byId('finCardReceita').textContent = money(data.cards.receitaInscricoes);
    byId('finCardRepassado').textContent = money(data.cards.jaRepassado);
    byId('finCardALiberar').textContent = money(data.cards.aLiberar);
    byId('finCardDebitos').textContent = money(data.cards.debitos);
    byId('finCardSaldo').textContent = money(data.cards.saldoDisponivel);
  }

  function clearCards() {
    byId('finCardReceita').textContent = 'R$ 0,00';
    byId('finCardRepassado').textContent = 'R$ 0,00';
    byId('finCardALiberar').textContent = 'R$ 0,00';
    byId('finCardDebitos').textContent = 'R$ 0,00';
    byId('finCardSaldo').textContent = 'R$ 0,00';
  }

  function renderTable(tab, items) {
    if (!Array.isArray(items) || items.length === 0) {
      renderEmptyTable('Nenhum registro encontrado para os filtros selecionados.');
      return;
    }

    const head = byId('finTableHead');
    const body = byId('finTableBody');
    if (!head || !body) return;

    if (tab === 'repasses') {
      head.innerHTML = `
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Repasse</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Valores</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Datas</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Observacao</th>
        </tr>
      `;
      body.innerHTML = items.map((item) => `
        <tr>
          <td class="px-4 py-3">
            <div class="font-semibold text-gray-900">#${item.id}</div>
            <div class="text-xs text-gray-600">${esc(item.beneficiarioNome || '-')}</div>
            <div class="text-xs text-gray-500">${esc(item.gatewayTransferId || '-')}</div>
          </td>
          <td class="px-4 py-3">
            <div class="text-sm text-gray-900">Solicitado: ${money(item.valorSolicitado)}</div>
            <div class="text-xs text-gray-600">Taxa: ${money(item.valorTaxa)}</div>
            <div class="text-xs text-gray-600">Liquido: ${money(item.valorLiquido)}</div>
          </td>
          <td class="px-4 py-3">${statusBadge(item.status)}</td>
          <td class="px-4 py-3 text-xs text-gray-700">
            <div>Agendado: ${dateOnly(item.agendadoPara)}</div>
            <div>Solicitado: ${dateTime(item.solicitadoEm)}</div>
            <div>Processado: ${dateTime(item.processadoEm)}</div>
          </td>
          <td class="px-4 py-3 text-xs text-gray-700">${esc(item.motivoFalha || '-')}</td>
        </tr>
      `).join('');
      return;
    }

    if (tab === 'estornos') {
      head.innerHTML = `
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estorno</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Valor</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Datas</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Motivo</th>
        </tr>
      `;
      body.innerHTML = items.map((item) => `
        <tr>
          <td class="px-4 py-3">
            <div class="font-semibold text-gray-900">#${item.id}</div>
            <div class="text-xs text-gray-600">Inscricao: ${item.inscricaoId > 0 ? item.inscricaoId : '-'}</div>
            <div class="text-xs text-gray-500">Payment: ${esc(item.paymentId || '-')}</div>
            <div class="text-xs text-gray-500">Refund: ${esc(item.gatewayRefundId || '-')}</div>
          </td>
          <td class="px-4 py-3 text-sm text-gray-900">${money(item.valor)}</td>
          <td class="px-4 py-3">${statusBadge(item.status)}</td>
          <td class="px-4 py-3 text-xs text-gray-700">
            <div>Solicitado: ${dateTime(item.solicitadoEm)}</div>
            <div>Concluido: ${dateTime(item.concluidoEm)}</div>
          </td>
          <td class="px-4 py-3 text-xs text-gray-700">${esc(item.motivo || '-')}</td>
        </tr>
      `).join('');
      return;
    }

    head.innerHTML = `
      <tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Chargeback</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Valor</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Datas</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Motivo</th>
      </tr>
    `;
    body.innerHTML = items.map((item) => `
      <tr>
        <td class="px-4 py-3">
          <div class="font-semibold text-gray-900">#${item.id}</div>
          <div class="text-xs text-gray-600">Inscricao: ${item.inscricaoId > 0 ? item.inscricaoId : '-'}</div>
          <div class="text-xs text-gray-500">Payment: ${esc(item.paymentId || '-')}</div>
          <div class="text-xs text-gray-500">Prazo: ${dateOnly(item.prazoResposta)}</div>
        </td>
        <td class="px-4 py-3 text-sm text-gray-900">${money(item.valor)}</td>
        <td class="px-4 py-3">${statusBadge(item.status)}</td>
        <td class="px-4 py-3 text-xs text-gray-700">
          <div>Aberto: ${dateTime(item.abertoEm)}</div>
          <div>Encerrado: ${dateTime(item.encerradoEm)}</div>
        </td>
        <td class="px-4 py-3 text-xs text-gray-700">${esc(item.motivo || '-')}</td>
      </tr>
    `).join('');
  }

  function renderEmptyTable(message) {
    const head = byId('finTableHead');
    const body = byId('finTableBody');
    if (!head || !body) return;
    head.innerHTML = '';
    body.innerHTML = `
      <tr>
        <td class="px-4 py-10 text-center text-gray-500">
          <i class="fas fa-inbox text-2xl mb-2 block"></i>
          <span>${esc(message)}</span>
        </td>
      </tr>
    `;
  }

  function setStatusOptionsForTab(tab) {
    const select = byId('finStatus');
    if (!select) return;
    const values = STATUS_OPTIONS[tab] || [''];
    select.innerHTML = values.map((value) => {
      const label = value === '' ? 'Todos' : value;
      return `<option value="${escAttr(value)}">${esc(label)}</option>`;
    }).join('');
  }

  function setTabUi(tab) {
    document.querySelectorAll('.fin-tab-btn').forEach((btn) => {
      const btnTab = btn.getAttribute('data-fin-tab');
      const isActive = btnTab === tab;
      btn.classList.toggle('border-brand-green', isActive);
      btn.classList.toggle('text-brand-green', isActive);
      btn.classList.toggle('font-semibold', isActive);
      btn.classList.toggle('border-transparent', !isActive);
      btn.classList.toggle('text-gray-500', !isActive);
    });
  }

  function updatePagination(start, end, total) {
    byId('finInicio').textContent = String(start);
    byId('finFim').textContent = String(end);
    byId('finTotal').textContent = String(total);
  }

  function updatePaginationButtons() {
    const tabState = state.tabs[state.activeTab];
    const totalPages = Math.max(1, Math.ceil(tabState.total / tabState.per));

    const prev = byId('finAnterior');
    const next = byId('finProximo');
    if (prev) prev.disabled = tabState.page <= 1 || tabState.total === 0;
    if (next) next.disabled = tabState.page >= totalPages || tabState.total === 0;
  }

  function syncEventoInUrl(eventoId) {
    const url = new URL(window.location.href);
    if (eventoId) {
      url.searchParams.set('evento_id', eventoId);
    } else {
      url.searchParams.delete('evento_id');
    }
    window.history.replaceState({}, '', url.toString());
  }

  function resetPages() {
    state.tabs.repasses.page = 1;
    state.tabs.estornos.page = 1;
    state.tabs.chargebacks.page = 1;
  }

  async function fetchJson(url) {
    const response = await fetch(url, { credentials: 'same-origin' });
    const text = await response.text();

    let json = null;
    try {
      json = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error(`Resposta invalida do servidor (${response.status}).`);
    }

    if (!response.ok) {
      const message = json?.message || `HTTP ${response.status}`;
      throw new Error(message);
    }

    return json;
  }

  function assertEnvelope(json, contextLabel) {
    if (!json || typeof json !== 'object') {
      throw new Error(`Envelope ausente em ${contextLabel}.`);
    }
    if (!json.success) {
      throw new Error(String(json.message || `Erro na API ${contextLabel}.`));
    }
    return json.data || {};
  }

  function showAlert(type, message) {
    const alert = byId('finAlert');
    if (!alert) return;
    alert.classList.remove('hidden', 'border-red-200', 'bg-red-50', 'text-red-700', 'border-green-200', 'bg-green-50', 'text-green-700', 'border-blue-200', 'bg-blue-50', 'text-blue-700');

    if (type === 'error') {
      alert.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
    } else if (type === 'success') {
      alert.classList.add('border-green-200', 'bg-green-50', 'text-green-700');
    } else {
      alert.classList.add('border-blue-200', 'bg-blue-50', 'text-blue-700');
    }

    alert.textContent = message;
    alert.classList.remove('hidden');
  }

  function clearAlert() {
    const alert = byId('finAlert');
    if (!alert) return;
    alert.textContent = '';
    alert.classList.add('hidden');
  }

  function setLoading(isLoading) {
    state.loading = isLoading;
    const mainButtons = ['finAplicarEventoBtn', 'finAplicarFiltrosBtn', 'finRefreshBtn'];
    mainButtons.forEach((id) => {
      const el = byId(id);
      if (el) el.disabled = isLoading;
    });

    if (isLoading) {
      const prev = byId('finAnterior');
      const next = byId('finProximo');
      if (prev) prev.disabled = true;
      if (next) next.disabled = true;
      return;
    }

    updatePaginationButtons();
  }

  function statusBadge(status) {
    const safeStatus = esc(status || 'desconhecido');
    const colorClass = statusColorClass(status);
    return `<span class="px-2 py-1 text-xs font-semibold rounded-full ${colorClass}">${safeStatus}</span>`;
  }

  function statusColorClass(status) {
    const s = String(status || '').toLowerCase();
    if (['pago', 'concluido', 'ganho', 'liquidado', 'disponivel'].includes(s)) {
      return 'bg-green-100 text-green-800';
    }
    if (['falhou', 'perdido', 'negado', 'cancelado', 'rejeitado'].includes(s)) {
      return 'bg-red-100 text-red-800';
    }
    if (['agendado', 'processando', 'aberto', 'em_disputa', 'em_processamento', 'pendente', 'bloqueado', 'criado', 'solicitado'].includes(s)) {
      return 'bg-yellow-100 text-yellow-800';
    }
    return 'bg-gray-100 text-gray-800';
  }

  function money(value) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(toNum(value));
  }

  function dateTime(value) {
    if (!value) return '-';
    const d = new Date(value.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return esc(value);
    return d.toLocaleString('pt-BR');
  }

  function dateOnly(value) {
    if (!value) return '-';
    const d = new Date(value.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return esc(value);
    return d.toLocaleDateString('pt-BR');
  }

  function byId(id) {
    return document.getElementById(id);
  }

  function toNum(value) {
    const n = Number(value);
    return Number.isFinite(n) ? n : 0;
  }

  function toInt(value, fallback) {
    const n = parseInt(value, 10);
    return Number.isFinite(n) ? n : fallback;
  }

  function esc(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function escAttr(value) {
    return esc(value).replace(/"/g, '&quot;');
  }
})();
