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
    if (!isset($input['id']) || !isset($input['evento_id']) || !isset($input['numero_lote']) || 
        !isset($input['preco']) || !isset($input['data_inicio']) || !isset($input['data_fim'])) {
        throw new Exception('Campos obrigatórios: id, evento_id, numero_lote, preco, data_inicio, data_fim');
    }

    // Validar que modalidades foi informado e é um array
    if (!isset($input['modalidades']) || !is_array($input['modalidades']) || empty($input['modalidades'])) {
        throw new Exception('Selecione pelo menos uma modalidade');
    }

    $lote_id = (int)$input['id'];
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

    // Validar se o lote existe e pertence ao organizador
    $stmt = $pdo->prepare("
        SELECT li.id, li.numero_lote, li.evento_id FROM lotes_inscricao li
        INNER JOIN eventos e ON li.evento_id = e.id
        WHERE li.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
    ");
    $stmt->execute([$lote_id, $organizador_id, $usuario_id]);
    $lote_existente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$lote_existente) {
        throw new Exception('Lote não encontrado ou não autorizado');
    }

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

    // Buscar todos os lotes com mesmo numero_lote e evento_id
    $stmt = $pdo->prepare("SELECT id, modalidade_id FROM lotes_inscricao WHERE evento_id = ? AND numero_lote = ?");
    $stmt->execute([$evento_id, $numero_lote]);
    $lotes_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $modalidades_existentes = array_map(function($lote) {
        return (int)$lote['modalidade_id'];
    }, $lotes_existentes);
    
    $ids_lotes_existentes = array_map(function($lote) {
        return (int)$lote['id'];
    }, $lotes_existentes);

    // Identificar modalidades a adicionar, remover e manter
    $modalidades_para_adicionar = array_diff($modalidades_validas, $modalidades_existentes);
    $modalidades_para_remover = array_diff($modalidades_existentes, $modalidades_validas);
    $modalidades_para_manter = array_intersect($modalidades_existentes, $modalidades_validas);

    // Iniciar transação
    $pdo->beginTransaction();

    // Remover lotes de modalidades não selecionadas
    if (!empty($modalidades_para_remover)) {
        $placeholders = str_repeat('?,', count($modalidades_para_remover) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM lotes_inscricao WHERE evento_id = ? AND numero_lote = ? AND modalidade_id IN ($placeholders)");
        $params = array_merge([$evento_id, $numero_lote], array_map('intval', $modalidades_para_remover));
        $stmt->execute($params);
    }

    // Atualizar dados compartilhados em todos os lotes existentes (mesmo os que serão mantidos)
    if (!empty($modalidades_para_manter)) {
        $placeholders = str_repeat('?,', count($modalidades_para_manter) - 1) . '?';
        $stmt = $pdo->prepare("
            UPDATE lotes_inscricao SET
                preco = ?,
                preco_por_extenso = ?,
                data_inicio = ?,
                data_fim = ?,
                vagas_disponiveis = ?,
                taxa_servico = ?,
                quem_paga_taxa = ?,
                idade_min = ?,
                idade_max = ?,
                desconto_idoso = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE evento_id = ? AND numero_lote = ? AND modalidade_id IN ($placeholders)
        ");
        $params = array_merge([
            $preco, $preco_por_extenso, $data_inicio, $data_fim, $vagas_disponiveis,
            $taxa_servico, $quem_paga_taxa, $idade_min, $idade_max, $desconto_idoso,
            $evento_id, $numero_lote
        ], array_map('intval', $modalidades_para_manter));
        $stmt->execute($params);
    }

    // Criar novos lotes para modalidades adicionadas
    $lotes_criados = [];
    if (!empty($modalidades_para_adicionar)) {
        $stmt = $pdo->prepare("
            INSERT INTO lotes_inscricao (
                evento_id, modalidade_id, numero_lote, preco, preco_por_extenso,
                data_inicio, data_fim, vagas_disponiveis, taxa_servico, quem_paga_taxa,
                idade_min, idade_max, desconto_idoso, ativo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        
        foreach ($modalidades_para_adicionar as $modalidade_id) {
            $stmt->execute([
                $evento_id, (int)$modalidade_id, $numero_lote, $preco, $preco_por_extenso,
                $data_inicio, $data_fim, $vagas_disponiveis, $taxa_servico, $quem_paga_taxa,
                $idade_min, $idade_max, $desconto_idoso
            ]);
            $lotes_criados[] = $pdo->lastInsertId();
        }
    }

    // Commit da transação
    $pdo->commit();

    error_log("✅ Lote(s) de inscrição atualizado(s). Removidos: " . count($modalidades_para_remover) . ", Mantidos: " . count($modalidades_para_manter) . ", Criados: " . count($modalidades_para_adicionar));

    echo json_encode([
        'success' => true,
        'message' => 'Lote(s) atualizado(s) com sucesso',
        'lotes_atualizados' => count($modalidades_para_manter),
        'lotes_criados' => count($lotes_criados),
        'lotes_removidos' => count($modalidades_para_remover)
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("💥 Erro ao atualizar lote: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 
