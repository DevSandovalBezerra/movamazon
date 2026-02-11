<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $organizador_id = $ctx['organizador_id'];

    // Produtos são globais para o organizador, não filtrados por evento
    $sql = "SELECT 
                p.id,
                p.nome,
                p.descricao,
                p.preco,
                p.disponivel_venda,
                p.foto_produto,
                p.ativo,
                p.data_criacao,
                p.updated_at
            FROM produtos p
            WHERE p.ativo = 1
            ORDER BY p.nome ASC";

    $stmt = $pdo->prepare($sql);

    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);


    if (count($produtos) > 0) {
        //error_log("🔍 DEBUG produtos/list.php - Primeiro produto: " . json_encode($produtos[0]));
    }

    // Formatar dados
    foreach ($produtos as &$produto) {
        $produto['data_criacao_formatada'] = date('d/m/Y H:i', strtotime($produto['data_criacao']));
        $produto['updated_at_formatada'] = $produto['updated_at'] ? date('d/m/Y H:i', strtotime($produto['updated_at'])) : null;
        $produto['disponivel_venda'] = (bool)$produto['disponivel_venda'];
        $produto['ativo'] = (bool)$produto['ativo'];

        // Retornar caminho relativo (sem o prefixo frontend/)
        if ($produto['foto_produto']) {
            // Remove o prefixo "frontend/" se existir
            $produto['foto_url'] = str_replace('frontend/', '', $produto['foto_produto']);
        } else {
            $produto['foto_url'] = null;
        }
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
