<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    $evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
    error_log('🔍 API modalidades/list.php - Evento ID recebido: ' . $evento_id);
    error_log('🔍 API modalidades/list.php - User ID: ' . ($_SESSION['user_id'] ?? 'NÃO DEFINIDO'));

    if (!$evento_id) {
        error_log('❌ API modalidades/list.php - Evento ID não fornecido');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
        exit();
    }

    // Validar se o evento pertence ao organizador (compatível com legado: organizador_id pode estar como usuarios.id)
    $stmtEvento = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmtEvento->execute([$evento_id, $organizador_id, $usuario_id]);
    if (!$stmtEvento->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou acesso negado']);
        exit();
    }

    $sql = "
        SELECT 
            m.id,
            m.categoria_id,
            m.nome,
            m.nome AS nome_modalidade,
            m.descricao,
            m.distancia,
            m.tipo_prova,
            m.limite_vagas,
            m.ativo,
            m.data_criacao,
            c.nome as categoria_nome,
            c.tipo_publico,
            c.idade_min,
            c.idade_max
        FROM modalidades m
        INNER JOIN categorias c ON m.categoria_id = c.id
        WHERE m.evento_id = ? AND m.ativo = 1
        ORDER BY c.nome, m.nome
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$evento_id]);
    $modalidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log('✅ API modalidades/list.php - Modalidades encontradas: ' . count($modalidades));

    echo json_encode([
        'success' => true,
        'modalidades' => $modalidades,
        'total' => count($modalidades)
    ]);
} catch (PDOException $e) {
    error_log("Erro ao listar modalidades: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
