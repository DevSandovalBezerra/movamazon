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

try {
    $lote_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$lote_id) {
        throw new Exception('ID do lote não informado');
    }

    // Buscar dados do lote específico primeiro para obter numero_lote e evento_id
    $stmt = $pdo->prepare("
        SELECT 
            li.*,
            e.nome as evento_nome
        FROM lotes_inscricao li
        LEFT JOIN eventos e ON li.evento_id = e.id
        WHERE li.id = ? AND li.evento_id IN (
            SELECT id FROM eventos WHERE organizador_id = ? OR organizador_id IN (
                SELECT organizador_id FROM usuarios WHERE id = ?
            )
        )
    ");
    $stmt->execute([$lote_id, $_SESSION['user_id'], $_SESSION['user_id']]);
    $lote_base = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lote_base) {
        throw new Exception('Lote não encontrado ou não autorizado');
    }

    $evento_id = $lote_base['evento_id'];
    $numero_lote = $lote_base['numero_lote'];

    // Buscar todos os lotes com mesmo numero_lote e evento_id (múltiplas modalidades)
    $stmt = $pdo->prepare("
        SELECT 
            li.*,
            e.nome as evento_nome,
            m.nome as modalidade_nome,
            m.id as modalidade_id,
            c.nome as categoria_nome,
            CONCAT(c.nome, ' - ', m.nome) as modalidade_completa
        FROM lotes_inscricao li
        LEFT JOIN eventos e ON li.evento_id = e.id
        LEFT JOIN modalidades m ON li.modalidade_id = m.id
        LEFT JOIN categorias c ON m.categoria_id = c.id
        WHERE li.evento_id = ? AND li.numero_lote = ?
        ORDER BY li.modalidade_id
    ");
    $stmt->execute([$evento_id, $numero_lote]);
    $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($lotes)) {
        throw new Exception('Lote não encontrado');
    }

    // Usar o primeiro lote como base para dados compartilhados
    $lote = $lotes[0];

    // Coletar todas as modalidades
    $modalidades = [];
    foreach ($lotes as $l) {
        $modalidades[] = [
            'id' => (int)$l['modalidade_id'],
            'nome' => $l['modalidade_nome'],
            'categoria_nome' => $l['categoria_nome'],
            'modalidade_completa' => $l['modalidade_completa']
        ];
    }

    // Formatar dados (estrutura otimizada)
    $loteFormatado = [
        'id' => (int)$lote['id'],
        'evento_id' => (int)$lote['evento_id'],
        'evento_nome' => $lote['evento_nome'],
        'modalidade_id' => (int)$lote['modalidade_id'],
        'modalidades' => $modalidades,
        'numero_lote' => (int)$lote['numero_lote'],
        'preco' => (float)$lote['preco'],
        'preco_formatado' => 'R$ ' . number_format($lote['preco'], 2, ',', '.'),
        'preco_por_extenso' => $lote['preco_por_extenso'],
        'data_inicio' => $lote['data_inicio'],
        'data_fim' => $lote['data_fim'],
        'data_inicio_formatada' => date('d/m/Y', strtotime($lote['data_inicio'])),
        'data_fim_formatada' => date('d/m/Y', strtotime($lote['data_fim'])),
        'vagas_disponiveis' => $lote['vagas_disponiveis'] ? (int)$lote['vagas_disponiveis'] : null,
        'taxa_servico' => (float)$lote['taxa_servico'],
        'taxa_formatada' => $lote['taxa_servico'] > 0 ? 'R$ ' . number_format($lote['taxa_servico'], 2, ',', '.') : 'Não há',
        'quem_paga_taxa' => $lote['quem_paga_taxa'],
        'quem_paga_taxa_formatado' => $lote['quem_paga_taxa'] === 'organizador' ? 'Organizador' : 'Participante',
        'idade_min' => (int)$lote['idade_min'],
        'idade_max' => (int)$lote['idade_max'],
        'faixa_etaria' => $lote['idade_min'] . ' a ' . $lote['idade_max'] . ' anos',
        'desconto_idoso' => (bool)$lote['desconto_idoso'],
        'desconto_idoso_formatado' => $lote['desconto_idoso'] ? 'Sim' : 'Não',
        'ativo' => (bool)$lote['ativo'],
        'created_at' => $lote['created_at'],
        'updated_at' => $lote['updated_at']
    ];

    error_log("✅ API lotes-inscricao/get.php - Retornando dados do lote ID: $lote_id");

    echo json_encode([
        'success' => true,
        'lote' => $loteFormatado
    ]);

} catch (Exception $e) {
    error_log("💥 Erro ao buscar lote: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 
