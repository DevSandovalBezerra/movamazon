<?php
/**
 * Script de Migração: Corrigir nomes de arquivos de imagens com espaços
 * 
 * Este script corrige arquivos de imagens salvos com nomes contendo espaços,
 * normalizando-os para evitar problemas na renderização em CDN/nuvem.
 * 
 * Tabelas afetadas:
 * - kit_templates (campo foto_kit)
 * - kits_eventos (campo foto_kit)
 * 
 * Uso:
 * - Via CLI: php api/helpers/migrate_image_filenames.php
 * - Via Web: Acessar via browser (requer autenticação admin)
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/file_utils.php';

// Configuração
$dry_run = false; // Se true, apenas simula sem fazer alterações
$log_file = __DIR__ . '/../../logs/migrate_filenames_' . date('Y-m-d_His') . '.log';

// Função de log
function logMessage($message, $log_file) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    echo $log_entry;
}

/**
 * Busca arquivo no diretório de forma flexível
 * Tenta encontrar arquivo mesmo com pequenas variações no nome
 */
function buscarArquivoFlexivel($nome_buscado, $diretorio) {
    if (!is_dir($diretorio)) {
        return null;
    }
    
    $arquivos = scandir($diretorio);
    $nome_buscado_lower = mb_strtolower(trim($nome_buscado));
    $nome_base_buscado = mb_strtolower(pathinfo($nome_buscado, PATHINFO_FILENAME));
    
    $melhor_match = null;
    $melhor_score = 0;
    
    foreach ($arquivos as $arquivo) {
        if ($arquivo === '.' || $arquivo === '..' || is_dir($diretorio . $arquivo)) {
            continue;
        }
        
        $nome_base_arquivo = mb_strtolower(pathinfo($arquivo, PATHINFO_FILENAME));
        
        // Match exato
        if ($nome_base_arquivo === $nome_base_buscado) {
            return $diretorio . $arquivo;
        }
        
        // Match parcial (um contém o outro)
        if (strpos($nome_base_arquivo, $nome_base_buscado) !== false ||
            strpos($nome_base_buscado, $nome_base_arquivo) !== false) {
            $score = min(strlen($nome_base_arquivo), strlen($nome_base_buscado)) / 
                     max(strlen($nome_base_arquivo), strlen($nome_base_buscado), 1);
            if ($score > $melhor_score) {
                $melhor_score = $score;
                $melhor_match = $diretorio . $arquivo;
            }
        }
    }
    
    // Se encontrou match parcial com score > 0.7, usar
    if ($melhor_score > 0.7) {
        return $melhor_match;
    }
    
    return null;
}

// Verificar se é execução via web (requer autenticação)
$is_web = (php_sapi_name() !== 'cli');
if ($is_web) {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores.']);
        exit;
    }
    header('Content-Type: application/json');
}

logMessage("=== INÍCIO DA MIGRAÇÃO DE NOMES DE ARQUIVOS ===", $log_file);
logMessage("Modo: " . ($dry_run ? "DRY RUN (simulação)" : "EXECUÇÃO REAL"), $log_file);

$stats = [
    'kit_templates_encontrados' => 0,
    'kit_templates_corrigidos' => 0,
    'kit_templates_erros' => 0,
    'kits_eventos_encontrados' => 0,
    'kits_eventos_corrigidos' => 0,
    'kits_eventos_erros' => 0,
    'arquivos_renomeados' => 0,
    'arquivos_nao_encontrados' => 0
];

$base_dir = dirname(__DIR__, 2); // Raiz do projeto
$kits_dir = $base_dir . '/frontend/assets/img/kits/';

// ============================================
// 1. CORRIGIR kit_templates
// ============================================
logMessage("\n--- Processando kit_templates ---", $log_file);

