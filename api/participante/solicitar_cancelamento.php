<?php
/**
 * Endpoint para participante solicitar cancelamento de inscrição
 */

// Desabilitar exibição de erros para não quebrar JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security_middleware.php';
require_once __DIR__ . '/../helpers/inscricao_logger.php';
require_once __DIR__ . '/../helpers/email_helper.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

$usuario_id = $_SESSION['user_id'];
$dados = json_decode(file_get_contents('php://input'), true);

$inscricao_id = $dados['inscricao_id'] ?? null;
$motivo = trim($dados['motivo'] ?? '');

if (!$inscricao_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da inscrição é obrigatório.']);
    exit();
}

if (empty($motivo)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Motivo do cancelamento é obrigatório.']);
    exit();
}

try {
    // Buscar inscrição e validar
    $stmt = $pdo->prepare("
        SELECT i.*, e.nome as evento_nome, COALESCE(e.data_realizacao, e.data_inicio) as data_evento, e.politica_cancelamento,
               u.email as usuario_email, u.nome_completo as usuario_nome
        FROM inscricoes i
        JOIN eventos e ON i.evento_id = e.id
        JOIN usuarios u ON i.usuario_id = u.id
        WHERE i.id = ? AND i.usuario_id = ?
    ");
    $stmt->execute([$inscricao_id, $usuario_id]);
    $inscricao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$inscricao) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Inscrição não encontrada ou não pertence ao usuário.']);
        exit();
    }
    
    // Validar se já está cancelada
    if ($inscricao['status'] === 'cancelada' || $inscricao['status_pagamento'] === 'cancelado') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Esta inscrição já está cancelada.']);
        exit();
    }
    
    // Verificar se já existe solicitação pendente
    $stmt_check = $pdo->prepare("
        SELECT id FROM solicitacoes_cancelamento 
        WHERE inscricao_id = ? AND status = 'pendente'
    ");
    $stmt_check->execute([$inscricao_id]);
    if ($stmt_check->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Já existe uma solicitação de cancelamento pendente para esta inscrição.']);
        exit();
    }
    
    // Validar prazo mínimo antes do evento (7 dias por padrão)
    $data_evento = new DateTime($inscricao['data_evento']);
    $hoje = new DateTime();
    $dias_restantes = $hoje->diff($data_evento)->days;
    
    // Verificar política de cancelamento do evento (se configurada)
    $prazo_minimo = 7; // dias padrão
    if (!empty($inscricao['politica_cancelamento'])) {
        $politica = json_decode($inscricao['politica_cancelamento'], true);
        if (isset($politica['prazo_minimo_dias'])) {
            $prazo_minimo = (int)$politica['prazo_minimo_dias'];
        }
    }
    
    if ($dias_restantes < $prazo_minimo && $data_evento > $hoje) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => "Cancelamento não permitido. O evento ocorre em menos de $prazo_minimo dias."
        ]);
        exit();
    }
    
    // Verificar rate limiting (máximo 3 solicitações por dia)
    $stmt_rate = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM solicitacoes_cancelamento
        WHERE usuario_id = ? 
        AND DATE(data_solicitacao) = CURDATE()
    ");
    $stmt_rate->execute([$usuario_id]);
    $rate_data = $stmt_rate->fetch(PDO::FETCH_ASSOC);
    
    if ($rate_data['total'] >= 3) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Limite de solicitações diárias atingido. Tente novamente amanhã.']);
        exit();
    }
    
    // Criar solicitação
    $stmt_insert = $pdo->prepare("
        INSERT INTO solicitacoes_cancelamento (
            inscricao_id, usuario_id, motivo, status
        ) VALUES (?, ?, ?, 'pendente')
    ");
    $stmt_insert->execute([$inscricao_id, $usuario_id, $motivo]);
    
    $solicitacao_id = $pdo->lastInsertId();
    
    // Log da solicitação
    logInscricaoPagamento('INFO', 'CANCELAMENTO_SOLICITADO', [
        'inscricao_id' => $inscricao_id,
        'usuario_id' => $usuario_id,
        'solicitacao_id' => $solicitacao_id,
        'motivo' => substr($motivo, 0, 200) // Limitar tamanho do log
    ]);
    
    // Enviar email para admin
    $admin_emails = [];
    $stmt_admins = $pdo->prepare("
        SELECT email FROM usuarios 
        WHERE papel = 'admin' AND status = 'ativo'
    ");
    $stmt_admins->execute();
    while ($admin = $stmt_admins->fetch(PDO::FETCH_ASSOC)) {
        $admin_emails[] = $admin['email'];
    }
    
    if (!empty($admin_emails)) {
        require_once __DIR__ . '/../helpers/email_templates.php';
        
        $email_subject = "Nova Solicitação de Cancelamento - Inscrição #$inscricao_id";
        $email_body = getEmailTemplateCancelamentoSolicitadoAdmin([
            'solicitacao_id' => $solicitacao_id,
            'inscricao_id' => $inscricao_id,
            'evento_nome' => $inscricao['evento_nome'],
            'usuario_nome' => $inscricao['usuario_nome'],
            'usuario_email' => $inscricao['usuario_email'],
            'valor_total' => $inscricao['valor_total'],
            'motivo' => $motivo
        ]);
        
        foreach ($admin_emails as $admin_email) {
            sendEmail($admin_email, $email_subject, $email_body);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Solicitação de cancelamento criada com sucesso. Aguarde análise administrativa.',
        'solicitacao_id' => $solicitacao_id
    ]);
    
} catch (Exception $e) {
    error_log("[SOLICITAR_CANCELAMENTO] Erro: " . $e->getMessage());
    error_log("[SOLICITAR_CANCELAMENTO] Stack trace: " . $e->getTraceAsString());
    
    if (function_exists('logInscricaoPagamento')) {
        logInscricaoPagamento('ERROR', 'CANCELAMENTO_SOLICITADO_ERROR', [
            'inscricao_id' => $inscricao_id ?? null,
            'usuario_id' => $usuario_id ?? null,
            'erro' => $e->getMessage()
        ]);
    }
    
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar solicitação de cancelamento.',
        'error' => $e->getMessage()
    ]);
}
