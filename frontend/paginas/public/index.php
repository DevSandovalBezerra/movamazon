<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$pageTitle = 'MovAmazon - Encontre sua próxima corrida';
include '../../includes/header_index.php';

// Verificar se houve logout
$logoutMessage = '';
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
  $logoutMessage = 'Logout realizado com sucesso!';
}
?>
<div aria-hidden="true" class="fixed inset-0 pointer-events-none" style="background: rgba(255, 255, 255, 0.95); z-index:0;"></div>
<?php if ($logoutMessage): ?>
  <!-- Mensagem de Logout -->
  <div class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg animate-slide-up" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
    <div class="flex items-center space-x-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      </svg>
      <span><?php echo htmlspecialchars($logoutMessage); ?></span>
    </div>
  </div>
<?php endif; ?>
<!-- Hero Section com Carrossel de Banners (SEPARADA DO HEADER) -->
<section class="hero-section relative w-full h-[500px] overflow-hidden bg-brand-green">
  <!-- Swiper Carousel -->
  <div class="swiper hero-carousel h-full">
    <div class="swiper-wrapper">
      <!-- Banners serão carregados dinamicamente via API -->
      <!-- Fallback apenas se não houver banners no banco -->
      <div class="swiper-slide fallback-banner" style="display: none;">
        <div class="relative w-full h-full">
          <div class="absolute inset-0 w-full h-full">
            <img 
              src="../../assets/img/eventos/evento_4.jpg" 
              alt="Encontre sua próxima corrida"
              class="w-full h-full object-cover object-center"
              loading="lazy"
              onerror="console.error('[PUBLIC_BANNERS] Erro ao carregar imagem fallback');"
            />
          </div>
          <div class="absolute inset-0 bg-black/30"></div>
          <div class="relative z-10 h-full flex items-center justify-center px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center text-white">
              <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 leading-tight">
                Encontre sua próxima
                <span class="text-brand-yellow">Corrida</span>
              </h1>
              <p class="text-lg sm:text-xl md:text-2xl mb-6 max-w-3xl mx-auto">
                A plataforma completa e única que une você a todos os eventos de corridas no Amazonas
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Pagination -->
    <div class="swiper-pagination"></div>
    
    <!-- Navigation -->
    <div class="swiper-button-next text-white"></div>
    <div class="swiper-button-prev text-white"></div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Função para garantir altura completa
  function garantirAlturaCompleta() {
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
      heroSection.style.height = '500px';
      heroSection.style.minHeight = '500px';
    }
    
    const swiper = document.querySelector('.hero-section .swiper');
    if (swiper) {
      swiper.style.height = '100%';
      swiper.style.minHeight = '100%';
    }
    
    const slides = document.querySelectorAll('.hero-section .swiper-slide');
    slides.forEach(slide => {
      slide.style.height = '100%';
      slide.style.minHeight = '100%';
      
      // Corrigir seletor CSS inválido - usar firstElementChild ou :scope
      const slideDiv = slide.firstElementChild || slide.querySelector(':scope > div');
      if (slideDiv) {
        slideDiv.style.height = '100%';
        slideDiv.style.minHeight = '100%';
      }
      
      const imgContainer = slide.querySelector('div[class*="absolute"]');
      if (imgContainer) {
        imgContainer.style.height = '100%';
        imgContainer.style.minHeight = '100%';
      }
    });
    
    // Forçar altura das imagens
    const heroImages = document.querySelectorAll('.hero-section img');
    heroImages.forEach(img => {
      img.style.width = '100%';
      img.style.height = '100%';
      img.style.minHeight = '100%';
      img.style.objectFit = 'cover';
      img.style.objectPosition = 'center';
    });
  }
  
  // Executar imediatamente
  garantirAlturaCompleta();
  
  // Executar após imagens carregarem
  const heroImages = document.querySelectorAll('.hero-section img');
  heroImages.forEach(img => {
    if (img.complete) {
      garantirAlturaCompleta();
    } else {
      img.addEventListener('load', garantirAlturaCompleta);
    }
  });
  
  // Atualizar Swiper se já estiver inicializado
  if (window.heroSwiper) {
    setTimeout(() => {
      window.heroSwiper.update();
    }, 100);
  }
});
</script>

