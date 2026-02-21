<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../api/assessoria/middleware.php';

requireAssessor();

$assessoria_id = getAssessoriaDoUsuario();
$page = $_GET['page'] ?? 'dashboard';

$allowedPages = [
    'dashboard',
    'atletas',
    'atleta-detalhe',
    'programas',
    'programa-detalhe',
    'planos',
    'monitoramento',
    'equipe',
    'configuracoes'
];

if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}

$pageFile = __DIR__ . '/pages/' . str_replace('-', '_', $page) . '.php';
if (!file_exists($pageFile)) {
    $pageFile = __DIR__ . '/pages/dashboard.php';
    $page = 'dashboard';
}

$userName = $_SESSION['user_name'] ?? 'Assessor';
$userPapel = $_SESSION['papel'] ?? 'assessor';
$assessoriaFuncao = $_SESSION['assessoria_funcao'] ?? 'assessor';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Assessoria - MovAmazon</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../assets/img/logo.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .sidebar-link.active {
            background-color: rgba(139, 92, 246, 0.1);
            color: #7C3AED;
            border-left: 3px solid #7C3AED;
        }
        .sidebar-link:hover:not(.active) {
            background-color: rgba(139, 92, 246, 0.05);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg hidden lg:block">
            <div class="p-5 border-b">
                <div class="flex items-center gap-2">
                    <img src="../../assets/img/logo.png" alt="Logo" class="h-8 w-8" onerror="this.style.display='none'">
                    <div>
                        <h2 class="text-lg font-bold text-purple-700">MovAmazon</h2>
                        <p class="text-xs text-gray-500">Assessoria</p>
                    </div>
                </div>
            </div>
            <nav class="p-4 space-y-1">
                <a href="?page=dashboard" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 <?= $page === 'dashboard' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
                <a href="?page=atletas" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 <?= $page === 'atletas' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Atletas
                </a>
                <a href="?page=programas" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 <?= in_array($page, ['programas', 'programa-detalhe']) ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Programas
                </a>
                <a href="?page=planos" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 <?= $page === 'planos' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Planos de Treino
                </a>
                <a href="?page=monitoramento" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 <?= $page === 'monitoramento' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Monitoramento
                </a>

                <?php if ($assessoriaFuncao === 'admin'): ?>
                <div class="border-t my-3"></div>
                <a href="?page=equipe" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 <?= $page === 'equipe' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Equipe
                </a>
                <a href="?page=configuracoes" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 <?= $page === 'configuracoes' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Configuracoes
                </a>
                <?php endif; ?>
            </nav>

            <!-- Logout -->
            <div class="absolute bottom-0 w-64 p-4 border-t">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <span class="text-purple-700 font-bold text-sm"><?= strtoupper(substr($userName, 0, 1)) ?></span>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($userName) ?></p>
                        <p class="text-xs text-gray-500"><?= ucfirst(str_replace('_', ' ', $assessoriaFuncao)) ?></p>
                    </div>
                </div>
                <a href="../../../api/auth/logout.php" 
                   class="flex items-center gap-2 text-sm text-red-600 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Sair
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col">
            <!-- Top bar mobile -->
            <header class="bg-white shadow-sm lg:hidden">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center gap-2">
                        <img src="../../assets/img/logo.png" alt="Logo" class="h-7 w-7" onerror="this.style.display='none'">
                        <span class="font-bold text-purple-700">Assessoria</span>
                    </div>
                    <button id="mobile-menu-btn" class="p-2 text-gray-600 hover:text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 lg:p-8">
                <?php include $pageFile; ?>
            </main>
        </div>
    </div>

    <script>
    const menuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.querySelector('aside');
    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('fixed');
            sidebar.classList.toggle('inset-0');
            sidebar.classList.toggle('z-50');
        });
    }
    </script>
</body>
</html>
