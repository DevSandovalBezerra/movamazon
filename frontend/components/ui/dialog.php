<?php
/**
 * Componente Dialog (Modal) baseado em shadcn/ui
 * Adaptado para PHP
 * 
 * @param string $id - ID único do dialog
 * @param string $title - Título do dialog
 * @param string $content - Conteúdo do dialog
 * @param string $footer - Rodapé (botões) do dialog
 * @param array $options - Opções adicionais
 * @return string HTML do dialog
 */
function shadcn_dialog($id, $title, $content, $footer = '', $options = []) {
    $open = isset($options['open']) && $options['open'];
    $size = $options['size'] ?? 'default';
    $class = $options['class'] ?? '';
    $showClose = $options['showClose'] ?? true;
    
    // Tamanhos do dialog
    $sizeClasses = [
        'sm' => 'max-w-sm',
        'default' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-full mx-4'
    ];
    
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['default'];
    $displayClass = $open ? '' : 'hidden';
    
    $closeButton = $showClose ? '
        <button 
            class="rounded-sm opacity-70 ring-offset-white transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-gray-950 focus:ring-offset-2 disabled:pointer-events-none" 
            data-dialog-close="' . $id . '"
            aria-label="Fechar"
        >
            <i class="fas fa-times text-gray-500"></i>
        </button>
    ' : '';
    
    return "
    <div id=\"$id\" class=\"$displayClass fixed inset-0 z-50 flex items-center justify-center\" role=\"dialog\" aria-modal=\"true\" aria-labelledby=\"{$id}-title\">
        <!-- Overlay -->
        <div class=\"fixed inset-0 bg-black/50 transition-opacity\" data-dialog-overlay=\"$id\"></div>
        
        <!-- Dialog Content -->
        <div class=\"relative z-50 w-full $sizeClass rounded-lg border border-gray-200 bg-white p-6 shadow-lg $class\">
            <!-- Header -->
            <div class=\"flex items-center justify-between mb-4\">
                <h2 id=\"{$id}-title\" class=\"text-lg font-semibold text-gray-900\">$title</h2>
                $closeButton
            </div>
            
            <!-- Content -->
            <div class=\"mb-4 text-gray-700\">$content</div>
            
            <!-- Footer -->
            " . ($footer ? "<div class=\"flex justify-end gap-2 mt-6\">$footer</div>" : "") . "
        </div>
    </div>
    ";
}
?>

