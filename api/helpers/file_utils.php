<?php
/**
 * Utilitários para manipulação de arquivos
 */

/**
 * Normaliza nome de arquivo removendo espaços e caracteres especiais
 * 
 * Esta função garante que nomes de arquivos sejam seguros para uso em URLs
 * e sistemas de arquivos, especialmente quando servidos via CDN/nuvem.
 * 
 * @param string $nome Nome original do arquivo ou item
 * @param string $prefix Prefixo opcional (ex: 'kit_template_')
 * @param string $extension Extensão do arquivo (ex: 'png')
 * @return string Nome normalizado sem espaços ou caracteres especiais
 * 
 * @example
 * normalizarNomeArquivo('Kit Atleta', 'kit_template_', 'png')
 * // Retorna: 'kit_template_Kit_Atleta.png'
 * 
 * @example
 * normalizarNomeArquivo('Produto Especial!@#', '', 'jpg')
 * // Retorna: 'Produto_Especial.jpg'
 */
function normalizarNomeArquivo($nome, $prefix = '', $extension = '') {
    if (empty($nome)) {
        return '';
    }
    
    // Remover espaços e caracteres especiais
    $normalizado = trim($nome);
    
    // Substituir caracteres especiais por underscore
    // Mantém apenas: letras, números, underscore e hífen
    $normalizado = preg_replace('/[^a-zA-Z0-9_-]/', '_', $normalizado);
    
    // Múltiplos underscores consecutivos viram um único
    $normalizado = preg_replace('/_+/', '_', $normalizado);
    
    // Remove underscores nas extremidades
    $normalizado = trim($normalizado, '_');
    
    // Se ficou vazio após normalização, usar fallback
    if (empty($normalizado)) {
        $normalizado = 'arquivo';
    }
    
    // Adicionar prefixo se fornecido
    if (!empty($prefix)) {
        $normalizado = $prefix . $normalizado;
    }
    
    // Adicionar extensão se fornecida
    if (!empty($extension)) {
        $normalizado .= '.' . strtolower($extension);
    }
    
    return $normalizado;
}

/**
 * Gera nome determinístico para kits baseando-se em tipo e relacionamentos.
 *
 * @param string $tipo 'template' ou 'evento'
 * @param int $id Identificador do registro (template_id ou kit_evento_id)
 * @param int|null $evento_id Quando aplicável, o evento atrelado ao kit
 * @param string $extension Extensão do arquivo (sem ponto)
 * @return string Nome já normalizado (prefixo + identificadores + extensão)
 */
function gerarNomeKit($tipo, $id, $evento_id = null, $extension = 'png') {
    $tipo = strtolower($tipo);

    if (!in_array($tipo, ['template', 'evento'], true)) {
        $tipo = 'template';
    }

    $segments = [];

    if ($tipo === 'template') {
        $segments[] = 'kit_template';
        $segments[] = 'kit';
        $segments[] = $id;
    } else {
        $segments[] = 'kit_template';
        $segments[] = 'evento';
        $segments[] = $evento_id;
        $segments[] = 'kit';
        $segments[] = $id;
    }

    $base = implode('_', $segments);
    return normalizarNomeArquivo($base, '', $extension);
}
