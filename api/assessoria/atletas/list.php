<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../middleware.php';
requireAssessorAPI();

$assessoria_id = getAssessoriaDoUsuario();
if (!$assessoria_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Assessoria nao encontrada']);
    exit;
}

try {
    $status = $_GET['status'] ?? '';

    $sql = "
        SELECT aa.id, aa.status, aa.data_inicio, aa.data_fim, aa.origem, aa.observacoes, aa.created_at,
               u.id as usuario_id, u.nome_completo, u.email, u.telefone,
               ass.nome_completo as assessor_nome
        FROM assessoria_atletas aa
        JOIN usuarios u ON aa.atleta_usuario_id = u.id
        LEFT JOIN usuarios ass ON aa.assessor_usuario_id = ass.id
        WHERE aa.assessoria_id = ?
    ";
    $params = [$assessoria_id];

    if ($status && in_array($status, ['ativo', 'pausado', 'encerrado'])) {
        $sql .= " AND aa.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY u.nome_completo ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $atletas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'atletas' => $atletas,
        'total' => count($atletas)
    ]);
} catch (Exception $e) {
    error_log("[ASSESSORIA_ATLETAS_LIST] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