<!-- Main Wrapper -->
<main class="max-w-[1200px] mx-auto">
  <!-- Events Section -->
  <section id="eventos-disponiveis" class="py-12 sm:py-16 md:py-20 bg-transparent">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12 animate-fade-in">
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-4">Eventos Disponíveis</h2>
        <div id="eventos-count" class="text-lg text-gray-600">
          <div class="flex items-center justify-center space-x-2">
            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-green-600"></div>
            <span>Carregando eventos...</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <!-- Evento dinâmico -->
        <div id="eventos-dinamicos" class="contents"></div>
      </div>
    </div>
  </section>

  <!-- Organizadores Section -->
  <section class="py-12 sm:py-16 md:py-20 bg-gradient-to-br from-brand-green to-green-600 text-white relative overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-20" style="background-image: url('../../assets/img/pernas.jpg');"></div>
    <!-- Overlay -->
    <div class="absolute inset-0"></div>
    <!-- Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4">Você é um <span class="text-brand-yellow">Organizador</span> de Eventos?</h2>
        <p class="text-lg sm:text-xl text-green-100 mb-8">Acelere a venda e faça gestão das inscrições do seu evento com o <span class="text-brand-yellow">MovAmazon</span>!</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div class="space-y-6">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-brand-yellow/20 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-brand-yellow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold">Gestão Completa</h3>
              <p class="text-green-100">Controle inscrições, pagamentos e kits em uma única plataforma</p>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-brand-yellow/20 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-brand-yellow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold">Relatórios Detalhados</h3>
              <p class="text-green-100">Acompanhe o desempenho do seu evento em tempo real</p>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-brand-yellow/20 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-brand-yellow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold">Pagamentos Seguros</h3>
              <p class="text-green-100">Múltiplas formas de pagamento com segurança total</p>
            </div>
          </div>
        </div>

        <div class="relative">
          <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20">
            <h3 class="text-2xl font-bold mb-6">Quer criar seu evento?</h3>
            <p class="text-green-100 mb-8">Preencha o formulário e entraremos em contato com você 😊</p>

            <a href="organizador-landing.php" class="w-full bg-brand-yellow text-brand-green px-6 py-4 rounded-xl font-bold text-lg hover:bg-yellow-400 transition-colors duration-200 flex items-center justify-center space-x-2">
              <span>QUERO CRIAR MEU EVENTO</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 5l7 7-7 7" />
              </svg>
            </a>

            <p class="text-xs text-green-200 mt-4 text-center">*Não atendemos dúvidas de participantes neste formulário.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include '../../includes/footer.php'; ?>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  // Definir API_BASE ANTES de importar qualquer módulo
  (function() {
    if (!window.API_BASE) {
      const path = window.location.pathname || '';
      const idx = path.indexOf('/frontend/');
      if (idx > 0) {
        // Caso normal: /movamazon/frontend/... -> base = /movamazon
        window.API_BASE = path.slice(0, idx);
      } else if (idx === 0) {
        // Caso: /frontend/... -> base = '' (raiz)
        window.API_BASE = '';
      } else {
        // Fallback: tentar detectar pelo caminho atual
        const pathParts = path.split('/').filter(p => p);
        const frontendIdx = pathParts.indexOf('frontend');
        if (frontendIdx > 0) {
          window.API_BASE = '/' + pathParts.slice(0, frontendIdx).join('/');
        } else {
          window.API_BASE = '';
        }
      }
      console.log('[INDEX] API_BASE definido:', window.API_BASE, '| Pathname:', path);
    }
  })();
