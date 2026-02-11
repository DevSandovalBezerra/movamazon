<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../../api/db.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    header('Location: ../../../auth/login.php');
    exit();
}

$pageTitle = 'programacao';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programação - MovAmazon</title>
    <!-- TODO: Em produção, usar versão local do Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/custom.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50">
    <div class="p-4 sm:p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Navegação Simples -->
            <div class="mb-4 sm:mb-6 flex gap-2 sm:gap-4">
                <a href="?page=produtos-extras" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition-colors text-xs sm:text-sm">
                    ← Produtos Extras
                </a>
                <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
                    12- Checklist
                </span>
                <span class="bg-gray-300 text-gray-500 px-4 py-2 rounded-lg cursor-not-allowed">
                    Fim da Sequência
                </span>
            </div>

            <!-- Cabeçalho -->
            <div class="mb-6 sm:mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center space-x-2 mb-2">
                            <a href="../index.php" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-arrow-left"></i> Dashboard
                            </a>
                            <span class="text-gray-400">/</span>
                            <span class="font-semibold">Programação</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Configuração do Evento</h1>
                        <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Configure as informações essenciais do seu evento de corrida</p>
                    </div>
                    <div class="flex space-x-3">
                        <button id="btnAlternarModo" onclick="alternarModo()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg flex items-center text-sm">
                            <i class="fas fa-edit mr-2"></i>
                            <span id="textoModo">Editar</span>
                        </button>
                        <button id="btnSalvar" onclick="salvarEvento()" class="hidden bg-green-600 hover:bg-green-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg flex items-center text-sm">
                            <i class="fas fa-save mr-2"></i>
                            Salvar
                        </button>
                        <button id="btnCancelar" onclick="cancelarEdicao()" class="hidden bg-gray-600 hover:bg-gray-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg flex items-center text-sm">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
                        <select id="filtro-evento" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
                            <option value="">Selecione um evento</option>
                        </select>
                    </div>
                    <div class="sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="filtro-status" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm">
                            <option value="">Todos</option>
                            <option value="ativo">Ativos</option>
                            <option value="inativo">Inativos</option>
                        </select>
                    </div>
                    <div class="flex items-end sm:col-span-2 lg:col-span-1">
                        <button onclick="aplicarFiltros()" class="w-full bg-brand-green hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg flex items-center justify-center transition text-xs sm:text-sm">
                            <i class="fas fa-search mr-2"></i>
                            <span class="hidden sm:inline">Filtrar</span>
                            <span class="sm:hidden">Filtrar</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formulário de Evento Integrado -->
            <div class="max-w-5xl mx-auto p-4 sm:p-8 bg-white rounded-lg shadow mt-8">
                <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">Informações Gerais do Evento</h2>
                <form>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Evento <span class="text-gray-400 text-xs">(máx. 121 caracteres)</span></label>
                            <input type="text" id="nomeEvento" class="w-full border border-gray-300 rounded-lg px-3 py-2" maxlength="121" placeholder="Nome do evento" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Logotipo do evento</label>
                            <div class="flex items-center space-x-4">
                                <div id="imagem-container" class="relative w-32 h-20 bg-gray-100 border-2 border-dashed border-gray-300 rounded overflow-hidden">
                                    <i id="imagem-placeholder" class="absolute inset-0 z-0 flex items-center justify-center fas fa-image text-gray-400 text-2xl"></i>
                                    <img id="imagem-evento" class="hidden absolute inset-0 z-10 w-full h-full object-cover bg-white" alt="Imagem do evento">
                                </div>
                                <input type="file" id="inputImagemEvento" accept="image/jpeg,image/png,image/webp" class="hidden">
                                <button type="button" id="btnAlterarImagemEvento" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">Alterar Imagem</button>
                                <button type="button" id="btnExcluirImagemEvento" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">Excluir</button>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                            <select id="categoriaEvento" class="w-full border border-gray-300 rounded-lg px-3 py-2" disabled>
                                <option value="">Selecione</option>
                                <option value="corrida_rua">Corrida de Rua</option>
                                <option value="caminhada">Caminhada</option>
                                <option value="bike">Bike</option>
                                <option value="kids">Kids</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dia de realização do evento</label>
                            <input type="date" id="dataInicio" class="w-full border border-gray-300 rounded-lg px-3 py-2" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Horário de início do evento</label>
                            <input type="time" id="horaInicio" class="w-full border border-gray-300 rounded-lg px-3 py-2" disabled>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gênero</label>
                            <select id="generoEvento" class="w-full border border-gray-300 rounded-lg px-3 py-2" disabled>
                                <option value="">Selecione</option>
                                <option value="masculino">Masculino</option>
                                <option value="feminino">Feminino</option>
                                <option value="misto">Misto</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Realização</label>
                            <input type="date" id="dataRealizacao" class="w-full border border-gray-300 rounded-lg px-3 py-2" disabled>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição do Evento</label>
                        <textarea id="descricaoEvento" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Descrição detalhada do evento" disabled></textarea>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Regulamento</label>

                        <!-- Placeholder (mesmo padrão das Solicitações: '-' quando não existe) -->
                        <div id="regulamento-placeholder" class="text-gray-600 text-sm mb-3">-</div>
                        
                        <!-- Se existe arquivo: mostrar link -->
                        <div id="regulamento-arquivo-existente" class="hidden mb-3">
                            <div class="flex items-center space-x-3">
                                <a id="link-regulamento" href="#" target="_blank" class="text-brand-green underline flex items-center">
                                    <i class="fas fa-file-pdf mr-1"></i>
                                    <span id="nome-arquivo-regulamento"></span>
                                </a>
                                <button id="btn-substituir-regulamento" type="button" class="hidden text-sm text-gray-600 hover:text-gray-800 flex items-center underline">
                                    <i class="fas fa-edit mr-1"></i>Alterar
                                </button>
                            </div>
                        </div>
                        
                        <!-- Input de upload (sempre visível em modo edição) -->
                        <div id="regulamento-upload-container" class="hidden">
                            <input type="file" id="regulamentoArquivo" name="regulamento_arquivo" 
                                   accept=".pdf,.doc,.docx" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">
                                Formatos aceitos: PDF, DOC, DOCX. Tamanho máximo: 10MB
                            </p>
                        </div>
                        
                        <!-- Mensagem de confirmação para substituição -->
                        <div id="regulamento-confirmacao-substituir" class="hidden mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Um arquivo já existe. Ao salvar, o arquivo atual será substituído.
                            </p>
                        </div>
                    </div>

                    <!-- LOCAL DO EVENTO -->
                    <h3 class="text-lg font-bold mb-4 mt-8">Local do Evento</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Local</label>
                            <input type="text" id="localEvento" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Local" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cep</label>
                            <input type="text" id="cepEvento" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Cep" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Url Mapa</label>
                            <input type="text" id="urlMapa" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Url Mapa" disabled>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                            <input type="text" id="logradouro" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Logradouro" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                            <input type="text" id="numero" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Número" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                            <select id="cidadeEvento" class="w-full border border-gray-300 rounded-lg px-3 py-2" disabled>
                                <option value="">Selecione</option>
                                <option value="Manaus">Manaus</option>
                                <option value="São Paulo">São Paulo</option>
                                <option value="Rio de Janeiro">Rio de Janeiro</option>
                                <option value="Belo Horizonte">Belo Horizonte</option>
                                <option value="Curitiba">Curitiba</option>
                                <option value="Porto Alegre">Porto Alegre</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">País</label>
                            <select id="paisEvento" class="w-full border border-gray-300 rounded-lg px-3 py-2" disabled>
                                <option value="Brasil">Brasil</option>
                                <option value="Argentina">Argentina</option>
                                <option value="Uruguai">Uruguai</option>
                                <option value="Chile">Chile</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select id="estadoEvento" class="w-full border border-gray-300 rounded-lg px-3 py-2" disabled>
                                <option value="">Selecione</option>
                                <option value="Amazonas">Amazonas</option>
                                <option value="SP">SP</option>
                                <option value="RJ">RJ</option>
                                <option value="MG">MG</option>
                                <option value="RS">RS</option>
                                <option value="PR">PR</option>
                            </select>
                        </div>
                    </div>



                    <!-- MODALIDADES E VALORES -->
                    <h3 class="text-lg font-bold mb-4 mt-8">Modalidades e Valores</h3>
                    <div class="mb-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <p class="text-blue-800 text-sm">Configure as modalidades e valores de inscrição para cada distância.</p>
                            </div>
                        </div>

                        <!-- Container dinâmico para modalidades -->
                        <div id="modalidades-container" class="space-y-4">
                            <!-- Será preenchido dinamicamente pelo JavaScript -->
                            <div id="modalidades-loading" class="text-center py-8">
                                <div class="inline-flex items-center">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-2"></div>
                                    <span class="text-gray-600">Carregando modalidades...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Mensagem quando não há evento selecionado -->
                        <div id="modalidades-empty" class="hidden text-center py-8">
                            <div class="text-gray-500">
                                <i class="fas fa-info-circle text-2xl mb-2"></i>
                                <p>Selecione um evento para visualizar as modalidades e valores</p>
                            </div>
                        </div>
                    </div>

                    <!-- INSCRIÇÕES -->
                    <h3 class="text-lg font-bold mb-4 mt-8">Período de Inscrições</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Qtd de vagas totais do evento</label>
                            <div class="flex items-center space-x-2 mb-2">
                                <input type="radio" name="vagas" value="ilimitado" id="vagasIlimitado" disabled>
                                <label for="vagasIlimitado">Ilimitada</label>
                                <input type="radio" name="vagas" value="limitado" id="vagasLimitado" checked disabled>
                                <label for="vagasLimitado">Limite de vagas</label>
                            </div>
                            <input type="number" id="limiteVagas" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Número de vagas" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de término das inscrições</label>
                            <input type="date" id="dataFimInscricoes" class="w-full border border-gray-300 rounded-lg px-3 py-2" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Horário de término das inscrições</label>
                            <input type="time" id="horaFimInscricoes" class="w-full border border-gray-300 rounded-lg px-3 py-2" disabled>
                        </div>
                    </div>

                    <!-- ORGANIZADOR -->
                    <h3 class="text-lg font-bold mb-4 mt-8">Organizador</h3>
                    <div class="grid grid-cols-1 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Responsável pelo evento <span class="text-gray-400 text-xs">(?)</span></label>
                            <input type="text" id="organizadorResponsavel" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Nome do organizador" disabled>
                        </div>
                    </div>

                    <!-- TAXAS -->
                    <h3 class="text-lg font-bold mb-4 mt-8">Taxas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Taxa mínima cobrada do organizador para inscrições (gratuitas e cupom/reserva 100%)</label>
                            <div class="flex items-center">
                                <span class="mr-2">R$</span>
                                <input type="number" step="0.01" id="taxaGratuitas" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="2,50" disabled>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Taxa mínima cobrada do organizador para inscrições pagas de qualquer valor maior que 0</label>
                            <div class="flex items-center">
                                <span class="mr-2">R$</span>
                                <input type="number" step="0.01" id="taxaPagas" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="3,00" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Taxa de setup do evento</label>
                            <div class="flex items-center">
                                <span class="mr-2">R$</span>
                                <input type="number" step="0.01" id="taxaSetup" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="129,90" disabled>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Percentual máximo para repasse no evento</label>
                            <div class="flex items-center">
                                <input type="number" step="0.01" id="percentualRepasse" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="85,00" disabled>
                                <span class="ml-2">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- PROGRAMAÇÃO DO EVENTO -->
                    <h3 class="text-lg font-bold mb-4 mt-8">Programação do Evento</h3>
                    <div class="mb-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                    <p class="text-blue-800 text-sm">Gerencie os itens da programação do evento (percurso, horários de largada, atividades adicionais).</p>
                                </div>
                                <button id="btnAdicionarProgramacao" onclick="adicionarItemProgramacao()" type="button" class="hidden bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-plus mr-2"></i>
                                    Adicionar Item
                                </button>
                            </div>
                        </div>

                        <!-- Container para lista de programação -->
                        <div id="programacao-container" class="space-y-3">
                            <div id="programacao-loading" class="text-center py-8">
                                <div class="inline-flex items-center">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-2"></div>
                                    <span class="text-gray-600">Carregando programação...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Mensagem quando não há evento selecionado -->
                        <div id="programacao-empty" class="hidden text-center py-8">
                            <div class="text-gray-500">
                                <i class="fas fa-info-circle text-2xl mb-2"></i>
                                <p>Selecione um evento para gerenciar a programação</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 mt-8">
                        <button id="btnSalvarForm" type="button" onclick="salvarEvento()" class="hidden bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                            <i class="fas fa-save mr-2"></i>
                            Salvar Evento
                        </button>
                    </div>
            </div>
        </div>


        <!-- URL da imagem do evento (base única) -->
        <script src="../../js/utils/eventImageUrl.js"></script>
        <script src="../../js/programacao.js"></script>
</body>

</html>
