<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../db.php';

// Inicializar variáveis
$login = false;
$user_id = 0;
$user_email = "";
$user_name = "";
$user_papel = "";

/**
 * Verificar se o usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verificar se o usuário tem um papel específico
 */
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

/**
 * Verificar se o usuário é organizador
 */
function isOrganizador() {
    return hasRole('organizador');
}

/**
 * Verificar se o usuário é admin
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Verificar se o usuário é participante
 */
function isParticipante() {
    return hasRole('participante');
}

/**
 * Autenticar usuário
 */
function authenticateUser($email, $password) {
    global $pdo;
    
    try {
        // Buscar usuário por email
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? AND status = "ativo"');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuário não encontrado ou inativo'];
        }
        
        // Verificar senha
        if (!password_verify($password, $usuario['senha'])) {
            return ['success' => false, 'message' => 'Senha incorreta'];
        }
        
        // Buscar papéis do usuário
        $stmt = $pdo->prepare("
            SELECT p.nome as papel_nome
            FROM usuario_papeis up 
            JOIN papeis p ON up.papel_id = p.id 
            WHERE up.usuario_id = ?
        ");
        $stmt->execute([$usuario['id']]);
        $papeis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Determinar papel principal (prioridade: admin > organizador > participante)
        $papel_principal = $usuario['papel'] ?? 'participante';
        if (empty($papeis)) {
            // Se não tem papéis na tabela usuario_papeis, usar o papel da tabela usuarios
            $papeis = [['papel_nome' => $papel_principal]];
        }
        
        // Definir papel principal baseado na hierarquia
        $papel_principal = 'participante'; // padrão
        foreach ($papeis as $papel) {
            if ($papel['papel_nome'] === 'admin') {
                $papel_principal = 'admin';
                break;
            } elseif ($papel['papel_nome'] === 'organizador') {
                $papel_principal = 'organizador';
            }
        }
        
        // Criar sessão usando $_SESSION['papel'] (sistema original)
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['user_name'] = $usuario['nome_completo'];
        $_SESSION['papel'] = $papel_principal;
        $_SESSION['user_papeis'] = array_column($papeis, 'papel_nome');
        $_SESSION['login_time'] = time();
        
        // Log de login
        error_log("Login bem-sucedido: {$usuario['email']} (ID: {$usuario['id']}, Papel: {$papel_principal})");
        
        return [
            'success' => true, 
            'message' => 'Login realizado com sucesso',
            'user' => [
                'id' => $usuario['id'],
                'name' => $usuario['nome_completo'],
                'email' => $usuario['email'],
                'role' => $papel_principal,
                'papeis' => array_column($papeis, 'papel_nome')
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erro no login: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

/**
 * Fazer logout
 */
function logout() {
    session_destroy();
    return ['success' => true, 'message' => 'Logout realizado com sucesso'];
}

/**
 * Requerir autenticação
 */
function requireAuth() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Acesso negado - Usuário não autenticado']);
        exit;
    }
}

/**
 * Requerir papel específico (para APIs - retorna JSON)
 */
if (!function_exists('requireRole')) {
    function requireRole($role) {
        requireAuth();
        if (!hasRole($role)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado - Permissão insuficiente']);
            exit;
        }
    }
}

/**
 * Requerir ser organizador (para APIs - retorna JSON)
 * Nota: Esta função pode ser sobrescrita por middleware.php para uso em páginas web
 */
function requireOrganizador() {
    requireRole('organizador');
}

/**
 * Requerir ser admin (para APIs - retorna JSON)
 */
if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        requireRole('admin');
    }
}

// Verificar se já está logado
if (isLoggedIn()) {
    $login = true;
    $user_id = $_SESSION['user_id'];
    $user_email = $_SESSION['user_email'];
    $user_name = $_SESSION['user_name'];
    $user_papel = $_SESSION['papel'];
}
