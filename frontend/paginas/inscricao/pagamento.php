<?php
// Verificar se está sendo incluído pelo index.php
$isIncluded = isset($GLOBALS['INSCRICAO_INCLUDED']) && $GLOBALS['INSCRICAO_INCLUDED'] === true;

// ✅ NOTA: Validações de redirecionamento foram movidas para index.php
// para evitar erro "headers already sent". Aqui apenas validamos dados.

// Remover ou proteger session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Garantir que temos os dados necessários (sem redirecionamento aqui)
if (!isset($_SESSION['inscricao']) || empty($_SESSION['inscricao']['modalidades_selecionadas'])) {
    // Se chegou aqui sem dados, algo deu errado - mostrar erro sem redirecionar
    die('<div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6 text-center">
            <div class="text-red-500 text-6xl mb-4">⚠️</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Erro nos Dados</h2>
            <p class="text-gray-600 mb-6">Não foi possível carregar os dados da inscrição.</p>
            <a href="index.php?evento_id=' . ($_GET['evento_id'] ?? 1) . '&etapa=1" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">Voltar ao Início</a>
        </div>
    </div>');
}

$inscricao = $_SESSION['inscricao'];
$modalidades = $inscricao['modalidades_selecionadas'] ?? [];
$evento_id = $inscricao['evento_id'];

// ✅ Buscar dados do evento (já validado no index.php, mas buscamos novamente para uso)
require_once '../../../api/db.php';
$stmt = $pdo->prepare("SELECT id, nome, status, percentual_repasse FROM eventos WHERE id = ? AND status = 'ativo'");
$stmt->execute([$evento_id]);
$evento = $stmt->fetch();

// Se evento não encontrado (não deveria acontecer devido à validação no index.php)
if (!$evento) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6 text-center">
            <div class="text-red-500 text-6xl mb-4">⚠️</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Evento Não Encontrado</h2>
            <p class="text-gray-600 mb-6">O evento não está mais disponível.</p>
            <a href="../public/index.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">Voltar</a>
        </div>
    </div>');
}

// ✅ Calcular totais com validação robusta
// Fórmula: modalidade + produtos_extras - desconto_cupom
$total_modalidades = 0;
$modalidades_validas = [];

// Primeira tentativa: usar modalidades da sessão
if (!empty($modalidades)) {
    foreach ($modalidades as $modalidade) {
        $preco = floatval($modalidade['preco_total'] ?? 0);

        if ($preco > 0) {
            $total_modalidades += $preco;
            $modalidades_validas[] = $modalidade;
        }
    }
}

// ✅ Se não há modalidades válidas, buscar diretamente da sessão
if (empty($modalidades_validas) || $total_modalidades <= 0) {
    // Verificar se há dados na sessão de forma diferente
    if (isset($_SESSION['inscricao']['modalidades_selecionadas']) && !empty($_SESSION['inscricao']['modalidades_selecionadas'])) {
        $modalidades_sessao = $_SESSION['inscricao']['modalidades_selecionadas'];

        foreach ($modalidades_sessao as $modalidade) {
            $preco = floatval($modalidade['preco_total'] ?? 0);
            if ($preco > 0) {
                $total_modalidades += $preco;
                $modalidades_validas[] = $modalidade;
            }
        }
    }
}

// ✅ Log para debug do cálculo de modalidades
error_log("[PAGAMENTO] Total modalidades calculado: R$ $total_modalidades");
error_log("[PAGAMENTO] Modalidades válidas encontradas: " . count($modalidades_validas));

// Se não há modalidades válidas, redirecionar
if (empty($modalidades_validas) || $total_modalidades <= 0) {
    error_log("ERRO pagamento.php - Redirecionando: modalidades_validas=" . count($modalidades_validas) . ", total=$total_modalidades");

    // Mostrar erro mais informativo
    echo '<div class="min-h-screen flex items-center justify-center bg-gray-50">';
    echo '<div class="max-w-md w-full bg-white rounded-lg shadow-md p-6 text-center">';
    echo '<div class="text-red-500 text-6xl mb-4">⚠️</div>';
    echo '<h2 class="text-2xl font-bold text-gray-900 mb-4">Erro nos Dados</h2>';
    echo '<p class="text-gray-600 mb-6">Não foi possível carregar os dados da inscrição. Por favor, tente novamente.</p>';
    echo '<a href="index.php?evento_id=' . $evento_id . '&etapa=1" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">Voltar ao Início</a>';
    echo '</div></div>';
    exit;
}