</script>
<script type="module">
  import { carregarBanners } from '../../js/public/banners.js';
  
  // Função para inicializar o Swiper
  function inicializarSwiper() {
    console.log('[INDEX] inicializarSwiper() chamado');
    if (window.heroSwiper) {
      console.log('[INDEX] Swiper já inicializado, ignorando');
      return; // Já inicializado
    }
    
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
      heroSection.style.height = '500px';
      heroSection.style.minHeight = '500px';
    }
    
    // Verificar se há slides antes de inicializar
    const swiperWrapper = document.querySelector('.hero-carousel .swiper-wrapper');
    const hasSlides = swiperWrapper && swiperWrapper.children.length > 0;
    
    console.log('[INDEX] Criando nova instância do Swiper (hasSlides:', hasSlides, ')');
    window.heroSwiper = new Swiper('.hero-carousel', {
      loop: hasSlides, // Só ativar loop se houver slides
      height: 500,
      autoplay: hasSlides ? {
        delay: 5000,
        disableOnInteraction: false,
      } : false,
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      effect: 'fade',
      fadeEffect: {
        crossFade: true
      },
      on: {
        init: function() {
          console.log('[INDEX] ✓ Swiper inicializado com sucesso');
          this.update();
        }
      }
    });
  }
  
  // Escutar evento de banners carregados
  window.addEventListener('bannersCarregados', function(event) {
    console.log('[INDEX] Evento bannersCarregados recebido', event.detail ? `(${event.detail.banners?.length || 0} banners)` : '');
    setTimeout(() => {
      const wrapper = document.querySelector('.hero-carousel .swiper-wrapper');
      const fallback = document.querySelector('.fallback-banner');
      const hasSlides = wrapper && (wrapper.children.length > 0 || (fallback && fallback.style.display !== 'none'));
      
      if (hasSlides) {
        console.log('[INDEX] Slides encontrados (' + (wrapper?.children.length || 0) + '), inicializando Swiper');
        inicializarSwiper();
      } else {
        console.warn('[INDEX] Nenhum slide encontrado no wrapper, aguardando...');
        // Tentar novamente após mais um delay
        setTimeout(() => {
          const wrapperRetry = document.querySelector('.hero-carousel .swiper-wrapper');
          const fallbackRetry = document.querySelector('.fallback-banner');
          const hasSlidesRetry = wrapperRetry && (wrapperRetry.children.length > 0 || (fallbackRetry && fallbackRetry.style.display !== 'none'));
          
          if (hasSlidesRetry) {
            console.log('[INDEX] Slides encontrados no retry, inicializando Swiper');
            inicializarSwiper();
          } else {
            console.warn('[INDEX] Ainda sem slides após retry, inicializando mesmo assim para garantir');
            // Inicializar mesmo sem slides para garantir que o Swiper funcione
            inicializarSwiper();
          }
        }, 200);
      }
    }, 200); // Aumentar delay para garantir DOM atualizado
  });
  
  // Fallback caso módulo ES6 não carregue
  let bannersCarregados = false;
  setTimeout(() => {
    if (!bannersCarregados && !window.heroSwiper) {
      console.warn('[INDEX] Módulo ES6 pode não ter carregado, tentando fallback');
      // Tentar carregar banners diretamente via fetch
      const apiBase = window.API_BASE || '';
      let apiUrl;
      if (apiBase && apiBase.trim() !== '') {
        const baseClean = apiBase.replace(/\/$/, '');
        apiUrl = `${baseClean}/api/banners/public.php`;
      } else {
        const path = window.location.pathname || '';
        const pathParts = path.split('/').filter(p => p);
        const frontendIdx = pathParts.indexOf('frontend');
        if (frontendIdx >= 0) {
          const baseParts = pathParts.slice(0, frontendIdx);
          if (baseParts.length > 0) {
            apiUrl = '/' + baseParts.join('/') + '/api/banners/public.php';
          } else {
            apiUrl = '/api/banners/public.php';
          }
        } else {
          apiUrl = '/api/banners/public.php';
        }
      }
      fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.banners && data.banners.length > 0) {
            console.log('[INDEX] Fallback: banners carregados via fetch direto');
            window.dispatchEvent(new CustomEvent('bannersCarregados', { detail: { banners: data.banners } }));
          }
        })
        .catch(err => console.error('[INDEX] Erro no fallback:', err));
    }
  }, 1000);
  
  // Inicializar após DOM carregar
  document.addEventListener('DOMContentLoaded', function() {
    console.log('[INDEX] DOM carregado, iniciando carregamento de banners');
    // Primeiro, carregar os banners (que vai disparar o evento 'bannersCarregados')
    carregarBanners().then(() => {
      bannersCarregados = true;
      console.log('[INDEX] carregarBanners() concluído com sucesso');
    }).catch((error) => {
      console.error('[INDEX] Erro ao carregar banners:', error);
      bannersCarregados = false;
      // Mesmo com erro, tentar inicializar com fallback
      setTimeout(() => {
        const wrapper = document.querySelector('.hero-carousel .swiper-wrapper');
        if (wrapper && wrapper.children.length > 0) {
          console.log('[INDEX] Inicializando Swiper com fallback após erro');
          inicializarSwiper();
        }
      }, 200);
    });
  });
</script>
<script type="module" src="../../js/main.js"></script>
