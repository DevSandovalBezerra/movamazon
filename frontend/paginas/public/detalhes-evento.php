<?php
$pageTitle = 'Detalhes do Evento';
require_once '../../../api/db.php';

$evento_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$evento = null;
$modalidades = [];
$questionario = [];
$programacao = [];
$retirada_kits = null;
$composicao_kit = [];

// Função para formatar número do WhatsApp
function formatarWhatsApp($telefone)
{
  if (empty($telefone)) return null;

  // Remove todos os caracteres não numéricos
  $numero = preg_replace('/[^0-9]/', '', $telefone);

  // Se não começar com 55 (Brasil), adiciona
  if (strlen($numero) == 11 && substr($numero, 0, 2) != '55') {
    $numero = '55' . $numero;
  } elseif (strlen($numero) == 10) {
    $numero = '55' . $numero;
  }

  return $numero;
}

if ($evento_id > 0) {
  // Buscar dados do evento com telefone do organizador
  // Relação: eventos.organizador_id = organizadores.usuario_id = usuarios.id
  // Prioridade: empresa do organizador > nome completo do usuário > fallback
  $stmt = $pdo->prepare("
    SELECT 
      e.*, 
      COALESCE(
        NULLIF(o.empresa, ''), 
        NULLIF(u.nome_completo, ''),
        'Organizador não informado'
      ) as organizadora, 
      u.telefone, 
      u.celular 
    FROM eventos e 
    LEFT JOIN organizadores o ON e.organizador_id = o.usuario_id 
    LEFT JOIN usuarios u ON e.organizador_id = u.id 
    WHERE e.id = ? AND e.deleted_at IS NULL
  ");
  $stmt->execute([$evento_id]);
  $evento = $stmt->fetch(PDO::FETCH_ASSOC);
  
  // Se não encontrou organizador pelo usuario_id, tenta buscar pelo id da tabela organizadores
  if ($evento && (empty($evento['organizadora']) || $evento['organizadora'] === 'Organizador não informado')) {
    $stmt2 = $pdo->prepare("
      SELECT 
        o.empresa,
        u.nome_completo,
        u.telefone,
        u.celular
      FROM eventos e
      LEFT JOIN organizadores o ON e.organizador_id = o.id
      LEFT JOIN usuarios u ON o.usuario_id = u.id
      WHERE e.id = ?
    ");
    $stmt2->execute([$evento_id]);
    $org_data = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    if ($org_data) {
      $evento['organizadora'] = !empty($org_data['empresa']) ? $org_data['empresa'] : (!empty($org_data['nome_completo']) ? $org_data['nome_completo'] : 'Organizador não informado');
      if (empty($evento['telefone']) && !empty($org_data['telefone'])) {
        $evento['telefone'] = $org_data['telefone'];
      }
      if (empty($evento['celular']) && !empty($org_data['celular'])) {
        $evento['celular'] = $org_data['celular'];
      }
    }
  }
  
  // Garantir que organizadora tenha um valor (fallback final)
  if ($evento && (empty($evento['organizadora']) || !isset($evento['organizadora']))) {
    $evento['organizadora'] = 'Organizador não informado';
  }

  // Buscar modalidades com kit
  $sql = "SELECT 
            m.id, m.nome, c.nome as nome_categoria, 
            k.id as kit_id, k.nome as kit_nome, k.foto_kit, k.valor
        FROM modalidades m
        INNER JOIN categorias c ON m.categoria_id = c.id
        LEFT JOIN kits_eventos k ON k.modalidade_evento_id = m.id AND k.ativo = 1 AND k.disponivel_venda = 1
        WHERE m.evento_id = ?
        ORDER BY m.nome, c.nome";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$evento_id]);
  $modalidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Buscar questionário
  $stmt = $pdo->prepare("SELECT * FROM questionario_evento WHERE evento_id = ? AND ativo = 1");
  $stmt->execute([$evento_id]);
  $questionario = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Buscar programação
  $stmt = $pdo->prepare("SELECT * FROM programacao_evento WHERE evento_id = ? AND ativo = 1 ORDER BY tipo, titulo");
  $stmt->execute([$evento_id]);
  $programacao = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Buscar retirada de kits
  $stmt = $pdo->prepare("SELECT * FROM retirada_kits_evento WHERE evento_id = ? AND ativo = 1 LIMIT 1");
  $stmt->execute([$evento_id]);
  $retirada_kits = $stmt->fetch(PDO::FETCH_ASSOC);

  // Buscar dados de inscrições
  $stmt = $pdo->prepare("SELECT COUNT(*) as total_inscritos FROM inscricoes WHERE evento_id = ? AND status != 'cancelado'");
  $stmt->execute([$evento_id]);
  $inscricoes = $stmt->fetch(PDO::FETCH_ASSOC);
  $total_inscritos = $inscricoes['total_inscritos'];

  // Buscar preços dos lotes de inscrição
  $stmt = $pdo->prepare("
        SELECT MIN(li.preco) as preco_minimo, MAX(li.preco) as preco_maximo
        FROM lotes_inscricao li
        WHERE li.evento_id = ? AND li.ativo = 1
    ");
  $stmt->execute([$evento_id]);
  $precos = $stmt->fetch(PDO::FETCH_ASSOC);

  // Buscar composição do kit - buscar todos os produtos dos kits do evento
  $stmt = $pdo->prepare("
        SELECT DISTINCT p.nome as item, p.descricao, p.preco_base
        FROM kits_eventos k
        INNER JOIN kit_produtos kp ON k.id = kp.kit_id
        INNER JOIN kit_templates p ON kp.produto_id = p.id
        WHERE k.evento_id = ? AND k.ativo = 1 AND kp.ativo = 1 AND p.ativo = 1
        ORDER BY p.nome
    ");
  $stmt->execute([$evento_id]);
  $composicao_kit = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Buscar TODAS as modalidades do evento (mesmo sem lotes)
  $stmt = $pdo->prepare("
        SELECT 
            m.id as modalidade_id,
            m.nome as modalidade_nome, 
            c.nome as categoria_nome,
            li.id as lote_id,
            li.numero_lote,
            li.preco,
            li.data_inicio,
            li.data_fim,
            li.ativo as lote_ativo
        FROM modalidades m
        INNER JOIN categorias c ON m.categoria_id = c.id
        LEFT JOIN lotes_inscricao li ON li.modalidade_id = m.id AND li.evento_id = ? AND li.ativo = 1
        WHERE m.evento_id = ? AND m.ativo = 1
        ORDER BY c.nome, m.nome, li.numero_lote
    ");
  $stmt->execute([$evento_id, $evento_id]);
  $modalidades_com_lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  // Organizar dados: agrupar modalidades e seus lotes
  $modalidades_organizadas = [];
  $max_numero_lote = 0; // Para determinar quantas colunas mostrar
  
  foreach ($modalidades_com_lotes as $row) {
    $chave = $row['categoria_nome'] . '|' . $row['modalidade_nome'];
    if (!isset($modalidades_organizadas[$chave])) {
      $modalidades_organizadas[$chave] = [
        'categoria' => $row['categoria_nome'],
        'modalidade' => $row['modalidade_nome'],
        'lotes' => []
      ];
    }
    // Adicionar lote se existir
    if ($row['lote_id'] && $row['lote_ativo']) {
      $numero_lote = (int)$row['numero_lote'];
      $modalidades_organizadas[$chave]['lotes'][] = [
        'numero_lote' => $numero_lote,
        'preco' => $row['preco']
      ];
      // Atualizar o número máximo de lotes
      if ($numero_lote > $max_numero_lote) {
        $max_numero_lote = $numero_lote;
      }
    }
  }
  
  // Garantir pelo menos 3 colunas (padrão) ou usar o máximo encontrado
  $total_colunas = max(3, $max_numero_lote);

  // Buscar formas de pagamento
  $stmt = $pdo->prepare("SELECT * FROM formas_pagamento_evento WHERE evento_id = ?");
  $stmt->execute([$evento_id]);
  $formas_pagamento = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once dirname(__DIR__, 3) . '/api/helpers/get_regulamento_url.php';
$regulamento_url = $evento_id > 0 ? getRegulamentoUrl($evento_id, $pdo) : null;

// Fallback para dados básicos se evento não encontrado
if (!$evento) {
  $evento = [
    'nome' => 'Evento não encontrado',
    'data_inicio' => '2025-10-24',
    'local' => 'Manaus/AM',
    'hora_inicio' => '07:00',
    'organizadora' => 'Organizador não informado',
    'descricao' => 'Descrição não disponível.',
    'largada' => 'Av. do Samba, 1000 - Manaus/AM',
  ];
}

// Garantir que as variáveis estejam definidas mesmo se não houver evento
if (!isset($modalidades_organizadas)) {
  $modalidades_organizadas = [];
}
if (!isset($total_colunas)) {
  $total_colunas = 3; // Padrão de 3 colunas
}

// Ajustes de fallback para campos obrigatórios apenas se não houver dados reais
if (!isset($evento['hora_inicio']) || !$evento['hora_inicio']) {
  $evento['hora_inicio'] = '07:00';
}
if (!isset($evento['descricao']) || !$evento['descricao']) {
  $evento['descricao'] = 'Prepare-se para uma experiência única de corrida pelas ruas de Manaus!';
}

// Determinar local de largada baseado na programação
$largada = 'Local não informado';
if ($programacao) {
  foreach ($programacao as $item) {
    if ($item['tipo'] === 'percurso' && strpos(strtolower($item['titulo']), 'largada') !== false) {
      $largada = $item['descricao'];
      break;
    }
  }
}

// Base para assets: relativo ao diretório deste script (public/ -> frontend/ = 2 níveis)
$baseAssetsRel = str_repeat('../', 2); // ../../ desde public/

// Determinar imagem do banner dinamicamente
$banner = '';
if ($evento && isset($evento['imagem']) && $evento['imagem']) {
  if (strpos($evento['imagem'], 'http') === 0) {
    $banner = $evento['imagem'];
  } else {
    $banner = $baseAssetsRel . 'assets/img/eventos/' . $evento['imagem'];
  }
} else {
  $banner = $baseAssetsRel . 'assets/img/corrida_sauim.png';
}

$logo = $baseAssetsRel . 'assets/img/logo.png';
$redes = [
  'facebook' => '#',
  'instagram' => '#',
  'whatsapp' => '#',
  'site' => '#'
];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($evento['nome']) ?> - MovAmazon</title>
  <link rel="stylesheet" href="../../assets/css/tailwind.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --brand-green: #0b4340;
      --brand-yellow: #f5c113;
    }

    .brand-green {
      color: #10B981;
    }

    .bg-brand-green {
      background-color: var(--brand-green);
    }

    .text-brand-green {
      color: var(--brand-green);
    }

    .brand-yellow {
      color: #FBBF24;
    }

    .text-brand-yellow {
      color: var(--brand-yellow) !important;
    }

    .bg-brand-yellow {
      background-color: var(--brand-yellow);
    }
  </style>
</head>

<body class="bg-gray-50">
  <!-- Header -->
  <header class="bg-brand-green shadow-lg" style="background-color: #0b4340;">
    <div class="max-w-6xl mx-auto px-3 sm:px-4 lg:px-8">
      <div class="flex justify-between items-center h-12 sm:h-14 lg:h-16">
        <div class="flex items-center">
          <div class="bg-white/20 backdrop-blur-sm rounded-xl p-1.5 border border-white/30">
            <img src="<?= $logo ?>" alt="MovAmazon" class="h-6 w-auto sm:h-8 lg:h-10">
          </div>
          <span class="ml-2 sm:ml-3 text-sm sm:text-lg lg:text-xl font-bold">
            <span class="text-white">Mov</span><span class="text-brand-yellow">Amazon</span>
          </span>
        </div>
        <nav class="hidden md:flex space-x-4 lg:space-x-8">
          <a href="../public/index.php" class="text-gray-100 hover:text-brand-yellow text-sm lg:text-base transition-colors">INÍCIO</a>
          <a href="../public/index.php" class="text-gray-100 hover:text-brand-yellow text-sm lg:text-base transition-colors">CALENDÁRIO</a>
          <a href="../organizador/index.php" class="text-gray-100 hover:text-brand-yellow text-sm lg:text-base transition-colors">ORGANIZADOR</a>
          <a href="../auth/login.php" class="text-gray-100 hover:text-brand-yellow text-sm lg:text-base transition-colors">LOGIN</a>
        </nav>
        <!-- Menu mobile -->
        <button class="md:hidden p-2 text-white" id="mobile-menu-btn">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>
      <!-- Menu mobile expandido -->
      <div class="md:hidden hidden" id="mobile-menu">
        <div class="py-4 space-y-2 border-t border-white/20">
          <a href="../public/index.php" class="block px-3 py-2 text-gray-100 hover:text-brand-yellow">INÍCIO</a>
          <a href="../public/index.php" class="block px-3 py-2 text-gray-100 hover:text-brand-yellow">CALENDÁRIO</a>
          <a href="../organizador/index.php" class="block px-3 py-2 text-gray-100 hover:text-brand-yellow">ORGANIZADOR</a>
          <a href="../auth/login.php" class="block px-3 py-2 text-gray-100 hover:text-brand-yellow">LOGIN</a>
        </div>
      </div>
    </div>
  </header>

  <!-- Banner Principal -->
  <div class="w-full max-w-6xl mx-auto px-3 sm:px-4 lg:px-8 mt-4 sm:mt-6 lg:mt-8">
    <div class="relative w-full aspect-video sm:aspect-[16/9] lg:aspect-[21/9] rounded-lg sm:rounded-xl overflow-hidden mx-auto">
      <?php if ($banner && $banner !== '../../assets/img/corrida_sauim.png'): ?>
        <img src="<?= $banner ?>" alt="Banner do Evento" class="w-full h-full object-cover">
      <?php else: ?>
        <div class="w-full h-full bg-gradient-to-r from-brand-green to-green-600 flex items-center justify-center">
          <div class="text-center text-white">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-2"><?= htmlspecialchars($evento['nome']) ?></h1>
            <p class="text-sm sm:text-base md:text-lg">Prepare-se para uma experiência única!</p>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Título da Corrida abaixo da foto -->
    <div class="mt-4 sm:mt-6 text-center">
      <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-gray-800 px-2"><?= htmlspecialchars($evento['nome']) ?></h1>
    </div>
  </div>

  <!-- Conteúdo Principal -->
  <div class="max-w-6xl mx-auto px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
      <!-- Coluna Principal -->
      <div class="lg:col-span-2 space-y-4 sm:space-y-6 lg:space-y-8">
        <!-- Informações Principais -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6">
          <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 mb-4 sm:mb-6 text-center">Informações do Evento</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div class="flex items-start sm:items-center space-x-3">
              <div class="bg-brand-green text-white p-2 sm:p-3 rounded-full flex-shrink-0">
                <i class="fas fa-calendar-alt text-sm sm:text-base"></i>
              </div>
              <div class="min-w-0">
                <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Data e Hora</h3>
                <p class="text-gray-600 text-sm sm:text-base"><?= date('d/m/Y', strtotime($evento['data_inicio'])) ?> às <?= htmlspecialchars($evento['hora_inicio']) ?></p>
              </div>
            </div>
            <div class="flex items-start sm:items-center space-x-3">
              <div class="bg-brand-green text-white p-2 sm:p-3 rounded-full flex-shrink-0">
                <i class="fas fa-map-marker-alt text-sm sm:text-base"></i>
              </div>
              <div class="min-w-0">
                <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Localização</h3>
                <p class="text-gray-600 text-sm sm:text-base"><?= htmlspecialchars($evento['local']) ?></p>
              </div>
            </div>
            <div class="flex items-start sm:items-center space-x-3">
              <div class="bg-brand-green text-white p-2 sm:p-3 rounded-full flex-shrink-0">
                <i class="fas fa-running text-sm sm:text-base"></i>
              </div>
              <div class="min-w-0">
                <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Modalidade</h3>
                <p class="text-gray-600 text-sm sm:text-base">Corrida de Rua</p>
              </div>
            </div>
            <div class="flex items-start sm:items-center space-x-3">
              <div class="bg-brand-green text-white p-2 sm:p-3 rounded-full flex-shrink-0">
                <i class="fas fa-box text-sm sm:text-base"></i>
              </div>
              <div class="min-w-0">
                <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Retirada do Kit</h3>
                <p class="text-gray-600 text-sm sm:text-base"><?= $retirada_kits ? date('d/m/Y', strtotime($retirada_kits['data_retirada'])) : 'A definir' ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sobre o Evento -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6">
          <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 mb-4 sm:mb-6 text-center">SOBRE O EVENTO</h2>
          <div class="prose max-w-none text-center">
            <p class="text-gray-700 leading-relaxed mb-4"><?= htmlspecialchars($evento['descricao']) ?></p>

            <?php if ($modalidades && count($modalidades)): ?>
              <div class="mt-6">
                <h3 class="text-lg font-semibold text-brand-green mb-3">Modalidades Disponíveis</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <?php foreach ($modalidades as $mod): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-brand-green transition-colors text-center">
                      <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($mod['nome']) ?></h4>
                      <p class="text-sm text-gray-600"><?= htmlspecialchars($mod['nome_categoria']) ?></p>
                      <?php if ($mod['kit_nome']): ?>
                        <p class="text-sm text-brand-green mt-1">Kit: <?= htmlspecialchars($mod['kit_nome']) ?></p>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Inscrições -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6">
          <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 mb-4 sm:mb-6 text-center">INSCRIÇÕES</h2>

          <?php if (!empty($modalidades_organizadas)): ?>
            <div class="mb-6 text-center">
              <h3 class="text-lg font-semibold text-brand-green mb-4">Período de Inscrição</h3>
              <p class="text-gray-600 mb-4">Inscrições abertas até <?= date('d/m/Y', strtotime($evento['data_inicio'])) ?></p>

              <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 mx-auto">
                  <thead>
                    <tr class="bg-gray-50">
                      <th class="border border-gray-300 px-4 py-2 text-center">Categoria</th>
                      <th class="border border-gray-300 px-4 py-2 text-center">Modalidade</th>
                      <?php 
                      // Criar colunas dinamicamente baseado no número máximo de lotes
                      for ($col = 1; $col <= $total_colunas; $col++): 
                        $ordinais = ['', '1º', '2º', '3º', '4º', '5º', '6º', '7º', '8º', '9º', '10º'];
                        $ordinal = isset($ordinais[$col]) ? $ordinais[$col] : $col . 'º';
                      ?>
                        <th class="border border-gray-300 px-4 py-2 text-center"><?= $ordinal ?> Lote</th>
                      <?php endfor; ?>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    // Ordenar por categoria e modalidade
                    ksort($modalidades_organizadas);
                    
                    foreach ($modalidades_organizadas as $item):
                    ?>
                      <tr>
                        <td class="border border-gray-300 px-4 py-2 font-medium text-center"><?= htmlspecialchars($item['categoria']) ?></td>
                        <td class="border border-gray-300 px-4 py-2 font-medium text-center"><?= htmlspecialchars($item['modalidade']) ?></td>
                        <?php for ($i = 1; $i <= $total_colunas; $i++): ?>
                          <td class="border border-gray-300 px-4 py-2 text-center">
                            <?php
                            $lote_atual = array_filter($item['lotes'], function ($l) use ($i) {
                              return $l['numero_lote'] == $i;
                            });
                            if (!empty($lote_atual)) {
                              $lote = array_values($lote_atual)[0];
                              echo 'R$ ' . number_format($lote['preco'], 2, ',', '.');
                            } else {
                              echo '-';
                            }
                            ?>
                          </td>
                        <?php endfor; ?>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 mx-auto max-w-2xl">
                <p class="text-sm text-yellow-800 text-center">
                  <strong>Cortesias:</strong> Doadores de sangue, PCD e idosos têm desconto especial.
                  Entre em contato para mais informações.
                </p>
              </div>
            </div>
          <?php else: ?>
            <div class="text-center py-8">
              <p class="text-gray-600">Informações de inscrição serão divulgadas em breve.</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Retirada do Kit -->
        <?php if ($retirada_kits): ?>
          <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6">
            <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 mb-4 sm:mb-6 text-center">RETIRADA DO KIT</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-center">
              <div>
                <h3 class="text-lg font-semibold text-brand-green mb-3">Data e Horário</h3>
                <div class="space-y-2">
                  <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($retirada_kits['data_retirada'])) ?></p>
                  <p><strong>Horário:</strong> <?= substr($retirada_kits['horario_inicio'], 0, 5) ?> às <?= substr($retirada_kits['horario_fim'], 0, 5) ?></p>
                </div>
              </div>

              <div>
                <h3 class="text-lg font-semibold text-brand-green mb-3">Local</h3>
                <p class="text-gray-700"><?= htmlspecialchars($retirada_kits['local_retirada'] ?? '') ?></p>
                <p class="text-sm text-gray-600 mt-2"><?= htmlspecialchars($retirada_kits['endereco_completo'] ?? '') ?></p>
              </div>
            </div>

            <?php if ($composicao_kit): ?>
              <div class="mt-6">
                <h3 class="text-lg font-semibold text-brand-green mb-3 text-center">Conteúdo do Kit</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <?php foreach ($composicao_kit as $produto): ?>
                    <div class="p-3 bg-gray-50 rounded-lg text-center">
                      <h4 class="font-medium text-gray-800"><?= htmlspecialchars($produto['item']) ?></h4>
                      <?php if ($produto['descricao']): ?>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($produto['descricao']) ?></p>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <?php if ($retirada_kits['retirada_terceiros']): ?>
              <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <h4 class="font-semibold text-yellow-800 mb-2 text-center">Retirada por Terceiros</h4>
                <p class="text-sm text-yellow-800 text-center"><?= htmlspecialchars($retirada_kits['retirada_terceiros'] ?? '') ?></p>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- Programação -->
        <?php if ($programacao): ?>
          <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6">
            <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 mb-4 sm:mb-6 text-center">PROGRAMAÇÃO</h2>

            <div class="space-y-4">
              <?php
              $atividades = array_filter($programacao, function ($item) {
                return $item['tipo'] === 'atividade_adicional';
              });
              foreach ($atividades as $item):
              ?>
                <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                  <div class="bg-brand-green text-white p-2 rounded-full">
                    <i class="fas fa-clock"></i>
                  </div>
                  <div class="text-center flex-1">
                    <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($item['titulo']) ?></h3>
                    <p class="text-gray-600"><?= htmlspecialchars($item['descricao']) ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Premiação -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6">
          <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 mb-4 sm:mb-6 text-center">PREMIAÇÃO</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-center">
            <div>
              <h3 class="text-lg font-semibold text-brand-green mb-3">Valores em Dinheiro</h3>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span>1º Lugar Geral Masculino</span>
                  <span class="font-semibold text-brand-green">R$ 500,00</span>
                </div>
                <div class="flex justify-between">
                  <span>1º Lugar Geral Feminino</span>
                  <span class="font-semibold text-brand-green">R$ 500,00</span>
                </div>
                <div class="flex justify-between">
                  <span>2º Lugar Geral Masculino</span>
                  <span class="font-semibold text-brand-green">R$ 300,00</span>
                </div>
                <div class="flex justify-between">
                  <span>2º Lugar Geral Feminino</span>
                  <span class="font-semibold text-brand-green">R$ 300,00</span>
                </div>
              </div>
            </div>

            <div>
              <h3 class="text-lg font-semibold text-brand-green mb-3">Troféus e Medalhas</h3>
              <div class="space-y-2">
                <p>• Troféus para os 3 primeiros colocados de cada categoria</p>
                <p>• Medalhas para todos os participantes</p>
                <p>• Certificado de participação</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="lg:col-span-1">
        <div class="sticky top-8 space-y-6">
          <!-- Botão de Inscrição -->
          <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6">
            <button id="btn-inscrever-aside" class="w-full bg-brand-green text-white py-3 sm:py-4 rounded-lg font-bold text-base sm:text-lg hover:bg-green-700 transition-colors">
              INSCREVA-SE
            </button>
            <p class="text-center text-xs sm:text-sm text-gray-600 mt-2">Inscrições limitadas</p>
          </div>

          <!-- Organizador -->
          <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4 text-center">Organizador</h3>
            <div class="flex items-center space-x-3 mb-4">
              <img src="<?= $logo ?>" alt="Logo" class="h-10 w-10 sm:h-12 sm:w-12 rounded-full border">
              <div>
                <h4 class="font-semibold text-brand-green text-sm sm:text-base"><?= htmlspecialchars($evento['organizadora']) ?></h4>
                <p class="text-xs sm:text-sm text-gray-600">Organizador do evento</p>
              </div>
            </div>

            <?php
            $whatsapp_numero_org = null;
            if (!empty($evento['celular'])) {
              $whatsapp_numero_org = formatarWhatsApp($evento['celular']);
            } elseif (!empty($evento['telefone'])) {
              $whatsapp_numero_org = formatarWhatsApp($evento['telefone']);
            }

            if ($whatsapp_numero_org):
              $mensagem_org = "Olá! Gostaria de saber mais informações sobre o evento: " . htmlspecialchars($evento['nome']);
              $whatsapp_url_org = "https://wa.me/{$whatsapp_numero_org}?text=" . urlencode($mensagem_org);
            ?>
              <a href="<?= $whatsapp_url_org ?>"
                target="_blank"
                class="block w-full bg-brand-yellow text-brand-green py-2 rounded-lg font-medium hover:bg-yellow-400 transition-colors text-center">
                <i class="fab fa-whatsapp mr-2"></i>
                Falar com organizador
              </a>
            <?php endif; ?>
          </div>

          <!-- Redes Sociais -->
          <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4 text-center">Compartilhe</h3>
            <div class="flex justify-center space-x-4">
              <a href="<?= $redes['facebook'] ?>" target="_blank" class="bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition-colors">
                <i class="fab fa-facebook"></i>
              </a>
              <a href="<?= $redes['instagram'] ?>" target="_blank" class="bg-pink-600 text-white p-3 rounded-full hover:bg-pink-700 transition-colors">
                <i class="fab fa-instagram"></i>
              </a>
              <a href="<?= $redes['whatsapp'] ?>" target="_blank" class="bg-green-600 text-white p-3 rounded-full hover:bg-green-700 transition-colors">
                <i class="fab fa-whatsapp"></i>
              </a>
              <a href="<?= $redes['site'] ?>" target="_blank" class="bg-gray-600 text-white p-3 rounded-full hover:bg-gray-700 transition-colors">
                <i class="fas fa-globe"></i>
              </a>
            </div>
          </div>

          <!-- Links Úteis -->
          <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4 text-center">Links Úteis</h3>
            <div class="space-y-3 text-center">
              <?php if (!empty($regulamento_url)): ?>
                <a href="<?= htmlspecialchars($regulamento_url) ?>" target="_blank" rel="noopener"
                   class="block w-full py-3 px-4 rounded-lg bg-brand-green text-white font-semibold hover:bg-green-700 transition-colors">
                  <i class="fas fa-file-pdf mr-2"></i> Regulamento do evento
                </a>
              <?php else: ?>
                <span class="block py-3 px-4 rounded-lg bg-gray-200 text-gray-500 font-medium">Regulamento não disponível</span>
              <?php endif; ?>
              <a href="#" class="block text-sm text-brand-green hover:text-green-700 py-1">
                Dúvidas sobre inscrição
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mapa -->
    <div class="mt-6 sm:mt-8 bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6">
      <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 mb-4 sm:mb-6 text-center">Localização</h2>
      <div class="flex items-center space-x-2 mb-4">
        <i class="fas fa-map-marker-alt text-brand-green"></i>
        <span class="text-gray-700 text-sm sm:text-base"><?= htmlspecialchars($largada) ?></span>
        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($largada) ?>"
          class="text-brand-green underline ml-2 hover:text-green-700 text-xs sm:text-sm" target="_blank">
          Ver mais no mapa
        </a>
      </div>
      <div class="rounded-lg sm:rounded-xl overflow-hidden border">
        <iframe src="https://www.google.com/maps?q=<?= urlencode($largada) ?>&output=embed"
          width="100%" height="250" class="sm:h-[300px]" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white py-12 mt-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
          <h3 class="text-lg font-semibold mb-4">Eventos</h3>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="hover:text-brand-green">Próximos eventos</a></li>
            <li><a href="#" class="hover:text-brand-green">Eventos passados</a></li>
            <li><a href="#" class="hover:text-brand-green">Calendário</a></li>
          </ul>
        </div>
        <div>
          <h3 class="text-lg font-semibold mb-4">Participantes</h3>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="hover:text-brand-green">Como se inscrever</a></li>
            <li><a href="#" class="hover:text-brand-green">Área do participante</a></li>
            <li><a href="#" class="hover:text-brand-green">Dúvidas frequentes</a></li>
          </ul>
        </div>
        <div>
          <h3 class="text-lg font-semibold mb-4">Organizadores</h3>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="hover:text-brand-green">Criar evento</a></li>
            <li><a href="#" class="hover:text-brand-green">Painel administrativo</a></li>
            <li><a href="#" class="hover:text-brand-green">Suporte</a></li>
          </ul>
        </div>
        <div>
          <h3 class="text-lg font-semibold mb-4">Contato</h3>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="hover:text-brand-green">WhatsApp: (99) 98202-7654</a></li>
            <li><a href="#" class="hover:text-brand-green">Email: suporte@movamazon.com.br</a></li>
            <li><a href="#" class="hover:text-brand-green">Atendimento: 9h às 18h</a></li>
          </ul>
        </div>
      </div>
      <div class="border-t border-gray-700 mt-8 pt-8 text-center">
        <p class="text-sm text-gray-400">&copy; 2024 MovAmazon. Todos os direitos reservados.</p>
      </div>
    </div>
  </footer>

  <!-- JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    window.eventoId = <?= $evento_id ?>;

    // Função para verificar se usuário está logado
    async function verificarUsuarioLogado() {
      try {
        const response = await fetch('../../../api/auth/check_session.php');
        const data = await response.json();
        return data.logged_in;
      } catch (error) {
        console.error('Erro ao verificar sessão:', error);
        return false;
      }
    }

    // Função para abrir inscrição
    async function abrirInscricao() {
      const logado = await verificarUsuarioLogado();

      if (!logado) {
        const eventoId = window.eventoId;
        const redirectUrl = `../inscricao/login-inscricao.php?evento_id=${eventoId}`;

        Swal.fire({
          icon: 'info',
          title: 'Login necessário',
          text: 'Para se inscrever no evento, você precisa fazer login.',
          showConfirmButton: true,
          confirmButtonText: 'Ir para Login',
          showCancelButton: true,
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = redirectUrl;
          }
        });
        return;
      }

      // Usuário logado - redirecionar para página de inscrição
      const eventoId = window.eventoId;
      const inscricaoUrl = `../inscricao/index.php?evento_id=${eventoId}`;
      window.location.href = inscricaoUrl;
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
      const btnInscrever = document.getElementById('btn-inscrever-aside');
      if (btnInscrever) {
        btnInscrever.addEventListener('click', abrirInscricao);
      }

      // Menu mobile
      const mobileMenuBtn = document.getElementById('mobile-menu-btn');
      const mobileMenu = document.getElementById('mobile-menu');
      
      if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
          mobileMenu.classList.toggle('hidden');
        });
      }
    });
  </script>
</body>

</html>
