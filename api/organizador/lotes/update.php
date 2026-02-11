<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

error_log('📡 API lotes/update.php - Iniciando requisição');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    error_log('❌ API lotes/update.php - Usuário não autorizado');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    // Validar campos obrigatórios
    $campos_obrigatorios = ['lote_id', 'categoria_modalidade', 'idade_min', 'idade_max'];
    
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo json_encode(['success' => false, 'message' => "Campo '$campo' é obrigatório"]);
            exit();
        }
    }
    
    // Preparar dados para atualização
    $lote_id = (int)$_POST['lote_id'];
    $categoria_modalidade = trim($_POST['categoria_modalidade']);
    $idade_min = (int)$_POST['idade_min'];
    $idade_max = (int)$_POST['idade_max'];
    $limite_vagas = isset($_POST['limite_vagas']) && !empty($_POST['limite_vagas']) 
                    ? (int)$_POST['limite_vagas'] : null;
    $desconto_idoso = isset($_POST['desconto_idoso']) ? 1 : 0;
    
    error_log('📋 API lotes/update.php - Atualizando lote ID: ' . $lote_id);
    
    // Verificar se o lote existe e pertence a um evento do organizador
    $stmt = $pdo->prepare("
        SELECT l.id_lote, l.id_modalidade, e.organizador_id 
        FROM lotes l
        INNER JOIN modalidades m ON l.id_modalidade = m.id
        INNER JOIN eventos e ON m.evento_id = e.id
        WHERE l.id_lote = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
    ");
    $stmt->execute([$lote_id, $organizador_id, $usuario_id]);
    $lote = $stmt->fetch();
    
    if (!$lote) {
        error_log('❌ API lotes/update.php - Lote não encontrado ou não autorizado');
        echo json_encode(['success' => false, 'message' => 'Lote não encontrado ou não autorizado']);
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
    
    // Verificar se já existe outro lote com a mesma categoria para esta modalidade
    $stmt = $pdo->prepare("
        SELECT id_lote FROM lotes 
        WHERE id_modalidade = ? AND categoria_modalidade = ? AND id_lote != ?
    ");
    $stmt->execute([$lote['id_modalidade'], $categoria_modalidade, $lote_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Já existe outro lote com esta categoria para esta modalidade']);
        exit();
    }
    
    // Atualizar lote
    $sql = "UPDATE lotes SET 
        categoria_modalidade = ?, 
        idade_min = ?, 
        idade_max = ?, 
        limite_vagas = ?, 
        desconto_idoso = ?
        WHERE id_lote = ?";
    
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([
        $categoria_modalidade, $idade_min, $idade_max,
        $limite_vagas, $desconto_idoso, $lote_id
    ]);
    
    if ($resultado) {
        error_log('✅ API lotes/update.php - Lote atualizado com sucesso');
        
        // Processar preços se fornecidos
        if (isset($_POST['precos']) && !empty($_POST['precos'])) {
            // Remover preços existentes
            $stmt = $pdo->prepare("DELETE FROM lote_precos WHERE id_lote = ?");
            $stmt->execute([$lote_id]);
            
            // Inserir novos preços
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
            'message' => 'Lote atualizado com sucesso'
        ]);
    } else {
        throw new Exception('Erro ao atualizar lote no banco de dados');
    }
    
} catch (Exception $e) {
    error_log('💥 API lotes/update.php - Erro ao atualizar lote: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
