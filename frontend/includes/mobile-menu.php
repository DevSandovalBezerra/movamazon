<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina = $_GET['page'] ?? 'dashboard';
$menuItems = [
    'dashboard' => ['label' => 'Dashboard', 'icon' => 'fas fa-gauge'],
    'minhas-inscricoes' => ['label' => 'Inscrições', 'icon' => 'fas fa-ticket-alt'],
    'meu-perfil' => ['label' => 'Meu Perfil', 'icon' => 'fas fa-user'],
    'meus-treinos' => ['label' => 'Meus Treinos', 'icon' => 'fas fa-running'],
    'meu-cashback' => ['label' => 'Meu Cashback', 'icon' => 'fas fa-coins']
];
?>

<button id="mobile-menu-toggle"
        class="mobile-only touch-target fixed left-4 top-4 bg-brand-green text-white rounded-lg shadow-lg">
    <i class="fas fa-bars"></i>
</button>

<div id="mobile-menu-overlay" class="mobile-menu-overlay"></div>

<aside id="mobile-menu-panel" class="mobile-menu-panel">
    <div class="flex items-center justify-between p-4 border-b border-green-600">
        <div class="flex items-center gap-3">
            <img src="../../assets/img/logo.png" class="h-8 w-8" alt="Logo" loading="lazy">
            <span class="font-semibold">MovAmazon</span>
        </div>
        <button id="mobile-menu-close" class="touch-target text-white">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="p-3 space-y-2">
        <?php foreach ($menuItems as $key => $item): ?>
            <a href="?page=<?php echo $key; ?>"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?php echo $pagina === $key ? 'bg-brand-yellow text-brand-green font-semibold' : 'text-white hover:bg-green-700'; ?>">
                <i class="<?php echo $item['icon']; ?>"></i>
                <span><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

<script>
(() => {
    const toggle = document.getElementById('mobile-menu-toggle');
    const closeBtn = document.getElementById('mobile-menu-close');
    const overlay = document.getElementById('mobile-menu-overlay');
    const panel = document.getElementById('mobile-menu-panel');

    if (!toggle || !panel || !overlay) return;

    const openMenu = () => {
        panel.classList.add('is-open');
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    const closeMenu = () => {
        panel.classList.remove('is-open');
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    toggle.addEventListener('click', openMenu);
    if (closeBtn) closeBtn.addEventListener('click', closeMenu);
    overlay.addEventListener('click', closeMenu);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMenu();
    });
})();
</script>
