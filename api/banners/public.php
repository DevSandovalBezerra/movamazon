<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

try {
    $stmt = $pdo->prepare("
        SELECT id, titulo, descricao, imagem, link, texto_botao, target_blank
        FROM banners
        WHERE ativo = 1
          AND (data_inicio IS NULL OR data_inicio <= NOW())
          AND (data_fim IS NULL OR data_fim >= NOW())
        ORDER BY ordem ASC, id ASC
    ");
    $stmt->execute();
    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Se não houver banners no banco, retornar array vazio (sem fallback mockado)
    if (empty($banners)) {
        echo json_encode(['success' => true, 'banners' => []]);
        exit;
    }

    foreach ($banners as &$banner) {
        if (!empty($banner['imagem'])) {
            // Se não for URL completa, garantir que comece com /
            if (strpos($banner['imagem'], 'http://') !== 0 && strpos($banner['imagem'], 'https://') !== 0) {
                $banner['imagem'] = '/' . ltrim($banner['imagem'], '/');
            }
        }
        $banner['target_blank'] = (int) $banner['target_blank'];
    }

    echo json_encode(['success' => true, 'banners' => $banners]);
} catch (Throwable $e) {
    error_log('[PUBLIC_BANNERS] ' . $e->getMessage());
    http_response_code(200);
    echo json_encode(['success' => true, 'banners' => []]);
}

