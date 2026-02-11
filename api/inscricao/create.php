<?php
require_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    exit;
}
// Validação básica
$campos_obrigatorios = ['evento_id','usuario_id','modalidade_id','tamanho_camiseta'];
foreach ($campos_obrigatorios as $campo) {
    if (empty($data[$campo])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Campo obrigatório: $campo"]);
        exit;
    }
}
try {
    $stmt = $pdo->prepare("INSERT INTO inscricoes (evento_id, usuario_id, modalidade_id, tamanho_camiseta, status, status_pagamento, data_inscricao) VALUES (?, ?, ?, ?, 'ativa', 'pendente', NOW())");
    $stmt->execute([
        $data['evento_id'],
        $data['usuario_id'],
        $data['modalidade_id'],
        $data['tamanho_camiseta']
    ]);
    $id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'inscricao_id' => $id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
