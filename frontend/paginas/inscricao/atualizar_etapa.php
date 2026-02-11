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

// Verificar se dados de inscrição existem
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

// Verificar se evento_id da sessão confere
if ($_SESSION['inscricao']['evento_id'] != $evento_id) {
    echo json_encode(['success' => false, 'message' => 'Evento não confere']);
    exit;
}

// Processar requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $nova_etapa = (int)($input['etapa'] ?? 0);
    
    // Validar etapa (1 a 4)
    if ($nova_etapa < 1 || $nova_etapa > 4) {
        echo json_encode(['success' => false, 'message' => 'Etapa inválida']);
        exit;
    }
    
    // Atualizar etapa na sessão
    $_SESSION['inscricao']['etapa_atual'] = $nova_etapa;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Etapa atualizada com sucesso',
        'etapa_atual' => $nova_etapa
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>
