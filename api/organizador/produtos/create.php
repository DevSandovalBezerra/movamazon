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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $organizador_id = $ctx['organizador_id'];

    // Validar dados obrigatórios
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = isset($_POST['preco']) ? (float)$_POST['preco'] : 0.00;
    $disponivel_venda = isset($_POST['disponivel_venda']) ? 1 : 0;

    if (empty($nome)) {
        echo json_encode(['success' => false, 'error' => 'Nome é obrigatório']);
        exit();
    }

    // Validar preço
    if ($preco < 0) {
        echo json_encode(['success' => false, 'error' => 'Preço deve ser maior ou igual a zero']);
        exit();
    }

    // Processar upload de foto
    $foto_produto = null;
    if (isset($_FILES['foto_produto']) && $_FILES['foto_produto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../../frontend/assets/img/produtos/';

        // Criar diretório se não existir
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_info = pathinfo($_FILES['foto_produto']['name']);
        $extension = strtolower($file_info['extension']);

        // Validar extensão
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'error' => 'Formato de imagem não suportado. Use: JPG, PNG ou WEBP']);
            exit();
        }

        // Gerar nome único
        $filename = 'produto_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $upload_dir . $filename;

        // Mover arquivo
        if (move_uploaded_file($_FILES['foto_produto']['tmp_name'], $filepath)) {
            $foto_produto = 'frontend/assets/img/produtos/' . $filename;
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao fazer upload da imagem']);
            exit();
        }
    }

    // Inserir produto
    $sql = "INSERT INTO produtos (nome, descricao, preco, disponivel_venda, foto_produto, ativo, data_criacao) 
            VALUES (?, ?, ?, ?, ?, 1, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $descricao, $preco, $disponivel_venda, $foto_produto]);

    $produto_id = $pdo->lastInsertId();

    // Buscar produto criado
    $sql = "SELECT 
                p.id,
                p.nome,
                p.descricao,
                p.preco,
                p.disponivel_venda,
                p.foto_produto,
                p.ativo,
                p.data_criacao,
                p.updated_at
            FROM produtos p
            WHERE p.id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    // Formatar dados
    $produto['data_criacao_formatada'] = date('d/m/Y H:i', strtotime($produto['data_criacao']));
    $produto['updated_at_formatada'] = $produto['updated_at'] ? date('d/m/Y H:i', strtotime($produto['updated_at'])) : null;
    $produto['disponivel_venda'] = (bool)$produto['disponivel_venda'];
    $produto['ativo'] = (bool)$produto['ativo'];

    // Retornar caminho relativo (sem o prefixo frontend/)
    if ($produto['foto_produto']) {
        $produto['foto_url'] = str_replace('frontend/', '', $produto['foto_produto']);
    } else {
        $produto['foto_url'] = null;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Produto criado com sucesso!',
        'data' => $produto
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
