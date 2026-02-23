<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function testing_load_env_file(string $filePath): void
{
    if (!is_file($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, "\"'");

        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}

testing_load_env_file(BASE_PATH . '/.env.testing');
if ((getenv('RUN_INTEGRATION_TESTS') ?: '0') === '1') {
    testing_load_env_file(BASE_PATH . '/.env');
}

if ((getenv('RUN_INTEGRATION_TESTS') ?: '0') === '1' && trim((string) (getenv('DB_NAME') ?: '')) !== '') {
    $dbName = trim((string) getenv('DB_NAME'));
    if (!str_ends_with(strtolower($dbName), '_test')) {
        $derived = $dbName . '_test';
        putenv('DB_NAME=' . $derived);
        $_ENV['DB_NAME'] = $derived;
    }
}

function testing_assert_safe_integration_db(): void
{
    if ((getenv('RUN_INTEGRATION_TESTS') ?: '0') !== '1') {
        return;
    }

    $dbName = trim((string) (getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? '')));
    if ($dbName === '') {
        throw new RuntimeException('Integration tests blocked: DB_NAME is empty.');
    }

    if (!str_ends_with(strtolower($dbName), '_test')) {
        throw new RuntimeException(
            'Integration tests blocked: DB_NAME must end with "_test". Current DB_NAME: ' . $dbName
        );
    }
}

testing_assert_safe_integration_db();

function testing_integration_db_config(): array
{
    return [
        'host' => trim((string) (getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost'))),
        'port' => trim((string) (getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306'))),
        'name' => trim((string) (getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? ''))),
        'user' => trim((string) (getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root'))),
        'pass' => (string) (getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? '')),
    ];
}

function testing_integration_create_database_if_missing(): void
{
    $cfg = testing_integration_db_config();
    $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $cfg['host'], $cfg['port']);
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', $cfg['name']) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
}

function testing_integration_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = testing_integration_db_config();
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $cfg['host'],
        $cfg['port'],
        $cfg['name']
    );

    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    return $pdo;
}
