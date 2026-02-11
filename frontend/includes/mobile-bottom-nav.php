<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina = $_GET['page'] ?? 'dashboard';
$isAdmin = false;

if (isset($_SERVER['REQUEST_URI']) && stripos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
    $isAdmin = true;
}

$items = $isAdmin ? [
    'dashboard' => ['label' => 'Admin', 'icon' => 'fas fa-gauge'],
    'configuracoes' => ['label' => 'Config', 'icon' => 'fas fa-sliders-h'],
    'organizadores' => ['label' => 'Org.', 'icon' => 'fas fa-users-cog'],
    'pagamentos-pendentes' => ['label' => 'Pagos', 'icon' => 'fas fa-clock'],
    'solicitacoes' => ['label' => 'Solic.', 'icon' => 'fas fa-clipboard-check']
] : [
    'dashboard' => ['label' => 'Home', 'icon' => 'fas fa-gauge'],
    'minhas-inscricoes' => ['label' => 'Insc.', 'icon' => 'fas fa-ticket-alt'],
    'meu-perfil' => ['label' => 'Perfil', 'icon' => 'fas fa-user'],
    'meus-treinos' => ['label' => 'Treinos', 'icon' => 'fas fa-running'],
    'meu-cashback' => ['label' => 'Extra', 'icon' => 'fas fa-coins']
];
?>

<nav class="mobile-bottom-nav mobile-only" aria-label="Navegação principal">
    <div class="flex h-full">
        <?php foreach ($items as $key => $item): ?>
            <a href="?page=<?php echo $key; ?>"
               class="flex-1 flex flex-col items-center justify-center text-xs <?php echo $pagina === $key ? 'text-brand-green font-semibold' : 'text-gray-500'; ?>"
               aria-current="<?php echo $pagina === $key ? 'page' : 'false'; ?>">
                <i class="<?php echo $item['icon']; ?> text-lg"></i>
                <span><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>
