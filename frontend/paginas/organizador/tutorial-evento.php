<?php
// Tutorial passo a passo para criação de eventos pelo organizador
?>
<div class="w-full space-y-6">
  <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">
      Tutorial de Criação de Evento no MovAmazon
    </h1>
    <p class="text-gray-600">
      Siga este passo a passo para criar e configurar seu evento usando o menu numerado do painel do organizador.
    </p>
  </section>

  <!-- PASSO 1 -->
  <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3">
    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
      <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-green text-white text-sm font-bold">1</span>
      Criar o evento (1 – Meus Eventos)
    </h2>
    <p class="text-gray-700">
      Comece criando a estrutura básica do seu evento. Esta etapa define o “esqueleto” usado nas demais configurações.
    </p>
    <ol class="list-decimal list-inside space-y-1 text-gray-700">
      <li>No menu lateral, clique em <strong>1 – Meus Eventos</strong> e depois em <strong>Criar Evento</strong>.</li>
      <li>Na aba <strong>Dados Básicos</strong>, preencha:
        <ul class="list-disc list-inside ml-4 mt-1 space-y-0.5">
          <li><strong>Nome do Evento</strong>: use um nome claro e comercial (ex.: “Corrida da Engenharia 10K”).</li>
          <li><strong>Descrição</strong>: explique público, objetivos, distância e diferenciais.</li>
          <li><strong>Data de Início e Fim das Inscrições</strong>: período em que o formulário ficará aberto.</li>
          <li><strong>Hora de Início</strong>: horário oficial da largada.</li>
          <li><strong>Categoria</strong>: tipo de evento (Corrida de Rua, Caminhada, Triatlo, etc.).</li>
          <li><strong>Gênero</strong>: Masculino, Feminino ou Misto (recomendado).</li>
          <li><strong>Status do Evento</strong>: mantenha em <strong>Rascunho</strong> até concluir todas as etapas.</li>
        </ul>
      </li>
      <li>Na aba <strong>Localização</strong>, informe:
        <ul class="list-disc list-inside ml-4 mt-1 space-y-0.5">
          <li><strong>Local / Nome do Local</strong> (ex.: Parque do Mindu, Arena da Amazônia).</li>
          <li>Endereço (logradouro, número, CEP, cidade e estado).</li>
        </ul>
      </li>
      <li>Use o botão <strong>Criar Evento</strong> para salvar o rascunho. Você poderá voltar e editar sempre que precisar.</li>
    </ol>
    <p class="text-sm text-gray-500">
      Dica: mesmo sem todos os detalhes definidos, mantenha o evento como <strong>Rascunho</strong> até finalizar as próximas etapas.
    </p>
  </section>

  <!-- PASSO 2 -->
  <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3">
    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
      <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-green text-white text-sm font-bold">2</span>
      Definir Modalidades (2 – Modalidades)
    </h2>
    <p class="text-gray-700">
      Modalidades representam os diferentes formatos de participação (ex.: 5 km, 10 km, Caminhada, Kids).
    </p>
    <ol class="list-decimal list-inside space-y-1 text-gray-700">
      <li>Acesse o menu <strong>2 – Modalidades</strong>.</li>
      <li>Selecione o evento que deseja configurar, se houver mais de um.</li>
      <li>Crie uma modalidade para cada percurso:
        <ul class="list-disc list-inside ml-4 mt-1 space-y-0.5">
          <li><strong>Nome</strong> (ex.: Corrida 5 km, Corrida 10 km, Caminhada 3 km).</li>
          <li><strong>Distância</strong> e outras informações importantes.</li>
          <li><strong>Faixa etária</strong> e restrições, se houver.</li>
        </ul>
      </li>
    </ol>
    <p class="text-sm text-gray-500">
      Dica: modalidades bem definidas facilitam a organização de categorias, lotes e resultados.
    </p>
  </section>

  <!-- PASSO 3 -->
  <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3">
    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
      <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-green text-white text-sm font-bold">3</span>
      Configurar Lotes de Inscrição (3 – Lotes de Inscrição)
    </h2>
    <p class="text-gray-700">
      Lotes definem preços, datas de virada e limites de vagas para cada modalidade.
    </p>
    <ol class="list-decimal list-inside space-y-1 text-gray-700">
      <li>No menu, acesse <strong>3 – Lotes de Inscrição</strong>.</li>
      <li>Para cada modalidade, cadastre um ou mais lotes com:
        <ul class="list-disc list-inside ml-4 mt-1 space-y-0.5">
          <li><strong>Nome do lote</strong> (ex.: 1º Lote, 2º Lote Promocional).</li>
          <li><strong>Período do lote</strong> (data/hora de início e fim).</li>
          <li><strong>Valor da inscrição</strong>.</li>
          <li><strong>Limite de vagas</strong> (opcional, mas recomendado).</li>
        </ul>
      </li>
      <li>Revise se as datas dos lotes estão dentro do período geral de inscrições do evento.</li>
    </ol>
  </section>

  <!-- PASSO 4 -->
  <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3">
    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
      <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-green text-white text-sm font-bold">4</span>
      Cupons de Desconto (4 – Cupons de Desconto)
    </h2>
    <p class="text-gray-700">
      Cupons permitem campanhas específicas (grupos, parceiros, influenciadores) com descontos controlados.
    </p>
    <ol class="list-decimal list-inside space-y-1 text-gray-700">
      <li>Acesse <strong>4 – Cupons de Desconto</strong>.</li>
      <li>Crie cupons definindo:
        <ul class="list-disc list-inside ml-4 mt-1 space-y-0.5">
          <li><strong>Código</strong> (ex.: CORRIDA10, EQUIPEVIP).</li>
          <li><strong>Tipo de desconto</strong> (valor fixo ou percentual).</li>
          <li><strong>Quantidade máxima de usos</strong>.</li>
          <li><strong>Modalidades/lotes elegíveis</strong>, se aplicável.</li>
        </ul>
      </li>
    </ol>
  </section>

  <!-- PASSO 5 -->
  <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3">
    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
      <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-green text-white text-sm font-bold">5</span>
      Questionário e Termos (5 – Questionário, 6 – Termos de Inscrição)
    </h2>
    <p class="text-gray-700">
      Esta etapa garante que você colete as informações corretas e esteja protegido juridicamente.
    </p>
    <ol class="list-decimal list-inside space-y-1 text-gray-700">
      <li>Em <strong>5 – Questionário</strong>, defina campos extras que o participante deverá responder (equipe, tamanho de camisa, histórico de corridas, etc.).</li>
      <li>Em <strong>6 – Termos de Inscrição</strong>, revise ou cadastre:
        <ul class="list-disc list-inside ml-4 mt-1 space-y-0.5">
          <li><strong>Termos gerais</strong> (responsabilidade, uso de imagem, política de cancelamento).</li>
          <li><strong>Termos específicos</strong> de modalidades, se houver.</li>
        </ul>
      </li>
    </ol>
  </section>

  <!-- PASSO 6 -->
  <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3">
    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
      <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-green text-white text-sm font-bold">6</span>
      Produtos, Kits e Extras (7 – Produtos, 8 – Templates de Kit, 9 – Kits do Evento, 12 – Produtos Extras)
    </h2>
    <p class="text-gray-700">
      Aqui você organiza o que será entregue ao participante: camisas, kits, brindes e produtos adicionais.
    </p>
    <ol class="list-decimal list-inside space-y-1 text-gray-700">
      <li>Em <strong>7 – Produtos</strong>, cadastre itens como camisas, medalhas extras e serviços adicionais.</li>
      <li>Em <strong>8 – Templates de Kit</strong>, monte combinações padrão de itens (Kit Básico, Kit Premium, etc.).</li>
      <li>Em <strong>9 – Kits do Evento</strong>, associe os kits ao evento atual.</li>
      <li>Se necessário, use <strong>12 – Produtos Extras</strong> para itens opcionais vendidos à parte.</li>
    </ol>
  </section>

  <!-- PASSO 7 -->
  <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3">
    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
      <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-green text-white text-sm font-bold">7</span>
      Programação, Retirada de Kits e Camisas (10 – Retirada de Kits, 11 – Camisas, 13 – Programação)
    </h2>
    <p class="text-gray-700">
      Defina a logística do evento para que participantes saibam exatamente onde e quando comparecer.
    </p>
    <ol class="list-decimal list-inside space-y-1 text-gray-700">
      <li>Em <strong>13 – Programação</strong>, registre horários de largadas, premiações e outras atividades.</li>
      <li>Em <strong>10 – Retirada de Kits</strong>, informe endereço, datas, horários e documentos necessários para retirada.</li>
      <li>Em <strong>11 – Camisas</strong>, configure tamanhos disponíveis e estoques, se o módulo estiver ativo.</li>
    </ol>
  </section>

  <!-- PASSO 8 -->
  <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3">
    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
      <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-green text-white text-sm font-bold">8</span>
      Revisão Final e Publicação
    </h2>
    <p class="text-gray-700">
      Depois de configurar todas as seções, revise o evento antes de abrir as inscrições ao público.
    </p>
    <ol class="list-decimal list-inside space-y-1 text-gray-700">
      <li>Abra <strong>1 – Meus Eventos → Lista de Eventos</strong> e clique para editar o evento desejado.</li>
      <li>Confirme nome, datas, horários, modalidades, lotes, termos e questionário.</li>
      <li>Quando estiver tudo certo, altere o <strong>Status do Evento</strong> de <strong>Rascunho</strong> para <strong>Ativo</strong>.</li>
      <li>Divulgue o link de inscrição gerado pelo sistema para o seu público.</li>
    </ol>
    <p class="text-sm text-gray-500">
      Dica: após publicar, qualquer mudança importante (datas, valores, regulamento) deve ser comunicada aos participantes.
    </p>
  </section>
</div>

