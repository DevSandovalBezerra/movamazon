<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_path = dirname(__DIR__);
require_once $base_path . '/../db.php';
require_once $base_path . '/../helpers/config_helper.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

header('Content-Type: application/json');

$inscricao_id = $_GET['inscricao_id'] ?? null;
$usuario_id = $_SESSION['user_id'];

if (!$inscricao_id || !is_numeric($inscricao_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da inscrição inválido.']);
    exit();
}

try {
    // Verificar configuração de exigência de inscrição
    $exigir_inscricao = ConfigHelper::get('treino.exigir_inscricao', true);
    
    if ($exigir_inscricao) {
        // MODO PRODUÇÃO: Validar inscrição no banco
        $verificar_inscricao = $pdo->prepare("SELECT id FROM inscricoes WHERE id = ? AND usuario_id = ?");
        $verificar_inscricao->execute([$inscricao_id, $usuario_id]);
        
        if (!$verificar_inscricao->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Inscrição não encontrada ou não pertence ao usuário.']);
            exit();
        }
    } else {
        // REGRA PROVISÓRIA: Inscrição não exigida
        error_log('[GET_TREINO] ⚠️ REGRA PROVISÓRIA: Inscrição não exigida (inscricao_id=' . $inscricao_id . ')');
    }

    if ($exigir_inscricao) {
        $sql_plano = "SELECT ptg.*, 
                             COALESCE(e.data_realizacao, e.data_inicio) as evento_data,
                             e.nome as evento_nome
                      FROM planos_treino_gerados ptg
                      LEFT JOIN inscricoes i ON ptg.inscricao_id = i.id
                      LEFT JOIN eventos e ON i.evento_id = e.id
                      WHERE ptg.inscricao_id = ? 
                      ORDER BY ptg.data_criacao_plano DESC 
                      LIMIT 1";
        $stmt_plano = $pdo->prepare($sql_plano);
        $stmt_plano->execute([$inscricao_id]);
    } else {
        $sql_plano = "SELECT ptg.*, 
                             COALESCE(e.data_realizacao, e.data_inicio) as evento_data,
                             e.nome as evento_nome
                      FROM planos_treino_gerados ptg
                      LEFT JOIN inscricoes i ON ptg.inscricao_id = i.id
                      LEFT JOIN eventos e ON i.evento_id = e.id
                      WHERE ptg.usuario_id = ? 
                      ORDER BY ptg.data_criacao_plano DESC 
                      LIMIT 1";
        $stmt_plano = $pdo->prepare($sql_plano);
        $stmt_plano->execute([$usuario_id]);
    }
    $plano = $stmt_plano->fetch(PDO::FETCH_ASSOC);

    if (!$plano) {
        if (!$exigir_inscricao) {
            echo json_encode([
                'success' => false,
                'message' => 'Nenhum plano de treino encontrado para esta inscrição.',
                'plano' => null,
                'treinos' => []
            ]);
            exit();
        }
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Nenhum plano de treino encontrado para esta inscrição.', 'plano' => null, 'treinos' => []]);
        exit();
    }

    $sql_treinos = "SELECT * FROM treinos WHERE plano_treino_gerado_id = ? ORDER BY semana_numero ASC, dia_semana_id ASC";
    $stmt_treinos = $pdo->prepare($sql_treinos);
    $stmt_treinos->execute([$plano['id']]);
    $treinos = $stmt_treinos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($treinos as &$treino) {
        $sql_exercicios = "SELECT * FROM treino_exercicios WHERE treino_id = ?";
        $stmt_exercicios = $pdo->prepare($sql_exercicios);
        $stmt_exercicios->execute([$treino['id']]);
        $exercicios = $stmt_exercicios->fetchAll(PDO::FETCH_ASSOC);
        $treino['exercicios'] = $exercicios;

        foreach(['parte_inicial','parte_principal','volta_calma'] as $parte) {
            if (isset($treino[$parte]) && is_string($treino[$parte]) && trim($treino[$parte]) !== '') {
                if ($treino[$parte][0] === '[' || $treino[$parte][0] === '{') {
                    $json = json_decode($treino[$parte], true);
                    if ($json !== null) {
                        $treino[$parte] = is_array($json) ? $json : [];
                    } else {
                        $treino[$parte] = [];
                    }
                } else {
                    $treino[$parte] = [];
                }
            } else if (!isset($treino[$parte]) || $treino[$parte] === null || $treino[$parte] === 'N/A') {
                $treino[$parte] = [];
            }
            if (!is_array($treino[$parte])) {
                $treino[$parte] = [];
            }
        }
    }

    echo json_encode([
        'success' => true,
        'plano' => $plano,
        'treinos' => $treinos
    ]);

} catch (PDOException $e) {
    error_log('Erro ao buscar treino: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar treino.']);
} catch (Exception $e) {
    error_log('Erro geral ao buscar treino: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

