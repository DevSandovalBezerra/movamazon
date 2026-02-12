<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db.php';

try {
    $evento_id = $_GET['evento_id'] ?? null;
    $tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : 'inscricao';

    $tiposValidos = ['inscricao', 'anamnese', 'treino'];
    if (!in_array($tipo, $tiposValidos)) {
        $tipo = 'inscricao';
    }

    // 1. Buscar termo ativo da plataforma para o tipo solicitado
    $sql = "
        SELECT 
            te.id,
            te.titulo,
            te.conteudo,
            te.versao,
            COALESCE(te.tipo, 'inscricao') as tipo
        FROM termos_eventos te
        WHERE te.ativo = 1
          AND COALESCE(te.tipo, 'inscricao') = :tipo
        ORDER BY te.data_criacao DESC
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['tipo' => $tipo]);
    $termos = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Se não encontrou termo da plataforma, usar regulamento do evento como fallback (quando evento_id fornecido)
    if ((!$termos || empty($termos['conteudo'])) && $evento_id) {
        $sql = "
            SELECT 
                e.id,
                'Regulamento do Evento' as titulo,
                e.regulamento as conteudo,
                '1.0' as versao,
                'fallback' as tipo
            FROM eventos e
            WHERE e.id = ? AND e.deleted_at IS NULL
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evento_id]);
        $termos = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. Se ainda não encontrou nada, usar termos padrão
    if (!$termos || empty($termos['conteudo'])) {
        $termos = [
            'id' => 0,
            'titulo' => 'Termos e Condições',
            'conteudo' => 'Ao se inscrever neste evento, você concorda com os termos e condições estabelecidos pela organização.',
            'versao' => '1.0',
            'tipo' => 'padrao'
        ];
    }

    echo json_encode([
        'success' => true,
        'termos' => $termos
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
