<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Query base corrigida
    $sql = "SELECT 
                k.id,
                k.nome,
                k.descricao,
                k.valor,
                k.preco_calculado,
                COALESCE(k.foto_kit, '') as foto_kit,
                COALESCE(k.disponivel_venda, 1) as disponivel_venda,
                k.ativo,
                k.data_criacao,
                COALESCE(k.updated_at, k.data_criacao) as updated_at,
                k.evento_id,
                k.modalidade_evento_id,
                k.kit_template_id,
                e.nome as evento_nome,
                e.data_inicio as data_evento,
                COALESCE(m.nome, 'Modalidade não definida') as modalidade_nome,
                COALESCE(kt.nome, 'Template não definido') as template_nome
            FROM kits_eventos k
            INNER JOIN eventos e ON k.evento_id = e.id
            LEFT JOIN modalidades m ON k.modalidade_evento_id = m.id
            LEFT JOIN kit_templates kt ON k.kit_template_id = kt.id
            WHERE (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
            ORDER BY e.data_inicio DESC, k.nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$organizador_id, $usuario_id]);
    $kits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar modalidades associadas a cada kit
    // Suporta tanto o relacionamento N:N (kit_modalidade_evento) quanto o legado (modalidade_evento_id direto)
    foreach ($kits as &$kit) {
        // Primeiro, buscar na tabela de relacionamento N:N
        $stmt_modalidades = $pdo->prepare("
            SELECT modalidade_evento_id 
            FROM kit_modalidade_evento 
            WHERE kit_id = ?
        ");
        $stmt_modalidades->execute([$kit['id']]);
        $modalidades = $stmt_modalidades->fetchAll(PDO::FETCH_COLUMN);
        
        // Se não encontrou na tabela N:N, usar o campo legado modalidade_evento_id
        if (empty($modalidades) && !empty($kit['modalidade_evento_id'])) {
            $modalidades = [$kit['modalidade_evento_id']];
        }
        
        $kit['modalidades'] = $modalidades;
        
        $kit['data_criacao_formatada'] = date('d/m/Y H:i', strtotime($kit['data_criacao']));
        $kit['updated_at_formatada'] = $kit['updated_at'] ? date('d/m/Y H:i', strtotime($kit['updated_at'])) : null;
        $kit['data_evento_formatada'] = date('d/m/Y', strtotime($kit['data_evento']));
        $kit['disponivel_venda'] = (bool)$kit['disponivel_venda'];
        $kit['ativo'] = (bool)$kit['ativo'];
        $kit['valor_formatado'] = 'R$ ' . number_format($kit['valor'], 2, ',', '.');
        $kit['preco_calculado_formatado'] = $kit['preco_calculado'] ? 'R$ ' . number_format($kit['preco_calculado'], 2, ',', '.') : null;
    }

    echo json_encode([
        'success' => true,
        'data' => $kits
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
