<?php
require_once __DIR__ . '/../db.php';

error_log('--- INÍCIO RECUPERAR SENHA ---');

// PHPMailer
$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
error_log('E-mail recebido para recuperação: ' . $email);

if (empty($email)) {
    error_log('E-mail vazio. Encerrando.');
    echo json_encode(['success' => true, 'message' => 'Se o e-mail estiver cadastrado, você receberá um link para redefinir sua senha.']);
    exit;
}

try {
    // Buscar usuário por e-mail usando PDO
    $stmt = $pdo->prepare('SELECT id, nome_completo FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log($usuario ? 'Usuário encontrado: ' . $usuario['id'] : 'Usuário não encontrado para este e-mail.');
    
    if ($usuario) {
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', time() + 3600);
        error_log('Token gerado: ' . $token . ' | Expira: ' . $expira);
        
        // Salvar token no banco
        $stmt = $pdo->prepare('UPDATE usuarios SET token_recuperacao = ?, token_expira = ? WHERE id = ?');
        $stmt->execute([$token, $expira, $usuario['id']]);
        
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $projectBase = rtrim(dirname(dirname(dirname($scriptName))), '/');
        if ($projectBase === '/' || $projectBase === '.' || $projectBase === '\\') {
            $projectBase = '';
        }
        $pathReset = $projectBase . '/frontend/paginas/auth/resetar_senha.php';
        $link = $scheme . '://' . $host . $pathReset . '?token=' . urlencode($token);
        $assunto = 'Redefinição de senha - MovAmazon';
        $mensagem = "Olá,<br><br>Recebemos uma solicitação para redefinir a senha da sua conta no MovAmazon.<br><br>Para criar uma nova senha, acesse o link abaixo (válido por 1 hora):<br><a href='$link'>$link</a><br><br>Se você não solicitou esta alteração, pode ignorar este e-mail com segurança.<br><br>Equipe MovAmazon";
        
        $mail = new PHPMailer(true);
        try {
            error_log('Tentando enviar e-mail para: ' . $email);
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'movhealth@moveromundo.com.br';
            $mail->Password = 'Moveromundo2025@';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('movhealth@moveromundo.com.br', 'MovAmazon');
            $mail->addAddress($email);
            $mail->Subject = $assunto;
            $mail->isHTML(true);
            $mail->Body = $mensagem;
            $mail->send();
            error_log('E-mail enviado com sucesso para: ' . $email);
        } catch (Exception $e) {
            error_log('Erro ao enviar e-mail: ' . $e->getMessage());
        }
    }
    
    error_log('Fluxo finalizado. Retornando resposta genérica.');
    echo json_encode(['success' => true, 'message' => 'Se o e-mail estiver cadastrado, você receberá um link para redefinir sua senha.']);
} catch (Exception $e) {
    error_log('Exceção no fluxo de recuperação: ' . $e->getMessage());
    echo json_encode(['success' => true, 'message' => 'Se o e-mail estiver cadastrado, você receberá um link para redefinir sua senha.']);
} 
