<?php
/**
 * Script para testar se as imagens dos banners estão acessíveis
 */

require_once __DIR__ . '/../api/db.php';

echo "=== TESTE DE IMAGENS DOS BANNERS ===\n\n";

try {
    $stmt = $pdo->query("SELECT id, titulo, imagem FROM banners ORDER BY id");
    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $baseDir = dirname(__DIR__);
    $total = count($banners);
    $ok = 0;
    $erro = 0;
    
    foreach ($banners as $banner) {
        echo "Banner ID: {$banner['id']}\n";
        echo "Título: {$banner['titulo']}\n";
        echo "Caminho no banco: {$banner['imagem']}\n";
        
        // Normalizar caminho
        $caminho = $banner['imagem'] ?? '';
        if ($caminho && !str_starts_with($caminho, 'http://') && !str_starts_with($caminho, 'https://')) {
            $caminho = '/' . ltrim($caminho, '/');
        }
        
        // Caminho físico
        $caminhoFisico = $baseDir . $caminho;
        
        echo "Caminho normalizado: {$caminho}\n";
        echo "Caminho físico: {$caminhoFisico}\n";
        
        if (file_exists($caminhoFisico)) {
            $tamanho = filesize($caminhoFisico);
            echo "✓ Arquivo existe ({$tamanho} bytes)\n";
            $ok++;
        } else {
            echo "✗ ERRO: Arquivo NÃO existe!\n";
            $erro++;
        }
        
        // Testar URL relativa
        $urlRelativa = $caminho;
        echo "URL relativa para browser: {$urlRelativa}\n";
        echo "---\n\n";
    }
    
    echo "\n=== RESUMO ===\n";
    echo "Total de banners: {$total}\n";
    echo "Imagens OK: {$ok}\n";
    echo "Imagens com erro: {$erro}\n";
    
    if ($erro > 0) {
        echo "\n⚠ ATENÇÃO: Algumas imagens não foram encontradas!\n";
        exit(1);
    } else {
        echo "\n✓ Todas as imagens estão acessíveis!\n";
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}

