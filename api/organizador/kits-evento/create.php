<?php
header('Content-Type: application/json');
require_once '../../db.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados inválidos');
    }
    
    // Extrair dados básicos do kit
    $evento_id = isset($input['evento_id']) ? (int)$input['evento_id'] : 0;
    $nome = isset($input['nome']) ? trim($input['nome']) : '';
    $descricao = isset($input['descricao']) ? trim($input['descricao']) : '';
    $valor = isset($input['valor']) ? (float)$input['valor'] : 0;
    $ativo = isset($input['ativo']) ? (int)$input['ativo'] : 1;
    $modalidades = isset($input['modalidades']) && is_array($input['modalidades']) ? $input['modalidades'] : [];
    
    // Extrair produtos e tamanhos
    $produtos = isset($input['produtos']) ? $input['produtos'] : [];
    $tamanhos = isset($input['tamanhos']) ? $input['tamanhos'] : [];
    
    // Validações
    if ($evento_id <= 0) {
        throw new Exception('ID do evento é obrigatório');
    }
    if (empty($nome)) {
        throw new Exception('Nome do kit é obrigatório');
    }
    if ($valor <= 0) {
        throw new Exception('Valor deve ser maior que zero');
    }
    if (empty($produtos)) {
        throw new Exception('Pelo menos um produto deve ser selecionado');
    }
    $organizador_id = $_SESSION['user_id'];
    // Verificar se o evento pertence ao organizador e não está excluído
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND organizador_id = ? AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Evento não encontrado, foi excluído ou não pertence a você');
    }
    // Validar modalidades (se houver)
    if (!empty($modalidades)) {
        $placeholders = implode(',', array_fill(0, count($modalidades), '?'));
        $params = $modalidades;
        $params[] = $evento_id;
        $sql = "SELECT COUNT(*) as total FROM modalidades WHERE id IN ($placeholders) AND evento_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        if ($row['total'] != count($modalidades)) {
            throw new Exception('Uma ou mais modalidades não pertencem ao evento');
        }
    }
    // Verificar se já existe um kit com o mesmo nome para este evento
    $stmt = $pdo->prepare("SELECT id FROM kits_eventos WHERE evento_id = ? AND nome = ?");
    $stmt->execute([$evento_id, $nome]);
    if ($stmt->fetch()) {
        throw new Exception('Já existe um kit com este nome para este evento');
    }
    // Iniciar transação
    $pdo->beginTransaction();
    // Inserir kit (modalidade_evento_id pode ser NULL ou 0)
    $stmt = $pdo->prepare("
        INSERT INTO kits_eventos (nome, descricao, evento_id, modalidade_evento_id, valor, ativo, data_criacao)
        VALUES (?, ?, ?, NULL, ?, ?, NOW())
    ");
    $stmt->execute([$nome, $descricao, $evento_id, $valor, $ativo]);
    $kit_id = $pdo->lastInsertId();
    // Inserir associações kit-modalidade
    if (!empty($modalidades)) {
        $stmt = $pdo->prepare("INSERT INTO kit_modalidade_evento (kit_id, modalidade_evento_id) VALUES (?, ?)");
        foreach ($modalidades as $mod_id) {
            $stmt->execute([$kit_id, $mod_id]);
        }
    }
    // Inserir produtos do kit (mantido igual)
    foreach ($produtos as $index => $produto) {
        $produto_id = (int)$produto['produto_id'];
        $quantidade = isset($produto['quantidade']) ? (int)$produto['quantidade'] : 1;
        $tamanho_id = isset($produto['tamanho_id']) ? (int)$produto['tamanho_id'] : null;
        $ordem = $index + 1;
        $stmt = $pdo->prepare("SELECT id FROM produtos WHERE id = ?");
        $stmt->execute([$produto_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Produto não encontrado');
        }
        $stmt = $pdo->prepare("
            INSERT INTO kit_produtos (kit_id, produto_id, tamanho_id, quantidade, ordem, ativo)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$kit_id, $produto_id, $tamanho_id, $quantidade, $ordem]);
    }
    // Inserir tamanhos disponíveis para o kit (se houver camisetas)
    if (!empty($tamanhos)) {
        foreach ($tamanhos as $tamanho) {
            $tamanho_id = (int)$tamanho['tamanho_id'];
            $quantidade_disponivel = (int)$tamanho['quantidade_disponivel'];
            $stmt = $pdo->prepare("
                SELECT id FROM tamanhos_camisetas_evento 
                WHERE id = ? AND evento_id = ?
            ");
            $stmt->execute([$tamanho_id, $evento_id]);
            if (!$stmt->fetch()) {
                throw new Exception('Tamanho não encontrado ou não pertence ao evento');
            }
            $stmt = $pdo->prepare("
                INSERT INTO kit_tamanhos (kit_id, tamanho_id, quantidade_disponivel, quantidade_vendida, ativo, data_criacao)
                VALUES (?, ?, ?, 0, 1, NOW())
            ");
            $stmt->execute([$kit_id, $tamanho_id, $quantidade_disponivel]);
        }
    }
    $pdo->commit();
    error_log("Kit criado - ID: $kit_id, Nome: $nome, Evento: $evento_id, Organizador: $organizador_id");
    echo json_encode([
        'success' => true,
        'message' => 'Kit criado com sucesso',
        'data' => [
            'id' => $kit_id,
            'nome' => $nome,
            'descricao' => $descricao,
            'valor' => $valor,
            'ativo' => $ativo
        ]
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro ao criar kit: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 
