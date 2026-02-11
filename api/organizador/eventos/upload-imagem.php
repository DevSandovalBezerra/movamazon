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

    // Verificar se o evento pertence ao organizador
    $stmt = $pdo->prepare("SELECT id, imagem FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$evento) {
        echo json_encode(['success' => false, 'error' => 'Evento não encontrado ou sem permissão']);
        exit;
    }

    // Aceitar campo 'imagem' ou 'logo'
    $fileKey = isset($_FILES['imagem']) ? 'imagem' : (isset($_FILES['logo']) ? 'logo' : null);
    if (!$fileKey || empty($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Nenhum arquivo foi enviado ou ocorreu um erro no upload']);
        exit;
    }

    $root = dirname(__DIR__, 3);
    $uploadDir = $root . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'eventos';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    $originalName = $_FILES[$fileKey]['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowedExt, true)) {
        echo json_encode(['success' => false, 'error' => 'Formato de imagem não suportado. Use: JPG, PNG ou WEBP.']);
        exit;
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($_FILES[$fileKey]['size'] > $maxSize) {
        echo json_encode(['success' => false, 'error' => 'Imagem muito grande. Tamanho máximo: 5MB.']);
        exit;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES[$fileKey]['tmp_name']);
    finfo_close($finfo);
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $allowedMime, true)) {
        echo json_encode(['success' => false, 'error' => 'Tipo de arquivo não permitido.']);
        exit;
    }

    // Nome fixo por evento: evento_{id}.{ext} (sobrescreve anterior)
    $nomeArquivo = 'evento_' . $evento_id . '.' . $ext;
    $destPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nomeArquivo;

    if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $destPath)) {
        echo json_encode(['success' => false, 'error' => 'Erro ao salvar imagem']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE eventos SET imagem = ? WHERE id = ?");
    $stmt->execute([$nomeArquivo, $evento_id]);

    $imagemUrl = '../../assets/img/eventos/' . $nomeArquivo;

    echo json_encode([
        'success' => true,
        'message' => 'Imagem enviada com sucesso',
        'data' => [
            'imagem' => $nomeArquivo,
            'imagem_url' => $imagemUrl,
            'nome_original' => $originalName
        ]
    ]);

} catch (Exception $e) {
    error_log('Erro em upload-imagem.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
