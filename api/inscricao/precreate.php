<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Método não permitido']);
	exit;
}

if (!isset($_SESSION['user_id'])) {
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
	exit;
}

try {
	$input = json_decode(file_get_contents('php://input'), true) ?: [];
	error_log("DEBUG precreate.php - Input recebido: " . print_r($input, true));
	
	$usuarioId = (int) $_SESSION['user_id'];
	$eventoId = (int) ($input['evento_id'] ?? 0);
	$modalidadeId = (int) ($input['modalidade_id'] ?? 0); // corresponde a modalidades.id
	$tamanhoCamiseta = trim((string) ($input['tamanho_camiseta'] ?? ''));
	$valorModalidades = (float) ($input['valor_modalidades'] ?? 0);
	$valorExtras = (float) ($input['valor_extras'] ?? 0);
	$valorDesconto = (float) ($input['valor_desconto'] ?? 0);
	$cupom = isset($input['cupom']) ? (string) $input['cupom'] : null;
	$produtosExtras = $input['produtos_extras'] ?? [];
	$seguro = !empty($input['seguro']) ? 1 : 0;
	
	error_log("DEBUG precreate.php - Dados processados: eventoId=$eventoId, modalidadeId=$modalidadeId, tamanhoCamiseta=$tamanhoCamiseta");

	if ($eventoId <= 0 || $modalidadeId <= 0 || $tamanhoCamiseta === '') {
		http_response_code(400);
		echo json_encode(['success' => false, 'message' => 'Dados obrigatórios ausentes']);
		exit;
	}

	$valorSeguro = $seguro ? 25.0 : 0.0;
	$valorTotal = max(0, $valorModalidades + $valorExtras + $valorSeguro - $valorDesconto);

	$pdo->beginTransaction();

	// Verificar se já existe inscrição para este usuário/evento
	$checkSql = "SELECT id, external_reference FROM inscricoes WHERE usuario_id = ? AND evento_id = ?";
	$checkStmt = $pdo->prepare($checkSql);
	$checkStmt->execute([$usuarioId, $eventoId]);
	$inscricaoExistente = $checkStmt->fetch();

	if ($inscricaoExistente) {
		// Usar inscrição existente
		$inscricaoId = (int) $inscricaoExistente['id'];
		$externalRef = $inscricaoExistente['external_reference'] ?: 'MOVAMAZON_' . $inscricaoId;
		
		// Atualizar dados da inscrição existente (reabertura: status pendente e limpa expiração/forma)
		$updateSql = "UPDATE inscricoes SET 
			modalidade_evento_id = ?, 
			tamanho_camiseta = ?, 
			valor_total = ?, 
			valor_desconto = ?, 
			cupom_aplicado = ?, 
			produtos_extras_ids = ?, 
			seguro_contratado = ?,
			external_reference = ?,
			status = 'pendente',
			status_pagamento = 'pendente',
			data_inscricao = NOW(),
			data_expiracao_pagamento = NULL,
			forma_pagamento = NULL
			WHERE id = ?";
		$updateStmt = $pdo->prepare($updateSql);
		$updateStmt->execute([
			$modalidadeId,
			$tamanhoCamiseta,
			$valorTotal,
			$valorDesconto,
			$cupom,
			json_encode($produtosExtras, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			$seguro,
			$externalRef,
			$inscricaoId
		]);
		
		error_log("DEBUG precreate.php - Inscrição existente atualizada: ID=$inscricaoId");
	} else {
		// Criar nova inscrição
		$sql = "INSERT INTO inscricoes (
			usuario_id, evento_id, modalidade_evento_id, tamanho_camiseta,
			status, status_pagamento, valor_total, valor_desconto, cupom_aplicado,
			produtos_extras_ids, data_inscricao, seguro_contratado
		) VALUES (
			?, ?, ?, ?,
			'pendente', 'pendente', ?, ?, ?,
			?, NOW(), ?
		)";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
			$usuarioId,
			$eventoId,
			$modalidadeId,
			$tamanhoCamiseta,
			$valorTotal,
			$valorDesconto,
			$cupom,
			json_encode($produtosExtras, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			$seguro
		]);
		$inscricaoId = (int) $pdo->lastInsertId();

		$externalRef = 'MOVAMAZON_' . $inscricaoId;
		$pdo->prepare("UPDATE inscricoes SET external_reference = ? WHERE id = ?")
			->execute([$externalRef, $inscricaoId]);
		
		error_log("DEBUG precreate.php - Nova inscrição criada: ID=$inscricaoId");
	}

	$pdo->commit();

	echo json_encode([
		'success' => true,
		'inscricao_id' => $inscricaoId,
		'external_reference' => $externalRef,
		'valor_total' => $valorTotal
	]);
	exit;

} catch (Throwable $e) {
	if ($pdo && $pdo->inTransaction()) {
		$pdo->rollBack();
	}
	error_log('precreate inscricao erro: ' . $e->getMessage());
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Erro interno ao preparar inscrição']);
	exit;
}


