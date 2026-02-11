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
    $campos_obrigatorios = ['evento_id', 'categoria_id', 'nome'];
    
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo json_encode(['success' => false, 'message' => "Campo '$campo' é obrigatório"]);
            exit();
        }
    }
    
    $evento_id = (int)$_POST['evento_id'];
    $categoria_id = (int)$_POST['categoria_id'];
    $nome = trim($_POST['nome']);
    $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $distancia = isset($_POST['distancia']) ? trim($_POST['distancia']) : '';
    $tipo_prova = isset($_POST['tipo_prova']) ? $_POST['tipo_prova'] : 'corrida';
    $limite_vagas = isset($_POST['limite_vagas']) && !empty($_POST['limite_vagas']) ? (int)$_POST['limite_vagas'] : null;
    
    $pdo->beginTransaction();
    
    $sql = "INSERT INTO modalidades (evento_id, categoria_id, nome, descricao, distancia, tipo_prova, limite_vagas) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([$evento_id, $categoria_id, $nome, $descricao, $distancia, $tipo_prova, $limite_vagas]);
    
    if ($resultado) {
        $modalidade_id = $pdo->lastInsertId();
        
        error_log("Modalidade criada - ID: $modalidade_id, Nome: $nome, Evento: $evento_id, Categoria: $categoria_id");
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Modalidade criada com sucesso',
            'modalidade_id' => $modalidade_id
        ]);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar modalidade']);
    }
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro ao criar modalidade: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
