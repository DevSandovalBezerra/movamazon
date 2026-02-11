<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir apenas as funções base de auth.php (isLoggedIn, hasRole, etc)
// sem incluir requireRole, requireOrganizador, requireParticipante
// que vamos declarar aqui para uso em páginas web

// Carregar funções base
require_once __DIR__ . '/../db.php';

// Copiar apenas as funções necessárias de auth.php sem as que vamos sobrescrever
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('hasRole')) {
    function hasRole($role) {
        if (!isLoggedIn()) return false;
        
        // Verificar papel na sessão
        if (isset($_SESSION['papel']) && $_SESSION['papel'] === $role) {
            return true;
        }
        
        // Verificar papel na tabela usuario_papeis
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT up.papel_id, p.nome 
            FROM usuario_papeis up 
            JOIN papeis p ON up.papel_id = p.id 
            WHERE up.usuario_id = ? AND p.nome = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $role]);
        return $stmt->fetch() !== false;
    }
}

// Funções para páginas web (com redirecionamento)
function requireRoleWeb($role) {
    if (!isLoggedIn()) {
        $redirectUrl = '../../paginas/auth/login.php?area=' . $role;
        if (isset($_SERVER['REQUEST_URI'])) {
            $redirectUrl .= '&redirect=' . urlencode($_SERVER['REQUEST_URI']);
        }
        header('Location: ' . $redirectUrl);
        exit();
    }
    
    if (!hasRole($role)) {
        error_log('[AUTH_MIDDLEWARE] Acesso negado: Usuário ' . ($_SESSION['user_id'] ?? 'desconhecido') . ' tentou acessar área ' . $role . ' mas tem papel ' . ($_SESSION['papel'] ?? 'nenhum'));
        header('Location: ../../paginas/auth/login.php?area=' . $role . '&erro=acesso_negado');
        exit();
    }
}

// Funções específicas para páginas web
// Só declarar se ainda não existirem (para evitar conflito quando auth.php já foi carregado)
if (!function_exists('requireParticipante')) {
    function requireParticipante() {
        requireRoleWeb('participante');
    }
}

if (!function_exists('requireOrganizador')) {
    function requireOrganizador() {
        requireRoleWeb('organizador');
    }
}

if (!function_exists('redirectByRole')) {
    function redirectByRole($papel, $selectedArea = 'auto') {
        // Normalizar área: 'auto' = 'participante' para validação
        $areaParaValidacao = ($selectedArea === 'auto') ? 'participante' : $selectedArea;
        
        // Validação rigorosa: papel deve corresponder à área
        if ($papel !== $areaParaValidacao) {
            error_log('[REDIRECT_BY_ROLE] Erro: Papel ' . $papel . ' não corresponde à área ' . $areaParaValidacao);
            // Não redirecionar, deixar o login.php tratar o erro
            return false;
        }
        
        switch ($papel) {
            case 'admin':
                header('Location: ../../paginas/admin/index.php?page=dashboard');
                exit();
            case 'organizador':
                header('Location: ../../paginas/organizador/index.php?page=dashboard');
                exit();
            case 'participante':
            default:
                header('Location: ../../paginas/participante/index.php?page=dashboard');
                exit();
        }
    }
}
