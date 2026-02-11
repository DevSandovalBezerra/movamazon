<?php
// Desabilitar exibição de erros para não quebrar JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json; charset=utf-8');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

try {
    $sql = "SELECT id, nome 
            FROM eventos 
            WHERE deleted_at IS NULL 
            ORDER BY nome ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $eventos
    ]);
} catch (Throwable $e) {
    error_log('[ADMIN_EVENTOS_LIST] Erro: ' . $e->getMessage());
    error_log('[ADMIN_EVENTOS_LIST] Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao listar eventos',
        'error' => $e->getMessage()
    ]);
}
