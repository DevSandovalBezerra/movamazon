<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $sql = "SELECT 
                u.id,
                u.nome_completo,
                u.email,
                u.telefone,
                u.celular,
                u.data_nascimento,
                u.tipo_documento,
                u.documento,
                u.sexo,
                u.endereco,
                u.numero,
                u.complemento,
                u.bairro,
                u.cidade,
                u.uf,
                u.cep,
                u.pais,
                u.foto_perfil,
                u.status,
                u.data_cadastro,
                u.papel,
                o.id AS organizador_id,
                o.empresa,
                o.regiao,
                o.modalidade_esportiva,
                o.quantidade_eventos,
                o.regulamento
            FROM usuarios u
            LEFT JOIN organizadores o ON u.id = o.usuario_id
            WHERE u.id = :id 
            AND (u.papel = 'organizador' OR o.id IS NOT NULL)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $organizador = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$organizador) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Organizador não encontrado']);
        exit;
    }

    $sqlPapeis = "SELECT p.id, p.nome 
                  FROM usuario_papeis up
                  JOIN papeis p ON up.papel_id = p.id
                  WHERE up.usuario_id = :id";
    $stmtPapeis = $pdo->prepare($sqlPapeis);
    $stmtPapeis->execute(['id' => $id]);
    $papeis = $stmtPapeis->fetchAll(PDO::FETCH_ASSOC);

    $sqlEventos = "SELECT 
                        id,
                        nome,
                        status,
                        data_realizacao,
                        data_criacao
                    FROM eventos
                    WHERE organizador_id = :organizador_id
                    ORDER BY data_criacao DESC
                    LIMIT 10";
    $stmtEventos = $pdo->prepare($sqlEventos);
    $stmtEventos->execute(['organizador_id' => $organizador['organizador_id'] ?: 0]);
    $eventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => (int) $organizador['id'],
            'nome_completo' => $organizador['nome_completo'],
            'email' => $organizador['email'],
            'telefone' => $organizador['telefone'],
            'celular' => $organizador['celular'],
            'data_nascimento' => $organizador['data_nascimento'],
            'tipo_documento' => $organizador['tipo_documento'],
            'documento' => $organizador['documento'],
            'sexo' => $organizador['sexo'],
            'endereco' => $organizador['endereco'],
            'numero' => $organizador['numero'],
            'complemento' => $organizador['complemento'],
            'bairro' => $organizador['bairro'],
            'cidade' => $organizador['cidade'],
            'uf' => $organizador['uf'],
            'cep' => $organizador['cep'],
            'pais' => $organizador['pais'],
            'foto_perfil' => $organizador['foto_perfil'],
            'status' => $organizador['status'],
            'data_cadastro' => $organizador['data_cadastro'],
            'papel' => $organizador['papel'],
            'organizador_id' => $organizador['organizador_id'] ? (int) $organizador['organizador_id'] : null,
            'empresa' => $organizador['empresa'],
            'regiao' => $organizador['regiao'],
            'modalidade_esportiva' => $organizador['modalidade_esportiva'],
            'quantidade_eventos' => $organizador['quantidade_eventos'],
            'regulamento' => $organizador['regulamento'],
            'papeis' => array_map(function($p) {
                return ['id' => (int) $p['id'], 'nome' => $p['nome']];
            }, $papeis),
            'eventos' => array_map(function($e) {
                return [
                    'id' => (int) $e['id'],
                    'nome' => $e['nome'],
                    'status' => $e['status'],
                    'data_realizacao' => $e['data_realizacao'],
                    'data_criacao' => $e['data_criacao']
                ];
            }, $eventos)
        ]
    ]);
} catch (Throwable $e) {
    error_log('[ADMIN_ORGANIZADORES_GET] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao obter organizador']);
}

