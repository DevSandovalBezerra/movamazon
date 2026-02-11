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

// Parâmetros
$evento_id = $_GET['evento_id'] ?? null;

if (!$evento_id) {
    echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Verificar se o evento pertence ao organizador
    $stmt = $pdo->prepare('SELECT id, limite_vagas FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL');
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou sem permissão']);
        exit();
    }
    
    // Calcular total de camisas
    $stmt = $pdo->prepare('SELECT SUM(quantidade_inicial) as total_camisas FROM camisas WHERE evento_id = ? AND ativo = 1');
    $stmt->execute([$evento_id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_camisas = $resultado['total_camisas'] ?? 0;
    $limite_vagas = $evento['limite_vagas'] ?? 0;
    $disponivel = $limite_vagas - $total_camisas;
    
    echo json_encode([
        'success' => true,
        'total_camisas' => (int)$total_camisas,
        'limite_vagas' => (int)$limite_vagas,
        'disponivel' => (int)$disponivel,
        'percentual' => $limite_vagas > 0 ? round(($total_camisas / $limite_vagas) * 100, 1) : 0
    ]);
    
} catch (Exception $e) {
    error_log('Erro ao calcular total de camisas: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
