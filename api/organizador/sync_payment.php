<?php
/**
 * Endpoint para sincronização manual de pagamento
 * Permite ao organizador forçar a sincronização do status de uma inscrição específica
 * 
 * Uso: POST /api/organizador/sync_payment.php
 * Body: { "inscricao_id": 123 }
 * 
 * Retorno:
 * - success: true/false
 * - message: descrição do resultado
 * - status_anterior: status antes da sync
 * - status_novo: status após a sync (se atualizado)
 * - atualizado: true se houve mudança
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Responder OPTIONS para preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/inscricao_logger.php';
require_once __DIR__ . '/../mercadolivre/payment_helper.php';

session_start();

// Verificar autenticação
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

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

// Ler dados do POST
$input = json_decode(file_get_contents('php://input'), true);
$inscricao_id = (int)($input['inscricao_id'] ?? 0);

if ($inscricao_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de inscrição inválido']);
    exit;
}

try {
    $config = require __DIR__ . '/../mercadolivre/config.php';
    
    // Buscar inscrição com dados do pagamento
    $stmt = $pdo->prepare("
        SELECT 
            i.*,
            pm.payment_id,
            pm.preference_id,
            pm.id as pagamento_ml_id,
            u.nome_completo as usuario_nome,
            u.email as usuario_email,
            e.nome as evento_nome
        FROM inscricoes i
        LEFT JOIN pagamentos_ml pm ON pm.inscricao_id = i.id
        JOIN usuarios u ON i.usuario_id = u.id
        JOIN eventos e ON i.evento_id = e.id
        WHERE i.id = ?
    ");
    $stmt->execute([$inscricao_id]);
    $inscricao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$inscricao) {
        throw new Exception('Inscrição não encontrada');
    }
    
    $external_ref = $inscricao['external_reference'];
    $payment_id = $inscricao['payment_id'];
    $status_anterior = $inscricao['status_pagamento'];
    
    // Se não tem payment_id nem external_reference, não há como consultar
    if (empty($payment_id) && empty($external_ref)) {
        echo json_encode([
            'success' => true,
            'message' => 'Inscrição não possui payment_id ou external_reference. Nenhum pagamento associado.',
            'status_atual' => $status_anterior,
            'atualizado' => false
        ]);
        exit;
    }
    
    // Tentar consultar por payment_id primeiro, depois por external_reference
    $payment = null;
    $paymentHelper = new PaymentHelper();
    
    if (!empty($payment_id)) {
        try {
            $payment = $paymentHelper->consultarStatusPagamento($payment_id);
        } catch (Exception $e) {
            error_log("Sync manual: Erro ao consultar payment_id $payment_id: " . $e->getMessage());
        }
    }
    
    // Se não encontrou por payment_id, buscar por external_reference via search
    if (!$payment && !empty($external_ref)) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/search?external_reference=' . urlencode($external_ref),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $config['accesstoken'],
                'Content-Type: application/json'
            ],
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            if (!empty($data['results'])) {
                $payment = $data['results'][0]; // Pegar o mais recente
            }
        }
    }
    
    if (!$payment) {
        echo json_encode([
            'success' => true,
            'message' => 'Nenhum pagamento encontrado no Mercado Pago para esta inscrição',
            'status_atual' => $status_anterior,
            'external_reference' => $external_ref,
            'payment_id' => $payment_id,
            'atualizado' => false
        ]);
        exit;
    }
    
    // Mapear status
    $status_mp = $payment['status'] ?? 'pending';
    $novo_status = PaymentHelper::mapearStatus($status_mp);
    $novo_status_ml = PaymentHelper::mapearStatusPagamentosML($status_mp);
    $payment_id_encontrado = $payment['id'] ?? null;
    
    // Se status mudou, atualizar
    if ($novo_status !== $status_anterior) {
        $pdo->beginTransaction();
        
        try {
            // Atualizar inscrição
            $novo_status_inscricao = ($novo_status === 'pago') ? 'confirmada' : $inscricao['status'];
            $data_pagamento = ($novo_status === 'pago') ? date('Y-m-d H:i:s') : $inscricao['data_pagamento'];
            
            $stmt_upd = $pdo->prepare("
                UPDATE inscricoes SET 
                    status = ?,
                    status_pagamento = ?,
                    data_pagamento = ?
                WHERE id = ?
            ");
            $stmt_upd->execute([$novo_status_inscricao, $novo_status, $data_pagamento, $inscricao_id]);
            
            // Atualizar pagamentos_ml
            if ($inscricao['pagamento_ml_id']) {
                $stmt_ml = $pdo->prepare("
                    UPDATE pagamentos_ml SET 
                        status = ?,
                        payment_id = COALESCE(payment_id, ?),
                        dados_pagamento = ?,
                        data_atualizacao = NOW()
                    WHERE id = ?
                ");
                $stmt_ml->execute([
                    $novo_status_ml,
                    $payment_id_encontrado,
                    json_encode($payment, JSON_UNESCAPED_UNICODE),
                    $inscricao['pagamento_ml_id']
                ]);
            } else {
                // Inserir novo registro se não existe
                $stmt_ins = $pdo->prepare("
                    INSERT INTO pagamentos_ml (
                        inscricao_id, payment_id, preference_id, init_point, 
                        status, dados_pagamento, user_id, data_criacao, data_atualizacao
                    ) VALUES (?, ?, '', '', ?, ?, ?, NOW(), NOW())
                ");
                $stmt_ins->execute([
                    $inscricao_id,
                    $payment_id_encontrado,
                    $novo_status_ml,
                    json_encode($payment, JSON_UNESCAPED_UNICODE),
                    $inscricao['usuario_id']
                ]);
            }
            
            // Log
            logInscricaoPagamento('SUCCESS', 'SYNC_MANUAL_PAGAMENTO', [
                'inscricao_id' => $inscricao_id,
                'status_anterior' => $status_anterior,
                'status_novo' => $novo_status,
                'payment_id' => $payment_id_encontrado,
                'sincronizado_por' => $_SESSION['user_id'],
                'usuario_nome' => $inscricao['usuario_nome'],
                'evento_nome' => $inscricao['evento_nome']
            ]);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => "Status atualizado de '$status_anterior' para '$novo_status'",
                'status_anterior' => $status_anterior,
                'status_novo' => $novo_status,
                'payment_id' => $payment_id_encontrado,
                'usuario' => $inscricao['usuario_nome'],
                'evento' => $inscricao['evento_nome'],
                'atualizado' => true
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Status já está correto, nenhuma atualização necessária',
            'status_atual' => $novo_status,
            'payment_id' => $payment_id_encontrado,
            'atualizado' => false
        ]);
    }
    
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    logInscricaoPagamento('ERROR', 'ERRO_SYNC_MANUAL', [
        'inscricao_id' => $inscricao_id,
        'erro' => $e->getMessage(),
        'sincronizado_por' => $_SESSION['user_id'] ?? null
    ]);
    
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
