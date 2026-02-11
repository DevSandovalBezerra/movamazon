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
    // Buscar todos os produtos ativos (globais)
    $sql = "SELECT 
                id,
                nome,
                descricao,
                tipo,
                preco,
                disponivel_venda,
                foto_produto,
                ativo,
                data_criacao
            FROM produtos 
            WHERE ativo = 1
            ORDER BY nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar dados
    foreach ($produtos as &$produto) {
        $produto['preco_formatado'] = 'R$ ' . number_format($produto['preco'], 2, ',', '.');
        $produto['disponivel_venda'] = (bool)$produto['disponivel_venda'];
        $produto['ativo'] = (bool)$produto['ativo'];
        $produto['data_criacao_formatada'] = date('d/m/Y', strtotime($produto['data_criacao']));
    }

    echo json_encode([
        'success' => true,
        'data' => $produtos
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
