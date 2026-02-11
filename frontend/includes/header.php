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

    <!-- CSS Mobile-First -->
    <link rel="stylesheet" href="../../assets/css/mobile-only.css">
    
    <!-- CSS customizado inline para evitar problemas de caminho -->
    <style>
        /* Custom CSS para complementar o Tailwind CSS e mobile */

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
    </style>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon.ico">
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Tailwind CSS carregado localmente -->
