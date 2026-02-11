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

$usuario_id = $_SESSION['user_id'];

try {
    $sql = "SELECT * 
            FROM anamneses 
            WHERE usuario_id = ? AND inscricao_id IS NULL
            ORDER BY data_anamnese DESC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $anamnese = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$anamnese) {
        echo json_encode([
            'success' => true,
            'anamnese' => null,
            'message' => 'Nenhuma anamnese encontrada.'
        ]);
        exit();
    }

    echo json_encode([
        'success' => true,
        'anamnese' => $anamnese
    ]);

} catch (PDOException $e) {
    error_log('Erro ao buscar anamnese geral: ' . $e->getMessage());
    http_response_code(500);
    
    $message = 'Erro ao buscar anamnese.';
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $message = 'Tabela anamneses não encontrada. Execute o script SQL: scripts/criar_tabela_anamneses.sql';
    }
    
    echo json_encode(['success' => false, 'message' => $message]);
} catch (Exception $e) {
    error_log('Erro geral ao buscar anamnese geral: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