$stmt = $pdo->query("SELECT id, nome, foto_kit FROM kit_templates WHERE foto_kit IS NOT NULL AND foto_kit != ''");
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($templates as $template) {
    $foto_kit = $template['foto_kit'];
    $stats['kit_templates_encontrados']++;
    
    // Verificar se contém espaços ou caracteres problemáticos
    if (!preg_match('/[\s]/', $foto_kit) && !preg_match('/[^a-zA-Z0-9._-]/', basename($foto_kit))) {
        // Já está normalizado
        continue;
    }
    
    logMessage("Template ID {$template['id']}: '{$foto_kit}'", $log_file);
    
    // Extrair nome do template
    // Se o arquivo já tem um nome que parece ser do template, tentar extrair dele primeiro
    $nome_template = $template['nome'];
    $nome_arquivo_atual = basename($foto_kit);
    
    // Se o arquivo começa com "kit_template_", tentar extrair o nome original
    if (preg_match('/^kit_template_(.+?)\./', $nome_arquivo_atual, $matches)) {
        // O nome no arquivo pode ser mais preciso que o nome atual do template
        // Mas vamos usar o nome do template para manter consistência
        // (o nome do template pode ter sido alterado)
    }
    
    // Extrair extensão do arquivo atual
    $extensao_atual = pathinfo($foto_kit, PATHINFO_EXTENSION);
    if (empty($extensao_atual)) {
        // Tentar detectar extensão comum
        $extensoes = ['png', 'jpg', 'jpeg', 'webp'];
        $extensao_atual = 'png'; // Default
        foreach ($extensoes as $ext) {
            if (file_exists($kits_dir . basename($foto_kit) . '.' . $ext)) {
                $extensao_atual = $ext;
                break;
            }
        }
    }
    
    // Gerar novo nome normalizado
    $novo_nome = normalizarNomeArquivo($nome_template, 'kit_template_', $extensao_atual);
    
    // Se o nome não mudou, pular
    if (basename($foto_kit) === $novo_nome) {
        logMessage("  → Já está normalizado, pulando", $log_file);
        continue;
    }
    
    logMessage("  → Novo nome: '{$novo_nome}'", $log_file);
    
    // Determinar caminho do arquivo antigo (pode estar com ou sem caminho completo)
    $arquivo_antigo = null;
    $nome_arquivo_antigo = basename($foto_kit);
    
    // Tentar diferentes variações de caminho
    $caminhos_tentados = [];
    
    // 1. Se foto_kit contém caminho completo (frontend/assets/img/kits/...)
    if (strpos($foto_kit, 'frontend/') === 0 || strpos($foto_kit, '/frontend/') === 0) {
        $caminho_completo = $base_dir . '/' . ltrim($foto_kit, '/');
        $caminhos_tentados[] = $caminho_completo;
        if (file_exists($caminho_completo)) {
            $arquivo_antigo = $caminho_completo;
        }
    }
    
    // 2. Tentar apenas nome do arquivo no diretório kits
    if (!$arquivo_antigo) {
        $caminho_simples = $kits_dir . $nome_arquivo_antigo;
        $caminhos_tentados[] = $caminho_simples;
        if (file_exists($caminho_simples)) {
            $arquivo_antigo = $caminho_simples;
        }
    }
    
    // 3. Tentar variações de extensão
    if (!$arquivo_antigo) {
        $nome_base_antigo = pathinfo($nome_arquivo_antigo, PATHINFO_FILENAME);
        foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
            $candidato = $kits_dir . $nome_base_antigo . '.' . $ext;
            $caminhos_tentados[] = $candidato;
            if (file_exists($candidato)) {
                $arquivo_antigo = $candidato;
                $extensao_atual = $ext;
                $novo_nome = normalizarNomeArquivo($nome_template, 'kit_template_', $ext);
                break;
            }
        }
    }
    
    // 4. Busca flexível usando função auxiliar
    if (!$arquivo_antigo) {
        $arquivo_encontrado = buscarArquivoFlexivel($nome_arquivo_antigo, $kits_dir);
        if ($arquivo_encontrado) {
            $arquivo_antigo = $arquivo_encontrado;
            $extensao_atual = pathinfo($arquivo_encontrado, PATHINFO_EXTENSION) ?: 'png';
            $novo_nome = normalizarNomeArquivo($nome_template, 'kit_template_', $extensao_atual);
            logMessage("  ℹ️  Arquivo encontrado por busca flexível: " . basename($arquivo_encontrado), $log_file);
        }
    }
    
    if (!$arquivo_antigo) {
        logMessage("  ⚠️  Arquivo físico não encontrado. Tentados: " . implode(', ', array_slice($caminhos_tentados, 0, 3)) . (count($caminhos_tentados) > 3 ? '...' : ''), $log_file);
        logMessage("  ℹ️  Atualizando apenas banco de dados com nome normalizado", $log_file);
        
        // Mesmo sem arquivo físico, atualizar banco para normalizar o nome
        // Isso garante que futuros uploads usem o nome correto
        if (!$dry_run) {
            $stmt_update = $pdo->prepare("UPDATE kit_templates SET foto_kit = ? WHERE id = ?");
            if ($stmt_update->execute([$novo_nome, $template['id']])) {
                logMessage("  ✅ Banco atualizado com: {$novo_nome}", $log_file);
                $stats['kit_templates_corrigidos']++;
            } else {
                logMessage("  ❌ Erro ao atualizar banco: " . implode(', ', $stmt_update->errorInfo()), $log_file);
                $stats['kit_templates_erros']++;
            }
        } else {
            logMessage("  [DRY RUN] Seria atualizado no banco: {$novo_nome}", $log_file);
            $stats['kit_templates_corrigidos']++;
        }
        $stats['arquivos_nao_encontrados']++;
        continue;
    }
    
    // Caminho do arquivo novo (sempre apenas nome do arquivo no diretório kits)
    $arquivo_novo = $kits_dir . $novo_nome;
    
    // Verificar se arquivo novo já existe
    if (file_exists($arquivo_novo) && $arquivo_novo !== $arquivo_antigo) {
        logMessage("  ⚠️  Arquivo novo já existe, pulando renomeação", $log_file);
        // Atualizar apenas o banco se o arquivo já existe com nome correto
        if (!$dry_run) {
            $stmt_update = $pdo->prepare("UPDATE kit_templates SET foto_kit = ? WHERE id = ?");
            $stmt_update->execute([$novo_nome, $template['id']]);
            $stats['kit_templates_corrigidos']++;
            logMessage("  ✅ Banco atualizado para: {$novo_nome}", $log_file);
        }
        continue;
    }
    
    // Renomear arquivo
    if (!$dry_run) {
        if (rename($arquivo_antigo, $arquivo_novo)) {
            logMessage("  ✅ Arquivo renomeado: " . basename($arquivo_antigo) . " → {$novo_nome}", $log_file);
            $stats['arquivos_renomeados']++;
            
            // Atualizar banco de dados (salvar apenas nome do arquivo, sem caminho)
            $stmt_update = $pdo->prepare("UPDATE kit_templates SET foto_kit = ? WHERE id = ?");
            if ($stmt_update->execute([$novo_nome, $template['id']])) {
                logMessage("  ✅ Banco atualizado com: {$novo_nome}", $log_file);
                $stats['kit_templates_corrigidos']++;
            } else {
                logMessage("  ❌ Erro ao atualizar banco: " . implode(', ', $stmt_update->errorInfo()), $log_file);
                $stats['kit_templates_erros']++;
                // Tentar reverter renomeação
                @rename($arquivo_novo, $arquivo_antigo);
            }
        } else {
            logMessage("  ❌ Erro ao renomear arquivo", $log_file);
            $stats['kit_templates_erros']++;
        }
    } else {
        logMessage("  [DRY RUN] Seria renomeado: " . basename($arquivo_antigo) . " → {$novo_nome}", $log_file);
        $stats['kit_templates_corrigidos']++;
    }
}

