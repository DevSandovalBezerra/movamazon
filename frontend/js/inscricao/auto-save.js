export class AutoSave {
    constructor(stateManager, options = {}) {
        if (!stateManager) {
            throw new Error('StateManager é obrigatório para AutoSave');
        }
        this.stateManager = stateManager;
        this.interval = options.interval || 30000;
        this.debounceTime = options.debounceTime || 2000;
        this.enabled = true;
        this.timers = new Map();
        this.saveIndicator = null;
        this.init();
    }

    init() {
        this.createSaveIndicator();
        this.setupFormWatchers();
        this.startPeriodicSave();
    }

    createSaveIndicator() {
        this.saveIndicator = document.createElement('div');
        this.saveIndicator.id = 'auto-save-indicator';
        this.saveIndicator.className = 'auto-save-indicator fixed bottom-4 right-4 z-50 transition-all duration-300';
        this.saveIndicator.innerHTML = `
            <div class="bg-white rounded-lg shadow-lg p-3 flex items-center space-x-2">
                <div class="save-icon">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <span class="save-text text-sm text-gray-700">Salvando...</span>
            </div>
        `;
        this.saveIndicator.style.display = 'none';
        document.body.appendChild(this.saveIndicator);
    }

    setupFormWatchers() {
        const forms = document.querySelectorAll('form[data-auto-save]');
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    this.debouncedSave(input);
                });
                input.addEventListener('change', () => {
                    this.debouncedSave(input);
                });
            });
        });
    }

    debouncedSave(element) {
        if (!this.enabled) return;

        const fieldName = element.name || element.id;
        if (!fieldName) return;

        if (this.timers.has(fieldName)) {
            clearTimeout(this.timers.get(fieldName));
        }

        const timer = setTimeout(() => {
            this.saveField(fieldName, element);
            this.timers.delete(fieldName);
        }, this.debounceTime);

        this.timers.set(fieldName, timer);
    }

    async saveField(fieldName, element) {
        const value = this.getFieldValue(element);
        
        if (fieldName.startsWith('questionario[')) {
            const match = fieldName.match(/\[(\d+)\]/);
            if (match) {
                const questionarioId = match[1];
                const ficha = this.stateManager.getFicha();
                ficha.respostas_questionario = ficha.respostas_questionario || {};
                ficha.respostas_questionario[questionarioId] = value;
                this.stateManager.setFicha(ficha);
            }
        } else if (fieldName === 'tamanho_camiseta') {
            this.stateManager.setFicha({ tamanho_camiseta: value });
        } else {
            const ficha = this.stateManager.getFicha();
            ficha[fieldName] = value;
            this.stateManager.setFicha(ficha);
        }

        await this.save();
    }

    getFieldValue(element) {
        if (element.type === 'checkbox') {
            return element.checked;
        } else if (element.type === 'radio') {
            const checked = document.querySelector(`input[name="${element.name}"]:checked`);
            return checked ? checked.value : null;
        } else if (element.tagName === 'SELECT') {
            return element.value;
        } else {
            return element.value;
        }
    }

    async save() {
        if (!this.enabled) return;

        this.showSaving();
        
        try {
            await this.stateManager.saveToSession();
            this.showSaved();
        } catch (error) {
            console.error('Erro ao salvar automaticamente:', error);
            this.showError();
        }
    }

    startPeriodicSave() {
        if (this.periodicTimer) {
            clearInterval(this.periodicTimer);
        }

        this.periodicTimer = setInterval(() => {
            if (this.enabled) {
                this.save();
            }
        }, this.interval);
    }

    stopPeriodicSave() {
        if (this.periodicTimer) {
            clearInterval(this.periodicTimer);
            this.periodicTimer = null;
        }
    }

    showSaving() {
        if (!this.saveIndicator) return;
        
        this.saveIndicator.style.display = 'block';
        this.saveIndicator.querySelector('.save-icon').innerHTML = `
            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
        `;
        this.saveIndicator.querySelector('.save-text').textContent = 'Salvando...';
        this.saveIndicator.querySelector('.bg-white').classList.remove('bg-green-50', 'bg-red-50');
        this.saveIndicator.querySelector('.bg-white').classList.add('bg-blue-50');
    }

    showSaved() {
        if (!this.saveIndicator) return;
        
        this.saveIndicator.querySelector('.save-icon').innerHTML = `
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        `;
        this.saveIndicator.querySelector('.save-text').textContent = 'Salvo automaticamente';
        this.saveIndicator.querySelector('.bg-white').classList.remove('bg-blue-50', 'bg-red-50');
        this.saveIndicator.querySelector('.bg-white').classList.add('bg-green-50');

        setTimeout(() => {
            this.hideIndicator();
        }, 2000);
    }

    showError() {
        if (!this.saveIndicator) return;
        
        this.saveIndicator.querySelector('.save-icon').innerHTML = `
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        `;
        this.saveIndicator.querySelector('.save-text').textContent = 'Erro ao salvar';
        this.saveIndicator.querySelector('.bg-white').classList.remove('bg-blue-50', 'bg-green-50');
        this.saveIndicator.querySelector('.bg-white').classList.add('bg-red-50');

        setTimeout(() => {
            this.hideIndicator();
        }, 3000);
    }

    hideIndicator() {
        if (this.saveIndicator) {
            this.saveIndicator.style.display = 'none';
        }
    }

    enable() {
        this.enabled = true;
        this.startPeriodicSave();
    }

    disable() {
        this.enabled = false;
        this.stopPeriodicSave();
    }

    destroy() {
        this.stopPeriodicSave();
        this.timers.forEach(timer => clearTimeout(timer));
        this.timers.clear();
        if (this.saveIndicator) {
            this.saveIndicator.remove();
        }
    }
}

let globalAutoSave = null;

export function initAutoSave(stateManager, options) {
    globalAutoSave = new AutoSave(stateManager, options);
    window.autoSave = globalAutoSave;
    return globalAutoSave;
}

export function getAutoSave() {
    return globalAutoSave;
}
