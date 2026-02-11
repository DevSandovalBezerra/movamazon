<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../auth/auth.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Verificar autenticação
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validação dos campos obrigatórios
    $id = $input['id'] ?? null;
    $evento_id = $input['evento_id'] ?? null;
    $tipo = $input['tipo'] ?? null;
    $texto = $input['texto'] ?? null;
    
    if (!$id || !$evento_id || !$tipo || !$texto) {
        echo json_encode(['success' => false, 'message' => 'Campos obrigatórios: id, evento_id, tipo, texto']);
        exit;
    }

    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Verificar se a pergunta existe e pertence ao organizador (novo + legado)
    $stmt = $pdo->prepare("
        SELECT qe.id 
        FROM questionario_evento qe 
        INNER JOIN eventos e ON qe.evento_id = e.id 
        WHERE qe.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?)
    ");
    $stmt->execute([$id, $organizador_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Pergunta não encontrada ou acesso negado']);
        exit;
    }

    // Campos opcionais
    $tipo_resposta = $input['tipo_resposta'] ?? null;
    $mascara = $input['mascara'] ?? null;
    $obrigatorio = isset($input['obrigatorio']) ? (int)$input['obrigatorio'] : 0;
    $ordem = isset($input['ordem']) ? (int)$input['ordem'] : 0;
    $ativo = isset($input['ativo']) ? (int)$input['ativo'] : 1;
    $status_site = $input['status_site'] ?? 'publicada';
    $status_grupo = $input['status_grupo'] ?? 'publicada';
    $modalidades = $input['modalidades'] ?? [];
    $classificacao = $input['classificacao'] ?? 'evento';
    
    // Validar classificacao
    if (!in_array($classificacao, ['evento', 'atleta'])) {
        $classificacao = 'evento';
    }

    $pdo->beginTransaction();

    // Atualizar pergunta/campo
    $stmt = $pdo->prepare("
        UPDATE questionario_evento 
        SET tipo = ?, tipo_resposta = ?, classificacao = ?, mascara = ?, texto = ?, obrigatorio = ?, 
            ordem = ?, ativo = ?, status_site = ?, status_grupo = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $tipo, 
        $tipo_resposta, 
        $classificacao,
        $mascara, 
        $texto, 
        $obrigatorio, 
        $ordem, 
        $ativo, 
        $status_site, 
        $status_grupo,
        $id
    ]);

    // Remover modalidades associadas existentes
    $stmt = $pdo->prepare("DELETE FROM questionario_evento_modalidade WHERE questionario_evento_id = ?");
    $stmt->execute([$id]);

    // Associar novas modalidades se informadas
    if (!empty($modalidades) && is_array($modalidades)) {
        $stmt_modalidade = $pdo->prepare("INSERT INTO questionario_evento_modalidade (questionario_evento_id, modalidade_id) VALUES (?, ?)");
        
        foreach ($modalidades as $modalidade_id) {
            if (is_numeric($modalidade_id)) {
                $stmt_modalidade->execute([$id, $modalidade_id]);
            }
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Pergunta/campo atualizado com sucesso'
    ]);

} catch (Exception $e) {
    $pdo->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
