<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = 'Assessoria de Corrida - MovAmazon';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessoria de Corrida - MovAmazon</title>
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
        background: linear-gradient(135deg, #8B5CF6 0%, #A855F7 50%, #C084FC 100%);
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(139, 92, 246, 0.2);
    }

    .benefit-icon {
        background: linear-gradient(135deg, #8B5CF6, #A855F7);
    }
</style>

<body class="bg-gray-50">
    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Assessoria de Corrida
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-purple-100">
                    Transforme seus treinos com acompanhamento profissional especializado
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button onclick="scrollToSection('beneficios')" class="bg-white text-purple-600 px-8 py-4 rounded-lg font-semibold hover:bg-purple-50 transition-colors">
                        Conhecer Benefícios
                    </button>
                    <button onclick="scrollToSection('cadastro')" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-purple-600 transition-colors">
                        Começar Agora
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- O que é Assessoria -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        O que é Assessoria de Corrida?
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        A assessoria de corrida é um serviço personalizado que oferece acompanhamento técnico,
                        planejamento de treinos e suporte completo para atletas de todos os níveis.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Nossa Metodologia</h3>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-4">
                                <div class="benefit-icon w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Avaliação Individual</h4>
                                    <p class="text-gray-600">Análise completa do seu perfil físico e objetivos</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="benefit-icon w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Planejamento Personalizado</h4>
                                    <p class="text-gray-600">Treinos adaptados à sua rotina e metas específicas</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="benefit-icon w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Acompanhamento Contínuo</h4>
                                    <p class="text-gray-600">Monitoramento constante do seu progresso e ajustes necessários</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-8 rounded-2xl">
                        <img src="../../assets/img/assessoria-hero.jpg" alt="Assessoria de Corrida" class="w-full h-64 object-cover rounded-lg mb-4" onerror="this.style.display='none'">
                        <div class="text-center">
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Resultados Comprovados</h4>
                            <p class="text-gray-600">Mais de 500 atletas já transformaram sua performance conosco</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefícios -->
    <section id="beneficios" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Benefícios da Assessoria
                    </h2>
                    <p class="text-xl text-gray-600">
                        Descubra como nossa assessoria pode acelerar seus resultados
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Benefício 1 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                        <div class="benefit-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Melhoria de Performance</h3>
                        <p class="text-gray-600">Aumente sua velocidade, resistência e eficiência na corrida com treinos específicos e periodizados.</p>
                    </div>

                    <!-- Benefício 2 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                        <div class="benefit-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Prevenção de Lesões</h3>
                        <p class="text-gray-600">Treinos seguros e progressivos que respeitam seus limites e previnem lesões comuns.</p>
                    </div>

                    <!-- Benefício 3 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                        <div class="benefit-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Economia de Tempo</h3>
                        <p class="text-gray-600">Treinos otimizados que maximizam seus resultados em menos tempo, adaptados à sua agenda.</p>
                    </div>

                    <!-- Benefício 4 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                        <div class="benefit-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Suporte Personalizado</h3>
                        <p class="text-gray-600">Acompanhamento individual com feedback constante e ajustes personalizados.</p>
                    </div>

                    <!-- Benefício 5 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                        <div class="benefit-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Acompanhamento de Dados</h3>
                        <p class="text-gray-600">Análise detalhada de seus treinos com métricas e evolução do seu desempenho.</p>
                    </div>

                    <!-- Benefício 6 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                        <div class="benefit-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Motivação Constante</h3>
                        <p class="text-gray-600">Suporte emocional e motivação para manter a consistência nos treinos e alcançar seus objetivos.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Responsabilidades -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Nossas Responsabilidades
                    </h2>
                    <p class="text-xl text-gray-600">
                        Compromisso total com o seu sucesso e bem-estar
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-12">
                    <div class="space-y-8">
                        <div class="flex items-start space-x-4">
                            <div class="bg-purple-100 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <span class="text-purple-600 font-bold text-sm">1</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Avaliação Inicial Completa</h3>
                                <p class="text-gray-600">Análise detalhada do seu histórico, objetivos, limitações e condicionamento atual.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-purple-100 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <span class="text-purple-600 font-bold text-sm">2</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Planejamento Estratégico</h3>
                                <p class="text-gray-600">Desenvolvimento de um plano de treinos personalizado com metas realistas e progressivas.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-purple-100 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <span class="text-purple-600 font-bold text-sm">3</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Monitoramento Contínuo</h3>
                                <p class="text-gray-600">Acompanhamento regular do seu progresso com ajustes necessários no plano de treinos.</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="flex items-start space-x-4">
                            <div class="bg-purple-100 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <span class="text-purple-600 font-bold text-sm">4</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Suporte Técnico</h3>
                                <p class="text-gray-600">Orientações sobre técnica de corrida, equipamentos e estratégias de prova.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-purple-100 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <span class="text-purple-600 font-bold text-sm">5</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Preparação para Provas</h3>
                                <p class="text-gray-600">Estratégias específicas para competições e eventos, incluindo táticas de prova.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-purple-100 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <span class="text-purple-600 font-bold text-sm">6</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Suporte Nutricional</h3>
                                <p class="text-gray-600">Orientações básicas sobre alimentação e hidratação para otimizar seus treinos.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Planos -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Escolha seu Plano
                    </h2>
                    <p class="text-xl text-gray-600">
                        Planos flexíveis para atender suas necessidades
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Plano Básico -->
                    <div class="bg-white p-8 rounded-2xl shadow-lg">
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Básico</h3>
                            <div class="text-4xl font-bold text-purple-600 mb-2">R$ 89</div>
                            <div class="text-gray-500">/mês</div>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Plano de treinos personalizado</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Avaliação inicial</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Suporte por WhatsApp</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Ajustes mensais</span>
                            </li>
                        </ul>
                        <button onclick="cadastrarAssessoria('basico')" class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                            Escolher Plano
                        </button>
                    </div>

                    <!-- Plano Premium -->
                    <div class="bg-white p-8 rounded-2xl shadow-lg border-2 border-purple-500 relative">
                        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                            <span class="bg-purple-500 text-white px-4 py-1 rounded-full text-sm font-semibold">Mais Popular</span>
                        </div>
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Premium</h3>
                            <div class="text-4xl font-bold text-purple-600 mb-2">R$ 149</div>
                            <div class="text-gray-500">/mês</div>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Tudo do plano Básico</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Acompanhamento semanal</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Análise de dados de treino</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Orientações nutricionais</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Preparação para provas</span>
                            </li>
                        </ul>
                        <button onclick="cadastrarAssessoria('premium')" class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                            Escolher Plano
                        </button>
                    </div>

                    <!-- Plano Elite -->
                    <div class="bg-white p-8 rounded-2xl shadow-lg">
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Elite</h3>
                            <div class="text-4xl font-bold text-purple-600 mb-2">R$ 249</div>
                            <div class="text-gray-500">/mês</div>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Tudo do plano Premium</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Acompanhamento diário</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Consultoria nutricional</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Treinos presenciais (1x/semana)</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600">Suporte 24/7</span>
                            </li>
                        </ul>
                        <button onclick="cadastrarAssessoria('elite')" class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                            Escolher Plano
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section id="cadastro" class="py-16 bg-purple-600 text-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">
                    Pronto para Transformar sua Corrida?
                </h2>
                <p class="text-xl mb-8 text-purple-100">
                    Junte-se a centenas de atletas que já alcançaram seus objetivos conosco
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button onclick="cadastrarAssessoria('premium')" class="bg-white text-purple-600 px-8 py-4 rounded-lg font-semibold hover:bg-purple-50 transition-colors">
                        Começar Agora
                    </button>
                    <button onclick="scrollToSection('beneficios')" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-purple-600 transition-colors">
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

        function cadastrarAssessoria(plano) {
            Swal.fire({
                title: 'Cadastro de Assessoria',
                html: `
          <div class="text-left">
            <p class="mb-4">Você será redirecionado para o cadastro como atleta para ter acesso aos serviços de assessoria.</p>
            <p class="text-sm text-gray-600">Plano selecionado: <strong>${plano.charAt(0).toUpperCase() + plano.slice(1)}</strong></p>
          </div>
        `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Continuar Cadastro',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#8B5CF6'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'auth/register.php?tipo=assessoria&plano=' + plano;
                }
            });
        }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>

</html>
