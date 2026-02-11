/**
 * Utilitários para componentes shadcn/ui adaptados
 * Baseado em: https://ui.shadcn.com/docs
 */

/**
 * Merge classes CSS (similar ao cn() do shadcn/ui)
 * @param {...string} classes - Classes CSS para combinar
 * @returns {string} Classes combinadas
 */
export function cn(...classes) {
    return classes.filter(Boolean).join(' ');
}

/**
 * Toggle de dialog/modal
 * @param {string} id - ID do dialog
 * @param {boolean} show - Mostrar ou esconder
 */
export function toggleDialog(id, show) {
    const dialog = document.getElementById(id);
    if (!dialog) return;
    
    if (show) {
        dialog.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        dialog.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

/**
 * Abrir dialog
 */
export function openDialog(id) {
    toggleDialog(id, true);
}

/**
 * Fechar dialog
 */
export function closeDialog(id) {
    toggleDialog(id, false);
}

/**
 * Inicializar event listeners para dialogs
 */
export function initDialogs() {
    document.addEventListener('click', (e) => {
        // Fechar ao clicar no overlay
        if (e.target.hasAttribute('data-dialog-overlay')) {
            const id = e.target.getAttribute('data-dialog-overlay');
            closeDialog(id);
        }
        
        // Fechar ao clicar no botão de fechar
        if (e.target.hasAttribute('data-dialog-close') || 
            e.target.closest('[data-dialog-close]')) {
            const btn = e.target.hasAttribute('data-dialog-close') 
                ? e.target 
                : e.target.closest('[data-dialog-close]');
            const id = btn.getAttribute('data-dialog-close');
            closeDialog(id);
        }
    });
    
    // Fechar com ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const openDialogs = document.querySelectorAll('[id^="dialog-"]:not(.hidden)');
            openDialogs.forEach(dialog => {
                closeDialog(dialog.id);
            });
        }
    });
}

// Auto-inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDialogs);
} else {
    initDialogs();
}

