<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

session_start();

error_log('API programacao/update.php - Início');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['papel']) || $_SESSION['papel'] !== 'organizador') {
    error_log('API programacao/update.php - Acesso negado: user_id=' . ($_SESSION['user_id'] ?? 'null') . ', papel=' . ($_SESSION['papel'] ?? 'null'));
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

try {
    error_log('API programacao/update.php - Try block');
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    error_log('API programacao/update.php - Dados recebidos: ' . print_r($data, true));
    
    $programacao_id = $data['id'] ?? null;
    $evento_id = $data['evento_id'] ?? null;
    $tipo = $data['tipo'] ?? '';
    $titulo = $data['titulo'] ?? '';
    $descricao = $data['descricao'] ?? '';
    $ordem = $data['ordem'] ?? 0;
    $ativo = isset($data['ativo']) ? (int)$data['ativo'] : 1;
    $hora_inicio = $data['hora_inicio'] ?? null;
    $hora_fim = $data['hora_fim'] ?? null;
    $local = $data['local'] ?? null;
    $latitude = $data['latitude'] ?? null;
    $longitude = $data['longitude'] ?? null;
    
    if (!$programacao_id || !$evento_id || !$tipo || !$titulo) {
        error_log('API programacao/update.php - Campos obrigatórios faltando');
        echo json_encode(['success' => false, 'error' => 'ID, evento, tipo e título são obrigatórios']);
        exit;
    }
    
    // Validar tipo
    if (!in_array($tipo, ['percurso', 'horario_largada', 'atividade_adicional'])) {
        error_log('API programacao/update.php - Tipo inválido: ' . $tipo);
        echo json_encode(['success' => false, 'error' => 'Tipo inválido. Use: percurso, horario_largada ou atividade_adicional']);
        exit;
    }
    
    // Verificar se o item de programação pertence ao organizador
    $sql = "SELECT pe.id FROM programacao_evento pe 
            INNER JOIN eventos e ON pe.evento_id = e.id 
            WHERE pe.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$programacao_id, $organizador_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        error_log('API programacao/update.php - Item de programação não encontrado ou não pertence ao organizador');
        echo json_encode(['success' => false, 'error' => 'Item de programação não encontrado ou não pertence ao organizador']);
        exit;
    }
    
    // Validar e converter horários
    $hora_inicio_sql = !empty($hora_inicio) ? $hora_inicio : null;
    $hora_fim_sql = !empty($hora_fim) ? $hora_fim : null;
    
    // Validar formato de horário se fornecido
    if ($hora_inicio_sql && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora_inicio_sql)) {
        error_log('API programacao/update.php - Formato de hora_inicio inválido: ' . $hora_inicio_sql);
        echo json_encode(['success' => false, 'error' => 'Formato de hora de início inválido. Use HH:MM']);
        exit;
    }
    if ($hora_fim_sql && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora_fim_sql)) {
        error_log('API programacao/update.php - Formato de hora_fim inválido: ' . $hora_fim_sql);
        echo json_encode(['success' => false, 'error' => 'Formato de hora de fim inválido. Use HH:MM']);
        exit;
    }
    
    // Validar coordenadas se fornecidas
    if ($latitude !== null && !is_numeric($latitude)) {
        $latitude = null;
    }
    if ($longitude !== null && !is_numeric($longitude)) {
        $longitude = null;
    }
    
    // Validar que campos de evento não sejam enviados acidentalmente (exceto evento_id que é necessário)
    $camposEvento = ['nome', 'categoria', 'data_inicio', 'data_fim', 'genero', 'cep', 'url_mapa', 'logradouro', 'numero', 'cidade', 'estado', 'pais', 'status'];
    foreach ($camposEvento as $campo) {
        if (isset($data[$campo])) {
            error_log("⚠️ AVISO: Campo de evento '{$campo}' recebido na API de programação. Ignorando.");
            unset($data[$campo]);
        }
    }
    
    // Atualizar item de programação
    error_log('API programacao/update.php - Atualizando item de programação');
    error_log("📝 UPDATE programacao_evento (id={$programacao_id}, evento_id={$evento_id}, tipo={$tipo}, hora_inicio={$hora_inicio_sql}, local={$local})");
    $sql = "UPDATE programacao_evento SET 
                evento_id = ?, 
                tipo = ?, 
                titulo = ?, 
                descricao = ?, 
                ordem = ?,
                ativo = ?,
                hora_inicio = ?,
                hora_fim = ?,
                local = ?,
                latitude = ?,
                longitude = ?
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$evento_id, $tipo, $titulo, $descricao, $ordem, $ativo, $hora_inicio_sql, $hora_fim_sql, $local, $latitude, $longitude, $programacao_id]);
    error_log('API programacao/update.php - Item atualizado: ' . $programacao_id);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Item de programação atualizado com sucesso'
    ]);
    
} catch (Exception $e) {
    error_log('API programacao/update.php - Exceção: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?> 
