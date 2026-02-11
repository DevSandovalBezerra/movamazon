<?php
session_start();
require_once '../../../api/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Debug
error_log("[FICHA] salvar_ficha.php - Requisição recebida - tamanho_camiseta=" . (isset($input['tamanho_camiseta']) ? $input['tamanho_camiseta'] : 'null') . " respostas_count=" . (isset($input['respostas_questionario']) ? count($input['respostas_questionario']) : 0));

// Validar dados recebidos
if (!isset($input['tamanho_camiseta']) || !isset($input['respostas_questionario'])) {
    error_log("ERRO salvar_ficha.php - Dados obrigatórios não fornecidos");
    echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não fornecidos']);
    exit;
}

// Atualizar dados da ficha na sessão
if (!isset($_SESSION['inscricao'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão de inscrição não encontrada']);
    exit;
}

// Salvar dados da ficha
$_SESSION['inscricao']['ficha'] = [
    'tamanho_camiseta' => $input['tamanho_camiseta'],
    'respostas_questionario' => $input['respostas_questionario'],
    'produtos_extras' => $input['produtos_extras'] ?? [],
    'cupom_aplicado' => $input['cupom_aplicado'] ?? null,
    'valor_desconto' => floatval($input['valor_desconto'] ?? 0)
];

// ✅ OTIMIZADO: Salvar inscrição DIRETAMENTE no banco (sem cURL)
try {
    $startTime = microtime(true);
    
    if (!isset($_SESSION['user_id'])) {
        error_log("❌ Usuário não autenticado ao tentar salvar inscrição");
        throw new Exception('Usuário não autenticado');
    }
    
    $usuarioId = (int) $_SESSION['user_id'];
    $inscricaoData = $_SESSION['inscricao'];
    $modalidade = $inscricaoData['modalidades_selecionadas'][0] ?? [];

    // Calcular valores
    $valorModalidades = 0;
    foreach ($inscricaoData['modalidades_selecionadas'] ?? [] as $mod) {
        $valorModalidades += floatval($mod['preco_total'] ?? 0);
    }

    $valorExtras = 0;
    foreach ($_SESSION['inscricao']['ficha']['produtos_extras'] ?? [] as $produto) {
        $valorExtras += floatval($produto['valor'] ?? 0);
    }
    
    $eventoId = (int) $inscricaoData['evento_id'];
    $modalidadeId = (int) ($modalidade['id'] ?? 0);
    $tamanhoCamiseta = $input['tamanho_camiseta'];
    $valorDesconto = floatval($input['valor_desconto'] ?? 0);
    $cupom = $input['cupom_aplicado'] ?? null;
    $produtosExtras = $input['produtos_extras'] ?? [];
    $respostasQuestionario = $input['respostas_questionario'] ?? [];
    $valorSeguro = 0;
    $valorTotal = max(0, $valorModalidades + $valorExtras + $valorSeguro - $valorDesconto);
    
    error_log("[SALVAR_FICHA] Salvando inscrição: usuarioId=$usuarioId, eventoId=$eventoId, modalidadeId=$modalidadeId, valorTotal=$valorTotal");
    
    $pdo->beginTransaction();
    
    // Verificar se já existe inscrição para este usuário/evento
    $checkStmt = $pdo->prepare("SELECT id, external_reference FROM inscricoes WHERE usuario_id = ? AND evento_id = ?");
    $checkStmt->execute([$usuarioId, $eventoId]);
    $inscricaoExistente = $checkStmt->fetch();
    
    if ($inscricaoExistente) {
        // Atualizar inscrição existente
        $inscricaoId = (int) $inscricaoExistente['id'];
        $externalRef = $inscricaoExistente['external_reference'] ?: 'MOVAMAZON_' . $inscricaoId;
        
        $updateStmt = $pdo->prepare("UPDATE inscricoes SET 
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
            WHERE id = ?");
        $updateStmt->execute([
            $modalidadeId,
            $tamanhoCamiseta,
            $valorTotal,
            $valorDesconto,
            $cupom,
            json_encode($produtosExtras, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            0,
            $externalRef,
            $inscricaoId
        ]);
        
        error_log("[SALVAR_FICHA] Inscrição atualizada: ID=$inscricaoId");
    } else {
        // Criar nova inscrição
        $externalRef = 'MOVAMAZON_' . time() . '_' . $usuarioId;
        
        $insertStmt = $pdo->prepare("INSERT INTO inscricoes (
            usuario_id, evento_id, modalidade_evento_id, tamanho_camiseta,
            status, status_pagamento, valor_total, valor_desconto, cupom_aplicado,
            produtos_extras_ids, data_inscricao, seguro_contratado, external_reference
        ) VALUES (?, ?, ?, ?, 'pendente', 'pendente', ?, ?, ?, ?, NOW(), 0, ?)");
        $insertStmt->execute([
            $usuarioId,
            $eventoId,
            $modalidadeId,
            $tamanhoCamiseta,
            $valorTotal,
            $valorDesconto,
            $cupom,
            json_encode($produtosExtras, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $externalRef
        ]);
        $inscricaoId = (int) $pdo->lastInsertId();
        
        error_log("[SALVAR_FICHA] Nova inscrição criada: ID=$inscricaoId");
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
        
        error_log("[SALVAR_FICHA] Produtos extras salvos: " . count($produtosExtras) . " items");
    }
    
    $pdo->commit();
    
    // Salvar ID na sessão
    $_SESSION['inscricao']['id'] = $inscricaoId;
    $_SESSION['inscricao']['external_reference'] = $externalRef;
    
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000, 2);
    
    error_log("✅ [SALVAR_FICHA] Inscrição salva com sucesso: ID=$inscricaoId, Tempo=${duration}ms");
    
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("❌ [SALVAR_FICHA] Erro ao salvar inscrição: " . $e->getMessage());
    error_log("❌ [SALVAR_FICHA] Stack trace: " . $e->getTraceAsString());
}

// Avançar para próxima etapa
$_SESSION['inscricao']['etapa_atual'] = 4;

// Debug
error_log("DEBUG salvar_ficha.php - Sessão atualizada: " . print_r($_SESSION['inscricao'], true));

echo json_encode([
    'success' => true,
    'message' => 'Ficha salva com sucesso',
    'etapa_atual' => 4,
    'inscricao_id' => $_SESSION['inscricao']['id'] ?? null,
    'debug' => [
        'modalidades_count' => count($_SESSION['inscricao']['modalidades_selecionadas'] ?? []),
        'ficha_saved' => isset($_SESSION['inscricao']['ficha']),
        'inscricao_saved' => isset($_SESSION['inscricao']['id'])
    ]
]);
