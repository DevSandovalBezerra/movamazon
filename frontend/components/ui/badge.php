<?php
/**
 * Componente Badge baseado em shadcn/ui
 * Adaptado para PHP
 * 
 * @param string $content - Conteúdo do badge
 * @param array $options - Opções do badge
 * @return string HTML do badge
 */
function shadcn_badge($content, $options = []) {
    $variant = $options['variant'] ?? 'default';
    $class = $options['class'] ?? '';
    $icon = $options['icon'] ?? '';
    
    // Classes base
    $baseClasses = "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-gray-950 focus:ring-offset-2";
    
    // Variantes
    $variants = [
        'default' => 'border-transparent bg-gray-900 text-white hover:bg-gray-800',
        'secondary' => 'border-transparent bg-gray-100 text-gray-900 hover:bg-gray-200',
        'success' => 'border-transparent bg-green-100 text-green-800 hover:bg-green-200',
        'destructive' => 'border-transparent bg-red-100 text-red-800 hover:bg-red-200',
        'warning' => 'border-transparent bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
        'outline' => 'text-gray-950 border-gray-300',
        // Variantes customizadas para o projeto
        'active' => 'border-transparent bg-green-100 text-green-800',
        'inactive' => 'border-transparent bg-gray-100 text-gray-600',
        'pending' => 'border-transparent bg-yellow-100 text-yellow-800',
    ];
    
    $variantClass = $variants[$variant] ?? $variants['default'];
    $classes = trim("$baseClasses $variantClass $class");
    
    $iconHtml = $icon ? "<i class=\"$icon mr-1\"></i>" : '';
    
    return "<span class=\"$classes\">$iconHtml$content</span>";
}
?>

