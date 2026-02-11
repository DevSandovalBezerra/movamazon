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

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizadorId = $ctx['organizador_id'];
    $pagina = max(1, (int)($_GET['pagina'] ?? 1));
    $limite = 10;
    $offset = ($pagina - 1) * $limite;

    // Filtros
    $filtros = [];
    $params = [$organizadorId, $usuario_id];

    if (!empty($_GET['evento_id'])) {
        $filtros[] = "t.evento_id = ?";
        $params[] = $_GET['evento_id'];
    }

    if (!empty($_GET['modalidade_id'])) {
        $filtros[] = "t.modalidade_id = ?";
        $params[] = $_GET['modalidade_id'];
    }

    if (!empty($_GET['status'])) {
        $filtros[] = "t.ativo = ?";
        $params[] = $_GET['status'];
    }

    $whereClause = !empty($filtros) ? 'AND ' . implode(' AND ', $filtros) : '';

    // Query principal
    $sql = "
        SELECT 
            t.*,
            e.nome as evento_nome,
            m.nome as modalidade_nome
        FROM termos_eventos t
        INNER JOIN eventos e ON t.evento_id = e.id
        LEFT JOIN modalidades m ON t.modalidade_id = m.id
        WHERE (e.organizador_id = ? OR e.organizador_id = ?) $whereClause
        ORDER BY t.data_criacao DESC
        LIMIT $limite OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $termos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contar total para paginação
    $countSql = "
        SELECT COUNT(*) as total
        FROM termos_eventos t
        INNER JOIN eventos e ON t.evento_id = e.id
        WHERE (e.organizador_id = ? OR e.organizador_id = ?) $whereClause
    ";

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    $totalPaginas = ceil($total / $limite);

    echo json_encode([
        'success' => true,
        'termos' => $termos,
        'total' => $total,
        'total_pages' => $totalPaginas,
        'current_page' => $pagina
    ]);
} catch (Exception $e) {
    error_log('Erro ao listar termos: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