// ✅ DEBUG: Log completo da sessão para diagnóstico
error_log("[PAGAMENTO] ========== DEBUG INÍCIO ==========");
error_log("[PAGAMENTO] Sessão inscricao keys: " . json_encode(array_keys($_SESSION['inscricao'] ?? [])));
error_log("[PAGAMENTO] Ficha existe? " . (isset($_SESSION['inscricao']['ficha']) ? 'SIM' : 'NÃO'));
if (isset($_SESSION['inscricao']['ficha'])) {
    error_log("[PAGAMENTO] Ficha keys: " . json_encode(array_keys($_SESSION['inscricao']['ficha'])));
    error_log("[PAGAMENTO] Produtos extras na ficha: " . json_encode($_SESSION['inscricao']['ficha']['produtos_extras'] ?? null));
}
error_log("[PAGAMENTO] ID da inscrição na sessão: " . ($_SESSION['inscricao']['id'] ?? 'NULL'));

// ✅ Buscar produtos extras selecionados na ficha (não mais disponíveis)
$produtos_extras_selecionados = [];
$total_produtos_extras = 0;

if (isset($inscricao['ficha']['produtos_extras']) && !empty($inscricao['ficha']['produtos_extras'])) {
    $produtos_extras_selecionados = $inscricao['ficha']['produtos_extras'];
    error_log("[PAGAMENTO] ✅ Produtos extras encontrados na SESSÃO: " . count($produtos_extras_selecionados));
    foreach ($produtos_extras_selecionados as $produto) {
        $valor = floatval($produto['valor'] ?? 0);
        $total_produtos_extras += $valor;
        error_log("[PAGAMENTO] Produto: " . ($produto['nome'] ?? 'sem nome') . " = R$ $valor");
    }
} else {
    error_log("[PAGAMENTO] ⚠️ NENHUM produto extra encontrado na sessão");
    error_log("[PAGAMENTO] isset(ficha.produtos_extras)? " . (isset($inscricao['ficha']['produtos_extras']) ? 'SIM' : 'NÃO'));
    error_log("[PAGAMENTO] empty(ficha.produtos_extras)? " . (empty($inscricao['ficha']['produtos_extras']) ? 'SIM' : 'NÃO'));
    
    // ✅ FALLBACK: Buscar do banco de dados se não encontrou na sessão
    if (!empty($_SESSION['inscricao']['id'])) {
        $inscricaoId = $_SESSION['inscricao']['id'];
        error_log("[PAGAMENTO] 🔄 Tentando buscar produtos extras do BANCO (inscricao_id=$inscricaoId)");
        
        $stmt = $pdo->prepare("SELECT produtos_extras_ids FROM inscricoes WHERE id = ?");
        $stmt->execute([$inscricaoId]);
        $inscricaoDb = $stmt->fetch();
        
        if ($inscricaoDb && !empty($inscricaoDb['produtos_extras_ids'])) {
            $produtosExtrasDb = json_decode($inscricaoDb['produtos_extras_ids'], true);
            error_log("[PAGAMENTO] Produtos no banco (JSON): " . $inscricaoDb['produtos_extras_ids']);
            error_log("[PAGAMENTO] Produtos decodificados: " . json_encode($produtosExtrasDb));
            
            if (is_array($produtosExtrasDb) && !empty($produtosExtrasDb)) {
                $produtos_extras_selecionados = $produtosExtrasDb;
                foreach ($produtos_extras_selecionados as $produto) {
                    $valor = floatval($produto['valor'] ?? 0);
                    $total_produtos_extras += $valor;
                    error_log("[PAGAMENTO] ✅ Produto (do banco): " . ($produto['nome'] ?? 'sem nome') . " = R$ $valor");
                }
                error_log("[PAGAMENTO] ✅ Total de produtos extras recuperados do banco: " . count($produtos_extras_selecionados));
            } else {
                error_log("[PAGAMENTO] ❌ Produtos extras no banco estão vazios ou inválidos");
            }
        } else {
            error_log("[PAGAMENTO] ❌ Nenhum produto extra encontrado no banco");
        }
    } else {
        error_log("[PAGAMENTO] ❌ ID da inscrição não disponível, não é possível buscar do banco");
    }
}

