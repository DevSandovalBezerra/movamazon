<?php

use PHPMailer\PHPMailer\PHPMailer;

// Carregar .env se ainda não foi carregado (ex.: quando este arquivo é incluído sem db.php)
if (!isset($_ENV['DB_HOST']) && !isset($_ENV['SMTP_PASSWORD'])) {
    $root = __DIR__ . '/../..';
    $autoload = $root . '/vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    }
    $env_path = $root . '/.env';
    if (file_exists($env_path)) {
        if (class_exists('Dotenv\Dotenv')) {
            try {
                Dotenv\Dotenv::createImmutable($root)->load();
            } catch (Exception $e) {
                // ignora
            }
        } else {
            $lines = @file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#') continue;
                    if (strpos($line, '=') !== false) {
                        $parts = explode('=', $line, 2);
                        $key = trim($parts[0]);
                        if ($key !== '') {
                            $val = isset($parts[1]) ? trim($parts[1]) : '';
                            if (preg_match('/^["\'](.+)["\']\s*$/', $val, $m)) {
                                $val = str_replace(['\\"', "\\'"], ['"', "'"], $m[1]);
                            }
                            $_ENV[$key] = $val;
                            putenv($key . '=' . $val);
                        }
                    }
                }
            }
        }
    }
}

// Função helper para ler variáveis de ambiente (se não estiver definida)
if (!function_exists('envValue')) {
    function envValue($key, $default = '')
    {
        $val = getenv($key);
        if ($val === false) {
            $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }
        return (string) $val;
    }
}

if (!function_exists('resolveSmtpSecureMode')) {
    function resolveSmtpSecureMode($smtpPort, $smtpSecureEnv)
    {
        $secure = strtolower(trim((string) $smtpSecureEnv));
        if ($secure === 'ssl' || $secure === 'smtps') {
            return PHPMailer::ENCRYPTION_SMTPS;
        }
        if ($secure === 'tls' || $secure === 'starttls') {
            return PHPMailer::ENCRYPTION_STARTTLS;
        }
        if ($secure === 'none' || $secure === '') {
            return ((int) $smtpPort === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        }
        return ((int) $smtpPort === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    }
}

// SMTP settings loaded from environment with safe defaults (HostGator SSL/TLS).
$smtpHost = envValue('SMTP_HOST', 'mail.movamazon.com.br');
$smtpUsername = envValue('SMTP_USERNAME', 'noreply@movamazon.com.br');
$smtpPassword = envValue('SMTP_PASSWORD', '');
$smtpPort = (int) envValue('SMTP_PORT', '465');
$smtpSecureEnv = envValue('SMTP_SECURE', envValue('SMTP_ENCRYPTION', ''));
$smtpSecure = resolveSmtpSecureMode($smtpPort, $smtpSecureEnv);
$emailFromAddress = envValue('EMAIL_FROM_ADDRESS', $smtpUsername);
$emailFromName = envValue('EMAIL_FROM_NAME', 'MovAmazonas');

if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', $smtpHost);
}
if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', $smtpUsername);
}
if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', $smtpPassword);
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', $smtpPort);
}
if (!defined('SMTP_SECURE')) {
    define('SMTP_SECURE', $smtpSecure);
}
if (!defined('EMAIL_FROM_ADDRESS')) {
    define('EMAIL_FROM_ADDRESS', $emailFromAddress);
}
if (!defined('EMAIL_FROM_NAME')) {
    define('EMAIL_FROM_NAME', $emailFromName);
}
