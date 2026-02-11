<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/file_utils.php';

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
    $organizador_id = $_SESSION['user_id'];

    // Validar dados obrigatórios
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco_base = (float)($_POST['preco_base'] ?? 0);
    $disponivel_venda = isset($_POST['disponivel_venda']) ? 1 : 0;

    if (empty($nome) || $preco_base <= 0) {
        echo json_encode(['success' => false, 'error' => 'Nome e preço base são obrigatórios']);
        exit();
    }

    // Preparar upload (sem mover ainda)
    $foto_kit_tmp = null;
    $foto_kit_extension = null;

    if (isset($_FILES['foto_kit']) && $_FILES['foto_kit']['error'] === UPLOAD_ERR_OK) {
        $file_info = pathinfo($_FILES['foto_kit']['name']);
        $extension = strtolower($file_info['extension']);

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'error' => 'Formato de imagem não suportado. Use: JPG, PNG ou WEBP']);
            exit();
        }

        $foto_kit_tmp = $_FILES['foto_kit']['tmp_name'];
        $foto_kit_extension = $extension;
    }

    // Iniciar transação
    $pdo->beginTransaction();

    // Inserir template
    $sql = "INSERT INTO kit_templates (nome, descricao, preco_base, foto_kit, disponivel_venda, ativo, data_criacao) 
            VALUES (?, ?, ?, NULL, ?, 1, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $descricao, $preco_base, $disponivel_venda]);

    $template_id = $pdo->lastInsertId();

    // Processar produtos do template
    $produtos_data = json_decode($_POST['produtos'] ?? '[]', true);

    if (!empty($produtos_data)) {
        $sql = "INSERT INTO kit_template_produtos (kit_template_id, produto_id, quantidade, ordem, ativo, data_criacao) 
                VALUES (?, ?, ?, ?, 1, NOW())";

        $stmt = $pdo->prepare($sql);

        foreach ($produtos_data as $produto) {
            if (!empty($produto['produto_id']) && !empty($produto['quantidade'])) {
                $stmt->execute([
                    $template_id,
                    $produto['produto_id'],
                    $produto['quantidade'],
                    $produto['ordem'] ?? 1
                ]);
            }
        }
    }

    if ($foto_kit_tmp && $foto_kit_extension) {
        $upload_dir = '../../../frontend/assets/img/kits/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $filename = gerarNomeKit('template', $template_id, null, $foto_kit_extension);
        $filepath = $upload_dir . $filename;

        if (!move_uploaded_file($foto_kit_tmp, $filepath)) {
            throw new Exception('Erro ao fazer upload da imagem');
        }

        $stmt = $pdo->prepare("UPDATE kit_templates SET foto_kit = ? WHERE id = ?");
        $stmt->execute([$filename, $template_id]);
    }

    // Commit da transação
    $pdo->commit();

    // Buscar template criado
    $sql = "SELECT 
                kt.id,
                kt.nome,
                kt.descricao,
                kt.preco_base,
                kt.foto_kit,
                kt.disponivel_venda,
                kt.ativo,
                kt.data_criacao,
                kt.updated_at,
                COUNT(ktp.id) as total_produtos
            FROM kit_templates kt
            LEFT JOIN kit_template_produtos ktp ON kt.id = ktp.kit_template_id AND ktp.ativo = 1
            WHERE kt.id = ?
            GROUP BY kt.id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$template_id]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    // Formatar dados
    $template['data_criacao_formatada'] = date('d/m/Y H:i', strtotime($template['data_criacao']));
    $template['updated_at_formatada'] = $template['updated_at'] ? date('d/m/Y H:i', strtotime($template['updated_at'])) : null;
    $template['disponivel_venda'] = (bool)$template['disponivel_venda'];
    $template['ativo'] = (bool)$template['ativo'];
    $template['total_produtos'] = (int)$template['total_produtos'];
    $template['preco_base_formatado'] = 'R$ ' . number_format($template['preco_base'], 2, ',', '.');

    echo json_encode([
        'success' => true,
        'message' => 'Template criado com sucesso!',
        'data' => $template
    ]);
} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
