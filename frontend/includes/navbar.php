<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<nav class="bg-brand-green text-white shadow-sm border-b border-gray-100 sticky top-0 z-50 backdrop-blur-sm">
  <div class="max-w-7xl mx-auto flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
    <!-- Logo e Nome -->
    <div class="flex items-center space-x-3">
      <a href="../../paginas/public/index.php" class="flex items-center space-x-2">
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
      <a href="#" class="text-gray-100 hover:text-brand-yellow font-medium transition-colors duration-200">Sobre</a>
      <a href="#contato" class="text-gray-100 hover:text-brand-yellow font-medium transition-colors duration-200">Contato</a>
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
            // Sempre mostrar atalho para dashboard se estiver logado
            $dashboardUrl = '../../paginas/participante/dashboard.php';
            $dashboardText = 'Minha Área';
            $dashboardIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>';
            if (isset($_SESSION['papel']) && $_SESSION['papel'] === 'organizador') {
              $dashboardUrl = '../../paginas/organizador/index.php?page=dashboard';
              $dashboardText = 'Dashboard Organizador';
              $dashboardIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>';
            } elseif (isset($_SESSION['papel']) && $_SESSION['papel'] === 'admin') {
              $dashboardUrl = '../../paginas/admin/index.php?page=dashboard';
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
            <a href="../../paginas/public/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200">
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
              </svg>
              Página Inicial
            </a>
            <div class="border-t border-gray-200 my-1"></div>
            <a href="../../paginas/auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200">
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"></path>
              </svg>
              Sair
            </a>
          </div>
        </div>
      <?php else: ?>
        <!-- Usuário não logado -->
        <a href="../../paginas/auth/login.php" class="text-gray-300 hover:text-brand-yellow font-medium transition-colors duration-200 px-3 py-2 rounded-lg hover:bg-brand-green">
          Entrar
        </a>
        <a href="../../paginas/auth/register.php" class="bg-brand-green text-white px-4 py-2 rounded-lg font-medium hover:text-brand-yellow transition-all duration-200 shadow-sm hover:shadow-md">
          Cadastrar
        </a>
      <?php endif; ?>

      <!-- Menu mobile -->
      <button class="md:hidden p-2 rounded-lg text-gray-600 hover:text-brand-green hover:bg-gray-50 transition-colors duration-200">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>
  </div>
</nav>
