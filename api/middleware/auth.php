<?php
/**
 * Middleware de Autenticação Centralizado
 * 
 * Este arquivo deve ser incluído no início de qualquer API que precise de autenticação.
 * Ele verifica automaticamente a sessão e retorna respostas padronizadas para sessões expiradas.
 */

// Iniciar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se o usuário está autenticado
 * @param string $papel_requerido Papel necessário ('organizador', 'participante', 'admin' ou null para qualquer usuário logado)
 * @param bool $retornar_json Se deve retornar JSON (true) ou apenas boolean (false)
 * @return bool|void Retorna true se autenticado, ou envia resposta JSON e termina execução
 */
function verificarAutenticacao($papel_requerido = null, $retornar_json = true) {
    // Verificar se usuário está logado
    if (!isset($_SESSION['user_id'])) {
        if ($retornar_json) {
            enviarRespostaSessaoExpirada();
        }
        return false;
    }
    
    // Verificar papel específico se solicitado
    if ($papel_requerido && $_SESSION['papel'] !== $papel_requerido) {
        if ($retornar_json) {
            enviarRespostaAcessoNegado($papel_requerido);
        }
        return false;
    }
    
    return true;
}

/**
 * Envia resposta padronizada para sessão expirada
 */
function enviarRespostaSessaoExpirada() {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Sessão expirada',
        'code' => 'SESSION_EXPIRED',
        'message' => 'Sua sessão expirou. Você será redirecionado para fazer login novamente.',
        'redirect' => '/frontend/paginas/auth/login.php'
    ]);
    exit;
}

/**
 * Envia resposta padronizada para acesso negado
 */
function enviarRespostaAcessoNegado($papel_requerido) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Acesso negado',
        'code' => 'ACCESS_DENIED',
        'message' => "Você precisa ter permissão de {$papel_requerido} para acessar esta funcionalidade.",
        'redirect' => '/frontend/paginas/auth/login.php'
    ]);
    exit;
}

/**
 * Obtém dados do usuário logado
 * @return array|null Dados do usuário ou null se não logado
 */
function obterUsuarioLogado() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'nome' => $_SESSION['nome_completo'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'papel' => $_SESSION['papel'] ?? '',
        'organizador_id' => $_SESSION['papel'] === 'organizador' ? $_SESSION['user_id'] : null
    ];
}

/**
 * Verifica se o usuário é organizador
 * @return bool
 */
function ehOrganizador() {
    return isset($_SESSION['user_id']) && $_SESSION['papel'] === 'organizador';
}

/**
 * Verifica se o usuário é participante
 * @return bool
 */
function ehParticipante() {
    return isset($_SESSION['user_id']) && $_SESSION['papel'] === 'participante';
}

/**
 * Verifica se o usuário é admin
 * @return bool
 */
function ehAdmin() {
    return isset($_SESSION['user_id']) && $_SESSION['papel'] === 'admin';
}

/**
 * Log de segurança para auditoria
 * @param string $acao Ação realizada
 * @param string $detalhes Detalhes adicionais
 */
function logSeguranca($acao, $detalhes = '') {
    $usuario_id = $_SESSION['user_id'] ?? 'anonimo';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_message = "[{$timestamp}] SECURITY - Usuario: {$usuario_id}, IP: {$ip}, Acao: {$acao}";
    if ($detalhes) {
        $log_message .= ", Detalhes: {$detalhes}";
    }
    
    error_log($log_message);
}
