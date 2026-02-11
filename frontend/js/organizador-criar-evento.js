console.log('CriarEventoManager constructor');
class CriarEventoManager {

    constructor() {
        this.currentStep = 1;
        this.totalSteps = 5;
        this.debugEnabled = true;
        this.nextStepLastTs = 0;
        this.instanceId = Math.random().toString(36).slice(2, 8);
        this.formData = {};
        this.init();
    }

    log(message, payload) {
        try {
            if (!this.debugEnabled) return;
            if (payload !== undefined) {
                console.log(`[CriarEvento][${this.instanceId}] ${message}`, payload);
            } else {
                console.log(`[CriarEvento][${this.instanceId}] ${message}`);
            }
        } catch (_) {}
    }

    reportState(tag) {
        const states = [];
        for (let i = 1; i <= this.totalSteps; i++) {
            const el = document.getElementById(`step-${i}`);
            states.push({
                step: i,
                exists: !!el,
                hidden: el ? el.classList.contains('hidden') : null
            });
        }
        this.log(`[STATE] ${tag}`, states);
    }

    init() {
        this.log('[INIT] Inicializando manager', {
            instanceId: this.instanceId
        });
        // Ajustar totalSteps dinamicamente, se necessário
        const stepsCount = document.querySelectorAll('.step-content').length;
        if (typeof stepsCount === 'number' && stepsCount > 0 && stepsCount !== this.totalSteps) {
            this.log('[INIT] Atualizando totalSteps dinamicamente', {
                from: this.totalSteps,
                to: stepsCount
            });
            this.totalSteps = stepsCount;
        }
        this.bindEvents();
        this.setMinDate();
        this.log('[INIT] Estado inicial', {
            currentStep: this.currentStep,
            totalSteps: this.totalSteps
        });
        this.reportState('after-init');
    }

    bindEvents() {
        // Botões de navegação
        const btnProximo = document.getElementById('btn-proximo');
        const btnAnterior = document.getElementById('btn-anterior');
        const binds = {
            proximo: btnProximo && btnProximo.dataset.bound,
            anterior: btnAnterior && btnAnterior.dataset.bound,
        };
        //this.log('[BIND] Verificando binds existentes', binds);
        //console.log('Verificando binds existentes', binds);

        if (btnProximo && !btnProximo.dataset.bound) {
            btnProximo.addEventListener('click', (e) => {
                //this.log('[CLICK] btn-proximo');
                this.nextStep();
                e.stopImmediatePropagation();
                e.preventDefault();
            }, true);
            btnProximo.dataset.bound = 'true';
            //this.log('[BIND] bound btn-proximo (capture)');
        }
        if (btnAnterior && !btnAnterior.dataset.bound) {
            btnAnterior.addEventListener('click', (e) => {
                //this.log('[CLICK] btn-anterior');
                this.previousStep();
                e.stopImmediatePropagation();
                e.preventDefault();
            }, true);
            btnAnterior.dataset.bound = 'true';
            //this.log('[BIND] bound btn-anterior (capture)');
            //console.log('bound btn-anterior (capture)');
        }

        // Submit do formulário
        const form = document.getElementById('form-criar-evento');
        if (form && !form.dataset.bound) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
            form.dataset.bound = 'true';
            //this.log('[BIND] bound form submit');
            //console.log('bound form submit');
        }

