<?php
header('Content-Type: application/json');

session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';
require_once __DIR__ . '/../../helpers/file_utils.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || ($_SESSION['papel'] ?? '') !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $kit_id = (int)($input['kit_id'] ?? 0);
    $template_id = (int)($input['template_id'] ?? 0);

    if ($kit_id <= 0 || $template_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Kit e template são obrigatórios']);
        exit();
    }

    // Verificar se o kit existe e pertence ao organizador
    $stmt = $pdo->prepare("
        SELECT k.id, k.evento_id, k.foto_kit
        FROM kits_eventos k
        INNER JOIN eventos e ON k.evento_id = e.id
        WHERE k.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
        LIMIT 1
    ");
    $stmt->execute([$kit_id, $organizador_id, $usuario_id]);
    $kit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kit) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Kit não encontrado ou não autorizado']);
        exit();
    }

    $evento_id = (int)($kit['evento_id'] ?? 0);
    $old_foto = $kit['foto_kit'] ?? null;

    // Buscar template
    $stmt = $pdo->prepare("
        SELECT id, nome, preco_base, foto_kit, disponivel_venda, ativo
        FROM kit_templates
        WHERE id = ? AND ativo = 1
        LIMIT 1
    ");
    $stmt->execute([$template_id]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$template) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Template não encontrado']);
        exit();
    }

    // Produtos do template (pode ser vazio: nesse caso o kit ficará sem produtos)
    $stmt = $pdo->prepare("
        SELECT produto_id, quantidade, ordem
        FROM kit_template_produtos
        WHERE kit_template_id = ? AND ativo = 1
        ORDER BY ordem ASC, id ASC
    ");
    $stmt->execute([$template_id]);
    $template_produtos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $upload_dir = '../../../frontend/assets/img/kits/';
    $copied = false;
    $new_filename = null;

    // Preparar cópia da foto (se existir)
    $template_foto = $template['foto_kit'] ?? null;
    if (!empty($template_foto)) {
        $source_path = $upload_dir . $template_foto;
        if (is_file($source_path)) {
            $ext = strtolower(pathinfo($template_foto, PATHINFO_EXTENSION) ?: 'png');
            $new_filename = gerarNomeKit('evento', $kit_id, $evento_id, $ext);
            $target_path = $upload_dir . $new_filename;
            if (@copy($source_path, $target_path)) {
                $copied = true;
            }
        }
    }

    $pdo->beginTransaction();

    // Atualizar vínculo + campos derivados do template
    $valor = (float)($template['preco_base'] ?? 0);
    $disponivel_venda = !empty($template['disponivel_venda']) ? 1 : 0;

    $stmt = $pdo->prepare("
        UPDATE kits_eventos
        SET kit_template_id = ?, valor = ?, preco_calculado = ?, disponivel_venda = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$template_id, $valor, $valor, $disponivel_venda, $kit_id]);

    // Substituir produtos do kit pelo template
    $pdo->prepare("DELETE FROM kit_produtos WHERE kit_id = ?")->execute([$kit_id]);

    if (!empty($template_produtos)) {
        $stmtIns = $pdo->prepare("
            INSERT INTO kit_produtos (kit_id, produto_id, quantidade, ordem, ativo)
            VALUES (?, ?, ?, ?, 1)
        ");

        foreach ($template_produtos as $p) {
            $produto_id = (int)($p['produto_id'] ?? 0);
            $quantidade = (int)($p['quantidade'] ?? 1);
            $ordem = (int)($p['ordem'] ?? 1);
            if ($produto_id > 0 && $quantidade > 0) {
                $stmtIns->execute([$kit_id, $produto_id, $quantidade, $ordem]);
            }
        }
    }

    // Atualizar foto se copiou com sucesso
    if ($copied && !empty($new_filename)) {
        $stmt = $pdo->prepare("UPDATE kits_eventos SET foto_kit = ? WHERE id = ?");
        $stmt->execute([$new_filename, $kit_id]);
    }

    $pdo->commit();

    // Limpar foto antiga (após commit) se for diferente
    if ($copied && !empty($new_filename) && !empty($old_foto) && $old_foto !== $new_filename) {
        $old_path = $upload_dir . $old_foto;
        if (is_file($old_path)) {
            @unlink($old_path);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Template reaplicado no kit com sucesso.',
        'data' => [
            'kit_id' => $kit_id,
            'evento_id' => $evento_id,
            'template_id' => $template_id,
            'template_nome' => $template['nome'] ?? null,
            'produtos_aplicados' => count($template_produtos),
            'foto_aplicada' => (bool)$copied,
            'valor' => $valor,
            'disponivel_venda' => (bool)$disponivel_venda
        ]
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Se copiamos foto mas falhou no banco, tentar limpar o arquivo novo para evitar órfãos
    if (!empty($copied) && !empty($new_filename)) {
        $upload_dir = '../../../frontend/assets/img/kits/';
        $new_path = $upload_dir . $new_filename;
        if (is_file($new_path)) {
            @unlink($new_path);
        }
    }

    error_log('Erro reaplicar-template.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor']);
}

