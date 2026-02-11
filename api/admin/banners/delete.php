<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

error_log('[ADMIN_BANNERS_DELETE] Iniciando processo de remoção');

if (!requererAdmin(false)) {
    error_log('[ADMIN_BANNERS_DELETE] Acesso negado - usuário não é admin');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
error_log('[ADMIN_BANNERS_DELETE] Payload recebido: ' . json_encode($payload));

$id = isset($payload['id']) ? (int) $payload['id'] : 0;
error_log('[ADMIN_BANNERS_DELETE] ID extraído: ' . $id);

if ($id <= 0) {
    error_log('[ADMIN_BANNERS_DELETE] ID inválido: ' . $id);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    // Primeiro, buscar o banner para obter o caminho da imagem
    error_log('[ADMIN_BANNERS_DELETE] Buscando banner ID: ' . $id);
    $stmt = $pdo->prepare("SELECT id, titulo, imagem FROM banners WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$banner) {
        error_log('[ADMIN_BANNERS_DELETE] Banner não encontrado no banco: ID ' . $id);
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Banner não encontrado']);
        exit;
    }

    error_log('[ADMIN_BANNERS_DELETE] Banner encontrado: ' . json_encode($banner));

    // Deletar o arquivo de imagem se existir e não for URL externa
    if (!empty($banner['imagem'])) {
        $imagemPath = $banner['imagem'];
        error_log('[ADMIN_BANNERS_DELETE] Caminho da imagem: ' . $imagemPath);
        
        // Se não for URL completa, tentar deletar o arquivo físico
        if (strpos($imagemPath, 'http://') !== 0 && strpos($imagemPath, 'https://') !== 0) {
            // Normalizar caminho
            $imagemPath = '/' . ltrim($imagemPath, '/');
            // Caminho físico do arquivo
            $filePath = dirname(__DIR__, 3) . $imagemPath;
            error_log('[ADMIN_BANNERS_DELETE] Caminho físico do arquivo: ' . $filePath);
            
            // Verificar se o arquivo existe e deletar
            if (file_exists($filePath) && is_file($filePath)) {
                if (@unlink($filePath)) {
                    error_log('[ADMIN_BANNERS_DELETE] ✓ Arquivo de imagem removido com sucesso: ' . $filePath);
                } else {
                    error_log('[ADMIN_BANNERS_DELETE] ✗ Erro ao remover arquivo de imagem: ' . $filePath);
                }
            } else {
                error_log('[ADMIN_BANNERS_DELETE] Arquivo de imagem não encontrado ou não é arquivo: ' . $filePath);
            }
        } else {
            error_log('[ADMIN_BANNERS_DELETE] Imagem é URL externa, não será deletada: ' . $imagemPath);
        }
    } else {
        error_log('[ADMIN_BANNERS_DELETE] Banner não possui imagem associada');
    }

    // Deletar o registro do banco
    error_log('[ADMIN_BANNERS_DELETE] Deletando registro do banco para ID: ' . $id);
    $stmt = $pdo->prepare("DELETE FROM banners WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $rowsAffected = $stmt->rowCount();
    error_log('[ADMIN_BANNERS_DELETE] Linhas afetadas: ' . $rowsAffected);

    if ($rowsAffected > 0) {
        error_log('[ADMIN_BANNERS_DELETE] ✓ Banner removido com sucesso: ID ' . $id);
        echo json_encode([
            'success' => true, 
            'message' => 'Banner "' . ($banner['titulo'] ?? 'ID ' . $id) . '" removido com sucesso'
        ]);
    } else {
        error_log('[ADMIN_BANNERS_DELETE] ✗ Nenhuma linha foi afetada ao deletar banner ID: ' . $id);
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Banner não encontrado no banco de dados']);
    }
} catch (Throwable $e) {
    error_log('[ADMIN_BANNERS_DELETE] ERRO: ' . $e->getMessage());
    error_log('[ADMIN_BANNERS_DELETE] Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao remover banner: ' . $e->getMessage()]);
}

