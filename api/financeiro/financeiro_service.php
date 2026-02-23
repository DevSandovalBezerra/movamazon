<?php
/**
 * Helper Financeiro (Ledger, Saldo, Extrato, Repasse)
 *
 * Convencoes do modulo:
 * - ledger e imutavel (nao atualizar linhas antigas para ajuste financeiro)
 * - reversoes e compensacoes sao sempre novos lancamentos
 * - valores monetarios sempre trafegam em DECIMAL no banco
 */

function fin_decimal($value): float
{
    if ($value === null || $value === '') {
        return 0.0;
    }

    if (is_int($value) || is_float($value)) {
        return round((float) $value, 2);
    }

    $raw = trim((string) $value);
    $raw = preg_replace('/[^0-9,\.\-]/', '', $raw);
    if ($raw === null || $raw === '' || $raw === '-' || $raw === '.' || $raw === ',') {
        return 0.0;
    }

    $hasComma = strpos($raw, ',') !== false;
    $hasDot = strpos($raw, '.') !== false;

    if ($hasComma && $hasDot) {
        if (strrpos($raw, ',') > strrpos($raw, '.')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        } else {
            $raw = str_replace(',', '', $raw);
        }
    } elseif ($hasComma) {
        $raw = str_replace('.', '', $raw);
        $raw = str_replace(',', '.', $raw);
    }

    return is_numeric($raw) ? round((float) $raw, 2) : 0.0;
}

function fin_to_datetime($value): ?string
{
    if (empty($value)) {
        return null;
    }

    $ts = strtotime((string) $value);
    if ($ts === false) {
        return null;
    }

    return date('Y-m-d H:i:s', $ts);
}

function fin_status_saldo_considerado(string $status): bool
{
    return in_array($status, ['disponivel', 'liquidado'], true);
}

function fin_lock_evento(PDO $pdo, int $evento_id): void
{
    $stmt = $pdo->prepare('SELECT GET_LOCK(?, 10) AS l');
    $stmt->execute(['fin_evento_' . $evento_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || (int) $row['l'] !== 1) {
        throw new Exception('Nao foi possivel obter lock do evento para operacao financeira.');
    }
}

function fin_unlock_evento(PDO $pdo, int $evento_id): void
{
    try {
        $stmt = $pdo->prepare('SELECT RELEASE_LOCK(?)');
        $stmt->execute(['fin_evento_' . $evento_id]);
    } catch (Exception $e) {
        // nao interromper fluxo por falha de unlock
    }
}

function fin_evento_pertence_organizador(PDO $pdo, int $evento_id, int $organizador_id, int $usuario_id): bool
{
    $stmt = $pdo->prepare(
        'SELECT id FROM eventos WHERE id = ? AND deleted_at IS NULL AND (organizador_id = ? OR organizador_id = ?) LIMIT 1'
    );
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);

    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}

