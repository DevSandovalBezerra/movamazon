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

/**
 * Valida extensao permitida para upload de fotos de kit.
 *
 * @param string $extension Extensao sem ponto
 * @return string Extensao normalizada
 * @throws Exception
 */
function validarExtensaoFotoKit($extension) {
    $ext = strtolower(trim((string) $extension));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if ($ext === '' || !in_array($ext, $allowed, true)) {
        throw new Exception('Formato de imagem nao suportado. Use: JPG, PNG ou WEBP');
    }
    return $ext;
}

/**
 * Salva foto do template de kit com nome deterministico.
 *
 * @param int $template_id
 * @param array $file Entrada de $_FILES['foto_kit']
 * @param string $upload_dir Caminho para frontend/assets/img/kits/
 * @param string|null $foto_antiga Nome antigo do arquivo (opcional)
 * @return string|null Nome final salvo ou null se nao houve upload
 * @throws Exception
 */
function salvarFotoKitTemplate($template_id, array $file, $upload_dir, $foto_antiga = null) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file_info = pathinfo($file['name'] ?? '');
    $extension = validarExtensaoFotoKit($file_info['extension'] ?? '');

    $upload_dir = rtrim($upload_dir, '/\\') . DIRECTORY_SEPARATOR;
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
            throw new Exception('Erro ao criar diretorio de upload');
        }
    }

    $filename = gerarNomeKit('template', (int) $template_id, null, $extension);
    $filepath = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Erro ao fazer upload da imagem');
    }

    error_log('[KIT_TEMPLATE_UPLOAD] Saved photo: template_id=' . (int) $template_id . ' filename=' . $filename);

    if ($foto_antiga && $foto_antiga !== $filename) {
        $old = $upload_dir . basename($foto_antiga);
        if (is_file($old)) {
            if (@unlink($old)) {
                error_log('[KIT_TEMPLATE_UPLOAD] Removed old photo: template_id=' . (int) $template_id . ' filename=' . basename($foto_antiga));
            }
        }
    }

    return $filename;
}
