<?php
session_start();
require_once '../db.php';
require_once '../mercadolivre/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit;
}

$payment_id = $_GET['payment_id'] ?? null;
$inscricao_id = $_GET['inscricao_id'] ?? null;

if (!$payment_id && !$inscricao_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'payment_id ou inscricao_id é obrigatório']);
    exit;
}

try {
    // Se temos inscricao_id, buscar payment_id (pagamentos_ml ou, para PIX, inscricoes.external_reference)
    if ($inscricao_id && !$payment_id) {
        $stmt_payment = $pdo->prepare("SELECT payment_id FROM pagamentos_ml WHERE inscricao_id = ? ORDER BY data_criacao DESC LIMIT 1");
        $stmt_payment->execute([$inscricao_id]);
        $row_ml = $stmt_payment->fetch(PDO::FETCH_ASSOC);
        if ($row_ml && !empty($row_ml['payment_id'])) {
            $payment_id = $row_ml['payment_id'];
        } else {
            $stmt = $pdo->prepare("SELECT external_reference FROM inscricoes WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$inscricao_id, $_SESSION['user_id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['external_reference']) && preg_match('/^\d+$/', (string)$row['external_reference'])) {
                $payment_id = $row['external_reference']; // PIX: create_pix grava payment_id aqui
            }
        }
    }

    if (!$payment_id) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Pagamento não encontrado']);
        exit;
    }

    // Consultar dados do pagamento no Mercado Pago
    $config_path = __DIR__ . '/../mercadolivre/config.php';
    if (!file_exists($config_path)) {
        throw new Exception('Arquivo de configuração não encontrado');
    }
    $config = require $config_path;
    $access_token = $config['accesstoken_test'] ?? $config['access_token'] ?? $config['accesstoken'] ?? null;
    
    if (!$access_token) {
        throw new Exception('Token de acesso não configurado');
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.mercadopago.com/v1/payments/$payment_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        ],
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        throw new Exception("Erro ao consultar pagamento: $error");
    }

    if ($http_code !== 200) {
        http_response_code($http_code);
        echo json_encode(['success' => false, 'error' => 'Erro ao consultar pagamento no Mercado Pago', 'http_code' => $http_code]);
        exit;
    }

    $payment_data = json_decode($response, true);
    
    if (!$payment_data) {
        throw new Exception('Resposta inválida do Mercado Pago');
    }

    $external_reference = $payment_data['external_reference'] ?? null;
    $status = $payment_data['status'] ?? null;

    // Buscar dados completos da inscrição: por id (quando temos inscricao_id, ex. PIX) ou por external_reference
    if ($inscricao_id) {
        $stmt = $pdo->prepare("
            SELECT 
                i.*,
                e.nome as evento_nome,
                COALESCE(e.data_realizacao, e.data_inicio) as data_evento,
                e.local_evento,
                m.nome as modalidade_nome,
                m.numero_lote,
                u.nome_completo as usuario_nome,
                u.email as usuario_email,
                u.documento as usuario_documento
            FROM inscricoes i
            INNER JOIN eventos e ON i.evento_id = e.id
            INNER JOIN modalidades m ON i.modalidade_evento_id = m.id
            INNER JOIN usuarios u ON i.usuario_id = u.id
            WHERE i.id = ? AND i.usuario_id = ?
        ");
        $stmt->execute([$inscricao_id, $_SESSION['user_id']]);
    } else {
        if (!$external_reference) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'external_reference não encontrado']);
            exit;
        }
        $stmt = $pdo->prepare("
            SELECT 
                i.*,
                e.nome as evento_nome,
                COALESCE(e.data_realizacao, e.data_inicio) as data_evento,
                e.local_evento,
                m.nome as modalidade_nome,
                m.numero_lote,
                u.nome_completo as usuario_nome,
                u.email as usuario_email,
                u.documento as usuario_documento
            FROM inscricoes i
            INNER JOIN eventos e ON i.evento_id = e.id
            INNER JOIN modalidades m ON i.modalidade_evento_id = m.id
            INNER JOIN usuarios u ON i.usuario_id = u.id
            WHERE i.external_reference = ? AND i.usuario_id = ?
        ");
        $stmt->execute([$external_reference, $_SESSION['user_id']]);
    }
    $inscricao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscricao) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Inscrição não encontrada']);
        exit;
    }

    // Mapear status
    $status_map = [
        'approved' => ['status' => 'aprovado', 'label' => 'Aprovado', 'color' => 'green'],
        'pending' => ['status' => 'pendente', 'label' => 'Pendente', 'color' => 'yellow'],
        'in_process' => ['status' => 'processando', 'label' => 'Processando', 'color' => 'blue'],
        'rejected' => ['status' => 'rejeitado', 'label' => 'Rejeitado', 'color' => 'red'],
        'cancelled' => ['status' => 'cancelado', 'label' => 'Cancelado', 'color' => 'gray'],
        'refunded' => ['status' => 'reembolsado', 'label' => 'Reembolsado', 'color' => 'orange']
    ];

    $status_info = $status_map[$status] ?? ['status' => 'desconhecido', 'label' => 'Desconhecido', 'color' => 'gray'];

    // Buscar dados do pagamento na tabela pagamentos_ml
    $stmt_payment = $pdo->prepare("
        SELECT * FROM pagamentos_ml 
        WHERE payment_id = ? AND inscricao_id = ?
        ORDER BY data_atualizacao DESC 
        LIMIT 1
    ");
    $stmt_payment->execute([$payment_id, $inscricao['id']]);
    $payment_db = $stmt_payment->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'payment' => [
            'id' => $payment_id,
            'status' => $status,
            'status_label' => $status_info['label'],
            'status_color' => $status_info['color'],
            'transaction_amount' => $payment_data['transaction_amount'] ?? $inscricao['valor_total'],
            'payment_method_id' => $payment_data['payment_method_id'] ?? null,
            'payment_type_id' => $payment_data['payment_type_id'] ?? null,
            'installments' => $payment_data['installments'] ?? 1,
            'date_approved' => $payment_data['date_approved'] ?? null,
            'date_created' => $payment_data['date_created'] ?? null,
            'payer' => [
                'email' => $payment_data['payer']['email'] ?? $inscricao['usuario_email'],
                'identification' => $payment_data['payer']['identification']['number'] ?? null
            ]
        ],
        'inscricao' => [
            'id' => $inscricao['id'],
            'external_reference' => $external_reference,
            'evento_nome' => $inscricao['evento_nome'],
            'data_evento' => $inscricao['data_evento'],
            'local_evento' => $inscricao['local_evento'],
            'modalidade_nome' => $inscricao['modalidade_nome'],
            'numero_lote' => $inscricao['numero_lote'],
            'valor_total' => $inscricao['valor_total'],
            'tamanho_camiseta' => $inscricao['tamanho_camiseta'],
            'status_pagamento' => $inscricao['status_pagamento'],
            'data_pagamento' => $inscricao['data_pagamento'],
            'usuario_nome' => $inscricao['usuario_nome'],
            'usuario_email' => $inscricao['usuario_email']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

