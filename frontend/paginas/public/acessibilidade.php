<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = 'Acessibilidade - MovAmazon';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acessibilidade - MovAmazon</title>
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
        background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 50%, #A855F7 100%);
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(99, 102, 241, 0.2);
    }

    .accessibility-icon {
        background: linear-gradient(135deg, #6366F1, #8B5CF6);
    }

    .support-card {
        background: linear-gradient(135deg, #F0F4FF, #E0E7FF);
    }
</style>

<body class="bg-gray-50">
    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Corrida Inclusiva
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-indigo-100">
                    Todos têm direito ao esporte. Vamos correr juntos!
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button onclick="scrollToSection('servicos')" class="bg-white text-indigo-600 px-8 py-4 rounded-lg font-semibold hover:bg-indigo-50 transition-colors">
                        Conhecer Serviços
                    </button>
                    <button onclick="scrollToSection('cadastro')" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition-colors">
                        Participar
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Missão -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Nossa Missão
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Promover a inclusão no esporte através de serviços especializados e apoio completo
                        para atletas com deficiência e seus acompanhantes.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Acessibilidade é Prioridade</h3>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-4">
                                <div class="accessibility-icon w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Inclusão Real</h4>
                                    <p class="text-gray-600">Eventos adaptados para todas as necessidades especiais</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="accessibility-icon w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Suporte Especializado</h4>
                                    <p class="text-gray-600">Equipe treinada para atender necessidades específicas</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="accessibility-icon w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Infraestrutura Adaptada</h4>
                                    <p class="text-gray-600">Locais preparados com acessibilidade completa</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="support-card p-8 rounded-2xl">
                        <img src="../../assets/img/acessibilidade-hero.jpg" alt="Corrida Inclusiva" class="w-full h-64 object-cover rounded-lg mb-4" onerror="this.style.display='none'">
                        <div class="text-center">
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Juntos Somos Mais Fortes</h4>
                            <p class="text-gray-600">Cada atleta é único e merece o melhor suporte</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Serviços -->
    <section id="servicos" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Nossos Serviços
                    </h2>
                    <p class="text-xl text-gray-600">
                        Suporte completo para atletas com deficiência
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Serviço 1 - Guias Visuais -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                        <div class="accessibility-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Guias Visuais</h3>
                        <p class="text-gray-600 mb-4">Acompanhantes treinados para atletas com deficiência visual</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li>• Cadastro de guias voluntários</li>
                            <li>• Treinamento especializado</li>
                            <li>• Pareamento por compatibilidade</li>
                            <li>• Acompanhamento em provas</li>
                        </ul>
                    </div>

                    <!-- Serviço 2 - Suporte para Cadeirantes -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                        <div class="accessibility-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Suporte Cadeirantes</h3>
                        <p class="text-gray-600 mb-4">Infraestrutura e apoio para atletas em cadeira de rodas</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li>• Locais para guardar cadeiras</li>
                            <li>• Assistência técnica especializada</li>
                            <li>• Rotas adaptadas</li>
                            <li>• Equipamentos de apoio</li>
                        </ul>
                    </div>

                    <!-- Serviço 3 - Acompanhantes -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                        <div class="accessibility-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Acompanhantes</h3>
                        <p class="text-gray-600 mb-4">Voluntários treinados para apoio durante as provas</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li>• Cadastro de voluntários</li>
                            <li>• Treinamento específico</li>
                            <li>• Suporte emocional</li>
                            <li>• Acompanhamento completo</li>
                        </ul>
                    </div>

                    <!-- Serviço 4 - Infraestrutura -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                        <div class="accessibility-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Infraestrutura Adaptada</h3>
                        <p class="text-gray-600 mb-4">Locais preparados com acessibilidade completa</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li>• Banheiros adaptados</li>
                            <li>• Rampas de acesso</li>
                            <li>• Sinalização tátil</li>
                            <li>• Áreas de descanso</li>
                        </ul>
                    </div>

                    <!-- Serviço 5 - Equipamentos -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                        <div class="accessibility-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Equipamentos Especiais</h3>
                        <p class="text-gray-600 mb-4">Disponibilização de equipamentos adaptados</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li>• Cadeiras de rodas esportivas</li>
                            <li>• Cordas guia</li>
                            <li>• Equipamentos de proteção</li>
                            <li>• Materiais adaptados</li>
                        </ul>
                    </div>

                    <!-- Serviço 6 - Suporte Técnico -->
                    <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                        <div class="accessibility-icon w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Suporte Técnico</h3>
                        <p class="text-gray-600 mb-4">Assistência técnica especializada durante as provas</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li>• Mecânicos especializados</li>
                            <li>• Peças de reposição</li>
                            <li>• Reparos emergenciais</li>
                            <li>• Manutenção preventiva</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Processo de Cadastro -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Como Participar
                    </h2>
                    <p class="text-xl text-gray-600">
                        Processo simples e acolhedor para todos
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Passo 1 -->
                    <div class="text-center">
                        <div class="accessibility-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-xl">1</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Cadastre-se</h3>
                        <p class="text-gray-600">Preencha o formulário com suas informações e necessidades específicas</p>
                    </div>

                    <!-- Passo 2 -->
                    <div class="text-center">
                        <div class="accessibility-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-xl">2</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Avaliação</h3>
                        <p class="text-gray-600">Nossa equipe analisa suas necessidades e define o melhor suporte</p>
                    </div>

                    <!-- Passo 3 -->
                    <div class="text-center">
                        <div class="accessibility-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-xl">3</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Pareamento</h3>
                        <p class="text-gray-600">Conectamos você com guias, acompanhantes e suporte adequado</p>
                    </div>

                    <!-- Passo 4 -->
                    <div class="text-center">
                        <div class="accessibility-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-xl">4</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Participação</h3>
                        <p class="text-gray-600">Participe das provas com todo o suporte necessário</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Depoimentos -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Histórias de Sucesso
                    </h2>
                    <p class="text-xl text-gray-600">
                        Conheça quem já transformou sua vida através do esporte inclusivo
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Depoimento 1 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <span class="text-indigo-600 font-bold">M</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">Maria Silva</h4>
                                <p class="text-sm text-gray-600">Atleta com deficiência visual</p>
                            </div>
                        </div>
                        <p class="text-gray-600 italic">
                            "Com o apoio dos guias voluntários, consegui completar minha primeira meia maratona.
                            A experiência foi transformadora!"
                        </p>
                    </div>

                    <!-- Depoimento 2 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <span class="text-indigo-600 font-bold">J</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">João Santos</h4>
                                <p class="text-sm text-gray-600">Atleta cadeirante</p>
                            </div>
                        </div>
                        <p class="text-gray-600 italic">
                            "A infraestrutura adaptada e o suporte técnico me permitiram participar de provas
                            que antes pareciam impossíveis."
                        </p>
                    </div>

                    <!-- Depoimento 3 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <span class="text-indigo-600 font-bold">A</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">Ana Costa</h4>
                                <p class="text-sm text-gray-600">Guia voluntária</p>
                            </div>
                        </div>
                        <p class="text-gray-600 italic">
                            "Ser guia voluntária mudou minha perspectiva sobre inclusão.
                            É uma experiência gratificante e enriquecedora."
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section id="cadastro" class="py-16 bg-indigo-600 text-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">
                    Pronto para Correr Conosco?
                </h2>
                <p class="text-xl mb-8 text-indigo-100">
                    Faça parte da nossa comunidade inclusiva e descubra o poder transformador do esporte
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button onclick="cadastrarAtleta()" class="bg-white text-indigo-600 px-8 py-4 rounded-lg font-semibold hover:bg-indigo-50 transition-colors">
                        Cadastrar como Atleta
                    </button>
                    <button onclick="cadastrarVoluntario()" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition-colors">
                        Ser Voluntário
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

        function cadastrarAtleta() {
            Swal.fire({
                title: 'Cadastro de Atleta',
                html: `
          <div class="text-left">
            <p class="mb-4">Você será redirecionado para o cadastro como atleta com necessidades especiais.</p>
            <p class="text-sm text-gray-600">Após o cadastro, nossa equipe entrará em contato para entender suas necessidades específicas.</p>
          </div>
        `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Continuar Cadastro',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#6366F1'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'auth/register.php?tipo=atleta_acessibilidade';
                }
            });
        }

        function cadastrarVoluntario() {
            Swal.fire({
                title: 'Cadastro de Voluntário',
                html: `
          <div class="text-left">
            <p class="mb-4">Você será redirecionado para o cadastro como voluntário.</p>
            <p class="text-sm text-gray-600">Como voluntário, você pode ser guia, acompanhante ou oferecer suporte técnico.</p>
          </div>
        `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Continuar Cadastro',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#6366F1'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'auth/register.php?tipo=voluntario_acessibilidade';
                }
            });
        }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>

</html>
