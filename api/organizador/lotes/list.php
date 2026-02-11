<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

error_log('📡 API lotes/list.php - Iniciando requisição');

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    error_log('❌ API lotes/list.php - Usuário não autorizado');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];
$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
$modalidade_id = isset($_GET['modalidade_id']) ? (int)$_GET['modalidade_id'] : 0;

error_log('📋 API lotes/list.php - Organizador ID: ' . $organizador_id . ' - Evento ID: ' . $evento_id);

if (!$evento_id) {
    error_log('❌ API lotes/list.php - ID do evento não fornecido');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
    exit();
}

try {
    error_log('🔍 API lotes/list.php - Verificando se evento pertence ao organizador');
    
    // Verificar se o evento pertence ao organizador
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    $evento = $stmt->fetch();
    
    if (!$evento) {
        error_log('❌ API lotes/list.php - Evento não encontrado ou não autorizado');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou não autorizado']);
        exit();
    }
    
    error_log('✅ API lotes/list.php - Evento autorizado, buscando lotes');

    // Contar lotes do evento específico
    $stmtCount = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM lotes l
        INNER JOIN modalidades m ON l.id_modalidade = m.id
        INNER JOIN eventos e ON m.evento_id = e.id
        WHERE e.id = ? AND (? = 0 OR l.id_modalidade = ?)
    ");
    $stmtCount->execute([$evento_id, $modalidade_id, $modalidade_id]);
    $totalLotes = $stmtCount->fetch()['total'];
    
    error_log('📊 API lotes/list.php - Total de lotes do evento ' . $evento_id . ': ' . $totalLotes);

    // Buscar lotes do evento específico com informações das modalidades
    $query = "
        SELECT 
            l.id_lote,
            l.id_modalidade,
            l.categoria_modalidade,
            l.idade_min,
            l.idade_max,
            l.limite_vagas,
            l.desconto_idoso,
            COALESCE(m.nome, 'Modalidade não encontrada') as nome_modalidade,
            COALESCE(c.nome, 'Categoria não encontrada') as nome_categoria,
            COUNT(lp.id_lote_preco) as total_precos,
            MIN(lp.preco) as preco_minimo,
            MAX(lp.preco) as preco_maximo
        FROM lotes l
        INNER JOIN modalidades m ON l.id_modalidade = m.id
        INNER JOIN eventos e ON m.evento_id = e.id
        LEFT JOIN lote_precos lp ON l.id_lote = lp.id_lote
        WHERE e.id = ? AND (? = 0 OR l.id_modalidade = ?)
        GROUP BY l.id_lote, l.id_modalidade, l.categoria_modalidade, l.idade_min, l.idade_max, l.limite_vagas, l.desconto_idoso, m.nome_modalidade, m.nome_categoria
        ORDER BY l.id_lote ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$evento_id, $modalidade_id, $modalidade_id]);
    $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log('📊 API lotes/list.php - Lotes encontrados: ' . count($lotes));

    // Formatar dados para exibição
    $lotesFormatados = [];
    foreach ($lotes as $lote) {
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
        $stmtPrecos->execute([$lote['id_lote']]);
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
        
        $lotesFormatados[] = [
            'id' => $lote['id_lote'],
            'modalidade_id' => $lote['id_modalidade'],
            'modalidade_nome' => $lote['nome_modalidade'],
            'categoria_modalidade' => $lote['nome_categoria'],
            'categoria_lote' => $lote['categoria_modalidade'],
            'idade_min' => (int)$lote['idade_min'],
            'idade_max' => (int)$lote['idade_max'],
            'limite_vagas' => $lote['limite_vagas'] ? (int)$lote['limite_vagas'] : null,
            'desconto_idoso' => (bool)$lote['desconto_idoso'],
            'total_precos' => (int)$lote['total_precos'],
            'preco_minimo' => $lote['preco_minimo'] ? number_format($lote['preco_minimo'], 2, ',', '.') : '0,00',
            'preco_maximo' => $lote['preco_maximo'] ? number_format($lote['preco_maximo'], 2, ',', '.') : '0,00',
            'precos' => $precosFormatados
        ];
    }

    error_log('✅ API lotes/list.php - Retornando ' . count($lotesFormatados) . ' lotes formatados');
    
    echo json_encode([
        'success' => true,
        'lotes' => $lotesFormatados,
        'total_lotes' => $totalLotes,
        'lotes_encontrados' => count($lotesFormatados)
    ]);

} catch (Exception $e) {
    error_log('💥 API lotes/list.php - Erro ao listar lotes: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
