<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$admin_name = $_SESSION['user_name'] ?? 'Administrador';
$admin_email = $_SESSION['user_email'] ?? '';
$admin_initial = strtoupper(substr($admin_name, 0, 1));
$currentPageTitle = $currentPageTitle ?? 'Painel Administrativo';
?>

<header class="admin-topbar bg-white border-b border-gray-200 shadow-sm sticky top-0 z-30">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <button id="sidebar-toggle" class="lg:hidden h-11 w-11 rounded-xl bg-[#e2f3f0] text-[#0b4340] flex items-center justify-center shadow-sm">
                <i class="fas fa-bars text-lg"></i>
            </button>
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-400 font-semibold">MovAmazon Admin</p>
                <h1 class="text-2xl font-semibold text-gray-900 mt-1">
                    <?php echo htmlspecialchars($currentPageTitle); ?>
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <a href="../../paginas/public/index.php" target="_blank" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:text-[#0b4340] hover:border-[#0b4340] transition-colors">
                <i class="fas fa-external-link-alt text-xs"></i>
                Ver site
            </a>
            <div class="flex items-center gap-3 bg-white px-3 py-2 rounded-xl border border-gray-100 shadow-sm">
                <div class="hidden sm:block">
                    <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($admin_name); ?></p>
                    <span class="text-xs text-gray-500"><?php echo htmlspecialchars($admin_email); ?></span>
                </div>
                <div class="h-10 w-10 rounded-xl bg-[#0b4340] text-white font-bold flex items-center justify-center shadow-md">
                    <?php echo $admin_initial; ?>
                </div>
            </div>
        </div>
    </div>
</header>

