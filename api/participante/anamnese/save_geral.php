<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_path = dirname(__DIR__);
require_once $base_path . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido. Use POST.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados JSON inválidos.']);
    exit();
}

$usuario_id = $_SESSION['user_id'];
$peso = isset($data['peso']) ? (float)$data['peso'] : null;
$altura = isset($data['altura']) ? (int)$data['altura'] : null;
$nivel_atividade = isset($data['nivel_atividade']) ? trim($data['nivel_atividade']) : null;
$historico_corridas = isset($data['historico_corridas']) ? trim($data['historico_corridas']) : null;
$limitacoes_fisicas = isset($data['limitacoes_fisicas']) ? trim($data['limitacoes_fisicas']) : null;
$objetivo_principal = isset($data['objetivo_principal']) ? trim($data['objetivo_principal']) : null;
$preferencias_atividades = isset($data['preferencias_atividades']) ? trim($data['preferencias_atividades']) : null;
$disponibilidade_horarios = isset($data['disponibilidade_horarios']) ? trim($data['disponibilidade_horarios']) : null;
$doencas_preexistentes = isset($data['doencas_preexistentes']) ? trim($data['doencas_preexistentes']) : null;
$uso_medicamentos = isset($data['uso_medicamentos']) ? trim($data['uso_medicamentos']) : null;

if (!$peso || !$altura || !$nivel_atividade) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios: peso, altura, nivel_atividade.']);
    exit();
}

try {
    $pdo->beginTransaction();

    $imc = $altura > 0 ? round($peso / (($altura / 100) * ($altura / 100)), 2) : null;

    if (!$preferencias_atividades && $historico_corridas) {
        $preferencias_atividades = $historico_corridas;
    } elseif (!$preferencias_atividades) {
        $preferencias_atividades = 'Corrida';
    }
    if (!$disponibilidade_horarios) {
        $disponibilidade_horarios = 'A definir pelo participante';
    }
    if (!$objetivo_principal) {
        $objetivo_principal = 'preparacao_corrida';
    }

    $sql_check = "SELECT id FROM anamneses WHERE usuario_id = ? AND inscricao_id IS NULL ORDER BY data_anamnese DESC LIMIT 1";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$usuario_id]);
    $anamnese_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($anamnese_existente) {
        $sql = "UPDATE anamneses SET
                peso = :peso,
                altura = :altura,
                imc = :imc,
                nivel_atividade = :nivel_atividade,
                objetivo_principal = :objetivo_principal,
                limitacoes_fisicas = :limitacoes_fisicas,
                preferencias_atividades = :preferencias_atividades,
                disponibilidade_horarios = :disponibilidade_horarios,
                doencas_preexistentes = :doencas_preexistentes,
                uso_medicamentos = :uso_medicamentos,
                historico_corridas = :historico_corridas,
                data_anamnese = NOW()
                WHERE id = :id AND usuario_id = :usuario_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $anamnese_existente['id'],
            ':usuario_id' => $usuario_id,
            ':peso' => $peso,
            ':altura' => $altura,
            ':imc' => $imc,
            ':nivel_atividade' => $nivel_atividade,
            ':objetivo_principal' => $objetivo_principal,
            ':limitacoes_fisicas' => $limitacoes_fisicas ?: null,
            ':preferencias_atividades' => $preferencias_atividades,
            ':disponibilidade_horarios' => $disponibilidade_horarios,
            ':doencas_preexistentes' => $doencas_preexistentes ?: null,
            ':uso_medicamentos' => $uso_medicamentos ?: null,
            ':historico_corridas' => $historico_corridas ?: null
        ]);

        $anamnese_id = $anamnese_existente['id'];
    } else {
        $sql = "INSERT INTO anamneses (
            usuario_id, inscricao_id, peso, altura, imc, nivel_atividade, 
            objetivo_principal, limitacoes_fisicas, preferencias_atividades, 
            disponibilidade_horarios, doencas_preexistentes, uso_medicamentos, 
            historico_corridas, data_anamnese
        ) VALUES (
            :usuario_id, NULL, :peso, :altura, :imc, :nivel_atividade,
            :objetivo_principal, :limitacoes_fisicas, :preferencias_atividades,
            :disponibilidade_horarios, :doencas_preexistentes, :uso_medicamentos,
            :historico_corridas, NOW()
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':peso' => $peso,
            ':altura' => $altura,
            ':imc' => $imc,
            ':nivel_atividade' => $nivel_atividade,
            ':objetivo_principal' => $objetivo_principal,
            ':limitacoes_fisicas' => $limitacoes_fisicas ?: null,
            ':preferencias_atividades' => $preferencias_atividades,
            ':disponibilidade_horarios' => $disponibilidade_horarios,
            ':doencas_preexistentes' => $doencas_preexistentes ?: null,
            ':uso_medicamentos' => $uso_medicamentos ?: null,
            ':historico_corridas' => $historico_corridas ?: null
        ]);

        $anamnese_id = $pdo->lastInsertId();
    }

    $pdo->commit();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $anamnese_existente ? 'Anamnese atualizada com sucesso.' : 'Anamnese salva com sucesso.',
        'anamnese_id' => $anamnese_id
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro ao salvar anamnese geral: ' . $e->getMessage());
    http_response_code(500);
    
    $message = 'Erro ao salvar anamnese.';
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $message = 'Tabela anamneses não encontrada. Execute o script SQL: scripts/criar_tabela_anamneses.sql';
    }
    
    echo json_encode(['success' => false, 'message' => $message]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro geral ao salvar anamnese geral: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

