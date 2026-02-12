<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Termos de inscrição são regras da plataforma - gerenciados apenas pelo admin
http_response_code(403);
echo json_encode([
    'success' => false,
    'message' => 'Termos de inscrição são gerenciados pela administração da plataforma.'
]);
