<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once dirname(__DIR__, 3) . '/api/auth/middleware.php';

// Verificar autenticação e papel
requireOrganizador();

$pageTitle = 'Painel do Organizador';
include '../../includes/header.php';
include '../../includes/navbar.php'; // Navbar global

// CSS para links desabilitados
echo '<style>
.disabled-link {
  pointer-events: none;
  opacity: 0.5;
  cursor: not-allowed;
}
.disabled-link:hover {
  background-color: transparent !important;
  color: inherit !important;
}
</style>';

// Mock do usuário logado (ajuste para pegar da sessão real)
$user = [
  'name' => $_SESSION['user_name'] ?? 'Eudimaci Lira',
  'role' => 'organizador'
];
$page_inicial = 'dashboard';
$page = isset($_GET['page']) ? $_GET['page'] : $page_inicial;

$allowedPages = [
  'dashboard',
  'eventos',
  'criar-evento',
  'tutorial-evento',
  'participantes',
  'modalidades',
  'produtos',
  'kits-templates',
  'kits-evento',
  'lotes-inscricao',
  'produtos-extras',
  'programacao',
  'retirada-kits',
  'questionario',
  'pagamentos',
  'camisas',
  'estoque',
  'relatorios',
  'cupons-remessa'
];
if (!in_array($page, $allowedPages)) $page = $page_inicial;
?>
<div class="min-h-screen bg-gray-50">
  <!-- Header com Menu Hambúrguer -->
  <header class="bg-brand-green text-white shadow-lg sticky top-0 z-50 lg:hidden">
    <div class="flex items-center justify-between px-3 sm:px-4 py-2 sm:py-3">
      <!-- Logo e Título -->
      <div class="flex items-center space-x-2 sm:space-x-3">
        <img src="../../assets/img/logo.png" class="h-6 w-6 sm:h-8 sm:w-8" alt="Logo">
        <span class="font-bold text-sm sm:text-lg text-white">MovAmazon</span>
      </div>

      <!-- Botão Hambúrguer -->
      <button id="menu-toggle" aria-label="Abrir menu" class="p-2 sm:p-2.5 rounded-lg bg-brand-yellow text-brand-green ring-2 ring-brand-yellow/60 shadow-md hover:brightness-95 transition-colors">
        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>
  </header>

  <div class="flex min-h-screen">
    <!-- Sidebar: em mobile fica oculto (drawer); em lg+ fixo à esquerda -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 max-w-[85vw] bg-brand-green text-white transform -translate-x-full transition-transform duration-300 ease-in-out z-40 lg:translate-x-0 lg:static lg:inset-0 flex flex-col overflow-y-auto overscroll-y-contain">
      <div class="p-4 sm:p-6 flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
        <img src="../../assets/img/logo.png" class="h-6 w-6 sm:h-8 sm:w-8" alt="Logo">
        <span class="font-bold text-sm sm:text-lg">MovAmazon</span>
      </div>

      <nav class="flex-1 space-y-1 sm:space-y-2 mt-4 sm:mt-6 lg:mt-8">
        <a href="?page=dashboard" class="flex items-center px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition text-sm sm:text-base <?php if ($page == 'dashboard') echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-chart-line mr-1.5 sm:mr-2 text-xs sm:text-sm"></i> Dashboard
        </a>

        <a href="?page=participantes" class="flex items-center px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition text-sm sm:text-base <?php if ($page == 'participantes') echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-users mr-1.5 sm:mr-2 text-xs sm:text-sm"></i> Participantes
        </a>

        <!--  <a href="#" class="flex items-center px-4 py-2 rounded-lg transition disabled-link opacity-50 cursor-not-allowed" onclick="return false;">
        <i class="fas fa-warehouse mr-2"></i> Estoque
      </a> -->
        <a href="#" class="flex items-center px-4 py-2 rounded-lg transition disabled-link opacity-50 cursor-not-allowed" onclick="return false;">
          <i class="fas fa-chart-bar mr-2"></i> Relatórios
        </a>
        <a href="#" class="flex items-center px-4 py-2 rounded-lg transition disabled-link opacity-50 cursor-not-allowed" onclick="return false;">
          <i class="fas fa-credit-card mr-2"></i> Pagamentos
        </a>
        <div class="my-6 px-4">
          <div class="h-1 bg-gradient-to-r from-brand-yellow/10 via-brand-yellow/50 to-brand-yellow/10 rounded-full shadow-sm"></div>
        </div>

        <!-- Eventos com submenu -->
        <div class="relative">
          <button onclick="toggleSubmenu('eventos-submenu')" class="flex items-center justify-between w-full px-4 py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition <?php if ($page == 'eventos' || $page == 'criar-evento') echo 'bg-white text-brand-green font-bold'; ?>">
            <div class="flex items-center">
              <i class="fas fa-calendar-alt mr-2"></i>
              1- Meus Eventos
            </div>
            <i class="fas fa-chevron-down text-xs transition-transform duration-200" id="eventos-arrow"></i>
          </button>

          <!-- Submenu Eventos -->
          <div id="eventos-submenu" class="ml-4 mt-2 space-y-1 <?php if ($page == 'eventos' || $page == 'criar-evento') echo 'block';
                                                                else echo 'hidden'; ?>">
            <a href="?page=eventos" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-brand-yellow/20 hover:text-brand-green transition <?php if ($page == 'eventos') echo 'bg-brand-yellow/30 text-brand-green font-semibold'; ?>">
              <i class="fas fa-list mr-2"></i>
              Lista de Eventos
            </a>
          <!--   <a href="?page=criar-evento" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-brand-yellow/20 hover:text-brand-green transition <?php if ($page == 'criar-evento') echo 'bg-brand-yellow/30 text-brand-green font-semibold'; ?>">
              <i class="fas fa-plus mr-2"></i>
              Criar Evento
            </a> -->
          </div>
        </div>

        <a href="?page=modalidades" class="flex items-center px-4 py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition whitespace-nowrap <?php if ($page == 'modalidades') echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-running mr-2 flex-shrink-0"></i><span>2- Modalidades</span>
        </a>
        <a href="?page=lotes-inscricao" class="flex items-center px-4 py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition whitespace-nowrap <?php if (
                                                                                                                                                                  $page == 'lotes-inscricao'
                                                                                                                                                                ) echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-tags mr-2 flex-shrink-0"></i><span>3- Lotes de Inscrição</span>
        </a>
        <a href="?page=cupons-remessa" class="flex items-center px-4 py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition whitespace-nowrap <?php if ($page == 'cupons-remessa') echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-ticket-alt mr-2 flex-shrink-0"></i><span>4- Cupons de Desconto</span>
        </a>
        <a href="?page=questionario" class="flex items-center px-4 py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition whitespace-nowrap <?php if ($page == 'questionario') echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-question-circle mr-2 flex-shrink-0"></i><span>5- Questionário</span>
        </a>
        <a href="?page=produtos" class="flex items-center px-4 py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition whitespace-nowrap <?php if ($page == 'produtos') echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-tags mr-2 flex-shrink-0"></i><span>6- Produtos</span>
        </a>
        <a href="?page=kits-templates" class="flex items-center px-4 py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition whitespace-nowrap <?php if ($page == 'kits-templates') echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-box mr-2 flex-shrink-0"></i><span>7- Templates de Kit</span>
        </a>
        <a href="?page=kits-evento" class="flex items-center px-4 py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition whitespace-nowrap <?php if ($page == 'kits-evento') echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-gift mr-2 flex-shrink-0"></i><span>8- Kits do Evento</span>
        </a>
        <a href="?page=retirada-kits" class="flex items-center px-4 py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition whitespace-nowrap <?php if ($page == 'retirada-kits') echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-box-open mr-2 flex-shrink-0"></i><span>9- Retirada de Kits</span>
        </a>

        <a href="?page=camisas" class="flex items-center px-4 py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition whitespace-nowrap <?php if ($page == 'camisas') echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-tshirt mr-2 flex-shrink-0"></i><span>10- Camisas</span>
        </a>


        <a href="?page=produtos-extras" class="flex items-center px-4 py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition whitespace-nowrap <?php if ($page == 'produtos-extras') echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-shopping-cart mr-2 flex-shrink-0"></i><span>11- Produtos Extras</span>
        </a>

        <a href="?page=programacao" class="flex items-center px-4 py-2 rounded-lg hover:bg-brand-yellow hover:text-brand-green transition whitespace-nowrap <?php if ($page == 'programacao') echo 'bg-white text-brand-green font-bold'; ?>">
          <i class="fas fa-calendar-check mr-2 flex-shrink-0"></i><span>12- Checklist</span>
        </a>




      </nav>
    </aside>
    <!-- Main Content: evita overflow horizontal em mobile -->
    <main class="flex-1 min-w-0 lg:ml-0 pt-0 px-2 sm:px-3 lg:px-6 pb-2 sm:pb-3 lg:pb-6 bg-gray-50 min-h-screen lg:pt-8 flex justify-center">
      <div class="w-full mx-auto max-w-screen-md sm:max-w-screen-lg lg:max-w-6xl xl:max-w-7xl min-w-0">
        <?php
        if ($page === 'dashboard') {
          require_once('dashboard.php');
        } elseif ($page === 'criar-evento') {
          require_once('eventos/criar.php');
        } elseif ($page === 'tutorial-evento') {
          require_once('tutorial-evento.php');
        } elseif ($page === 'editar-evento') {
          require_once('editar-evento.php');
        } else {
          require_once($page . '/index.php');
        }
        ?>
      </div>
    </main>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>

