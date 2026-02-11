<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../admin/auth_middleware.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Permite ADMIN ou ORGANIZADOR (com validação de posse do arquivo)
$isAdmin = false;
try {
    $isAdmin = requererAdmin(false);
} catch (Throwable $e) {
    $isAdmin = false;
}

$filename = isset($_GET['file']) ? basename($_GET['file']) : '';

if (empty($filename)) {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo 'Nome do arquivo não informado';
    exit;
}

$uploadDir = __DIR__;
$filePath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo 'Arquivo não encontrado';
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

// ✅ NOVO: Permitir acesso público se o arquivo pertence a um evento ativo
$isPublicAccess = false;
if (!$isAdmin) {
    // Verificar se o arquivo pertence a um evento ativo (acesso público)
    try {
        $hasCol = false;
        try {
            $stCol = $pdo->prepare("
                SELECT COUNT(*) 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'eventos' 
                  AND COLUMN_NAME = 'regulamento_arquivo'
            ");
            $stCol->execute();
            $hasCol = ((int)$stCol->fetchColumn() > 0);
        } catch (Throwable $e) {
            $hasCol = false;
        }
        
        if ($hasCol) {
            // Verificar se o arquivo está associado a um evento ativo
            // O campo regulamento_arquivo pode ter diferentes formatos:
            // - "frontend/assets/docs/regulamentos/arquivo.pdf"
            // - "api/uploads/regulamentos/arquivo.pdf"
            // - "arquivo.pdf" (apenas nome)
            $stEvento = $pdo->prepare("
                SELECT e.id
                FROM eventos e
                WHERE e.deleted_at IS NULL
                  AND e.status = 'ativo'
                  AND (
                        e.regulamento_arquivo = ?
                     OR e.regulamento_arquivo = CONCAT('frontend/assets/docs/regulamentos/', ?)
                     OR e.regulamento_arquivo = CONCAT('api/uploads/regulamentos/', ?)
                     OR e.regulamento_arquivo LIKE CONCAT('%/', ?)
                     OR e.regulamento_arquivo LIKE CONCAT('%\\\\', ?)
                  )
                LIMIT 1
            ");
            $stEvento->execute([$filename, $filename, $filename, $filename, $filename]);
            if ($stEvento->fetch(PDO::FETCH_ASSOC)) {
                $isPublicAccess = true; // Arquivo pertence a evento ativo - acesso público permitido
            }
        }

        // Fallback público: arquivo veio de solicitacao aprovada
        if (!$isPublicAccess) {
            $stSolic = $pdo->prepare("
                SELECT id
                FROM solicitacoes_evento
                WHERE status = 'aprovado'
                  AND link_regulamento = ?
                LIMIT 1
            ");
            $stSolic->execute([$filename]);
            if ($stSolic->fetch(PDO::FETCH_ASSOC)) {
                $isPublicAccess = true;
            }
        }
    } catch (Throwable $e) {
        error_log('[UPLOADS_REGULAMENTOS_DOWNLOAD] Erro ao verificar evento ativo: ' . $e->getMessage());
    }
}

if (!$isAdmin && !$isPublicAccess) {
    // ORGANIZADOR: precisa estar logado e o arquivo precisa pertencer a ele
    if (!isset($_SESSION['user_id']) || ($_SESSION['papel'] ?? '') !== 'organizador') {
        http_response_code(403);
        header('Content-Type: text/plain');
        echo 'Acesso negado';
        exit;
    }

    try {
        $ctx = requireOrganizadorContext($pdo);
        $usuario_id = $ctx['usuario_id'];
        $organizador_id = $ctx['organizador_id'];
        $userEmail = $_SESSION['user_email'] ?? '';

        $allowed = false;

        // 1) Se a coluna eventos.regulamento_arquivo existir, valida por evento do organizador
        $hasCol = false;
        try {
            $stCol = $pdo->prepare("
                SELECT COUNT(*) 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'eventos' 
                  AND COLUMN_NAME = 'regulamento_arquivo'
            ");
            $stCol->execute();
            $hasCol = ((int)$stCol->fetchColumn() > 0);
        } catch (Throwable $e) {
            $hasCol = false;
        }

        if ($hasCol) {
            $st = $pdo->prepare("
                SELECT e.id
                FROM eventos e
                WHERE (e.organizador_id = ? OR e.organizador_id = ?)
                  AND e.deleted_at IS NULL
                  AND (
                        e.regulamento_arquivo = ?
                     OR e.regulamento_arquivo = CONCAT('frontend/assets/docs/regulamentos/', ?)
                  )
                LIMIT 1
            ");
            $st->execute([$organizador_id, $usuario_id, $filename, $filename]);
            if ($st->fetch(PDO::FETCH_ASSOC)) {
                $allowed = true;
            }
        }

        // 2) Fallback: arquivo veio da solicitação aprovada (solicitacoes_evento.link_regulamento)
        if (!$allowed && $userEmail !== '') {
            $st2 = $pdo->prepare("
                SELECT id
                FROM solicitacoes_evento
                WHERE status = 'aprovado'
                  AND responsavel_email = :email
                  AND link_regulamento = :file
                LIMIT 1
            ");
            $st2->execute(['email' => $userEmail, 'file' => $filename]);
            if ($st2->fetch(PDO::FETCH_ASSOC)) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            http_response_code(403);
            header('Content-Type: text/plain');
            echo 'Acesso negado';
            exit;
        }
    } catch (Throwable $e) {
        error_log('[UPLOADS_REGULAMENTOS_DOWNLOAD] ' . $e->getMessage());
        http_response_code(403);
        header('Content-Type: text/plain');
        echo 'Acesso negado';
        exit;
    }
}

header('Content-Type: ' . $mimeType);
header('Content-Disposition: inline; filename="' . htmlspecialchars($filename) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=3600');

readfile($filePath);
exit;

