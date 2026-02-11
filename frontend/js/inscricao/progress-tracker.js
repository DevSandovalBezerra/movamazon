export class ProgressTracker {
    constructor(containerId, etapas = []) {
        this.container = document.getElementById(containerId) || document.querySelector('.progress-container');
        this.etapas = etapas.length > 0 ? etapas : this.getDefaultEtapas();
        this.etapaAtual = 1;
        this.etapasCompletas = [];
        this.init();
    }

    getDefaultEtapas() {
        return [
            { id: 1, nome: 'Modalidade', descricao: 'Escolha sua modalidade', icon: 'fa-list' },
            { id: 2, nome: 'Termos', descricao: 'Termos e condições', icon: 'fa-file-contract' },
            { id: 3, nome: 'Cadastro', descricao: 'Preencha seus dados', icon: 'fa-user-edit' },
            { id: 4, nome: 'Resumo', descricao: 'Revise sua inscrição', icon: 'fa-clipboard-check' },
            { id: 5, nome: 'Pagamento', descricao: 'Finalize o pagamento', icon: 'fa-credit-card' }
        ];
    }

    init() {
        if (this.container) {
            this.injectStyles();
            this.render();
        }
    }

    injectStyles() {
        if (document.getElementById('progress-tracker-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'progress-tracker-styles';
        style.textContent = `
            .progress-tracker .step {
                transition: all 0.3s ease;
            }
            .progress-tracker .step-icon-wrapper {
                position: relative;
                margin-bottom: 8px;
            }
            .progress-tracker .step-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
            }
            .progress-tracker .step-check {
                position: absolute;
                top: -4px;
                right: -4px;
                width: 16px;
                height: 16px;
                background: #10b981;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 10px;
            }
            @media (max-width: 768px) {
                .progress-tracker .progress-steps {
                    grid-template-columns: repeat(5, 1fr);
                    gap: 4px;
                }
                .progress-tracker .step-label {
                    font-size: 10px;
                }
            }
        `;
        document.head.appendChild(style);
    }

    render() {
        const progress = this.calculateProgress();
        
        this.container.classList.add('progress-tracker');
        this.container.innerHTML = `
            <div class="progress-header mb-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">Progresso da Inscrição</h3>
                    <span class="text-sm font-medium text-gray-600">${progress.percentage}% concluído</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full transition-all duration-500" 
                         style="width: ${progress.percentage}%"></div>
                </div>
            </div>
            <div class="progress-steps grid grid-cols-2 md:grid-cols-5 gap-2 md:gap-4">
                ${this.etapas.map(etapa => this.renderStep(etapa)).join('')}
            </div>
            ${this.renderTimeEstimate()}
        `;
    }

    renderStep(etapa) {
        const status = this.getStepStatus(etapa.id);
        const isCurrent = etapa.id === this.etapaAtual;
        const isComplete = this.etapasCompletas.includes(etapa.id);
        
        const statusClasses = {
            completed: 'bg-green-500 text-white',
            current: 'bg-blue-500 text-white ring-4 ring-blue-200',
            pending: 'bg-gray-200 text-gray-600'
        };
        
        return `
            <div class="step flex flex-col items-center text-center ${status}" 
                 data-step="${etapa.id}">
                <div class="relative mb-2">
                    <div class="step-number w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 ${statusClasses[status]}">
                        ${isComplete ? '<i class="fas fa-check"></i>' : etapa.id}
                    </div>
                    ${isCurrent ? '<div class="absolute -top-1 -right-1 w-4 h-4 bg-blue-500 rounded-full animate-pulse"></div>' : ''}
                </div>
                <div class="step-label text-xs font-semibold ${isCurrent ? 'text-blue-600' : isComplete ? 'text-green-600' : 'text-gray-500'}">
                    ${etapa.nome}
                </div>
                <div class="step-description text-xs text-gray-400 mt-1 hidden md:block">
                    ${etapa.descricao}
                </div>
            </div>
        `;
    }

    renderTimeEstimate() {
        const remainingSteps = this.etapas.length - this.etapasCompletas.length;
        const estimatedMinutes = remainingSteps * 2;
        
        return `
            <div class="time-estimate mt-4 p-3 bg-blue-50 rounded-lg">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-clock text-blue-600"></i>
                    <span class="text-sm text-blue-800">
                        Tempo estimado restante: <strong>${estimatedMinutes} minutos</strong>
                    </span>
                </div>
            </div>
        `;
    }

    getStepStatus(etapaId) {
        if (this.etapasCompletas.includes(etapaId)) {
            return 'completed';
        } else if (etapaId === this.etapaAtual) {
            return 'current';
        } else if (etapaId < this.etapaAtual) {
            return 'completed';
        } else {
            return 'pending';
        }
    }

    calculateProgress() {
        const total = this.etapas.length;
        const completed = this.etapasCompletas.length;
        const current = this.etapaAtual;
        
        let progress = 0;
        if (completed > 0) {
            progress = (completed / total) * 100;
        } else if (current > 1) {
            progress = ((current - 1) / total) * 100;
        }
        
        return {
            percentage: Math.round(progress),
            completed,
            total,
            current
        };
    }

    setEtapaAtual(etapa) {
        this.etapaAtual = etapa;
        this.render();
    }

    marcarComoCompleta(etapa) {
        if (!this.etapasCompletas.includes(etapa)) {
            this.etapasCompletas.push(etapa);
            this.render();
        }
    }

    desmarcarCompleta(etapa) {
        this.etapasCompletas = this.etapasCompletas.filter(e => e !== etapa);
        this.render();
    }

    reset() {
        this.etapaAtual = 1;
        this.etapasCompletas = [];
        this.render();
    }

    getProgress() {
        return this.calculateProgress();
    }

    getEtapaAtual() {
        return this.etapaAtual;
    }

    getEtapasCompletas() {
        return [...this.etapasCompletas];
    }
}

let globalProgressTracker = null;

export function initProgressTracker(containerId, etapas) {
    globalProgressTracker = new ProgressTracker(containerId, etapas);
    window.progressTracker = globalProgressTracker;
    return globalProgressTracker;
}

export function getProgressTracker() {
    return globalProgressTracker;
}
