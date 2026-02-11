<?php
require_once '../../auth/auth.php';
require_once '../../db.php';
header('Content-Type: application/json');

if (!isOrganizador()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID não informado']);
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM cupons_remessa WHERE id = ?');
$stmt->execute([$id]);
$remessa = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$remessa) {
    echo json_encode(['success' => false, 'message' => 'Remessa não encontrada']);
    exit;
}
if ($remessa['evento_id']) {
    $stmt = $pdo->prepare('SELECT id FROM eventos WHERE id = ? AND organizador_id = ?');
    $stmt->execute([$remessa['evento_id'], $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Sem permissão para esta remessa']);
        exit;
    }
}
echo json_encode(['success' => true, 'remessa' => $remessa]); 
