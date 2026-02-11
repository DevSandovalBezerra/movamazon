<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/inscricao_logger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    
    error_log("DEBUG save_inscricao.php - Input recebido: " . print_r($input, true));

    $usuarioId = (int) $_SESSION['user_id'];
    $eventoId = (int) ($input['evento_id'] ?? 0);
    $modalidadeId = (int) ($input['modalidade_id'] ?? 0);
    $tamanhoCamiseta = trim((string) ($input['tamanho_camiseta'] ?? ''));
    $valorModalidades = (float) ($input['valor_modalidades'] ?? 0);
    $valorExtras = (float) ($input['valor_extras'] ?? 0);
    $valorDesconto = (float) ($input['valor_desconto'] ?? 0);
    $cupom = isset($input['cupom']) ? (string) $input['cupom'] : null;
    $produtosExtras = $input['produtos_extras'] ?? [];
    $seguro = !empty($input['seguro']) ? 1 : 0;
    $respostasQuestionario = $input['respostas_questionario'] ?? [];

    error_log("DEBUG save_inscricao.php - Dados processados: eventoId=$eventoId, modalidadeId=$modalidadeId, tamanhoCamiseta=$tamanhoCamiseta");
    
    // Log início do salvamento
    logInscricaoPagamento('INFO', 'INICIO_SALVAMENTO_INSCRICAO', [
        'usuario_id' => $usuarioId,
        'evento_id' => $eventoId,
        'modalidade_id' => $modalidadeId,
        'valor_modalidades' => $valorModalidades,
        'valor_extras' => $valorExtras,
        'valor_desconto' => $valorDesconto,
        'cupom_aplicado' => $cupom,
        'seguro_contratado' => $seguro
    ]);

    if ($eventoId <= 0 || $modalidadeId <= 0 || $tamanhoCamiseta === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados obrigatórios ausentes']);
        exit;
    }

    // Calcular valor total
    $valorSeguro = $seguro ? 25.0 : 0.0;
    $valorTotal = max(0, $valorModalidades + $valorExtras + $valorSeguro - $valorDesconto);

    $pdo->beginTransaction();

    // Verificar se já existe inscrição para este usuário/evento
    $checkSql = "SELECT id, external_reference FROM inscricoes WHERE usuario_id = ? AND evento_id = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$usuarioId, $eventoId]);
    $inscricaoExistente = $checkStmt->fetch();

    if ($inscricaoExistente) {
        // Usar inscrição existente
        $inscricaoId = (int) $inscricaoExistente['id'];
        $externalRef = $inscricaoExistente['external_reference'] ?: 'MOVAMAZON_' . $inscricaoId;

        // Atualizar dados da inscrição existente (reabertura: limpa expiração/forma para evitar re-cancelamento)
        $updateSql = "UPDATE inscricoes SET 
            modalidade_evento_id = ?, 
            tamanho_camiseta = ?, 
            valor_total = ?, 
            valor_desconto = ?, 
            cupom_aplicado = ?, 
            produtos_extras_ids = ?, 
            seguro_contratado = ?,
            external_reference = ?,
            status = 'pendente',
            status_pagamento = 'pendente',
            data_inscricao = NOW(),
            data_expiracao_pagamento = NULL,
            forma_pagamento = NULL
            WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            $modalidadeId,
            $tamanhoCamiseta,
            $valorTotal,
            $valorDesconto,
            $cupom,
            json_encode($produtosExtras, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $seguro,
            $externalRef,
            $inscricaoId
        ]);

        error_log("DEBUG save_inscricao.php - Inscrição existente atualizada: ID=$inscricaoId");
        
        // Log warning: inscrição existente sendo atualizada
        logInscricaoPagamento('WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', [
            'inscricao_id' => $inscricaoId,
            'usuario_id' => $usuarioId,
            'evento_id' => $eventoId,
            'modalidade_id' => $modalidadeId,
            'valor_total' => $valorTotal,
            'valor_desconto' => $valorDesconto,
            'cupom_aplicado' => $cupom
        ]);
    } else {
        // Criar nova inscrição
        $sql = "INSERT INTO inscricoes (
            usuario_id, evento_id, modalidade_evento_id, tamanho_camiseta,
            status, status_pagamento, valor_total, valor_desconto, cupom_aplicado,
            produtos_extras_ids, data_inscricao, seguro_contratado, external_reference
        ) VALUES (
            ?, ?, ?, ?,
            'pendente', 'pendente', ?, ?, ?,
            ?, NOW(), ?, ?
        )";
        $stmt = $pdo->prepare($sql);

        // Gerar external_reference único
        $externalRef = 'MOVAMAZON_' . time() . '_' . $usuarioId;

        $stmt->execute([
            $usuarioId,
            $eventoId,
            $modalidadeId,
            $tamanhoCamiseta,
            $valorTotal,
            $valorDesconto,
            $cupom,
            json_encode($produtosExtras, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $seguro,
            $externalRef
        ]);
        $inscricaoId = (int) $pdo->lastInsertId();

        error_log("DEBUG save_inscricao.php - Nova inscrição criada: ID=$inscricaoId");
        
        // Log success: nova inscrição criada
        logInscricaoPagamento('SUCCESS', 'CRIACAO_INSCRICAO', [
            'inscricao_id' => $inscricaoId,
            'usuario_id' => $usuarioId,
            'evento_id' => $eventoId,
            'modalidade_id' => $modalidadeId,
            'valor_total' => $valorTotal,
            'valor_desconto' => $valorDesconto,
            'cupom_aplicado' => $cupom,
            'seguro_contratado' => $seguro,
            'external_reference' => $externalRef
        ]);
    }

    // Salvar produtos extras na tabela específica
    if (!empty($produtosExtras)) {
        // Limpar produtos extras existentes
        $pdo->prepare("DELETE FROM inscricoes_produtos_extras WHERE inscricao_id = ?")
            ->execute([$inscricaoId]);

        // Inserir novos produtos extras
        foreach ($produtosExtras as $produto) {
            $stmtProduto = $pdo->prepare("
                INSERT INTO inscricoes_produtos_extras 
                (inscricao_id, produto_extra_evento_id, quantidade, status, data_compra) 
                VALUES (?, ?, ?, 'pendente', NOW())
            ");
            $stmtProduto->execute([
                $inscricaoId,
                $produto['id'] ?? 0,
                $produto['quantidade'] ?? 1
            ]);
        }
    }

    // Salvar respostas do questionário se existirem
    if (!empty($respostasQuestionario)) {
        // Aqui você pode implementar a lógica para salvar as respostas do questionário
        // Por exemplo, em uma tabela específica ou como JSON
        error_log("DEBUG save_inscricao.php - Respostas do questionário: " . json_encode($respostasQuestionario));
    }

    $pdo->commit();
    
    // Log success final
    logInscricaoPagamento('SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', [
        'inscricao_id' => $inscricaoId,
        'usuario_id' => $usuarioId,
        'evento_id' => $eventoId,
        'valor_total' => $valorTotal,
        'external_reference' => $externalRef
    ]);

    echo json_encode([
        'success' => true,
        'inscricao_id' => $inscricaoId,
        'external_reference' => $externalRef,
        'valor_total' => $valorTotal,
        'message' => 'Inscrição salva com sucesso'
    ]);
    exit;
} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('save_inscricao erro: ' . $e->getMessage());
    
    // Log error com stack trace
    logInscricaoPagamento('ERROR', 'ERRO_SALVAMENTO_INSCRICAO', [
        'usuario_id' => $usuarioId ?? null,
        'evento_id' => $eventoId ?? null,
        'erro' => $e->getMessage(),
        'stack_trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao salvar inscrição: ' . $e->getMessage()]);
    exit;
}
