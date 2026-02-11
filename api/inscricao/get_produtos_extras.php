<?php
require_once '../db.php';
header('Content-Type: application/json');
$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
if (!$evento_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Evento não informado']);
    exit;
}
$stmt = $pdo->prepare("SELECT pe.id, pe.nome, pe.descricao, pe.valor, pe.categoria FROM produtos_extras pe WHERE pe.evento_id = ? AND pe.ativo = 1 ORDER BY pe.nome");
$stmt->execute([$evento_id]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['success' => true, 'produtos_extras' => $produtos]);
