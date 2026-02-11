<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Limpar todas as variáveis de sessão
    $_SESSION = array();

    // Destruir o cookie de sessão se existir
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destruir a sessão
    session_destroy();

    echo json_encode([
        'success' => true,
        'message' => 'Logout realizado com sucesso'
    ]);
} catch (Exception $e) {
    error_log('Erro no logout: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Erro ao fazer logout'
    ]);
}
