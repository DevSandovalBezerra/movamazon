<?php
header('Content-Type: application/json');
require_once '../../db.php';

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
    $campos_obrigatorios = ['id', 'evento_id', 'categoria_id', 'nome'];
    
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo json_encode(['success' => false, 'message' => "Campo '$campo' é obrigatório"]);
            exit();
        }
    }
    
    $id = (int)$_POST['id'];
    $evento_id = (int)$_POST['evento_id'];
    $categoria_id = (int)$_POST['categoria_id'];
    $nome = trim($_POST['nome']);
    $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $distancia = isset($_POST['distancia']) ? trim($_POST['distancia']) : '';
    $tipo_prova = isset($_POST['tipo_prova']) ? $_POST['tipo_prova'] : 'corrida';
    $limite_vagas = isset($_POST['limite_vagas']) && !empty($_POST['limite_vagas']) ? (int)$_POST['limite_vagas'] : null;
    
    $pdo->beginTransaction();
    
    $sql = "UPDATE modalidades SET evento_id = ?, categoria_id = ?, nome = ?, descricao = ?, distancia = ?, tipo_prova = ?, limite_vagas = ? WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([$evento_id, $categoria_id, $nome, $descricao, $distancia, $tipo_prova, $limite_vagas, $id]);
    
    if ($resultado) {
        $pdo->commit();
      //  error_log("Modalidade atualizada - ID: $id, Nome: $nome, Evento: $evento_id, Categoria: $categoria_id");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Modalidade atualizada com sucesso'
        ]);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar modalidade']);
    }
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro ao atualizar modalidade: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
