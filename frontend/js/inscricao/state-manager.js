export class InscricaoStateManager {
    constructor(eventoId) {
        this.eventoId = eventoId;
        this.storageKey = `inscricao_${eventoId}`;
        this.state = this.loadState();
        this.listeners = [];
        this.autoSaveInterval = null;
        this.init();
    }

    init() {
        this.syncWithSession();
        this.startAutoSync();
    }

    loadState() {
        try {
            const saved = localStorage.getItem(this.storageKey);
            if (saved) {
                const parsed = JSON.parse(saved);
                if (parsed.evento_id === this.eventoId) {
                    return parsed;
                }
            }
        } catch (error) {
            console.error('Erro ao carregar estado:', error);
        }
        
        return {
            evento_id: this.eventoId,
            etapa_atual: 1,
            modalidades_selecionadas: [],
            ficha: {
                tamanho_camiseta: null,
                produtos_extras: [],
                respostas_questionario: {}
            },
            cupom_aplicado: null,
            valor_desconto: 0,
            timestamp: Date.now()
        };
    }

    saveState() {
        try {
            this.state.timestamp = Date.now();
            localStorage.setItem(this.storageKey, JSON.stringify(this.state));
            this.notifyListeners('stateSaved', this.state);
            return true;
        } catch (error) {
            console.error('Erro ao salvar estado:', error);
            return false;
        }
    }

    async syncWithSession() {
        try {
            const apiBase = window.API_BASE || '';
            const url = `${apiBase}/api/inscricao/get_session.php?evento_id=${this.eventoId}`;
            const response = await fetch(url);
            if (response.ok) {
                const sessionData = await response.json();
                if (sessionData.success && sessionData.data) {
                    this.mergeState(sessionData.data);
                }
            } else if (response.status !== 404) {
                console.warn('Erro ao sincronizar com sessão:', response.status);
            }
        } catch (error) {
            // Ignorar erros de rede silenciosamente (API pode não existir)
            if (error.message && !error.message.includes('404')) {
                console.warn('Erro ao sincronizar com sessão:', error);
            }
        }
    }

    async saveToSession() {
        try {
            const apiBase = window.API_BASE || '';
            const url = `${apiBase}/api/inscricao/save_session.php`;
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.state)
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    this.notifyListeners('sessionSynced', this.state);
                    return true;
                }
            } else if (response.status !== 404) {
                console.warn('Erro ao salvar na sessão:', response.status);
            }
        } catch (error) {
            // Ignorar erros de rede silenciosamente (API pode não existir)
            if (error.message && !error.message.includes('404')) {
                console.warn('Erro ao salvar na sessão:', error);
            }
        }
        return false;
    }

    mergeState(newState) {
        this.state = {
            ...this.state,
            ...newState,
            modalidades_selecionadas: newState.modalidades_selecionadas || this.state.modalidades_selecionadas,
            ficha: {
                ...this.state.ficha,
                ...(newState.ficha || {})
            }
        };
        this.saveState();
    }

    getState() {
        return { ...this.state };
    }

    setModalidades(modalidades) {
        this.state.modalidades_selecionadas = modalidades;
        this.saveState();
        this.saveToSession();
    }

    setFicha(ficha) {
        this.state.ficha = {
            ...this.state.ficha,
            ...ficha
        };
        this.saveState();
        this.saveToSession();
    }

    setEtapa(etapa) {
        this.state.etapa_atual = etapa;
        this.saveState();
    }

    setCupom(cupom, desconto) {
        this.state.cupom_aplicado = cupom;
        this.state.valor_desconto = desconto;
        this.saveState();
        this.saveToSession();
    }

    getModalidades() {
        return this.state.modalidades_selecionadas || [];
    }

    getFicha() {
        return this.state.ficha || {};
    }

    getEtapaAtual() {
        return this.state.etapa_atual || 1;
    }

    startAutoSync() {
        if (this.autoSaveInterval) {
            clearInterval(this.autoSaveInterval);
        }
        
        this.autoSaveInterval = setInterval(() => {
            this.saveToSession();
        }, 30000);
    }

    stopAutoSync() {
        if (this.autoSaveInterval) {
            clearInterval(this.autoSaveInterval);
            this.autoSaveInterval = null;
        }
    }

    subscribe(callback) {
        this.listeners.push(callback);
        return () => {
            this.listeners = this.listeners.filter(l => l !== callback);
        };
    }

    notifyListeners(event, data) {
        this.listeners.forEach(listener => {
            try {
                listener(event, data);
            } catch (error) {
                console.error('Erro ao notificar listener:', error);
            }
        });
    }

    clear() {
        localStorage.removeItem(this.storageKey);
        this.state = this.loadState();
        this.notifyListeners('stateCleared', null);
    }

    exportState() {
        return JSON.stringify(this.state, null, 2);
    }

    importState(jsonString) {
        try {
            const imported = JSON.parse(jsonString);
            if (imported.evento_id === this.eventoId) {
                this.state = imported;
                this.saveState();
                this.saveToSession();
                this.notifyListeners('stateImported', this.state);
                return true;
            }
        } catch (error) {
            console.error('Erro ao importar estado:', error);
        }
        return false;
    }
}

let globalStateManager = null;

export function getStateManager(eventoId) {
    if (!globalStateManager || globalStateManager.eventoId !== eventoId) {
        globalStateManager = new InscricaoStateManager(eventoId);
    }
    return globalStateManager;
}

export function initStateManager(eventoId) {
    globalStateManager = new InscricaoStateManager(eventoId);
    window.inscricaoStateManager = globalStateManager;
    return globalStateManager;
}
