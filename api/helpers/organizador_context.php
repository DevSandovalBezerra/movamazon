<?php
/**
 * Resolve contexto do organizador a partir do usuário logado.
 *
 * Padrão desejado: eventos.organizador_id = organizadores.id
 * Compatibilidade: alguns registros antigos podem ter eventos.organizador_id = usuarios.id
 */

function getOrganizadorIdByUsuarioId(PDO $pdo, int $usuarioId): ?int
{
    $stmt = $pdo->prepare('SELECT id FROM organizadores WHERE usuario_id = ? LIMIT 1');
    $stmt->execute([$usuarioId]);
    $id = $stmt->fetchColumn();
    if ($id === false || $id === null) {
        return null;
    }
    return (int) $id;
}

function requireOrganizadorContext(PDO $pdo): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || ($_SESSION['papel'] ?? null) !== 'organizador') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
        exit();
    }

    $usuarioId = (int) $_SESSION['user_id'];
    $organizadorId = getOrganizadorIdByUsuarioId($pdo, $usuarioId);

    if (!$organizadorId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Organizador não encontrado para este usuário']);
        exit();
    }

    return [
        'usuario_id' => $usuarioId,
        'organizador_id' => $organizadorId
    ];
}


