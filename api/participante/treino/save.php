<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_path = dirname(__DIR__);
require_once $base_path . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Acesso negado."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método não permitido. Use POST."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['inscricao_id']) || !is_numeric($data['inscricao_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Campo 'inscricao_id' ausente ou inválido."]);
    exit;
}

if (!isset($data['treinos']) || !is_array($data['treinos']) || empty($data['treinos'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Campo 'treinos' ausente, inválido ou vazio."]);
    exit;
}

$inscricao_id = (int) $data['inscricao_id'];
$usuario_id = $_SESSION['user_id'];
$treinosArray = $data['treinos'];
$bibliografia = isset($data['bibliografia']) ? trim($data['bibliografia']) : '';

try {
    $pdo->beginTransaction();

    $verificar_inscricao = $pdo->prepare("SELECT id FROM inscricoes WHERE id = ? AND usuario_id = ?");
    $verificar_inscricao->execute([$inscricao_id, $usuario_id]);
    
    if (!$verificar_inscricao->fetch()) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Inscrição não encontrada ou não pertence ao usuário."]);
        exit;
    }

    $sqlAnamnese = "SELECT id FROM anamneses WHERE inscricao_id = ? ORDER BY data_anamnese DESC LIMIT 1";
    $stmtAnamnese = $pdo->prepare($sqlAnamnese);
    $stmtAnamnese->execute([$inscricao_id]);
    $anamnese = $stmtAnamnese->fetch(PDO::FETCH_ASSOC);

    if (!$anamnese || !isset($anamnese['id'])) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Nenhuma anamnese encontrada para esta inscrição."]);
        exit;
    }

    $anamnese_id = $anamnese['id'];

    $foco_primario = $treinosArray[0]['foco_primario'] ?? 'preparacao-corrida';
    $duracao_treino_geral = $treinosArray[0]['volume_total'] ?? null;
    $equipamento_geral = $treinosArray[0]['equipamento_principal'] ?? null;

    $sqlInsertPlano = "INSERT INTO planos_treino_gerados (
        usuario_id, inscricao_id, anamnese_id, bibliografia_plano, foco_primario, duracao_treino_geral, equipamento_geral
    ) VALUES (
        :usuario_id, :inscricao_id, :anamnese_id, :bibliografia_plano, :foco_primario, :duracao_treino_geral, :equipamento_geral
    )";
    
    $stmtInsertPlano = $pdo->prepare($sqlInsertPlano);
    $stmtInsertPlano->execute([
        ':usuario_id' => $usuario_id,
        ':inscricao_id' => $inscricao_id,
        ':anamnese_id' => $anamnese_id,
        ':bibliografia_plano' => $bibliografia,
        ':foco_primario' => $foco_primario,
        ':duracao_treino_geral' => $duracao_treino_geral,
        ':equipamento_geral' => $equipamento_geral
    ]);

    $plano_treino_gerado_id = $pdo->lastInsertId();

    $sqlInsertTreino = "INSERT INTO treinos (
        anamnese_id, usuario_id, plano_treino_gerado_id,
        nome, descricao, nivel_dificuldade, dia_semana_id, semana_numero,
        parte_inicial, parte_principal, volta_calma,
        fcmax, volume_total,
        grupos_musculares, numero_series, intervalo,
        numero_repeticoes, intensidade, carga_interna,
        observacoes, data_criacao, ativo
    ) VALUES (
        :anamnese_id, :usuario_id, :plano_treino_gerado_id,
        :nome, :descricao, :nivel_dificuldade, :dia_semana_id, :semana_numero,
        :parte_inicial, :parte_principal, :volta_calma,
        :fcmax, :volume_total,
        :grupos_musculares, :num_series, :intervalo,
        :num_repeticoes, :intensidade, :carga_interna,
        :observacoes, NOW(), 1
    )";
    
    $stmtInsertTreino = $pdo->prepare($sqlInsertTreino);

    $idsTreinosInseridos = [];

    foreach ($treinosArray as $index => $diaTreino) {
        $nome = $diaTreino['nome'] ?? ('Treino - Dia ' . ($diaTreino['dia_semana_id'] ?? ($index + 1)));
        $descricao = $diaTreino['descricao'] ?? 'Treino gerado para preparação de corrida';
        $nivel_dificuldade = $diaTreino['nivel_dificuldade'] ?? 'intermediario';
        $dia_semana_id = $diaTreino['dia_semana_id'] ?? ($index + 1);
        $semana_numero = isset($diaTreino['semana_numero']) ? (int)$diaTreino['semana_numero'] : 1;

        $parte_inicial = isset($diaTreino['parte_inicial']) && is_array($diaTreino['parte_inicial'])
            ? json_encode($diaTreino['parte_inicial'], JSON_UNESCAPED_UNICODE)
            : ($diaTreino['parte_inicial'] ?? 'N/A');
        $parte_principal = isset($diaTreino['parte_principal']) && is_array($diaTreino['parte_principal'])
            ? json_encode($diaTreino['parte_principal'], JSON_UNESCAPED_UNICODE)
            : ($diaTreino['parte_principal'] ?? 'N/A');
        $volta_calma = isset($diaTreino['volta_calma']) && is_array($diaTreino['volta_calma'])
            ? json_encode($diaTreino['volta_calma'], JSON_UNESCAPED_UNICODE)
            : ($diaTreino['volta_calma'] ?? 'N/A');
        
        $volume_total = $diaTreino['volume_total'] ?? 'N/A';
        $grupos_musculares = $diaTreino['grupos_musculares'] ?? 'N/A';
        $numero_series = isset($diaTreino['numero_series']) ? (int)$diaTreino['numero_series'] : 3;
        $intervalo = $diaTreino['intervalo'] ?? 'N/A';
        $numero_repeticoes = $diaTreino['numero_repeticoes'] ?? 'N/A';
        $intensidade = $diaTreino['intensidade'] ?? 'N/A';
        $carga_interna = $diaTreino['carga_interna'] ?? 'N/A';

        $fcmax_val = isset($diaTreino['fcmax']) && is_numeric($diaTreino['fcmax']) ? (int)$diaTreino['fcmax'] : null;
        $observacoes_dia = $diaTreino['observacoes'] ?? '';

        if ($index === 0 && !empty($bibliografia)) {
            $observacoes_dia .= (!empty($observacoes_dia) ? "\n\n" : "") . "==Bibliografia Consultada==\n" . $bibliografia;
        }
        if (empty($observacoes_dia)) {
            $observacoes_dia = null;
        }

        $stmtInsertTreino->execute([
            ':anamnese_id' => $anamnese_id,
            ':usuario_id' => $usuario_id,
            ':plano_treino_gerado_id' => $plano_treino_gerado_id,
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':nivel_dificuldade' => $nivel_dificuldade,
            ':dia_semana_id' => $dia_semana_id,
            ':semana_numero' => $semana_numero,
            ':parte_inicial' => $parte_inicial,
            ':parte_principal' => $parte_principal,
            ':volta_calma' => $volta_calma,
            ':fcmax' => $fcmax_val,
            ':volume_total' => $volume_total,
            ':grupos_musculares' => $grupos_musculares,
            ':num_series' => $numero_series,
            ':intervalo' => $intervalo,
            ':num_repeticoes' => $numero_repeticoes,
            ':intensidade' => $intensidade,
            ':carga_interna' => $carga_interna,
            ':observacoes' => $observacoes_dia
        ]);

        $treino_id = $pdo->lastInsertId();
        $idsTreinosInseridos[] = $treino_id;

        $sqlInsertExercicio = "INSERT INTO treino_exercicios (
            treino_id, nome_exercicio, exercicio_id, series, repeticoes, tempo, peso, tempo_descanso, observacoes, tipo
        ) VALUES (
            :treino_id, :nome_exercicio, :exercicio_id, :series, :repeticoes, :tempo, :peso, :tempo_descanso, :observacoes, :tipo
        )";
        
        $stmtInsertExercicio = $pdo->prepare($sqlInsertExercicio);

        $inserirExerciciosParte = function($exerciciosParte) use ($stmtInsertExercicio, $treino_id, $pdo) {
            if (is_string($exerciciosParte)) {
                $exerciciosParte = json_decode($exerciciosParte, true);
            }
            if (!is_array($exerciciosParte)) return;
            
            foreach ($exerciciosParte as $exercicio) {
                $nome_exercicio = $exercicio['nome_item'] ?? null;
                $observacoes = json_encode($exercicio, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $series = null;
                $repeticoes = null;
                $tempo = null;
                $tipo = 'livre';
                
                if (isset($exercicio['detalhes_item'])) {
                    if (preg_match('/(\d+)x(\d+)/i', $exercicio['detalhes_item'], $matches)) {
                        $series = $matches[1];
                        $repeticoes = $matches[2];
                        $tipo = 'repeticao';
                    } elseif (preg_match('/(\d+)\s*séries?\s*de\s*(\d+)\s*repet/i', $exercicio['detalhes_item'], $matches)) {
                        $series = $matches[1];
                        $repeticoes = $matches[2];
                        $tipo = 'repeticao';
                    }
                    if (preg_match('/(\d+)\s*min/i', $exercicio['detalhes_item'], $matches)) {
                        $tempo = $matches[1] . ' min';
                        $tipo = 'tempo';
                    } elseif (preg_match('/(\d+)\s*seg/i', $exercicio['detalhes_item'], $matches)) {
                        $tempo = $matches[1] . ' seg';
                        $tipo = 'tempo';
                    }
                }
                
                $exercicio_id = $exercicio['exercicio_id'] ?? null;
                $series = $series !== null ? (int)$series : null;
                $repeticoes = $repeticoes !== null ? (string)$repeticoes : null;
                $peso = $exercicio['peso'] ?? null;
                $tempo_descanso = $exercicio['tempo_descanso'] ?? null;
                
                $stmtInsertExercicio->execute([
                    ':treino_id' => $treino_id,
                    ':nome_exercicio' => $nome_exercicio,
                    ':exercicio_id' => $exercicio_id,
                    ':series' => $series,
                    ':repeticoes' => $repeticoes,
                    ':tempo' => $tempo,
                    ':peso' => $peso,
                    ':tempo_descanso' => $tempo_descanso,
                    ':observacoes' => $observacoes,
                    ':tipo' => $tipo
                ]);
            }
        };

        $inserirExerciciosParte(isset($diaTreino['parte_inicial']) && is_array($diaTreino['parte_inicial']) ? $diaTreino['parte_inicial'] : []);
        $inserirExerciciosParte(isset($diaTreino['parte_principal']) && is_array($diaTreino['parte_principal']) ? $diaTreino['parte_principal'] : []);
        $inserirExerciciosParte(isset($diaTreino['volta_calma']) && is_array($diaTreino['volta_calma']) ? $diaTreino['volta_calma'] : []);
    }

    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Plano de treino salvo com sucesso.",
        "plano_treino_gerado_id" => $plano_treino_gerado_id,
        "treino_ids" => $idsTreinosInseridos
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $code = $e->getCode();
    $statusCode = (is_numeric($code) && (int)$code >= 400 && (int)$code < 600) ? (int)$code : 500;
    http_response_code($statusCode);
    error_log("Erro em save.php para inscricao_id $inscricao_id: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => $e->getMessage() ?: "Ocorreu um erro interno ao salvar o plano de treino."]);
}

