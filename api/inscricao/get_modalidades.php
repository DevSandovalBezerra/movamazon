<?php
require_once '../db.php';
header('Content-Type: application/json');
$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
//error_log('🔍 [DEBUG] Evento ID: ' . $evento_id);
if (!$evento_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Evento não informado']);
    exit;
}
$stmt = $pdo->prepare("SELECT m.id, m.nome, c.nome as categoria FROM modalidades m LEFT JOIN categorias c ON m.categoria_id = c.id WHERE m.evento_id = ? AND m.ativo = 1");
$stmt->execute([$evento_id]);
$modalidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
//error_log(print_r($modalidades, true));
echo json_encode(['success' => true, 'modalidades' => $modalidades]);
