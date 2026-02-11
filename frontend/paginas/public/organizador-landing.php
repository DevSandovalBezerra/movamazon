<?php
$pageTitle = 'MovAmazon - Para Organizadores';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<!-- Hero Section -->
<section class="relative text-brand-green py-8 sm:py-16 lg:py-24 overflow-hidden">
  <!-- Background Pattern -->
  <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-20" style="background-image: url('../../assets/img/sinara.jpg');"></div>
  <!-- Overlay -->
  <div class="absolute inset-0"></div>
  <div class="relative max-w-7xl mx-auto px-3 sm:px-4 lg:px-8">
    <div class="text-center mb-8 sm:mb-12">
      <h1 class="text-lg sm:text-2xl md:text-3xl lg:text-4xl xl:text-6xl font-bold mb-4 sm:mb-6 leading-tight">
        Acelere a venda e faça gestão das inscrições do seu evento com o
        <span class="text-lg sm:text-2xl md:text-3xl lg:text-4xl xl:text-6xl font-bold tracking-tight">
          <span class="text-green-500">MOV</span><span class="text-brand-yellow">Amazon</span>
        </span>!
      </h1>
      <p class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl mb-6 sm:mb-8 text-brand-red max-w-3xl mx-auto px-2 sm:px-4">
        Preencha o formulário e entraremos em contato com você 😊
      </p>
    </div>

    <!-- Grid com imagens e formulário -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
      <!-- Imagens dos corredores -->
      <div class="space-y-6 md:space-y-12">
        <div class="relative">
          <img src="../../assets/img/campo-verde.webp" alt="Campo-verde" class="w-full h-48 md:h-64 object-cover rounded-2xl shadow-2xl">
          <div class="absolute bottom-4 left-4 bg-brand-yellow/95 backdrop-blur-sm rounded-lg p-3">
            <p class="text-sm font-semibold text-brand-green">Eventos Profissionais</p>
            <p class="text-xs text-brand-green/80">Gestão completa de inscrições</p>
          </div>
        </div>

        <div class="relative">
          <img src="../../assets/img/pexels-run-SC.jpg" alt="Corrida SC" class="w-full h-48 md:h-64 object-cover rounded-2xl shadow-2xl">
          <div class="absolute bottom-4 left-4 bg-brand-red/95 backdrop-blur-sm rounded-lg p-3">
            <p class="text-sm font-semibold text-white">Múltiplas Modalidades</p>
            <p class="text-xs text-white/80">5km, 10km, 21km, 42km</p>
          </div>
        </div>

      </div>


      <!-- Formulário -->
      <div class="bg-gradient-to-br from-brand-green to-green-600 text-white rounded-lg sm:rounded-xl lg:rounded-2xl p-4 sm:p-6 md:p-8 border border-white/50">
        <form id="organizadorForm" class="space-y-4 sm:space-y-6">
          <h3 class="text-xl font-semibold text-center">Dados do responsável</h3>
          <!-- Nome Completo -->
          <div>
            <label for="nome" class="block text-sm font-medium text-white mb-2 text-center">
              Nome completo *
            </label>
            <input type="text" id="nome" name="nome" required
              class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-brand-yellow focus:outline-none text-sm sm:text-base text-center">
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-white mb-2 text-center">
              E-mail *
            </label>
            <input type="email" id="email" name="email" required
              class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-brand-yellow focus:outline-none text-sm sm:text-base text-center">
          </div>

          <!-- Telefone -->
          <div>
            <label for="telefone" class="block text-sm font-medium text-white mb-2 text-center">
              Telefone *
            </label>
            <div class="flex">
              <div class="relative">
                <select name="telefone_ddi" class="px-2 sm:px-3 py-2 sm:py-3 rounded-l-lg sm:rounded-l-xl border-0 bg-gray/90 backdrop-blur-sm text-gray-900 focus:ring-2 focus:ring-brand-yellow focus:outline-none appearance-none text-sm sm:text-base">
                  <option value="+55">🇧🇷 +55</option>
                  <option value="+1">🇺🇸 +1</option>
                  <option value="+44">🇬🇧 +44</option>
                </select>
                <svg class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 w-3 h-3 sm:w-4 sm:h-4 text-gray-900/80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M19 9l-7 7-7-7" />
                </svg>
              </div>
              <input type="tel" id="telefone" name="telefone" required
                class="flex-1 px-3 sm:px-4 py-2 sm:py-3 rounded-r-lg sm:rounded-r-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-brand-yellow focus:outline-none text-sm sm:text-base text-center"
                placeholder="(11) 99999-9999">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label for="documento" class="block text-sm font-medium text-white mb-2 text-center">
                CPF/CNPJ do responsável *
              </label>
              <input type="text" id="documento" name="documento" required
                class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 text-center">
            </div>
            <div>
              <label for="rg" class="block text-sm font-medium text-white mb-2 text-center">
                RG
              </label>
              <input type="text" id="rg" name="rg"
                class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 text-center">
            </div>
            <div>
              <label for="cargo" class="block text-sm font-medium text-white mb-2 text-center">
                Cargo na organização
              </label>
              <input type="text" id="cargo" name="cargo"
                class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 text-center">
            </div>
          </div>

          <!-- Empresa -->
          <div>
            <label for="empresa" class="block text-sm font-medium text-white mb-2 text-center">
              Empresa *
            </label>
            <input type="text" id="empresa" name="empresa" required
              class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-brand-yellow focus:outline-none text-center">
          </div>

          <!-- Região -->
          <div>
            <label for="regiao" class="block text-sm font-medium text-white mb-2 text-center">
              Região *
            </label>
            <select id="regiao" name="regiao" required
              class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 focus:ring-2 focus:ring-brand-yellow focus:outline-none appearance-none text-center">
              <option value="">Selecione</option>
              <option value="AC">Acre</option>
              <option value="AL">Alagoas</option>
              <option value="AP">Amapá</option>
              <option value="AM">Amazonas</option>
              <option value="BA">Bahia</option>
              <option value="CE">Ceará</option>
              <option value="DF">Distrito Federal</option>
              <option value="ES">Espírito Santo</option>
              <option value="GO">Goiás</option>
              <option value="MA">Maranhão</option>
              <option value="MT">Mato Grosso</option>
              <option value="MS">Mato Grosso do Sul</option>
              <option value="MG">Minas Gerais</option>
              <option value="PA">Pará</option>
              <option value="PB">Paraíba</option>
              <option value="PR">Paraná</option>
              <option value="PE">Pernambuco</option>
              <option value="PI">Piauí</option>
              <option value="RJ">Rio de Janeiro</option>
              <option value="RN">Rio Grande do Norte</option>
              <option value="RS">Rio Grande do Sul</option>
              <option value="RO">Rondônia</option>
              <option value="RR">Roraima</option>
              <option value="SC">Santa Catarina</option>
              <option value="SP">São Paulo</option>
              <option value="SE">Sergipe</option>
              <option value="TO">Tocantins</option>
            </select>
          </div>

          <h3 class="text-xl font-semibold text-center pt-4">Dados do evento</h3>

          <!-- Modalidade Esportiva -->
          <div>
            <label for="modalidade" class="block text-sm font-medium text-white mb-2">
              Qual é a modalidade esportiva do seu evento? *
            </label>
            <select id="modalidade" name="modalidade" required
              class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 focus:ring-2 focus:ring-brand-yellow focus:outline-none appearance-none">
              <option value="">Selecione</option>
              <option value="corrida-rua">Corrida de Rua / Caminhada</option>
              <option value="ciclismo">Ciclismo / MTB</option>
              <option value="obstaculos">Corrida de Obstáculos</option>
              <option value="kids">Corrida Kids</option>
              <option value="canoagem">Canoagem VAA</option>
              <option value="virtual">Desafios Virtuais</option>
              <option value="natacao">Natação / Travessia</option>
              <option value="trail">Trail Run / Corrida de Montanha</option>
              <option value="triathlon">Triathlon / Duathlon</option>
              <option value="surf">Surf</option>
              <option value="cursos">Cursos</option>
              <option value="outros-esportivos">Outros Eventos Esportivos</option>
              <option value="outros-geral">Outros Eventos em Geral</option>
            </select>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label for="quantidade-eventos" class="block text-sm font-medium text-white mb-2">
                Eventos/ano *
              </label>
              <select id="quantidade-eventos" name="quantidade_eventos" required
                class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 focus:ring-2 focus:ring-brand-yellow focus:outline-none appearance-none">
                <option value="">Selecione</option>
                <option value="1">1 Evento</option>
                <option value="2-4">2 - 4 Eventos</option>
                <option value="5-10">5 - 10 Eventos</option>
                <option value="acima-10">Acima de 10 Eventos</option>
              </select>
            </div>
            <div>
              <label for="cidade-evento" class="block text-sm font-medium text-white mb-2">
                Cidade do evento *
              </label>
              <input type="text" id="cidade-evento" name="cidade_evento" required
                class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 text-center">
            </div>
            <div>
              <label for="uf-evento" class="block text-sm font-medium text-white mb-2">
                UF *
              </label>
              <select id="uf-evento" name="uf_evento" required
                class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 focus:ring-2 focus:ring-brand-yellow focus:outline-none appearance-none text-center">
                <option value="">UF</option>
                <option value="AC">AC</option>
                <option value="AL">AL</option>
                <option value="AM">AM</option>
                <option value="AP">AP</option>
                <option value="BA">BA</option>
                <option value="CE">CE</option>
                <option value="DF">DF</option>
                <option value="ES">ES</option>
                <option value="GO">GO</option>
                <option value="MA">MA</option>
                <option value="MT">MT</option>
                <option value="MS">MS</option>
                <option value="MG">MG</option>
                <option value="PA">PA</option>
                <option value="PB">PB</option>
                <option value="PR">PR</option>
                <option value="PE">PE</option>
                <option value="PI">PI</option>
                <option value="RJ">RJ</option>
                <option value="RN">RN</option>
                <option value="RO">RO</option>
                <option value="RR">RR</option>
                <option value="RS">RS</option>
                <option value="SC">SC</option>
                <option value="SP">SP</option>
                <option value="SE">SE</option>
                <option value="TO">TO</option>
              </select>
            </div>
          </div>

          <!-- Nome do Evento -->
          <div>
            <label for="nome-evento" class="block text-sm font-medium text-white mb-2">
              Qual o nome do seu evento? *
            </label>
            <input type="text" id="nome-evento" name="nome_evento" required
              class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-brand-yellow focus:outline-none">
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="data-prevista" class="block text-sm font-medium text-white mb-2">
                Data prevista
              </label>
              <input type="date" id="data-prevista" name="data_prevista"
                class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900">
            </div>
            <div>
              <label for="estimativa-participantes" class="block text-sm font-medium text-white mb-2">
                Estimativa de participantes
              </label>
              <input type="number" min="0" id="estimativa-participantes" name="estimativa_participantes"
                class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900" placeholder="Ex.: 500">
            </div>
          </div>

          <!-- Regulamento -->
          <div>
            <label for="regulamento" class="block text-sm font-medium text-white mb-2">
              O seu evento já possui regulamento? *
            </label>
            <select id="regulamento" name="regulamento" required
              class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 focus:ring-2 focus:ring-brand-yellow focus:outline-none appearance-none">
              <option value="">Selecione</option>
              <option value="sim">Sim, Tenho Regulamento do Evento</option>
              <option value="nao">Meu Evento Não Possui Regulamento Pronto</option>
              <option value="quero-saber">Quero Saber Mais Sobre Como Criar um Regulamento</option>
            </select>
          </div>

          <div>
            <label for="arquivo-regulamento" class="block text-sm font-medium text-white mb-2">
              Regulamento do evento (PDF ou DOC)
            </label>
            <input type="file" id="arquivo-regulamento" name="arquivo_regulamento"
              accept=".pdf,.doc,.docx"
              class="w-full text-sm text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-yellow file:text-brand-green hover:file:bg-yellow-400">
            <p class="mt-1 text-xs text-white/80">
              Opcional neste momento. Envie se já tiver um regulamento em PDF ou DOC.
            </p>
          </div>

          <div>
            <label for="possui-autorizacao" class="block text-sm font-medium text-white mb-2">
              Já possui autorização municipal?
            </label>
            <select id="possui-autorizacao" name="possui_autorizacao"
              class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 focus:ring-2 focus:ring-brand-yellow focus:outline-none appearance-none">
              <option value="">Selecione</option>
              <option value="sim">Sim</option>
              <option value="em-andamento">Em andamento</option>
              <option value="nao">Ainda não</option>
            </select>
          </div>

          <div>
            <label for="descricao-evento" class="block text-sm font-medium text-white mb-2">
              Resumo do evento (trajeto, distância, diferencial)
            </label>
            <textarea id="descricao-evento" name="descricao_evento" rows="3"
              class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-brand-yellow focus:outline-none"></textarea>
          </div>

          

          <!-- Indicação -->
          <div>
            <label for="indicacao" class="block text-sm font-medium text-white mb-2">
              Coloque o nome em caso de indicação
            </label>
            <input type="text" id="indicacao" name="indicacao"
              class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-brand-yellow focus:outline-none">
          </div>

          <div>
            <label for="preferencia-contato" class="block text-sm font-medium text-white mb-2">
              Preferência de contato
            </label>
            <select id="preferencia-contato" name="preferencia_contato"
              class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm text-gray-900 focus:ring-2 focus:ring-brand-yellow focus:outline-none appearance-none">
              <option value="">Selecione</option>
              <option value="email">Email</option>
              <option value="telefone">Telefone</option>
              <option value="whatsapp">WhatsApp</option>
            </select>
          </div>

          <!-- Política de Privacidade -->
          <div class="text-xs text-white space-y-2">
            <p>
              Ao preencher o formulário, você está ciente que o MovAmazon poderá enviar comunicações e conteúdos
              de acordo com os seus interesses. Você pode modificar as suas permissões a qualquer momento. Para mais informações, confira a nossa <a href="#" class="underline font-medium">Política de Privacidade</a>.
            </p>
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" id="aceite-politica" name="aceite_politica" required class="admin-checkbox">
              <span class="text-sm">Confirmo que os dados informados são verdadeiros e autorizo o contato do MovAmazon.</span>
            </label>
          </div>

          <!-- Botão Submit -->
          <button type="submit"
            class="w-full bg-brand-red text-white px-4 sm:px-6 py-3 sm:py-4 rounded-lg sm:rounded-xl font-bold text-base sm:text-lg hover:bg-red-700 transition-colors duration-200 flex items-center justify-center space-x-2">
            <span>ENVIAR SOLICITAÇÃO DE EVENTO</span>
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </form>

        <p class="text-xs text-white mt-4 text-center">*Não atendemos dúvidas de participantes neste formulário.</p>
      </div>
    </div>
  </div>
