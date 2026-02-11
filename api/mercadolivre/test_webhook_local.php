<?php
/**
 * Script de Teste Local do Webhook do Mercado Pago
 * 
 * Este script simula o payload que o Mercado Pago envia para o webhook,
 * permitindo testar a lógica de processamento localmente sem depender
 * de uma URL pública ou do ambiente de produção.
 * 
 * USO:
 *   php api/mercadolivre/test_webhook_local.php
 * 
 * OU via POST:
 *   curl -X POST http://localhost/movamazon/api/mercadolivre/test_webhook_local.php \
 *        -H "Content-Type: application/json" \
 *        -d '{"payment_id":"123456789","external_reference":"MOVAMAZON_123","status":"approved"}'
 */

echo "=== TESTE LOCAL DO WEBHOOK MERCADO PAGO ===\n\n";

// Função auxiliar para buscar variáveis de ambiente
function envValue($key, $default = '') {
    // Tentar getenv primeiro
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return (string) $val;
    }
    // Tentar $_ENV
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }
    // Tentar $_SERVER
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return (string) $_SERVER[$key];
    }
    return (string) $default;
}

// Tentar carregar .env manualmente (sem dotenv)
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentários
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        // Processar linhas KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remover aspas se houver
            $value = trim($value, '"\'');
            if (!empty($key)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Conectar diretamente ao banco (sem passar pelo db.php que pode fazer exit)
$host = trim(envValue('DB_HOST', 'localhost'));
$db = trim(envValue('DB_NAME', 'brunor90_movamazon'));
$user = trim(envValue('DB_USER', 'root'));
$pass = envValue('DB_PASS', '');

echo "🔌 Conectando ao banco de dados...\n";
echo "   Host: $host\n";
echo "   Database: $db\n";
echo "   User: $user\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Conexão estabelecida com sucesso!\n\n";
} catch (PDOException $e) {
    die("❌ ERRO: Não foi possível conectar ao banco de dados.\n" .
        "   Mensagem: " . $e->getMessage() . "\n\n" .
        "   Verifique as configurações no arquivo .env:\n" .
        "   DB_HOST=$host\n" .
        "   DB_NAME=$db\n" .
        "   DB_USER=$user\n" .
        "   DB_PASS=" . (empty($pass) ? '(vazio)' : '***') . "\n\n");
}

// Verificar se helpers existem antes de carregar
if (file_exists(__DIR__ . '/../helpers/email_helper.php')) {
    require_once __DIR__ . '/../helpers/email_helper.php';
}

if (file_exists(__DIR__ . '/../helpers/inscricao_logger.php')) {
    require_once __DIR__ . '/../helpers/inscricao_logger.php';
}

// Verificar conexão com banco
if (!isset($pdo) || !$pdo) {
    die("❌ ERRO: Conexão com banco de dados não estabelecida.\n" .
        "   Verifique as variáveis DB_HOST, DB_NAME, DB_USER, DB_PASS no arquivo .env\n");
}

// Permitir apenas em ambiente de desenvolvimento
// Em CLI, sempre permitir (é local por definição)
$is_cli = php_sapi_name() === 'cli';
$is_local = $is_cli 
            || in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'localhost:8080', 'localhost:8000']) 
            || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false;

if (!$is_local && !isset($_GET['force'])) {
    if (!$is_cli) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Este script só pode ser executado em ambiente local de desenvolvimento.',
            'host' => $_SERVER['HTTP_HOST'] ?? 'desconhecido'
        ]);
    } else {
        echo "❌ ERRO: Este script só pode ser executado em ambiente local de desenvolvimento.\n";
    }
    exit;
}

echo "=== TESTE LOCAL DO WEBHOOK MERCADO PAGO ===\n\n";

