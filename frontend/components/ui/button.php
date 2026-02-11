<?php
/**
 * Componente Button baseado em shadcn/ui
 * Adaptado para PHP
 * 
 * @param string $content - Conteúdo do botão (texto ou HTML)
 * @param array $options - Opções do botão
 * @return string HTML do botão
 */
function shadcn_button($content, $options = []) {
    $variant = $options['variant'] ?? 'primary';
    $size = $options['size'] ?? 'default';
    $class = $options['class'] ?? '';
    $type = $options['type'] ?? 'button';
    $disabled = isset($options['disabled']) && $options['disabled'];
    $onclick = $options['onclick'] ?? '';
    $id = $options['id'] ?? '';
    $icon = $options['icon'] ?? '';
    $iconPosition = $options['iconPosition'] ?? 'left'; // left, right
    
    // Classes base do shadcn/ui button
    $baseClasses = "inline-flex items-center justify-center rounded-md font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none";
    
    // Variantes de estilo
    $variants = [
        'primary' => 'bg-brand-green text-white hover:bg-[#065f5a] focus-visible:ring-brand-green',
        'secondary' => 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus-visible:ring-gray-400',
        'destructive' => 'bg-brand-red text-white hover:bg-[#8a181a] focus-visible:ring-brand-red',
        'outline' => 'border border-gray-300 bg-transparent hover:bg-gray-100 focus-visible:ring-gray-400',
        'ghost' => 'hover:bg-gray-100 focus-visible:ring-gray-400',
        'link' => 'text-brand-green underline-offset-4 hover:underline focus-visible:ring-brand-green'
    ];
    
    // Tamanhos
    $sizes = [
        'default' => 'h-10 py-2 px-4',
        'sm' => 'h-9 px-3 text-sm rounded-md',
        'lg' => 'h-11 px-8 rounded-md',
        'icon' => 'h-10 w-10'
    ];
    
    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass = $sizes[$size] ?? $sizes['default'];
    
    $classes = trim("$baseClasses $variantClass $sizeClass $class");
    
    // Atributos
    $attrs = [];
    if ($id) $attrs[] = "id=\"$id\"";
    if ($disabled) $attrs[] = 'disabled';
    if ($onclick) $attrs[] = "onclick=\"$onclick\"";
    $attrsStr = !empty($attrs) ? ' ' . implode(' ', $attrs) : '';
    
    // Ícone
    $iconHtml = '';
    if ($icon) {
        $iconClass = $size === 'sm' ? 'text-sm' : '';
        $iconHtml = "<i class=\"$icon $iconClass\"></i>";
    }
    
    // Conteúdo com ícone
    $buttonContent = '';
    if ($icon && $iconPosition === 'left') {
        $buttonContent = $iconHtml . ($content ? "<span class=\"ml-2\">$content</span>" : '');
    } elseif ($icon && $iconPosition === 'right') {
        $buttonContent = ($content ? "<span class=\"mr-2\">$content</span>" : '') . $iconHtml;
    } else {
        $buttonContent = $content;
    }
    
    return "<button type=\"$type\" class=\"$classes\"$attrsStr>$buttonContent</button>";
}
?>

