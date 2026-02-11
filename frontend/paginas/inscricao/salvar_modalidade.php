<?php
session_start();
header('Content-Type: application/json');

// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

// Verificar se evento_id está na sessão
if (!isset($_SESSION['inscricao']['evento_id'])) {
    echo json_encode(['success' => false, 'message' => 'Evento não especificado']);
    exit;
}

$evento_id = (int)$_SESSION['inscricao']['evento_id'];

// Processar requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // ✅ Validação completa
    $modalidade_id = (int)($input['modalidade_id'] ?? 0);
    $modalidade_nome = trim($input['modalidade_nome'] ?? '');
    $preco_total = floatval($input['preco_total'] ?? 0);
    $lote_id = (int)($input['lote_id'] ?? 0);
    $lote_numero = (int)($input['lote_numero'] ?? 0);
    $data_fim_lote = $input['data_fim_lote'] ?? '';
    
    // Validações
    error_log("DEBUG salvar_modalidade: modalidade_id=$modalidade_id, nome='$modalidade_nome', preco=$preco_total");
    
    if (!$modalidade_id || !$modalidade_nome || $preco_total <= 0) {
        error_log("ERRO: Dados inválidos - modalidade_id=$modalidade_id, nome='$modalidade_nome', preco=$preco_total");
        echo json_encode(['success' => false, 'message' => 'Dados da modalidade inválidos']);
        exit;
    }
    
    // ✅ Verificar se lote ainda está válido (comentado temporariamente para teste)
    // if ($data_fim_lote && strtotime($data_fim_lote) < time()) {
    //     echo json_encode(['success' => false, 'message' => 'Lote de inscrição expirado']);
    //     exit;
    // }
    
    // Inicializar sessão de inscrição se não existir
    if (!isset($_SESSION['inscricao'])) {
        $_SESSION['inscricao'] = [
            'evento_id' => $evento_id,
            'etapa_atual' => 1,
            'dados' => [],
            'modalidades_selecionadas' => [],
            'produtos_extras' => [],
            'cupom_aplicado' => null,
            'valor_desconto' => 0.00
        ];
    }
    
    // ✅ Salvar dados estruturados
    $_SESSION['inscricao']['modalidades_selecionadas'] = [
        [
            'id' => $modalidade_id,
            'nome' => $modalidade_nome,
            'preco_total' => $preco_total, // ✅ Numérico
            'lote_id' => $lote_id,
            'lote_numero' => $lote_numero,
            'data_fim_lote' => $data_fim_lote,
            'quantidade' => 1
        ]
    ];
    
    // Avançar para próxima etapa
    $_SESSION['inscricao']['etapa_atual'] = 2;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Modalidade salva com sucesso',
        'etapa_atual' => 2,
        'dados' => $_SESSION['inscricao']['modalidades_selecionadas'][0]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>
