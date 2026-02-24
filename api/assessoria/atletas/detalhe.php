<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../middleware.php';
requireAssessorAPI();

$assessoria_id = getAssessoriaDoUsuario();
if (!$assessoria_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Assessoria nao encontrada']);
    exit;
}

try {
    $atleta_id = (int) ($_GET['id'] ?? 0);

    if (!$atleta_id) {
        throw new Exception('ID do atleta e obrigatorio');
    }

    // Dados do vinculo
    $stmt = $pdo->prepare("
        SELECT aa.*, u.nome_completo, u.email, u.telefone, u.documento, u.data_nascimento,
               u.cidade, u.uf, u.genero,
               ass.nome_completo as assessor_nome
        FROM assessoria_atletas aa
        JOIN usuarios u ON aa.atleta_usuario_id = u.id
        LEFT JOIN usuarios ass ON aa.assessor_usuario_id = ass.id
        WHERE aa.assessoria_id = ? AND aa.atleta_usuario_id = ?
        LIMIT 1
    ");
    $stmt->execute([$assessoria_id, $atleta_id]);
    $atleta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$atleta) {
        throw new Exception('Atleta nao encontrado nesta assessoria');
    }

    // Inscricoes em eventos
    $stmt = $pdo->prepare("
        SELECT i.id, i.status_pagamento, i.created_at,
               e.titulo as evento_titulo, e.data_inicio as evento_data
        FROM inscricoes i
        JOIN eventos e ON i.evento_id = e.id
        WHERE i.usuario_id = ?
        ORDER BY e.data_inicio DESC
        LIMIT 10
    ");
    $stmt->execute([$atleta_id]);
    $inscricoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Programas do atleta
    $stmt = $pdo->prepare("
        SELECT pa.status, p.titulo, p.tipo, p.data_inicio, p.data_fim
        FROM assessoria_programa_atletas pa
        JOIN assessoria_programas p ON pa.programa_id = p.id
        WHERE pa.atleta_usuario_id = ? AND p.assessoria_id = ?
        ORDER BY p.data_inicio DESC
    ");
    $stmt->execute([$atleta_id, $assessoria_id]);
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'atleta' => $atleta,
        'inscricoes' => $inscricoes,
        'programas' => $programas
    ]);
} catch (Exception $e) {
    error_log("[ASSESSORIA_ATLETA_DETALHE] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
