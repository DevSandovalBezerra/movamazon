<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizadorId = $ctx['organizador_id'];

    // Receber dados do JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados inválidos');
    }

    // Validar dados
    $termoId = isset($input['id']) ? (int)$input['id'] : 0;
    $eventoId = isset($input['evento_id']) ? (int)$input['evento_id'] : 0;
    $modalidadeId = isset($input['modalidade_id']) && $input['modalidade_id'] !== '' && $input['modalidade_id'] !== null ? (int)$input['modalidade_id'] : null;
    $titulo = trim($input['titulo'] ?? '');
    $conteudo = trim($input['conteudo'] ?? '');
    $versao = trim($input['versao'] ?? '1.0');
    $ativo = isset($input['ativo']) && ($input['ativo'] === true || $input['ativo'] === '1' || $input['ativo'] === 1) ? 1 : 0;

    if ($termoId <= 0) {
        throw new Exception('ID do termo é obrigatório');
    }

    if ($eventoId <= 0) {
        throw new Exception('Evento é obrigatório');
    }

    if (empty($titulo)) {
        throw new Exception('Título é obrigatório');
    }

    if (empty($conteudo)) {
        throw new Exception('Conteúdo é obrigatório');
    }

    // Verificar se o termo existe e pertence ao organizador
    $stmt = $pdo->prepare("
        SELECT t.id 
        FROM termos_eventos t
        INNER JOIN eventos e ON t.evento_id = e.id
        WHERE t.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
    ");
    $stmt->execute([$termoId, $organizadorId, $usuario_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Termo não encontrado ou sem permissão');
    }

    // Verificar se o evento pertence ao organizador
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$eventoId, $organizadorId, $usuario_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Evento não encontrado ou sem permissão');
    }

    // Verificar se a modalidade pertence ao evento (se especificada)
    if ($modalidadeId) {
        $stmt = $pdo->prepare("SELECT id FROM modalidades WHERE id = ? AND evento_id = ?");
        $stmt->execute([$modalidadeId, $eventoId]);
        if (!$stmt->fetch()) {
            throw new Exception('Modalidade não encontrada para este evento');
        }
    }

    // Atualizar termo
    $sql = "
        UPDATE termos_eventos 
        SET evento_id = ?, modalidade_id = ?, titulo = ?, conteudo = ?, versao = ?, ativo = ?
        WHERE id = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$eventoId, $modalidadeId, $titulo, $conteudo, $versao, $ativo, $termoId]);

    echo json_encode([
        'success' => true,
        'message' => 'Termo atualizado com sucesso'
    ]);
} catch (Exception $e) {
    error_log('Erro ao atualizar termo: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
