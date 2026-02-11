<?php
/**
 * Endpoint público para download de regulamento de evento
 * Permite que participantes baixem o regulamento durante a inscrição
 */

require_once __DIR__ . '/../db.php';

$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;

if (!$evento_id) {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo 'ID do evento não informado';
    exit;
}

try {
    // Verificar se a coluna regulamento_arquivo existe
    $hasRegulamentoArquivo = false;
    try {
        $stCol = $pdo->prepare("
            SELECT COUNT(*) 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'eventos' 
              AND COLUMN_NAME = 'regulamento_arquivo'
        ");
        $stCol->execute();
        $hasRegulamentoArquivo = ((int)$stCol->fetchColumn() > 0);
    } catch (Throwable $e) {
        $hasRegulamentoArquivo = false;
    }

    if (!$hasRegulamentoArquivo) {
        http_response_code(404);
        header('Content-Type: text/plain');
        echo 'Regulamento não disponível';
        exit;
    }

    // Verificar se o evento existe e está ativo
    $stmt = $pdo->prepare("SELECT id, regulamento_arquivo, status FROM eventos WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        http_response_code(404);
        header('Content-Type: text/plain');
        echo 'Evento não encontrado';
        exit;
    }
    
    if (empty($evento['regulamento_arquivo'])) {
        http_response_code(404);
        header('Content-Type: text/plain');
        echo 'Regulamento não encontrado para este evento';
        exit;
    }
    
    $stored = (string)$evento['regulamento_arquivo'];
    $root = dirname(__DIR__, 1);
    
    // Suporte a dois formatos:
    // 1) novo: apenas nome do arquivo salvo em api/uploads/regulamentos/
    // 2) legado: caminho tipo frontend/assets/docs/regulamentos/arquivo.pdf
    $candidates = [];
    
    if (str_contains($stored, '/') || str_contains($stored, '\\')) {
        $candidate = $root . DIRECTORY_SEPARATOR . ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $stored), DIRECTORY_SEPARATOR);
        $candidates[] = $candidate;
    }
    
    $filename = basename($stored);
    $candidates[] = $root . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'regulamentos' . DIRECTORY_SEPARATOR . $filename;
    $candidates[] = $root . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'regulamentos' . DIRECTORY_SEPARATOR . $filename;
    
    $filePath = null;
    foreach ($candidates as $cand) {
        if (file_exists($cand) && is_file($cand)) {
            $filePath = $cand;
            break;
        }
    }
    
    if (!$filePath) {
        http_response_code(404);
        header('Content-Type: text/plain');
        echo 'Arquivo não encontrado no servidor';
        exit;
    }
    
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowedExt = ['pdf', 'doc', 'docx'];
    
    if (!in_array($ext, $allowedExt, true)) {
        http_response_code(403);
        header('Content-Type: text/plain');
        echo 'Tipo de arquivo não permitido';
        exit;
    }
    
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
    
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="' . htmlspecialchars($filename) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: private, max-age=3600');
    
    readfile($filePath);
    exit;
    
} catch (Exception $e) {
    error_log('Erro em download-regulamento.php: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain');
    echo 'Erro interno do servidor';
    exit;
}
?>
