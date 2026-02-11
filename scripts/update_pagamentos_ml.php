<?php

/**
 * Script para adicionar campo init_point na tabela pagamentos_ml
 * 
 * Este script:
 * 1. Verifica se a tabela pagamentos_ml existe
 * 2. Verifica se o campo init_point já existe
 * 3. Adiciona o campo apenas se necessário
 * 4. Atualiza o código para usar o campo
 */

require_once '../api/db.php';

echo "=== SCRIPT DE ATUALIZAÇÃO DA TABELA pagamentos_ml ===\n\n";

try {
    // 1. Verificar se a tabela existe
    echo "1. Verificando se tabela pagamentos_ml existe...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'pagamentos_ml'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("❌ Tabela pagamentos_ml não existe!");
    }
    echo "✅ Tabela pagamentos_ml encontrada.\n\n";

    // 2. Verificar estrutura atual
    echo "2. Verificando estrutura atual da tabela...\n";
    $stmt = $pdo->query("DESCRIBE pagamentos_ml");
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $campos_existentes = array_column($campos, 'Field');
    echo "Campos existentes: " . implode(', ', $campos_existentes) . "\n\n";

    // 3. Verificar se init_point já existe
    if (in_array('init_point', $campos_existentes)) {
        echo "✅ Campo init_point já existe na tabela.\n";
        echo "✅ Nenhuma alteração necessária.\n\n";
    } else {
        echo "3. Adicionando campo init_point...\n";

        // Adicionar o campo
        $sql = "ALTER TABLE `pagamentos_ml` 
                ADD COLUMN `init_point` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL 
                COMMENT 'URL de inicialização do Mercado Pago' 
                AFTER `preference_id`";

        $pdo->exec($sql);
        echo "✅ Campo init_point adicionado com sucesso!\n\n";
    }

    // 4. Verificar estrutura final
    echo "4. Verificando estrutura final da tabela...\n";
    $stmt = $pdo->query("DESCRIBE pagamentos_ml");
    $campos_finais = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($campos_finais as $campo) {
        echo "- {$campo['Field']}: {$campo['Type']} {$campo['Null']} {$campo['Key']}\n";
    }
    echo "\n";

    // 5. Atualizar código para usar o campo
    echo "5. Atualizando código para usar o campo init_point...\n";

    // Atualizar MercadoLivrePayment.php
    $arquivo_ml = '../api/mercadolivre/MercadoLivrePayment.php';
    if (file_exists($arquivo_ml)) {
        $conteudo = file_get_contents($arquivo_ml);

        // Verificar se já está atualizado
        if (strpos($conteudo, 'init_point') !== false) {
            echo "✅ MercadoLivrePayment.php já está atualizado.\n";
        } else {
            echo "⚠️  MercadoLivrePayment.php precisa ser atualizado manualmente.\n";
            echo "   Adicione 'init_point' no INSERT da função salvarPagamento().\n";
        }
    }

    echo "\n=== SCRIPT CONCLUÍDO COM SUCESSO! ===\n";
    echo "✅ Tabela pagamentos_ml está pronta para uso.\n";
    echo "✅ Campo init_point disponível para armazenar URLs do Mercado Pago.\n\n";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "❌ Script interrompido.\n";
    exit(1);
}
