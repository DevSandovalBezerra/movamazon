<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once '../../helpers/organizador_context.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

try {
    // Aceitar tanto JSON quanto FormData
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $input = [];
    
    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
    } else {
        // FormData
        $input = $_POST;
        // Decodificar JSON strings do FormData
        if (isset($input['modalidades']) && is_string($input['modalidades'])) {
            $input['modalidades'] = json_decode($input['modalidades'], true) ?: [];
        }
    }
    
    if (!$input) {
        throw new Exception('Dados inválidos');
    }
    
    // Extrair dados básicos do kit
    $kit_id = isset($input['id']) ? (int)$input['id'] : 0;
    $nome = isset($input['nome']) ? trim($input['nome']) : '';
    $descricao = isset($input['descricao']) ? trim($input['descricao']) : '';
    $valor = isset($input['valor']) ? (float)$input['valor'] : 0;
    $ativo = isset($input['ativo']) ? (int)($input['ativo'] ?? 1) : 1;
    $modalidades = isset($input['modalidades']) && is_array($input['modalidades']) ? $input['modalidades'] : [];
    
    // Validações
    if ($kit_id <= 0) {
        throw new Exception('ID do kit é obrigatório');
    }
    if (empty($nome)) {
        throw new Exception('Nome do kit é obrigatório');
    }
    if ($valor <= 0) {
        throw new Exception('Valor deve ser maior que zero');
    }
    
    // Usar contexto do organizador
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    // Verificar se o kit existe e pertence ao organizador
    $stmt = $pdo->prepare("
        SELECT k.id, k.nome, k.evento_id, e.nome as evento_nome
        FROM kits_eventos k
        INNER JOIN eventos e ON k.evento_id = e.id
        WHERE k.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?)
    ");
    $stmt->execute([$kit_id, $organizador_id, $usuario_id]);
    $kit = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$kit) {
        throw new Exception('Kit não encontrado ou não pertence a você');
    }
    // Verificar se já existe outro kit com o mesmo nome para o mesmo evento
    $stmt = $pdo->prepare("
        SELECT id FROM kits_eventos 
        WHERE evento_id = ? AND nome = ? AND id != ?
    ");
    $stmt->execute([$kit['evento_id'], $nome, $kit_id]);
    if ($stmt->fetch()) {
        throw new Exception('Já existe outro kit com este nome para este evento');
    }
    // Iniciar transação
    $pdo->beginTransaction();
    // Atualizar dados básicos do kit
    // Se houver apenas uma modalidade, atualizar também o campo legado modalidade_evento_id
    $modalidade_principal = !empty($modalidades) ? (int)$modalidades[0] : null;
    
    $stmt = $pdo->prepare("
        UPDATE kits_eventos 
        SET nome = ?, descricao = ?, valor = ?, ativo = ?, modalidade_evento_id = ?
        WHERE id = ?
    ");
    $stmt->execute([$nome, $descricao, $valor, $ativo, $modalidade_principal, $kit_id]);
    
    // Atualizar modalidades associadas na tabela N:N
    $pdo->prepare("DELETE FROM kit_modalidade_evento WHERE kit_id = ?")->execute([$kit_id]);
    if (!empty($modalidades)) {
        $stmt = $pdo->prepare("INSERT INTO kit_modalidade_evento (kit_id, modalidade_evento_id) VALUES (?, ?)");
        foreach ($modalidades as $mod_id) {
            $stmt->execute([$kit_id, $mod_id]);
        }
    }
    $pdo->commit();
    error_log("Kit atualizado - ID: $kit_id, Nome: $nome, Evento: {$kit['evento_nome']}, Organizador: $organizador_id");
    echo json_encode([
        'success' => true,
        'message' => 'Kit atualizado com sucesso',
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
    error_log('Erro ao atualizar kit: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 
