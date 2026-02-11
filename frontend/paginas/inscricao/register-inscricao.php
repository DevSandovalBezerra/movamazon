<?php
// Alias para compatibilidade com links antigos:
// Sempre redireciona para o cadastro unificado em `frontend/paginas/auth/register.php`,
// preservando querystring e garantindo `redirect=inscricao` por padrão.

$params = $_GET ?: [];
if (empty($params['redirect'])) {
    $params['redirect'] = 'inscricao';
}

$dest = '../auth/register.php';
$qs = http_build_query($params);
if ($qs !== '') {
    $dest .= '?' . $qs;
}

header('Location: ' . $dest, true, 302);
exit;
?>