// Buscar uma inscrição de teste do banco
try {
    $stmt = $pdo->prepare("
        SELECT i.id, i.external_reference, i.status_pagamento, i.valor_total, i.usuario_id, i.tamanho_camiseta,
               u.nome_completo, u.email, e.nome as evento_nome
        FROM inscricoes i
        JOIN usuarios u ON i.usuario_id = u.id
        JOIN eventos e ON i.evento_id = e.id
        WHERE i.external_reference IS NOT NULL 
        AND i.external_reference != ''
        ORDER BY i.id DESC
        LIMIT 1
    ");
    $stmt->execute();
    $inscricao_teste = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$inscricao_teste) {
        echo "❌ ERRO: Nenhuma inscrição com external_reference encontrada no banco.\n";
        echo "   Crie uma inscrição primeiro ou forneça os dados manualmente.\n\n";
        exit(1);
    }
    
    echo "✅ Inscrição de teste encontrada:\n";
    echo "   ID: {$inscricao_teste['id']}\n";
    echo "   External Reference: {$inscricao_teste['external_reference']}\n";
    echo "   Status Atual: {$inscricao_teste['status_pagamento']}\n";
    echo "   Valor Total: R$ " . number_format($inscricao_teste['valor_total'], 2, ',', '.') . "\n\n";
    
} catch (Exception $e) {
    echo "❌ ERRO ao buscar inscrição: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Simular dados do pagamento do Mercado Pago
$payment_id = $_POST['payment_id'] ?? $_GET['payment_id'] ?? 'TEST_' . time();
$status_teste = $_POST['status'] ?? $_GET['status'] ?? 'approved';
$external_ref = $_POST['external_reference'] ?? $_GET['external_reference'] ?? $inscricao_teste['external_reference'];

echo "📋 Parâmetros do teste:\n";
echo "   Payment ID: $payment_id\n";
echo "   Status: $status_teste\n";
echo "   External Reference: $external_ref\n\n";

// Simular payload do webhook do Mercado Pago
$webhook_payload = [
    'type' => 'payment',
    'data' => [
        'id' => $payment_id
    ]
];

echo "📨 Payload simulado do webhook:\n";
echo json_encode($webhook_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Simular resposta da API do Mercado Pago para GET /v1/payments/{id}
$payment_data_simulado = [
    'id' => $payment_id,
    'status' => $status_teste,
    'status_detail' => $status_teste === 'approved' ? 'accredited' : 'pending',
    'external_reference' => $external_ref,
    'transaction_amount' => (float)$inscricao_teste['valor_total'],
    'payment_method_id' => 'pix',
    'payment_type_id' => 'bank_transfer',
    'installments' => 1,
    'fee_details' => [
        [
            'type' => 'mercadopago_fee',
            'amount' => 2.50
        ]
    ],
    'date_created' => date('Y-m-d\TH:i:s.000-04:00'),
    'date_approved' => $status_teste === 'approved' ? date('Y-m-d\TH:i:s.000-04:00') : null,
    'payer' => [
        'email' => $inscricao_teste['email'],
        'identification' => [
            'type' => 'CPF',
            'number' => '12345678900'
        ]
    ]
];

echo "💳 Dados simulados do pagamento (resposta da API MP):\n";
echo json_encode($payment_data_simulado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Agora simular o processamento do webhook.php
echo "🔄 Processando webhook...\n\n";

// Incluir o código do webhook.php (mas vamos interceptar a chamada à API)
// Vamos criar uma versão modificada que usa os dados simulados

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [];
file_put_contents('php://input', json_encode($webhook_payload));

// Mock da resposta da API do Mercado Pago
$config = require __DIR__ . '/config.php';
$access_token = $config['accesstoken'] ?? '';

if (empty($access_token)) {
    echo "⚠️  AVISO: Access token não configurado. Usando dados simulados.\n\n";
}

// Simular o processamento do webhook
try {
    $pdo->beginTransaction();
    
    // Buscar inscrição pelo external_reference (alinhado ao webhook: incluir i.status)
    $stmt_find = $pdo->prepare(
        "SELECT i.id, i.usuario_id, i.valor_total, i.tamanho_camiseta, i.status_pagamento, i.status,
                u.email as user_email, u.nome_completo as user_name, e.nome as event_name
         FROM inscricoes i
         JOIN usuarios u ON i.usuario_id = u.id
         JOIN eventos e ON i.evento_id = e.id
         WHERE i.external_reference = ?"
    );
    $stmt_find->execute([$external_ref]);
    $inscricao = $stmt_find->fetch(PDO::FETCH_ASSOC);
    
    if (!$inscricao) {
        throw new Exception("Inscrição não encontrada para external_reference: $external_ref");
    }
    
    echo "✅ Inscrição encontrada: ID {$inscricao['id']}\n";
    
    // Mapear status
    $status_inscricao_map = [
        'approved' => 'pago',
        'pending' => 'pendente',
        'in_process' => 'processando',
        'rejected' => 'rejeitado',
        'cancelled' => 'cancelado',
        'refunded' => 'reembolsado'
    ];
    
    $status_pagamentos_ml_map = [
        'approved' => 'pago',
        'pending' => 'pendente',
        'in_process' => 'processando',
        'rejected' => 'rejeitado',
        'cancelled' => 'cancelado',
        'refunded' => 'cancelado'
    ];
    
    $status_pagamentos_map = [
        'approved' => 'pago',
        'pending' => 'pendente',
        'in_process' => 'pendente',
        'rejected' => 'pendente',
        'cancelled' => 'cancelado',
        'refunded' => 'cancelado'
    ];
    
    $novo_status_inscricao = $status_inscricao_map[$status_teste] ?? 'pendente';
    $novo_status_pagamentos_ml = $status_pagamentos_ml_map[$status_teste] ?? 'pendente';
    $novo_status_pagamentos = $status_pagamentos_map[$status_teste] ?? 'pendente';
    
    echo "📊 Status mapeados:\n";
    echo "   Inscrição: {$inscricao['status_pagamento']} → $novo_status_inscricao\n";
    echo "   Pagamentos ML: → $novo_status_pagamentos_ml\n";
    echo "   Pagamentos: → $novo_status_pagamentos\n\n";
    
    // Extrair dados do pagamento
    $valor_pago = (float)($payment_data_simulado['transaction_amount'] ?? 0);
    $metodo_pagamento = $payment_data_simulado['payment_method_id'] ?? $payment_data_simulado['payment_type_id'] ?? null;
    $parcelas = (int)($payment_data_simulado['installments'] ?? 1);
    $taxa_ml = 0.0;
    if (!empty($payment_data_simulado['fee_details']) && is_array($payment_data_simulado['fee_details'])) {
        foreach ($payment_data_simulado['fee_details'] as $fee) {
            if (isset($fee['amount'])) {
                $taxa_ml += (float)$fee['amount'];
            }
        }
    }
    
    echo "💰 Dados financeiros:\n";
    echo "   Valor Pago: R$ " . number_format($valor_pago, 2, ',', '.') . "\n";
    echo "   Método: $metodo_pagamento\n";
    echo "   Parcelas: $parcelas\n";
    echo "   Taxa ML: R$ " . number_format($taxa_ml, 2, ',', '.') . "\n\n";
    
    // Atualizar inscrição (mesma regra do webhook: status sempre valor válido do enum)
    $novo_status = ($novo_status_inscricao === 'pago')
        ? 'confirmada'
        : (isset($inscricao['status']) && in_array($inscricao['status'], ['pendente', 'confirmada', 'cancelada'], true)
            ? $inscricao['status']
            : 'pendente');
    $data_pagamento = ($novo_status_inscricao === 'pago') ? date('Y-m-d H:i:s') : null;
    
    $stmt_update = $pdo->prepare(
        "UPDATE inscricoes SET 
            status = ?,
            status_pagamento = ?, 
            data_pagamento = ?, 
            forma_pagamento = 'mercadolivre'
            WHERE id = ?"
    );
    $stmt_update->execute([$novo_status, $novo_status_inscricao, $data_pagamento, $inscricao['id']]);
    
    echo "✅ Inscrição atualizada:\n";
    echo "   Status: $novo_status\n";
    echo "   Status Pagamento: $novo_status_inscricao\n";
    echo "   Data Pagamento: " . ($data_pagamento ?? 'NULL') . "\n\n";
    
    // Verificar se tabela pagamentos_ml existe
    $hasPagamentosMl = false;
    try {
        $stmtTbl = $pdo->query("SHOW TABLES LIKE 'pagamentos_ml'");
        $hasPagamentosMl = $stmtTbl && $stmtTbl->rowCount() > 0;
    } catch (Exception $e) {
        $hasPagamentosMl = false;
    }
    
    if ($hasPagamentosMl) {
        // Buscar ou criar registro em pagamentos_ml
        $stmt_check = $pdo->prepare("SELECT id, preference_id, init_point FROM pagamentos_ml WHERE inscricao_id = ? LIMIT 1");
        $stmt_check->execute([$inscricao['id']]);
        $pagamento_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        $preference_id = $pagamento_existente['preference_id'] ?? '';
        $init_point = $pagamento_existente['init_point'] ?? '';
        
        $stmt_check_pagamentos_ml = $pdo->prepare("SELECT id FROM pagamentos_ml WHERE payment_id = ? LIMIT 1");
        $stmt_check_pagamentos_ml->execute([$payment_id]);
        $pagamento_ml_existente = $stmt_check_pagamentos_ml->fetch(PDO::FETCH_ASSOC);
        
        $dados_pagamento_json = json_encode($payment_data_simulado, JSON_UNESCAPED_UNICODE);
        
        if ($pagamento_ml_existente) {
            $stmt_pagamentos_ml = $pdo->prepare(
                "UPDATE pagamentos_ml SET 
                    payment_id = ?,
                    status = ?, 
                    valor_pago = ?,
                    metodo_pagamento = ?,
                    parcelas = ?,
                    taxa_ml = ?,
                    dados_pagamento = ?,
                    data_atualizacao = NOW()
                    WHERE id = ?"
            );
            $stmt_pagamentos_ml->execute([
                $payment_id,
                $novo_status_pagamentos_ml,
                $valor_pago,
                $metodo_pagamento,
                $parcelas,
                $taxa_ml,
                $dados_pagamento_json,
                $pagamento_ml_existente['id']
            ]);
            echo "✅ Pagamentos ML atualizado (ID: {$pagamento_ml_existente['id']})\n\n";
        } else {
            $preference_id_final = !empty($preference_id) ? $preference_id : 'webhook_' . $payment_id;
            $init_point_final = !empty($init_point) ? $init_point : 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=' . $preference_id_final;
            
            $stmt_pagamentos_ml = $pdo->prepare(
                "INSERT INTO pagamentos_ml (
                    inscricao_id, payment_id, preference_id, init_point, status,
                    valor_pago, metodo_pagamento, parcelas, taxa_ml,
                    dados_pagamento, user_id, data_criacao, data_atualizacao
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
            );
            $stmt_pagamentos_ml->execute([
                $inscricao['id'],
                $payment_id,
                $preference_id_final,
                $init_point_final,
                $novo_status_pagamentos_ml,
                $valor_pago,
                $metodo_pagamento,
                $parcelas,
                $taxa_ml,
                $dados_pagamento_json,
                $inscricao['usuario_id']
            ]);
            echo "✅ Pagamentos ML inserido (novo registro)\n\n";
        }
    } else {
        echo "⚠️  Tabela pagamentos_ml não existe (pulando)\n\n";
    }
    
    // Atualizar tabela pagamentos (idempotente por payment_id quando coluna existir; alinhado ao webhook)
    $has_payment_id_col = false;
    try {
        $pdo->query("SELECT payment_id FROM pagamentos LIMIT 0");
        $has_payment_id_col = true;
    } catch (Exception $e) {
        $has_payment_id_col = false;
    }
    if ($has_payment_id_col) {
        $stmt_by_payment = $pdo->prepare("SELECT id, inscricao_id, status FROM pagamentos WHERE payment_id = ? LIMIT 1");
        $stmt_by_payment->execute([$payment_id]);
        $row_by_payment = $stmt_by_payment->fetch(PDO::FETCH_ASSOC);
        if ($row_by_payment) {
            $stmt_pagamentos = $pdo->prepare("UPDATE pagamentos SET status = ?, valor_pago = ?, forma_pagamento = ?, data_pagamento = NOW() WHERE id = ?");
            $stmt_pagamentos->execute([$novo_status_pagamentos, $valor_pago, $metodo_pagamento, $row_by_payment['id']]);
            echo "✅ Pagamentos atualizado por payment_id (ID: {$row_by_payment['id']})\n\n";
        } else {
            $stmt_by_insc = $pdo->prepare("SELECT id FROM pagamentos WHERE inscricao_id = ? AND status = 'pago' LIMIT 1");
            $stmt_by_insc->execute([$inscricao['id']]);
            $row_by_insc = $stmt_by_insc->fetch(PDO::FETCH_ASSOC);
            if ($row_by_insc) {
                $stmt_pagamentos = $pdo->prepare("UPDATE pagamentos SET payment_id = ?, status = ?, valor_pago = ?, forma_pagamento = ?, data_pagamento = NOW() WHERE id = ?");
                $stmt_pagamentos->execute([$payment_id, $novo_status_pagamentos, $valor_pago, $metodo_pagamento, $row_by_insc['id']]);
                echo "✅ Pagamentos atualizado e payment_id setado (ID: {$row_by_insc['id']})\n\n";
            } else {
                $stmt_pagamentos = $pdo->prepare("INSERT INTO pagamentos (inscricao_id, forma_pagamento, data_pagamento, valor_total, valor_pago, status, payment_id) VALUES (?, ?, NOW(), ?, ?, ?, ?)");
                $stmt_pagamentos->execute([$inscricao['id'], $metodo_pagamento, $inscricao['valor_total'] ?: $valor_pago, $valor_pago, $novo_status_pagamentos, $payment_id]);
                echo "✅ Pagamentos inserido com payment_id (novo registro)\n\n";
            }
        }
    } else {
        $stmt_check_pagamentos = $pdo->prepare("SELECT id FROM pagamentos WHERE inscricao_id = ? AND status = 'pago' LIMIT 1");
        $stmt_check_pagamentos->execute([$inscricao['id']]);
        $pagamento_geral_existente = $stmt_check_pagamentos->fetch(PDO::FETCH_ASSOC);
        if ($pagamento_geral_existente) {
            $stmt_pagamentos = $pdo->prepare("UPDATE pagamentos SET status = ?, valor_pago = ?, forma_pagamento = ?, data_pagamento = NOW() WHERE inscricao_id = ?");
            $stmt_pagamentos->execute([$novo_status_pagamentos, $valor_pago, $metodo_pagamento, $inscricao['id']]);
            echo "✅ Pagamentos atualizado (ID: {$pagamento_geral_existente['id']})\n\n";
        } else {
            $stmt_pagamentos = $pdo->prepare("INSERT INTO pagamentos (inscricao_id, forma_pagamento, data_pagamento, valor_total, valor_pago, status) VALUES (?, ?, NOW(), ?, ?, ?)");
            $stmt_pagamentos->execute([$inscricao['id'], $metodo_pagamento, $inscricao['valor_total'] ?: $valor_pago, $valor_pago, $novo_status_pagamentos]);
            echo "✅ Pagamentos inserido (novo registro)\n\n";
        }
    }
    
    // Atualizar estoque de camisas (se pagamento aprovado)
    if ($novo_status_inscricao === 'pago' && !empty($inscricao['tamanho_camiseta'])) {
        $stmt_estoque = $pdo->prepare(
            "UPDATE camisas SET quantidade_vendida = quantidade_vendida + 1, quantidade_disponivel = quantidade_disponivel - 1 
             WHERE tamanho = ? AND evento_id = (SELECT evento_id FROM inscricoes WHERE id = ?)"
        );
        $stmt_estoque->execute([$inscricao['tamanho_camiseta'], $inscricao['id']]);
        echo "✅ Estoque de camisas atualizado (tamanho: {$inscricao['tamanho_camiseta']})\n\n";
    }
    
    $pdo->commit();

    // Cenário não aprovado: garantir que inscricoes.status não ficou NULL e é valor válido
    if ($status_teste !== 'approved') {
        $stmt_verif = $pdo->prepare("SELECT status FROM inscricoes WHERE id = ? LIMIT 1");
        $stmt_verif->execute([$inscricao['id']]);
        $row_verif = $stmt_verif->fetch(PDO::FETCH_ASSOC);
        $status_inscricao_apos = $row_verif['status'] ?? null;
        if ($status_inscricao_apos === null || !in_array($status_inscricao_apos, ['pendente', 'confirmada', 'cancelada'], true)) {
            echo "⚠️  AVISO (cenário não aprovado): inscricoes.status após commit = " . var_export($status_inscricao_apos, true) . " (deve ser pendente/confirmada/cancelada)\n\n";
        } else {
            echo "✅ Cenário não aprovado: inscricoes.status preservado = $status_inscricao_apos\n\n";
        }
    }

    echo "✅✅✅ TESTE CONCLUÍDO COM SUCESSO! ✅✅✅\n\n";
    echo "📊 Resumo:\n";
    echo "   - Inscrição ID {$inscricao['id']} atualizada\n";
    echo "   - Status: {$inscricao['status_pagamento']} → $novo_status_inscricao\n";
    if ($hasPagamentosMl) {
        echo "   - Tabela pagamentos_ml atualizada\n";
    }
    echo "   - Tabela pagamentos atualizada\n";
    if ($novo_status_inscricao === 'pago') {
        echo "   - Estoque de camisas atualizado\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo "❌❌❌ ERRO NO TESTE ❌❌❌\n\n";
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
