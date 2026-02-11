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
$titulo = trim($data['titulo'] ?? '');
$codigo_remessa = trim($data['codigo_remessa'] ?? '');
$valor_desconto = $data['valor_desconto'] ?? null;
$tipo_valor = $data['tipo_valor'] ?? '';
$tipo_desconto = $data['tipo_desconto'] ?? 'ambos';
$max_uso = $data['max_uso'] ?? 1;
$habilita_desconto_itens = !empty($data['habilita_desconto_itens']) ? 1 : 0;
$data_inicio = $data['data_inicio'] ?? null;
$data_validade = $data['data_validade'] ?? null;
$evento_id = $data['evento_id'] ?? null;

if (!$titulo || !$codigo_remessa || !$valor_desconto || !$tipo_valor || !$data_inicio || !$data_validade) {
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios ausentes']);
    exit;
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    $pdo->beginTransaction();
    if ($evento_id) {
        $stmt = $pdo->prepare('SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL');
        $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
        if (!$stmt->fetch()) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Evento não encontrado, foi excluído ou sem permissão']);
            exit;
        }
    }
    $stmt = $pdo->prepare('INSERT INTO cupons_remessa (titulo, codigo_remessa, valor_desconto, tipo_valor, tipo_desconto, max_uso, habilita_desconto_itens, data_inicio, data_validade, evento_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$titulo, $codigo_remessa, $valor_desconto, $tipo_valor, $tipo_desconto, $max_uso, $habilita_desconto_itens, $data_inicio, $data_validade, $evento_id]);
    $id = $pdo->lastInsertId();
    $pdo->commit();
    echo json_encode(['success' => true, 'id' => $id]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Erro ao criar remessa de cupons: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao criar remessa']);
} 
