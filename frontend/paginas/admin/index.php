<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 3) . '/api/admin/auth_middleware.php';

requererAdmin();

$pageTitle = 'Painel Administrativo';
$page_inicial = 'dashboard';
$activePage = isset($_GET['page']) ? $_GET['page'] : $page_inicial;

// Apenas páginas funcionais
    $allowedPages = [
        'dashboard',
        'banners',
        'solicitacoes',
        'configuracoes',
        'ia',
        'organizadores',
        'inscricoes',
        'termos-inscricao',
        'problemas-inscricoes',
        'pagamentos-pendentes',
        'cancelamentos'
    ];

if (!in_array($activePage, $allowedPages)) {
    $activePage = $page_inicial;
}

include '../../includes/header.php';
?>

<link rel="stylesheet" href="../../assets/css/admin.css">

<!-- JavaScript comum para páginas admin (SweetAlert e utilitários) -->
<script src="../../js/admin/common.js"></script>

<div class="admin-layout bg-[#f5f7fa] min-h-screen">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <?php
        $pageTitles = [
            'dashboard' => 'Dashboard',
            'banners' => 'Banners do Carrossel',
            'solicitacoes' => 'Solicitações de Eventos',
            'configuracoes' => 'Configurações do Sistema',
            'ia' => 'Inteligência Artificial',
            'organizadores' => 'Organizadores',
            'inscricoes' => 'Inscrições',
            'termos-inscricao' => 'Termos de Inscrição',
            'problemas-inscricoes' => 'Problemas com Inscrições',
            'pagamentos-pendentes' => 'Pagamentos Pendentes',
            'cancelamentos' => 'Solicitações de Cancelamento'
        ];
        $currentPageTitle = $pageTitles[$activePage] ?? 'Painel Administrativo';
    ?>

    <div class="admin-content flex-1 flex flex-col min-h-screen mobile-bottom-padding">
        <?php include '../../includes/admin_header.php'; ?>

        <main class="admin-main flex-1">
            <div class="admin-page mx-auto w-full">
                <?php
                $pageDir = __DIR__;
                $pageFile = $pageDir . DIRECTORY_SEPARATOR . $activePage . '.php';
                if (file_exists($pageFile)) {
                    include $pageFile;
                } else {
                    include $pageDir . DIRECTORY_SEPARATOR . 'dashboard.php';
                }
                ?>
            </div>
        </main>

        <?php include '../../includes/mobile-bottom-nav.php'; ?>

        <footer class="admin-footer">
            &copy; <?php echo date('Y'); ?> MovAmazon. Todos os direitos reservados.
        </footer>
    </div>
</div>
