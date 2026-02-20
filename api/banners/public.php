<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

function bannerFileExists($relativePath)
{
    $relativePath = trim((string) $relativePath);
    if ($relativePath === '') {
        return false;
    }
    if (strpos($relativePath, 'http://') === 0 || strpos($relativePath, 'https://') === 0) {
        return true;
    }

    $normalized = ltrim(str_replace('\\', '/', $relativePath), '/');
    $fullPath = dirname(__DIR__, 2) . '/' . $normalized;
    return is_file($fullPath);
}

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
        echo json_encode(['success' => true, 'banners' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    foreach ($banners as &$banner) {
        if (!empty($banner['imagem'])) {
            // Se não for URL completa, garantir que comece com /
            if (strpos($banner['imagem'], 'http://') !== 0 && strpos($banner['imagem'], 'https://') !== 0) {
                $banner['imagem'] = '/' . ltrim($banner['imagem'], '/');
            }
            if (!bannerFileExists($banner['imagem'])) {
                $banner['imagem'] = '';
            }
        }
        $banner['target_blank'] = (int) $banner['target_blank'];
    }

    echo json_encode(['success' => true, 'banners' => $banners], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('[PUBLIC_BANNERS] ' . $e->getMessage());
    http_response_code(200);
    echo json_encode(['success' => true, 'banners' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

