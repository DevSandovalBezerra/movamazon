<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_path = dirname(__DIR__);
require_once $base_path . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

header('Content-Type: application/json');

$inscricao_id = $_GET['inscricao_id'] ?? null;
$usuario_id = $_SESSION['user_id'];

if (!$inscricao_id || !is_numeric($inscricao_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da inscrição inválido.']);
    exit();
}

try {
    $sql = "SELECT a.* 
            FROM anamneses a
            WHERE a.usuario_id = ? 
            AND (a.inscricao_id = ? OR a.inscricao_id IS NULL)
            ORDER BY 
                CASE WHEN a.inscricao_id = ? THEN 1 ELSE 2 END,
                a.data_anamnese DESC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $inscricao_id, $inscricao_id]);
    $anamnese = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$anamnese) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Anamnese não encontrada.', 'anamnese' => null]);
        exit();
    }

    echo json_encode([
        'success' => true,
        'anamnese' => $anamnese
    ]);

} catch (PDOException $e) {
    error_log('Erro ao buscar anamnese: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar anamnese.']);
} catch (Exception $e) {
    error_log('Erro geral ao buscar anamnese: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

