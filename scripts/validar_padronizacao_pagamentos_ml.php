<?php
/**
 * Script de validação para verificar padronização da tabela pagamentos_ml
 * 
 * Este script verifica:
 * 1. Se não existem mais referências à tabela payments_ml
 * 2. Se todos os status usados estão no enum correto
 * 3. Se todas as colunas referenciadas existem na tabela
 */

require_once __DIR__ . '/../api/db.php';

echo "=== VALIDAÇÃO DE PADRONIZAÇÃO DA TABELA PAGAMENTOS_ML ===\n\n";

$erros = [];
$avisos = [];
$sucessos = [];

// 1. Verificar estrutura da tabela pagamentos_ml
echo "1. Verificando estrutura da tabela pagamentos_ml...\n";
try {
    $stmt = $pdo->query("DESCRIBE pagamentos_ml");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $colunas_nomes = array_column($colunas, 'Field');
    $status_enum = null;
    
    foreach ($colunas as $coluna) {
        if ($coluna['Field'] === 'status') {
            // Extrair valores do enum - suporta diferentes formatos
            $type = $coluna['Type'];
            
            // Debug: mostrar o tipo encontrado
            if (stripos($type, 'enum') === false) {
                error_log("Tipo da coluna status não é enum: " . $type);
            }
            
            // Tentar diferentes padrões de enum
            if (preg_match("/enum\s*\(\s*'([^']+)'\s*\)/i", $type, $matches)) {
                $status_enum = explode("','", $matches[1]);
            } elseif (preg_match("/enum\s*\(\s*\"([^\"]+)\"\s*\)/i", $type, $matches)) {
                $status_enum = explode("\",\"", $matches[1]);
            } elseif (preg_match("/enum\s*\(([^)]+)\)/i", $type, $matches)) {
                // Formato mais genérico - remover aspas e dividir
                $values = $matches[1];
                // Remover todas as aspas
                $values = preg_replace("/['\"]/", "", $values);
                // Dividir por vírgula
                $status_enum = array_map('trim', explode(',', $values));
            }
            
            // Se ainda não encontrou, tentar método direto
            if (!$status_enum && stripos($type, 'enum') !== false) {
                // Extrair tudo entre parênteses
                if (preg_match("/\((.+)\)/", $type, $matches)) {
                    $values_str = $matches[1];
                    // Remover aspas e espaços
                    $values_str = preg_replace("/['\"]/", "", $values_str);
                    $status_enum = array_map('trim', explode(',', $values_str));
                }
            }
        }
    }
    
    if ($status_enum && is_array($status_enum) && count($status_enum) > 0) {
        echo "   ✅ Tabela pagamentos_ml encontrada\n";
        echo "   ✅ Status enum: " . implode(', ', $status_enum) . "\n";
        $sucessos[] = "Tabela pagamentos_ml existe com enum correto";
    } else {
        // Tentar consulta direta para ver o tipo real
        try {
            $stmt_type = $pdo->query("SHOW COLUMNS FROM pagamentos_ml WHERE Field = 'status'");
            $col_status = $stmt_type->fetch(PDO::FETCH_ASSOC);
            if ($col_status) {
                $erros[] = "Não foi possível extrair enum de status. Tipo encontrado: " . $col_status['Type'];
            } else {
                $erros[] = "Coluna 'status' não encontrada na tabela pagamentos_ml";
            }
        } catch (Exception $e) {
            $erros[] = "Erro ao verificar tipo da coluna status: " . $e->getMessage();
        }
    }
    
    // Colunas esperadas
    $colunas_esperadas = [
        'id', 'inscricao_id', 'preference_id', 'payment_id', 'init_point',
        'status', 'data_criacao', 'data_atualizacao', 'dados_pagamento',
        'valor_pago', 'metodo_pagamento', 'parcelas', 'taxa_ml', 'user_id', 'created'
    ];
    
    $colunas_faltantes = array_diff($colunas_esperadas, $colunas_nomes);
    if (empty($colunas_faltantes)) {
        echo "   ✅ Todas as colunas esperadas estão presentes\n";
        $sucessos[] = "Todas as colunas esperadas existem";
    } else {
        $erros[] = "Colunas faltantes: " . implode(', ', $colunas_faltantes);
    }
    
} catch (Exception $e) {
    $erros[] = "Erro ao verificar estrutura da tabela: " . $e->getMessage();
}

// 2. Verificar se tabela payments_ml existe (não deveria)
echo "\n2. Verificando se tabela payments_ml existe (não deveria)...\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'payments_ml'");
    if ($stmt->rowCount() > 0) {
        $avisos[] = "Tabela payments_ml ainda existe no banco de dados (deve ser removida ou renomeada)";
        echo "   ⚠️  Tabela payments_ml ainda existe\n";
    } else {
        echo "   ✅ Tabela payments_ml não existe (correto)\n";
        $sucessos[] = "Tabela payments_ml não existe";
    }
} catch (Exception $e) {
    $erros[] = "Erro ao verificar tabela payments_ml: " . $e->getMessage();
}

