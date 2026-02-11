<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db.php';

// Log de debug
////error_log("🔵 [DEBUG] ===== API MODALIDADES CHAMADA =====");
////error_log("🔵 [DEBUG] GET parameters: " . print_r($_GET, true));
////error_log("🔵 [DEBUG] Request URI: " . $_SERVER['REQUEST_URI']);

try {
    $evento_id = $_GET['evento_id'] ?? null;
    ////error_log("🔵 [DEBUG] Evento ID recebido: " . $evento_id);
    
    if (!$evento_id) {
        ////error_log("❌ [DEBUG] Evento ID não fornecido");
        throw new Exception('Evento ID é obrigatório');
    }
    
    // Buscar modalidades ativas do evento com categoria
    $sql = "
        SELECT 
            m.id as modalidade_id,
            m.nome as modalidade_nome,
            m.distancia,
            m.categoria_id,
            c.nome as categoria_nome,
            c.tipo_publico
        FROM modalidades m
        INNER JOIN categorias c ON m.categoria_id = c.id
        WHERE m.evento_id = ? AND m.ativo = 1 AND c.ativo = 1
        ORDER BY m.categoria_id, m.nome
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$evento_id]);
    $modalidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    //error_log("🔵 [DEBUG] Modalidades encontradas: " . count($modalidades));
    //error_log("🔵 [DEBUG] Modalidades: " . print_r($modalidades, true));
    
    $opcoes = [];
    
    foreach ($modalidades as $modalidade) {
        //error_log("🔵 [DEBUG] Processando modalidade: " . $modalidade['modalidade_nome']);
        
        // Buscar kits da modalidade
        $sql_kits = "
            SELECT 
                ke.id as kit_id,
                ke.nome as kit_nome,
                ke.foto_kit,
                ke.valor as preco_base
            FROM kits_eventos ke
            WHERE ke.modalidade_evento_id = ? AND ke.ativo = 1 AND ke.disponivel_venda = 1
            ORDER BY ke.nome
        ";
        
        $stmt_kits = $pdo->prepare($sql_kits);
        $stmt_kits->execute([$modalidade['modalidade_id']]);
        $kits = $stmt_kits->fetchAll(PDO::FETCH_ASSOC);
        
        //error_log("🔵 [DEBUG] Kits encontrados para modalidade " . $modalidade['modalidade_nome'] . ": " . count($kits));
        
        // Se não há kits, criar opção apenas da modalidade
        if (empty($kits)) {
            //error_log("🔵 [DEBUG] Modalidade sem kits, buscando preço do lote...");
            $preco_lote = buscarPrecoLote($pdo, $evento_id, $modalidade['modalidade_id'], $modalidade['tipo_publico']);
            //error_log("🔵 [DEBUG] Preço do lote: " . print_r($preco_lote, true));
            
            $opcao = [
                'modalidade_id' => $modalidade['modalidade_id'],
                'modalidade_nome' => $modalidade['modalidade_nome'],
                'distancia' => $modalidade['distancia'],
                'categoria_id' => $modalidade['categoria_id'],
                'categoria_nome' => $modalidade['categoria_nome'],
                'tipo_publico' => $modalidade['tipo_publico'],
                'kits' => [],
                'preco_lote' => $preco_lote['preco'],
                'taxa_servico' => $preco_lote['taxa_servico'],
                'quem_paga_taxa' => $preco_lote['quem_paga_taxa']
            ];
            
            //error_log("🔵 [DEBUG] Opção criada (sem kits): " . print_r($opcao, true));
            $opcoes[] = $opcao;
        } else {
            // Processar cada kit
            $kits_processados = [];
            
            foreach ($kits as $kit) {
                // Buscar itens do kit
                $sql_itens = "
                    SELECT p.nome
                    FROM kit_produtos kp
                    INNER JOIN produtos p ON kp.produto_id = p.id
                    WHERE kp.kit_id = ? AND kp.ativo = 1
                    ORDER BY kp.ordem
                ";
                
                $stmt_itens = $pdo->prepare($sql_itens);
                $stmt_itens->execute([$kit['kit_id']]);
                $itens = $stmt_itens->fetchAll(PDO::FETCH_COLUMN);
                
                // Buscar preço do lote
                $preco_lote = buscarPrecoLote($pdo, $evento_id, $modalidade['modalidade_id'], $modalidade['tipo_publico']);
                
                $kits_processados[] = [
                    'kit_id' => $kit['kit_id'],
                    'kit_nome' => $kit['kit_nome'],
                    'foto_kit' => $kit['foto_kit'],
                    'preco_lote' => $preco_lote['preco'],
                    'taxa_servico' => $preco_lote['taxa_servico'],
                    'quem_paga_taxa' => $preco_lote['quem_paga_taxa'],
                    'itens' => $itens
                ];
            }
            
            $opcoes[] = [
                'modalidade_id' => $modalidade['modalidade_id'],
                'modalidade_nome' => $modalidade['modalidade_nome'],
                'distancia' => $modalidade['distancia'],
                'categoria_id' => $modalidade['categoria_id'],
                'categoria_nome' => $modalidade['categoria_nome'],
                'tipo_publico' => $modalidade['tipo_publico'],
                'kits' => $kits_processados
            ];
        }
    }
    
    //error_log("🔵 [DEBUG] Total de opções criadas: " . count($opcoes));
    //error_log("🔵 [DEBUG] Opções finais: " . print_r($opcoes, true));
    
    $response = [
        'success' => true,
        'opcoes' => $opcoes
    ];
    
    //error_log("🔵 [DEBUG] Resposta final: " . json_encode($response));
    echo json_encode($response);
    
} catch (Exception $e) {
    //error_log("❌ [DEBUG] Erro na API: " . $e->getMessage());
    //error_log("❌ [DEBUG] Stack trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function buscarPrecoLote($pdo, $evento_id, $modalidade_id, $tipo_publico) {
    // Buscar lote específico da modalidade primeiro
    $sql = "
        SELECT preco, taxa_servico, quem_paga_taxa
        FROM lotes_inscricao
        WHERE evento_id = ? 
          AND modalidade_evento_id = ?
          AND tipo_publico = ?
          AND data_inicio <= CURDATE() 
          AND data_fim >= CURDATE()
          AND ativo = 1
        ORDER BY numero_lote DESC
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$evento_id, $modalidade_id, $tipo_publico]);
    $lote = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Se não encontrou lote específico, buscar lote geral
    if (!$lote) {
        $sql = "
            SELECT preco, taxa_servico, quem_paga_taxa
            FROM lotes_inscricao
            WHERE evento_id = ? 
              AND modalidade_evento_id IS NULL
              AND tipo_publico = ?
              AND data_inicio <= CURDATE() 
              AND data_fim >= CURDATE()
              AND ativo = 1
            ORDER BY numero_lote DESC
            LIMIT 1
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evento_id, $tipo_publico]);
        $lote = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Fallback para preço base se não houver lote
    if (!$lote) {
        return [
            'preco' => 0.00,
            'taxa_servico' => 0.00,
            'quem_paga_taxa' => 'participante'
        ];
    }
    
    return $lote;
}
?>
