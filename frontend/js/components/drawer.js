/**
 * Componente: Drawer Lateral
 * Menu lateral para mobile com animações suaves
 */

/**
 * Cria e inicializa um drawer lateral
 * @param {string} drawerId - ID do elemento drawer
 * @param {string} triggerId - ID do botão que abre o drawer
 * @param {string} closeId - ID do botão que fecha o drawer
 * @param {string} overlayId - ID do overlay de fundo
 */
export function inicializarDrawer(drawerId, triggerId, closeId, overlayId) {
    const drawer = document.getElementById(drawerId);
    const trigger = document.getElementById(triggerId);
    const closeBtn = document.getElementById(closeId);
    const overlay = document.getElementById(overlayId);

    if (!drawer || !trigger) return;

    // Abrir drawer
    trigger.addEventListener('click', () => {
        drawer.classList.remove('translate-x-full');
        drawer.classList.add('translate-x-0');
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.classList.add('block');
        }
        document.body.style.overflow = 'hidden'; // Prevenir scroll
    });

    // Fechar drawer
    const fecharDrawer = () => {
        drawer.classList.remove('translate-x-0');
        drawer.classList.add('translate-x-full');
        if (overlay) {
            overlay.classList.add('hidden');
            overlay.classList.remove('block');
        }
        document.body.style.overflow = ''; // Restaurar scroll
    };

    if (closeBtn) {
        closeBtn.addEventListener('click', fecharDrawer);
    }

    if (overlay) {
        overlay.addEventListener('click', fecharDrawer);
    }

    // Fechar com ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !drawer.classList.contains('translate-x-full')) {
            fecharDrawer();
        }
    });
}


