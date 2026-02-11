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

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Validar dados obrigatórios
    $produto_extra_id = (int)($_POST['id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $valor = (float)($_POST['valor'] ?? 0);
    $categoria = $_POST['categoria'] ?? 'outros';
    $disponivel_venda = isset($_POST['disponivel_venda']) ? 1 : 0;
    $produtos_raw = $_POST['produtos'] ?? []; // String JSON ou array
    $produtos = is_string($produtos_raw) ? json_decode($produtos_raw, true) : $produtos_raw;

    if ($produto_extra_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID do produto extra é obrigatório']);
        exit();
    }

    if (empty($nome)) {
        echo json_encode(['success' => false, 'error' => 'Nome é obrigatório']);
        exit();
    }

    if ($valor <= 0) {
        echo json_encode(['success' => false, 'error' => 'Valor deve ser maior que zero']);
        exit();
    }

    // Validar produtos apenas se for uma atualização completa
    // Se produtos estiver vazio, manter os produtos existentes
    if (!empty($produtos) && !is_array($produtos)) {
        echo json_encode(['success' => false, 'error' => 'Formato de produtos inválido']);
        exit();
    }

    // Verificar se o produto extra existe e pertence ao organizador
    $stmt = $pdo->prepare("SELECT pe.id, pe.evento_id FROM produtos_extras pe 
                           INNER JOIN eventos e ON pe.evento_id = e.id 
                           WHERE pe.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL");
    $stmt->execute([$produto_extra_id, $organizador_id, $usuario_id]);
    $produto_extra_existente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto_extra_existente) {
        echo json_encode(['success' => false, 'error' => 'Produto extra não encontrado ou não autorizado']);
        exit();
    }

    $evento_id = $produto_extra_existente['evento_id'];

    // Verificar se já existe outro produto extra com o mesmo nome no evento
    // (apenas se o nome foi alterado)
    $stmt = $pdo->prepare("SELECT nome FROM produtos_extras WHERE id = ?");
    $stmt->execute([$produto_extra_id]);
    $nome_atual = $stmt->fetchColumn();

    if ($nome !== $nome_atual) {
        $stmt = $pdo->prepare("SELECT id FROM produtos_extras WHERE nome = ? AND evento_id = ? AND id != ?");
        $stmt->execute([$nome, $evento_id, $produto_extra_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Já existe um produto extra com este nome neste evento']);
            exit();
        }
    }

    // Iniciar transação
    $pdo->beginTransaction();

    try {
        // Atualizar produto extra
        $sql = "UPDATE produtos_extras SET 
                nome = ?, descricao = ?, valor = ?, categoria = ?, 
                disponivel_venda = ?, updated_at = NOW() 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $descricao, $valor, $categoria, $disponivel_venda, $produto_extra_id]);

        // Atualizar produtos relacionados apenas se produtos foram enviados
        if (!empty($produtos)) {
            // Remover produtos relacionados existentes
            $sql = "DELETE FROM produto_extra_produtos WHERE produto_extra_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$produto_extra_id]);

            // Inserir novos produtos relacionados
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
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Produto extra atualizado com sucesso'
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
