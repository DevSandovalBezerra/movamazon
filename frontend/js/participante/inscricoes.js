(function() {
    'use strict';

    const ParticipanteInscricoes = {
        container: null,
        loadingEl: null,
        nenhumaInscricaoEl: null,

        init: function() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.loadInscricoes());
            } else {
                this.loadInscricoes();
            }
        },

        loadInscricoes: function() {
            this.container = document.getElementById('inscricoes-container');
            this.loadingEl = document.getElementById('loading');
            this.nenhumaInscricaoEl = document.getElementById('nenhuma-inscricao');

            if (!this.container || !this.loadingEl) {
                console.error('Elementos DOM não encontrados');
                return;
            }

            // Sync da inscrição quando veio de retorno de pagamento (ex.: login após sucesso/pendente)
            const params = new URLSearchParams(window.location.search);
            const inscricaoIdUrl = params.get('inscricao_id');
            const retornoPagamento = params.get('retorno_pagamento');
            const syncPromise = inscricaoIdUrl
                ? fetch('../../../api/participante/sync_payment_status.php?inscricao_id=' + encodeURIComponent(inscricaoIdUrl), { method: 'GET', credentials: 'same-origin' }).then(r => r.json())
                : Promise.resolve(null);

            syncPromise.then(syncResult => {
                if (syncResult && syncResult.success && retornoPagamento && typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Inscrição confirmada', text: 'Verifique abaixo.', timer: 3000, showConfirmButton: false });
                }
            }).catch(() => {});

            syncPromise.finally(() => {
                this._fetchInscricoes();
            });
        },

        _fetchInscricoes: function() {
            fetch('../../../api/participante/get_inscricoes.php', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                this.loadingEl.classList.add('hidden');
                if (data.success && data.inscricoes && data.inscricoes.length > 0) {
                    this.container.classList.remove('hidden');
                    this.renderInscricoes(data.inscricoes);
                    // Sincronizar com Mercado Pago inscrições pendentes/processando (ex.: PIX já pago e webhook atrasou)
                    this.sincronizarPendentesComMP(data.inscricoes);
                } else {
                    if (this.nenhumaInscricaoEl) {
                        this.nenhumaInscricaoEl.classList.remove('hidden');
                    }
                }
            })
            .catch(err => {
                console.error('Erro ao carregar inscrições:', err);
                this.showError();
            });
        },

        renderInscricoes: function(inscricoes) {
            if (!this.container) return;

            this.container.innerHTML = '';
            inscricoes.forEach(inscricao => {
                const card = this.createInscricaoCard(inscricao);
                this.container.appendChild(card);
            });
        },

        createInscricaoCard: function(inscricao) {
            const card = document.createElement('div');
            card.className = 'bg-white p-4 sm:p-6 rounded-lg shadow-md';

            const statusHtml = this.getStatusHtml(inscricao);
            const acaoHtml = this.getAcaoHtml(inscricao);
            const dataFormatada = inscricao.evento_data 
                ? new Date(inscricao.evento_data + 'T00:00:00').toLocaleDateString('pt-BR')
                : 'Data não informada';

            card.innerHTML = `
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                    <div class="flex-grow">
                        <h2 class="text-xl font-bold">${this.escapeHtml(inscricao.evento_nome || 'Evento sem nome')}</h2>
                        <p class="text-gray-600">${this.escapeHtml(inscricao.modalidade_nome || 'Modalidade não informada')} - ${this.escapeHtml(inscricao.kit_nome || 'Kit Padrão')}</p>
                        <p class="text-sm text-gray-500">${dataFormatada} - ${this.escapeHtml(inscricao.evento_local || 'Local não informado')}</p>
                        ${statusHtml}
                    </div>
                    <div class="w-full md:w-auto mt-3 md:mt-0 md:ml-6 flex-shrink-0">
                        ${acaoHtml}
                    </div>
                </div>
            `;

            return card;
        },

        getStatusHtml: function(inscricao) {
            const status = inscricao.status || 'pendente';
            const statusMap = {
                'confirmada': '<span class="mt-2 inline-block px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Confirmada</span>',
                'pendente': '<span class="mt-2 inline-block px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendente</span>',
                'cancelada': '<span class="mt-2 inline-block px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Cancelada</span>'
            };
            return statusMap[status] || statusMap['pendente'];
        },

        getAcaoHtml: function(inscricao) {
            const status = inscricao.status || 'pendente';
            const inscricaoId = inscricao.inscricao_id || inscricao.id;

            if (status === 'confirmada') {
                const numeroInscricao = this.escapeHtml(inscricao.numero_inscricao || '');
                return `<button onclick="ParticipanteInscricoes.showQrCode('${numeroInscricao}', ${inscricaoId})" class="w-full md:w-auto bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 touch-target">Ver QR Code</button>`;
            } else if (status === 'pendente') {
                if (inscricao.status_pagamento === 'pago') {
                    return `
                        <div class="space-y-2">
                            <button onclick="ParticipanteInscricoes.sincronizarStatus(${inscricaoId}, this)" class="w-full md:w-auto block text-center bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 touch-target">
                                Sincronizar Status
                            </button>
                            <a href="index.php?page=pagamento-inscricao&inscricao_id=${inscricaoId}" class="w-full md:w-auto block text-center bg-orange-500 text-white font-bold py-2 px-4 rounded hover:bg-orange-600 touch-target">
                                Pagar Agora
                            </a>
                        </div>
                    `;
                } else {
                    return `
                        <div class="space-y-2">
                            <button onclick="ParticipanteInscricoes.sincronizarStatus(${inscricaoId}, this)" class="w-full md:w-auto block text-center bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 touch-target">Verificar pagamento</button>
                            <a href="index.php?page=pagamento-inscricao&inscricao_id=${inscricaoId}" class="w-full md:w-auto block text-center bg-orange-500 text-white font-bold py-2 px-4 rounded hover:bg-orange-600 touch-target">Pagar Agora</a>
                        </div>
                    `;
                }
            } else if (status === 'cancelada') {
                return '<span class="text-gray-500 text-sm">Inscrição cancelada</span>';
            }
            return '';
        },

        sincronizarPendentesComMP: function(inscricoes) {
            const pendentes = inscricoes.filter(i => {
                const s = i.status || 'pendente';
                const sp = i.status_pagamento || 'pendente';
                return s === 'pendente' || sp === 'processando';
            });
            if (pendentes.length === 0) return;
            const idKey = (i) => i.inscricao_id || i.id;
            Promise.all(pendentes.map(i =>
                fetch('../../../api/participante/sync_payment_status.php?inscricao_id=' + idKey(i), { credentials: 'same-origin' }).then(r => r.json())
            )).then(results => {
                const algumAtualizado = results.some(r => r.success && r.atualizado);
                if (algumAtualizado) this.loadInscricoes();
            }).catch(() => {});
        },

        sincronizarStatus: function(inscricaoId, btnElement) {
            const btn = btnElement || event?.target || window.event?.target;
            if (!btn) {
                console.error('Botão não encontrado');
                return;
            }

            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Sincronizando...';

            fetch(`../../../api/participante/sync_payment_status.php?inscricao_id=${inscricaoId}`, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (data.atualizado) {
                        alert('Status sincronizado com sucesso! A página será recarregada.');
                        location.reload();
                    } else {
                        alert('Status já está atualizado.');
                        btn.disabled = false;
                        btn.textContent = originalText;
                    }
                } else {
                    alert('Erro ao sincronizar: ' + (data.message || 'Erro desconhecido'));
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao sincronizar status. Tente novamente.');
                btn.disabled = false;
                btn.textContent = originalText;
            });
        },

        showQrCode: function(numeroInscricao, inscricaoId) {
            if (typeof window.ParticipanteQrCode !== 'undefined' && window.ParticipanteQrCode.showQrCode) {
                window.ParticipanteQrCode.showQrCode(numeroInscricao, inscricaoId);
            } else if (typeof window.showQrCode === 'function') {
                window.showQrCode(numeroInscricao, inscricaoId);
            } else {
                console.error('Módulo QR Code não carregado');
            }
        },

        showError: function() {
            if (!this.loadingEl) return;

            this.loadingEl.innerHTML = `
                <div class="text-center py-16 bg-white rounded-lg shadow-lg">
                    <div class="max-w-md mx-auto">
                        <div class="w-24 h-24 bg-gradient-to-br from-red-100 to-red-200 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Ops! Algo deu errado</h3>
                        <p class="text-gray-600 mb-6 leading-relaxed">
                            Não foi possível carregar suas inscrições no momento. Tente novamente em alguns instantes.
                        </p>
                        <div class="space-y-3">
                            <button onclick="location.reload()" class="inline-block w-full bg-brand-green text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition-colors duration-200 shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Tentar Novamente
                            </button>
                            <button onclick="window.history.back()" class="inline-block w-full bg-gray-100 text-gray-700 font-medium py-2 px-6 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                                Voltar
                            </button>
                        </div>
                        <div class="mt-8 p-4 bg-red-50 rounded-lg border border-red-200">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-left">
                                    <p class="text-sm font-medium text-red-900 mb-1">Problema persistente?</p>
                                    <p class="text-sm text-red-700">
                                        Entre em contato com o suporte se o problema continuar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        },

        escapeHtml: function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    window.ParticipanteInscricoes = ParticipanteInscricoes;
    ParticipanteInscricoes.init();
})();

