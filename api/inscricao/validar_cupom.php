<?php
/**
 * API de validação de cupom de desconto na inscrição.
 * Usada na etapa Ficha para aplicar cupom e obter valor_desconto (em R$).
 * Tabela: cupons_remessa (coluna codigo_remessa, status, data_inicio, data_validade).
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/inscricao_logger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

$codigo = isset($data['codigo']) ? trim($data['codigo']) : (isset($data['cupom']) ? trim($data['cupom']) : '');
$evento_id = isset($data['evento_id']) ? (int)$data['evento_id'] : 0;
$valor_total = isset($data['valor_total']) ? (float)$data['valor_total'] : 0;

if ($codigo === '' || $evento_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Código do cupom e evento são obrigatórios']);
    exit;
}

try {
    // Buscar cupom: coluna codigo_remessa, status = 'ativo', vigência (data_inicio/data_validade), evento_id ou global (evento_id IS NULL)
    $sql = "SELECT id, titulo, codigo_remessa, valor_desconto, tipo_valor, tipo_desconto, max_uso, usos_atuais, evento_id, data_inicio, data_validade
            FROM cupons_remessa
            WHERE (evento_id = ? OR evento_id IS NULL)
              AND codigo_remessa = ?
              AND status = 'ativo'
              AND CURDATE() >= data_inicio
              AND CURDATE() <= data_validade
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$evento_id, $codigo]);
    $cupom = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cupom) {
        echo json_encode(['success' => false, 'error' => 'Cupom inválido ou fora do período de validade']);
        exit;
    }

    $usos_atuais = (int)($cupom['usos_atuais'] ?? 0);
    $max_uso = (int)($cupom['max_uso'] ?? 1);

    if ($max_uso > 0 && $usos_atuais >= $max_uso) {
        echo json_encode(['success' => false, 'error' => 'Cupom esgotado']);
        exit;
    }

    $valor_desconto_banco = (float)($cupom['valor_desconto'] ?? 0);
    $tipo_valor = $cupom['tipo_valor'] ?? 'valor_real';

    // Calcular valor do desconto em R$
    if ($tipo_valor === 'percentual') {
        $valor_desconto_final = $valor_total > 0
            ? round($valor_total * ($valor_desconto_banco / 100), 2)
            : 0;
    } else {
        // valor_real ou preco_fixo: valor fixo em R$
        $valor_desconto_final = $valor_desconto_banco;
    }

    // Não dar desconto maior que o total
    if ($valor_desconto_final > $valor_total && $valor_total > 0) {
        $valor_desconto_final = $valor_total;
    }

    $cupom_resposta = [
        'id' => (int)$cupom['id'],
        'codigo' => $cupom['codigo_remessa'],
        'titulo' => $cupom['titulo'] ?? null,
        'valor_desconto' => $valor_desconto_final,
        'tipo_valor' => $tipo_valor,
        'evento_id' => $cupom['evento_id'] ? (int)$cupom['evento_id'] : null,
    ];

    // Log crítico: aplicação de cupom (auditoria e suporte)
    $usuario_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $cupom_id = (int)$cupom['id'];
    $codigo_mascarado = strlen($codigo) > 4 ? substr($codigo, 0, 2) . '***' . substr($codigo, -2) : '****';
    logInscricaoPagamento('SUCCESS', 'CUPOM_APLICADO', [
        'evento_id' => $evento_id,
        'cupom_id' => $cupom_id,
        'codigo_cupom' => $codigo_mascarado,
        'valor_total_inscricao' => $valor_total,
        'valor_desconto_aplicado' => $valor_desconto_final,
        'tipo_valor' => $tipo_valor,
        'usuario_id' => $usuario_id,
        'usos_atuais' => $usos_atuais,
        'max_uso' => $max_uso,
    ]);
    $log_cupom_dir = dirname(__DIR__, 2) . '/logs';
    $log_cupom_file = $log_cupom_dir . '/cupom_aplicacao.log';
    if (!is_dir($log_cupom_dir)) {
        @mkdir($log_cupom_dir, 0755, true);
    }
    $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (strpos($client_ip, ',') !== false) {
        $client_ip = trim(explode(',', $client_ip)[0]);
    }
    $linha_cupom = sprintf(
        "[%s] CUPOM_APLICADO | evento_id=%d | cupom_id=%d | codigo=%s | valor_total=%.2f | valor_desconto=%.2f | tipo=%s | usuario_id=%s | ip=%s\n",
        date('Y-m-d H:i:s'),
        $evento_id,
        $cupom_id,
        $codigo_mascarado,
        $valor_total,
        $valor_desconto_final,
        $tipo_valor,
        $usuario_id ?? 'N/A',
        $client_ip
    );
    @file_put_contents($log_cupom_file, $linha_cupom, FILE_APPEND | LOCK_EX);

    echo json_encode([
        'success' => true,
        'valor_desconto' => $valor_desconto_final,
        'cupom' => $cupom_resposta,
    ]);
} catch (Exception $e) {
    error_log('validar_cupom.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro ao validar cupom']);
}
