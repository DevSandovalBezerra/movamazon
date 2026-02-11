<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

error_log('📡 API lotes/create.php - Iniciando requisição');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    error_log('❌ API lotes/create.php - Usuário não autorizado');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    // Validar campos obrigatórios
    $campos_obrigatorios = ['modalidade_id', 'categoria_modalidade', 'idade_min', 'idade_max'];
    
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo json_encode(['success' => false, 'message' => "Campo '$campo' é obrigatório"]);
            exit();
        }
    }
    
    // Preparar dados para inserção
    $modalidade_id = (int)$_POST['modalidade_id'];
    $categoria_modalidade = trim($_POST['categoria_modalidade']);
    $idade_min = (int)$_POST['idade_min'];
    $idade_max = (int)$_POST['idade_max'];
    $limite_vagas = isset($_POST['limite_vagas']) && !empty($_POST['limite_vagas']) 
                    ? (int)$_POST['limite_vagas'] : null;
    $desconto_idoso = isset($_POST['desconto_idoso']) ? 1 : 0;
    
    error_log('📋 API lotes/create.php - Dados recebidos: Modalidade ID: ' . $modalidade_id . ' - Categoria: ' . $categoria_modalidade);
    
    // Verificar se a modalidade existe e pertence a um evento do organizador
    $stmt = $pdo->prepare("
        SELECT m.id, m.evento_id, e.organizador_id 
        FROM modalidades m 
        INNER JOIN eventos e ON m.evento_id = e.id 
        WHERE m.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
    ");
    $stmt->execute([$modalidade_id, $organizador_id, $usuario_id]);
    $modalidade = $stmt->fetch();
    
    if (!$modalidade) {
        error_log('❌ API lotes/create.php - Modalidade não encontrada ou não autorizada');
        echo json_encode(['success' => false, 'message' => 'Modalidade não encontrada ou não autorizada']);
        exit();
    }
    
    // Validar idades
    if ($idade_min < 0 || $idade_max < 0) {
        echo json_encode(['success' => false, 'message' => 'As idades devem ser maiores ou iguais a zero']);
        exit();
    }
    
    if ($idade_min > $idade_max) {
        echo json_encode(['success' => false, 'message' => 'A idade mínima não pode ser maior que a idade máxima']);
        exit();
    }
    
    // Verificar se já existe um lote com a mesma categoria para esta modalidade
    $stmt = $pdo->prepare("SELECT id_lote FROM lotes WHERE id_modalidade = ? AND categoria_modalidade = ?");
    $stmt->execute([$modalidade_id, $categoria_modalidade]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Já existe um lote com esta categoria para esta modalidade']);
        exit();
    }
    
    // Inserir lote
    $sql = "INSERT INTO lotes (
        id_modalidade, categoria_modalidade, idade_min, idade_max, 
        limite_vagas, desconto_idoso
    ) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([
        $modalidade_id, $categoria_modalidade, $idade_min, $idade_max,
        $limite_vagas, $desconto_idoso
    ]);
    
    if ($resultado) {
        $lote_id = $pdo->lastInsertId();
        error_log('✅ API lotes/create.php - Lote criado com ID: ' . $lote_id);
        
        // Processar preços se fornecidos
        if (isset($_POST['precos']) && !empty($_POST['precos'])) {
            $precos = json_decode($_POST['precos'], true);
            if (is_array($precos)) {
                foreach ($precos as $preco) {
                    if (!empty($preco['preco']) && !empty($preco['data_inicio']) && !empty($preco['data_fim'])) {
                        $stmt = $pdo->prepare("
                            INSERT INTO lote_precos (
                                id_lote, data_inicio_validade, data_fim_validade, 
                                preco, taxa_ticket_sports, desconto_percentual
                            ) VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $lote_id,
                            $preco['data_inicio'],
                            $preco['data_fim'],
                            (float)$preco['preco'],
                            isset($preco['taxa_ticket_sports']) ? (float)$preco['taxa_ticket_sports'] : null,
                            isset($preco['desconto_percentual']) ? (float)$preco['desconto_percentual'] : null
                        ]);
                    }
                }
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Lote criado com sucesso',
            'lote_id' => $lote_id
        ]);
    } else {
        throw new Exception('Erro ao inserir lote no banco de dados');
    }
    
} catch (Exception $e) {
    error_log('💥 API lotes/create.php - Erro ao criar lote: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