        // Validação em tempo real
        this.setupValidation();
    }

    setMinDate() {
        const today = new Date().toISOString().split('T')[0];

        // Verificar se os elementos existem antes de acessá-los
        const dataInicio = document.getElementById('data_inicio');
        const dataFim = document.getElementById('data_fim');
        const dataRealizacao = document.getElementById('data_realizacao');

        if (dataInicio) {
            dataInicio.setAttribute('min', today);
        } else {
            console.warn('[setMinDate] Elemento data_inicio não encontrado');
        }

        if (dataFim) {
            dataFim.setAttribute('min', today);
        } else {
            console.warn('[setMinDate] Elemento data_fim não encontrado');
        }

        if (dataRealizacao) {
            dataRealizacao.setAttribute('min', today);
        } else {
            console.warn('[setMinDate] Elemento data_realizacao não encontrado');
        }
    }

    setupValidation() {
        // Validação da data de fim
        const dataInicioElement = document.getElementById('data_inicio');
        if (dataInicioElement) {
            dataInicioElement.addEventListener('change', (e) => {
                const dataInicio = e.target.value;
                const dataFim = document.getElementById('data_fim');
                if (dataInicio && dataFim) {
                    dataFim.setAttribute('min', dataInicio);
                }
            });
        }

        // Validação do CEP
        const cepElement = document.getElementById('cep');
        if (cepElement) {
            cepElement.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            });
        }

        // Preview da imagem do evento
        const inputImagem = document.getElementById('imagem');
        const preview = document.getElementById('preview-imagem-evento');
        if (inputImagem && preview && !inputImagem.dataset.previewBound) {
            inputImagem.addEventListener('change', (e) => {
                const file = e.target.files && e.target.files[0];
                if (!file) {
                    preview.innerHTML = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (ev) => {
                    preview.innerHTML = `<img src="${ev.target.result}" alt="Preview" class="w-full h-full object-cover">`;
                };
                reader.readAsDataURL(file);
            });
            inputImagem.dataset.previewBound = 'true';
        }
    }

    nextStep() {
        const now = Date.now();
        const delta = now - this.nextStepLastTs;
        //this.log('[STEP] nextStep called', { currentStep: this.currentStep, deltaMs: delta });
        //console.log('nextStep called', { currentStep: this.currentStep, deltaMs: delta });
        if (delta < 200) {
            this.log('[GUARD] Ignorando chamada duplicada do nextStep (delta < 200ms)');
            return;
        }
        this.nextStepLastTs = now;

        if (this.validateCurrentStep()) {
            this.saveCurrentStepData();

            if (this.currentStep < this.totalSteps) {
                this.hideCurrentStep();
                const from = this.currentStep;
                this.currentStep++;
                const to = this.currentStep;
                //this.log('[STEP] Avançando etapa', { from, to });
                //console.log('Avançando etapa', { from, to });
                this.showCurrentStep();
                this.updateProgress();
                this.updateButtons();
                this.reportState('after-next');

                // Se for a última etapa, mostrar resumo
                if (this.currentStep === this.totalSteps) {
                    this.generateResumo();
                }
            }
        }
    }

    previousStep() {
        //this.log('[STEP] previousStep called', { currentStep: this.currentStep });
        //console.log('previousStep called', { currentStep: this.currentStep });
        if (this.currentStep > 1) {
            this.hideCurrentStep();
            const from = this.currentStep;
            this.currentStep--;
            const to = this.currentStep;
            this.log('[STEP] Retornando etapa', {
                from,
                to
            });
            this.showCurrentStep();
            this.updateProgress();
            this.updateButtons();
            this.reportState('after-previous');
        }
    }

    hideCurrentStep() {
        const el = document.getElementById(`step-${this.currentStep}`);
        //this.log('[UI] hideCurrentStep', { step: this.currentStep, exists: !!el });
        //console.log('hideCurrentStep', { step: this.currentStep, exists: !!el });
        if (el) el.classList.add('hidden');
    }

    showCurrentStep() {
        const el = document.getElementById(`step-${this.currentStep}`);
        //this.log('[UI] showCurrentStep', { step: this.currentStep, exists: !!el });
        //console.log('showCurrentStep', { step: this.currentStep, exists: !!el });
        if (el) el.classList.remove('hidden');
    }

    updateProgress() {
        // Atualizar indicadores de progresso
        for (let i = 1; i <= this.totalSteps; i++) {
            const indicator = document.getElementById(`step-${i}-indicator`);
            const span = indicator.nextElementSibling;

            if (i < this.currentStep) {
                // Etapas concluídas
                indicator.className = 'w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-medium';
                span.className = 'ml-2 text-sm font-medium text-green-600';
            } else if (i === this.currentStep) {
                // Etapa atual
                indicator.className = 'w-8 h-8 bg-primary-600 text-white rounded-full flex items-center justify-center text-sm font-medium';
                span.className = 'ml-2 text-sm font-medium text-gray-700';
            } else {
                // Etapas futuras
                indicator.className = 'w-8 h-8 bg-gray-300 text-gray-500 rounded-full flex items-center justify-center text-sm font-medium';
                span.className = 'ml-2 text-sm font-medium text-gray-500';
            }
        }
    }

    updateButtons() {
        const btnAnterior = document.getElementById('btn-anterior');
        const btnProximo = document.getElementById('btn-proximo');
        const btnCriar = document.getElementById('btn-criar');

        // Botão anterior
        if (this.currentStep === 1) {
            btnAnterior.classList.add('hidden');
        } else {
            btnAnterior.classList.remove('hidden');
        }

        // Botões próximo/criar
        if (this.currentStep === this.totalSteps) {
            btnProximo.classList.add('hidden');
            btnCriar.classList.remove('hidden');
        } else {
            btnProximo.classList.remove('hidden');
            btnCriar.classList.add('hidden');
        }
        //this.log('[UI] updateButtons', { currentStep: this.currentStep });
        //console.log('updateButtons', { currentStep: this.currentStep });
    }

    validateCurrentStep() {
        //this.log('[VALIDATE] validateCurrentStep', { currentStep: this.currentStep });
        //console.log('validateCurrentStep', { currentStep: this.currentStep });
        const currentStepElement = document.getElementById(`step-${this.currentStep}`);
        const requiredFields = currentStepElement.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'Este campo é obrigatório');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });

        // Validações específicas por etapa
        if (this.currentStep === 1) {
            isValid = this.validateStep1() && isValid;
        } else if (this.currentStep === 2) {
            isValid = this.validateStep2() && isValid;
        }

        // this.log('[VALIDATE] result', { isValid });
        return isValid;
    }

    validateStep1() {
        let isValid = true;

        // Validar data de início
        const dataInicio = document.getElementById('data_inicio').value;
        const hoje = new Date().toISOString().split('T')[0];

        if (dataInicio < hoje) {
            this.showFieldError(document.getElementById('data_inicio'), 'A data não pode ser anterior a hoje');
            isValid = false;
        }

        // Validar data de fim
        const dataFim = document.getElementById('data_fim').value;
        if (dataFim && dataFim < dataInicio) {
            this.showFieldError(document.getElementById('data_fim'), 'A data de fim não pode ser anterior à data de início');
            isValid = false;
        }

        return isValid;
    }

    validateStep2() {
        let isValid = true;

        // Validar CEP se preenchido
        const cep = document.getElementById('cep').value;
        if (cep && !/^\d{5}-\d{3}$/.test(cep)) {
            this.showFieldError(document.getElementById('cep'), 'CEP deve estar no formato 00000-000');
            isValid = false;
        }

        return isValid;
    }

    showFieldError(field, message) {
        this.clearFieldError(field);

        field.classList.add('border-red-500');
        field.classList.remove('border-gray-300');

        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-500 text-sm mt-1 field-error';
        errorDiv.textContent = message;

        field.parentNode.appendChild(errorDiv);
    }

    clearFieldError(field) {
        field.classList.remove('border-red-500');
        field.classList.add('border-gray-300');

        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    saveCurrentStepData() {
        const currentStepElement = document.getElementById(`step-${this.currentStep}`);
        const inputs = currentStepElement.querySelectorAll('input, textarea, select');

        inputs.forEach(input => {
            if (input.type === 'checkbox') {
                this.formData[input.name] = input.checked;
            } else if (input.type === 'file') {
                // Arquivos serão tratados no submit
                if (input.files.length > 0) {
                    this.formData[input.name] = input.files[0];
                }
            } else {
                this.formData[input.name] = input.value;
            }
        });
        //this.log('[DATA] saveCurrentStepData', { step: this.currentStep, keys: Object.keys(this.formData) });
        //console.log('saveCurrentStepData', { step: this.currentStep, keys: Object.keys(this.formData) });
    }

    generateResumo() {
        const resumoContainer = document.getElementById('resumo-evento');

        const resumo = `
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-900">Informações Básicas</h4>
                        <p><strong>Nome:</strong> ${this.formData.nome || 'Não informado'}</p>
                        <p><strong>Descrição:</strong> ${this.formData.descricao || 'Não informado'}</p>
                        <p><strong>Data de Início:</strong> ${this.formatDate(this.formData.data_inicio)}</p>
                        <p><strong>Data de Fim:</strong> ${this.formatDate(this.formData.data_fim) || 'Não informado'}</p>
                        <p><strong>Hora de Início:</strong> ${this.formData.hora_inicio || 'Não informado'}</p>
                        <p><strong>Categoria:</strong> ${this.getCategoriaName(this.formData.categoria)}</p>
                        <p><strong>Gênero:</strong> ${this.formData.genero || 'Não informado'}</p>
                        <p><strong>Status:</strong> ${this.getStatusName(this.formData.status)}</p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900">Localização</h4>
                        <p><strong>Local:</strong> ${this.formData.local || 'Não informado'}</p>
                        <p><strong>Endereço:</strong> ${this.formatEndereco()}</p>
                        <p><strong>Cidade:</strong> ${this.formData.cidade || 'Não informado'}</p>
                        <p><strong>Estado:</strong> ${this.formData.estado || 'Não informado'}</p>
                        <p><strong>CEP:</strong> ${this.formData.cep || 'Não informado'}</p>
                        <p><strong>País:</strong> ${this.formData.pais || 'Brasil'}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-900">Configurações</h4>
                        <p><strong>Limite de Vagas:</strong> ${this.formData.limite_vagas || 'Ilimitado'}</p>
                        <p><strong>Data Fim Inscrições:</strong> ${this.formatDate(this.formData.data_fim_inscricoes) || 'Não definido'}</p>
                        <p><strong>Hora Fim Inscrições:</strong> ${this.formData.hora_fim_inscricoes || 'Não definido'}</p>
                        <p><strong>Taxa Setup:</strong> ${this.formatCurrency(this.formData.taxa_setup) || 'Não definido'}</p>
                        <p><strong>Percentual Repasse:</strong> ${this.formData.percentual_repasse ? this.formData.percentual_repasse + '%' : 'Não definido'}</p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900">Taxas</h4>
                        <p><strong>Taxa Gratuitas:</strong> ${this.formatCurrency(this.formData.taxa_gratuitas) || 'Não definido'}</p>
                        <p><strong>Taxa Pagas:</strong> ${this.formatCurrency(this.formData.taxa_pagas) || 'Não definido'}</p>
                        <p><strong>Retirada de Kits:</strong> ${this.formData.exibir_retirada_kit ? 'Sim' : 'Não'}</p>
                    </div>
                </div>
                
                ${this.formData.regulamento ? `
                    <div>
                        <h4 class="font-semibold text-gray-900">Regulamento</h4>
                        <div class="bg-gray-50 p-4 rounded-lg max-h-40 overflow-y-auto">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">${this.formData.regulamento}</p>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;

        resumoContainer.innerHTML = resumo;
    }

    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR');
    }

    formatCurrency(value) {
        if (!value) return '';
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    }

    formatEndereco() {
        const parts = [
            this.formData.logradouro,
            this.formData.numero
        ].filter(Boolean);

        return parts.length > 0 ? parts.join(', ') : 'Não informado';
    }

    getCategoriaName(categoria) {
        const categorias = {
            'corrida_rua': 'Corrida de Rua',
            'caminhada': 'Caminhada',
            'triatlo': 'Triatlo',
            'ciclismo': 'Ciclismo',
            'natacao': 'Natação',
            'outros': 'Outros'
        };
        return categorias[categoria] || categoria;
    }

    getStatusName(status) {
        const statuses = {
            'rascunho': 'Rascunho',
            'ativo': 'Ativo',
            'pausado': 'Pausado'
        };
        return statuses[status] || status;
    }

    async handleSubmit(e) {
        e.preventDefault();
        this.log('[SUBMIT] Iniciando submit');

        // Salvar dados da última etapa
        this.saveCurrentStepData();

        // Mostrar loading
        const btnCriar = document.getElementById('btn-criar');
        const originalText = btnCriar.innerHTML;
        btnCriar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Criando...';
        btnCriar.disabled = true;

        try {
            // Preparar FormData para envio
            const formData = new FormData();

            // Adicionar todos os dados do formulário
            Object.keys(this.formData).forEach(key => {
                if (this.formData[key] instanceof File) {
                    formData.append(key, this.formData[key]);
                } else {
                    formData.append(key, this.formData[key]);
                }
            });

            // Fazer requisição para criar evento (caminho relativo ao index.php do painel)
            const response = await fetch('../../../api/evento/create.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            this.log('[SUBMIT] Resposta API', result);

            if (result.success) {
                // Sucesso
                Swal.fire({
                    icon: 'success',
                    title: 'Evento Criado!',
                    text: result.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Redirecionar para a página de eventos
                    window.location.href = 'index.php';
                });
            } else {
                // Erro
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao Criar Evento',
                    text: result.message,
                    confirmButtonText: 'OK'
                });
            }

        } catch (error) {
            console.error('Erro:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro de Conexão',
                text: 'Ocorreu um erro ao comunicar com o servidor. Tente novamente.',
                confirmButtonText: 'OK'
            });
        } finally {
            // Restaurar botão
            btnCriar.innerHTML = originalText;
            btnCriar.disabled = false;
        }
    }
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded');
    if (window.__criarEventoManagerInitialized) {
        console.warn('[CriarEvento] Manager já inicializado. Evitando dupla vinculação de eventos.');
        return;
    }
    window.__criarEventoManagerInitialized = true;
    new CriarEventoManager();
});