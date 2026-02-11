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

    // Parâmetros de filtro
    $evento_id = $_GET['evento_id'] ?? null;

    if (!$evento_id) {
        echo json_encode([
            'success' => false,
            'error' => 'Evento ID é obrigatório'
        ]);
        exit();
    }

    // Query para buscar produtos extras do evento
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
                e.data_inicio as data_evento,
                COUNT(DISTINCT pep.produto_id) as total_produtos,
                GROUP_CONCAT(DISTINCT p.nome ORDER BY p.nome SEPARATOR ', ') as produtos_nomes
            FROM produtos_extras pe
            INNER JOIN eventos e ON pe.evento_id = e.id
            LEFT JOIN produto_extra_produtos pep ON pe.id = pep.produto_extra_id AND pep.ativo = 1
            LEFT JOIN produtos p ON pep.produto_id = p.id AND p.ativo = 1
            WHERE (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL AND pe.evento_id = ?
            GROUP BY pe.id
            ORDER BY pe.nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$organizador_id, $usuario_id, $evento_id]);
    $produtos_extras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar dados
    foreach ($produtos_extras as &$produto_extra) {
        $produto_extra['data_criacao_formatada'] = date('d/m/Y H:i', strtotime($produto_extra['data_criacao']));
        $produto_extra['updated_at_formatada'] = $produto_extra['updated_at'] ? date('d/m/Y H:i', strtotime($produto_extra['updated_at'])) : null;
        $produto_extra['data_evento_formatada'] = date('d/m/Y', strtotime($produto_extra['data_evento']));
        $produto_extra['disponivel_venda'] = (bool)$produto_extra['disponivel_venda'];
        $produto_extra['ativo'] = (bool)$produto_extra['ativo'];
        $produto_extra['valor_formatado'] = 'R$ ' . number_format($produto_extra['valor'], 2, ',', '.');
        $produto_extra['total_produtos'] = (int)$produto_extra['total_produtos'];
    }

    echo json_encode([
        'success' => true,
        'data' => $produtos_extras
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
