<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Verificar autenticação
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit();
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Ler dados do corpo da requisição
$input = json_decode(file_get_contents('php://input'), true);

// Validar dados obrigatórios
$evento_id = $input['evento_id'] ?? null;
$tamanho = $input['tamanho'] ?? null;
$quantidade_inicial = $input['quantidade_inicial'] ?? null;
$ativo = $input['ativo'] ?? 1;

if (!$evento_id || !$tamanho || $quantidade_inicial === null) {
    echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não fornecidos']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Verificar se o evento pertence ao organizador e não está excluído
    $stmt = $pdo->prepare('SELECT id, limite_vagas FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL');
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado, foi excluído ou sem permissão']);
        exit();
    }
    
    // Verificar se já existe um tamanho para este evento
    $stmt = $pdo->prepare('SELECT id FROM camisas WHERE evento_id = ? AND tamanho = ?');
    $stmt->execute([$evento_id, $tamanho]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Já existe um tamanho ' . $tamanho . ' para este evento']);
        exit();
    }
    
    // Calcular total atual de camisas
    $stmt = $pdo->prepare('SELECT SUM(quantidade_inicial) as total_camisas FROM camisas WHERE evento_id = ? AND ativo = 1');
    $stmt->execute([$evento_id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_atual = $resultado['total_camisas'] ?? 0;
    
    // Verificar se excede o limite de vagas
    $limite_vagas = $evento['limite_vagas'] ?? 0;
    $total_novo = $total_atual + $quantidade_inicial;
    
    if ($limite_vagas > 0 && $total_novo > $limite_vagas) {
        $disponivel = $limite_vagas - $total_atual;
        echo json_encode([
            'success' => false, 
            'message' => "Quantidade excede o limite de vagas do evento. Máximo disponível: {$disponivel} camisas"
        ]);
        exit();
    }
    
    // Inserir nova camisa
    $stmt = $pdo->prepare('INSERT INTO camisas (evento_id, tamanho, quantidade_inicial, quantidade_disponivel, ativo) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([
        $evento_id,
        $tamanho,
        $quantidade_inicial,
        $quantidade_inicial, // quantidade_disponivel = quantidade_inicial inicialmente
        $ativo
    ]);
    
    $id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Tamanho criado com sucesso',
        'id' => $id
    ]);
    
} catch (Exception $e) {
    error_log('Erro ao criar camisa: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
