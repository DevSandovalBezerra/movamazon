<?php
/**
 * Componente Input baseado em shadcn/ui
 * Adaptado para PHP
 * 
 * @param array $options - Opções do input
 * @return string HTML do input
 */
function shadcn_input($options = []) {
    $type = $options['type'] ?? 'text';
    $name = $options['name'] ?? '';
    $id = $options['id'] ?? $name;
    $value = $options['value'] ?? '';
    $placeholder = $options['placeholder'] ?? '';
    $class = $options['class'] ?? '';
    $disabled = isset($options['disabled']) && $options['disabled'];
    $required = isset($options['required']) && $options['required'];
    $label = $options['label'] ?? '';
    $error = $options['error'] ?? '';
    $helpText = $options['helpText'] ?? '';
    
    // Classes base do shadcn/ui input
    $baseClasses = "flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-green focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50";
    
    $inputClasses = trim("$baseClasses $class");
    if ($error) {
        $inputClasses .= ' border-red-500 focus-visible:ring-red-500';
    }
    
    // Atributos
    $attrs = [];
    if ($id) $attrs[] = "id=\"$id\"";
    if ($name) $attrs[] = "name=\"$name\"";
    if ($value !== '') $attrs[] = "value=\"" . htmlspecialchars($value) . "\"";
    if ($placeholder) $attrs[] = "placeholder=\"" . htmlspecialchars($placeholder) . "\"";
    if ($disabled) $attrs[] = 'disabled';
    if ($required) $attrs[] = 'required';
    $attrsStr = !empty($attrs) ? ' ' . implode(' ', $attrs) : '';
    
    $labelHtml = $label ? "<label for=\"$id\" class=\"block text-sm font-medium text-gray-700 mb-1\">$label" . ($required ? ' <span class="text-red-500">*</span>' : '') . "</label>" : '';
    $errorHtml = $error ? "<p class=\"mt-1 text-sm text-red-600\">$error</p>" : '';
    $helpHtml = $helpText ? "<p class=\"mt-1 text-sm text-gray-500\">$helpText</p>" : '';
    
    return "
    <div class=\"space-y-1\">
        $labelHtml
        <input type=\"$type\" class=\"$inputClasses\"$attrsStr>
        $errorHtml
        $helpHtml
    </div>
    ";
}
?>