</section>

<!-- Benefícios Section -->
<section class="py-12 sm:py-16 lg:py-20 bg-gray-50">
  <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8">
    <div class="text-center mb-12 sm:mb-16">
      <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-4">Por que escolher o <span class="text-brand-red">MovAmazon</span>?</h2>
      <p class="text-base sm:text-lg text-gray-600">Tudo que você precisa para organizar eventos esportivos de sucesso</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
      <!-- Benefício 1 -->
      <div class="bg-white rounded-lg sm:rounded-xl lg:rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition-all duration-300">
        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-brand-green/10 rounded-lg sm:rounded-xl lg:rounded-2xl flex items-center justify-center mb-4 sm:mb-6">
          <svg class="w-6 h-6 sm:w-8 sm:h-8 text-brand-green" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">Gestão Completa</h3>
        <p class="text-sm sm:text-base text-gray-600">Controle inscrições, pagamentos, kits e participantes em uma única plataforma intuitiva.</p>
      </div>

      <!-- Benefício 2 -->
      <div class="bg-white rounded-lg sm:rounded-xl lg:rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition-all duration-300">
        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-brand-red/10 rounded-lg sm:rounded-xl lg:rounded-2xl flex items-center justify-center mb-4 sm:mb-6">
          <svg class="w-6 h-6 sm:w-8 sm:h-8 text-brand-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">Relatórios Detalhados</h3>
        <p class="text-sm sm:text-base text-gray-600">Acompanhe o desempenho do seu evento em tempo real com gráficos e análises completas.</p>
      </div>

      <!-- Benefício 3 -->
      <div class="bg-white rounded-lg sm:rounded-xl lg:rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition-all duration-300">
        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-brand-yellow/10 rounded-lg sm:rounded-xl lg:rounded-2xl flex items-center justify-center mb-4 sm:mb-6">
          <svg class="w-6 h-6 sm:w-8 sm:h-8 text-brand-yellow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">Pagamentos Seguros</h3>
        <p class="text-sm sm:text-base text-gray-600">Múltiplas formas de pagamento com segurança total e integração com principais gateways.</p>
      </div>

      <!-- Benefício 4 -->
      <div class="bg-white rounded-lg sm:rounded-xl lg:rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition-all duration-300">
        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-brand-green/10 rounded-lg sm:rounded-xl lg:rounded-2xl flex items-center justify-center mb-4 sm:mb-6">
          <svg class="w-6 h-6 sm:w-8 sm:h-8 text-brand-green" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
          </svg>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">Suporte Especializado</h3>
        <p class="text-sm sm:text-base text-gray-600">Equipe dedicada para ajudar você em todas as etapas da organização do seu evento.</p>
      </div>

      <!-- Benefício 5 -->
      <div class="bg-white rounded-lg sm:rounded-xl lg:rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition-all duration-300">
        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-brand-red/10 rounded-lg sm:rounded-xl lg:rounded-2xl flex items-center justify-center mb-4 sm:mb-6">
          <svg class="w-6 h-6 sm:w-8 sm:h-8 text-brand-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">Performance Otimizada</h3>
        <p class="text-sm sm:text-base text-gray-600">Plataforma rápida e responsiva que funciona perfeitamente em qualquer dispositivo.</p>
      </div>

      <!-- Benefício 6 -->
      <div class="bg-white rounded-lg sm:rounded-xl lg:rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition-all duration-300">
        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-brand-yellow/10 rounded-lg sm:rounded-xl lg:rounded-2xl flex items-center justify-center mb-4 sm:mb-6">
          <svg class="w-6 h-6 sm:w-8 sm:h-8 text-brand-yellow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">Configuração Rápida</h3>
        <p class="text-sm sm:text-base text-gray-600">Configure seu evento em minutos com templates e ferramentas intuitivas.</p>
      </div>
    </div>
  </div>
