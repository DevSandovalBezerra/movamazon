<?php
/**
 * Helper Script para remover índices e colunas da tabela termos_eventos
 * Execute este script antes da ETAPA 5 da migration
 * 
 * Uso: php helper_drop_indexes_termos.php
 */

// Carregar variáveis de ambiente
require_once __DIR__ . '/../vendor/autoload.php';
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Exception $e) {
    // Ignora se .env não existir
}

// Função helper para ler variáveis de ambiente
function envValue($key, $default = '') {
    $val = getenv($key);
    if ($val === false) {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    return (string) $val;
}

// Conectar ao banco diretamente (sem usar db.php que faz exit em erros)
$host = trim(envValue('DB_HOST', 'localhost'));
$db = trim(envValue('DB_NAME', ''));
$user = trim(envValue('DB_USER', 'root'));
$pass = envValue('DB_PASS', '');

// Validação
if (empty($db)) {
    echo "✗ ERRO: DB_NAME não configurado.\n";
    echo "Configure no arquivo .env:\n";
    echo "  DB_HOST=localhost\n";
    echo "  DB_NAME=nome_do_banco\n";
    echo "  DB_USER=usuario\n";
    echo "  DB_PASS=senha\n";
    exit(1);
}

// Tentar conectar
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo "✗ ERRO: Não foi possível conectar ao banco de dados.\n";
    echo "Erro: " . $e->getMessage() . "\n\n";
    echo "Verifique:\n";
    echo "  1. Arquivo .env existe e está configurado corretamente\n";
    echo "  2. Variáveis DB_HOST, DB_NAME, DB_USER, DB_PASS estão definidas\n";
    echo "  3. Servidor MySQL está rodando\n";
    echo "  4. Credenciais estão corretas\n";
    exit(1);
}

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $db_name = $pdo->query("SELECT DATABASE()")->fetchColumn();
    $table_name = 'termos_eventos';
    
    echo "=== Helper: Remover Índices e Colunas Antigas ===\n\n";
    echo "Banco de dados: $db_name\n";
    echo "Tabela: $table_name\n\n";
    
    // Verificar e remover índices
    echo "1. Verificando índices...\n";
    $stmt = $pdo->prepare("
        SELECT INDEX_NAME 
        FROM INFORMATION_SCHEMA.STATISTICS 
        WHERE TABLE_SCHEMA = ? 
        AND TABLE_NAME = ? 
        AND INDEX_NAME IN ('evento_id', 'modalidade_id')
    ");
    $stmt->execute([$db_name, $table_name]);
    $indexes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($indexes)) {
        echo "   ✓ Nenhum índice antigo encontrado.\n";
    } else {
        foreach ($indexes as $index) {
            echo "   - Removendo índice: $index\n";
            try {
                $pdo->exec("ALTER TABLE $table_name DROP INDEX `$index`");
                echo "     ✓ Índice $index removido com sucesso.\n";
            } catch (PDOException $e) {
                echo "     ✗ Erro ao remover índice $index: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Verificar e remover colunas
    echo "\n2. Verificando colunas...\n";
    $stmt = $pdo->prepare("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = ? 
        AND TABLE_NAME = ? 
        AND COLUMN_NAME IN ('evento_id', 'modalidade_id')
    ");
    $stmt->execute([$db_name, $table_name]);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($columns)) {
        echo "   ✓ Nenhuma coluna antiga encontrada.\n";
    } else {
        foreach ($columns as $column) {
            echo "   - Removendo coluna: $column\n";
            try {
                $pdo->exec("ALTER TABLE $table_name DROP COLUMN `$column`");
                echo "     ✓ Coluna $column removida com sucesso.\n";
            } catch (PDOException $e) {
                echo "     ✗ Erro ao remover coluna $column: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n=== Concluído! ===\n";
    echo "Agora você pode executar a ETAPA 5 da migration normalmente.\n";
    
} catch (Exception $e) {
    echo "\n✗ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
