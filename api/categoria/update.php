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

    $campos_obrigatorios = ['id', 'nome', 'tipo_publico'];
    
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo json_encode(['success' => false, 'message' => "Campo '$campo' é obrigatório"]);
            exit();
        }
    }
    
    $id = (int)$_POST['id'];
    $nome = trim($_POST['nome']);
    $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $tipo_publico = $_POST['tipo_publico'];
    $idade_min = isset($_POST['idade_min']) && !empty($_POST['idade_min']) ? (int)$_POST['idade_min'] : 0;
    $idade_max = isset($_POST['idade_max']) && !empty($_POST['idade_max']) ? (int)$_POST['idade_max'] : 100;
    $desconto_idoso = isset($_POST['desconto_idoso']) ? 1 : 0;
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    $sql = "UPDATE categorias c
            INNER JOIN eventos e ON e.id = c.evento_id
            SET c.nome = ?, c.descricao = ?, c.tipo_publico = ?, c.idade_min = ?, c.idade_max = ?, c.desconto_idoso = ?, c.ativo = ?
            WHERE c.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL";
    
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([$nome, $descricao, $tipo_publico, $idade_min, $idade_max, $desconto_idoso, $ativo, $id, $organizador_id, $usuario_id]);
    
    if ($resultado && $stmt->rowCount() > 0) {
        error_log("Categoria atualizada - ID: $id, Nome: $nome");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Categoria atualizada com sucesso'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Categoria não encontrada ou não pertence ao seu evento']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao atualizar categoria: " . $e->getMessage());
    if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Nome de categoria já em uso neste evento']);
        exit();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
