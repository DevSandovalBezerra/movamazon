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
    
    if (!$evento_id) {
        throw new Exception('ID do evento não informado');
    }

    // Validar se o evento pertence ao organizador
    $stmt = $pdo->prepare("SELECT id, nome FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$evento) {
        throw new Exception('Evento não encontrado ou não autorizado');
    }

    // Buscar modalidades do evento
    $stmt = $pdo->prepare("
        SELECT 
            m.id as modalidade_id,
            m.nome as nome_modalidade,
            m.distancia,
            m.tipo_prova,
            m.limite_vagas,
            c.nome as nome_categoria,
            c.tipo_publico,
            CONCAT(m.nome, ' - ', c.nome) as modalidade_completa,
            m.ativo
        FROM modalidades m
        INNER JOIN categorias c ON m.categoria_id = c.id
        WHERE m.evento_id = ? AND m.ativo = 1
        ORDER BY c.nome, m.nome
    ");
    $stmt->execute([$evento_id]);
    $modalidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar dados
    $modalidadesFormatadas = [];
    foreach ($modalidades as $modalidade) {
        $modalidadesFormatadas[] = [
            'id' => $modalidade['modalidade_id'],
            'nome_modalidade' => $modalidade['nome_modalidade'], // Manter para compatibilidade
            'nome' => $modalidade['nome_modalidade'], // Adicionar (igual API modalidades)
            'categoria' => $modalidade['nome_categoria'], // Manter para compatibilidade
            'categoria_nome' => $modalidade['nome_categoria'], // Adicionar (igual API modalidades)
            'distancia' => $modalidade['distancia'],
            'tipo_prova' => $modalidade['tipo_prova'],
            'modalidade_completa' => $modalidade['modalidade_completa'],
            'ativo' => (bool)$modalidade['ativo']
        ];
    }

    //error_log("✅ API lotes-inscricao/modalidades.php - Retornando " . count($modalidadesFormatadas) . " modalidades para evento ID: $evento_id");

    echo json_encode([
        'success' => true,
        'evento' => [
            'id' => $evento['id'],
            'nome' => $evento['nome']
        ],
        'modalidades' => $modalidadesFormatadas
    ]);

} catch (Exception $e) {
    error_log("💥 Erro ao buscar modalidades: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 
