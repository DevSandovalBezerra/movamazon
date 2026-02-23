<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/file_utils.php';
require_once __DIR__ . '/../../helpers/kit_template_sync.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}

//  error_log("🔍 DEBUG update.php - Método recebido: " . $_SERVER['REQUEST_METHOD']);
// error_log("🔍 DEBUG update.php - POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit();
}

try {
    $organizador_id = $_SESSION['user_id'];

    // Validar dados obrigatórios
    $template_id = intval($_POST['id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco_base = (float)($_POST['preco_base'] ?? 0);
    $disponivel_venda = isset($_POST['disponivel_venda']) ? 1 : 0;

    if (empty($template_id) || empty($nome) || $preco_base <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID, nome e preço base são obrigatórios']);
        exit();
    }

    // Verificar se o template existe
    // error_log("🔍 DEBUG update.php - Verificando template ID: $template_id");

    $sql = "SELECT id, foto_kit FROM kit_templates WHERE id = ? AND ativo = 1";
    // error_log("🔍 DEBUG update.php - Query: $sql");

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$template_id]);
    $template = $stmt->fetch();

    // error_log("🔍 DEBUG update.php - Template encontrado: " . ($template ? 'SIM' : 'NÃO'));

    if (!$template) {
        echo json_encode(['success' => false, 'error' => 'Template não encontrado']);
        exit();
    }

    // Processar upload de foto
    $foto_kit = null;
    $upload_dir = '../../../frontend/assets/img/kits/';

    if (isset($_FILES['foto_kit']) && $_FILES['foto_kit']['error'] === UPLOAD_ERR_OK) {
        $foto_kit = salvarFotoKitTemplate($template_id, $_FILES['foto_kit'], $upload_dir, $template['foto_kit'] ?? null);
    }

    // Iniciar transação
    $pdo->beginTransaction();

    // Atualizar template
    if ($foto_kit) {
        $sql = "UPDATE kit_templates SET 
                nome = ?, descricao = ?, preco_base = ?, foto_kit = ?, 
                disponivel_venda = ?, updated_at = NOW() 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $descricao, $preco_base, $foto_kit, $disponivel_venda, $template_id]);
    } else {
        $sql = "UPDATE kit_templates SET 
                nome = ?, descricao = ?, preco_base = ?, 
                disponivel_venda = ?, updated_at = NOW() 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $descricao, $preco_base, $disponivel_venda, $template_id]);
    }

    // Processar produtos do template
    $produtos_data = json_decode($_POST['produtos'] ?? '[]', true);
    // error_log("🔍 DEBUG update.php - Produtos recebidos: " . json_encode($produtos_data));

    // Só remover produtos se novos produtos forem fornecidos
    if (!empty($produtos_data)) {
        // error_log("🔍 DEBUG update.php - Produtos fornecidos, atualizando lista");

        // Remover produtos existentes
        $sql = "DELETE FROM kit_template_produtos WHERE kit_template_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$template_id]);
        error_log("🔍 DEBUG update.php - Produtos removidos para template $template_id");

        // Inserir novos produtos
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
                error_log("🔍 DEBUG update.php - Produto inserido: " . json_encode($produto));
            } else {
                error_log("🔍 DEBUG update.php - Produto inválido ignorado: " . json_encode($produto));
            }
        }
    } else {
        error_log("🔍 DEBUG update.php - Nenhum produto fornecido, mantendo produtos existentes");
    }

    // Commit da transação
    $pdo->commit();

    // =====================================================
    // SINCRONIZAÇÃO AUTOMÁTICA: kits_eventos vinculados
    // Atualiza FOTO + PRODUTOS conforme template, preservando campos manuais do kit
    // =====================================================
    try {
        $sync = syncKitsEventosFromTemplate($pdo, $template_id, $upload_dir);
        error_log('✅ kits-templates/update.php - Sync kits_eventos: ' . json_encode($sync));
    } catch (Exception $e) {
        // Não derrubar o update do template se a sync falhar
        error_log('🚨 kits-templates/update.php - Falha ao sincronizar kits_eventos: ' . $e->getMessage());
    }

    // Buscar template atualizado
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
        'message' => 'Template atualizado com sucesso!',
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
