import { initStateManager } from './state-manager.js';
import { initValidator } from './validation.js';
import { AutoSave } from './auto-save.js';
import { initProgressTracker } from './progress-tracker.js';

export function initInscricaoModules(eventoId) {
    console.log('🚀 Inicializando módulos de inscrição para evento:', eventoId);
    
    const stateManager = initStateManager(eventoId);
    const validator = initValidator();
    const autoSave = new AutoSave(stateManager, {
        interval: 30000,
        debounceTime: 2000
    });
    
    const etapas = [
        { id: 1, nome: 'Modalidade', descricao: 'Escolha sua modalidade', icon: 'fa-list' },
        { id: 2, nome: 'Termos', descricao: 'Termos e condições', icon: 'fa-file-contract' },
        { id: 3, nome: 'Cadastro', descricao: 'Preencha seus dados', icon: 'fa-user-edit' },
        { id: 4, nome: 'Resumo', descricao: 'Revise sua inscrição', icon: 'fa-clipboard-check' },
        { id: 5, nome: 'Pagamento', descricao: 'Finalize o pagamento', icon: 'fa-credit-card' }
    ];
    
    const progressTracker = initProgressTracker('progress-container', etapas);
    
    const urlParams = new URLSearchParams(window.location.search);
    const etapaAtual = parseInt(urlParams.get('etapa')) || 1;
    progressTracker.setEtapaAtual(etapaAtual);
    
    window.inscricaoModules = {
        stateManager,
        validator,
        autoSave,
        progressTracker
    };
    
    console.log('✅ Módulos de inscrição inicializados');
    
    return {
        stateManager,
        validator,
        autoSave,
        progressTracker
    };
}

if (typeof window !== 'undefined' && window.eventoId) {
    document.addEventListener('DOMContentLoaded', () => {
        initInscricaoModules(window.eventoId);
    });
}
