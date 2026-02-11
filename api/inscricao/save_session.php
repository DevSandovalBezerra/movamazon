<?php
// ✅ Verificar se sessão já foi iniciada para evitar erro
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Garantir que não há output antes do header
ob_start();

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/inscricao_logger.php';

// ✅ Limpar qualquer output acidental antes de enviar header
ob_clean();
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        exit;
    }

    $evento_id = $data['evento_id'] ?? null;

    if (!$evento_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
        exit;
    }

    if (!isset($_SESSION['inscricao'])) {
        $_SESSION['inscricao'] = [];
    }

    // ✅ Garantir que dados estão no formato correto antes de mesclar
    $data_formatada = [
        'evento_id' => (int)($data['evento_id'] ?? 0),
        'etapa_atual' => (int)($data['etapa_atual'] ?? 1),
        'modalidades_selecionadas' => $data['modalidades_selecionadas'] ?? [],
        'ficha' => $data['ficha'] ?? [
            'tamanho_camiseta' => null,
            'produtos_extras' => [],
            'respostas_questionario' => []
        ],
        'cupom_aplicado' => $data['cupom_aplicado'] ?? null,
        'valor_desconto' => floatval($data['valor_desconto'] ?? 0)
    ];

    $_SESSION['inscricao'] = array_merge($_SESSION['inscricao'], $data_formatada);

    echo json_encode([
        'success' => true,
        'message' => 'Dados salvos com sucesso',
        'data' => $_SESSION['inscricao']
    ]);
} catch (Exception $e) {
    // ✅ Log do erro para debug
    error_log("Erro em save_session.php: " . $e->getMessage());
    
    // Log error específico de inscrição
    logInscricaoPagamento('ERROR', 'ERRO_SALVAMENTO_SESSAO', [
        'usuario_id' => $_SESSION['user_id'] ?? null,
        'evento_id' => $evento_id ?? null,
        'erro' => $e->getMessage(),
        'stack_trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar dados da sessão',
        'error' => $e->getMessage()
    ]);
}
?>
