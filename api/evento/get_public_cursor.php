<?php
header('Content-Type: application/json');
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    $evento_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$evento_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
        exit();
    }
    
                $sql = "
                SELECT 
                    e.id,
                    e.nome,
                    e.descricao,
                    COALESCE(e.data_realizacao, e.data_inicio) as data_evento,
                    e.hora_inicio,
                    e.local as endereco,
                    e.cidade,
                    e.estado,
                    e.cep,
                    e.limite_vagas as limite_participantes,
                    e.status,
                    e.imagem,
                    e.data_criacao,
                    u.nome_completo as organizador,
                    u.email as email_organizador,
                    CONCAT(e.cidade, ' - ', e.estado) as local_formatado,
                    DATE_FORMAT(COALESCE(e.data_realizacao, e.data_inicio), '%d/%m/%Y') as data_formatada,
                    TIME_FORMAT(e.hora_inicio, '%H:%M') as hora_formatada,
                    (SELECT COUNT(*) FROM inscricoes i WHERE i.evento_id = e.id) as inscritos
                FROM eventos e
                LEFT JOIN usuarios u ON e.organizador_id = u.id
                WHERE e.id = ? AND e.status = 'ativo'
            ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou não está ativo']);
        exit();
    }
    
    // Buscar modalidades com categorias
    $sql_modalidades = "
        SELECT 
            m.id,
            m.nome as nome_modalidade,
            m.descricao,
            m.distancia,
            m.tipo_prova,
            m.limite_vagas,
            c.id as categoria_id,
            c.nome as nome_categoria,
            c.descricao as categoria_descricao,
            c.tipo_publico,
            c.idade_min,
            c.idade_max,
            c.desconto_idoso
        FROM modalidades m
        INNER JOIN categorias c ON m.categoria_id = c.id
        WHERE m.evento_id = ? AND m.ativo = 1
        ORDER BY c.nome, m.nome
    ";
    
    $stmt_modalidades = $pdo->prepare($sql_modalidades);
    $stmt_modalidades->execute([$evento_id]);
    $modalidades = $stmt_modalidades->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar modalidades
    $modalidades_formatadas = [];
    foreach ($modalidades as $modalidade) {
        $modalidades_formatadas[] = [
            'id' => $modalidade['id'],
            'nome' => $modalidade['nome_modalidade'],
            'descricao' => $modalidade['descricao'],
            'distancia' => $modalidade['distancia'],
            'tipo_prova' => $modalidade['tipo_prova'],
            'limite_vagas' => $modalidade['limite_vagas'],
            'categoria' => [
                'id' => $modalidade['categoria_id'],
                'nome' => $modalidade['nome_categoria'],
                'descricao' => $modalidade['categoria_descricao'],
                'tipo_publico' => $modalidade['tipo_publico'],
                'idade_min' => $modalidade['idade_min'],
                'idade_max' => $modalidade['idade_max'],
                'desconto_idoso' => $modalidade['desconto_idoso']
            ]
        ];
    }
    
    $evento['modalidades'] = $modalidades_formatadas;
    
    echo json_encode([
        'success' => true,
        'evento' => $evento
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar evento público: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
