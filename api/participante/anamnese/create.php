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
$inscricao_id = isset($data['inscricao_id']) ? (int)$data['inscricao_id'] : null;
$peso = isset($data['peso']) ? (float)$data['peso'] : null;
$altura = isset($data['altura']) ? (int)$data['altura'] : null;
$nivel_condicionamento = isset($data['nivel_condicionamento']) ? trim($data['nivel_condicionamento']) : null;
$historico_corridas = isset($data['historico_corridas']) ? trim($data['historico_corridas']) : null;
$limitacoes_fisicas = isset($data['limitacoes_fisicas']) ? trim($data['limitacoes_fisicas']) : null;
$objetivo_corrida = isset($data['objetivo_corrida']) ? trim($data['objetivo_corrida']) : null;

if (!$inscricao_id || !$peso || !$altura || !$nivel_condicionamento) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios: inscricao_id, peso, altura, nivel_condicionamento.']);
    exit();
}

try {
    $pdo->beginTransaction();

    if ($inscricao_id !== 999) {
        $verificar_inscricao = $pdo->prepare("SELECT id FROM inscricoes WHERE id = ? AND usuario_id = ?");
        $verificar_inscricao->execute([$inscricao_id, $usuario_id]);
        if (!$verificar_inscricao->fetch()) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Inscrição não encontrada ou não pertence ao usuário.']);
            exit();
        }
    }

    $imc = $altura > 0 ? round($peso / (($altura / 100) * ($altura / 100)), 2) : null;

    $sql = "INSERT INTO anamneses (
        usuario_id, inscricao_id, peso, altura, imc, nivel_atividade, 
        objetivo_principal, limitacoes_fisicas, preferencias_atividades, 
        disponibilidade_horarios, data_anamnese
    ) VALUES (
        :usuario_id, :inscricao_id, :peso, :altura, :imc, :nivel_atividade,
        :objetivo_principal, :limitacoes_fisicas, :preferencias_atividades,
        :disponibilidade_horarios, NOW()
    )";

    $stmt = $pdo->prepare($sql);
    
    $nivel_atividade = $nivel_condicionamento === 'iniciante' ? 'inativo' : 'ativo';
    $preferencias_atividades = $historico_corridas ?: 'Corrida';
    $disponibilidade_horarios = 'A definir pelo participante';

    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':inscricao_id' => $inscricao_id,
        ':peso' => $peso,
        ':altura' => $altura,
        ':imc' => $imc,
        ':nivel_atividade' => $nivel_atividade,
        ':objetivo_principal' => $objetivo_corrida ?: 'Preparação para corrida',
        ':limitacoes_fisicas' => $limitacoes_fisicas ?: null,
        ':preferencias_atividades' => $preferencias_atividades,
        ':disponibilidade_horarios' => $disponibilidade_horarios
    ]);

    $anamnese_id = $pdo->lastInsertId();

    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Anamnese salva com sucesso.',
        'anamnese_id' => $anamnese_id
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro ao salvar anamnese: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar anamnese.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro geral ao salvar anamnese: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