// ============================================
// 2. CORRIGIR kits_eventos
// ============================================
logMessage("\n--- Processando kits_eventos ---", $log_file);

$stmt = $pdo->query("
    SELECT ke.id, ke.nome, ke.foto_kit, ke.kit_template_id, kt.nome as template_nome
    FROM kits_eventos ke
    LEFT JOIN kit_templates kt ON ke.kit_template_id = kt.id
    WHERE ke.foto_kit IS NOT NULL AND ke.foto_kit != ''
");
$kits = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($kits as $kit) {
    $foto_kit = $kit['foto_kit'];
    $stats['kits_eventos_encontrados']++;
    
    // Verificar se contém espaços ou caracteres problemáticos
    if (!preg_match('/[\s]/', $foto_kit) && !preg_match('/[^a-zA-Z0-9._-]/', basename($foto_kit))) {
        // Já está normalizado
        continue;
    }
    
    logMessage("Kit Evento ID {$kit['id']}: '{$foto_kit}'", $log_file);
    
    // Extrair nome base do arquivo atual (remover prefixo e extensão)
    // IMPORTANTE: Para kits_eventos, priorizar o nome do arquivo atual,
    // pois ele pode ser diferente do template (ex: arquivo genérico usado em múltiplos eventos)
    $nome_arquivo_atual = basename($foto_kit);
    $nome_base = null;
    
    // Primeiro, SEMPRE tentar extrair do nome do arquivo atual
    if (preg_match('/^kit_template_(.+?)\./', $nome_arquivo_atual, $matches)) {
        // Usar o nome extraído do arquivo (mais confiável para kits_eventos)
        // O arquivo pode ter sido salvo com um nome genérico que não corresponde ao template
        $nome_base = $matches[1];
    } elseif ($kit['kit_template_id'] && $kit['template_nome']) {
        // Se não conseguiu extrair do arquivo, usar nome do template
        $nome_base = $kit['template_nome'];
    } else {
        // Tentar extrair do nome do kit
        // Ex: "Kit Atleta - CORRIDA 10KM" → "Kit Atleta"
        if (preg_match('/^(Kit\s+[^-]+)/i', $kit['nome'], $matches)) {
            $nome_base = trim($matches[1]);
        } else {
            $nome_base = $kit['nome'];
        }
    }
    
    // Extrair extensão
    $extensao_atual = pathinfo($foto_kit, PATHINFO_EXTENSION);
    if (empty($extensao_atual)) {
        $extensao_atual = 'png'; // Default
    }
    
    // Gerar novo nome normalizado
    $novo_nome = normalizarNomeArquivo($nome_base, 'kit_template_', $extensao_atual);
    
    // Se o nome não mudou, pular
    if (basename($foto_kit) === $novo_nome) {
        logMessage("  → Já está normalizado, pulando", $log_file);
        continue;
    }
    
    logMessage("  → Novo nome: '{$novo_nome}'", $log_file);
    
    // Determinar caminho do arquivo antigo (mesma lógica do template)
    $arquivo_antigo = null;
    $nome_arquivo_antigo = basename($foto_kit);
    $caminhos_tentados = [];
    
    // 1. Se foto_kit contém caminho completo
    if (strpos($foto_kit, 'frontend/') === 0 || strpos($foto_kit, '/frontend/') === 0) {
        $caminho_completo = $base_dir . '/' . ltrim($foto_kit, '/');
        $caminhos_tentados[] = $caminho_completo;
        if (file_exists($caminho_completo)) {
            $arquivo_antigo = $caminho_completo;
        }
    }
    
    // 2. Tentar apenas nome do arquivo
    if (!$arquivo_antigo) {
        $caminho_simples = $kits_dir . $nome_arquivo_antigo;
        $caminhos_tentados[] = $caminho_simples;
        if (file_exists($caminho_simples)) {
            $arquivo_antigo = $caminho_simples;
        }
    }
    
    // 3. Tentar variações de extensão
    if (!$arquivo_antigo) {
        $nome_base_antigo = pathinfo($nome_arquivo_antigo, PATHINFO_FILENAME);
        foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
            $candidato = $kits_dir . $nome_base_antigo . '.' . $ext;
            $caminhos_tentados[] = $candidato;
            if (file_exists($candidato)) {
                $arquivo_antigo = $candidato;
                $extensao_atual = $ext;
                $novo_nome = normalizarNomeArquivo($nome_base, 'kit_template_', $ext);
                break;
            }
        }
    }
    
    // 4. Busca flexível usando função auxiliar
    if (!$arquivo_antigo) {
        $arquivo_encontrado = buscarArquivoFlexivel($nome_arquivo_antigo, $kits_dir);
        if ($arquivo_encontrado) {
            $arquivo_antigo = $arquivo_encontrado;
            $extensao_atual = pathinfo($arquivo_encontrado, PATHINFO_EXTENSION) ?: 'png';
            $novo_nome = normalizarNomeArquivo($nome_base, 'kit_template_', $extensao_atual);
            logMessage("  ℹ️  Arquivo encontrado por busca flexível: " . basename($arquivo_encontrado), $log_file);
        }
    }
    
    if (!$arquivo_antigo) {
        logMessage("  ⚠️  Arquivo físico não encontrado. Tentados: " . implode(', ', array_slice($caminhos_tentados, 0, 3)) . (count($caminhos_tentados) > 3 ? '...' : ''), $log_file);
        logMessage("  ℹ️  Atualizando apenas banco de dados com nome normalizado", $log_file);
        
        // Mesmo sem arquivo físico, atualizar banco para normalizar o nome
        if (!$dry_run) {
            $stmt_update = $pdo->prepare("UPDATE kits_eventos SET foto_kit = ? WHERE id = ?");
            if ($stmt_update->execute([$novo_nome, $kit['id']])) {
                logMessage("  ✅ Banco atualizado com: {$novo_nome}", $log_file);
                $stats['kits_eventos_corrigidos']++;
            } else {
                logMessage("  ❌ Erro ao atualizar banco: " . implode(', ', $stmt_update->errorInfo()), $log_file);
                $stats['kits_eventos_erros']++;
            }
        } else {
            logMessage("  [DRY RUN] Seria atualizado no banco: {$novo_nome}", $log_file);
            $stats['kits_eventos_corrigidos']++;
        }
        $stats['arquivos_nao_encontrados']++;
        continue;
    }
    
    // Caminho do arquivo novo
    $arquivo_novo = $kits_dir . $novo_nome;
    
    // Verificar se arquivo novo já existe
    if (file_exists($arquivo_novo) && $arquivo_novo !== $arquivo_antigo) {
        logMessage("  ⚠️  Arquivo novo já existe, pulando renomeação", $log_file);
        if (!$dry_run) {
            $stmt_update = $pdo->prepare("UPDATE kits_eventos SET foto_kit = ? WHERE id = ?");
            $stmt_update->execute([$novo_nome, $kit['id']]);
            $stats['kits_eventos_corrigidos']++;
            logMessage("  ✅ Banco atualizado para: {$novo_nome}", $log_file);
        }
        continue;
    }
    
    // Renomear arquivo
    if (!$dry_run) {
        if (rename($arquivo_antigo, $arquivo_novo)) {
            logMessage("  ✅ Arquivo renomeado: " . basename($arquivo_antigo) . " → {$novo_nome}", $log_file);
            $stats['arquivos_renomeados']++;
            
            // Atualizar banco de dados (salvar apenas nome do arquivo, sem caminho)
            $stmt_update = $pdo->prepare("UPDATE kits_eventos SET foto_kit = ? WHERE id = ?");
            if ($stmt_update->execute([$novo_nome, $kit['id']])) {
                logMessage("  ✅ Banco atualizado com: {$novo_nome}", $log_file);
                $stats['kits_eventos_corrigidos']++;
            } else {
                logMessage("  ❌ Erro ao atualizar banco: " . implode(', ', $stmt_update->errorInfo()), $log_file);
                $stats['kits_eventos_erros']++;
                @rename($arquivo_novo, $arquivo_antigo);
            }
        } else {
            logMessage("  ❌ Erro ao renomear arquivo", $log_file);
            $stats['kits_eventos_erros']++;
        }
    } else {
        logMessage("  [DRY RUN] Seria renomeado: " . basename($arquivo_antigo) . " → {$novo_nome}", $log_file);
        $stats['kits_eventos_corrigidos']++;
    }
}

