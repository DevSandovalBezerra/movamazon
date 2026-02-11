<?php
session_start();
require_once '../../db.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}

try {
    $organizador_id = $_SESSION['user_id'];
    
    // Query para verificar produtos de cada template
    $sql = "SELECT 
                kt.id,
                kt.nome,
                COUNT(ktp.id) as total_produtos,
                GROUP_CONCAT(CONCAT(p.nome, ' (', ktp.quantidade, ')') SEPARATOR ', ') as produtos_lista
            FROM kit_templates kt
            LEFT JOIN kit_template_produtos ktp ON kt.id = ktp.kit_template_id AND ktp.ativo = 1
            LEFT JOIN produtos p ON ktp.produto_id = p.id
            WHERE kt.ativo = 1
            GROUP BY kt.id
            ORDER BY kt.nome ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $templates
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?> 
