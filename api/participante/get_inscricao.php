<?php
// Verificar se a sessão já está ativa antes de iniciar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Usar caminhos relativos para compatibilidade com hospedagem
$base_path = dirname(__DIR__);
require_once $base_path . '/db.php';
require_once $base_path . '/security_middleware.php';
require_once $base_path . '/helpers/cancelar_inscricoes_expiradas_helper.php';

// Garante que apenas usuários logados possam acessar
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

header('Content-Type: application/json');

// Validar parâmetros
$inscricao_id = $_GET['inscricao_id'] ?? null;
$usuario_id = $_SESSION['user_id'];

// Log para debug
error_log("[GET_INSCRICAO] Usuário logado: " . $usuario_id);
error_log("[GET_INSCRICAO] Inscrição solicitada: " . $inscricao_id);

if (!$inscricao_id || !is_numeric($inscricao_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da inscrição inválido.']);
    exit();
}

try {
    // ✅ FALLBACK 1: Verificar e cancelar inscrições expiradas antes de buscar
    // Executa silenciosamente para não impactar performance
    cancelarInscricoesExpiradas($pdo, true);
    
    // Buscar dados completos da inscrição
    $sql = "
    SELECT 
        i.id AS inscricao_id,
        i.numero_inscricao,
        i.status,
        i.status_pagamento,
        i.data_inscricao,
        i.valor_total,
        i.usuario_id,

        -- Dados do evento
        e.id AS evento_id,
        e.nome AS evento_nome,
        e.data_inicio AS evento_data,
        e.data_fim AS evento_fim,
        e.data_fim_inscricoes AS evento_data_fim_inscricoes,
        e.hora_fim_inscricoes AS evento_hora_fim_inscricoes,
        e.local AS evento_local,
        e.descricao AS evento_descricao,
        e.imagem AS evento_imagem,

        -- Dados da modalidade
        m.id AS modalidade_id,
        m.nome AS modalidade_nome,
        m.descricao AS modalidade_descricao,

        -- Dados do kit
        k.id AS kit_id,
        k.nome AS kit_nome,
        k.descricao AS kit_descricao,
        k.valor AS valor_kit,

        -- Dados do lote (se aplicável)
        li.id AS lote_id,
        li.numero_lote AS lote_numero,
        li.preco AS preco_lote,
        li.data_inicio AS lote_inicio,
        li.data_fim AS lote_fim,

        -- Dados do usuário
        u.nome_completo AS usuario_nome,
        u.email AS usuario_email,
        u.telefone AS usuario_telefone

    FROM inscricoes i
    JOIN eventos e ON i.evento_id = e.id
    JOIN modalidades m ON i.modalidade_evento_id = m.id
    LEFT JOIN kits_eventos k ON i.kit_id = k.id
    LEFT JOIN lotes_inscricao li ON li.id = i.lote_inscricao_id
    JOIN usuarios u ON i.usuario_id = u.id

    WHERE i.id = ? AND i.usuario_id = ?
    LIMIT 1
";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$inscricao_id, $usuario_id]);
    $inscricao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscricao) {
        error_log("[GET_INSCRICAO] ❌ Inscrição não encontrada ou não pertence ao usuário. Inscrição ID: $inscricao_id, Usuário ID: $usuario_id");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Inscrição não encontrada ou não pertence ao usuário.']);
        exit();
    }

    // Log para debug
    error_log("[GET_INSCRICAO] ✅ Inscrição encontrada. ID: " . $inscricao['inscricao_id'] . ", Usuário da inscrição: " . $inscricao['usuario_id'] . ", Usuário logado: $usuario_id");
    
    // Verificar se o usuário da inscrição corresponde ao usuário logado
    if ($inscricao['usuario_id'] != $usuario_id) {
        error_log("[GET_INSCRICAO] ❌ ERRO DE SEGURANÇA: Usuário tentando acessar inscrição de outro usuário!");
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acesso negado. Esta inscrição não pertence ao usuário logado.']);
        exit();
    }

    // Verificar se a inscrição pode ser paga
    if ($inscricao['status'] !== 'pendente') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Esta inscrição não pode ser paga. Status atual: ' . $inscricao['status']
        ]);
        exit();
    }

    // Verificar se o evento ainda aceita pagamentos
    // Usar data_fim_inscricoes e hora_fim_inscricoes se disponíveis, senão usar data_fim
    if ($inscricao['evento_data_fim_inscricoes']) {
        $data_limite = $inscricao['evento_data_fim_inscricoes'];
        if ($inscricao['evento_hora_fim_inscricoes']) {
            $data_limite .= ' ' . $inscricao['evento_hora_fim_inscricoes'];
        } else {
            $data_limite .= ' 23:59:59'; // Se não tem hora, usar fim do dia
        }
        $data_atual = date('Y-m-d H:i:s');
        if ($data_atual > $data_limite) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'O prazo para pagamento deste evento já expirou.'
            ]);
            exit();
        }
    } elseif ($inscricao['evento_fim']) {
        // Fallback: usar data_fim se não houver data_fim_inscricoes
        $data_limite = $inscricao['evento_fim'] . ' 23:59:59';
        $data_atual = date('Y-m-d H:i:s');
        if ($data_atual > $data_limite) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'O prazo para pagamento deste evento já expirou.'
            ]);
            exit();
        }
    }

    // Buscar produtos extras da inscrição
    $sql_extras = "
        SELECT 
            pe.id,
            pe.nome,
            pe.descricao,
            pe.valor,
            ipe.quantidade,
            (pe.valor * ipe.quantidade) as subtotal
        FROM inscricoes_produtos_extras ipe
        JOIN produtos_extras pe ON ipe.produto_extra_evento_id = pe.id
        WHERE ipe.inscricao_id = ?
    ";

    $stmt_extras = $pdo->prepare($sql_extras);
    $stmt_extras->execute([$inscricao_id]);
    $produtos_extras = $stmt_extras->fetchAll(PDO::FETCH_ASSOC);

    // Garantir que valores numéricos estejam no formato correto
    foreach ($produtos_extras as &$extra) {
        $extra['valor'] = isset($extra['valor']) && $extra['valor'] !== null ? (float)$extra['valor'] : 0.0;
        $extra['subtotal'] = isset($extra['subtotal']) && $extra['subtotal'] !== null ? (float)$extra['subtotal'] : 0.0;
        $extra['quantidade'] = isset($extra['quantidade']) && $extra['quantidade'] !== null ? (int)$extra['quantidade'] : 0;
        
        // Garantir que subtotal seja calculado se não existir
        if ($extra['subtotal'] == 0 && $extra['valor'] > 0 && $extra['quantidade'] > 0) {
            $extra['subtotal'] = (float)($extra['valor'] * $extra['quantidade']);
        }
    }
    unset($extra);

    // Calcular breakdown completo dos valores
    $valor_lote = $inscricao['preco_lote'] ? (float)$inscricao['preco_lote'] : 0.0;
    $valor_kit_original = $inscricao['valor_kit'] ? (float)$inscricao['valor_kit'] : 0.0;
    $valor_total_inscricao = (float)($inscricao['valor_total'] ?? 0);
    
    // Calcular total de produtos extras
    $total_extras_original = 0;
    foreach ($produtos_extras as $extra) {
        $total_extras_original += $extra['subtotal'];
    }
    
    // Determinar valor base da modalidade e calcular valor final
    if ($valor_lote > 0) {
        // Se há lote, usar o preço do lote como base
        $valor_base_modalidade = $valor_lote;
        // Com lote: valor final = lote + kit + extras
        $valor_final = $valor_base_modalidade + $valor_kit_original + $total_extras_original;
        $valor_kit = $valor_kit_original;
        $total_extras = $total_extras_original;
    } else {
        // Se não há lote, calcular o valor base a partir do valor_total
        // Tentar calcular: valor_base = valor_total - kit - extras
        $valor_base_calculado = $valor_total_inscricao - $valor_kit_original - $total_extras_original;
        
        if ($valor_base_calculado > 0) {
            // Se o cálculo é positivo, significa que valor_total inclui tudo separadamente
            $valor_base_modalidade = $valor_base_calculado;
            $valor_final = $valor_base_modalidade + $valor_kit_original + $total_extras_original;
            $valor_kit = $valor_kit_original;
            $total_extras = $total_extras_original;
        } else {
            // Se o cálculo é negativo ou zero, significa que valor_total já inclui tudo
            // Nesse caso, considerar que o valor_total é o valor base (já inclui kit e extras)
            $valor_base_modalidade = $valor_total_inscricao;
            // Manter valores originais para exibição, mas não somar ao total
            $valor_kit = $valor_kit_original;
            $total_extras = $total_extras_original;
            // Valor final é o valor_total (já inclui tudo)
            $valor_final = $valor_total_inscricao;
        }
    }
    
    // Garantir que valores sejam números e não negativos
    $valor_final = max(0, (float)$valor_final);
    $valor_base_modalidade = max(0, (float)$valor_base_modalidade);
    $valor_kit = max(0, (float)$valor_kit);
    $total_extras = max(0, (float)$total_extras);
    
    // Log para debug
    error_log("[GET_INSCRICAO] Breakdown de valores calculado:");
    error_log("  - Valor Lote: " . $valor_lote);
    error_log("  - Valor Kit Original: " . $valor_kit_original);
    error_log("  - Total Extras Original: " . $total_extras_original);
    error_log("  - Valor Total Inscrição: " . $valor_total_inscricao);
    error_log("  - Valor Base Modalidade: " . $valor_base_modalidade);
    error_log("  - Valor Kit (final): " . $valor_kit);
    error_log("  - Total Extras (final): " . $total_extras);
    error_log("  - Valor Final: " . $valor_final);

    // Preparar resposta
    $response = [
        'success' => true,
        'inscricao' => [
            'id' => $inscricao['inscricao_id'],
            'numero' => $inscricao['numero_inscricao'],
            'status' => $inscricao['status'],
            'status_pagamento' => $inscricao['status_pagamento'],
            'data_inscricao' => $inscricao['data_inscricao'],
            'valor_total' => $valor_final,

            'evento' => [
                'id' => $inscricao['evento_id'],
                'nome' => $inscricao['evento_nome'],
                'data' => $inscricao['evento_data'],
                'local' => $inscricao['evento_local'],
                'descricao' => $inscricao['evento_descricao'],
                'imagem' => $inscricao['evento_imagem']
            ],

            'modalidade' => [
                'id' => $inscricao['modalidade_id'],
                'nome' => $inscricao['modalidade_nome'],
                'descricao' => $inscricao['modalidade_descricao']
            ],

            'kit' => $inscricao['kit_id'] ? [
                'id' => $inscricao['kit_id'],
                'nome' => $inscricao['kit_nome'],
                'descricao' => $inscricao['kit_descricao'],
                'valor' => $inscricao['valor_kit']
            ] : null,

            'lote' => $inscricao['lote_id'] ? [
                'id' => $inscricao['lote_id'],
                'numero' => $inscricao['lote_numero'],
                'preco' => $inscricao['preco_lote'],
                'inicio' => $inscricao['lote_inicio'],
                'fim' => $inscricao['lote_fim']
            ] : null,

            'produtos_extras' => $produtos_extras,
            'total_extras' => $total_extras,
            
            // Breakdown detalhado dos valores para exibição
            'breakdown_valores' => [
                'valor_base' => $valor_base_modalidade, // Valor do lote ou modalidade base
                'valor_kit' => $valor_kit,
                'total_extras' => $total_extras,
                'valor_total' => $valor_final,
                'tem_lote' => ($valor_lote > 0) // Flag para indicar se tem lote
            ],

            'usuario' => [
                'nome' => $inscricao['usuario_nome'],
                'email' => $inscricao['usuario_email'],
                'telefone' => $inscricao['usuario_telefone']
            ]
        ]
    ];

    echo json_encode($response);
} catch (Exception $e) {
    error_log("Erro ao buscar inscrição: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
}
