<?php
error_log("============================================");
error_log("[GET_INSCRICOES] Iniciando requisição...");
error_log("[GET_INSCRICOES] Timestamp: " . date('Y-m-d H:i:s'));
error_log("[GET_INSCRICOES] Método: " . $_SERVER['REQUEST_METHOD']);
error_log("[GET_INSCRICOES] URL: " . $_SERVER['REQUEST_URI']);

// Verificar se a sessão já está ativa antes de iniciar
if (session_status() === PHP_SESSION_NONE) {
    error_log("[GET_INSCRICOES] Iniciando sessão...");
    session_start();
}

error_log("[GET_INSCRICOES] Sessão status: " . session_status());
error_log("[GET_INSCRICOES] Session ID: " . session_id());

// Usar caminhos relativos para compatibilidade com hospedagem
$base_path = dirname(__DIR__);
error_log("[GET_INSCRICOES] Base path: " . $base_path);

require_once $base_path . '/db.php';
require_once $base_path . '/security_middleware.php';

error_log("[GET_INSCRICOES] Verificando sessão...");
error_log("[GET_INSCRICOES] user_id na sessão: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NÃO DEFINIDO'));
error_log("[GET_INSCRICOES] Variáveis de sessão: " . print_r($_SESSION, true));

// Garante que apenas usuários logados (participantes ou outros) possam acessar
if (!isset($_SESSION['user_id'])) {
    error_log("[GET_INSCRICOES] ❌ ERRO: Acesso negado - user_id não encontrado na sessão");
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

header('Content-Type: application/json');
$usuario_id = $_SESSION['user_id'];
error_log("[GET_INSCRICOES] ✅ Usuário autenticado: " . $usuario_id);

try {
    error_log("[GET_INSCRICOES] Preparando query SQL...");
    $sql = "
        SELECT 
            i.id as inscricao_id,
            i.numero_inscricao,
            i.status,
            i.status_pagamento,
            e.id as evento_id,
            e.nome as evento_nome,
            COALESCE(e.data_realizacao, e.data_inicio) as evento_data,
            e.local as evento_local,
            e.imagem as evento_imagem,
            m.nome as modalidade_nome,
            k.nome as kit_nome
        FROM inscricoes i
        JOIN eventos e ON i.evento_id = e.id
        JOIN modalidades m ON i.modalidade_evento_id = m.id
        LEFT JOIN kits_eventos k ON i.kit_id = k.id
        WHERE i.usuario_id = ?
        ORDER BY COALESCE(e.data_realizacao, e.data_inicio) DESC
    ";

    error_log("[GET_INSCRICOES] SQL preparado. Executando com user_id: " . $usuario_id);
    $stmt = $pdo->prepare($sql);

    if (!$stmt) {
        error_log("[GET_INSCRICOES] ❌ ERRO: Falha ao preparar statement");
        error_log("[GET_INSCRICOES] Erro PDO: " . print_r($pdo->errorInfo(), true));
        throw new Exception("Falha ao preparar query SQL");
    }

    $executado = $stmt->execute([$usuario_id]);
    error_log("[GET_INSCRICOES] Query executada: " . ($executado ? 'SUCESSO' : 'FALHA'));

    if (!$executado) {
        error_log("[GET_INSCRICOES] ❌ ERRO: Falha ao executar query");
        error_log("[GET_INSCRICOES] Erro PDO: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Falha ao executar query SQL");
    }

    $inscricoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("[GET_INSCRICOES] ✅ Inscrições encontradas: " . count($inscricoes));

    if (count($inscricoes) > 0) {
        error_log("[GET_INSCRICOES] Primeira inscrição: " . print_r($inscricoes[0], true));
    }

    $response = ['success' => true, 'inscricoes' => $inscricoes];
    error_log("[GET_INSCRICOES] Resposta JSON preparada");
    error_log("[GET_INSCRICOES] Total de inscrições no response: " . count($response['inscricoes']));

    echo json_encode($response);
    error_log("[GET_INSCRICOES] ✅ Resposta enviada com sucesso");
    error_log("============================================");
} catch (PDOException $e) {
    error_log("[GET_INSCRICOES] ❌ ERRO PDO: " . $e->getMessage());
    error_log("[GET_INSCRICOES] Código do erro: " . $e->getCode());
    error_log("[GET_INSCRICOES] Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar inscrições: ' . $e->getMessage()]);
    error_log("============================================");
} catch (Exception $e) {
    error_log("[GET_INSCRICOES] ❌ ERRO GERAL: " . $e->getMessage());
    error_log("[GET_INSCRICOES] Código do erro: " . $e->getCode());
    error_log("[GET_INSCRICOES] Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar inscrições: ' . $e->getMessage()]);
    error_log("============================================");
}
