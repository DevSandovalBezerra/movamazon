<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/MercadoLivrePayment.php';

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Verificar se usuário está logado
// Verificar se a sessão já está ativa antes de iniciar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

try {
    // Obter dados do POST
    $input = file_get_contents('php://input');
    $dados = json_decode($input, true);

    if (!$dados) {
        throw new Exception('Dados inválidos');
    }

    // Validar dados obrigatórios
    $required_fields = ['inscricao_id', 'modalidade_nome', 'valor_total', 'evento_nome'];
    foreach ($required_fields as $field) {
        if (!isset($dados[$field]) || empty($dados[$field])) {
            throw new Exception("Campo obrigatório não informado: $field");
        }
    }

    // Buscar dados completos da inscrição existente
    require_once __DIR__ . '/../db.php';

    $sql = "
        SELECT 
            i.id as inscricao_id,
            i.numero_inscricao,
            i.status,
            i.valor_total,
            i.usuario_id,
            
            e.nome as evento_nome,
            e.data_inicio as evento_data,
            e.local as evento_local,
            
            m.nome as modalidade_nome,
            
            k.nome as kit_nome,
            k.valor as valor_kit,
            
            li.preco as preco_lote,
            
            u.nome_completo as usuario_nome,
            u.email as usuario_email
            
        FROM inscricoes i
        JOIN eventos e ON i.evento_id = e.id
        JOIN modalidades m ON i.modalidade_evento_id = m.id
        LEFT JOIN kits_eventos k ON i.kit_id = k.id
        LEFT JOIN lotes_inscricao li ON i.lote_inscricao_id = li.id
        JOIN usuarios u ON i.usuario_id = u.id
        
        WHERE i.id = ? AND i.usuario_id = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dados['inscricao_id'], $_SESSION['user_id']]);
    $inscricao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscricao) {
        throw new Exception('Inscrição não encontrada ou não pertence ao usuário');
    }

    // Verificar se pode ser paga
    if ($inscricao['status'] !== 'pendente') {
        throw new Exception('Esta inscrição não pode ser paga. Status atual: ' . $inscricao['status']);
    }

    // Usar o valor_total enviado pelo frontend (já calculado corretamente no breakdown)
    // Se não foi enviado, calcular aqui
    $valor_final = isset($dados['valor_total']) ? (float)$dados['valor_total'] : 0;
    
    if ($valor_final <= 0) {
        // Fallback: calcular se não foi enviado
        if ($inscricao['preco_lote']) {
            $valor_final = $inscricao['preco_lote'];
            if ($inscricao['valor_kit']) {
                $valor_final += $inscricao['valor_kit'];
            }
        } else {
            $valor_final = $inscricao['valor_total'] ?? 0;
        }

        // Buscar produtos extras
        $sql_extras = "
            SELECT 
                pe.nome,
                pe.valor,
                ipe.quantidade,
                (pe.valor * ipe.quantidade) as subtotal
            FROM inscricoes_produtos_extras ipe
            JOIN produtos_extras pe ON ipe.produto_extra_evento_id = pe.id
            WHERE ipe.inscricao_id = ?
        ";

        $stmt_extras = $pdo->prepare($sql_extras);
        $stmt_extras->execute([$inscricao['inscricao_id']]);
        $produtos_extras = $stmt_extras->fetchAll(PDO::FETCH_ASSOC);

        foreach ($produtos_extras as &$extra) {
            $extra['valor'] = isset($extra['valor']) ? (float)$extra['valor'] : 0.0;
            $extra['subtotal'] = isset($extra['subtotal']) ? (float)$extra['subtotal'] : 0.0;
            $extra['quantidade'] = isset($extra['quantidade']) ? (int)$extra['quantidade'] : 0;
        }
        unset($extra);

        $total_extras = 0;
        foreach ($produtos_extras as $extra) {
            $total_extras += $extra['subtotal'];
        }
        
        // Só somar extras se não há lote (com lote, já está incluído no cálculo acima)
        if (!$inscricao['preco_lote']) {
            $valor_final += $total_extras;
        }
    } else {
        // Se valor foi enviado, buscar produtos extras para incluir nos dados
        $sql_extras = "
            SELECT 
                pe.nome,
                pe.valor,
                ipe.quantidade,
                (pe.valor * ipe.quantidade) as subtotal
            FROM inscricoes_produtos_extras ipe
            JOIN produtos_extras pe ON ipe.produto_extra_evento_id = pe.id
            WHERE ipe.inscricao_id = ?
        ";

        $stmt_extras = $pdo->prepare($sql_extras);
        $stmt_extras->execute([$inscricao['inscricao_id']]);
        $produtos_extras = $stmt_extras->fetchAll(PDO::FETCH_ASSOC);

        foreach ($produtos_extras as &$extra) {
            $extra['valor'] = isset($extra['valor']) ? (float)$extra['valor'] : 0.0;
            $extra['subtotal'] = isset($extra['subtotal']) ? (float)$extra['subtotal'] : 0.0;
            $extra['quantidade'] = isset($extra['quantidade']) ? (int)$extra['quantidade'] : 0;
        }
        unset($extra);
        
        // Calcular total de extras para os dados
        $total_extras = 0;
        foreach ($produtos_extras as $extra) {
            $total_extras += $extra['subtotal'];
        }
    }
    
    // Garantir que valor_final seja válido
    $valor_final = max(0, (float)$valor_final);
    
    error_log("[CREATE_PAYMENT] Valor final para pagamento: " . $valor_final);

    // Preparar dados da inscrição
    $dados_inscricao = [
        'id' => (string)$inscricao['inscricao_id'],
        'numero_inscricao' => $inscricao['numero_inscricao'],
        'modalidade_nome' => $inscricao['modalidade_nome'],
        'valor_total' => (float)$valor_final,
        'evento_nome' => $inscricao['evento_nome'],
        'evento_data' => $inscricao['evento_data'],
        'evento_local' => $inscricao['evento_local'],
        'nome_participante' => $inscricao['usuario_nome'],
        'email' => $inscricao['usuario_email'],
        'kit_nome' => $inscricao['kit_nome'],
        'valor_kit' => $inscricao['valor_kit'],
        'lote_preco' => $inscricao['preco_lote'],
        'produtos_extras' => $produtos_extras,
        'total_extras' => $total_extras
    ];

    // Criar pagamento
    $ml_payment = new MercadoLivrePayment();
    $resultado = $ml_payment->criarPagamento($dados_inscricao);

    if ($resultado['success']) {
        // Salvar dados na sessão para uso posterior
        $_SESSION['pagamento_ml'] = [
            'preference_id' => $resultado['preference_id'],
            'dados_inscricao' => $dados_inscricao,
            'criado_em' => time()
        ];

        error_log("✅ Pagamento ML criado - Preference ID: " . $resultado['preference_id']);

        try {
            $external_prefix = $_ENV['ML_EXTERNAL_REFERENCE'] ?? 'MOVAMAZON_';
            $external_reference = $external_prefix . $dados_inscricao['id'];
            $up = $pdo->prepare("UPDATE inscricoes SET external_reference = ? WHERE id = ?");
            $up->execute([$external_reference, (int)$dados_inscricao['id']]);
        } catch (Exception $e) {
            error_log('Falha ao atualizar external_reference: ' . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'preference_id' => $resultado['preference_id'],
            'init_point' => $resultado['init_point'],
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $resultado['error']
        ]);
    }
} catch (Exception $e) {
    error_log('Erro em create_payment.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
