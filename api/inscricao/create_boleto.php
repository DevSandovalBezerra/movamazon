<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../db.php';
require_once '../config/email_config.php';
require_once '../helpers/inscricao_logger.php';
require_once '../helpers/cpf_validator.php';
require_once '../helpers/cancelar_inscricoes_expiradas_helper.php';
require_once __DIR__ . '/../mercadolivre/MercadoPagoClient.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Dados inválidos');
    }

    $required_fields = ['inscricao_id', 'valor_total'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Campo obrigatório: $field");
        }
    }

    $inscricaoId = (int) $data['inscricao_id'];
    $valorTotal = (float) $data['valor_total'];
    
    if ($inscricaoId <= 0) {
        throw new Exception('ID da inscrição inválido');
    }
    
    if ($valorTotal <= 0) {
        throw new Exception('Valor total inválido: ' . $valorTotal);
    }
    
    error_log("BOLETO INSCRICAO - Validando: inscricao_id=$inscricaoId, valor_total=$valorTotal");
    
    // ✅ FALLBACK 2: Verificar e cancelar inscrições expiradas antes de gerar boleto
    // Executa silenciosamente para não impactar performance
    cancelarInscricoesExpiradas($pdo, true);
    
    // Log início da geração de boleto
    logInscricaoPagamento('INFO', 'INICIO_GERACAO_BOLETO', [
        'inscricao_id' => $inscricaoId,
        'valor_total' => $valorTotal
    ]);

    $stmt = $pdo->prepare("
        SELECT i.*, e.nome as evento_nome, e.descricao as evento_descricao,
               m.nome as modalidade_nome,
               u.email as usuario_email, u.documento as usuario_cpf, u.nome_completo as usuario_nome,
               u.cep, u.endereco, u.numero, u.bairro, u.cidade, u.uf
        FROM inscricoes i
        JOIN eventos e ON i.evento_id = e.id
        LEFT JOIN modalidades m ON i.modalidade_evento_id = m.id
        JOIN usuarios u ON i.usuario_id = u.id
        WHERE i.id = ?
    ");
    $stmt->execute([$inscricaoId]);
    $inscricao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscricao) {
        throw new Exception('Inscrição não encontrada');
    }
    
    // Verificar se a inscrição foi cancelada após verificação automática
    if ($inscricao['status'] === 'cancelada' || $inscricao['status_pagamento'] === 'cancelado') {
        throw new Exception('Esta inscrição foi cancelada automaticamente por expiração. Por favor, faça uma nova inscrição.');
    }
    
    // Extrair e limpar CPF (remover pontos, traços e espaços)
    $cpf = $inscricao['usuario_cpf'] ?? '';
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Validar email e CPF
    $email = $inscricao['usuario_email'] ?? '';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("BOLETO INSCRICAO - Email inválido ou vazio: $email");
        logInscricaoPagamento('WARNING', 'VALIDACAO_DADOS_FALHOU', [
            'inscricao_id' => $inscricaoId,
            'usuario_id' => $inscricao['usuario_id'] ?? null,
            'campo' => 'email',
            'valor' => $email
        ]);
        throw new Exception('Email do usuário não encontrado ou inválido. Verifique seus dados cadastrais.');
    }
    
    // Validação completa do CPF (comprimento + dígitos verificadores)
    if (empty($cpf) || strlen($cpf) !== 11) {
        error_log("BOLETO INSCRICAO - CPF com comprimento inválido: " . strlen($cpf) . " dígitos");
        logInscricaoPagamento('WARNING', 'VALIDACAO_DADOS_FALHOU', [
            'inscricao_id' => $inscricaoId,
            'usuario_id' => $inscricao['usuario_id'] ?? null,
            'campo' => 'cpf',
            'erro' => 'comprimento_invalido',
            'valor' => empty($cpf) ? 'vazio' : substr($cpf, 0, 3) . '***'
        ]);
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'CPF do usuário não encontrado ou inválido. Verifique seus dados cadastrais.',
            'message' => 'CPF do usuário não encontrado ou inválido. Verifique seus dados cadastrais.'
        ]);
        exit;
    }
    
    // Validar CPF com algoritmo brasileiro (dígitos verificadores)
    if (!validarCPF($cpf)) {
        error_log("BOLETO INSCRICAO - CPF com dígitos verificadores inválidos: " . substr($cpf, 0, 3) . '***');
        logInscricaoPagamento('WARNING', 'CPF_INVALIDO', [
            'inscricao_id' => $inscricaoId,
            'usuario_id' => $inscricao['usuario_id'] ?? null,
            'campo' => 'cpf',
            'erro' => 'digitos_verificadores_incorretos',
            'valor' => substr($cpf, 0, 3) . '***' . substr($cpf, -2)
        ]);
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'CPF inválido. O CPF informado não é válido segundo a Receita Federal. Verifique seus dados cadastrais e corrija o CPF.',
            'message' => 'CPF inválido. O CPF informado não é válido segundo a Receita Federal. Verifique seus dados cadastrais e corrija o CPF.',
            'detalhes' => 'O CPF possui dígitos verificadores incorretos.'
        ]);
        exit;
    }
    
    // Extrair nome completo e dividir em first_name e last_name
    $nome_completo = trim($inscricao['usuario_nome'] ?? '');
    if (empty($nome_completo)) {
        error_log("BOLETO INSCRICAO - Nome completo vazio para usuário");
        throw new Exception('Nome completo do usuário não encontrado. Verifique seus dados cadastrais.');
    }
    
    // Dividir nome em primeiro nome e sobrenome
    $nomes = explode(' ', $nome_completo);
    $first_name = $nomes[0] ?? '';
    $last_name = '';
    if (count($nomes) > 1) {
        // Pegar todos os nomes após o primeiro como sobrenome
        $last_name = implode(' ', array_slice($nomes, 1));
    } else {
        // Se só tem um nome, usar ele como sobrenome também
        $last_name = $first_name;
    }
    
    // Validar que temos pelo menos first_name e last_name
    if (empty($first_name) || empty($last_name)) {
        error_log("BOLETO INSCRICAO - Nome inválido: first_name='$first_name', last_name='$last_name'");
        logInscricaoPagamento('WARNING', 'VALIDACAO_DADOS_FALHOU', [
            'inscricao_id' => $inscricaoId,
            'usuario_id' => $inscricao['usuario_id'] ?? null,
            'campo' => 'nome_completo'
        ]);
        throw new Exception('Nome completo inválido. Verifique seus dados cadastrais.');
    }
    
    // Extrair e validar dados de endereço
    $cep = preg_replace('/[^0-9]/', '', $inscricao['cep'] ?? '');
    $endereco = trim($inscricao['endereco'] ?? '');
    $numero = trim($inscricao['numero'] ?? '');
    $bairro = trim($inscricao['bairro'] ?? '');
    $cidade = trim($inscricao['cidade'] ?? '');
    $uf = strtoupper(trim($inscricao['uf'] ?? ''));
    
    // Validar endereço completo
    $campos_faltantes = [];
    if (empty($cep) || strlen($cep) !== 8) {
        $campos_faltantes[] = 'cep';
    }
    if (empty($endereco)) {
        $campos_faltantes[] = 'endereco';
    }
    if (empty($numero)) {
        $campos_faltantes[] = 'numero';
    }
    if (empty($bairro)) {
        $campos_faltantes[] = 'bairro';
    }
    if (empty($cidade)) {
        $campos_faltantes[] = 'cidade';
    }
    if (empty($uf) || strlen($uf) !== 2) {
        $campos_faltantes[] = 'uf';
    }
    
    if (!empty($campos_faltantes)) {
        $campos_faltantes_str = implode(', ', $campos_faltantes);
        error_log("BOLETO INSCRICAO - Dados de endereço incompletos: " . $campos_faltantes_str);
        logInscricaoPagamento('WARNING', 'ENDERECO_INCOMPLETO', [
            'inscricao_id' => $inscricaoId,
            'usuario_id' => $inscricao['usuario_id'] ?? null,
            'campos_faltantes' => $campos_faltantes
        ]);
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Dados de endereço incompletos. Verifique seus dados cadastrais.',
            'message' => 'Dados de endereço incompletos. Verifique seus dados cadastrais.',
            'campos_faltantes' => $campos_faltantes
        ]);
        exit;
    }
    
    error_log("BOLETO INSCRICAO - Dados do pagador: email=$email, cpf=" . substr($cpf, 0, 3) . "***, nome=$first_name $last_name");
    error_log("BOLETO INSCRICAO - Endereço: $endereco, $numero, $bairro, $cidade-$uf, CEP: $cep");

    $config = require_once __DIR__ . '/../mercadolivre/config.php';
    
    if (!$config['has_valid_tokens']) {
        throw new Exception('Tokens do Mercado Pago não configurados. Verifique o arquivo .env');
    }
    
    $accesstoken = $config['accesstoken'];

    // Usar external_reference da inscrição para consistência com webhook
    $external_ref = $inscricao['external_reference'] ?: 'MOVAMAZON_' . $inscricaoId;

    // Montar items para exigência MP (melhor taxa de aprovação)
    $evento_nome_b = $inscricao['evento_nome'] ?? 'Evento';
    $modalidade_nome_b = $inscricao['modalidade_nome'] ?? 'Inscrição';
    $boleto_items = [
        [
            'id' => 'MOVAMAZON_' . $inscricaoId,
            'title' => $modalidade_nome_b,
            'description' => 'Inscrição no evento: ' . $evento_nome_b,
            'category_id' => 'EBL-Evento Desportivo',
            'quantity' => 1,
            'unit_price' => (float) $valorTotal,
            'currency_id' => 'BRL'
        ]
    ];
    $produtos_extras_b = [];
    if (!empty($inscricao['produtos_extras_ids'])) {
        $decoded_b = json_decode($inscricao['produtos_extras_ids'], true);
        if (is_array($decoded_b)) {
            $produtos_extras_b = $decoded_b;
        }
    }
    $idx_b = 0;
    foreach ($produtos_extras_b as $p) {
        if (isset($p['nome']) && isset($p['valor']) && (float)$p['valor'] > 0) {
            $boleto_items[] = [
                'id' => 'extra_' . $inscricaoId . '_' . $idx_b,
                'title' => $p['nome'],
                'description' => $p['descricao'] ?? 'Produto adicional',
                'category_id' => 'EBL-Evento Desportivo',
                'quantity' => 1,
                'unit_price' => (float)$p['valor'],
                'currency_id' => 'BRL'
            ];
            $idx_b++;
        }
    }
    
    // Único método de boleto habilitado na conta (confirmado via diagnóstico em produção)
    $payment_method_id = 'bolbradesco';
    
    // NOTA: Campo 'items' foi removido pois a API /v1/payments para payments diretos (PIX/Boleto) NÃO aceita 'items'
    // O campo 'items' é válido apenas para Preferences e payments vinculados a preferences
    // Código de montagem de $boleto_items mantido comentado para referência futura
    $statement_descriptor = $config['statement_descriptor'] ?? 'MOVAMAZON';
    $boleto_payload = [
        "description" => "Inscrição no evento: " . $evento_nome_b,
        "external_reference" => $external_ref,
        "notification_url" => $config['url_notification_api'],
        "statement_descriptor" => $statement_descriptor,
        "payer" => [
            "email" => $email,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "identification" => [
                "type" => "CPF",
                "number" => $cpf
            ],
            "address" => [
                "zip_code" => $cep,
                "street_name" => $endereco,
                "street_number" => $numero,
                "neighborhood" => $bairro,
                "city" => $cidade,
                "federal_unit" => $uf
            ]
        ],
        "payment_method_id" => $payment_method_id,
        "transaction_amount" => (float) $valorTotal
        // "items" => $boleto_items // REMOVIDO: API /v1/payments não aceita items para payments diretos
    ];
    
    if (empty($boleto_payload['transaction_amount']) || $boleto_payload['transaction_amount'] <= 0) {
        throw new Exception("Valor da transação inválido: " . $valorTotal);
    }
    
    error_log("BOLETO INSCRICAO - Payload enviado: " . json_encode($boleto_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    // Criar pagamento boleto via MercadoPagoClient (SDK ou cURL)
    $client = new MercadoPagoClient($config);
    $boletoData = $client->createPayment($boleto_payload);

    error_log("BOLETO INSCRICAO - API Response OK, payment_id: " . ($boletoData['id'] ?? 'N/A'));

    // Log completo da resposta para debug
    error_log("BOLETO INSCRICAO - Resposta completa do Mercado Pago: " . json_encode($boletoData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    if (!isset($boletoData['id'])) {
        error_log("BOLETO INSCRICAO - ERRO: ID do pagamento não encontrado na resposta");
        logInscricaoPagamento('ERROR', 'RESPOSTA_INVALIDA_MERCADO_PAGO', [
            'inscricao_id' => $inscricaoId,
            'erro' => 'ID do pagamento não encontrado na resposta',
            'resposta' => json_encode($boletoData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ]);
        throw new Exception('Resposta inválida do Mercado Pago: ID não encontrado');
    }

    // Verificar se o pagamento foi rejeitado (ex.: rejected_by_bank)
    $status = $boletoData['status'] ?? 'unknown';
    $statusDetail = $boletoData['status_detail'] ?? '';
    
    if ($status === 'rejected') {
        error_log("BOLETO INSCRICAO - Pagamento REJEITADO: status=$status, detail=$statusDetail. Boleto não utilizável, sugerido PIX.");
        logInscricaoPagamento('ERROR', 'BOLETO_REJEITADO', [
            'inscricao_id' => $inscricaoId,
            'payment_id' => $boletoData['id'],
            'status' => $status,
            'status_detail' => $statusDetail,
            'erro' => "Boleto rejeitado: $statusDetail"
        ]);
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'error' => 'No momento não foi possível gerar o boleto. Use PIX ou cartão para concluir o pagamento.',
            'message' => 'No momento não foi possível gerar o boleto. Use PIX ou cartão para concluir o pagamento.',
            'error_code' => 'BOLETO_REJECTED_BY_BANK',
            'use_pix' => true
        ]);
        exit;
    }

    if (!isset($boletoData['point_of_interaction'])) {
        error_log("BOLETO INSCRICAO - ERRO: point_of_interaction não encontrado na resposta");
        error_log("BOLETO INSCRICAO - Estrutura disponível: " . json_encode(array_keys($boletoData)));
        throw new Exception('Resposta inválida do Mercado Pago: point_of_interaction não encontrado');
    }

    $paymentId = $boletoData['id'];
    
    // Tentar múltiplas formas de extrair o barcode (prioridade conforme estrutura atual do MP)
    $barcode = '';
    // Prioridade 1: barcode.content (estrutura mais comum na resposta atual)
    if (isset($boletoData['barcode']['content'])) {
        $barcode = $boletoData['barcode']['content'];
        error_log("BOLETO INSCRICAO - Barcode encontrado em barcode.content: " . substr($barcode, 0, 50) . "...");
    }
    // Prioridade 2: transaction_details.barcode.content
    elseif (isset($boletoData['transaction_details']['barcode']['content'])) {
        $barcode = $boletoData['transaction_details']['barcode']['content'];
        error_log("BOLETO INSCRICAO - Barcode encontrado em transaction_details.barcode.content: " . substr($barcode, 0, 50) . "...");
    }
    // Prioridade 3: point_of_interaction.transaction_data.barcode (estrutura antiga)
    elseif (isset($boletoData['point_of_interaction']['transaction_data']['barcode'])) {
        $barcode = $boletoData['point_of_interaction']['transaction_data']['barcode'];
        error_log("BOLETO INSCRICAO - Barcode encontrado em point_of_interaction.transaction_data.barcode: " . substr($barcode, 0, 50) . "...");
    }
    // Prioridade 4: bank_transfer_id (fallback)
    elseif (isset($boletoData['point_of_interaction']['transaction_data']['bank_transfer_id'])) {
        $barcode = $boletoData['point_of_interaction']['transaction_data']['bank_transfer_id'];
        error_log("BOLETO INSCRICAO - Usando bank_transfer_id como barcode: " . substr($barcode, 0, 50) . "...");
    }
    // Prioridade 5: digitable_line como último recurso (linha digitável)
    elseif (isset($boletoData['transaction_details']['digitable_line'])) {
        $barcode = $boletoData['transaction_details']['digitable_line'];
        error_log("BOLETO INSCRICAO - Usando digitable_line como barcode (fallback): " . substr($barcode, 0, 50) . "...");
    }
    
    if (empty($barcode)) {
        error_log("BOLETO INSCRICAO - Barcode NÃO encontrado em nenhuma localização conhecida");
        error_log("BOLETO INSCRICAO - Estrutura disponível: " . json_encode(array_keys($boletoData), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        if (isset($boletoData['transaction_details'])) {
            error_log("BOLETO INSCRICAO - transaction_details keys: " . json_encode(array_keys($boletoData['transaction_details']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }
    
    // Extrair ticket URL (prioridade conforme estrutura atual do MP)
    // Prioridade 1: external_resource_url (estrutura atual)
    $ticketUrl = $boletoData['transaction_details']['external_resource_url'] ?? '';
    // Prioridade 2: point_of_interaction.transaction_data.ticket_url (estrutura antiga)
    if (empty($ticketUrl) && isset($boletoData['point_of_interaction']['transaction_data']['ticket_url'])) {
        $ticketUrl = $boletoData['point_of_interaction']['transaction_data']['ticket_url'];
    }
    
    $dateOfExpiration = $boletoData['date_of_expiration'] ?? null;
    $externalResourceUrl = $boletoData['transaction_details']['external_resource_url'] ?? $ticketUrl;
    
    // Converter data de expiração do formato ISO 8601 para DATETIME MySQL
    $dateOfExpirationFormatted = null;
    if ($dateOfExpiration) {
        try {
            $dateTime = new DateTime($dateOfExpiration);
            $dateOfExpirationFormatted = $dateTime->format('Y-m-d H:i:s');
            error_log("BOLETO INSCRICAO - Data de expiração convertida: $dateOfExpirationFormatted (original: $dateOfExpiration)");
        } catch (Exception $e) {
            error_log("BOLETO INSCRICAO - Erro ao converter data de expiração: " . $e->getMessage());
        }
    }
    
    error_log("BOLETO INSCRICAO - Dados extraídos: barcode=" . ($barcode ? substr($barcode, 0, 50) . "..." : "VAZIO") . ", ticket_url=" . ($ticketUrl ? "OK" : "VAZIO") . ", date_of_expiration=" . ($dateOfExpiration ?? "NULL") . ", formatted=" . ($dateOfExpirationFormatted ?? "NULL"));

    // Critério antifalha: só considerar sucesso se houver boleto utilizável (barcode ou URL)
    $status_aceito = ($status === 'pending' || $status === 'in_process');
    $tem_boleto_utilizavel = !empty($barcode) || !empty($ticketUrl) || !empty($externalResourceUrl);
    if ($status_aceito && !$tem_boleto_utilizavel) {
        error_log("BOLETO INSCRICAO - Resposta 201 sem barcode/ticket_url utilizável. Boleto não utilizável, sugerido PIX.");
        logInscricaoPagamento('ERROR', 'BOLETO_SEM_DADOS_UTILIZAVEIS', [
            'inscricao_id' => $inscricaoId,
            'payment_id' => $boletoData['id'],
            'status' => $status,
            'status_detail' => $statusDetail
        ]);
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'error' => 'No momento não foi possível gerar o boleto. Use PIX ou cartão para concluir o pagamento.',
            'message' => 'No momento não foi possível gerar o boleto. Use PIX ou cartão para concluir o pagamento.',
            'error_code' => 'BOLETO_REJECTED_BY_BANK',
            'use_pix' => true
        ]);
        exit;
    }

    $status_pagamento = 'pendente';
    if (isset($boletoData['status'])) {
        $status_map = [
            'approved' => 'pago',
            'pending' => 'pendente',
            'rejected' => 'rejeitado',
            'cancelled' => 'cancelado'
        ];
        $status_pagamento = $status_map[$boletoData['status']] ?? 'pendente';
    }
    
    $stmt = $pdo->prepare("
        UPDATE inscricoes 
        SET status_pagamento = ?,
            forma_pagamento = 'boleto',
            external_reference = ?,
            data_expiracao_pagamento = ?
        WHERE id = ?
    ");
    $stmt->execute([$status_pagamento, $external_ref, $dateOfExpirationFormatted, $inscricaoId]);
    
    error_log("BOLETO INSCRICAO - Inscrição atualizada: ID=$inscricaoId, Status=$status_pagamento, PaymentID=$paymentId");
    
    // Log success: boleto gerado com sucesso
    logInscricaoPagamento('SUCCESS', 'BOLETO_GERADO', [
        'inscricao_id' => $inscricaoId,
        'payment_id' => (string)$paymentId,
        'valor_total' => $valorTotal,
        'status_pagamento' => $status_pagamento,
        'forma_pagamento' => 'boleto',
        'date_of_expiration' => $dateOfExpiration,
        'barcode' => substr($barcode, 0, 10) . '***' // Parcial para log
    ]);
    
    // Preparar resposta
    $response_data = [
        'success' => true,
        'payment_id' => $paymentId,
        'barcode' => $barcode,
        'ticket_url' => $ticketUrl,
        'external_resource_url' => $externalResourceUrl,
        'date_of_expiration' => $dateOfExpiration,
        'transaction_amount' => $valorTotal,
        'external_reference' => $external_ref,
        'evento_nome' => $inscricao['evento_nome']
    ];
    
    error_log("BOLETO INSCRICAO - Resposta JSON que será enviada: " . json_encode($response_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    error_log("BOLETO INSCRICAO - Barcode na resposta: " . ($barcode ? "PRESENTE (" . strlen($barcode) . " caracteres)" : "VAZIO/NULO"));

    echo json_encode($response_data);
} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("BOLETO INSCRICAO ERROR: " . $error_message);
    error_log("BOLETO INSCRICAO ERROR - Stack trace: " . $e->getTraceAsString());
    
    // Log error final
    logInscricaoPagamento('ERROR', 'ERRO_GERACAO_BOLETO', [
        'inscricao_id' => $inscricaoId ?? null,
        'erro' => $error_message,
        'stack_trace' => $e->getTraceAsString()
    ]);
    
    // Se já foi definido um código HTTP (como 400), usar ele, senão usar 500
    $current_code = http_response_code();
    if (!$current_code || $current_code === 200) {
        // Verificar tipo de erro para definir código apropriado
        if (stripos($error_message, 'endereço') !== false || 
            stripos($error_message, 'cpf') !== false ||
            stripos($error_message, 'dados cadastrais') !== false) {
            http_response_code(400);
        } else {
            http_response_code(500);
        }
    }
    
    $use_pix = (stripos($error_message, 'rejeitado') !== false || stripos($error_message, 'rejected_by_bank') !== false);
    echo json_encode([
        'success' => false,
        'error' => $error_message,
        'message' => $use_pix ? 'No momento não foi possível gerar o boleto. Use PIX ou cartão para concluir o pagamento.' : $error_message,
        'error_code' => $use_pix ? 'BOLETO_REJECTED_BY_BANK' : 'BOLETO_GENERATION_FAILED',
        'use_pix' => $use_pix
    ]);
}
