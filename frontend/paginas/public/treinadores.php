<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = 'Treinadores - MovAmazon';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treinadores - MovAmazon</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo.png">
    <link rel="icon" href="../../assets/img/favicon.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<style>
    body {
        background-color: #f9fafb;
        /* Imagem de fundo removida - usando cor sólida */
    }

    .hero-gradient {
        background: linear-gradient(135deg, #F59E0B 0%, #F97316 50%, #FB923C 100%);
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(245, 158, 11, 0.2);
    }

    .feature-icon {
        background: linear-gradient(135deg, #F59E0B, #F97316);
    }

    .platform-feature {
        background: linear-gradient(135deg, #FEF3C7, #FDE68A);
    }
</style>

<body class="bg-gray-50">
    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Para Treinadores
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-orange-100">
                    Moneteize seu conhecimento e alcance mais atletas
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button onclick="scrollToSection('como-funciona')" class="bg-white text-orange-600 px-8 py-4 rounded-lg font-semibold hover:bg-orange-50 transition-colors">
                        Como Funciona
                    </button>
                    <button onclick="scrollToSection('cadastro')" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-orange-600 transition-colors">
                        Começar Agora
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- O que é a Plataforma -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Transforme sua Experiência em Renda
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Nossa plataforma conecta treinadores qualificados a atletas que buscam orientação profissional,
                        criando uma comunidade de crescimento mútuo.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Por que Escolher Nossa Plataforma?</h3>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-4">
                                <div class="feature-icon w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Monetização Direta</h4>
                                    <p class="text-gray-600">Receba pagamentos diretos pelos seus treinos e consultorias</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="feature-icon w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Alcance Ampliado</h4>
                                    <p class="text-gray-600">Conecte-se com atletas de todo o Brasil</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="feature-icon w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Ferramentas Profissionais</h4>
                                    <p class="text-gray-600">Acesso a recursos avançados para criar treinos personalizados</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="platform-feature p-8 rounded-2xl">
                        <img src="../../assets/img/treinador-hero.jpg" alt="Treinador" class="w-full h-64 object-cover rounded-lg mb-4" onerror="this.style.display='none'">
                        <div class="text-center">
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Plataforma Completa</h4>
                            <p class="text-gray-600">Tudo que você precisa para gerenciar seus atletas e treinos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Como Funciona -->
    <section id="como-funciona" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Como Funciona
                    </h2>
                    <p class="text-xl text-gray-600">
                        Processo simples e direto para começar a monetizar
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Passo 1 -->
                    <div class="text-center">
                        <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-xl">1</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Cadastre-se</h3>
                        <p class="text-gray-600">Crie seu perfil profissional com credenciais e especialidades</p>
                    </div>

                    <!-- Passo 2 -->
                    <div class="text-center">
                        <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-xl">2</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Crie Treinos</h3>
                        <p class="text-gray-600">Desenvolva programas de treino personalizados usando nossas ferramentas</p>
                    </div>

                    <!-- Passo 3 -->
                    <div class="text-center">
                        <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-xl">3</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Defina Preços</h3>
                        <p class="text-gray-600">Estabeleça valores justos para seus serviços e treinos</p>
                    </div>

                    <!-- Passo 4 -->
                    <div class="text-center">
                        <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-xl">4</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Monetize</h3>
                        <p class="text-gray-600">Receba pagamentos e acompanhe seus atletas em tempo real</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recursos da Plataforma -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Recursos da Plataforma
                    </h2>
                    <p class="text-xl text-gray-600">
                        Ferramentas profissionais para maximizar seus resultados
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Recurso 1 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover border border-gray-200">
                        <div class="feature-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Criador de Treinos</h3>
                        <p class="text-gray-600">Interface intuitiva para criar treinos personalizados com exercícios, séries e repetições.</p>
                    </div>

                    <!-- Recurso 2 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover border border-gray-200">
                        <div class="feature-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Dashboard de Performance</h3>
                        <p class="text-gray-600">Acompanhe o progresso dos seus atletas com métricas detalhadas e relatórios.</p>
                    </div>

                    <!-- Recurso 3 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover border border-gray-200">
                        <div class="feature-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Chat Integrado</h3>
                        <p class="text-gray-600">Comunicação direta com seus atletas para orientações e feedback em tempo real.</p>
                    </div>

                    <!-- Recurso 4 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover border border-gray-200">
                        <div class="feature-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Sistema de Pagamentos</h3>
                        <p class="text-gray-600">Receba pagamentos seguros e automáticos pelos seus serviços.</p>
                    </div>

                    <!-- Recurso 5 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover border border-gray-200">
                        <div class="feature-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Treinos em Vídeo</h3>
                        <p class="text-gray-600">Crie e compartilhe vídeos demonstrativos dos exercícios.</p>
                    </div>

                    <!-- Recurso 6 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover border border-gray-200">
                        <div class="feature-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Certificação</h3>
                        <p class="text-gray-600">Valide suas credenciais e destaque-se no mercado.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tipos de Serviços -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Tipos de Serviços
                    </h2>
                    <p class="text-xl text-gray-600">
                        Diversas formas de monetizar seu conhecimento
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Serviço 1 -->
                    <div class="bg-white p-8 rounded-2xl shadow-lg">
                        <div class="text-center mb-6">
                            <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Treinos Personalizados</h3>
                            <p class="text-gray-600">Crie programas específicos para cada atleta</p>
                        </div>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Avaliação individual</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Planejamento periodizado</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Acompanhamento contínuo</span>
                            </li>
                        </ul>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600 mb-2">R$ 89-299</div>
                            <div class="text-gray-500">por mês</div>
                        </div>
                    </div>

                    <!-- Serviço 2 -->
                    <div class="bg-white p-8 rounded-2xl shadow-lg border-2 border-orange-500 relative">
                        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                            <span class="bg-orange-500 text-white px-4 py-1 rounded-full text-sm font-semibold">Mais Popular</span>
                        </div>
                        <div class="text-center mb-6">
                            <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Consultorias Online</h3>
                            <p class="text-gray-600">Orientações via videochamada</p>
                        </div>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Sessões de 1 hora</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Análise de técnica</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Plano de ação</span>
                            </li>
                        </ul>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600 mb-2">R$ 120-250</div>
                            <div class="text-gray-500">por sessão</div>
                        </div>
                    </div>

                    <!-- Serviço 3 -->
                    <div class="bg-white p-8 rounded-2xl shadow-lg">
                        <div class="text-center mb-6">
                            <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Cursos Online</h3>
                            <p class="text-gray-600">Crie e venda cursos especializados</p>
                        </div>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Conteúdo gravado</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Materiais complementares</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Certificado de conclusão</span>
                            </li>
                        </ul>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600 mb-2">R$ 49-199</div>
                            <div class="text-gray-500">por curso</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Requisitos -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Requisitos para Treinadores
                    </h2>
                    <p class="text-xl text-gray-600">
                        Garantimos qualidade através de critérios rigorosos
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-12">
                    <div class="space-y-8">
                        <div class="flex items-start space-x-4">
                            <div class="bg-orange-100 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Formação Acadêmica</h3>
                                <p class="text-gray-600">Graduação em Educação Física ou área relacionada ao esporte</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-orange-100 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Certificações</h3>
                                <p class="text-gray-600">Cursos de especialização em corrida, treinamento funcional ou áreas afins</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-orange-100 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Experiência Prática</h3>
                                <p class="text-gray-600">Mínimo de 2 anos de experiência comprovada em treinamento</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="flex items-start space-x-4">
                            <div class="bg-orange-100 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">CRF Ativo</h3>
                                <p class="text-gray-600">Registro profissional ativo no Conselho Regional de Educação Física</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-orange-100 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Referências</h3>
                                <p class="text-gray-600">Cartas de recomendação de atletas ou colegas de profissão</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-orange-100 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Avaliação Técnica</h3>
                                <p class="text-gray-600">Teste prático para validar conhecimentos e metodologia</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section id="cadastro" class="py-16 bg-orange-600 text-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">
                    Pronto para Começar?
                </h2>
                <p class="text-xl mb-8 text-orange-100">
                    Junte-se à nossa comunidade de treinadores e comece a monetizar seu conhecimento hoje
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button onclick="cadastrarTreinador()" class="bg-white text-orange-600 px-8 py-4 rounded-lg font-semibold hover:bg-orange-50 transition-colors">
                        Cadastrar como Treinador
                    </button>
                    <button onclick="scrollToSection('como-funciona')" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-orange-600 transition-colors">
                        Saber Mais
                    </button>
                </div>
            </div>
        </div>
    </section>

    <script>
        function scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }

        function cadastrarTreinador() {
            Swal.fire({
                title: 'Cadastro de Treinador',
                html: `
          <div class="text-left">
            <p class="mb-4">Você será redirecionado para o cadastro como treinador na plataforma.</p>
            <p class="text-sm text-gray-600">Após o cadastro, nossa equipe entrará em contato para validar suas credenciais.</p>
          </div>
        `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Continuar Cadastro',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#F59E0B'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'auth/register.php?tipo=treinador';
                }
            });
        }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>

</html>
