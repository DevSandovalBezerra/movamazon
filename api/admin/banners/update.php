<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$id = isset($payload['id']) ? (int) $payload['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$titulo = trim($payload['titulo'] ?? '');
$imagem = trim($payload['imagem'] ?? '');

if ($titulo === '' || $imagem === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Título e imagem são obrigatórios']);
    exit;
}

$descricao = trim($payload['descricao'] ?? '');
$link = trim($payload['link'] ?? '');
$textoBotao = trim($payload['texto_botao'] ?? '');
$targetBlank = !empty($payload['target_blank']) ? 1 : 0;
$ativo = isset($payload['ativo']) ? (int) !!$payload['ativo'] : 1;
$dataInicio = $payload['data_inicio'] ?? null;
$dataFim = $payload['data_fim'] ?? null;

try {
    $stmt = $pdo->prepare("UPDATE banners SET 
        titulo = :titulo,
        descricao = :descricao,
        imagem = :imagem,
        link = :link,
        texto_botao = :texto_botao,
        ativo = :ativo,
        data_inicio = :data_inicio,
        data_fim = :data_fim,
        target_blank = :target_blank
    WHERE id = :id");

    $stmt->execute([
        'titulo' => $titulo,
        'descricao' => $descricao,
        'imagem' => $imagem,
        'link' => $link ?: null,
        'texto_botao' => $textoBotao ?: null,
        'ativo' => $ativo,
        'data_inicio' => $dataInicio ?: null,
        'data_fim' => $dataFim ?: null,
        'target_blank' => $targetBlank,
        'id' => $id
    ]);

    echo json_encode(['success' => true, 'message' => 'Banner atualizado']);
} catch (Throwable $e) {
    error_log('[ADMIN_BANNERS_UPDATE] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar banner']);
}

