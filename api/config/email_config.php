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

// Configurações de SMTP para envio de e-mail
define('SMTP_HOST', 'mail.movamazon.com.br');
define('SMTP_USERNAME', 'contato@movamazon.com.br');
define('SMTP_PASSWORD', envValue('SMTP_PASSWORD', ''));
define('SMTP_PORT', 465);
define('SMTP_SECURE', PHPMailer::ENCRYPTION_SMTPS);

define('EMAIL_FROM_ADDRESS', 'contato@movamazon.com.br');
define('EMAIL_FROM_NAME', 'MovAmazonas');
