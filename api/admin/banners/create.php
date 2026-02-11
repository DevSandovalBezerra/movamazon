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

$titulo = trim($payload['titulo'] ?? '');
$imagem = trim($payload['imagem'] ?? '');
$descricao = trim($payload['descricao'] ?? '');
$link = trim($payload['link'] ?? '');
$textoBotao = trim($payload['texto_botao'] ?? '');
$targetBlank = !empty($payload['target_blank']) ? 1 : 0;
$ativo = isset($payload['ativo']) ? (int) !!$payload['ativo'] : 1;
$dataInicio = $payload['data_inicio'] ?? null;
$dataFim = $payload['data_fim'] ?? null;

if ($titulo === '' || $imagem === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Título e imagem são obrigatórios']);
    exit;
}

try {
    $stmtOrdem = $pdo->query("SELECT COALESCE(MAX(ordem), 0) + 1 AS prox FROM banners");
    $ordem = (int) $stmtOrdem->fetchColumn();

    $stmt = $pdo->prepare("INSERT INTO banners (titulo, descricao, imagem, link, texto_botao, ordem, ativo, data_inicio, data_fim, target_blank) VALUES (:titulo, :descricao, :imagem, :link, :texto_botao, :ordem, :ativo, :data_inicio, :data_fim, :target_blank)");
    $stmt->execute([
        'titulo' => $titulo,
        'descricao' => $descricao,
        'imagem' => $imagem,
        'link' => $link ?: null,
        'texto_botao' => $textoBotao ?: null,
        'ordem' => $ordem,
        'ativo' => $ativo,
        'data_inicio' => $dataInicio ?: null,
        'data_fim' => $dataFim ?: null,
        'target_blank' => $targetBlank
    ]);

    echo json_encode(['success' => true, 'message' => 'Banner criado com sucesso', 'id' => $pdo->lastInsertId()]);
} catch (Throwable $e) {
    error_log('[ADMIN_BANNERS_CREATE] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao criar banner']);
}