// ============================================
// RESUMO FINAL
// ============================================
logMessage("\n=== RESUMO DA MIGRAÇÃO ===", $log_file);
logMessage("kit_templates:", $log_file);
logMessage("  - Encontrados: {$stats['kit_templates_encontrados']}", $log_file);
logMessage("  - Corrigidos: {$stats['kit_templates_corrigidos']}", $log_file);
logMessage("  - Erros: {$stats['kit_templates_erros']}", $log_file);
logMessage("kits_eventos:", $log_file);
logMessage("  - Encontrados: {$stats['kits_eventos_encontrados']}", $log_file);
logMessage("  - Corrigidos: {$stats['kits_eventos_corrigidos']}", $log_file);
logMessage("  - Erros: {$stats['kits_eventos_erros']}", $log_file);
logMessage("Arquivos:", $log_file);
logMessage("  - Renomeados: {$stats['arquivos_renomeados']}", $log_file);
logMessage("  - Não encontrados: {$stats['arquivos_nao_encontrados']}", $log_file);
logMessage("Log salvo em: {$log_file}", $log_file);
logMessage("=== FIM DA MIGRAÇÃO ===", $log_file);

if ($is_web) {
    echo json_encode([
        'success' => true,
        'message' => 'Migração concluída',
        'stats' => $stats,
        'log_file' => $log_file
    ]);
}
