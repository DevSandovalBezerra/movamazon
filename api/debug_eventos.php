<?php
header('Content-Type: application/json');
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo json_encode([
    'session_user_id' => $_SESSION['user_id'] ?? 'null',
    'session_papel' => $_SESSION['papel'] ?? 'null',
    'total_eventos' => 0,
    'eventos_organizador' => []
]);

try {
    // Contar total de eventos
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM eventos');
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Buscar eventos do organizador se logado
    $eventos = [];
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare('SELECT id, nome, organizador_id, status FROM eventos WHERE organizador_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'session_user_id' => $_SESSION['user_id'] ?? 'null',
        'session_papel' => $_SESSION['papel'] ?? 'null',
        'total_eventos' => $total,
        'eventos_organizador' => $eventos
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'session_user_id' => $_SESSION['user_id'] ?? 'null',
        'session_papel' => $_SESSION['papel'] ?? 'null'
    ]);
}
?>
