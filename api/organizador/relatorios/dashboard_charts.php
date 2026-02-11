<?php
session_start();
require_once '../../db.php';
require_once '../../security_middleware.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Contexto do organizador (organizador_id padrão + usuario_id legado)
$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];

header('Content-Type: application/json');
$evento_id = $_GET['evento_id'] ?? 0;

if (!$evento_id) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'ID do evento é obrigatório.']));
}

try {
    // Validar se o organizador é dono do evento
    $stmt_check = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?)");
    $stmt_check->execute([$evento_id, $organizador_id, $usuario_id]);
    if ($stmt_check->fetch() === false) {
        http_response_code(403);
        exit(json_encode(['success' => false, 'message' => 'Acesso negado a este evento.']));
    }

    $response = ['success' => true];

    // Gráfico 1: Inscrições confirmadas por dia
    $sql_inscricoes = "SELECT DATE(data_inscricao) as dia, COUNT(id) as total 
                       FROM inscricoes 
                       WHERE evento_id = ? AND status = 'confirmada' 
                       GROUP BY dia ORDER BY dia ASC";
    $stmt = $pdo->prepare($sql_inscricoes);
    $stmt->execute([$evento_id]);
    $response['inscricoesPorDia'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Gráfico 2: Inscrições por modalidade (gráfico de pizza)
    $sql_modalidades = "SELECT m.nome, COUNT(i.id) as total 
                        FROM inscricoes i 
                        JOIN modalidades m ON i.modalidade_evento_id = m.id 
                        WHERE i.evento_id = ? AND i.status = 'confirmada' 
                        GROUP BY m.nome";
    $stmt = $pdo->prepare($sql_modalidades);
    $stmt->execute([$evento_id]);
    $response['inscricoesPorModalidade'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
