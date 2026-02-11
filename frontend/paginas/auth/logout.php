<?php
session_start();

// Log de logout para auditoria
if (isset($_SESSION['user_id'])) {
    error_log('[LOGOUT] Usuário ID: ' . $_SESSION['user_id'] . ' - Nome: ' . ($_SESSION['user_name'] ?? 'N/A') . ' - IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'N/A'));
}

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir o cookie de sessão se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão
session_destroy();

// Redirecionar para a página inicial com mensagem de sucesso
header('Location: ../public/index.php?logout=success');
exit;
?> 