error_log("[PAGAMENTO] Total final de produtos extras: " . count($produtos_extras_selecionados) . " items, R$ $total_produtos_extras");

// ✅ Calcular valor do desconto (cupom)
$valor_desconto = 0;
if (isset($inscricao['ficha']['valor_desconto'])) {
    $valor_desconto = floatval($inscricao['ficha']['valor_desconto'] ?? 0);
} elseif (isset($_SESSION['inscricao']['ficha']['valor_desconto'])) {
    $valor_desconto = floatval($_SESSION['inscricao']['ficha']['valor_desconto'] ?? 0);
}

// ✅ Calcular total final: modalidade + produtos_extras - desconto
$total_final_calculado = max(0, $total_modalidades + $total_produtos_extras - $valor_desconto);

error_log("[PAGAMENTO] Valor desconto: R$ $valor_desconto");
error_log("[PAGAMENTO] Total final calculado (modalidades + extras - desconto): R$ $total_final_calculado");
error_log("[PAGAMENTO] ========== DEBUG FIM ==========");

// Buscar dados do usuário para o resumo
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch();

// Recuperar tamanho da camiseta do banco se não estiver na sessão
if (empty($inscricao['ficha']['tamanho_camiseta'])) {
    // Tentar recuperar pelo ID da inscrição se existir
    if (!empty($inscricao['id'])) {
        $stmt = $pdo->prepare("SELECT tamanho_camiseta FROM inscricoes WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$inscricao['id'], $_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && !empty($result['tamanho_camiseta'])) {
            if (!isset($inscricao['ficha'])) {
                $inscricao['ficha'] = [];
            }
            $inscricao['ficha']['tamanho_camiseta'] = $result['tamanho_camiseta'];
        }
    } else {
        // Se não tiver ID da inscrição, tentar buscar pelo usuario_id + evento_id
        $stmt = $pdo->prepare("SELECT tamanho_camiseta FROM inscricoes WHERE usuario_id = ? AND evento_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$_SESSION['user_id'], $evento_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && !empty($result['tamanho_camiseta'])) {
            if (!isset($inscricao['ficha'])) {
                $inscricao['ficha'] = [];
            }
            $inscricao['ficha']['tamanho_camiseta'] = $result['tamanho_camiseta'];
        }
    }
}

