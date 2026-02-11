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

try {
    $stmt = $pdo->query("SELECT * FROM banners ORDER BY ordem ASC, id ASC");
    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalizar caminhos das imagens
    foreach ($banners as &$banner) {
        if (!empty($banner['imagem'])) {
            // Se não for URL completa, garantir que comece com /
            if (strpos($banner['imagem'], 'http://') !== 0 && strpos($banner['imagem'], 'https://') !== 0) {
                $banner['imagem'] = '/' . ltrim($banner['imagem'], '/');
            }
        }
    }

    echo json_encode(['success' => true, 'data' => $banners]);
} catch (Throwable $e) {
    error_log('[ADMIN_BANNERS_LIST] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao listar banners']);
}

