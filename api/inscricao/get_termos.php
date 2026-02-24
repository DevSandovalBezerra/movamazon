<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db.php';

function column_exists(PDO $pdo, $table, $column)
{
    static $cache = [];
    $cacheKey = $table . '.' . $column;
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    $sql = "SELECT 1
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :table_name
              AND COLUMN_NAME = :column_name
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'table_name' => $table,
        'column_name' => $column,
    ]);

    $exists = (bool) $stmt->fetchColumn();
    $cache[$cacheKey] = $exists;
    return $exists;
}

try {
    $evento_id = isset($_GET['evento_id']) ? (int) $_GET['evento_id'] : null;
    $modalidade_id = isset($_GET['modalidade_id']) ? (int) $_GET['modalidade_id'] : 0;
    $tipo = isset($_GET['tipo']) ? trim((string) $_GET['tipo']) : 'inscricao';

    $tiposValidos = ['inscricao', 'anamnese', 'treino', 'modalidade'];
    if (!in_array($tipo, $tiposValidos, true)) {
        $tipo = 'inscricao';
    }

    $hasTipoColumn = column_exists($pdo, 'termos_eventos', 'tipo');
    $hasEventoColumn = column_exists($pdo, 'termos_eventos', 'evento_id');
    $hasModalidadeColumn = column_exists($pdo, 'termos_eventos', 'modalidade_id');

    $termos = null;

    // 1) Prioriza termo por modalidade, se houver suporte no schema.
    if ($modalidade_id > 0 && $hasModalidadeColumn) {
        $sqlModalidade = "
            SELECT
                te.id,
                te.titulo,
                te.conteudo,
                te.versao,
                'modalidade' AS tipo
            FROM termos_eventos te
            WHERE te.ativo = 1
              AND te.modalidade_id = :modalidade_id
        ";
        $paramsModalidade = ['modalidade_id' => $modalidade_id];

        if ($evento_id && $hasEventoColumn) {
            $sqlModalidade .= " AND te.evento_id = :evento_id ";
            $paramsModalidade['evento_id'] = $evento_id;
        }

        $sqlModalidade .= " ORDER BY te.data_criacao DESC LIMIT 1 ";
        $stmt = $pdo->prepare($sqlModalidade);
        $stmt->execute($paramsModalidade);
        $termos = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 2) Busca termo geral da plataforma.
    if (!$termos || empty($termos['conteudo'])) {
        if ($hasTipoColumn) {
            $tipoConsulta = in_array($tipo, ['inscricao', 'anamnese', 'treino'], true) ? $tipo : 'inscricao';
            $sql = "
                SELECT
                    te.id,
                    te.titulo,
                    te.conteudo,
                    te.versao,
                    COALESCE(te.tipo, 'inscricao') AS tipo
                FROM termos_eventos te
                WHERE te.ativo = 1
                  AND COALESCE(te.tipo, 'inscricao') = :tipo
                ORDER BY te.data_criacao DESC
                LIMIT 1
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['tipo' => $tipoConsulta]);
            $termos = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $sql = "
                SELECT
                    te.id,
                    te.titulo,
                    te.conteudo,
                    te.versao,
                    'inscricao' AS tipo
                FROM termos_eventos te
                WHERE te.ativo = 1
                ORDER BY te.data_criacao DESC
                LIMIT 1
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $termos = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    // 3) Fallback para regulamento do evento.
    if ((!$termos || empty($termos['conteudo'])) && $evento_id) {
        $sql = "
            SELECT
                e.id,
                'Regulamento do Evento' AS titulo,
                e.regulamento AS conteudo,
                '1.0' AS versao,
                'fallback' AS tipo
            FROM eventos e
            WHERE e.id = ? AND e.deleted_at IS NULL
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evento_id]);
        $termos = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4) Fallback padrao do sistema.
    if (!$termos || empty($termos['conteudo'])) {
        $termos = [
            'id' => 0,
            'titulo' => 'Termos e Condicoes',
            'conteudo' => 'Ao se inscrever neste evento, voce concorda com os termos e condicoes estabelecidos pela organizacao.',
            'versao' => '1.0',
            'tipo' => 'padrao'
        ];
    }

    echo json_encode([
        'success' => true,
        'termos' => $termos
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