function fin_obter_config_evento(PDO $pdo, int $evento_id): array
{
    $stmt = $pdo->prepare('SELECT id, percentual_repasse, taxa_pagas FROM eventos WHERE id = ? LIMIT 1');
    $stmt->execute([$evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$evento) {
        throw new Exception('Evento nao encontrado para configuracao financeira.');
    }

    return [
        'percentual_repasse' => isset($evento['percentual_repasse']) ? (float) $evento['percentual_repasse'] : null,
        'taxa_pagas' => isset($evento['taxa_pagas']) ? (float) $evento['taxa_pagas'] : null,
        'prazo_liberacao_dias' => 1,
    ];
}

function fin_calcular_taxa_repasse(PDO $pdo, int $evento_id, float $valor): array
{
    $cfg = fin_obter_config_evento($pdo, $evento_id);
    $percentual = $cfg['percentual_repasse'];
    $fixa = $cfg['taxa_pagas'];

    if ($percentual !== null && $percentual > 0) {
        $taxa = round(($valor * $percentual) / 100, 2);
        return [
            'valor_taxa' => $taxa,
            'tipo' => 'percentual_evento',
            'percentual' => $percentual,
            'valor_fixo' => null,
        ];
    }

    $taxa = round(($fixa !== null && $fixa > 0) ? $fixa : 5.00, 2);
    return [
        'valor_taxa' => $taxa,
        'tipo' => 'fixa_evento',
        'percentual' => null,
        'valor_fixo' => $taxa,
    ];
}

function fin_saldo_disponivel(PDO $pdo, int $evento_id): float
{
    $sql = '
        SELECT
            COALESCE(SUM(CASE WHEN direcao = "credito" AND status IN ("disponivel","liquidado") THEN valor ELSE 0 END), 0) AS creditos_ok,
            COALESCE(SUM(CASE WHEN direcao = "debito"  AND status IN ("disponivel","liquidado") THEN valor ELSE 0 END), 0) AS debitos_ok,
            COALESCE(SUM(CASE WHEN direcao = "debito" AND status = "bloqueado" THEN valor ELSE 0 END), 0) AS bloqueado_debito
        FROM financeiro_ledger
        WHERE evento_id = ?
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$evento_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $saldo = ((float) $row['creditos_ok']) - ((float) $row['debitos_ok']) - ((float) $row['bloqueado_debito']);
    return round($saldo, 2);
}

function fin_normalizar_metadata($metadata): ?string
{
    if ($metadata === null || $metadata === '') {
        return null;
    }

    if (is_array($metadata)) {
        return json_encode($metadata, JSON_UNESCAPED_UNICODE);
    }

    if (is_string($metadata)) {
        return $metadata;
    }

    return json_encode($metadata, JSON_UNESCAPED_UNICODE);
}

function fin_ledger_insert(PDO $pdo, array $dados): int
{
    $sql = '
        INSERT INTO financeiro_ledger
        (evento_id, origem_tipo, origem_id, descricao, direcao, valor, status, ocorrido_em, disponivel_em, metadata, created_by)
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        (int) $dados['evento_id'],
        (string) $dados['origem_tipo'],
        isset($dados['origem_id']) ? (int) $dados['origem_id'] : null,
        (string) $dados['descricao'],
        (string) $dados['direcao'],
        fin_decimal($dados['valor'] ?? 0),
        (string) $dados['status'],
        (string) $dados['ocorrido_em'],
        $dados['disponivel_em'] ?? null,
        fin_normalizar_metadata($dados['metadata'] ?? null),
        isset($dados['created_by']) ? (int) $dados['created_by'] : null,
    ]);

    return (int) $pdo->lastInsertId();
}

function fin_ledger_existe(PDO $pdo, string $origem_tipo, int $origem_id, string $direcao, string $descricao): bool
{
    $stmt = $pdo->prepare(
        'SELECT id FROM financeiro_ledger WHERE origem_tipo = ? AND origem_id = ? AND direcao = ? AND descricao = ? LIMIT 1'
    );
    $stmt->execute([$origem_tipo, $origem_id, $direcao, $descricao]);

    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}

function fin_extrato_listar(PDO $pdo, int $evento_id, array $filtros): array
{
    $page = isset($filtros['page']) ? max(1, (int) $filtros['page']) : 1;
    $per = isset($filtros['per']) ? min(200, max(10, (int) $filtros['per'])) : 20;
    $off = ($page - 1) * $per;

    $where = ['l.evento_id = ?'];
    $params = [$evento_id];

    if (!empty($filtros['dt_ini'])) {
        $where[] = 'l.ocorrido_em >= ?';
        $params[] = $filtros['dt_ini'] . ' 00:00:00';
    }

    if (!empty($filtros['dt_fim'])) {
        $where[] = 'l.ocorrido_em <= ?';
        $params[] = $filtros['dt_fim'] . ' 23:59:59';
    }

    if (!empty($filtros['status'])) {
        $where[] = 'l.status = ?';
        $params[] = $filtros['status'];
    }

    if (!empty($filtros['tipo'])) {
        $where[] = 'l.origem_tipo = ?';
        $params[] = $filtros['tipo'];
    }

    if (!empty($filtros['busca'])) {
        $where[] = '(CAST(l.origem_id AS CHAR) LIKE ? OR l.descricao LIKE ?)';
        $params[] = '%' . $filtros['busca'] . '%';
        $params[] = '%' . $filtros['busca'] . '%';
    }

    $whereSql = 'WHERE ' . implode(' AND ', $where);

    $stmtC = $pdo->prepare("SELECT COUNT(*) AS total FROM financeiro_ledger l $whereSql");
    $stmtC->execute($params);
    $total = (int) $stmtC->fetchColumn();

    $sql = "
        SELECT
            l.id,
            l.origem_tipo,
            l.origem_id,
            l.descricao,
            l.direcao,
            l.valor,
            l.status,
            l.ocorrido_em,
            l.disponivel_em,
            l.metadata,
            l.created_at
        FROM financeiro_ledger l
        $whereSql
        ORDER BY l.ocorrido_em DESC, l.id DESC
        LIMIT $per OFFSET $off
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'page' => $page,
        'per' => $per,
        'total' => $total,
        'items' => $items,
    ];
}

