<?php
/**
 * Script para renomear arquivos físicos de kits para corresponder aos nomes normalizados no banco
 * 
 * Este script:
 * 1. Consulta o banco para obter os nomes normalizados
 * 2. Lista os arquivos físicos no diretório
 * 3. Tenta fazer match entre arquivos e nomes do banco
 * 4. Renomeia os arquivos físicos para corresponder aos nomes do banco
 */

// Verificar se .env existe antes de tentar carregar db.php
$env_file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
if (!file_exists($env_file)) {
    echo "⚠️  Arquivo .env não encontrado em: {$env_file}\n";
    echo "O script precisa das configurações do banco de dados para funcionar.\n";
    echo "Crie o arquivo .env ou configure as variáveis de ambiente:\n";
    echo "  - DB_HOST\n";
    echo "  - DB_NAME\n";
    echo "  - DB_USER\n";
    echo "  - DB_PASS\n";
    exit(1);
}

// Carregar db.php (pode fazer exit se houver erro)
require_once __DIR__ . '/../db.php';

// Verificar se $pdo foi criado
if (!isset($pdo)) {
    echo "❌ Erro: Não foi possível conectar ao banco de dados\n";
    echo "Verifique as configurações no arquivo .env\n";
    exit(1);
}

require_once __DIR__ . '/file_utils.php';

// Configurações
$base_dir = dirname(__DIR__, 2);
$kits_dir = $base_dir . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'kits' . DIRECTORY_SEPARATOR;

// Modo dry-run por padrão (simulação)
// Suporta tanto CLI quanto web
if (php_sapi_name() === 'cli') {
    // Modo CLI: verificar argumentos da linha de comando
    $dry_run = !in_array('execute=true', $argv) && !in_array('execute', $argv);
} else {
    // Modo web: verificar query string
    $dry_run = !isset($_GET['execute']) || $_GET['execute'] !== 'true';
}

// Log
$log_file = $base_dir . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'rename_files_' . date('Y-m-d_His') . '.log';
if (!is_dir(dirname($log_file))) {
    mkdir(dirname($log_file), 0755, true);
}

function logMessage($message, $log_file) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    echo $log_entry;
}

logMessage("=== INÍCIO DA RENOMEAÇÃO DE ARQUIVOS FÍSICOS ===", $log_file);
logMessage("Modo: " . ($dry_run ? "SIMULAÇÃO (DRY RUN)" : "EXECUÇÃO REAL"), $log_file);
logMessage("", $log_file);

// Verificar se diretório existe
if (!is_dir($kits_dir)) {
    logMessage("❌ Diretório não encontrado: {$kits_dir}", $log_file);
    exit(1);
}

// 1. Processar templates
logMessage("--- Processando templates ---", $log_file);
$stats = [
    'renomeados' => 0,
    'nao_encontrados' => 0,
    'ja_corretos' => 0,
    'erros' => 0
];

$stmt = $pdo->query("SELECT id, foto_kit FROM kit_templates WHERE foto_kit IS NOT NULL AND foto_kit != ''");
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($templates as $template) {
    $current_name = basename($template['foto_kit']);
    $extension = pathinfo($current_name, PATHINFO_EXTENSION) ?: 'png';
    $target_name = gerarNomeKit('template', $template['id'], null, $extension);

    if ($current_name === $target_name) {
        logMessage("✅ Template ID {$template['id']} já está normalizado ({$current_name})", $log_file);
        $stats['ja_corretos']++;
        continue;
    }

    $source_path = $kits_dir . $current_name;
    $target_path = $kits_dir . $target_name;

    if (!$dry_run) {
        if (is_file($source_path)) {
            if (rename($source_path, $target_path)) {
                logMessage("📝 Template ID {$template['id']} renomeado: {$current_name} → {$target_name}", $log_file);
                $stats['renomeados']++;
            } else {
                logMessage("  ❌ Erro ao renomear template {$current_name}", $log_file);
                $stats['erros']++;
                continue;
            }
        } else {
            logMessage("⚠️  Arquivo de template não encontrado: {$source_path}", $log_file);
            $stats['nao_encontrados']++;
        }
    } else {
        logMessage("  [DRY RUN] Template {$current_name} → {$target_name}", $log_file);
        $stats['renomeados']++;
    }

    if (!$dry_run) {
        $stmt_update = $pdo->prepare("UPDATE kit_templates SET foto_kit = ? WHERE id = ?");
        $stmt_update->execute([$target_name, $template['id']]);
    }
}

// 2. Processar kits de eventos
logMessage("--- Processando kits_eventos ---", $log_file);
$stmt = $pdo->query("
    SELECT ke.id, ke.evento_id, ke.foto_kit, ke.kit_template_id, kt.foto_kit as template_foto
    FROM kits_eventos ke
    LEFT JOIN kit_templates kt ON kt.id = ke.kit_template_id
    WHERE ke.foto_kit IS NOT NULL AND ke.foto_kit != ''
");
$kits = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($kits as $kit) {
    $current_file = basename($kit['foto_kit']);
    $extension = pathinfo($current_file, PATHINFO_EXTENSION) ?: 'png';
    $target_name = gerarNomeKit('evento', $kit['id'], $kit['evento_id'], $extension);

    if ($current_file === $target_name) {
        logMessage("✅ Kit Evento ID {$kit['id']} já normalizado ({$current_file})", $log_file);
        $stats['ja_corretos']++;
        continue;
    }

    $source_path = $kits_dir . $current_file;
    if (!is_file($source_path) && !empty($kit['template_foto'])) {
        $source_path = $kits_dir . basename($kit['template_foto']);
    }

    $target_path = $kits_dir . $target_name;

    if (!$dry_run) {
        if (is_file($source_path)) {
            if (!copy($source_path, $target_path)) {
                logMessage("  ❌ Erro ao copiar arquivo para evento {$kit['id']}", $log_file);
                $stats['erros']++;
                continue;
            }
            logMessage("📝 Kit Evento ID {$kit['id']} copiado: {$current_file} → {$target_name}", $log_file);
            $stats['renomeados']++;
        } else {
            logMessage("⚠️  Arquivo não encontrado para kit evento {$kit['id']}: buscado em {$source_path}", $log_file);
            $stats['nao_encontrados']++;
        }
        $stmt_update = $pdo->prepare("UPDATE kits_eventos SET foto_kit = ? WHERE id = ?");
        $stmt_update->execute([$target_name, $kit['id']]);
    } else {
        logMessage("  [DRY RUN] Kit Evento {$current_file} → {$target_name}", $log_file);
        $stats['renomeados']++;
    }
}

logMessage("", $log_file);
logMessage("=== RESUMO DA RENOMEAÇÃO ===", $log_file);
logMessage("Arquivos renomeados: {$stats['renomeados']}", $log_file);
logMessage("Arquivos já corretos: {$stats['ja_corretos']}", $log_file);
logMessage("Arquivos não encontrados: {$stats['nao_encontrados']}", $log_file);
logMessage("Erros: {$stats['erros']}", $log_file);
logMessage("Log salvo em: {$log_file}", $log_file);
logMessage("=== FIM DA RENOMEAÇÃO ===", $log_file);

// Retornar JSON se for requisição AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Renomeação concluída',
        'stats' => $stats,
        'log_file' => $log_file
    ]);
}
