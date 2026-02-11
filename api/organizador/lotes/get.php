<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

error_log('📡 API lotes/get.php - Iniciando requisição');

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    error_log('❌ API lotes/get.php - Usuário não autorizado');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];
$lote_id = isset($_GET['lote_id']) ? (int)$_GET['lote_id'] : 0;

error_log('📋 API lotes/get.php - Organizador ID: ' . $organizador_id . ' - Lote ID: ' . $lote_id);

if (!$lote_id) {
    error_log('❌ API lotes/get.php - ID do lote não fornecido');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do lote é obrigatório']);
    exit();
}

try {
    error_log('🔍 API lotes/get.php - Verificando se lote pertence ao organizador');
    
    // Verificar se o lote existe e pertence a um evento do organizador
    $stmt = $pdo->prepare("
        SELECT 
            l.id_lote,
            l.id_modalidade,
            l.categoria_modalidade,
            l.idade_min,
            l.idade_max,
            l.limite_vagas,
            l.desconto_idoso,
            m.nome as nome_modalidade,
            c.nome as nome_categoria,
            e.id as evento_id,
            e.nome as evento_nome
        FROM lotes l
        INNER JOIN modalidades m ON l.id_modalidade = m.id
        INNER JOIN categorias c ON m.categoria_id = c.id
        INNER JOIN eventos e ON m.evento_id = e.id
        WHERE l.id_lote = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
    ");
    $stmt->execute([$lote_id, $organizador_id, $usuario_id]);
    $lote = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$lote) {
        error_log('❌ API lotes/get.php - Lote não encontrado ou não autorizado');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Lote não encontrado ou não autorizado']);
        exit();
    }
    
    error_log('✅ API lotes/get.php - Lote autorizado, buscando preços');

    // Buscar preços do lote
    $stmtPrecos = $pdo->prepare("
        SELECT 
            id_lote_preco,
            data_inicio_validade,
            data_fim_validade,
            preco,
            taxa_ticket_sports,
            desconto_percentual
        FROM lote_precos 
        WHERE id_lote = ?
        ORDER BY data_inicio_validade ASC
    ");
    $stmtPrecos->execute([$lote_id]);
    $precos = $stmtPrecos->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar preços
    $precosFormatados = [];
    foreach ($precos as $preco) {
        $precosFormatados[] = [
            'id' => $preco['id_lote_preco'],
            'data_inicio' => $preco['data_inicio_validade'],
            'data_fim' => $preco['data_fim_validade'],
            'preco' => number_format($preco['preco'], 2, ',', '.'),
            'taxa_ticket_sports' => $preco['taxa_ticket_sports'] ? number_format($preco['taxa_ticket_sports'], 2, ',', '.') : null,
            'desconto_percentual' => $preco['desconto_percentual'] ? number_format($preco['desconto_percentual'], 1) . '%' : null
        ];
    }
    
    // Formatar dados do lote
    $loteFormatado = [
        'id' => $lote['id_lote'],
        'modalidade_id' => $lote['id_modalidade'],
        'modalidade_nome' => $lote['nome_modalidade'],
        'categoria_modalidade' => $lote['nome_categoria'],
        'categoria_lote' => $lote['categoria_modalidade'],
        'idade_min' => (int)$lote['idade_min'],
        'idade_max' => (int)$lote['idade_max'],
        'limite_vagas' => $lote['limite_vagas'] ? (int)$lote['limite_vagas'] : null,
        'desconto_idoso' => (bool)$lote['desconto_idoso'],
        'evento_id' => $lote['evento_id'],
        'evento_nome' => $lote['evento_nome'],
        'precos' => $precosFormatados
    ];

    error_log('✅ API lotes/get.php - Retornando dados do lote');
    
    echo json_encode([
        'success' => true,
        'lote' => $loteFormatado
    ]);

} catch (Exception $e) {
    error_log('💥 API lotes/get.php - Erro ao buscar lote: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
