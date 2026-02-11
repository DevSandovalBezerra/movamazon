<?php
// ✅ CRÍTICO: Verificar se sessão já foi iniciada antes de tentar iniciar novamente
// Não usar @session_start() pois pode mascarar problemas reais
if (session_status() === PHP_SESSION_NONE) {
    // Se output buffering não estiver ativo, iniciar
    if (!ob_get_level()) {
        ob_start();
    }
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Inscrição - MovAmazon'; ?></title>
    
    <!-- Tailwind CSS Local -->
    <link href="../../assets/css/tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 para modais elegantes -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Alpine.js para interatividade do menu -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
    :root {
        --brand-green: #0b4340;
        --brand-yellow: #f5c113;
        --brand-red: #ad1f22;
    }

    .bg-brand-green {
        background-color: var(--brand-green) !important;
    }

    .text-brand-green {
        color: var(--brand-green) !important;
    }

    .text-brand-yellow {
        color: var(--brand-yellow) !important;
    }

    .bg-brand-yellow {
        background-color: var(--brand-yellow) !important;
    }

    .bg-gradient-brand {
        background: linear-gradient(135deg, #0b4340 0%, #10B981 100%);
    }

    .btn-primary {
        background-color: #0b4340;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.2s;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: none;
        cursor: pointer;
    }

    .btn-primary:hover {
        background-color: #065f5a;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
    }

    .btn-secondary {
        background-color: #e5e7eb;
        color: #374151;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-secondary:hover {
        background-color: #d1d5db;
    }

    .input-field {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }

    .input-field:focus {
        outline: none;
        border-color: #0b4340;
        box-shadow: 0 0 0 3px rgba(11, 67, 64, 0.1);
    }

    .card {
        background-color: white;
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border: 1px solid #f3f4f6;
    }

    /* Layout da Inscrição - Estilo Modal */
    .inscricao-container {
        min-height: 100vh;
        background: #f8f9fa;
        padding: 20px;
    }

    .inscricao-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .inscricao-header {
        background: linear-gradient(135deg, #0b4340 0%, #10B981 100%);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .inscricao-titulo {
        font-size: 2rem;
        font-weight: bold;
        margin: 0 0 10px 0;
    }

    .inscricao-evento {
        font-size: 1.1rem;
        margin: 0;
        opacity: 0.9;
    }

    .progress-container {
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    .inscricao-content {
        padding: 30px;
        min-height: 400px;
    }

    /* Cards de Modalidades - Design Otimizado */
    .modalidades-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(480px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .modalidade-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        background: white;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .modalidade-card:hover {
        border-color: #0b4340;
        box-shadow: 0 4px 15px rgba(11, 67, 64, 0.1);
        transform: translateY(-2px);
    }

    .modalidade-card.selected {
        border-color: #0b4340;
        background: linear-gradient(135deg, #f0f9f8 0%, #e8f5f3 100%);
    }

    /* Header da Modalidade */
    .modalidade-header {
        margin-bottom: 16px;
    }

    .modalidade-titulo h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 6px 0;
        line-height: 1.3;
    }

    .modalidade-meta {
        margin: 0;
        color: #6b7280;
        font-size: 0.875rem;
        line-height: 1.4;
    }

    /* Seção do Kit */
    .kit-section {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 16px;
        flex: 1;
    }

    .kit-image-wrapper {
        flex-shrink: 0;
        width: 100px;
        height: 100px;
        border-radius: 8px;
        overflow: hidden;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .kit-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .kit-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 2rem;
    }

    .kit-content {
        flex: 1;
        min-width: 0;
    }

    .kit-nome {
        margin: 0 0 10px 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #0b4340;
        line-height: 1.3;
    }

    .kit-produtos {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }

    .produto-badge {
        background: #f0f9f8;
        color: #065f5a;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        border: 1px solid #d1fae5;
        display: inline-block;
    }

    /* Footer com Preço */
    .modalidade-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 16px;
        border-top: 1px solid #e9ecef;
        margin-top: auto;
    }

    .preco-info {
        flex: 1;
    }

    .preco-valor {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0b4340;
        margin-bottom: 4px;
        line-height: 1.2;
    }

    .preco-detalhes {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .tipo-publico,
    .lote-info {
        font-size: 0.8rem;
        color: #6b7280;
        font-weight: 500;
    }

    .lote-info {
        padding: 2px 8px;
        background: #f3f4f6;
        border-radius: 4px;
    }

    .preco-indisponivel {
        font-size: 0.95rem;
        color: #9ca3af;
        font-weight: 500;
    }

    .modalidade-radio {
        width: 22px;
        height: 22px;
        accent-color: #0b4340;
        cursor: pointer;
        flex-shrink: 0;
    }

    .text-muted {
        color: #6b7280;
    }

    .d-block {
        display: block;
    }

    .btn-lg {
        padding: 0.875rem 2rem;
        font-size: 1.125rem;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .inscricao-container {
            padding: 10px;
        }

        .modalidades-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .modalidade-card {
            padding: 15px;
        }

        .kit-section {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .kit-image-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto;
        }

        .modalidade-footer {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }

        .preco-detalhes {
            flex-direction: column;
            gap: 4px;
            align-items: flex-start;
        }
    }
</style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Header padrão do sistema -->
    <nav class="bg-brand-green text-white shadow-sm border-b border-gray-100 sticky top-0 z-50 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
            <!-- Logo e Nome -->
            <div class="flex items-center space-x-3">
                <a href="../public/index.php" class="flex items-center space-x-2">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-2 border border-white/30 shadow-lg">
                        <img src="../../assets/img/logo.png" alt="MovAmazon" class="h-10 w-auto transition-transform hover:scale-105">
                    </div>
                    <span class="text-3xl font-bold tracking-tight hidden sm:inline">
                        <span class="text-white">Mov</span><span class="text-brand-yellow">Amazon</span>
                    </span>
                </a>
            </div>

            <!-- Links de Navegação (centro) -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="../public/index.php#sobre" class="text-gray-100 hover:text-brand-yellow font-medium transition-colors duration-200">Sobre</a>
                <a href="../public/index.php#contato" class="text-gray-100 hover:text-brand-yellow font-medium transition-colors duration-200">Contato</a>
            </div>

            <!-- Botões de Ação (direita) -->
            <div class="flex items-center space-x-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Usuário logado - Menu Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-300 hover:text-brand-yellow transition-colors duration-200">
                            <div class="w-8 h-8 bg-brand-yellow rounded-full flex items-center justify-center">
                                <span class="text-brand-green font-medium text-sm font-bold"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></span>
                            </div>
                            <span class="hidden sm:block text-sm"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuário'); ?></span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                            <?php
                            $dashboardUrl = '../participante/dashboard.php';
                            $dashboardText = 'Minha Área';
                            $dashboardIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>';
                            if (isset($_SESSION['papel']) && $_SESSION['papel'] === 'organizador') {
                                $dashboardUrl = '../organizador/index.php?page=dashboard';
                                $dashboardText = 'Dashboard Organizador';
                                $dashboardIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>';
                            } elseif (isset($_SESSION['papel']) && $_SESSION['papel'] === 'admin') {
                                $dashboardUrl = '../admin/index.php?page=dashboard';
                                $dashboardText = 'Painel Administrativo';
                                $dashboardIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>';
                            }
                            ?>
                            <a href="<?php echo $dashboardUrl; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?php echo $dashboardIcon; ?>
                                </svg>
                                <?php echo $dashboardText; ?>
                            </a>
                            <div class="border-t border-gray-200 my-1"></div>
                            <a href="../public/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Página Inicial
                            </a>
                            <div class="border-t border-gray-200 my-1"></div>
                            <a href="../auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"></path>
                                </svg>
                                Sair
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Usuário não logado -->
                    <a href="../auth/login.php" class="text-gray-300 hover:text-brand-yellow font-medium transition-colors duration-200 px-3 py-2 rounded-lg hover:bg-brand-green">
                        Entrar
                    </a>
                    <a href="../auth/register.php" class="bg-brand-green text-white px-4 py-2 rounded-lg font-medium hover:text-brand-yellow transition-all duration-200 shadow-sm hover:shadow-md">
                        Cadastrar
                    </a>
                <?php endif; ?>

                <!-- Menu mobile -->
                <button class="md:hidden p-2 rounded-lg text-gray-300 hover:text-brand-yellow hover:bg-white/10 transition-colors duration-200" onclick="toggleMobileMenu()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Menu mobile expandido -->
        <div id="mobile-menu-inscricao" class="md:hidden hidden bg-brand-green border-t border-white/10">
            <div class="py-3 px-4 space-y-2">
                <a href="../public/index.php#sobre" class="block px-3 py-2 text-gray-100 hover:text-brand-yellow rounded-lg">Sobre</a>
                <a href="../public/index.php#contato" class="block px-3 py-2 text-gray-100 hover:text-brand-yellow rounded-lg">Contato</a>
                <a href="../public/index.php" class="block px-3 py-2 text-gray-100 hover:text-brand-yellow rounded-lg">
                    <i class="fas fa-home mr-2"></i>Página Inicial
                </a>
            </div>
        </div>
    </nav>
    
    <script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu-inscricao');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }
    </script>