<script>
  // Controle do menu hambúrguer
  document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.createElement('div');

    // Criar overlay para mobile
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden';
    document.body.appendChild(overlay);

    // Toggle do menu
    menuToggle.addEventListener('click', function() {
      sidebar.classList.toggle('-translate-x-full');
      overlay.classList.toggle('hidden');
    });

    // Fechar menu ao clicar no overlay
    overlay.addEventListener('click', function() {
      sidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
    });

    // Fechar menu ao redimensionar para desktop
    window.addEventListener('resize', function() {
      if (window.innerWidth >= 1024) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.add('hidden');
      }
    });
  });

  // Função para toggle do submenu
  function toggleSubmenu(submenuId) {
    const submenu = document.getElementById(submenuId);
    const arrow = document.getElementById(submenuId.replace('-submenu', '-arrow'));

    if (submenu.classList.contains('hidden')) {
      submenu.classList.remove('hidden');
      submenu.classList.add('block');
      arrow.style.transform = 'rotate(180deg)';
    } else {
      submenu.classList.add('hidden');
      submenu.classList.remove('block');
      arrow.style.transform = 'rotate(0deg)';
    }
  }

  // Auto-abrir submenu se estiver em uma página de eventos
  document.addEventListener('DOMContentLoaded', function() {
    const currentPage = '<?php echo $page; ?>';
    if (currentPage === 'eventos' || currentPage === 'criar-evento' || currentPage === 'editar-evento') {
      const submenu = document.getElementById('eventos-submenu');
      const arrow = document.getElementById('eventos-arrow');
      if (submenu) {
        submenu.classList.remove('hidden');
        submenu.classList.add('block');
        arrow.style.transform = 'rotate(180deg)';
      }
    }
  });
</script>