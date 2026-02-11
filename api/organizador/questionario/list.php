<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Verificar autenticação
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}



try {
    $evento_id = $_GET['evento_id'] ?? null;
    
    if (!$evento_id) {
        echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
        exit;
    }

    // Verificar se o evento pertence ao organizador logado
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?)");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou acesso negado']);
        exit;
    }

    // Buscar perguntas/campos do questionário com modalidades associadas
    $query = "
        SELECT 
            qe.id,
            qe.evento_id,
            qe.tipo,
            qe.tipo_resposta,
            qe.classificacao,
            qe.mascara,
            qe.texto,
            qe.obrigatorio,
            qe.ordem,
            qe.ativo,
            qe.status_site,
            qe.status_grupo,
            qe.data_criacao,
            GROUP_CONCAT(
                CONCAT(m.id, ':', m.nome)
                ORDER BY m.nome
                SEPARATOR ';'
            ) as modalidades_associadas
        FROM questionario_evento qe
        LEFT JOIN questionario_evento_modalidade qem ON qe.id = qem.questionario_evento_id
        LEFT JOIN modalidades m ON qem.modalidade_id = m.id
        LEFT JOIN categorias c ON m.categoria_id = c.id
        WHERE qe.evento_id = ?
        GROUP BY qe.id
        ORDER BY qe.ordem ASC, qe.id ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$evento_id]);
    $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Processar modalidades associadas
    foreach ($perguntas as &$pergunta) {
        $modalidades = [];
        if (!empty($pergunta['modalidades_associadas'])) {
            $modalidades_data = explode(';', $pergunta['modalidades_associadas']);
            foreach ($modalidades_data as $modalidade_data) {
                if (!empty($modalidade_data)) {
                    list($id, $nome) = explode(':', $modalidade_data, 2);
                    $modalidades[] = [
                        'id' => (int)$id,
                        'nome' => $nome
                    ];
                }
            }
        }
        $pergunta['modalidades'] = $modalidades;
        unset($pergunta['modalidades_associadas']);
    }

    echo json_encode([
        'success' => true, 
        'data' => $perguntas,
        'total' => count($perguntas)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