// 3. Buscar referências a payments_ml no código PHP
echo "\n3. Buscando referências a 'payments_ml' no código...\n";
$diretorios = [
    __DIR__ . '/../api',
    __DIR__ . '/../frontend',
    __DIR__ . '/../scripts'
];

$referencias_encontradas = [];
foreach ($diretorios as $dir) {
    if (!is_dir($dir)) continue;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filepath = $file->getPathname();
            $filepath_relativo = str_replace(__DIR__ . '/../', '', $filepath);
            
            // Ignorar referências no próprio script de validação
            if (basename($filepath) === 'validar_padronizacao_pagamentos_ml.php') {
                continue;
            }
            
            $content = file_get_contents($filepath);
            if (preg_match_all('/payments_ml/i', $content, $matches)) {
                $linhas = explode("\n", $content);
                foreach ($linhas as $num => $linha) {
                    if (stripos($linha, 'payments_ml') !== false) {
                        $referencias_encontradas[] = [
                            'arquivo' => $filepath_relativo,
                            'linha' => $num + 1,
                            'conteudo' => trim($linha)
                        ];
                    }
                }
            }
        }
    }
}

if (empty($referencias_encontradas)) {
    echo "   ✅ Nenhuma referência a 'payments_ml' encontrada no código\n";
    $sucessos[] = "Nenhuma referência a payments_ml no código";
} else {
    echo "   ⚠️  Encontradas " . count($referencias_encontradas) . " referências a 'payments_ml':\n";
    foreach ($referencias_encontradas as $ref) {
        echo "      - {$ref['arquivo']}:{$ref['linha']} - {$ref['conteudo']}\n";
        $avisos[] = "Referência a payments_ml em {$ref['arquivo']}:{$ref['linha']}";
    }
}

// 4. Verificar status usados no código vs enum da tabela
echo "\n4. Verificando status usados no código...\n";
if ($status_enum) {
    $status_encontrados = [];
    foreach ($diretorios as $dir) {
        if (!is_dir($dir)) continue;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                // Buscar padrões como 'approved', 'pending', etc. em contexto de status
                if (preg_match_all("/(?:status.*=.*['\"])(approved|pending|in_process|rejected|cancelled|refunded)(['\"])/i", $content, $matches)) {
                    foreach ($matches[1] as $status) {
                        $status_encontrados[strtolower($status)] = true;
                    }
                }
            }
        }
    }
    
    $status_invalidos = [];
    foreach ($status_encontrados as $status => $_) {
        // Verificar se é status em inglês que deveria ser português
        $status_pt = [
            'approved' => 'pago',
            'pending' => 'pendente',
            'in_process' => 'processando',
            'rejected' => 'rejeitado',
            'cancelled' => 'cancelado',
            'refunded' => 'cancelado'
        ];
        
        if (isset($status_pt[$status]) && !in_array($status_pt[$status], $status_enum)) {
            $status_invalidos[] = $status;
        }
    }
    
    if (empty($status_invalidos)) {
        echo "   ✅ Status usados no código são compatíveis com enum da tabela\n";
        $sucessos[] = "Status compatíveis com enum";
    } else {
        $avisos[] = "Status em inglês encontrados que precisam ser convertidos: " . implode(', ', $status_invalidos);
        echo "   ⚠️  Status em inglês encontrados: " . implode(', ', $status_invalidos) . "\n";
    }
}

// Resumo
echo "\n=== RESUMO ===\n";
echo "✅ Sucessos: " . count($sucessos) . "\n";
echo "⚠️  Avisos: " . count($avisos) . "\n";
echo "❌ Erros: " . count($erros) . "\n\n";

if (!empty($sucessos)) {
    echo "Sucessos:\n";
    foreach ($sucessos as $sucesso) {
        echo "  ✅ $sucesso\n";
    }
    echo "\n";
}

if (!empty($avisos)) {
    echo "Avisos:\n";
    foreach ($avisos as $aviso) {
        echo "  ⚠️  $aviso\n";
    }
    echo "\n";
}

if (!empty($erros)) {
    echo "Erros:\n";
    foreach ($erros as $erro) {
        echo "  ❌ $erro\n";
    }
    echo "\n";
}

if (empty($erros) && empty($avisos)) {
    echo "✅ VALIDAÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo "A padronização da tabela pagamentos_ml está correta.\n";
    exit(0);
} else {
    echo "⚠️  VALIDAÇÃO CONCLUÍDA COM AVISOS/ERROS\n";
    echo "Revise os itens acima antes de prosseguir.\n";
    exit(1);
}
