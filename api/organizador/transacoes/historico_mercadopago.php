<?php
/**
 * 📊 CONSULTAR HISTÓRICO DE TRANSAÇÕES DO MERCADO PAGO
 * 
 * Busca diretamente na API do Mercado Pago todas as transações (bem-sucedidas e falhadas)
 * 
 * Endpoint: GET /v1/payments/search
 * Documentação: https://www.mercadopago.com.br/developers/en/reference/payments/_payments_search/get
 * 
 * FILTROS DISPONÍVEIS:
 * - status: approved, pending, rejected, cancelled, refunded, in_process
 * - begin_date / end_date: Intervalo de datas
 * - external_reference: Referência da inscrição
 * - limit / offset: Paginação
 */

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../mercadolivre/config.php';

header('Content-Type: application/json');

try {
    // ========================================
    // 🔐 AUTENTICAÇÃO
    // ========================================
    
    session_start();
    if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'organizador') {
        http_response_code(401);
        echo json_encode(['error' => 'Não autorizado']);
        exit();
    }

    $organizador_id = $_SESSION['usuario_id'];

    // ========================================
    // 📋 PARÂMETROS DE FILTRO
    // ========================================
    
    $filtros = [
        'status' => $_GET['status'] ?? null,          // approved, rejected, pending, etc
        'begin_date' => $_GET['begin_date'] ?? null,  // YYYY-MM-DD
        'end_date' => $_GET['end_date'] ?? null,      // YYYY-MM-DD
        'external_reference' => $_GET['external_reference'] ?? null, // INSCRIÇÃO_123
        'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 100,
        'offset' => isset($_GET['offset']) ? (int)$_GET['offset'] : 0,
        'sort' => $_GET['sort'] ?? 'date_created',    // date_created, date_approved
        'order' => $_GET['order'] ?? 'desc'            // asc, desc
    ];

    // ========================================
    // 🔧 CARREGAR CONFIGURAÇÃO MP
    // ========================================
    
    $config = require __DIR__ . '/../../mercadolivre/config.php';
    $access_token = $config['accesstoken'] ?? '';

    if (empty($access_token)) {
        throw new Exception('Token do Mercado Pago não configurado');
    }

    // ========================================
    // 🔍 CONSTRUIR QUERY STRING
    // ========================================
    
    $query_params = [
        'access_token' => $access_token,
        'limit' => $filtros['limit'],
        'offset' => $filtros['offset'],
        'sort' => $filtros['sort'],
        'criteria' => $filtros['order']
    ];

    // Adicionar filtros opcionais
    if ($filtros['status']) {
        $query_params['status'] = $filtros['status'];
    }

    if ($filtros['begin_date']) {
        $query_params['begin_date'] = $filtros['begin_date'] . 'T00:00:00.000-00:00';
    }

    if ($filtros['end_date']) {
        $query_params['end_date'] = $filtros['end_date'] . 'T23:59:59.999-00:00';
    }

    if ($filtros['external_reference']) {
        $query_params['external_reference'] = $filtros['external_reference'];
    }

    $query_string = http_build_query($query_params);
    $api_url = "https://api.mercadopago.com/v1/payments/search?{$query_string}";

    // ========================================
    // 📡 CONSULTAR API DO MERCADO PAGO
    // ========================================
    
    error_log("[HISTORICO_MP] Consultando: $api_url");

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        ],
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);

    if ($curl_error) {
        throw new Exception("Erro cURL: $curl_error");
    }

    if ($http_code !== 200) {
        error_log("[HISTORICO_MP] Erro HTTP $http_code: $response");
        throw new Exception("Erro ao consultar Mercado Pago (HTTP $http_code)");
    }

    $resultado_mp = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Resposta inválida da API do Mercado Pago");
    }

    // ========================================
    // 🔄 ENRIQUECER DADOS COM INFORMAÇÕES LOCAIS
    // ========================================
    
    $payments = $resultado_mp['results'] ?? [];
    $total = $resultado_mp['paging']['total'] ?? count($payments);

    // Buscar inscrições relacionadas
    $external_refs = array_filter(array_column($payments, 'external_reference'));
    $inscricoes_map = [];

    if (!empty($external_refs)) {
        $placeholders = implode(',', array_fill(0, count($external_refs), '?'));
        $stmt = $pdo->prepare(
            "SELECT i.id, i.external_reference, i.usuario_id, i.evento_id, i.valor_total,
                    u.nome_completo, u.email,
                    e.nome as evento_nome
             FROM inscricoes i
             JOIN usuarios u ON i.usuario_id = u.id
             JOIN eventos e ON i.evento_id = e.id
             WHERE i.external_reference IN ($placeholders)"
        );
        $stmt->execute($external_refs);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $inscricoes_map[$row['external_reference']] = $row;
        }
    }

    // ========================================
    // 📊 FORMATAR RESPOSTA
    // ========================================
    
    $transacoes = [];
    foreach ($payments as $payment) {
        $external_ref = $payment['external_reference'] ?? null;
        $inscricao = $external_ref ? ($inscricoes_map[$external_ref] ?? null) : null;

        $transacao = [
            // Dados do Mercado Pago
            'payment_id' => $payment['id'],
            'status' => $payment['status'],
            'status_detail' => $payment['status_detail'] ?? null,
            'external_reference' => $external_ref,
            'transaction_amount' => (float)($payment['transaction_amount'] ?? 0),
            'net_amount' => (float)($payment['transaction_amount_refunded'] ?? 0),
            'payment_method_id' => $payment['payment_method_id'] ?? null,
            'payment_type_id' => $payment['payment_type_id'] ?? null,
            'installments' => (int)($payment['installments'] ?? 1),
            'date_created' => $payment['date_created'] ?? null,
            'date_approved' => $payment['date_approved'] ?? null,
            'date_last_updated' => $payment['date_last_updated'] ?? null,
            
            // Dados do comprador
            'payer' => [
                'email' => $payment['payer']['email'] ?? null,
                'identification' => $payment['payer']['identification'] ?? null,
                'first_name' => $payment['payer']['first_name'] ?? null,
                'last_name' => $payment['payer']['last_name'] ?? null,
            ],
            
            // Taxas
            'fee_details' => $payment['fee_details'] ?? [],
            'transaction_details' => $payment['transaction_details'] ?? [],
            
            // ✅ Dados locais (se encontrados)
            'inscricao' => $inscricao ? [
                'id' => $inscricao['id'],
                'usuario_nome' => $inscricao['nome_completo'],
                'usuario_email' => $inscricao['email'],
                'evento_nome' => $inscricao['evento_nome'],
                'valor_total' => (float)$inscricao['valor_total']
            ] : null,
            
            // Status traduzido
            'status_traduzido' => traduzirStatus($payment['status']),
            'status_cor' => corStatus($payment['status'])
        ];

        $transacoes[] = $transacao;
    }

    // ========================================
    // 📈 ESTATÍSTICAS
    // ========================================
    
    $stats = calcularEstatisticas($transacoes);

    // ========================================
    // ✅ RESPOSTA
    // ========================================
    
    echo json_encode([
        'success' => true,
        'filtros_aplicados' => array_filter($filtros),
        'paginacao' => [
            'total' => $total,
            'limit' => $filtros['limit'],
            'offset' => $filtros['offset'],
            'has_next' => ($filtros['offset'] + $filtros['limit']) < $total,
            'has_prev' => $filtros['offset'] > 0
        ],
        'estatisticas' => $stats,
        'transacoes' => $transacoes,
        'total_transacoes' => count($transacoes)
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("[HISTORICO_MP] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// ========================================
// 🎨 FUNÇÕES AUXILIARES
// ========================================

function traduzirStatus($status) {
    $map = [
        'approved' => 'Aprovado',
        'pending' => 'Pendente',
        'in_process' => 'Em Processamento',
        'rejected' => 'Rejeitado',
        'cancelled' => 'Cancelado',
        'refunded' => 'Reembolsado',
        'charged_back' => 'Estornado'
    ];
    return $map[$status] ?? $status;
}

function corStatus($status) {
    $map = [
        'approved' => 'success',
        'pending' => 'warning',
        'in_process' => 'info',
        'rejected' => 'danger',
        'cancelled' => 'secondary',
        'refunded' => 'dark',
        'charged_back' => 'danger'
    ];
    return $map[$status] ?? 'secondary';
}

function calcularEstatisticas($transacoes) {
    $stats = [
        'total_transacoes' => count($transacoes),
        'por_status' => [],
        'valor_total_aprovado' => 0,
        'valor_total_rejeitado' => 0,
        'valor_total_pendente' => 0,
        'taxa_aprovacao' => 0
    ];

    foreach ($transacoes as $t) {
        $status = $t['status'];
        
        if (!isset($stats['por_status'][$status])) {
            $stats['por_status'][$status] = [
                'count' => 0,
                'valor_total' => 0
            ];
        }
        
        $stats['por_status'][$status]['count']++;
        $stats['por_status'][$status]['valor_total'] += $t['transaction_amount'];
        
        if ($status === 'approved') {
            $stats['valor_total_aprovado'] += $t['transaction_amount'];
        } elseif ($status === 'rejected') {
            $stats['valor_total_rejeitado'] += $t['transaction_amount'];
        } elseif ($status === 'pending' || $status === 'in_process') {
            $stats['valor_total_pendente'] += $t['transaction_amount'];
        }
    }

    if ($stats['total_transacoes'] > 0) {
        $aprovados = $stats['por_status']['approved']['count'] ?? 0;
        $stats['taxa_aprovacao'] = round(($aprovados / $stats['total_transacoes']) * 100, 2);
    }

    return $stats;
}
