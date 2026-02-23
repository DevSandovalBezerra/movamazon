<?php
/**
 * API de validacao de cupom de desconto na inscricao.
 * Usada na etapa Ficha para aplicar cupom e obter valor_desconto (em R$).
 * Tabela: cupons_remessa (coluna codigo_remessa, status, data_inicio, data_validade).
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/inscricao_logger.php';
require_once __DIR__ . '/inscricao_service.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo nao permitido']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

$codigo = isset($data['codigo']) ? trim($data['codigo']) : (isset($data['cupom']) ? trim($data['cupom']) : '');
$evento_id = isset($data['evento_id']) ? (int) $data['evento_id'] : 0;
$valor_total = isset($data['valor_total']) ? (float) $data['valor_total'] : 0;

if ($codigo === '' || $evento_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Codigo do cupom e evento sao obrigatorios']);
    exit;
}

try {
    $cupom = buscar_cupom_valido_para_evento($pdo, $evento_id, $codigo);

    if (!$cupom) {
        echo json_encode(['success' => false, 'error' => 'Cupom invalido ou fora do periodo de validade']);
        exit;
    }

    $usos_atuais = (int) ($cupom['usos_atuais'] ?? 0);
    $max_uso = (int) ($cupom['max_uso'] ?? 1);

    if ($max_uso > 0 && $usos_atuais >= $max_uso) {
        echo json_encode(['success' => false, 'error' => 'Cupom esgotado']);
        exit;
    }

    $valor_desconto_banco = (float) ($cupom['valor_desconto'] ?? 0);
    $tipo_valor = $cupom['tipo_valor'] ?? 'valor_real';
    $valor_desconto_final = calcular_valor_desconto_cupom($valor_total, $valor_desconto_banco, $tipo_valor);

    $cupom_resposta = [
        'id' => (int) $cupom['id'],
        'codigo' => $cupom['codigo_remessa'],
        'titulo' => $cupom['titulo'] ?? null,
        'valor_desconto' => $valor_desconto_final,
        'tipo_valor' => $tipo_valor,
        'evento_id' => $cupom['evento_id'] ? (int) $cupom['evento_id'] : null,
    ];

    $usuario_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    $cupom_id = (int) $cupom['id'];
    $codigo_mascarado = mascarar_codigo_cupom($codigo);
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
