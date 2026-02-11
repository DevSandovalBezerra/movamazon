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
    $produto_extra_id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

    error_log("🔍 DEBUG delete.php - Produto Extra ID recebido: $produto_extra_id");
    error_log("🔍 DEBUG delete.php - Método HTTP: " . $_SERVER['REQUEST_METHOD']);
    error_log("🔍 DEBUG delete.php - GET id: " . ($_GET['id'] ?? 'NÃO DEFINIDO'));
    error_log("🔍 DEBUG delete.php - POST id: " . ($_POST['id'] ?? 'NÃO DEFINIDO'));

    if ($produto_extra_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID do produto extra é obrigatório']);
        exit();
    }

    // Verificar se o produto extra existe e pertence ao organizador
    $stmt = $pdo->prepare("SELECT pe.id FROM produtos_extras pe 
                           INNER JOIN eventos e ON pe.evento_id = e.id 
                           WHERE pe.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL");
    $stmt->execute([$produto_extra_id, $organizador_id, $usuario_id]);
    $produto_existente = $stmt->fetch();

    error_log("🔍 DEBUG delete.php - Produto extra encontrado: " . ($produto_existente ? 'SIM' : 'NÃO'));
    error_log("🔍 DEBUG delete.php - Organizador ID: $organizador_id");

    if (!$produto_existente) {
        echo json_encode(['success' => false, 'error' => 'Produto extra não encontrado ou não autorizado']);
        exit();
    }

    // Verificar se há inscrições que usam este produto extra (opcional - para validação futura)
    // Por enquanto, vamos permitir a exclusão

    // Iniciar transação
    $pdo->beginTransaction();

    try {
        // Deletar produtos relacionados (CASCADE já faz isso, mas vamos ser explícitos)
        $sql = "DELETE FROM produto_extra_produtos WHERE produto_extra_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$produto_extra_id]);
        $produtos_relacionados_deletados = $stmt->rowCount();
        error_log("🔍 DEBUG delete.php - Produtos relacionados deletados: $produtos_relacionados_deletados");

        // Deletar produto extra
        $sql = "DELETE FROM produtos_extras WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$produto_extra_id]);
        $produto_extra_deletado = $stmt->rowCount();
        error_log("🔍 DEBUG delete.php - Produto extra deletado: $produto_extra_deletado");

        $pdo->commit();
        error_log("🔍 DEBUG delete.php - Transação commitada com sucesso");

        echo json_encode([
            'success' => true,
            'message' => 'Produto extra excluído com sucesso'
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
