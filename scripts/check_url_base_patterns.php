<?php
/**
 * Bloqueia padroes divergentes de URL base.
 * Uso: php scripts/check_url_base_patterns.php
 */

$root = realpath(__DIR__ . '/..');
if ($root === false) {
    fwrite(STDERR, "Nao foi possivel resolver raiz do projeto.\n");
    exit(2);
}

$allowedApiBaseSetter = str_replace('\\', '/', realpath($root . '/frontend/js/core/url-base.js') ?: '');
$criticalPhpFiles = [
    str_replace('\\', '/', realpath($root . '/api/helpers/url_base.php') ?: ''),
    str_replace('\\', '/', realpath($root . '/api/mercadolivre/config.php') ?: ''),
];

$forbiddenJsPatterns = [
    '/window\\.API_BASE\\s*=/' => 'Setter local de window.API_BASE detectado fora do modulo canônico.',
];

$forbiddenPhpPatterns = [
    '/\\$_SERVER\\s*\\[\\s*[\'"]REQUEST_URI[\'"]\\s*\\]/' => 'Uso de REQUEST_URI para base URL em arquivo nao permitido.',
    '/\\$_SERVER\\s*\\[\\s*[\'"]SCRIPT_NAME[\'"]\\s*\\]/' => 'Uso de SCRIPT_NAME para base URL em arquivo nao permitido.',
];

$errors = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile()) {
        continue;
    }

    $ext = strtolower($fileInfo->getExtension());
    if (!in_array($ext, ['js', 'php'], true)) {
        continue;
    }

    $path = str_replace('\\', '/', $fileInfo->getPathname());

    if (strpos($path, '/vendor/') !== false || strpos($path, '/.git/') !== false) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        continue;
    }

    if ($ext === 'js') {
        foreach ($forbiddenJsPatterns as $regex => $message) {
            if (preg_match($regex, $content)) {
                if ($path !== $allowedApiBaseSetter) {
                    $errors[] = $path . ' :: ' . $message;
                }
            }
        }
    }

    if ($ext === 'php') {
        foreach ($forbiddenPhpPatterns as $regex => $message) {
            if (!preg_match($regex, $content)) {
                continue;
            }

            if (in_array($path, $criticalPhpFiles, true)) {
                continue;
            }

            $errors[] = $path . ' :: ' . $message;
        }
    }
}

if (!empty($errors)) {
    fwrite(STDERR, "Violacoes de padrao de URL base encontradas:\n");
    foreach ($errors as $error) {
        fwrite(STDERR, ' - ' . $error . "\n");
    }
    exit(1);
}

fwrite(STDOUT, "OK: nenhum desvio de padrao de URL base detectado.\n");
exit(0);
