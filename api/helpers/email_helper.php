<?php
// Inclui o autoloader do Composer para carregar a PHPMailer
require_once __DIR__ . '/../../vendor/autoload.php';
// Inclui as configurações de e-mail
require_once __DIR__ . '/../config/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envia email com suporte a anexos opcionais.
 *
 * @param string $to
 * @param string $subject
 * @param string $htmlBody
 * @param array $attachments Cada item pode conter:
 *   - path: caminho para arquivo
 *   - name: nome do arquivo
 *   - type: mime type
 *   - content: string com conteúdo (para anexos em memória)
 */
function sendEmail($to, $subject, $htmlBody, array $attachments = []) {
    $mail = new PHPMailer(true);

    try {
        // Configurações do Servidor
        // $mail->SMTPDebug = 2; // Descomente para debug detalhado
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Remetente e Destinatário
        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($to);

        // Conteúdo do E-mail
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody); // Versão em texto puro

        // Anexos (opcionais)
        foreach ($attachments as $attachment) {
            if (isset($attachment['path'])) {
                $mail->addAttachment(
                    $attachment['path'],
                    $attachment['name'] ?? ''
                );
                continue;
            }

            if (isset($attachment['content'])) {
                $mail->addStringAttachment(
                    $attachment['content'],
                    $attachment['name'] ?? 'anexo.txt',
                    'base64',
                    $attachment['type'] ?? 'application/octet-stream'
                );
            }
        }

        $mail->send();
        error_log("Email enviado com sucesso para: $to");
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail para $to: {$mail->ErrorInfo}");
        return false;
    }
}
