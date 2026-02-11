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

// Parâmetros de filtro
$evento_id = $_GET['evento_id'] ?? null;
$ativo = $_GET['ativo'] ?? null;

if (!$evento_id) {
    echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Verificar se o evento pertence ao organizador
    $stmt = $pdo->prepare('SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL');
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou sem permissão']);
        exit();
    }
    
    // Construir query
    $sql = "SELECT 
                c.id,
                c.evento_id,
                c.produto_id,
                c.tamanho,
                c.quantidade_inicial,
                c.quantidade_vendida,
                c.quantidade_disponivel,
                c.quantidade_reservada,
                c.ativo,
                c.data_criacao,
                p.nome as produto_nome
            FROM camisas c
            LEFT JOIN produtos p ON c.produto_id = p.id
            WHERE c.evento_id = ?";
    
    $params = [$evento_id];
    
    if ($ativo !== null && $ativo !== '') {
        $sql .= " AND c.ativo = ?";
        $params[] = $ativo;
    }
    
    $sql .= " ORDER BY c.tamanho ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $camisas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'camisas' => $camisas
    ]);
    
} catch (Exception $e) {
    error_log('Erro ao listar camisas: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
