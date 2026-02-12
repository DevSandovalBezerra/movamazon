(function() {
    'use strict';

    const ParticipanteQrCode = {
        modal: null,
        qrContainer: null,
        modalNumeroInscricao: null,

        init: function() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setupElements());
            } else {
                this.setupElements();
            }
        },

        setupElements: function() {
            this.modal = document.getElementById('qr-modal');
            this.qrContainer = document.getElementById('qr-code-container');
            this.modalNumeroInscricao = document.getElementById('modal-numero-inscricao');

            if (!this.modal) {
                console.warn('Modal QR Code não encontrado no DOM');
                return;
            }

            this.attachEventListeners();
        },

        attachEventListeners: function() {
            if (this.modal) {
                this.modal.addEventListener('click', (e) => {
                    if (e.target === this.modal) {
                        this.closeModal();
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !this.modal.classList.contains('hidden')) {
                        this.closeModal();
                    }
                });
            }
        },

        showQrCode: function(numeroInscricao, inscricaoId) {
            if (!this.modal) {
                console.error('Modal não inicializado');
                return;
            }

            const dataQr = this.getQrData(numeroInscricao, inscricaoId);

            if (this.modalNumeroInscricao) {
                this.modalNumeroInscricao.textContent = dataQr;
            }

            if (!this.qrContainer) {
                console.error('Container QR Code não encontrado');
                return;
            }

            if (dataQr !== 'N/A') {
                const qrUrl = `../../../api/participante/generate_qr.php?data=${encodeURIComponent(dataQr)}`;
                this.qrContainer.innerHTML = `<img src="${qrUrl}" alt="QR Code de Inscrição" class="max-w-full h-auto">`;
            } else {
                this.qrContainer.innerHTML = `<p class="text-red-600">Inscrição não confere. Entre em contato com o organizador.</p>`;
            }

            this.modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        },

        getQrData: function(numeroInscricao, inscricaoId) {
            if (numeroInscricao && 
                numeroInscricao !== 'null' && 
                numeroInscricao !== '' && 
                numeroInscricao !== 'undefined') {
                return numeroInscricao;
            }

            if (inscricaoId) {
                return `INSC-${inscricaoId}`;
            }

            return 'N/A';
        },

        closeModal: function() {
            if (this.modal) {
                this.modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
    };

    window.ParticipanteQrCode = ParticipanteQrCode;
    window.showQrCode = function(numeroInscricao, inscricaoId) {
        ParticipanteQrCode.showQrCode(numeroInscricao, inscricaoId);
    };
    window.closeModal = function() {
        ParticipanteQrCode.closeModal();
    };
    window.sincronizarStatus = function(inscricaoId, btnElement) {
        if (window.ParticipanteInscricoes && window.ParticipanteInscricoes.sincronizarStatus) {
            window.ParticipanteInscricoes.sincronizarStatus(inscricaoId, btnElement);
        } else {
            console.error('Módulo de inscrições não carregado');
        }
    };

    ParticipanteQrCode.init();
})();

