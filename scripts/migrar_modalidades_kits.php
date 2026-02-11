<?php
/**
 * Script de migração: Popular tabela kit_modalidade_evento
 * com os dados existentes do campo modalidade_evento_id da tabela kits_eventos
 * 
 * Este script deve ser executado apenas uma vez para migrar dados legados
 */

// Configurar para CLI
if (php_sapi_name() !== 'cli') {
    die("Este script deve ser executado via linha de comando.\n");
}

// Carregar variáveis de ambiente
require_once __DIR__ . '/../vendor/autoload.php';

$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
    } catch (Exception $e) {
        echo "AVISO: Erro ao carregar .env: " . $e->getMessage() . "\n";
    }
}

// Função para obter variável de ambiente
function envValue($key, $default = '') {
    $val = getenv($key);
    if ($val === false) {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    return (string) $val;
}

// Configurações do banco
$host = trim(envValue('DB_HOST'));
$db = trim(envValue('DB_NAME'));
$user = trim(envValue('DB_USER'));
$pass = envValue('DB_PASS');

// Validação
$missing = [];
if ($host === '') $missing[] = 'DB_HOST';
if ($db === '') $missing[] = 'DB_NAME';
if ($user === '') $missing[] = 'DB_USER';

if (!empty($missing)) {
    die("ERRO: Variáveis de ambiente ausentes: " . implode(', ', $missing) . "\n");
}

// Conectar ao banco
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Conexão com banco de dados estabelecida.\n";
} catch (PDOException $e) {
    die("ERRO: Falha ao conectar ao banco de dados: " . $e->getMessage() . "\n");
}

try {
    echo "Iniciando migração de modalidades dos kits...\n";
    
    // Buscar todos os kits que têm modalidade_evento_id mas não têm registro na tabela N:N
    $sql = "
        SELECT k.id, k.modalidade_evento_id, k.evento_id
        FROM kits_eventos k
        WHERE k.modalidade_evento_id IS NOT NULL 
        AND k.modalidade_evento_id > 0
        AND NOT EXISTS (
            SELECT 1 
            FROM kit_modalidade_evento kme 
            WHERE kme.kit_id = k.id
        )
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $kits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Encontrados " . count($kits) . " kits para migrar.\n";
    
    if (empty($kits)) {
        echo "Nenhum kit precisa ser migrado.\n";
        exit(0);
    }
    
    $pdo->beginTransaction();
    
    $stmt_insert = $pdo->prepare("
        INSERT INTO kit_modalidade_evento (kit_id, modalidade_evento_id) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE kit_id = kit_id
    ");
    
    $migrados = 0;
    foreach ($kits as $kit) {
        // Verificar se a modalidade existe
        $stmt_check = $pdo->prepare("SELECT id FROM modalidades WHERE id = ? AND evento_id = ?");
        $stmt_check->execute([$kit['modalidade_evento_id'], $kit['evento_id']]);
        
        if ($stmt_check->fetch()) {
            $stmt_insert->execute([$kit['id'], $kit['modalidade_evento_id']]);
            $migrados++;
            echo "Kit ID {$kit['id']} migrado com modalidade {$kit['modalidade_evento_id']}\n";
        } else {
            echo "AVISO: Modalidade {$kit['modalidade_evento_id']} não encontrada para kit {$kit['id']}\n";
        }
    }
    
    $pdo->commit();
    
    echo "\nMigração concluída! {$migrados} kits migrados com sucesso.\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}

