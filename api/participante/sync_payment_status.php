<?php
/**
 * Endpoint para sincronizar status de pagamento
 * 1. Verifica a tabela pagamentos_ml
 * 2. Se não houver registro (ex.: PIX), consulta a API do Mercado Pago usando external_reference como payment_id
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

$usuario_id = $_SESSION['user_id'];
$inscricao_id = $_GET['inscricao_id'] ?? null;

if (!$inscricao_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da inscrição é obrigatório.']);
    exit();
}

try {
    // ✅ IDEMPOTÊNCIA: Buscar inscrição com TODOS os dados necessários para verificação completa
    $stmt = $pdo->prepare("
        SELECT 
            i.id, 
            i.status, 
            i.status_pagamento, 
            i.external_reference, 
            i.numero_inscricao,
            i.valor_total,
            i.data_pagamento,
            i.forma_pagamento
        FROM inscricoes i
        WHERE i.id = ? AND i.usuario_id = ?
    ");
    $stmt->execute([$inscricao_id, $usuario_id]);
    $inscricao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscricao) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Inscrição não encontrada ou não pertence ao usuário.']);
        exit();
    }

    // ✅ IDEMPOTÊNCIA: Verificar se existe registro em pagamentos_ml separadamente
    $tem_registro_ml = 0;
    $pm_payment_id = null;
    $pm_status = null;
    
    try {
        $stmt_ml_check = $pdo->prepare("
            SELECT payment_id, status 
            FROM pagamentos_ml 
            WHERE inscricao_id = ? 
            ORDER BY data_atualizacao DESC 
            LIMIT 1
        ");
        $stmt_ml_check->execute([$inscricao_id]);
        $ml_data = $stmt_ml_check->fetch(PDO::FETCH_ASSOC);
        
        if ($ml_data) {
            $tem_registro_ml = 1;
            $pm_payment_id = $ml_data['payment_id'] ?? null;
            $pm_status = $ml_data['status'] ?? null;
        }
    } catch (Exception $e) {
        // Se tabela não existe ou erro, considerar como sem registro
        error_log("[SYNC_PAYMENT_STATUS] ⚠️ Erro ao verificar pagamentos_ml: " . $e->getMessage());
        $tem_registro_ml = 0;
    }

    // ✅ IDEMPOTÊNCIA: Verificar se JÁ ESTÁ TUDO COMPLETO - se sim, retornar sem fazer nada
    $ja_completo = (
        $inscricao['status'] === 'confirmada' &&
        $inscricao['status_pagamento'] === 'pago' &&
        !empty($inscricao['numero_inscricao']) &&
        $tem_registro_ml > 0 &&
        !empty($pm_payment_id) &&
        $pm_status === 'pago'
    );

    if ($ja_completo) {
        error_log("[SYNC_PAYMENT_STATUS] ✅ Inscrição ID $inscricao_id já está completa - nenhuma ação necessária (idempotência)");
        echo json_encode([
            'success' => true,
            'message' => 'Inscrição já está completa. Nenhuma ação necessária.',
            'atualizado' => false,
            'ja_completo' => true,
            'inscricao' => [
                'id' => $inscricao_id,
                'status' => $inscricao['status'],
                'status_pagamento' => $inscricao['status_pagamento'],
                'numero_inscricao' => $inscricao['numero_inscricao'],
                'tem_registro_ml' => (bool)$tem_registro_ml
            ]
        ]);
        exit();
    }

    // Buscar pagamento mais recente na tabela pagamentos_ml
    $stmt_payment = $pdo->prepare("
        SELECT status, payment_id, data_atualizacao, valor_pago
        FROM pagamentos_ml 
        WHERE inscricao_id = ? 
        ORDER BY data_atualizacao DESC 
        LIMIT 1
    ");
    $stmt_payment->execute([$inscricao_id]);
    $pagamento = $stmt_payment->fetch(PDO::FETCH_ASSOC);

    // PIX: quando não há registro em pagamentos_ml, external_reference pode ser o payment_id (create_pix grava assim)
    $payment_data_from_api = null; // Armazenar dados completos da API para criar registro em pagamentos_ml
    if (!$pagamento && !empty($inscricao['external_reference']) && preg_match('/^\d+$/', (string)$inscricao['external_reference'])) {
        $config = require __DIR__ . '/../mercadolivre/config.php';
        $access_token = $config['accesstoken'] ?? null;
        if ($access_token) {
            $payment_id = $inscricao['external_reference'];
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://api.mercadopago.com/v1/payments/{$payment_id}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $access_token,
                    'Content-Type: application/json'
                ],
            ]);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($http_code === 200 && $response) {
                $data = json_decode($response, true);
                $payment_data_from_api = $data; // Guardar dados completos
                $status_mp = $data['status'] ?? null;
                $map = ['approved' => 'pago', 'pending' => 'pendente', 'in_process' => 'processando', 'rejected' => 'rejeitado', 'cancelled' => 'cancelado', 'refunded' => 'cancelado'];
                $status_pagamento_ml = $map[$status_mp] ?? $inscricao['status_pagamento'];
                $pagamento = [
                    'status' => $status_pagamento_ml,
                    'payment_id' => $payment_id,
                    'data_atualizacao' => date('Y-m-d H:i:s'),
                    'valor_pago' => $data['transaction_amount'] ?? $inscricao['valor_total'] ?? 0
                ];
                error_log("[SYNC_PAYMENT_STATUS] PIX: consultou MP payment_id=$payment_id, status=$status_pagamento_ml");
            }
        }
    }

    // ✅ IDEMPOTÊNCIA: Se não encontrou pagamento nem na tabela nem na API, retornar
    if (!$pagamento) {
        echo json_encode([
            'success' => false,
            'message' => 'Nenhum pagamento encontrado para esta inscrição.',
            'inscricao' => [
                'id' => $inscricao_id,
                'status' => $inscricao['status'],
                'status_pagamento' => $inscricao['status_pagamento']
            ]
        ]);
        exit();
    }

    $status_pagamento_ml = $pagamento['status'];
    
    // ✅ IDEMPOTÊNCIA: Se já tem registro em pagamentos_ml do banco, usar dados dele
    // e verificar se precisa atualizar a inscrição
    if (!$payment_data_from_api && $tem_registro_ml > 0) {
        // Pagamento já existe no banco - só verificar se inscrição precisa ser atualizada
        error_log("[SYNC_PAYMENT_STATUS] ✅ Pagamento já existe em pagamentos_ml para inscrição ID $inscricao_id - usando dados do banco");
    }
    
    // Mapear status da tabela pagamentos_ml para status da inscrição
    // Se status é 'pago', marcar inscrição como confirmada
    $novo_status = ($status_pagamento_ml === 'pago') ? 'confirmada' : $inscricao['status'];
    
    // Se já está confirmada, manter confirmada (não reverter)
    if ($inscricao['status'] === 'confirmada' && $status_pagamento_ml !== 'pago') {
        $novo_status = 'confirmada';
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
        error_log("[SYNC_PAYMENT_STATUS] Gerando numero_inscricao: $numero_inscricao para inscrição ID: $inscricao_id");
    }
    
    // ✅ IDEMPOTÊNCIA: Criar/atualizar registro em pagamentos_ml APENAS se necessário
    $pagamento_ml_criado = false;
    $pagamento_ml_atualizado = false;
    
    // Só processar se encontrou pagamento via API E ainda não tem registro completo em pagamentos_ml
    if ($payment_data_from_api && $pagamento && $tem_registro_ml == 0) {
        // Verificar se tabela pagamentos_ml existe
        $hasPagamentosMl = false;
        try {
            $pdo->query("SELECT 1 FROM pagamentos_ml LIMIT 1");
            $hasPagamentosMl = true;
        } catch (Exception $e) {
            $hasPagamentosMl = false;
        }

        if ($hasPagamentosMl) {
            $payment_id = $pagamento['payment_id'];
            
            // ✅ IDEMPOTÊNCIA: Verificar novamente se já existe registro (por payment_id OU inscricao_id)
            $stmt_check_ml = $pdo->prepare("
                SELECT id, status, payment_id 
                FROM pagamentos_ml 
                WHERE payment_id = ? OR inscricao_id = ? 
                LIMIT 1
            ");
            $stmt_check_ml->execute([$payment_id, $inscricao_id]);
            $pagamento_ml_existente = $stmt_check_ml->fetch(PDO::FETCH_ASSOC);

            if ($pagamento_ml_existente) {
                // ✅ IDEMPOTÊNCIA: Só atualizar se status for diferente ou dados estiverem incompletos
                $status_atual_ml = $pagamento_ml_existente['status'];
                if ($status_atual_ml !== $status_pagamento_ml || empty($pagamento_ml_existente['payment_id'])) {
                    $valor_pago = $pagamento['valor_pago'] ?? $payment_data_from_api['transaction_amount'] ?? $inscricao['valor_total'] ?? 0;
                    $metodo_pagamento = $payment_data_from_api['payment_method_id'] ?? 'pix';
                    $parcelas = $payment_data_from_api['installments'] ?? 1;
                    $taxa_ml = null;
                    $dados_pagamento_json = json_encode($payment_data_from_api, JSON_UNESCAPED_UNICODE);
                    $preference_id = $payment_data_from_api['preference_id'] ?? 'sync_' . $payment_id;
                    $init_point = $payment_data_from_api['point_of_interaction']['transaction_data']['ticket_url'] ?? 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=' . $preference_id;

                    $stmt_update_ml = $pdo->prepare("
                        UPDATE pagamentos_ml SET 
                            payment_id = COALESCE(payment_id, ?),
                            status = ?,
                            valor_pago = COALESCE(valor_pago, ?),
                            metodo_pagamento = COALESCE(metodo_pagamento, ?),
                            parcelas = COALESCE(parcelas, ?),
                            dados_pagamento = COALESCE(dados_pagamento, ?),
                            data_atualizacao = NOW()
                        WHERE id = ?
                    ");
                    $stmt_update_ml->execute([
                        $payment_id,
                        $status_pagamento_ml,
                        $valor_pago,
                        $metodo_pagamento,
                        $parcelas,
                        $dados_pagamento_json,
                        $pagamento_ml_existente['id']
                    ]);
                    $pagamento_ml_atualizado = true;
                    error_log("[SYNC_PAYMENT_STATUS] ✅ Registro pagamentos_ml atualizado (ID: {$pagamento_ml_existente['id']}) via sync");
                } else {
                    error_log("[SYNC_PAYMENT_STATUS] ✅ Registro pagamentos_ml já está atualizado - nenhuma ação necessária (idempotência)");
                }
            } else {
                // ✅ IDEMPOTÊNCIA: Criar novo registro APENAS se não existe
                $valor_pago = $pagamento['valor_pago'] ?? $payment_data_from_api['transaction_amount'] ?? $inscricao['valor_total'] ?? 0;
                $metodo_pagamento = $payment_data_from_api['payment_method_id'] ?? 'pix';
                $parcelas = $payment_data_from_api['installments'] ?? 1;
                $taxa_ml = null;
                $dados_pagamento_json = json_encode($payment_data_from_api, JSON_UNESCAPED_UNICODE);
                $user_id = $usuario_id;
                $preference_id = $payment_data_from_api['preference_id'] ?? 'sync_' . $payment_id;
                $init_point = $payment_data_from_api['point_of_interaction']['transaction_data']['ticket_url'] ?? 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=' . $preference_id;

                // ✅ CORREÇÃO: 11 colunas que precisam de valores + 2 NOW() para data_criacao e data_atualizacao
                $stmt_insert_ml = $pdo->prepare("
                    INSERT INTO pagamentos_ml (
                        inscricao_id, payment_id, preference_id, init_point, status,
                        valor_pago, metodo_pagamento, parcelas, taxa_ml,
                        dados_pagamento, user_id, data_criacao, data_atualizacao
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                // ✅ 11 valores correspondendo aos 11 placeholders
                $stmt_insert_ml->execute([
                    $inscricao_id,        // 1
                    $payment_id,          // 2
                    $preference_id,       // 3
                    $init_point,          // 4
                    $status_pagamento_ml, // 5
                    $valor_pago,          // 6
                    $metodo_pagamento,    // 7
                    $parcelas,            // 8
                    $taxa_ml,             // 9
                    $dados_pagamento_json,// 10
                    $user_id              // 11
                ]);
                $pagamento_ml_criado = true;
                error_log("[SYNC_PAYMENT_STATUS] ⚠️ ATENÇÃO: Registro pagamentos_ml criado via sync (não webhook). Payment ID: $payment_id");
                error_log("[SYNC_PAYMENT_STATUS] ⚠️ Isso pode indicar que o webhook do Mercado Pago não foi recebido. Verifique a configuração do webhook.");
            }
        }
    } elseif ($tem_registro_ml > 0) {
        // ✅ IDEMPOTÊNCIA: Se já tem registro em pagamentos_ml, não precisa criar
        error_log("[SYNC_PAYMENT_STATUS] ✅ Registro pagamentos_ml já existe para inscrição ID $inscricao_id - nenhuma ação necessária (idempotência)");
    }

    // ✅ IDEMPOTÊNCIA: Atualizar inscrição APENAS se houver mudanças necessárias
    $atualizado = false;
    $precisa_atualizar = (
        $inscricao['status'] !== $novo_status || 
        $inscricao['status_pagamento'] !== $status_pagamento_ml || 
        ($numero_inscricao && empty($inscricao['numero_inscricao'])) ||
        ($status_pagamento_ml === 'pago' && empty($inscricao['data_pagamento']))
    );

    if ($precisa_atualizar) {
        if ($numero_inscricao && empty($inscricao['numero_inscricao'])) {
            // Atualizar com numero_inscricao
            $stmt_update = $pdo->prepare("
                UPDATE inscricoes SET 
                    status = ?,
                    status_pagamento = ?,
                    numero_inscricao = ?,
                    data_pagamento = CASE WHEN ? = 'pago' AND data_pagamento IS NULL THEN NOW() ELSE data_pagamento END,
                    forma_pagamento = COALESCE(forma_pagamento, 'mercadolivre')
                WHERE id = ?
            ");
            
            $stmt_update->execute([
                $novo_status,
                $status_pagamento_ml,
                $numero_inscricao,
                $status_pagamento_ml,
                $inscricao_id
            ]);
        } else {
            // Atualizar sem numero_inscricao
            $stmt_update = $pdo->prepare("
                UPDATE inscricoes SET 
                    status = ?,
                    status_pagamento = ?,
                    data_pagamento = CASE WHEN ? = 'pago' AND data_pagamento IS NULL THEN NOW() ELSE data_pagamento END,
                    forma_pagamento = COALESCE(forma_pagamento, 'mercadolivre')
                WHERE id = ?
            ");
            
            $stmt_update->execute([
                $novo_status,
                $status_pagamento_ml,
                $status_pagamento_ml,
                $inscricao_id
            ]);
        }
        
        $atualizado = true;
        $fonte = $pagamento_ml_criado ? 'sync (não webhook)' : ($pagamento_ml_atualizado ? 'sync (atualizado)' : 'sync');
        error_log("[SYNC_PAYMENT_STATUS] ✅ Inscrição ID $inscricao_id sincronizada via $fonte: status='$novo_status', status_pagamento='$status_pagamento_ml'");
    } else {
        error_log("[SYNC_PAYMENT_STATUS] ✅ Inscrição ID $inscricao_id já está atualizada - nenhuma ação necessária (idempotência)");
    }

    echo json_encode([
        'success' => true,
        'message' => $atualizado ? 'Status sincronizado e atualizado.' : 'Status já está atualizado.',
        'atualizado' => $atualizado,
        'inscricao' => [
            'id' => $inscricao_id,
            'status' => $novo_status,
            'status_pagamento' => $status_pagamento_ml,
            'status_anterior' => [
                'status' => $inscricao['status'],
                'status_pagamento' => $inscricao['status_pagamento']
            ]
        ],
        'pagamento' => [
            'status' => $status_pagamento_ml,
            'payment_id' => $pagamento['payment_id'],
            'data_atualizacao' => $pagamento['data_atualizacao']
        ]
    ]);

} catch (Exception $e) {
    error_log("[SYNC_PAYMENT_STATUS] ❌ ERRO: " . $e->getMessage());
    error_log("[SYNC_PAYMENT_STATUS] ❌ Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao sincronizar status: ' . $e->getMessage(),
        'error_details' => (ini_get('display_errors') ? $e->getTraceAsString() : 'Verifique os logs do servidor')
    ]);
}

