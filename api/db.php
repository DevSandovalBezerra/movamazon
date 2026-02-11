<?php
if (!defined('BASE_PATH')) {
    // Configurações gerais
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // Não exibe erros na tela
    ini_set('log_errors', 1); // Ativa o log de erros

    // Definir o diretório raiz do projeto
    define('BASE_PATH', dirname(__DIR__));

    $log_file_path = BASE_PATH . '/logs/php_errors.log';
    @ini_set('error_log', $log_file_path);
}

// Configurações do banco de dados

$vendor_autoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($vendor_autoload)) {
    require_once $vendor_autoload;
}

// Carregar variáveis de ambiente com tratamento de erro
$env_path = __DIR__ . '/../.env';
$env_file_exists = file_exists($env_path);

if ($env_file_exists) {
    $env_loaded = false;
    if (class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
            $env_loaded = true;
        } catch (Dotenv\Exception\InvalidFileException $e) {
            error_log('AVISO: Erro ao parsear .env: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log('AVISO: Erro ao carregar dotenv: ' . $e->getMessage());
        }
    }
    if (!$env_loaded) {
        // Fallback: Dotenv não disponível (ex.: vendor incompleto na hospedagem) - parse manual do .env
        $lines = @file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (is_array($lines)) {
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    $parts = explode('=', $line, 2);
                    $key = trim($parts[0]);
                    if ($key === '') {
                        continue;
                    }
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
} else {
    error_log('INFO: Arquivo .env não encontrado em: ' . $env_path . ' - usando variáveis de ambiente do sistema');
}
/////$STRIPE_PKEY = getenv('STRIPE_PKEY');
// Normalizar variáveis de ambiente (getenv retorna false quando não definida)
function envValue($key, $default = '')
{
    $val = getenv($key);
    if ($val === false) {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    return (string) $val;
}

$host = trim(envValue('DB_HOST'));
$db = trim(envValue('DB_NAME'));
$user = trim(envValue('DB_USER'));
$pass = envValue('DB_PASS');

// Validação mínima antes de tentar conectar
$missing = [];
if ($host === '') {
    $missing[] = 'DB_HOST';
}
if ($db === '') {
    $missing[] = 'DB_NAME';
}
if ($user === '') {
    $missing[] = 'DB_USER';
}

if (!empty($missing)) {
    $env_path = __DIR__ . '/../.env';
    $env_exists = file_exists($env_path) ? 'existe' : 'não existe';
    error_log('Configuração de banco ausente: ' . implode(', ', $missing));
    error_log('Arquivo .env: ' . $env_exists . ' em: ' . $env_path);
    error_log('Solução: Crie o arquivo .env na raiz do projeto ou configure as variáveis de ambiente no servidor');
    http_response_code(500);
    echo json_encode([
        'error' => 'Configuração de banco ausente',
        'missing' => $missing,
        'env_file' => $env_exists,
        'hint' => 'Verifique o arquivo .env ou configure as variáveis de ambiente no servidor'
    ]);
    exit;
}



/* 

$host = 'localhost';
$db = 'movamazon';
$user = 'root';
$pass = '';

 */

/* 

$host = 'localhost';
$db = 'brunor90_movamazon';
$user = 'brunor90_root';
$pass = 'k0gn022';

 */

/* 

$host = 'localhost';
$db = 'u697465806_movamazon';
$user = 'u697465806_movamazon';
$pass = 'Mind5unn352025';

 */




try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    error_log('Erro de conexão com o banco: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro de conexão com banco de dados']);
    exit;
}