</section>

<script>
  document.getElementById('organizadorForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Mostrar loading
    submitBtn.innerHTML = `
    <svg class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
    </svg>
    <span>Enviando...</span>
  `;

    // Coletar dados do formulário (incluindo arquivo)
    const formData = new FormData(this);

    // Ajustar telefone (ddi + número)
    const ddi = formData.get('telefone_ddi') || '+55';
    const tel = formData.get('telefone') || '';
    if (tel) {
      formData.set('telefone', `${ddi} ${tel}`.trim());
    }
    formData.delete('telefone_ddi');

    // Normalizar aceite de política
    formData.set('aceite_politica', formData.get('aceite_politica') ? '1' : '');

    // Enviar para a API (multipart/form-data)
    fetch('../../../api/organizador/create.php', {
        method: 'POST',
        body: formData
      })
      .then(async response => {
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
          const txt = await response.text();
          throw new Error('Resposta inválida do servidor');
        }
        const payload = await response.json();
        if (!response.ok || payload.success === false) {
          throw new Error(payload.error || payload.message || 'Erro ao enviar formulário');
        }
        return payload;
      })
      .then(() => {
        Swal.fire({
          icon: 'success',
          title: 'Sucesso!',
          text: 'Recebemos sua solicitação! Você receberá um e-mail com o checklist necessário.',
          confirmButtonText: 'OK'
        });
        this.reset();
      })
      .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
          icon: 'error',
          title: 'Erro!',
          text: 'Erro de conexão. Tente novamente.',
          confirmButtonText: 'OK'
        });
      })
      .finally(() => {
        // Restaurar botão
        submitBtn.innerHTML = originalText;
      });
  });
</script>

<?php include '../../includes/footer.php'; ?>
