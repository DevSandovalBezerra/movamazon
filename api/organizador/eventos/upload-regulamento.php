<?php
session_start();
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    $evento_id = isset($_POST['evento_id']) ? (int)$_POST['evento_id'] : 0;
    
    if (!$evento_id) {
        echo json_encode(['success' => false, 'error' => 'ID do evento é obrigatório']);
        exit;
    }
    
    // Compatibilidade: garantir que a coluna regulamento_arquivo exista (auto-migration)
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
        try {
            $pdo->exec("ALTER TABLE eventos ADD COLUMN regulamento_arquivo VARCHAR(500) NULL");
            $hasRegulamentoArquivo = true;
        } catch (Throwable $e) {
            error_log('[UPLOAD_REGULAMENTO] Falha ao criar coluna regulamento_arquivo: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Seu banco não possui a coluna regulamento_arquivo e não foi possível criá-la automaticamente. Execute a migração SQL para habilitar o upload do regulamento.'
            ]);
            exit;
        }
    }

    // Verificar se o evento pertence ao organizador
    $stmt = $pdo->prepare("SELECT id, regulamento_arquivo FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        echo json_encode(['success' => false, 'error' => 'Evento não encontrado ou sem permissão']);
        exit;
    }
    
    // Verificar se foi enviado arquivo
    if (empty($_FILES['regulamento_arquivo']) || $_FILES['regulamento_arquivo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Nenhum arquivo foi enviado ou ocorreu um erro no upload']);
        exit;
    }
    
    $uploadDir = dirname(__DIR__, 2) . '/uploads/regulamentos';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }
    
    $originalName = $_FILES['regulamento_arquivo']['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExt = ['pdf', 'doc', 'docx'];
    
    // Validar extensão
    if (!in_array($ext, $allowedExt, true)) {
        echo json_encode(['success' => false, 'error' => 'Formato de arquivo não permitido. Use PDF, DOC ou DOCX.']);
        exit;
    }
    
    // Validar tamanho (10MB)
    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($_FILES['regulamento_arquivo']['size'] > $maxSize) {
        echo json_encode(['success' => false, 'error' => 'Arquivo muito grande. Tamanho máximo: 10MB.']);
        exit;
    }
    
    // Validar MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES['regulamento_arquivo']['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimeTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        echo json_encode(['success' => false, 'error' => 'Tipo de arquivo não permitido.']);
        exit;
    }
    
    // Gerar nome fixo baseado no evento_id (sem timestamp) para sobrescrever
    $baseName = 'regulamento_' . $evento_id . '.' . $ext;
    $destPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $baseName;
    
    // Deletar arquivos antigos do mesmo evento (todas as extensões possíveis)
    $root = dirname(__DIR__, 3);
    $possibleExtensions = ['pdf', 'doc', 'docx'];
    $locationsToCheck = [
        $uploadDir, // api/uploads/regulamentos/
        $root . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'regulamentos' // legado
    ];
    
    foreach ($locationsToCheck as $location) {
        foreach ($possibleExtensions as $oldExt) {
            $oldFile = $location . DIRECTORY_SEPARATOR . 'regulamento_' . $evento_id . '.' . $oldExt;
            if (file_exists($oldFile) && is_file($oldFile)) {
                @unlink($oldFile);
                error_log("[UPLOAD_REGULAMENTO] Arquivo antigo deletado: " . basename($oldFile));
            }
        }
        
        // Deletar também arquivos com timestamp antigo (padrão antigo)
        $pattern = $location . DIRECTORY_SEPARATOR . 'regulamento_' . $evento_id . '_*';
        foreach (glob($pattern) as $oldFile) {
            if (is_file($oldFile)) {
                @unlink($oldFile);
                error_log("[UPLOAD_REGULAMENTO] Arquivo antigo com timestamp deletado: " . basename($oldFile));
            }
        }
    }
    
    // Mover arquivo
    if (!move_uploaded_file($_FILES['regulamento_arquivo']['tmp_name'], $destPath)) {
        echo json_encode(['success' => false, 'error' => 'Erro ao salvar arquivo']);
        exit;
    }
    
    // Atualizar banco de dados - armazenar caminho relativo completo
    $caminhoRelativo = 'api/uploads/regulamentos/' . $baseName;
    $stmt = $pdo->prepare("UPDATE eventos SET regulamento_arquivo = ? WHERE id = ?");
    $stmt->execute([$caminhoRelativo, $evento_id]);
    
    error_log("[UPLOAD_REGULAMENTO] Regulamento atualizado - Evento ID: $evento_id, Arquivo: $baseName");
    
    echo json_encode([
        'success' => true,
        'message' => 'Regulamento enviado com sucesso',
        'data' => [
            'arquivo' => $baseName,
            'nome_original' => $originalName
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Erro em upload-regulamento.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>

