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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    // Validação dos campos obrigatórios
    $evento_id = $input['evento_id'] ?? null;
    $tipo = $input['tipo'] ?? null;
    $texto = $input['texto'] ?? null;
    
    if (!$evento_id || !$tipo || !$texto) {
        echo json_encode(['success' => false, 'message' => 'Campos obrigatórios: evento_id, tipo, texto']);
        exit;
    }

    // Verificar se o evento pertence ao organizador logado e não está excluído
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado, foi excluído ou acesso negado']);
        exit;
    }

    // Campos opcionais com valores padrão
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

    // Se ordem não informada, buscar próxima ordem disponível
    if ($ordem == 0) {
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(ordem), 0) + 1 as proxima_ordem FROM questionario_evento WHERE evento_id = ?");
        $stmt->execute([$evento_id]);
        $ordem = $stmt->fetchColumn();
    }

    $pdo->beginTransaction();

    // Inserir pergunta/campo
    $stmt = $pdo->prepare("
        INSERT INTO questionario_evento 
        (evento_id, modalidade_id, tipo, tipo_resposta, classificacao, mascara, texto, obrigatorio, ordem, ativo, status_site, status_grupo) 
        VALUES (?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $evento_id, 
        $tipo, 
        $tipo_resposta, 
        $classificacao,
        $mascara, 
        $texto, 
        $obrigatorio, 
        $ordem, 
        $ativo, 
        $status_site, 
        $status_grupo
    ]);

    $pergunta_id = $pdo->lastInsertId();

    // Associar modalidades se informadas
    if (!empty($modalidades) && is_array($modalidades)) {
        $stmt_modalidade = $pdo->prepare("INSERT INTO questionario_evento_modalidade (questionario_evento_id, modalidade_id) VALUES (?, ?)");
        
        foreach ($modalidades as $modalidade_id) {
            if (is_numeric($modalidade_id)) {
                $stmt_modalidade->execute([$pergunta_id, $modalidade_id]);
            }
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Pergunta/campo criado com sucesso',
        'data' => ['id' => $pergunta_id]
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    error_log('Erro ao salvar pergunta: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar pergunta']);
}
?> 
