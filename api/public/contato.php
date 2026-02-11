<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../helpers/email_helper.php';
require_once __DIR__ . '/../config/email_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    
    // Validar campos obrigatórios
    $nome = trim($input['nome'] ?? '');
    $email = trim($input['email'] ?? '');
    $telefone = trim($input['telefone'] ?? '');
    $assunto = trim($input['assunto'] ?? '');
    $mensagem = trim($input['mensagem'] ?? '');
    
    $errors = [];
    
    if (empty($nome)) {
        $errors['nome'] = 'Nome é obrigatório';
    }
    
    if (empty($email)) {
        $errors['email'] = 'E-mail é obrigatório';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'E-mail inválido';
    }
    
    if (empty($telefone)) {
        $errors['telefone'] = 'Telefone é obrigatório';
    }
    
    if (empty($assunto)) {
        $errors['assunto'] = 'Assunto é obrigatório';
    }
    
    if (empty($mensagem)) {
        $errors['mensagem'] = 'Mensagem é obrigatória';
    } elseif (strlen($mensagem) < 10) {
        $errors['mensagem'] = 'Mensagem deve ter pelo menos 10 caracteres';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Campos inválidos',
            'errors' => $errors
        ]);
        exit;
    }
    
    // Mapear assunto para texto legível
    $assuntosMap = [
        'duvida' => 'Dúvida sobre Eventos',
        'inscricao' => 'Problema com Inscrição',
        'pagamento' => 'Dúvida sobre Pagamento',
        'organizador' => 'Sou Organizador',
        'outro' => 'Outro'
    ];
    $assuntoTexto = $assuntosMap[$assunto] ?? $assunto;
    
    // Preparar conteúdo do e-mail
    $emailSubject = "Contato MovAmazon - {$assuntoTexto}";
    
    $emailBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #0b4340; color: white; padding: 20px; text-align: center; }
            .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #0b4340; }
            .value { margin-top: 5px; padding: 10px; background-color: white; border-left: 3px solid #0b4340; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Novo Contato - MovAmazon</h1>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Nome:</div>
                    <div class='value'>" . htmlspecialchars($nome) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>E-mail:</div>
                    <div class='value'>" . htmlspecialchars($email) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Telefone:</div>
                    <div class='value'>" . htmlspecialchars($telefone) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Assunto:</div>
                    <div class='value'>" . htmlspecialchars($assuntoTexto) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Mensagem:</div>
                    <div class='value'>" . nl2br(htmlspecialchars($mensagem)) . "</div>
                </div>
            </div>
            <div class='footer'>
                <p>Este e-mail foi enviado através do formulário de contato do site MovAmazon.</p>
                <p>Data: " . date('d/m/Y H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Obter e-mail do admin do .env ou usar padrão
    $adminEmail = getenv('ADMIN_EMAIL') ?: $_ENV['ADMIN_EMAIL'] ?? 'contato@movamazon.com.br';
    
    // Enviar e-mail
    $emailEnviado = sendEmail($adminEmail, $emailSubject, $emailBody);
    
    if (!$emailEnviado) {
        error_log("[CONTATO] Erro ao enviar e-mail de contato de: {$email}");
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao enviar mensagem. Tente novamente mais tarde.'
        ]);
        exit;
    }
    
    // Log do contato
    error_log("[CONTATO] Mensagem recebida de: {$nome} ({$email}) - Assunto: {$assuntoTexto}");
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Mensagem enviada com sucesso! Entraremos em contato em breve.'
    ]);
    
} catch (Exception $e) {
    error_log("[CONTATO] Erro ao processar contato: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar solicitação. Tente novamente mais tarde.'
    ]);
}
