<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$admin_name = $_SESSION['user_name'] ?? 'Administrador';
$admin_email = $_SESSION['user_email'] ?? '';
$activePage = $activePage ?? 'dashboard';

$menuSections = [
    'Principal' => [
        ['slug' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-gauge']
    ],
    'Gestão' => [
        ['slug' => 'organizadores', 'label' => 'Organizadores', 'icon' => 'fas fa-users-cog'],
        ['slug' => 'inscricoes', 'label' => 'Inscrições', 'icon' => 'fas fa-clipboard-list'],
        ['slug' => 'termos-inscricao', 'label' => 'Termos de Inscrição', 'icon' => 'fas fa-file-contract'],
        ['slug' => 'solicitacoes', 'label' =>'Solicitações', 'icon' => 'fas fa-clipboard-check'],
        ['slug' => 'banners', 'label' =>'Banners', 'icon' => 'fas fa-images'],
        ['slug' => 'pagamentos-pendentes', 'label' => 'Pagamentos Pendentes', 'icon' => 'fas fa-clock'],
        ['slug' => 'cancelamentos', 'label' => 'Cancelamentos', 'icon' => 'fas fa-ban']
    ],
    'Sistema' => [
        ['slug' => 'configuracoes', 'label' =>'Configurações', 'icon' => 'fas fa-sliders-h'],
        ['slug' => 'ia', 'label' =>'Inteligência Artificial', 'icon' => 'fas fa-robot'],
        ['slug' => 'problemas-inscricoes', 'label' => 'Problemas com Inscrições', 'icon' => 'fas fa-exclamation-triangle']
    ]
];
?>

<!-- Overlay para mobile -->
<div id="sidebar-overlay" class="admin-sidebar__overlay lg:hidden hidden"></div>

<!-- Sidebar -->
<aside id="admin-sidebar" class="admin-sidebar">
    <button id="sidebar-close" class="admin-sidebar__close lg:hidden" aria-label="Fechar menu">
        <i class="fas fa-times"></i>
    </button>

    <div class="admin-sidebar__brand">
        <div class="admin-sidebar__logo">
            <img src="../../assets/img/logo.png" alt="MovAmazon" class="admin-sidebar__logo-img" loading="lazy">
        </div>
        <div>
            <p class="admin-sidebar__logo-title">MovAmazon</p>
            <span class="admin-sidebar__logo-subtitle">Admin Panel</span>
        </div>
    </div>

    <div class="admin-sidebar__profile">
        <p class="admin-sidebar__profile-name"><?php echo htmlspecialchars($admin_name); ?></p>
        <span class="admin-sidebar__profile-email"><?php echo htmlspecialchars($admin_email); ?></span>
    </div>

    <nav class="admin-sidebar__nav">
        <?php foreach ($menuSections as $section => $items): ?>
            <div class="admin-sidebar__section">
                <span class="admin-sidebar__section-title"><?php echo $section; ?></span>
                <ul class="admin-sidebar__menu">
                    <?php foreach ($items as $item):
                        $isActive = $activePage === $item['slug'];
                    ?>
                        <li>
                            <a href="?page=<?php echo $item['slug']; ?>"
                               class="admin-sidebar__link <?php echo $isActive ? 'is-active' : ''; ?>">
                                <span class="admin-sidebar__icon">
                                    <i class="<?php echo $item['icon']; ?>"></i>
                                </span>
                                <span><?php echo $item['label']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </nav>

    <div class="admin-sidebar__footer">
        <a href="../auth/logout.php" class="admin-sidebar__logout">
            <i class="fas fa-sign-out-alt"></i>
            Sair
        </a>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('admin-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const overlay = document.getElementById('sidebar-overlay');
    const closeBtn = document.getElementById('sidebar-close');

    if (!sidebar || !toggleBtn) return;

    if (window.innerWidth >= 1024) {
        sidebar.classList.add('is-open');
    }

    const closeSidebar = () => {
        sidebar.classList.remove('is-open');
        if (overlay) overlay.classList.add('hidden');
        document.body.style.overflow = '';
    };

    const openSidebar = () => {
        sidebar.classList.add('is-open');
        if (overlay) overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    toggleBtn.addEventListener('click', () => {
        if (!sidebar.classList.contains('is-open')) {
            openSidebar();
        } else {
            closeSidebar();
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            sidebar.classList.add('is-open');
            if (overlay) overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });
});
</script>