// Configurar progress bar apenas se não estiver sendo incluído
if (!$isIncluded) {
    $etapas = [
        1 => ['nome' => 'Modalidade', 'status' => 'concluida'],
        2 => ['nome' => 'Termos', 'status' => 'concluida'],
        3 => ['nome' => 'Cadastro', 'status' => 'concluida'],
        4 => ['nome' => 'Pagamento', 'status' => 'atual']
    ];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - MovAmazon</title>
    <link href="../../assets/css/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script src="https://www.mercadopago.com/v2/security.js" view="checkout"></script>

    <style>
        /* Estilos do Mercado Pago */
        .input-field {
            @apply w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200;
        }

        .form-group {
            @apply relative;
        }

        .form-group .input-field {
            @apply bg-white;
        }

        .progress-bar {
            @apply h-2 rounded-full bg-gray-200;
        }

        .progress-bar::-webkit-progress-bar {
            @apply bg-gray-200 rounded-full;
        }

        .progress-bar::-webkit-progress-value {
            @apply bg-blue-600 rounded-full;
        }

        .progress-bar::-moz-progress-bar {
            @apply bg-blue-600 rounded-full;
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <img src="../../assets/img/logo.png" alt="MovAmazon" class="h-8 w-auto">
                        <span class="ml-2 text-xl font-bold text-gray-900">MovAmazon</span>
                    </div>
                    <div class="text-sm text-gray-500">
                        Etapa 4 de 4 - Pagamento
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de Progresso -->
        <?php include 'includes/progress_bar.php'; ?>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
<?php } else {
    // Se está sendo incluído, apenas definir $etapas para o progress_bar.php do index.php
    // NÃO incluir progress_bar.php aqui pois já foi incluído pelo index.php
    $etapas = [
        1 => ['nome' => 'Modalidade', 'status' => 'concluida'],
        2 => ['nome' => 'Termos', 'status' => 'concluida'],
        3 => ['nome' => 'Cadastro', 'status' => 'concluida'],
        4 => ['nome' => 'Pagamento', 'status' => 'atual']
    ];
?>
    <!-- SDK Mercado Pago - necessário quando incluído -->
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script src="https://www.mercadopago.com/v2/security.js" view="checkout"></script>
        <div class="py-6">
<?php } ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Conteúdo Principal -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Modalidades Selecionadas -->
                    <?php if (!empty($modalidades_validas)): ?>
                        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-route text-blue-600 mr-2"></i>
                                Modalidades Selecionadas
                            </h3>
                            <div class="space-y-3">
                                <?php foreach ($modalidades_validas as $modalidade): ?>
                                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                                        <div>
                                            <h4 class="font-medium text-gray-900"><?= htmlspecialchars($modalidade['nome'] ?? 'Modalidade') ?></h4>
                                            <?php if (isset($modalidade['lote_numero']) && $modalidade['lote_numero']): ?>
                                                <p class="text-sm text-gray-600">Lote <?= $modalidade['lote_numero'] ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-lg font-semibold text-green-600">
                                            R$ <?= number_format(floatval($modalidade['preco_total'] ?? 0), 2, ',', '.') ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- ✅ Resumo das Escolhas Anteriores -->
                    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-clipboard-check text-green-600 mr-2"></i>
                            Resumo das Suas Escolhas
                        </h3>

                        <!-- Dados do Participante -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">
                                <i class="fas fa-user text-blue-600 mr-2"></i>
                                Dados do Participante
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Nome:</span>
                                    <span class="font-medium"><?= htmlspecialchars($usuario['nome_completo'] ?? 'N/A') ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">E-mail:</span>
                                    <span class="font-medium"><?= htmlspecialchars($usuario['email'] ?? 'N/A') ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">CPF:</span>
                                    <span class="font-medium"><?= isset($usuario['documento']) ? substr($usuario['documento'], 0, 3) . '.***.***-' . substr($usuario['documento'], -2) : 'N/A' ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Tamanho Camiseta:</span>
                                    <span class="font-medium"><?= htmlspecialchars($inscricao['ficha']['tamanho_camiseta'] ?? $inscricao['tamanho_camiseta'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Produtos Extras Selecionados -->
                        <?php if (!empty($produtos_extras_selecionados)): ?>
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-900 mb-3">
                                    <i class="fas fa-plus-circle text-orange-600 mr-2"></i>
                                    Produtos Adicionais Selecionados
                                </h4>
                                <div class="space-y-2">
                                    <?php foreach ($produtos_extras_selecionados as $produto): ?>
                                        <div class="flex justify-between items-center py-2 px-3 bg-orange-50 rounded-lg">
                                            <div>
                                                <span class="font-medium text-gray-900"><?= htmlspecialchars($produto['nome'] ?? 'Produto') ?></span>
                                                <?php if (isset($produto['descricao']) && !empty($produto['descricao'])): ?>
                                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($produto['descricao']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <span class="text-lg font-semibold text-orange-600">
                                                R$ <?= number_format(floatval($produto['valor'] ?? 0), 2, ',', '.') ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Cupom Aplicado -->
                        <?php if (isset($inscricao['ficha']['cupom_aplicado']) && $inscricao['ficha']['cupom_aplicado']): ?>
                            <div class="mb-6 p-4 bg-green-50 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">
                                    <i class="fas fa-tag text-green-600 mr-2"></i>
                                    Cupom de Desconto Aplicado
                                </h4>
                                <div class="flex justify-between items-center">
                                    <span class="text-green-800 font-medium"><?= htmlspecialchars($inscricao['ficha']['cupom_aplicado']) ?></span>
                                    <span class="text-green-600 font-semibold">
                                        -R$ <?= number_format(floatval($inscricao['ficha']['valor_desconto'] ?? 0), 2, ',', '.') ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>


                </div>

                <!-- Resumo da Compra -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-shopping-cart text-blue-600 mr-2"></i>
                            Resumo da Compra
                        </h3>
                        <div id="resumo-compra"></div>
                        <div class="mt-6 pt-4 border-t">
                            <div class="flex justify-between items-center text-lg font-semibold">
                                <span>Total</span>
                                <span id="total-geral" class="text-green-600">R$ 0,00</span>
                            </div>
                        </div>
                        <div class="mt-6 text-center">
                            <button id="btn-finalizar-compra" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors text-lg font-bold">
                                <i class="fas fa-credit-card mr-2"></i>
                                Finalizar Compra
                            </button>
                            <p id="payment-flow-hint" class="text-xs text-gray-500 mt-2">Você será redirecionado ao Mercado Pago para pagar com cartão, PIX ou boleto.</p>
                        </div>
                    </div>

                    <!-- Container de pagamento na página (controlado por flag de runtime) -->
                    <div id="janela-pagamento-mercadopago" class="hidden bg-white rounded-lg shadow-sm border p-6 mb-6">
                        <div class="mb-4 text-center">
                            <img src="../../assets/img/mercadopago-logo.png" alt="Mercado Pago" class="h-8 mx-auto mb-2">
                            <p class="text-sm text-gray-600">Pagamento 100% seguro</p>
                        </div>

                        <div id="paymentBrick_container" class="w-full"></div>

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="text-center mb-4">
                                <h4 class="text-md font-semibold text-gray-700 mb-2">Pagamento Instantâneo</h4>
                                <p class="text-sm text-gray-600 mb-4">Pague instantaneamente com PIX</p>
                                <button id="btn-pix-pagamento" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transition-all duration-200 flex items-center gap-2 mx-auto">
                                    <span>Pagar com PIX</span>
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Pagamento instantâneo e seguro</p>
                            </div>
                            <div id="pix-container" class="hidden mt-4"></div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="text-center mb-4">
                                <h4 class="text-md font-semibold text-gray-700 mb-2">Pagamento com Boleto</h4>
                                <p class="text-sm text-gray-600 mb-4">Pague com boleto bancário (válido por 3 dias)</p>
                                <button id="btn-boleto-pagamento" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transition-all duration-200 flex items-center gap-2 mx-auto">
                                    <span>Pagar com Boleto</span>
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Compensação em até 2 dias úteis após pagamento</p>
                            </div>
                            <div id="boleto-container" class="hidden mt-4"></div>
                        </div>

                        <div id="statusScreenBrick_container" class="w-full"></div>
                        <div class="mt-4">
                            <button id="btn-voltar-resumo" class="w-full bg-gray-100 text-gray-700 font-medium py-2 px-6 rounded-lg hover:bg-gray-200 transition-colors">
                                Voltar ao resumo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-sm mx-4">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="text-gray-700">Processando pagamento...</span>
            </div>
        </div>
    </div>

    <script src="../../js/mercadopago-config.js"></script>
    <script src="../../js/inscricao/pagamento.js"></script>
    <script>
        // ✅ Dados estruturados e validados para JavaScript
        // Fórmula: modalidade + produtos_extras - desconto_cupom
        window.dadosInscricao = {
            modalidades: <?= json_encode($modalidades_validas) ?>,
            evento: <?= json_encode($evento) ?>,
            eventoId: <?= json_encode($evento_id) ?>,
            totalModalidades: <?= $total_modalidades ?>,
            produtosExtras: <?= json_encode($produtos_extras_selecionados) ?>, // ✅ Produtos já selecionados
            totalProdutosExtras: <?= $total_produtos_extras ?>,
            cupomAplicado: <?= json_encode($inscricao['ficha']['cupom_aplicado'] ?? null) ?>,
            valorDesconto: <?= $valor_desconto ?>,
            inscricaoId: <?= json_encode($inscricao['id'] ?? null) ?>,
            percentualRepasse: <?= floatval($evento['percentual_repasse'] ?? 0) ?>,
            timestamp: <?= time() ?>,
            // ✅ Dados adicionais para debug
            debug: {
                modalidadesCount: <?= count($modalidades_validas) ?>,
                totalModalidades: <?= $total_modalidades ?>,
                totalProdutosExtras: <?= $total_produtos_extras ?>,
                valorDesconto: <?= $valor_desconto ?>,
                totalFinalCalculado: <?= $total_final_calculado ?>,
                produtosExtrasCount: <?= count($produtos_extras_selecionados) ?>,
                sessaoCompleta: <?= json_encode($_SESSION['inscricao'] ?? []) ?>
            }
        };

        // Debug detalhado
        console.log('✅ Dados de inscrição carregados:', window.dadosInscricao);
        console.log('✅ Modalidades válidas:', window.dadosInscricao.modalidades);
        console.log('✅ Total modalidades:', window.dadosInscricao.totalModalidades);
        console.log('✅ Produtos extras:', window.dadosInscricao.produtosExtras);
        console.log('✅ Total produtos extras:', window.dadosInscricao.totalProdutosExtras);
        console.log('✅ Percentual repasse:', window.dadosInscricao.percentualRepasse, '%');

        // UX: manter orientação e visibilidade alinhadas ao modo de checkout ativo.
        const janelaPagamento = document.getElementById('janela-pagamento-mercadopago');
        const paymentFlowHint = document.getElementById('payment-flow-hint');
        if (typeof USE_CHECKOUT_PRO_REDIRECT !== 'undefined' && USE_CHECKOUT_PRO_REDIRECT) {
            if (janelaPagamento) janelaPagamento.classList.add('hidden');
            if (paymentFlowHint) paymentFlowHint.textContent = 'Você será redirecionado ao Mercado Pago para pagar com cartão, PIX ou boleto.';
        } else {
            if (janelaPagamento) janelaPagamento.classList.remove('hidden');
            if (paymentFlowHint) paymentFlowHint.textContent = 'Escolha abaixo: cartão no formulário, PIX instantâneo ou boleto.';
        }

        // ✅ Função temporária para garantir que o total seja calculado
        function calcularTotalTemporario() {
            const totalModalidades = window.dadosInscricao?.totalModalidades || 0;
            const totalProdutosExtras = window.dadosInscricao?.totalProdutosExtras || 0;
            const valorDesconto = window.dadosInscricao?.valorDesconto || 0;
            const total = totalModalidades + totalProdutosExtras - valorDesconto;

            console.log('💰 Cálculo temporário:', {
                totalModalidades,
                totalProdutosExtras,
                valorDesconto,
                total
            });

            return total;
        }

        // ✅ Renderizar resumo da compra diretamente
        function renderizarResumoCompraTemporario() {
            const resumoContainer = document.getElementById('resumo-compra');
            if (!resumoContainer) {
                console.error('❌ Container resumo-compra não encontrado');
                return;
            }

            const modalidades = window.dadosInscricao?.modalidades || [];
            const produtosExtras = window.dadosInscricao?.produtosExtras || [];
            const valorModalidades = window.dadosInscricao?.totalModalidades || 0;
            const valorExtras = window.dadosInscricao?.totalProdutosExtras || 0;
            const valorDesconto = window.dadosInscricao?.valorDesconto || 0;

            let html = '<div class="space-y-3">';

            // Modalidades
            if (modalidades.length > 0) {
                modalidades.forEach(modalidade => {
                    const preco = parseFloat(modalidade.preco_total || 0);
                    html += `
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-700">${modalidade.nome || 'Modalidade'}</span>
                            <span class="font-medium text-gray-900">R$ ${preco.toFixed(2).replace('.', ',')}</span>
                        </div>
                    `;
                });
            } else if (valorModalidades > 0) {
                html += `
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <span class="text-gray-700">Inscrição</span>
                        <span class="font-medium text-gray-900">R$ ${valorModalidades.toFixed(2).replace('.', ',')}</span>
                    </div>
                `;
            }

            // Produtos extras
            if (produtosExtras.length > 0) {
                produtosExtras.forEach(produto => {
                    const valor = parseFloat(produto.valor || 0);
                    html += `
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-700">+ ${produto.nome || 'Produto'}</span>
                            <span class="font-medium text-gray-900">R$ ${valor.toFixed(2).replace('.', ',')}</span>
                        </div>
                    `;
                });
            } else if (valorExtras > 0) {
                html += `
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <span class="text-gray-700">+ Produtos Extras</span>
                        <span class="font-medium text-gray-900">R$ ${valorExtras.toFixed(2).replace('.', ',')}</span>
                    </div>
                `;
            }

            // Desconto
            if (valorDesconto > 0) {
                html += `
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <span class="text-green-600">- Desconto</span>
                        <span class="font-medium text-green-600">-R$ ${valorDesconto.toFixed(2).replace('.', ',')}</span>
                    </div>
                `;
            }

            html += '</div>';
            resumoContainer.innerHTML = html;
            console.log('✅ Resumo da compra renderizado temporariamente');
        }

        // ✅ Executar renderização usando funcoes de pagamento.js (que incluem taxa de repasse)
        // Garantir que window.dadosInscricao está populado antes de calcular
        function inicializarResumoPagamento() {
            if (!window.dadosInscricao) {
                console.warn('[PAGAMENTO] window.dadosInscricao ainda não está disponível, aguardando...');
                setTimeout(inicializarResumoPagamento, 100);
                return;
            }

            console.log('[PAGAMENTO] Renderizando resumo com taxa. percentualRepasse=', window.dadosInscricao.percentualRepasse);
            console.log('[PAGAMENTO] Valores disponíveis:', {
                totalModalidades: window.dadosInscricao.totalModalidades,
                totalProdutosExtras: window.dadosInscricao.totalProdutosExtras,
                valorDesconto: window.dadosInscricao.valorDesconto
            });

            if (typeof renderizarResumoCompra === 'function') {
                renderizarResumoCompra();
            } else {
                console.warn('[PAGAMENTO] renderizarResumoCompra não está disponível');
            }

            if (typeof updateTotalAmount === 'function') {
                updateTotalAmount();
            } else {
                console.warn('[PAGAMENTO] updateTotalAmount não está disponível');
            }

            console.log('[PAGAMENTO] Resumo e total atualizados com taxa de repasse');
        }

        // Aguardar carregamento completo do DOM e scripts
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(inicializarResumoPagamento, 500);
            });
        } else {
            setTimeout(inicializarResumoPagamento, 500);
        }

        // ✅ Validação final robusta
        if (!window.dadosInscricao.modalidades || window.dadosInscricao.modalidades.length === 0) {
            console.error('❌ Nenhuma modalidade válida encontrada');
            console.error('❌ Debug info:', window.dadosInscricao.debug);

            Swal.fire({
                icon: 'error',
                title: 'Erro nos dados',
                text: 'Nenhuma modalidade válida encontrada. Redirecionando...',
                timer: 3000
            }).then(() => {
                window.location.href = 'index.php?evento_id=' + window.dadosInscricao.eventoId + '&etapa=1';
            });
        } else if (window.dadosInscricao.totalModalidades <= 0) {
            console.error('❌ Valor total inválido:', window.dadosInscricao.totalModalidades);

            Swal.fire({
                icon: 'error',
                title: 'Valor inválido',
                text: 'O valor total da inscrição é inválido. Redirecionando...',
                timer: 3000
            }).then(() => {
                window.location.href = 'index.php?evento_id=' + window.dadosInscricao.eventoId + '&etapa=1';
            });
        } else {
            console.log('✅ Dados válidos - Pronto para pagamento');
        }
    </script>
<?php
// Se não estiver incluído, incluir footer antes de fechar body/html
if (!$isIncluded) {
    include 'includes/footer-inscricao.php';
}
// Se estiver incluído, não fechar body/html (index.php já inclui o footer)
?>
