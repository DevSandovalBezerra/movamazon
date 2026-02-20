<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth/auth.php';

/**
 * Verifica se o usuario logado tem papel de assessoria (admin ou assessor)
 */
function isAssessor() {
    return hasRole('assessoria_admin') || hasRole('assessor');
}

/**
 * Verifica se o usuario logado e admin da assessoria
 */
function isAssessoriaAdmin() {
    return hasRole('assessoria_admin');
}

/**
 * Exige que o usuario seja assessor ou admin da assessoria (para paginas web)
 */
function requireAssessor() {
    if (!isLoggedIn()) {
        header('Location: ' . getAssessoriaLoginUrl());
        exit();
    }
    if (!isAssessor()) {
        header('Location: ' . getAssessoriaLoginUrl() . '?erro=acesso_negado');
        exit();
    }
}

/**
 * Exige que o usuario seja admin da assessoria (para paginas web)
 */
function requireAssessoriaAdmin() {
    if (!isLoggedIn()) {
        header('Location: ' . getAssessoriaLoginUrl());
        exit();
    }
    if (!isAssessoriaAdmin()) {
        header('Location: ' . getAssessoriaLoginUrl() . '?erro=acesso_negado');
        exit();
    }
}

/**
 * Exige papel de assessor para endpoints de API (retorna JSON)
 */
function requireAssessorAPI() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Nao autenticado']);
        exit;
    }
    if (!isAssessor()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acesso negado - papel insuficiente']);
        exit;
    }
}

/**
 * Exige papel de admin da assessoria para endpoints de API (retorna JSON)
 */
function requireAssessoriaAdminAPI() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Nao autenticado']);
        exit;
    }
    if (!isAssessoriaAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acesso negado - requer admin da assessoria']);
        exit;
    }
}

/**
 * Retorna o assessoria_id do usuario logado.
 * Busca em assessoria_equipe onde o usuario esta vinculado e ativo.
 */
function getAssessoriaDoUsuario() {
    if (!isLoggedIn()) return null;

    global $pdo;
    $stmt = $pdo->prepare("
        SELECT ae.assessoria_id 
        FROM assessoria_equipe ae 
        WHERE ae.usuario_id = ? AND ae.status = 'ativo'
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? (int) $row['assessoria_id'] : null;
}

/**
 * Retorna URL absoluta do login da assessoria
 */
function getAssessoriaLoginUrl() {
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
    $projectDir = str_replace('\\', '/', __DIR__);
    $projectDir = str_replace(str_replace('\\', '/', $docRoot), '', $projectDir);
    $basePath = dirname(dirname($projectDir));
    return $basePath . '/frontend/paginas/assessoria/auth/login.php';
}
