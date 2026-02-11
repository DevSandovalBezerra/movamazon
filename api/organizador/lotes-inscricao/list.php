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
    $evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
    $modalidade_id = isset($_GET['modalidade_id']) ? (int)$_GET['modalidade_id'] : 0;
    $tipo_publico = isset($_GET['tipo_publico']) ? $_GET['tipo_publico'] : '';
    $ativo = isset($_GET['ativo']) ? (int)$_GET['ativo'] : 1;

    error_log('🔍 API lotes/list.php - Evento ID: ' . $evento_id);
    error_log('🔍 API lotes/list.php - User ID: ' . ($_SESSION['user_id'] ?? 'NÃO DEFINIDO'));

    // Validar se o evento pertence ao organizador e não está excluído
    if ($evento_id > 0) {
        $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
        $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Evento não encontrado, foi excluído ou não autorizado');
        }
    }

    // Construir query base
    $where_conditions = ["li.ativo = ?"];
    $params = [$ativo];

    if ($evento_id > 0) {
        $where_conditions[] = "li.evento_id = ?";
        $params[] = $evento_id;
    }

    if ($tipo_publico) {
        $where_conditions[] = "c.tipo_publico = ?";
        $params[] = $tipo_publico;
    }

    $where_clause = implode(" AND ", $where_conditions);

    // Query para contar total (estrutura otimizada)
    $countQuery = "
        SELECT COUNT(li.id) as total 
        FROM lotes_inscricao li
        LEFT JOIN modalidades m ON li.modalidade_id = m.id
        WHERE $where_clause
    ";

    if ($modalidade_id > 0) {
        $countQuery .= " AND li.modalidade_id = ?";
        $params[] = $modalidade_id;
    }

    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];

    // Query principal (estrutura otimizada)
    $query = "
        SELECT 
            li.id,
            li.evento_id,
            li.modalidade_id,
            li.numero_lote,
            li.preco,
            li.preco_por_extenso,
            li.data_inicio,
            li.data_fim,
            li.vagas_disponiveis,
            li.taxa_servico,
            li.quem_paga_taxa,
            li.idade_min,
            li.idade_max,
            li.desconto_idoso,
            li.ativo,
            li.created_at,
            e.nome as evento_nome,
            m.nome as modalidade_nome,
            c.nome as categoria_nome,
            c.tipo_publico,
            CONCAT(m.nome, ' - ', c.nome) as modalidade_completa
        FROM lotes_inscricao li
        LEFT JOIN eventos e ON li.evento_id = e.id
        LEFT JOIN modalidades m ON li.modalidade_id = m.id
        LEFT JOIN categorias c ON m.categoria_id = c.id
        WHERE $where_clause
    ";

    if ($modalidade_id > 0) {
        $query .= " AND li.modalidade_id = ?";
    }

    $query .= " ORDER BY li.evento_id, li.numero_lote, li.modalidade_id";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar dados
    $lotesFormatados = [];
    foreach ($lotes as $lote) {
        // Calcular status do lote
        $hoje = date('Y-m-d');
        $status = 'inativo';
        if ($lote['ativo']) {
            if ($lote['data_inicio'] <= $hoje && $lote['data_fim'] >= $hoje) {
                $status = 'ativo';
            } elseif ($lote['data_inicio'] > $hoje) {
                $status = 'futuro';
            } else {
                $status = 'expirado';
            }
        }

        // Formatar preço
        $preco_formatado = 'R$ ' . number_format($lote['preco'], 2, ',', '.');

        // Formatar taxa
        $taxa_formatada = $lote['taxa_servico'] > 0 ? 'R$ ' . number_format($lote['taxa_servico'], 2, ',', '.') : 'Não há';

        $lotesFormatados[] = [
            'id' => $lote['id'],
            'evento_id' => $lote['evento_id'],
            'evento_nome' => $lote['evento_nome'],
            'modalidade_id' => $lote['modalidade_id'],
            'modalidade_nome' => $lote['modalidade_nome'],
            'categoria_nome' => $lote['categoria_nome'],
            'modalidade_completa' => $lote['modalidade_completa'],
            'numero_lote' => $lote['numero_lote'],
            'tipo_publico' => $lote['tipo_publico'],
            'tipo_publico_formatado' => $lote['tipo_publico'] === 'comunidade_academica' ? 'Comunidade Acadêmica' : 'Público Geral',
            'preco' => $lote['preco'],
            'preco_formatado' => $preco_formatado,
            'preco_por_extenso' => $lote['preco_por_extenso'],
            'data_inicio' => $lote['data_inicio'],
            'data_fim' => $lote['data_fim'],
            'data_inicio_formatada' => date('d/m/Y', strtotime($lote['data_inicio'])),
            'data_fim_formatada' => date('d/m/Y', strtotime($lote['data_fim'])),
            'vagas_disponiveis' => $lote['vagas_disponiveis'],
            'taxa_servico' => $lote['taxa_servico'],
            'taxa_formatada' => $taxa_formatada,
            'quem_paga_taxa' => $lote['quem_paga_taxa'],
            'quem_paga_taxa_formatado' => $lote['quem_paga_taxa'] === 'organizador' ? 'Organizador' : 'Participante',
            'idade_min' => $lote['idade_min'],
            'idade_max' => $lote['idade_max'],
            'faixa_etaria' => $lote['idade_min'] . ' a ' . $lote['idade_max'] . ' anos',
            'desconto_idoso' => (bool)$lote['desconto_idoso'],
            'desconto_idoso_formatado' => $lote['desconto_idoso'] ? 'Sim' : 'Não',
            'ativo' => (bool)$lote['ativo'],
            'status' => $status,
            'created_at' => $lote['created_at']
        ];
    }

    // error_log("✅ API lotes-inscricao/list.php - Retornando " . count($lotesFormatados) . " lotes");

    echo json_encode([
        'success' => true,
        'lotes' => $lotesFormatados,
        'total' => $total
    ]);
} catch (Exception $e) {
    error_log("💥 Erro ao listar lotes: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}
