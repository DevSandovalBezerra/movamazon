<?php
error_log("🔍 DEBUG get-produtos-template.php - INICIANDO API");
session_start();
require_once '../../db.php';
error_log("🔍 DEBUG get-produtos-template.php - Sessão iniciada");

// Verificar se o usuário está logado como organizador
//error_log("🔍 DEBUG get-produtos-template.php - Verificando autenticação");
//error_log("🔍 DEBUG get-produtos-template.php - SESSION user_id: " . ($_SESSION['user_id'] ?? 'NÃO DEFINIDO'));
//error_log("🔍 DEBUG get-produtos-template.php - SESSION papel: " . ($_SESSION['papel'] ?? 'NÃO DEFINIDO'));

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    error_log("🔍 DEBUG get-produtos-template.php - AUTENTICAÇÃO FALHOU");
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}

//error_log("🔍 DEBUG get-produtos-template.php - Autenticação OK");

// Verificar se o ID do template foi fornecido
//error_log("🔍 DEBUG get-produtos-template.php - Verificando parâmetros");
//error_log("🔍 DEBUG get-produtos-template.php - GET id: " . ($_GET['id'] ?? 'NÃO DEFINIDO'));

if (!isset($_GET['id']) || empty($_GET['id'])) {
    error_log("🔍 DEBUG get-produtos-template.php - ID DO TEMPLATE NÃO FORNECIDO");
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID do template não fornecido']);
    exit();
}

$template_id = intval($_GET['id']);
error_log("🔍 DEBUG get-produtos-template.php - Template ID convertido: $template_id");

try {
    /* error_log("🔍 DEBUG get-produtos-template.php - Template ID: $template_id");
    error_log("🔍 DEBUG get-produtos-template.php - Sessão ID: " . session_id());
    error_log("🔍 DEBUG get-produtos-template.php - Todos os dados da sessão: " . print_r($_SESSION, true)); */
    
    // Query para buscar produtos salvos do template
   /*  $sql = "SELECT 
                ktp.produto_id,
                ktp.quantidade,
                ktp.ordem,
                p.nome as produto_nome,
                p.preco as produto_preco
            FROM kit_template_produtos ktp
            INNER JOIN produtos p ON p.id = ktp.produto_id
            WHERE ktp.kit_template_id = ?
            ORDER BY ktp.ordem ASC"; */
    
            $sql = "SELECT 
            ktp.produto_id,
            ktp.quantidade,
            ktp.ordem,
            p.nome as produto_nome,
            p.preco as produto_preco,
            p.tipo as produto_tipo
        FROM kit_template_produtos ktp
        INNER JOIN produtos p ON p.id = ktp.produto_id
        WHERE ktp.kit_template_id = ?
        ORDER BY ktp.ordem ASC";


    error_log("🔍 DEBUG get-produtos-template.php - Query: $sql");
    
    // Verificar se existem dados na tabela kit_template_produtos
    $sql_check = "SELECT COUNT(*) as total FROM kit_template_produtos WHERE kit_template_id = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$template_id]);
    $total_check = $stmt_check->fetch(PDO::FETCH_ASSOC);
    error_log("🔍 DEBUG get-produtos-template.php - Total de produtos na tabela para template $template_id: " . $total_check['total']);
    
    // Verificar alguns registros da tabela
    $sql_sample = "SELECT * FROM kit_template_produtos WHERE kit_template_id = ? LIMIT 3";
    $stmt_sample = $pdo->prepare($sql_sample);
    $stmt_sample->execute([$template_id]);
    $samples = $stmt_sample->fetchAll(PDO::FETCH_ASSOC);
  //  error_log("🔍 DEBUG get-produtos-template.php - Amostras de dados: " . json_encode($samples));
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$template_id]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
  //  error_log("🔍 DEBUG get-produtos-template.php - Produtos encontrados: " . count($produtos));
    if (count($produtos) > 0) {
        error_log("🔍 DEBUG get-produtos-template.php - Primeiro produto: " . json_encode($produtos[0]));
    }
    
    echo json_encode([
        'success' => true,
        'data' => $produtos
    ]);
    
} catch (Exception $e) {
    error_log("🔍 DEBUG get-produtos-template.php - Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?> 
