<?php
// PADRÃO: Container único no index.php (Opção 1)
// Todas as páginas de conteúdo devem conter apenas o conteúdo, sem wrappers extras.
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once dirname(__DIR__, 3) . '/api/auth/middleware.php';

// Verificar autenticação e papel
requireParticipante();

$papel = $_SESSION['papel'] ?? null;
$pageTitle = 'Painel do Participante';
include dirname(__DIR__, 2) . '/includes/header.php';
include dirname(__DIR__, 2) . '/includes/navbar.php';
// Tailwind CSS já está incluído via header.php
?>
<script src="../../js/utils/eventImageUrl.js"></script>
<?php
// Usuário logado (corrigido para evitar warnings)
$user = [
  'name'  => $_SESSION['user_name'] ?? null,
  'email' => $_SESSION['user_email'] ?? null,
  'role'  => $_SESSION['papel'] ?? $papel // ou 'Participante' como valor padrão
];
$page_inicial = 'dashboard';
$page = isset($_GET['page']) ? $_GET['page'] : $page_inicial;
$allowedPages = ['dashboard', 'minhas-inscricoes', 'meu-perfil', 'meus-treinos', 'meu-cashback', 'anamnese', 'ver-treino', 'pagamento-inscricao', 'pagamento-sucesso', 'pagamento-pendente', 'pagamento-erro'];
if (!in_array($page, $allowedPages)) $page = $page_inicial;
?>
<?php include dirname(__DIR__, 2) . '/includes/mobile-menu.php'; ?>
<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="w-64 bg-brand-green text-white min-h-screen flex flex-col shadow-lg desktop-sidebar">
    <div class="p-6 flex items-center space-x-3 border-b border-green-600">
      <img src="../../assets/img/logo.png" class="h-8 w-8" alt="Logo" loading="lazy">
      <span class="font-bold text-lg">MovAmazon</span>
    </div>
    <nav class="flex-1 space-y-1 mt-8 px-4">
      <a href="?page=dashboard"
        class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 group <?php if ($page == 'dashboard') echo 'bg-brand-yellow text-brand-green font-semibold shadow-md';
                                                                                        else echo 'hover:bg-green-600 hover:text-white'; ?>">
        <svg class="h-5 w-5 mr-3 transition-colors duration-200 <?php if ($page == 'dashboard') echo 'text-brand-green';
                                                                else echo 'text-green-300 group-hover:text-white'; ?>"
          fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z" />
        </svg>
        Dashboard
      </a>

      <a href="?page=minhas-inscricoes"
        class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 group <?php if ($page == 'minhas-inscricoes') echo 'bg-brand-yellow text-brand-green font-semibold shadow-md';
                                                                                        else echo 'hover:bg-green-600 hover:text-white'; ?>">
        <svg class="h-5 w-5 mr-3 transition-colors duration-200 <?php if ($page == 'minhas-inscricoes') echo 'text-brand-green';
                                                                else echo 'text-green-300 group-hover:text-white'; ?>"
          fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Minhas Inscrições
      </a>

      <a href="?page=meu-perfil"
        class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 group <?php if ($page == 'meu-perfil') echo 'bg-brand-yellow text-brand-green font-semibold shadow-md';
                                                                                        else echo 'hover:bg-green-600 hover:text-white'; ?>">
        <svg class="h-5 w-5 mr-3 transition-colors duration-200 <?php if ($page == 'meu-perfil') echo 'text-brand-green';
                                                                else echo 'text-green-300 group-hover:text-white'; ?>"
          fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        Meu Perfil
      </a>

      <a href="?page=meus-treinos"
        class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 group <?php if ($page == 'meus-treinos') echo 'bg-brand-yellow text-brand-green font-semibold shadow-md';
                                                                                        else echo 'hover:bg-green-600 hover:text-white'; ?>">
        <svg class="h-5 w-5 mr-3 transition-colors duration-200 <?php if ($page == 'meus-treinos') echo 'text-brand-green';
                                                                else echo 'text-green-300 group-hover:text-white'; ?>"
          fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        Meus Treinos
      </a>

      <a href="?page=meu-cashback"
        class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 group <?php if ($page == 'meu-cashback') echo 'bg-brand-yellow text-brand-green font-semibold shadow-md';
                                                                                        else echo 'hover:bg-green-600 hover:text-white'; ?>">
        <svg class="h-5 w-5 mr-3 transition-colors duration-200 <?php if ($page == 'meu-cashback') echo 'text-brand-green';
                                                                else echo 'text-green-300 group-hover:text-white'; ?>"
          fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Meu Cashback
      </a>
    </nav>

    <!-- Footer do menu -->
    <div class="p-4 border-t border-green-600">
      <div class="flex items-center space-x-3 text-sm text-green-200">
        <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
          <span
            class="text-white font-medium text-xs"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></span>
        </div>
        <div>
          <p class="font-medium"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuário'); ?></p>
          <p class="text-xs text-green-300">Participante</p>
        </div>
      </div>
    </div>
  </aside>
  <!-- Main Content -->
  <main class="flex-1 p-8 bg-graybg mobile-bottom-padding">
    <div class="max-w-7xl mx-auto">
      <?php require_once($page . '.php'); ?>
    </div>
  </main>
</div>
<?php include dirname(__DIR__, 2) . '/includes/mobile-bottom-nav.php'; ?>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
