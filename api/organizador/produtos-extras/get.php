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
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    $produto_extra_id = (int)($_GET['id'] ?? 0);

    if ($produto_extra_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID do produto extra é obrigatório']);
        exit();
    }

    // Buscar produto extra com produtos relacionados
    $sql = "SELECT 
                pe.id,
                pe.nome,
                pe.descricao,
                pe.evento_id,
                pe.valor,
                pe.categoria,
                pe.disponivel_venda,
                pe.ativo,
                pe.data_criacao,
                pe.updated_at,
                e.nome as evento_nome,
                e.data_inicio as data_evento
            FROM produtos_extras pe
            INNER JOIN eventos e ON pe.evento_id = e.id
            WHERE pe.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$produto_extra_id, $organizador_id, $usuario_id]);
    $produto_extra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto_extra) {
        echo json_encode(['success' => false, 'error' => 'Produto extra não encontrado']);
        exit();
    }

    // Buscar produtos relacionados
    $sql = "SELECT 
                pep.id,
                pep.produto_id,
                pep.quantidade,
                p.nome as produto_nome,
                p.descricao as produto_descricao,
                p.tipo as produto_tipo,
                p.preco as produto_preco,
                p.foto_produto
            FROM produto_extra_produtos pep
            INNER JOIN produtos p ON pep.produto_id = p.id
            WHERE pep.produto_extra_id = ? AND pep.ativo = 1 AND p.ativo = 1
            ORDER BY p.nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$produto_extra_id]);
    $produtos_relacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar dados
    $produto_extra['data_criacao_formatada'] = date('d/m/Y H:i', strtotime($produto_extra['data_criacao']));
    $produto_extra['updated_at_formatada'] = $produto_extra['updated_at'] ? date('d/m/Y H:i', strtotime($produto_extra['updated_at'])) : null;
    $produto_extra['data_evento_formatada'] = date('d/m/Y', strtotime($produto_extra['data_evento']));
    $produto_extra['disponivel_venda'] = (bool)$produto_extra['disponivel_venda'];
    $produto_extra['ativo'] = (bool)$produto_extra['ativo'];
    $produto_extra['valor_formatado'] = 'R$ ' . number_format($produto_extra['valor'], 2, ',', '.');
    $produto_extra['produtos'] = $produtos_relacionados;

    // Formatar produtos relacionados para compatibilidade com JavaScript
    foreach ($produto_extra['produtos'] as &$produto) {
        $produto['id'] = $produto['produto_id']; // Mapear produto_id para id
        $produto['nome'] = $produto['produto_nome']; // Mapear produto_nome para nome
        $produto['preco'] = $produto['produto_preco']; // Mapear produto_preco para preco
        $produto['produto_preco_formatado'] = 'R$ ' . number_format($produto['produto_preco'], 2, ',', '.');
    }

    echo json_encode([
        'success' => true,
        'data' => $produto_extra
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
