<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

//error_log('🔄 API update-order.php - Iniciando requisição de atualização de ordem');

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    //error_log('❌ API update-order.php - Usuário não autorizado: ' . ($_SESSION['user_id'] ?? 'não definido') . ' - Papel: ' . ($_SESSION['papel'] ?? 'não definido'));
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $evento_id = isset($data['evento_id']) ? (int)$data['evento_id'] : 0;
    $modalidades = isset($data['modalidades']) ? $data['modalidades'] : [];

    error_log('📋 API update-order.php - Dados recebidos: Evento ID: ' . $evento_id . ' - Modalidades: ' . count($modalidades));

    if (!$evento_id || empty($modalidades)) {
        error_log('❌ API update-order.php - Dados inválidos: Evento ID: ' . $evento_id . ' - Modalidades: ' . count($modalidades));
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        exit();
    }

    // Verificar se o evento pertence ao organizador
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    $evento = $stmt->fetch();
    
    if (!$evento) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou não autorizado']);
        exit();
    }

    // Verificar se todas as modalidades pertencem ao evento
    $modalidade_ids = array_column($modalidades, 'id');
    $placeholders = str_repeat('?,', count($modalidade_ids) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT id FROM modalidades WHERE id IN ($placeholders) AND evento_id = ?");
    $params = array_merge($modalidade_ids, [$evento_id]);
    $stmt->execute($params);
    $modalidades_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($modalidades_existentes) !== count($modalidade_ids)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Uma ou mais modalidades não pertencem ao evento']);
        exit();
    }

    // Iniciar transação
    $pdo->beginTransaction();

    // Atualizar ordem das modalidades
    foreach ($modalidades as $modalidade) {
        $stmt = $pdo->prepare("UPDATE modalidades SET ordem = ? WHERE id = ? AND evento_id = ?");
        $stmt->execute([$modalidade['ordem'], $modalidade['id'], $evento_id]);
    }

    // Commit da transação
    $pdo->commit();
    
    //error_log('✅ API update-order.php - Ordem das modalidades atualizada com sucesso');

    echo json_encode([
        'success' => true,
        'message' => 'Ordem das modalidades atualizada com sucesso'
    ]);

} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
        //error_log('🔄 API update-order.php - Rollback realizado devido a erro');
    }
    
    error_log('💥 API update-order.php - Erro ao atualizar ordem das modalidades: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
