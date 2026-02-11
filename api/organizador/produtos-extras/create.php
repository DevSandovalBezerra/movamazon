<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}
$data = json_decode(file_get_contents('php://input'), true);
error_log("DEBUG create.php - Data recebida: " . print_r($data, true));
try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Validar dados obrigatórios
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $evento_id = (int)($_POST['evento_id'] ?? 0);
    $valor = (float)($_POST['valor'] ?? 0);
    $categoria = $_POST['categoria'] ?? 'outros';
    $disponivel_venda = isset($_POST['disponivel_venda']) ? 1 : 0;
    $produtos_raw = $_POST['produtos'] ?? []; // String JSON ou array
    $produtos = is_string($produtos_raw) ? json_decode($produtos_raw, true) : $produtos_raw;
    error_log("DEBUG create.php - PRODUTOS RAW: " . print_r($produtos_raw, true));
    error_log("DEBUG create.php - PRODUTOS DECODED: " . print_r($produtos, true));

    if (empty($nome)) {
        echo json_encode(['success' => false, 'error' => 'Nome é obrigatório']);
        exit();
    }

    if ($evento_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Evento é obrigatório']);
        exit();
    }

    if ($valor <= 0) {
        echo json_encode(['success' => false, 'error' => 'Valor deve ser maior que zero']);
        exit();
    }

    if (empty($produtos) || !is_array($produtos)) {
        echo json_encode(['success' => false, 'error' => 'Selecione pelo menos um produto']);
        exit();
    }

    // Verificar se o evento pertence ao organizador
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Evento não encontrado ou não autorizado']);
        exit();
    }

    // Verificar se já existe produto extra com o mesmo nome no evento
    $stmt = $pdo->prepare("SELECT id FROM produtos_extras WHERE nome = ? AND evento_id = ?");
    $stmt->execute([$nome, $evento_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Já existe um produto extra com este nome neste evento']);
        exit();
    }

    // Iniciar transação
    $pdo->beginTransaction();

    try {
        // Inserir produto extra
        $sql = "INSERT INTO produtos_extras (nome, descricao, evento_id, valor, categoria, disponivel_venda, ativo, data_criacao) 
                VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $descricao, $evento_id, $valor, $categoria, $disponivel_venda]);

        $produto_extra_id = $pdo->lastInsertId();

        // Inserir produtos relacionados
        foreach ($produtos as $produto_data) {
            $produto_id = (int)$produto_data['id']; // Usar 'id' em vez de 'produto_id'
            $quantidade = 1; // Quantidade fixa em 1 por enquanto

            if ($produto_id > 0 && $quantidade > 0) {
                // Verificar se o produto existe e está ativo
                $stmt = $pdo->prepare("SELECT id FROM produtos WHERE id = ? AND ativo = 1");
                $stmt->execute([$produto_id]);
                if ($stmt->fetch()) {
                    $sql = "INSERT INTO produto_extra_produtos (produto_extra_id, produto_id, quantidade, ativo, data_criacao) 
                            VALUES (?, ?, ?, 1, NOW())";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$produto_extra_id, $produto_id, $quantidade]);
                }
            }
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Produto extra criado com sucesso',
            'data' => ['id' => $produto_extra_id]
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
