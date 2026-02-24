<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../assessoria/middleware.php';
requireAssessorAPI();

$assessoria_id = getAssessoriaDoUsuario();
if (!$assessoria_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Assessoria nao encontrada']);
    exit;
}

try {
    $status_filtro = $_GET['status'] ?? '';
    $tipo_filtro = $_GET['tipo'] ?? '';

    $sql = "
        SELECT p.*, 
               (SELECT COUNT(*) FROM assessoria_programa_atletas pa 
                WHERE pa.programa_id = p.id AND pa.status = 'ativo') as total_atletas,
               e.titulo as evento_titulo, e.data_inicio as evento_data
        FROM assessoria_programas p
        LEFT JOIN eventos e ON p.evento_id = e.id
        WHERE p.assessoria_id = ?
    ";
    $params = [$assessoria_id];

    if ($status_filtro && in_array($status_filtro, ['rascunho', 'ativo', 'encerrado'])) {
        $sql .= " AND p.status = ?";
        $params[] = $status_filtro;
    }

    if ($tipo_filtro && in_array($tipo_filtro, ['evento', 'continuo'])) {
        $sql .= " AND p.tipo = ?";
        $params[] = $tipo_filtro;
    }

    $sql .= " ORDER BY p.updated_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'programas' => $programas]);
} catch (Exception $e) {
    error_log("[PROGRAMAS_LIST] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
