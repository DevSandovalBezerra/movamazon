<?php
/**
 * Diagnóstico do Webhook do Mercado Pago
 * 
 * Este script verifica:
 * 1. Configuração de tokens
 * 2. Existência das tabelas necessárias
 * 3. Permissões de escrita nos logs
 * 4. Últimas entradas de log
 * 5. Inscrições pendentes que podem precisar de sync
 * 
 * Acesso: GET /api/mercadolivre/diagnostico_webhook.php
 * Autenticação: Apenas organizador ou admin
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db.php';

// Verificar autenticação
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit;
}

// Verificar se é organizador ou admin
$tipo = $_SESSION['tipo'] ?? '';
if (!in_array($tipo, ['organizador', 'admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Apenas organizador ou admin.']);
    exit;
}

$diagnostico = [
    'timestamp' => date('Y-m-d H:i:s'),
    'ambiente' => [],
    'tokens' => [],
    'tabelas' => [],
    'logs' => [],
    'inscricoes_pendentes' => [],
    'recomendacoes' => []
];

try {
    // 1. Verificar configuração
    $config = require __DIR__ . '/config.php';
    
    $diagnostico['ambiente'] = [
        'environment' => $config['environment'] ?? 'não definido',
        'is_production' => $config['is_production'] ?? false,
        'notification_url' => $config['url_notification_api'] ?? 'não definida',
        'base_url_debug' => $config['_debug_base_url'] ?? 'não definida'
    ];
    
    // 2. Verificar tokens (mostrar apenas se existem, não o valor)
    $diagnostico['tokens'] = [
        'access_token_configurado' => !empty($config['accesstoken']),
        'access_token_prefixo' => !empty($config['accesstoken']) ? substr($config['accesstoken'], 0, 8) . '...' : 'N/A',
        'public_key_configurado' => !empty($config['public_key']),
        'has_valid_tokens' => $config['has_valid_tokens'] ?? false
    ];
    
    // 3. Verificar tabelas
    $tabelas_necessarias = ['inscricoes', 'pagamentos_ml', 'pagamentos', 'usuarios', 'eventos'];
    
    foreach ($tabelas_necessarias as $tabela) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
            $existe = $stmt->rowCount() > 0;
            
            $count = 0;
            if ($existe) {
                $count_stmt = $pdo->query("SELECT COUNT(*) FROM `$tabela`");
                $count = $count_stmt->fetchColumn();
            }
            
            $diagnostico['tabelas'][$tabela] = [
                'existe' => $existe,
                'registros' => $count
            ];
        } catch (Exception $e) {
            $diagnostico['tabelas'][$tabela] = [
                'existe' => false,
                'erro' => $e->getMessage()
            ];
        }
    }
    
    // Verificar tabela de logs
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'logs_inscricoes_pagamentos'");
        $diagnostico['tabelas']['logs_inscricoes_pagamentos'] = [
            'existe' => $stmt->rowCount() > 0
        ];
    } catch (Exception $e) {
        $diagnostico['tabelas']['logs_inscricoes_pagamentos'] = [
            'existe' => false,
            'erro' => $e->getMessage()
        ];
    }

    // 3.1 Verificar colunas críticas para o webhook (schema_check)
    $schema_esperado = [
        'inscricoes' => ['status', 'status_pagamento', 'external_reference'],
        'pagamentos' => ['inscricao_id', 'status', 'valor_pago', 'forma_pagamento', 'data_pagamento', 'valor_total', 'payment_id'],
        'pagamentos_ml' => ['payment_id', 'inscricao_id', 'status', 'preference_id']
    ];
    $diagnostico['schema_check'] = [];
    foreach ($schema_esperado as $tabela => $colunas_necessarias) {
        $diagnostico['schema_check'][$tabela] = ['ok' => true, 'faltando' => []];
        if (empty($diagnostico['tabelas'][$tabela]['existe'])) {
            $diagnostico['schema_check'][$tabela]['ok'] = false;
            $diagnostico['schema_check'][$tabela]['faltando'] = $colunas_necessarias;
            continue;
        }
        try {
            $stmt_col = $pdo->query("SHOW COLUMNS FROM `$tabela`");
            $colunas_existentes = [];
            while ($row = $stmt_col->fetch(PDO::FETCH_ASSOC)) {
                $colunas_existentes[] = $row['Field'];
            }
            $faltando = array_diff($colunas_necessarias, $colunas_existentes);
            if (!empty($faltando)) {
                $diagnostico['schema_check'][$tabela]['ok'] = false;
                $diagnostico['schema_check'][$tabela]['faltando'] = array_values($faltando);
            }
        } catch (Exception $e) {
            $diagnostico['schema_check'][$tabela]['ok'] = false;
            $diagnostico['schema_check'][$tabela]['erro'] = $e->getMessage();
        }
    }
    
    // 4. Verificar logs
    $base_path = dirname(dirname(__DIR__));
    $log_files = [
        'webhook_mp.log' => $base_path . '/logs/webhook_mp.log',
        'inscricoes_pagamentos.log' => $base_path . '/logs/inscricoes_pagamentos.log',
        'php_errors.log' => $base_path . '/logs/php_errors.log'
    ];
    
    foreach ($log_files as $nome => $caminho) {
        $diagnostico['logs'][$nome] = [
            'caminho' => $caminho,
            'existe' => file_exists($caminho),
            'gravavel' => is_writable(dirname($caminho)),
            'tamanho' => file_exists($caminho) ? filesize($caminho) : 0,
            'ultimas_linhas' => []
        ];
        
        // Pegar últimas 5 linhas do log
        if (file_exists($caminho) && filesize($caminho) > 0) {
            $linhas = file($caminho);
            $ultimas = array_slice($linhas, -5);
            $diagnostico['logs'][$nome]['ultimas_linhas'] = array_map('trim', $ultimas);
        }
    }
    
    // 5. Verificar inscrições pendentes (últimos 7 dias)
    $stmt = $pdo->prepare("
        SELECT 
            i.id,
            i.external_reference,
            i.status_pagamento,
            i.data_inscricao,
            i.valor_total,
            u.nome_completo as usuario_nome,
            e.nome as evento_nome
        FROM inscricoes i
        JOIN usuarios u ON i.usuario_id = u.id
        JOIN eventos e ON i.evento_id = e.id
        WHERE i.status_pagamento IN ('pendente', 'processando')
        AND i.data_inscricao > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY i.data_inscricao DESC
        LIMIT 10
    ");
    $stmt->execute();
    $pendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $diagnostico['inscricoes_pendentes'] = [
        'total' => count($pendentes),
        'lista' => $pendentes
    ];
    
    // 6. Gerar recomendações
    if (!$config['has_valid_tokens']) {
        $diagnostico['recomendacoes'][] = 'CRÍTICO: Tokens não configurados. Verifique APP_Acess_token e APP_Public_Key no .env';
    }
    
    if (strpos($config['url_notification_api'], 'localhost') !== false) {
        $diagnostico['recomendacoes'][] = 'AVISO: URL do webhook aponta para localhost. Em produção, use o domínio real.';
    }
    
    if (!$diagnostico['tabelas']['pagamentos_ml']['existe']) {
        $diagnostico['recomendacoes'][] = 'CRÍTICO: Tabela pagamentos_ml não existe. Execute as migrations.';
    }
    
    if (!$diagnostico['logs']['webhook_mp.log']['existe']) {
        $diagnostico['recomendacoes'][] = 'AVISO: Arquivo de log do webhook não existe. Será criado na primeira requisição.';
    }
    
    if (count($pendentes) > 5) {
        $diagnostico['recomendacoes'][] = 'AVISO: Há ' . count($pendentes) . ' inscrições pendentes. Considere executar sync manual.';
    }

    foreach ($diagnostico['schema_check'] ?? [] as $tabela => $check) {
        if (empty($check['ok']) && !empty($check['faltando'])) {
            foreach ($check['faltando'] as $col) {
                if ($tabela === 'pagamentos' && $col === 'payment_id') {
                    $diagnostico['recomendacoes'][] = 'Execute a migration add_payment_id_pagamentos.sql para idempotência do webhook em pagamentos.';
                    break;
                }
                $diagnostico['recomendacoes'][] = "Coluna {$tabela}.{$col} necessária para o webhook está ausente.";
            }
        }
    }
    
    if (empty($diagnostico['recomendacoes'])) {
        $diagnostico['recomendacoes'][] = 'OK: Configuração parece estar correta.';
    }
    
    $diagnostico['success'] = true;
    
} catch (Exception $e) {
    $diagnostico['success'] = false;
    $diagnostico['error'] = $e->getMessage();
}

echo json_encode($diagnostico, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
