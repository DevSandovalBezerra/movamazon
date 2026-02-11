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

    $evento_id = $_GET['evento_id'] ?? null;

    if (!$evento_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
        exit;
    }

    $inscricao = $_SESSION['inscricao'] ?? [];

    if (!isset($inscricao['evento_id']) || (int)$inscricao['evento_id'] !== (int)$evento_id) {
        // Log warning: sessão vazia ou inválida
        logInscricaoPagamento('WARNING', 'SESSAO_VAZIA_OU_INVALIDA', [
            'usuario_id' => $_SESSION['user_id'] ?? null,
            'evento_id' => $evento_id
        ]);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'evento_id' => (int)$evento_id,
                'etapa_atual' => 1,
                'modalidades_selecionadas' => [],
                'ficha' => [
                    'tamanho_camiseta' => null,
                    'produtos_extras' => [],
                    'respostas_questionario' => [] // ✅ Corrigido: {} não é válido em PHP, usar []
                ],
                'cupom_aplicado' => null,
                'valor_desconto' => 0
            ]
        ]);
        exit;
    }

    // ✅ Garantir que dados da inscrição estão no formato correto
    $inscricao_formatada = [
        'evento_id' => (int)($inscricao['evento_id'] ?? 0),
        'etapa_atual' => (int)($inscricao['etapa_atual'] ?? 1),
        'modalidades_selecionadas' => $inscricao['modalidades_selecionadas'] ?? [],
        'ficha' => $inscricao['ficha'] ?? [
            'tamanho_camiseta' => null,
            'produtos_extras' => [],
            'respostas_questionario' => []
        ],
        'cupom_aplicado' => $inscricao['cupom_aplicado'] ?? null,
        'valor_desconto' => floatval($inscricao['valor_desconto'] ?? 0),
        'id' => $inscricao['id'] ?? null
    ];

    echo json_encode([
        'success' => true,
        'data' => $inscricao_formatada
    ]);
} catch (Exception $e) {
    // ✅ Log do erro para debug
    error_log("Erro em get_session.php: " . $e->getMessage());
    
    // Log error específico de inscrição
    logInscricaoPagamento('ERROR', 'ERRO_RECUPERACAO_SESSAO', [
        'usuario_id' => $_SESSION['user_id'] ?? null,
        'evento_id' => $evento_id ?? null,
        'erro' => $e->getMessage(),
        'stack_trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao recuperar dados da sessão',
        'error' => $e->getMessage()
    ]);
}
?>
