<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

session_start();

error_log('API programacao/list.php - Início');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['papel']) || $_SESSION['papel'] !== 'organizador') {
    error_log('API programacao/list.php - Acesso negado: user_id=' . ($_SESSION['user_id'] ?? 'null') . ', papel=' . ($_SESSION['papel'] ?? 'null'));
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Acesso negado']);
    exit;
}

try {
    error_log('API programacao/list.php - Try block');
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    $evento_id = $_GET['evento_id'] ?? null;
    
    $sql = "SELECT 
                p.id,
                p.evento_id,
                p.tipo,
                p.titulo,
                p.descricao,
                p.ordem,
                p.ativo,
                p.data_criacao,
                p.hora_inicio,
                p.hora_fim,
                p.local,
                p.latitude,
                p.longitude,
                e.nome as evento_nome,
                e.data_inicio as data_evento
            FROM programacao_evento p
            INNER JOIN eventos e ON p.evento_id = e.id
            WHERE (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL";
    
    $params = [$organizador_id, $usuario_id];
    
    if ($evento_id) {
        $sql .= " AND p.evento_id = ?";
        $params[] = $evento_id;
    }
    
    // Ordenar por ordem, tipo e horário (quando disponível)
    $sql .= " ORDER BY e.data_inicio DESC, p.ordem ASC, p.tipo ASC, p.hora_inicio ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $programacao = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log('API programacao/list.php - Programação encontrada: ' . count($programacao));
    
    foreach ($programacao as &$item) {
        if ($item['data_evento']) {
            $item['data_evento_formatada'] = date('d/m/Y', strtotime($item['data_evento']));
        }
        
        // Formatar horários se disponíveis
        if ($item['hora_inicio']) {
            $item['hora_inicio_formatada'] = date('H:i', strtotime($item['hora_inicio']));
        }
        if ($item['hora_fim']) {
            $item['hora_fim_formatada'] = date('H:i', strtotime($item['hora_fim']));
        }
        
        // Calcular duração se ambos os horários estiverem disponíveis
        if ($item['hora_inicio'] && $item['hora_fim']) {
            $item['duracao'] = calcularDuracao($item['hora_inicio'], $item['hora_fim']);
        }
    }
    
    echo json_encode(['success' => true, 'data' => $programacao]);
    
} catch (Exception $e) {
    error_log('API programacao/list.php - Exceção: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}

function calcularDuracao($inicio, $fim) {
    $inicio_timestamp = strtotime($inicio);
    $fim_timestamp = strtotime($fim);
    $diferenca = $fim_timestamp - $inicio_timestamp;
    $horas = floor($diferenca / 3600);
    $minutos = floor(($diferenca % 3600) / 60);
    
    if ($horas > 0) {
        return $horas . 'h ' . $minutos . 'min';
    } else {
        return $minutos . 'min';
    }
}
?> 
