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

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$banner) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Banner não encontrado']);
        exit;
    }

    // Normalizar caminho da imagem
    if (!empty($banner['imagem'])) {
        if (strpos($banner['imagem'], 'http://') !== 0 && strpos($banner['imagem'], 'https://') !== 0) {
            $banner['imagem'] = '/' . ltrim($banner['imagem'], '/');
        }
    }

    echo json_encode(['success' => true, 'data' => $banner]);
} catch (Throwable $e) {
    error_log('[ADMIN_BANNERS_GET] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar banner']);
}

