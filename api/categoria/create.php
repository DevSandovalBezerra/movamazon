<?php
header('Content-Type: application/json');
require_once '../db.php';
require_once __DIR__ . '/../helpers/organizador_context.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    $campos_obrigatorios = ['nome', 'tipo_publico', 'evento_id'];
    
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo json_encode(['success' => false, 'message' => "Campo '$campo' é obrigatório"]);
            exit();
        }
    }
    
    $nome = trim($_POST['nome']);
    $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $tipo_publico = $_POST['tipo_publico'];
    $idade_min = isset($_POST['idade_min']) && !empty($_POST['idade_min']) ? (int)$_POST['idade_min'] : 0;
    $idade_max = isset($_POST['idade_max']) && !empty($_POST['idade_max']) ? (int)$_POST['idade_max'] : 100;
    $desconto_idoso = isset($_POST['desconto_idoso']) ? 1 : 0;
    $evento_id = (int)$_POST['evento_id'];

    if ($evento_id <= 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Evento inválido']);
        exit();
    }

    $stmtEvento = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmtEvento->execute([$evento_id, $organizador_id, $usuario_id]);
    if (!$stmtEvento->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou não pertence a você']);
        exit();
    }
    
    $sql = "INSERT INTO categorias (evento_id, nome, descricao, tipo_publico, idade_min, idade_max, desconto_idoso) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([$evento_id, $nome, $descricao, $tipo_publico, $idade_min, $idade_max, $desconto_idoso]);
    
    if ($resultado) {
        $categoria_id = $pdo->lastInsertId();
        
        error_log("Categoria criada - ID: $categoria_id, Nome: $nome");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Categoria criada com sucesso',
            'categoria_id' => $categoria_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar categoria']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao criar categoria: " . $e->getMessage());
    if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Nome de categoria já em uso neste evento']);
        exit();
    }
    if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1452) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Evento inválido']);
        exit();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
