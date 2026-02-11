<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticação
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Suportar tanto POST quanto JSON
    $input = $_SERVER['CONTENT_TYPE'] === 'application/json' 
        ? json_decode(file_get_contents('php://input'), true) 
        : $_POST;

    // Validar dados obrigatórios
    if (!isset($input['evento_id']) || !isset($input['numero_lote']) || !isset($input['preco']) || 
        !isset($input['data_inicio']) || !isset($input['data_fim'])) {
        throw new Exception('Campos obrigatórios: evento_id, numero_lote, preco, data_inicio, data_fim');
    }

    // Validar que modalidades foi informado e é um array
    if (!isset($input['modalidades']) || !is_array($input['modalidades']) || empty($input['modalidades'])) {
        throw new Exception('Selecione pelo menos uma modalidade');
    }

    $evento_id = (int)$input['evento_id'];
    $modalidades = $input['modalidades'];
    $numero_lote = (int)$input['numero_lote'];
    $preco = (float)$input['preco'];
    $data_inicio = $input['data_inicio'];
    $data_fim = $input['data_fim'];
    $preco_por_extenso = $input['preco_por_extenso'] ?? '';
    $vagas_disponiveis = isset($input['vagas_disponiveis']) ? (int)$input['vagas_disponiveis'] : null;
    $taxa_servico = isset($input['taxa_servico']) ? (float)$input['taxa_servico'] : 0;
    $quem_paga_taxa = $input['quem_paga_taxa'] ?? 'participante';
    $idade_min = isset($input['idade_min']) ? (int)$input['idade_min'] : 0;
    $idade_max = isset($input['idade_max']) ? (int)$input['idade_max'] : 100;
    $desconto_idoso = isset($input['desconto_idoso']) ? (bool)$input['desconto_idoso'] : false;

    // Validar se o evento pertence ao organizador
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Evento não encontrado ou não autorizado');
    }

    // Validar datas
    if (strtotime($data_inicio) >= strtotime($data_fim)) {
        throw new Exception('Data de início deve ser anterior à data de fim');
    }

    // Validar quem paga taxa
    if (!in_array($quem_paga_taxa, ['organizador', 'participante'])) {
        throw new Exception('Quem paga taxa inválido');
    }

    // Validar se todas as modalidades pertencem ao evento
    $placeholders = str_repeat('?,', count($modalidades) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id FROM modalidades WHERE id IN ($placeholders) AND evento_id = ?");
    $params = array_merge(array_map('intval', $modalidades), [$evento_id]);
    $stmt->execute($params);
    $modalidades_validas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($modalidades_validas) !== count($modalidades)) {
        throw new Exception('Uma ou mais modalidades não foram encontradas ou não pertencem ao evento');
    }

    // Verificar se já existe lote com mesmo número para alguma das modalidades
    $placeholders = str_repeat('?,', count($modalidades) - 1) . '?';
    $stmt = $pdo->prepare("SELECT modalidade_id FROM lotes_inscricao WHERE evento_id = ? AND modalidade_id IN ($placeholders) AND numero_lote = ?");
    $params = array_merge([$evento_id], array_map('intval', $modalidades), [$numero_lote]);
    $stmt->execute($params);
    $lotes_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($lotes_existentes)) {
        throw new Exception('Já existe um lote com este número para uma ou mais modalidades selecionadas');
    }

    // Iniciar transação
    $pdo->beginTransaction();

    // Preparar statement de inserção
    $stmt = $pdo->prepare("
        INSERT INTO lotes_inscricao (
            evento_id, modalidade_id, numero_lote, preco, preco_por_extenso,
            data_inicio, data_fim, vagas_disponiveis, taxa_servico, quem_paga_taxa,
            idade_min, idade_max, desconto_idoso, ativo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");

    $lotes_criados = [];
    
    // Criar um registro para cada modalidade
    foreach ($modalidades_validas as $modalidade_id) {
        $stmt->execute([
            $evento_id, (int)$modalidade_id, $numero_lote, $preco, $preco_por_extenso,
            $data_inicio, $data_fim, $vagas_disponiveis, $taxa_servico, $quem_paga_taxa,
            $idade_min, $idade_max, $desconto_idoso
        ]);
        
        $lotes_criados[] = $pdo->lastInsertId();
    }

    // Commit da transação
    $pdo->commit();

    error_log("✅ Lotes de inscrição criados: " . count($lotes_criados) . " registro(s)");

    echo json_encode([
        'success' => true,
        'message' => 'Lote(s) criado(s) com sucesso',
        'lotes_ids' => $lotes_criados,
        'total_criados' => count($lotes_criados)
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("💥 Erro ao criar lote: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 
