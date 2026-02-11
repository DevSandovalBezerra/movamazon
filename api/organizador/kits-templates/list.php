<?php
header('Content-Type: application/json');
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

    // Parâmetros de filtro
    $evento_id = $_GET['evento_id'] ?? null;

    // Query base - mostrar todos os templates globais
    $sql = "SELECT 
                kt.id,
                kt.nome,
                kt.descricao,
                kt.preco_base,
                kt.foto_kit,
                kt.disponivel_venda,
                kt.ativo,
                kt.data_criacao,
                kt.updated_at,
                COALESCE(produtos_count.total_produtos, 0) as total_produtos,
                COUNT(DISTINCT ke.evento_id) as total_eventos_ativos
            FROM kit_templates kt
            LEFT JOIN (
                SELECT 
                    kit_template_id,
                    COUNT(*) as total_produtos
                FROM kit_template_produtos 
                WHERE ativo = 1
                GROUP BY kit_template_id
            ) produtos_count ON kt.id = produtos_count.kit_template_id
            LEFT JOIN kits_eventos ke ON kt.id = ke.kit_template_id AND ke.ativo = 1
            WHERE kt.ativo = 1
            GROUP BY kt.id
            ORDER BY kt.nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log para debug

    foreach ($templates as $template) {
        //error_log("🔍 DEBUG kits-templates/list.php - Template {$template['id']}: {$template['nome']} - {$template['total_produtos']} produtos");
    }

    // Formatar dados
    foreach ($templates as &$template) {
        $template['data_criacao_formatada'] = date('d/m/Y H:i', strtotime($template['data_criacao']));
        $template['updated_at_formatada'] = $template['updated_at'] ? date('d/m/Y H:i', strtotime($template['updated_at'])) : null;
        $template['disponivel_venda'] = (bool)$template['disponivel_venda'];
        $template['ativo'] = (bool)$template['ativo'];
        $template['total_produtos'] = (int)$template['total_produtos'];
        $template['total_eventos_ativos'] = (int)$template['total_eventos_ativos'];
        $template['preco_base_formatado'] = 'R$ ' . number_format($template['preco_base'], 2, ',', '.');
    }

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
