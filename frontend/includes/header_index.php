<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'MovAmazon - Encontre sua próxima corrida'; ?></title>

    <!-- Tailwind CSS Compilado -->
    <link rel="stylesheet" href="../../assets/css/tailwind.min.css">
    
    <!-- Swiper CSS para carrossel -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- CSS específico da Landing Page Pública -->
    <link rel="stylesheet" href="../../assets/css/public-landing.css">
    
    <!-- CSS Customizado (importado após Tailwind) -->
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/custom.css">

    <!-- Alpine.js para interatividade -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 para confirmações -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS customizado inline para evitar problemas de caminho -->
    <style>
        /* Custom CSS para complementar o Tailwind CSS */

        /* Cores da marca MovAmazon */
        :root {
            --brand-green: #0b4340;
            --brand-yellow: #f5c113;
            --brand-red: #ad1f22;
            --brand-azul: #1E90FF;
        }

        /* Garantir que o texto do menu seja visível */
        .bg-brand-green {
            background-color: var(--brand-green) !important;
        }

        .text-brand-green {
            color: var(--brand-green) !important;
        }

        .text-brand-yellow {
            color: var(--brand-yellow) !important;
        }

        /* Estilos customizados para componentes específicos */
        .btn-primary {
            background-color: var(--brand-green);
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
            display: inline-block;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #0a3a37;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(11, 67, 64, 0.3);
        }

        /* Cards modernos */
        .card {
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Animações customizadas */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Estilos específicos para Hero Section e Swiper */
        .hero-section {
            min-height: 500px;
        }

        .hero-section .swiper {
            height: 100% !important;
            min-height: 100% !important;
        }

        .hero-section .swiper-wrapper {
            height: 100% !important;
            min-height: 100% !important;
        }

        .hero-section .swiper-slide {
            height: 100% !important;
            min-height: 100% !important;
        }

        .hero-section .swiper-slide > div {
            height: 100% !important;
            min-height: 100% !important;
        }

        .hero-section .swiper-slide > div > div[class*="absolute"] {
            height: 100% !important;
            min-height: 100% !important;
        }

        .hero-section img {
            width: 100% !important;
            height: 100% !important;
            min-height: 100% !important;
            max-height: none !important;
            object-fit: cover !important;
            object-position: center !important;
        }

        /* Reset de possíveis estilos conflitantes */
        .hero-section * {
            box-sizing: border-box;
        }
    </style>

    <!-- Script para validação de meses -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obter mês corrente no formato YYYY-MM
            const hoje = new Date();
            const mesCorrente = hoje.getFullYear() + '-' + String(hoje.getMonth() + 1).padStart(2, '0');
            
            const filtroMesInicio = document.getElementById('filtro-mes-ano-inicio');
            const filtroMesFim = document.getElementById('filtro-mes-ano-fim');
            
            if (filtroMesInicio) {
                // Definir valor mínimo e padrão como mês corrente
                filtroMesInicio.setAttribute('min', mesCorrente);
                filtroMesInicio.value = mesCorrente;
                
                // Validar ao mudar o valor
                filtroMesInicio.addEventListener('change', function() {
                    const valorSelecionado = this.value;
                    if (valorSelecionado && valorSelecionado < mesCorrente) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data inválida',
                            text: 'Não é possível selecionar um mês anterior ao mês corrente.',
                            confirmButtonColor: '#0b4340'
                        });
                        this.value = mesCorrente;
                    }
                    
                    // Validar se mês fim é menor que mês início
                    if (filtroMesFim && filtroMesFim.value && valorSelecionado && valorSelecionado > filtroMesFim.value) {
                        filtroMesFim.value = valorSelecionado;
                    }
                });
            }
            
            if (filtroMesFim) {
                // Definir valor mínimo como mês corrente
                filtroMesFim.setAttribute('min', mesCorrente);
                filtroMesFim.value = mesCorrente;
                
                // Validar ao mudar o valor
                filtroMesFim.addEventListener('change', function() {
                    const valorSelecionado = this.value;
                    if (valorSelecionado && valorSelecionado < mesCorrente) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data inválida',
                            text: 'Não é possível selecionar um mês anterior ao mês corrente.',
                            confirmButtonColor: '#0b4340'
                        });
                        this.value = mesCorrente;
                    }
                    
                    // Validar se mês fim é menor que mês início
                    if (filtroMesInicio && filtroMesInicio.value && valorSelecionado && valorSelecionado < filtroMesInicio.value) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data inválida',
                            text: 'O mês final não pode ser anterior ao mês inicial.',
                            confirmButtonColor: '#0b4340'
                        });
                        this.value = filtroMesInicio.value;
                    }
                });
            }
        });
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon.ico">
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Tailwind CSS carregado localmente -->
<?php include 'navbar.php'; ?>

