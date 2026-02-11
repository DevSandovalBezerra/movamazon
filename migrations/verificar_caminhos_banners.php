<?php
require_once __DIR__ . '/../api/db.php';

$stmt = $pdo->query('SELECT id, titulo, imagem FROM banners ORDER BY id');
$banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Banners no banco de dados:\n\n";
foreach ($banners as $banner) {
    echo "ID: {$banner['id']}\n";
    echo "Título: {$banner['titulo']}\n";
    echo "Imagem: {$banner['imagem']}\n";
    echo "---\n";
}

