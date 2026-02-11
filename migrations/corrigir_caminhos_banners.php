<?php
/**
 * Script para corrigir caminhos de imagens dos banners no banco de dados
 * Remove "http://localhost" e normaliza os caminhos
 */

require_once __DIR__ . '/../api/db.php';

try {
    $stmt = $pdo->query("SELECT id, imagem FROM banners WHERE imagem IS NOT NULL AND imagem != ''");
    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Encontrados " . count($banners) . " banners com imagens.\n\n";
    
    $corrigidos = 0;
    foreach ($banners as $banner) {
        $caminhoOriginal = $banner['imagem'];
        $caminhoCorrigido = $caminhoOriginal;
        
        // Remover http://localhost se existir
        $caminhoCorrigido = preg_replace('#^https?://localhost#', '', $caminhoCorrigido);
        
        // Remover http://127.0.0.1 se existir
        $caminhoCorrigido = preg_replace('#^https?://127\.0\.0\.1#', '', $caminhoCorrigido);
        
        // Garantir que comece com /
        if (!empty($caminhoCorrigido) && strpos($caminhoCorrigido, 'http://') !== 0 && strpos($caminhoCorrigido, 'https://') !== 0) {
            $caminhoCorrigido = '/' . ltrim($caminhoCorrigido, '/');
        }
        
        // Se mudou, atualizar no banco
        if ($caminhoCorrigido !== $caminhoOriginal) {
            $updateStmt = $pdo->prepare("UPDATE banners SET imagem = :imagem WHERE id = :id");
            $updateStmt->execute([
                'imagem' => $caminhoCorrigido,
                'id' => $banner['id']
            ]);
            
            echo "Banner ID {$banner['id']}:\n";
            echo "  Antes: {$caminhoOriginal}\n";
            echo "  Depois: {$caminhoCorrigido}\n\n";
            $corrigidos++;
        }
    }
    
    if ($corrigidos === 0) {
        echo "Nenhum caminho precisou ser corrigido. Todos os caminhos já estão corretos.\n";
    } else {
        echo "Total de {$corrigidos} banner(s) corrigido(s).\n";
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}

