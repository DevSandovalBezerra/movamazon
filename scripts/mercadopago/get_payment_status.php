<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/MercadoLivrePayment.php';

// Verificar se usuário está logado
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

try {
    $payment_id = $_GET['payment_id'] ?? null;
    $preference_id = $_GET['preference_id'] ?? null;
    
    if (!$payment_id && !$preference_id) {
        throw new Exception('ID do pagamento ou preferência não informado');
    }
    
    $ml_payment = new MercadoLivrePayment();
    
    if ($payment_id) {
        // Consultar por payment_id
        $resultado = $ml_payment->consultarStatus($payment_id);
    } else {
        // Consultar por preference_id (buscar payment_id primeiro)
        $resultado = consultarPorPreferenceId($preference_id);
    }
    
    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'payment' => $resultado['payment']
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $resultado['error']
        ]);
    }
    
} catch (Exception $e) {
    error_log('Erro em get_payment_status.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Consultar pagamento por preference_id
 */
function consultarPorPreferenceId($preference_id) {
    global $pdo;
    
    try {
        // Buscar payment_id no banco
        $stmt = $pdo->prepare("
            SELECT payment_id 
            FROM pagamentos_ml 
            WHERE preference_id = ? 
            AND payment_id IS NOT NULL
        ");
        $stmt->execute([$preference_id]);
        $pagamento = $stmt->fetch();
        
        if (!$pagamento) {
            return [
                'success' => false,
                'error' => 'Pagamento não encontrado'
            ];
        }
        
        // Consultar status no ML
        $ml_payment = new MercadoLivrePayment();
        return $ml_payment->consultarStatus($pagamento['payment_id']);
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
?>
