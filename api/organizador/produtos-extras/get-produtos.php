<?php
session_start();
require_once '../../db.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}

// Verificar se o ID do produto extra foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID do produto extra não fornecido']);
    exit();
}

$produto_extra_id = intval($_GET['id']);

try {
    // Query para buscar produtos do produto extra
    $sql = "SELECT 
                pep.produto_id,
                pep.quantidade,
                p.nome as produto_nome,
                p.preco as produto_preco,
                p.tipo as produto_tipo
            FROM produto_extra_produtos pep
            INNER JOIN produtos p ON p.id = pep.produto_id
            WHERE pep.produto_extra_id = ? AND pep.ativo = 1
            ORDER BY pep.id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$produto_extra_id]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $produtos
    ]);
} catch (Exception $e) {
    error_log("Erro ao carregar produtos do produto extra: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
