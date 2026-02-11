<?php
header('Content-Type: application/json');
require_once '../db.php';

try {
    $sql = "SELECT DISTINCT cidade FROM eventos WHERE status = 'ativo' AND cidade IS NOT NULL AND cidade != '' ORDER BY cidade";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $cidades = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['success' => true, 'cidades' => $cidades]);
} catch (PDOException $e) {
    error_log("Erro ao listar cidades: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
