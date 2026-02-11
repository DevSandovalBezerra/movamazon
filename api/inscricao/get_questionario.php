<?php
require_once '../db.php';
header('Content-Type: application/json');
$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
if (!$evento_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Evento não informado']);
    exit;
}
$stmt = $pdo->prepare("SELECT id, texto, tipo, tipo_resposta, obrigatorio, ordem FROM questionario_evento WHERE evento_id = ? AND ativo = 1 AND status_site = 'publicada' ORDER BY ordem, id");
$stmt->execute([$evento_id]);
$perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['success' => true, 'perguntas' => $perguntas]);