<!-- Header Estendido com Cards e Filtros -->
<div class="hero-header bg-brand-green shadow-lg">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Cards de Perfis (Menores) -->
    <div class="mb-4">
      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
        <!-- Card Organizador -->
        <div class="bg-white rounded-lg p-2 sm:p-2.5 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 shadow-md border border-gray-100">
          <div class="text-center">
            <div class="w-6 h-6 sm:w-7 sm:h-7 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
              <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <h3 class="text-xs font-bold text-gray-900 mb-1">Organizador</h3>
            <a href="organizador-landing.php" class="inline-block w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white px-2 py-1.5 rounded-md text-xs font-semibold hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow-md">
              Criar Evento
            </a>
          </div>
        </div>

        <!-- Card Atleta -->
        <div class="bg-white rounded-lg p-2 sm:p-2.5 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 shadow-md border border-gray-100">
          <div class="text-center">
            <div class="w-6 h-6 sm:w-7 sm:h-7 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
              <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <h3 class="text-xs font-bold text-gray-900 mb-1">Atleta</h3>
            <a href="../auth/login.php" class="inline-block w-full bg-gradient-to-r from-green-500 to-green-600 text-white px-2 py-1.5 rounded-md text-xs font-semibold hover:from-green-600 hover:to-green-700 transition-all duration-200 shadow-sm hover:shadow-md">
              Dashboard
            </a>
          </div>
        </div>

        <!-- Card Assessoria -->
        <div class="bg-white rounded-lg p-2 sm:p-2.5 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 shadow-md border border-gray-100">
          <div class="text-center">
            <div class="w-6 h-6 sm:w-7 sm:h-7 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
              <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
            </div>
            <h3 class="text-xs font-bold text-gray-900 mb-1">Assessoria</h3>
            <a href="assessoria.php" class="inline-block w-full bg-gradient-to-r from-purple-500 to-purple-600 text-white px-2 py-1.5 rounded-md text-xs font-semibold hover:from-purple-600 hover:to-purple-700 transition-all duration-200 shadow-sm hover:shadow-md">
              Conhecer
            </a>
          </div>
        </div>



        <!-- Card Cadeirantes e Visuais -->
        <div class="bg-white rounded-lg p-2 sm:p-2.5 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 shadow-md border border-gray-100">
          <div class="text-center">
            <div class="w-6 h-6 sm:w-7 sm:h-7 bg-indigo-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
              <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="5" r="2" />
                <path d="M12 7v8" />
                <path d="M8 15l4-4 4 4" />
                <circle cx="12" cy="18" r="3" />
                <path d="M8 18h8" />
              </svg>
            </div>
            <h3 class="text-xs font-bold text-gray-900 mb-1">Cadeirante e Visual</h3>
            <a href="acessibilidade.php" class="inline-block w-full bg-gradient-to-r from-indigo-500 to-indigo-600 text-white px-2 py-1.5 rounded-md text-xs font-semibold hover:from-indigo-600 hover:to-indigo-700 transition-all duration-200 shadow-sm hover:shadow-md">
              Conhecer
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros de Pesquisa -->
    <div class="bg-white rounded-xl p-3 sm:p-4 shadow-xl border-2 border-brand-yellow/30">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 sm:gap-3">
        <select
          id="filtro-estado"
          class="w-full pl-3 pr-6 py-2.5 sm:py-2.5 rounded-lg border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm touch-manipulation min-h-[40px]">
          <option value="">Estados</option>
        </select>
        <select
          id="filtro-cidade"
          class="w-full pl-3 pr-6 py-2.5 sm:py-2.5 rounded-lg border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm touch-manipulation min-h-[40px]">
          <option value="">Cidades</option>
        </select>
        <div class="relative">
          <label for="filtro-mes-ano-inicio" class="block text-xs text-gray-600 mb-1">Mês/Ano Início</label>
          <input
            id="filtro-mes-ano-inicio"
            type="month"
            min=""
            class="w-full pl-3 pr-3 py-2.5 sm:py-2.5 rounded-lg border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm touch-manipulation min-h-[40px]">
        </div>
        <div class="relative">
          <label for="filtro-mes-ano-fim" class="block text-xs text-gray-600 mb-1">Mês/Ano Fim</label>
          <input
            id="filtro-mes-ano-fim"
            type="month"
            min=""
            class="w-full pl-3 pr-3 py-2.5 sm:py-2.5 rounded-lg border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm touch-manipulation min-h-[40px]">
        </div>
        <button
          id="btn-aplicar-filtros"
          class="w-full bg-brand-yellow text-brand-green px-4 py-2.5 sm:py-2.5 rounded-lg font-bold hover:bg-yellow-400 active:bg-yellow-500 transition-colors duration-200 flex items-center justify-center space-x-2 text-sm shadow-md hover:shadow-lg touch-manipulation min-h-[40px]">
          <span class="hidden sm:inline">Aplicar Filtros</span>
          <span class="sm:hidden">Filtrar</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</div>

