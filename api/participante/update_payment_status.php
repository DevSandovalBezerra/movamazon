<?php
/**
 * Endpoint para atualizar status da inscrição após pagamento
 * Usado quando o usuário retorna das páginas de sucesso/pendente/erro do Mercado Pago
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security_middleware.php';

header('Content-Type: application/json');

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

$usuario_id = $_SESSION['user_id'];
$dados = json_decode(file_get_contents('php://input'), true);

// Validar parâmetros
$inscricao_id = $dados['inscricao_id'] ?? null;
$collection_status = $dados['collection_status'] ?? null; // approved, pending, rejected
$preference_id = $dados['preference_id'] ?? null;
$external_reference = $dados['external_reference'] ?? null;

// Se não tem inscricao_id, tentar extrair do external_reference
if (!$inscricao_id && $external_reference) {
    // Formatos aceitos: PREFIXO_ID ou PREFIXO_TIMESTAMP_ID
    if (preg_match('/^[A-Z0-9]+_(\d+)$/', $external_reference, $matches)) {
        $inscricao_id = $matches[1];
    } elseif (preg_match('/^[A-Z0-9]+_\d+_(\d+)$/', $external_reference, $matches)) {
        $inscricao_id = $matches[1];
    } else {
        // Tentar extrair número do final
        $parts = explode('_', $external_reference);
        $last_part = end($parts);
        if (is_numeric($last_part)) {
            $inscricao_id = $last_part;
        }
    }
}

if (!$inscricao_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da inscrição é obrigatório.']);
    exit();
}

try {
    // Buscar inscrição e verificar se pertence ao usuário
    $stmt = $pdo->prepare("
        SELECT id, status, status_pagamento, external_reference, numero_inscricao
        FROM inscricoes 
        WHERE id = ? AND usuario_id = ?
    ");
    $stmt->execute([$inscricao_id, $usuario_id]);
    $inscricao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscricao) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Inscrição não encontrada ou não pertence ao usuário.']);
        exit();
    }

    // Se collection_status foi fornecido, usar ele
    // Senão, verificar na tabela pagamentos_ml (para casos onde o webhook não funcionou)
    $novo_status_pagamento = null;
    
    if ($collection_status) {
        // Mapear status do Mercado Pago para status interno
        $status_pagamento_map = [
            'approved' => 'pago',
            'pending' => 'pendente',
            'rejected' => 'rejeitado',
            'cancelled' => 'cancelado',
            'in_process' => 'processando'
        ];
        $novo_status_pagamento = $status_pagamento_map[$collection_status] ?? $inscricao['status_pagamento'];
    } else {
        // Se não tem collection_status, verificar na tabela pagamentos_ml
        $stmt_payment = $pdo->prepare("
            SELECT status 
            FROM pagamentos_ml 
            WHERE inscricao_id = ? 
            ORDER BY data_atualizacao DESC 
            LIMIT 1
        ");
        $stmt_payment->execute([$inscricao_id]);
        $pagamento = $stmt_payment->fetch(PDO::FETCH_ASSOC);
        
        if ($pagamento) {
            $novo_status_pagamento = $pagamento['status'];
            error_log("[UPDATE_PAYMENT_STATUS] Status obtido da tabela pagamentos_ml: " . $novo_status_pagamento);
        } else {
            $novo_status_pagamento = $inscricao['status_pagamento'];
        }
    }
    
    // Se pagamento aprovado, marcar inscrição como confirmada
    // Se já está confirmada, manter confirmada (não reverter)
    if ($novo_status_pagamento === 'pago') {
        $novo_status = 'confirmada';
    } elseif ($inscricao['status'] === 'confirmada') {
        // Se já está confirmada, manter (não reverter para pendente)
        $novo_status = 'confirmada';
    } else {
        // Manter status atual se não for aprovação
        $novo_status = $inscricao['status'];
    }
    
    // Gerar numero_inscricao se status for confirmada e numero_inscricao estiver vazio
    $numero_inscricao = null;
    if ($novo_status === 'confirmada' && empty($inscricao['numero_inscricao'])) {
        // Formato: MOV + YYYYMMDD + - + ID com 4 dígitos
        $ano = date('Y');
        $mes = str_pad(date('m'), 2, '0', STR_PAD_LEFT);
        $dia = str_pad(date('d'), 2, '0', STR_PAD_LEFT);
        $id_formatado = str_pad($inscricao_id, 4, '0', STR_PAD_LEFT);
        $numero_inscricao = "MOV{$ano}{$mes}{$dia}-{$id_formatado}";
        error_log("[UPDATE_PAYMENT_STATUS] Gerando numero_inscricao: $numero_inscricao para inscrição ID: $inscricao_id");
    }
    
    // Atualizar inscrição
    if ($numero_inscricao) {
        $stmt_update = $pdo->prepare("
            UPDATE inscricoes SET 
                status = ?,
                status_pagamento = ?,
                numero_inscricao = ?,
                data_pagamento = CASE WHEN ? = 'pago' THEN COALESCE(data_pagamento, NOW()) ELSE data_pagamento END,
                forma_pagamento = COALESCE(forma_pagamento, 'mercadolivre')
            WHERE id = ?
        ");
        
        $stmt_update->execute([
            $novo_status,
            $novo_status_pagamento,
            $numero_inscricao,
            $novo_status_pagamento,
            $inscricao_id
        ]);
    } else {
        $stmt_update = $pdo->prepare("
            UPDATE inscricoes SET 
                status = ?,
                status_pagamento = ?,
                data_pagamento = CASE WHEN ? = 'pago' THEN COALESCE(data_pagamento, NOW()) ELSE data_pagamento END,
                forma_pagamento = COALESCE(forma_pagamento, 'mercadolivre')
            WHERE id = ?
        ");
        
        $stmt_update->execute([
            $novo_status,
            $novo_status_pagamento,
            $novo_status_pagamento,
            $inscricao_id
        ]);
    }

    error_log("[UPDATE_PAYMENT_STATUS] Inscrição ID $inscricao_id atualizada: status='$novo_status', status_pagamento='$novo_status_pagamento'");

    echo json_encode([
        'success' => true,
        'message' => 'Status atualizado com sucesso.',
        'inscricao' => [
            'id' => $inscricao_id,
            'status' => $novo_status,
            'status_pagamento' => $novo_status_pagamento
        ]
    ]);

} catch (Exception $e) {
    error_log("[UPDATE_PAYMENT_STATUS] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status.']);
}

