<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

//error_log('📖 API get.php - Iniciando requisição de busca');

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    error_log('❌ API get.php - Usuário não autorizado: ' . ($_SESSION['user_id'] ?? 'não definido') . ' - Papel: ' . ($_SESSION['papel'] ?? 'não definido'));
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];
$modalidade_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

//error_log('📋 API get.php - Organizador ID: ' . $organizador_id . ' - Modalidade ID: ' . $modalidade_id);

if (!$modalidade_id) {
    //error_log('❌ API get.php - ID da modalidade não fornecido');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da modalidade é obrigatório']);
    exit();
}

try {
    //error_log('🔍 API get.php - Buscando modalidade no banco de dados');
    
    $stmt = $pdo->prepare("
        SELECT 
            m.*,
            c.nome as categoria_nome,
            c.tipo_publico,
            c.idade_min,
            c.idade_max,
            c.desconto_idoso,
            e.organizador_id
        FROM modalidades m
        INNER JOIN categorias c ON m.categoria_id = c.id
        INNER JOIN eventos e ON m.evento_id = e.id
        WHERE m.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
    ");
    $stmt->execute([$modalidade_id, $organizador_id, $usuario_id]);
    $modalidade = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$modalidade) {
        error_log('❌ API get.php - Modalidade não encontrada: Modalidade ID ' . $modalidade_id . ' - Organizador ID ' . $organizador_id);
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Modalidade não encontrada']);
        exit();
    }
    
    //error_log('✅ API get.php - Modalidade encontrada, buscando dados relacionados');

    $modalidadeFormatada = [
        'id' => $modalidade['id'],
        'nome' => $modalidade['nome'],
        'descricao' => $modalidade['descricao'],
        'distancia' => $modalidade['distancia'],
        'tipo_prova' => $modalidade['tipo_prova'],
        'limite_vagas' => $modalidade['limite_vagas'],
        'categoria' => [
            'id' => $modalidade['categoria_id'],
            'nome' => $modalidade['categoria_nome'],
            'tipo_publico' => $modalidade['tipo_publico'],
            'idade_min' => $modalidade['idade_min'],
            'idade_max' => $modalidade['idade_max'],
            'desconto_idoso' => $modalidade['desconto_idoso']
        ],
        'status' => $modalidade['ativo'] ? 'ativo' : 'inativo'
    ];

    //error_log('✅ API get.php - Modalidade formatada com sucesso');
    
    echo json_encode([
        'success' => true,
        'modalidade' => $modalidadeFormatada
    ]);

} catch (Exception $e) {
    error_log('💥 API get.php - Erro ao buscar modalidade: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