function fin_repasse_agendar(
    PDO $pdo,
    int $evento_id,
    int $beneficiario_id,
    int $conta_id,
    $valor_in,
    string $agendar_para,
    int $user_id = 0
): array {
    $valor = fin_decimal($valor_in);
    if ($valor <= 0) {
        return ['success' => false, 'message' => 'Valor invalido'];
    }

    $dtAgendamento = DateTime::createFromFormat('Y-m-d', $agendar_para);
    if (!$dtAgendamento || $dtAgendamento->format('Y-m-d') !== $agendar_para) {
        return ['success' => false, 'message' => 'agendado_para invalido. Use formato YYYY-MM-DD'];
    }

    try {
        $taxaInfo = fin_calcular_taxa_repasse($pdo, $evento_id, $valor);
        $taxa_repasse = (float) $taxaInfo['valor_taxa'];
        $valor_liquido = round($valor - $taxa_repasse, 2);

        if ($valor_liquido <= 0) {
            return ['success' => false, 'message' => 'Valor nao cobre a taxa de repasse'];
        }

        $pdo->beginTransaction();
        fin_lock_evento($pdo, $evento_id);

        $stmtV = $pdo->prepare(
            'SELECT cb.id
             FROM financeiro_contas_bancarias cb
             INNER JOIN financeiro_beneficiarios b ON b.id = cb.beneficiario_id
             WHERE cb.id = ?
               AND b.id = ?
               AND b.status IN ("ativo","pendente_validacao")
               AND cb.status IN ("ativa","pendente_validacao")
             LIMIT 1'
        );
        $stmtV->execute([$conta_id, $beneficiario_id]);
        if (!$stmtV->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception('Conta/beneficiario invalidos ou inativos.');
        }

        $saldo = fin_saldo_disponivel($pdo, $evento_id);
        if ($valor > $saldo) {
            throw new Exception('Saldo insuficiente para agendar este repasse.');
        }

        $stmtR = $pdo->prepare(
            'INSERT INTO financeiro_repasses
            (evento_id, beneficiario_id, conta_bancaria_id, valor_solicitado, valor_taxa_repasse, valor_liquido, status, agendado_para, solicitado_em)
            VALUES
            (?, ?, ?, ?, ?, ?, "agendado", ?, NOW())'
        );
        $stmtR->execute([$evento_id, $beneficiario_id, $conta_id, $valor, $taxa_repasse, $valor_liquido, $agendar_para]);
        $repasse_id = (int) $pdo->lastInsertId();

        fin_ledger_insert($pdo, [
            'evento_id' => $evento_id,
            'origem_tipo' => 'repasse',
            'origem_id' => $repasse_id,
            'descricao' => 'Repasse agendado (reserva de saldo)',
            'direcao' => 'debito',
            'valor' => $valor,
            'status' => 'bloqueado',
            'ocorrido_em' => date('Y-m-d H:i:s'),
            'metadata' => [
                'movimento_repasse' => 'reserva_bloqueio',
                'agendado_para' => $agendar_para,
                'taxa_repasse' => $taxa_repasse,
                'taxa_regra' => $taxaInfo['tipo'],
                'taxa_percentual' => $taxaInfo['percentual'],
                'valor_liquido' => $valor_liquido,
                'saldo_antes' => $saldo,
            ],
            'created_by' => $user_id,
        ]);

        fin_unlock_evento($pdo, $evento_id);
        $pdo->commit();

        return [
            'success' => true,
            'data' => [
                'repasse_id' => $repasse_id,
                'saldo_antes' => $saldo,
                'valor_solicitado' => round($valor, 2),
                'taxa_repasse' => round($taxa_repasse, 2),
                'valor_liquido' => round($valor_liquido, 2),
                'taxa_regra' => $taxaInfo['tipo'],
            ],
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fin_unlock_evento($pdo, $evento_id);
        error_log('Erro fin_repasse_agendar: ' . $e->getMessage());

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function fin_repasse_processar(
    PDO $pdo,
    int $repasse_id,
    string $novo_status,
    int $user_id = 0,
    ?string $comprovante_url = null,
    ?string $gateway_transfer_id = null,
    ?string $motivo_falha = null
): array {
    $statusPermitidos = ['processando', 'pago', 'falhou', 'cancelado'];
    if (!in_array($novo_status, $statusPermitidos, true)) {
        return ['success' => false, 'message' => 'Status de processamento invalido'];
    }

    $evento_id = 0;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT * FROM financeiro_repasses WHERE id = ? FOR UPDATE');
        $stmt->execute([$repasse_id]);
        $repasse = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$repasse) {
            throw new Exception('Repasse nao encontrado');
        }

        $evento_id = (int) $repasse['evento_id'];
        $status_atual = (string) $repasse['status'];

        fin_lock_evento($pdo, $evento_id);

        if ($status_atual === $novo_status) {
            fin_unlock_evento($pdo, $evento_id);
            $pdo->commit();
            return [
                'success' => true,
                'data' => [
                    'repasse_id' => $repasse_id,
                    'status' => $status_atual,
                    'idempotente' => true,
                ],
            ];
        }

        if (in_array($status_atual, ['pago', 'cancelado'], true)) {
            throw new Exception('Repasse ja finalizado e nao pode mudar de status');
        }

        $processadoEm = in_array($novo_status, ['pago', 'falhou', 'cancelado'], true)
            ? date('Y-m-d H:i:s')
            : null;

        $stmtU = $pdo->prepare(
            'UPDATE financeiro_repasses
             SET status = ?,
                 processado_em = COALESCE(?, processado_em),
                 comprovante_url = COALESCE(?, comprovante_url),
                 gateway_transfer_id = COALESCE(?, gateway_transfer_id),
                 motivo_falha = COALESCE(?, motivo_falha)
             WHERE id = ?'
        );
        $stmtU->execute([
            $novo_status,
            $processadoEm,
            $comprovante_url,
            $gateway_transfer_id,
            $motivo_falha,
            $repasse_id,
        ]);

        $valorSolicitado = fin_decimal($repasse['valor_solicitado']);

        if ($novo_status === 'pago') {
            if (!fin_ledger_existe($pdo, 'repasse', $repasse_id, 'credito', 'Repasse pago (liberacao do bloqueio)')) {
                fin_ledger_insert($pdo, [
                    'evento_id' => $evento_id,
                    'origem_tipo' => 'repasse',
                    'origem_id' => $repasse_id,
                    'descricao' => 'Repasse pago (liberacao do bloqueio)',
                    'direcao' => 'credito',
                    'valor' => $valorSolicitado,
                    'status' => 'liquidado',
                    'ocorrido_em' => date('Y-m-d H:i:s'),
                    'metadata' => [
                        'movimento_repasse' => 'liberacao_bloqueio_pago',
                        'status_anterior' => $status_atual,
                    ],
                    'created_by' => $user_id,
                ]);
            }

            if (!fin_ledger_existe($pdo, 'repasse', $repasse_id, 'debito', 'Repasse pago')) {
                fin_ledger_insert($pdo, [
                    'evento_id' => $evento_id,
                    'origem_tipo' => 'repasse',
                    'origem_id' => $repasse_id,
                    'descricao' => 'Repasse pago',
                    'direcao' => 'debito',
                    'valor' => $valorSolicitado,
                    'status' => 'liquidado',
                    'ocorrido_em' => date('Y-m-d H:i:s'),
                    'metadata' => [
                        'movimento_repasse' => 'saida_repassada',
                        'gateway_transfer_id' => $gateway_transfer_id,
                        'comprovante_url' => $comprovante_url,
                    ],
                    'created_by' => $user_id,
                ]);
            }
        }

        if (in_array($novo_status, ['falhou', 'cancelado'], true)) {
            if (!fin_ledger_existe($pdo, 'repasse', $repasse_id, 'credito', 'Repasse nao concluido (desbloqueio de reserva)')) {
                fin_ledger_insert($pdo, [
                    'evento_id' => $evento_id,
                    'origem_tipo' => 'repasse',
                    'origem_id' => $repasse_id,
                    'descricao' => 'Repasse nao concluido (desbloqueio de reserva)',
                    'direcao' => 'credito',
                    'valor' => $valorSolicitado,
                    'status' => 'liquidado',
                    'ocorrido_em' => date('Y-m-d H:i:s'),
                    'metadata' => [
                        'movimento_repasse' => 'desbloqueio_reserva',
                        'status_final' => $novo_status,
                        'motivo_falha' => $motivo_falha,
                    ],
                    'created_by' => $user_id,
                ]);
            }
        }

        fin_unlock_evento($pdo, $evento_id);
        $pdo->commit();

        return [
            'success' => true,
            'data' => [
                'repasse_id' => $repasse_id,
                'status_anterior' => $status_atual,
                'status_novo' => $novo_status,
            ],
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($evento_id > 0) {
            fin_unlock_evento($pdo, $evento_id);
        }

        error_log('Erro fin_repasse_processar: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function fin_webhook_registrar_evento(
    PDO $pdo,
    string $gateway,
    string $event_id,
    ?string $payment_id,
    ?string $status,
    $payload
): bool {
    $sql = '
        INSERT INTO financeiro_webhook_eventos
        (gateway, event_id, payment_id, status, payload, created_at)
        VALUES
        (?, ?, ?, ?, ?, NOW())
    ';

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $gateway,
            $event_id,
            $payment_id,
            $status,
            fin_normalizar_metadata($payload),
        ]);

        return true;
    } catch (PDOException $e) {
        if ((string) $e->getCode() === '23000') {
            return false;
        }
        throw $e;
    }
}

function fin_resolver_regra_receita(PDO $pdo, int $evento_id): array
{
    $stmt = $pdo->prepare(
        'SELECT
            SUM(CASE WHEN quem_paga_taxa = "organizador" THEN 1 ELSE 0 END) AS qtd_organizador,
            COUNT(*) AS qtd_total
         FROM lotes_inscricao
         WHERE evento_id = ?'
    );
    $stmt->execute([$evento_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['qtd_organizador' => 0, 'qtd_total' => 0];

    $regra = ((int) $row['qtd_organizador'] > 0) ? 'liquida' : 'bruta';

    return [
        'regra_receita' => $regra,
        'fonte' => 'lotes_inscricao.quem_paga_taxa',
        'qtd_lotes' => (int) $row['qtd_total'],
        'qtd_lotes_organizador' => (int) $row['qtd_organizador'],
    ];
}

function fin_registrar_receita_pagamento(
    PDO $pdo,
    int $inscricao_id,
    ?string $payment_id = null,
    ?array $payment_data = null,
    int $user_id = 0
): array {
    $stmtI = $pdo->prepare(
        'SELECT i.id, i.evento_id, i.valor_total, i.lote_inscricao_id
         FROM inscricoes i
         WHERE i.id = ?
         LIMIT 1'
    );
    $stmtI->execute([$inscricao_id]);
    $inscricao = $stmtI->fetch(PDO::FETCH_ASSOC);

    if (!$inscricao) {
        return ['success' => false, 'message' => 'Inscricao nao encontrada'];
    }

    $stmtPm = $pdo->prepare(
        'SELECT id, payment_id, valor_pago, taxa_ml, dados_pagamento, data_atualizacao
         FROM pagamentos_ml
         WHERE inscricao_id = ?
           AND (? IS NULL OR payment_id = ?)
         ORDER BY data_atualizacao DESC
         LIMIT 1'
    );
    $stmtPm->execute([$inscricao_id, $payment_id, $payment_id]);
    $pm = $stmtPm->fetch(PDO::FETCH_ASSOC);

    $evento_id = (int) $inscricao['evento_id'];
    $origem_id_pagamento = $pm ? (int) $pm['id'] : (int) $inscricao_id;

    if (fin_ledger_existe($pdo, 'pagamento', $origem_id_pagamento, 'credito', 'Receita de inscricao (pagamento confirmado)')) {
        return [
            'success' => true,
            'data' => [
                'idempotente' => true,
                'origem_id_pagamento' => $origem_id_pagamento,
            ],
        ];
    }

    $regra = fin_resolver_regra_receita($pdo, $evento_id);

    $valor_bruto = fin_decimal($inscricao['valor_total']);
    if ($pm && fin_decimal($pm['valor_pago']) > 0) {
        $valor_bruto = fin_decimal($pm['valor_pago']);
    }

    if (is_array($payment_data) && isset($payment_data['transaction_amount'])) {
        $valor_bruto = fin_decimal($payment_data['transaction_amount']);
    }

    $taxa_gateway = 0.0;
    if ($pm && fin_decimal($pm['taxa_ml']) > 0) {
        $taxa_gateway = fin_decimal($pm['taxa_ml']);
    }

    if (is_array($payment_data) && !empty($payment_data['fee_details']) && is_array($payment_data['fee_details'])) {
        $taxa_gateway = 0.0;
        foreach ($payment_data['fee_details'] as $fee) {
            $taxa_gateway += fin_decimal($fee['amount'] ?? 0);
        }
        $taxa_gateway = round($taxa_gateway, 2);
    }

    $ocorrido_em = fin_to_datetime($payment_data['date_approved'] ?? null)
        ?: fin_to_datetime($payment_data['date_last_updated'] ?? null)
        ?: date('Y-m-d H:i:s');

    $cfg = fin_obter_config_evento($pdo, $evento_id);
    $disponivel_em = fin_to_datetime($payment_data['money_release_date'] ?? null);
    if ($disponivel_em === null) {
        $disponivel_em = date('Y-m-d H:i:s', strtotime($ocorrido_em . ' +' . (int) $cfg['prazo_liberacao_dias'] . ' day'));
    }

    $status_credito = (strtotime($disponivel_em) > time()) ? 'pendente' : 'disponivel';

    $startedTx = false;
    if (!$pdo->inTransaction()) {
        $pdo->beginTransaction();
        $startedTx = true;
    }
    fin_lock_evento($pdo, $evento_id);

    try {
        $credit_id = fin_ledger_insert($pdo, [
            'evento_id' => $evento_id,
            'origem_tipo' => 'pagamento',
            'origem_id' => $origem_id_pagamento,
            'descricao' => 'Receita de inscricao (pagamento confirmado)',
            'direcao' => 'credito',
            'valor' => $valor_bruto,
            'status' => $status_credito,
            'ocorrido_em' => $ocorrido_em,
            'disponivel_em' => $disponivel_em,
            'metadata' => [
                'inscricao_id' => $inscricao_id,
                'payment_id' => $payment_id ?: ($pm['payment_id'] ?? null),
                'regra_receita' => $regra['regra_receita'],
                'regra_fonte' => $regra['fonte'],
                'valor_bruto' => $valor_bruto,
                'taxa_gateway' => $taxa_gateway,
            ],
            'created_by' => $user_id,
        ]);

        $tax_id = null;
        if ($taxa_gateway > 0) {
            $tax_id = fin_ledger_insert($pdo, [
                'evento_id' => $evento_id,
                'origem_tipo' => 'taxa',
                'origem_id' => $origem_id_pagamento,
                'descricao' => 'Taxa de gateway vinculada ao pagamento',
                'direcao' => 'debito',
                'valor' => $taxa_gateway,
                'status' => $status_credito,
                'ocorrido_em' => $ocorrido_em,
                'disponivel_em' => $disponivel_em,
                'metadata' => [
                    'inscricao_id' => $inscricao_id,
                    'payment_id' => $payment_id ?: ($pm['payment_id'] ?? null),
                ],
                'created_by' => $user_id,
            ]);
        }

        fin_unlock_evento($pdo, $evento_id);
        if ($startedTx) {
            $pdo->commit();
        }

        return [
            'success' => true,
            'data' => [
                'ledger_credito_id' => $credit_id,
                'ledger_taxa_id' => $tax_id,
                'regra_receita' => $regra['regra_receita'],
                'valor_bruto' => $valor_bruto,
                'taxa_gateway' => $taxa_gateway,
            ],
        ];
    } catch (Exception $e) {
        if ($startedTx && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fin_unlock_evento($pdo, $evento_id);
        throw $e;
    }
}

function fin_estorno_registrar(
    PDO $pdo,
    int $evento_id,
    ?int $inscricao_id,
    ?int $pagamento_ml_id,
    $valor_in,
    ?string $motivo,
    string $status = 'solicitado',
    ?string $gateway_refund_id = null,
    $raw_payload = null,
    int $user_id = 0
): array {
    $valor = fin_decimal($valor_in);
    if ($valor <= 0) {
        return ['success' => false, 'message' => 'Valor do estorno invalido'];
    }

    $statusPermitidos = ['solicitado', 'em_processamento', 'concluido', 'negado', 'falhou'];
    if (!in_array($status, $statusPermitidos, true)) {
        return ['success' => false, 'message' => 'Status de estorno invalido'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO financeiro_estornos
         (evento_id, inscricao_id, pagamento_ml_id, valor, motivo, status, solicitado_em, concluido_em, gateway_refund_id, raw_payload)
         VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)'
    );

    $concluidoEm = ($status === 'concluido') ? date('Y-m-d H:i:s') : null;
    $stmt->execute([
        $evento_id,
        $inscricao_id,
        $pagamento_ml_id,
        $valor,
        $motivo,
        $status,
        $concluidoEm,
        $gateway_refund_id,
        fin_normalizar_metadata($raw_payload),
    ]);

    $estorno_id = (int) $pdo->lastInsertId();

    if (in_array($status, ['em_processamento', 'concluido'], true)) {
        $ledgerStatus = ($status === 'concluido') ? 'liquidado' : 'bloqueado';
        fin_ledger_insert($pdo, [
            'evento_id' => $evento_id,
            'origem_tipo' => 'estorno',
            'origem_id' => $estorno_id,
            'descricao' => 'Estorno registrado (' . $status . ')',
            'direcao' => 'debito',
            'valor' => $valor,
            'status' => $ledgerStatus,
            'ocorrido_em' => date('Y-m-d H:i:s'),
            'metadata' => [
                'inscricao_id' => $inscricao_id,
                'pagamento_ml_id' => $pagamento_ml_id,
                'status_estorno' => $status,
                'gateway_refund_id' => $gateway_refund_id,
            ],
            'created_by' => $user_id,
        ]);
    }

    return ['success' => true, 'data' => ['estorno_id' => $estorno_id]];
}

function fin_chargeback_registrar(
    PDO $pdo,
    int $evento_id,
    ?int $inscricao_id,
    ?int $pagamento_ml_id,
    $valor_in,
    string $status = 'aberto',
    ?string $motivo = null,
    ?string $prazo_resposta = null,
    ?string $evidencias_url = null,
    $raw_payload = null,
    int $user_id = 0
): array {
    $valor = fin_decimal($valor_in);
    if ($valor <= 0) {
        return ['success' => false, 'message' => 'Valor do chargeback invalido'];
    }

    $statusPermitidos = ['aberto', 'em_disputa', 'ganho', 'perdido', 'cancelado'];
    if (!in_array($status, $statusPermitidos, true)) {
        return ['success' => false, 'message' => 'Status de chargeback invalido'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO financeiro_chargebacks
         (evento_id, inscricao_id, pagamento_ml_id, valor, status, aberto_em, encerrado_em, motivo, prazo_resposta, evidencias_url, raw_payload)
         VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)'
    );

    $encerradoEm = in_array($status, ['ganho', 'perdido', 'cancelado'], true) ? date('Y-m-d H:i:s') : null;
    $stmt->execute([
        $evento_id,
        $inscricao_id,
        $pagamento_ml_id,
        $valor,
        $status,
        $encerradoEm,
        $motivo,
        $prazo_resposta,
        $evidencias_url,
        fin_normalizar_metadata($raw_payload),
    ]);

    $chargeback_id = (int) $pdo->lastInsertId();

    if (in_array($status, ['aberto', 'em_disputa', 'perdido'], true)) {
        $ledgerStatus = ($status === 'perdido') ? 'liquidado' : 'bloqueado';
        fin_ledger_insert($pdo, [
            'evento_id' => $evento_id,
            'origem_tipo' => 'chargeback',
            'origem_id' => $chargeback_id,
            'descricao' => 'Chargeback registrado (' . $status . ')',
            'direcao' => 'debito',
            'valor' => $valor,
            'status' => $ledgerStatus,
            'ocorrido_em' => date('Y-m-d H:i:s'),
            'metadata' => [
                'inscricao_id' => $inscricao_id,
                'pagamento_ml_id' => $pagamento_ml_id,
                'status_chargeback' => $status,
            ],
            'created_by' => $user_id,
        ]);
    }

    if ($status === 'ganho') {
        fin_ledger_insert($pdo, [
            'evento_id' => $evento_id,
            'origem_tipo' => 'chargeback',
            'origem_id' => $chargeback_id,
            'descricao' => 'Chargeback ganho (reversao do impacto)',
            'direcao' => 'credito',
            'valor' => $valor,
            'status' => 'liquidado',
            'ocorrido_em' => date('Y-m-d H:i:s'),
            'metadata' => [
                'inscricao_id' => $inscricao_id,
                'pagamento_ml_id' => $pagamento_ml_id,
                'status_chargeback' => $status,
            ],
            'created_by' => $user_id,
        ]);
    }

    return ['success' => true, 'data' => ['chargeback_id' => $chargeback_id]];
}

function fin_fechamento_gerar(PDO $pdo, int $evento_id, int $fechado_por = 0): array
{
    try {
        $pdo->beginTransaction();
        fin_lock_evento($pdo, $evento_id);

        $stmt = $pdo->prepare(
            'SELECT id, origem_tipo, direcao, valor, status, ocorrido_em
             FROM financeiro_ledger
             WHERE evento_id = ?
             ORDER BY id ASC'
        );
        $stmt->execute([$evento_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $receita_bruta = 0.0;
        $taxas = 0.0;
        $estornos = 0.0;
        $chargebacks = 0.0;
        $repasses = 0.0;
        $ids_considerados = [];

        foreach ($rows as $r) {
            $ids_considerados[] = (int) $r['id'];

            $origem = (string) $r['origem_tipo'];
            $direcao = (string) $r['direcao'];
            $valor = fin_decimal($r['valor']);
            $status = (string) $r['status'];

            if (!fin_status_saldo_considerado($status) && $status !== 'bloqueado') {
                continue;
            }

            if ($origem === 'pagamento' && $direcao === 'credito') {
                $receita_bruta += $valor;
            }

            if ($origem === 'taxa' && $direcao === 'debito') {
                $taxas += $valor;
            }

            if ($origem === 'estorno' && $direcao === 'debito') {
                $estornos += $valor;
            }

            if ($origem === 'chargeback' && $direcao === 'debito') {
                $chargebacks += $valor;
            }

            if ($origem === 'repasse' && $direcao === 'debito' && fin_status_saldo_considerado($status)) {
                $repasses += $valor;
            }
        }

        $saldo_final = fin_saldo_disponivel($pdo, $evento_id);
        $snapshot = [
            'evento_id' => $evento_id,
            'fechado_em' => date('Y-m-d H:i:s'),
            'receita_bruta' => round($receita_bruta, 2),
            'taxas' => round($taxas, 2),
            'estornos' => round($estornos, 2),
            'chargebacks' => round($chargebacks, 2),
            'repasses' => round($repasses, 2),
            'saldo_final' => round($saldo_final, 2),
            'ledger_ids_considerados' => $ids_considerados,
            'ledger_total_itens' => count($ids_considerados),
            'snapshot_read_only' => true,
        ];

        $stmtF = $pdo->prepare(
            'INSERT INTO financeiro_fechamentos
            (evento_id, fechado_por, fechado_em, receita_bruta, taxas, estornos, chargebacks, repasses, saldo_final, snapshot)
            VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmtF->execute([
            $evento_id,
            $fechado_por > 0 ? $fechado_por : null,
            $snapshot['receita_bruta'],
            $snapshot['taxas'],
            $snapshot['estornos'],
            $snapshot['chargebacks'],
            $snapshot['repasses'],
            $snapshot['saldo_final'],
            json_encode($snapshot, JSON_UNESCAPED_UNICODE),
        ]);

        $fechamento_id = (int) $pdo->lastInsertId();
        fin_unlock_evento($pdo, $evento_id);
        $pdo->commit();

        return [
            'success' => true,
            'data' => [
                'fechamento_id' => $fechamento_id,
                'snapshot' => $snapshot,
            ],
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fin_unlock_evento($pdo, $evento_id);
        error_log('Erro fin_fechamento_gerar: ' . $e->getMessage());

        return ['success' => false, 'message' => $e->getMessage()];
    }
}
