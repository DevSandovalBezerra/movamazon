<?php
session_start();
require_once '../../db.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}

// Verificar se o ID do template foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID do template não fornecido']);
    exit();
}

$template_id = intval($_GET['id']);

try {
    error_log("🔍 DEBUG get-produtos.php - Template ID: $template_id");
    
    // Query para buscar produtos do template
    $sql = "SELECT 
                ktp.produto_id,
                ktp.quantidade,
                ktp.ordem,
                p.nome as produto_nome,
                p.preco as produto_preco
            FROM kit_template_produtos ktp
            INNER JOIN produtos p ON p.id = ktp.produto_id
            WHERE ktp.kit_template_id = ?
            ORDER BY ktp.ordem ASC";
    
    error_log("🔍 DEBUG get-produtos.php - Query: $sql");
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$template_id]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("🔍 DEBUG get-produtos.php - Produtos encontrados: " . count($produtos));
    if (count($produtos) > 0) {
        error_log("🔍 DEBUG get-produtos.php - Primeiro produto: " . json_encode($produtos[0]));
    }
    
    echo json_encode([
        'success' => true,
        'data' => $produtos
    ]);
    
} catch (Exception $e) {
    error_log("🔍 DEBUG get-produtos.php - Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?> 
