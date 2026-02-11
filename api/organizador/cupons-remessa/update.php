<?php
require_once '../../auth/auth.php';
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';
header('Content-Type: application/json');

if (!isOrganizador()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$titulo = trim($data['titulo'] ?? '');
$valor_desconto = $data['valor_desconto'] ?? null;
$tipo_valor = $data['tipo_valor'] ?? '';
$tipo_desconto = $data['tipo_desconto'] ?? 'ambos';
$max_uso = $data['max_uso'] ?? 1;
$habilita_desconto_itens = !empty($data['habilita_desconto_itens']) ? 1 : 0;
$data_inicio = $data['data_inicio'] ?? null;
$data_validade = $data['data_validade'] ?? null;
$status = $data['status'] ?? null;

if (!$id || !$titulo || !$valor_desconto || !$tipo_valor || !$data_inicio || !$data_validade) {
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios ausentes']);
    exit;
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    $stmt = $pdo->prepare('SELECT * FROM cupons_remessa WHERE id = ?');
    $stmt->execute([$id]);
    $remessa = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$remessa) {
        echo json_encode(['success' => false, 'message' => 'Remessa não encontrada']);
        exit;
    }
    if ($remessa['evento_id']) {
        $stmt = $pdo->prepare('SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL');
        $stmt->execute([$remessa['evento_id'], $organizador_id, $usuario_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão para esta remessa']);
            exit;
        }
    }
    
    $stmt = $pdo->prepare('UPDATE cupons_remessa SET titulo=?, valor_desconto=?, tipo_valor=?, tipo_desconto=?, max_uso=?, habilita_desconto_itens=?, data_inicio=?, data_validade=?, status=? WHERE id=?');
    $stmt->execute([$titulo, $valor_desconto, $tipo_valor, $tipo_desconto, $max_uso, $habilita_desconto_itens, $data_inicio, $data_validade, $status, $id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('Erro ao atualizar remessa de cupons: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar remessa']);
} 
