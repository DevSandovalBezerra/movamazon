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
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Ler dados do corpo da requisição
$input = json_decode(file_get_contents('php://input'), true);

// Validar dados obrigatórios
$id = $input['id'] ?? null;
$evento_id = $input['evento_id'] ?? null;
$tamanho = $input['tamanho'] ?? null;
$quantidade_inicial = $input['quantidade_inicial'] ?? null;
$ativo = $input['ativo'] ?? 1;

if (!$id || !$evento_id || !$tamanho || $quantidade_inicial === null) {
    echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não fornecidos']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Verificar se a camisa pertence ao organizador
    $stmt = $pdo->prepare('
        SELECT id FROM camisas 
        WHERE id = ? AND evento_id IN (SELECT id FROM eventos WHERE (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL)
    ');
    $stmt->execute([$id, $organizador_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Camisa não encontrada ou sem permissão']);
        exit();
    }
    
    // Verificar se já existe outro tamanho igual para este evento (exceto a própria)
    $stmt = $pdo->prepare('SELECT id FROM camisas WHERE evento_id = ? AND tamanho = ? AND id != ?');
    $stmt->execute([$evento_id, $tamanho, $id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Já existe um tamanho ' . $tamanho . ' para este evento']);
        exit();
    }
    
    // Buscar dados atuais para calcular quantidade_disponivel
    $stmt = $pdo->prepare('SELECT quantidade_inicial, quantidade_vendida, quantidade_reservada FROM camisas WHERE id = ?');
    $stmt->execute([$id]);
    $atual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calcular nova quantidade_disponivel
    $quantidade_disponivel = $quantidade_inicial - $atual['quantidade_vendida'] - $atual['quantidade_reservada'];
    
    // Calcular total atual de camisas (excluindo a própria)
    $stmt = $pdo->prepare('SELECT SUM(quantidade_inicial) as total_camisas FROM camisas WHERE evento_id = ? AND ativo = 1 AND id != ?');
    $stmt->execute([$evento_id, $id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_outros = $resultado['total_camisas'] ?? 0;
    
    // Verificar se excede o limite de vagas
    $stmt = $pdo->prepare('SELECT limite_vagas FROM eventos WHERE id = ?');
    $stmt->execute([$evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    $limite_vagas = $evento['limite_vagas'] ?? 0;
    $total_novo = $total_outros + $quantidade_inicial;
    
    if ($limite_vagas > 0 && $total_novo > $limite_vagas) {
        $disponivel = $limite_vagas - $total_outros;
        echo json_encode([
            'success' => false, 
            'message' => "Quantidade excede o limite de vagas do evento. Máximo disponível: {$disponivel} camisas"
        ]);
        exit();
    }
    
    // Atualizar camisa
    $stmt = $pdo->prepare('
        UPDATE camisas 
        SET tamanho = ?, quantidade_inicial = ?, quantidade_disponivel = ?, ativo = ?
        WHERE id = ?
    ');
    $stmt->execute([
        $tamanho,
        $quantidade_inicial,
        $quantidade_disponivel,
        $ativo,
        $id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Tamanho atualizado com sucesso'
    ]);
    
} catch (Exception $e) {
    error_log('Erro ao atualizar camisa: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
