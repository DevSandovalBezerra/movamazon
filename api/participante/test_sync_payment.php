<?php
/**
 * Script de teste para verificar se sync_payment_status.php está criando registros em pagamentos_ml
 * 
 * USO: Acesse via navegador ou linha de comando:
 * php test_sync_payment.php inscricao_id=18
 * 
 * Ou via navegador (requer autenticação):
 * /api/participante/test_sync_payment.php?inscricao_id=18
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

// Para linha de comando
if (php_sapi_name() === 'cli') {
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
}

$inscricao_id = $_GET['inscricao_id'] ?? null;

if (!$inscricao_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'inscricao_id é obrigatório']);
    exit();
}

try {
    // Verificar estado ANTES do sync
    $stmt_before = $pdo->prepare("
        SELECT 
            i.id,
            i.status,
            i.status_pagamento,
            i.external_reference,
            i.numero_inscricao,
            (SELECT COUNT(*) FROM pagamentos_ml WHERE inscricao_id = i.id) as tem_registro_ml
        FROM inscricoes i
        WHERE i.id = ?
    ");
    $stmt_before->execute([$inscricao_id]);
    $antes = $stmt_before->fetch(PDO::FETCH_ASSOC);

    if (!$antes) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Inscrição não encontrada']);
        exit();
    }

    // Executar sync (simular chamada)
    // Nota: Em produção, você chamaria o endpoint real
    $sync_url = __DIR__ . '/sync_payment_status.php?inscricao_id=' . $inscricao_id;
    
    // Verificar estado DEPOIS do sync
    $stmt_after = $pdo->prepare("
        SELECT 
            i.id,
            i.status,
            i.status_pagamento,
            i.external_reference,
            i.numero_inscricao,
            (SELECT COUNT(*) FROM pagamentos_ml WHERE inscricao_id = i.id) as tem_registro_ml,
            pm.id as pagamento_ml_id,
            pm.payment_id,
            pm.status as pm_status,
            pm.valor_pago
        FROM inscricoes i
        LEFT JOIN pagamentos_ml pm ON pm.inscricao_id = i.id
        WHERE i.id = ?
    ");
    $stmt_after->execute([$inscricao_id]);
    $depois = $stmt_after->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'antes' => [
            'status' => $antes['status'],
            'status_pagamento' => $antes['status_pagamento'],
            'external_reference' => $antes['external_reference'],
            'tem_registro_ml' => (bool)$antes['tem_registro_ml']
        ],
        'depois' => [
            'status' => $depois['status'],
            'status_pagamento' => $depois['status_pagamento'],
            'external_reference' => $depois['external_reference'],
            'tem_registro_ml' => (bool)$depois['tem_registro_ml'],
            'pagamento_ml_id' => $depois['pagamento_ml_id'],
            'payment_id' => $depois['payment_id'],
            'pm_status' => $depois['pm_status'],
            'valor_pago' => $depois['valor_pago']
        ],
        'instrucoes' => [
            '1' => 'Execute: /api/participante/sync_payment_status.php?inscricao_id=' . $inscricao_id,
            '2' => 'Depois execute este script novamente para verificar se o registro foi criado',
            '3' => 'Ou verifique diretamente no banco: SELECT * FROM pagamentos_ml WHERE inscricao_id = ' . $inscricao_id
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
