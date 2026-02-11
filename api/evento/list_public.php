<?php
/* error_log("============================================");
error_log("[LIST_PUBLIC] Iniciando requisição...");
error_log("[LIST_PUBLIC] Timestamp: " . date('Y-m-d H:i:s'));
error_log("[LIST_PUBLIC] Método: " . $_SERVER['REQUEST_METHOD']);
error_log ("[LIST_PUBLIC] URL: " . $_SERVER['REQUEST_URI']);*/

header('Content-Type: application/json');
require_once '../db.php';

error_log("[LIST_PUBLIC] db.php incluído");

// Filtros opcionais
$search = $_GET['search'] ?? '';
$cidade = $_GET['cidade'] ?? '';
$data_filtro = $_GET['data'] ?? '';
$data_inicio_de = $_GET['data_realizacao_de'] ?? '';
$data_inicio_ate = $_GET['data_realizacao_ate'] ?? '';
$mes_ano_de = $_GET['mes_ano_de'] ?? '';
$mes_ano_ate = $_GET['mes_ano_ate'] ?? '';

/* error_log("[LIST_PUBLIC] Parâmetros recebidos:");
error_log("[LIST_PUBLIC]   - search: " . $search);
error_log("[LIST_PUBLIC]   - cidade: " . $cidade);
error_log("[LIST_PUBLIC]   - data_filtro: " . $data_filtro);
error_log("[LIST_PUBLIC]   - data_inicio_de: " . $data_inicio_de);
error_log("[LIST_PUBLIC]   - data_inicio_ate: " . $data_inicio_ate);
 */
try {
    error_log("[LIST_PUBLIC] Construindo query SQL...");
    
    // Validação: apenas eventos ativos E com configurações obrigatórias completas
    $where = [
        "e.status = 'ativo'",
        // Verificar Modalidades (OBRIGATÓRIO)
        "(SELECT COUNT(*) FROM modalidades WHERE modalidades.evento_id = e.id AND modalidades.ativo = 1) > 0",
        // Verificar Lotes de Inscrição (OBRIGATÓRIO)
        "(SELECT COUNT(*) FROM lotes_inscricao WHERE lotes_inscricao.evento_id = e.id AND lotes_inscricao.ativo = 1) > 0",
        // Verificar Programação (OBRIGATÓRIO)
        "(SELECT COUNT(*) FROM programacao_evento WHERE programacao_evento.evento_id = e.id AND programacao_evento.ativo = 1) > 0",
        // Verificar Tamanhos (CONDICIONAL - só se evento tem kit)
        "((e.exibir_retirada_kit = 0) OR (e.exibir_retirada_kit = 1 AND (SELECT COUNT(*) FROM camisas WHERE camisas.evento_id = e.id AND camisas.ativo = 1) > 0))"
    ];
    $params = [];

    // Filtro por busca (nome/cidade)
    if (!empty($search)) {
        $where[] = "(e.nome LIKE ? OR e.cidade LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    // Filtro por cidade
    if (!empty($cidade)) {
        $where[] = "e.cidade = ?";
        $params[] = $cidade;
    }
    // Filtro por data (só aplica se o parâmetro 'data' for passado)
    if ($data_filtro === 'proximos_30_dias') {
        $where[] = "COALESCE(e.data_realizacao, e.data_inicio) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    } elseif ($data_filtro === 'proximos_3_meses') {
        $where[] = "COALESCE(e.data_realizacao, e.data_inicio) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 MONTH)";
    }

    // Filtro por período livre (data_realizacao)
    if (!empty($data_inicio_de)) {
        $where[] = "COALESCE(e.data_realizacao, e.data_inicio) >= ?";
        $params[] = $data_inicio_de;
    }
    if (!empty($data_inicio_ate)) {
        $where[] = "COALESCE(e.data_realizacao, e.data_inicio) <= ?";
        $params[] = $data_inicio_ate;
    }

    // Filtro por mês/ano
    if (!empty($mes_ano_de)) {
        $where[] = "DATE_FORMAT(COALESCE(e.data_realizacao, e.data_inicio), '%Y-%m') >= ?";
        $params[] = $mes_ano_de;
    }
    if (!empty($mes_ano_ate)) {
        $where[] = "DATE_FORMAT(COALESCE(e.data_realizacao, e.data_inicio), '%Y-%m') <= ?";
        $params[] = $mes_ano_ate;
    }
    // Se nenhum filtro de data for passado, NÃO filtra por data (traz todos os eventos ativos)

    $where_sql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
    #error_log("[LIST_PUBLIC] WHERE SQL construído: " . $where_sql);
    #error_log("[LIST_PUBLIC] Parâmetros: " . print_r($params, true));

    $sql = "
        SELECT 
            e.id,
            e.nome,
            e.descricao,
            COALESCE(e.data_realizacao, e.data_inicio) as data_evento,
            e.hora_inicio,
            e.local as endereco,
            e.cidade,
            e.estado,
            e.cep,
            e.limite_vagas as limite_participantes,
            e.status,
            e.imagem,
            e.data_criacao,
            COALESCE(u_org.nome_completo, u_legacy.nome_completo) as organizador,
            COALESCE(u_org.email, u_legacy.email) as email_organizador,
            CONCAT(e.cidade, ' - ', e.estado) as local_formatado,
            DATE_FORMAT(COALESCE(e.data_realizacao, e.data_inicio), '%d/%m/%Y') as data_formatada,
            TIME_FORMAT(e.hora_inicio, '%H:%M') as hora_formatada,
            (SELECT COUNT(*) FROM inscricoes i WHERE i.evento_id = e.id) as inscritos
        FROM eventos e
        LEFT JOIN organizadores o ON e.organizador_id = o.id
        LEFT JOIN usuarios u_org ON u_org.id = o.usuario_id
        LEFT JOIN usuarios u_legacy ON u_legacy.id = e.organizador_id
        $where_sql
        ORDER BY COALESCE(e.data_realizacao, e.data_inicio) ASC, e.hora_inicio ASC
    ";

    #error_log("[LIST_PUBLIC] SQL final: " . $sql);
    #error_log("[LIST_PUBLIC] Preparando statement...");
    
    $stmt = $pdo->prepare($sql);
    
    if (!$stmt) {
        error_log("[LIST_PUBLIC] ❌ ERRO: Falha ao preparar statement");
        error_log("[LIST_PUBLIC] Erro PDO: " . print_r($pdo->errorInfo(), true));
        throw new Exception("Falha ao preparar query SQL");
    }
    
    #error_log("[LIST_PUBLIC] Executando query...");
    $executado = $stmt->execute($params);
    #error_log("[LIST_PUBLIC] Query executada: " . ($executado ? 'SUCESSO' : 'FALHA'));
    
    if (!$executado) {
        error_log("[LIST_PUBLIC] ❌ ERRO: Falha ao executar query");
        error_log("[LIST_PUBLIC] Erro PDO: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Falha ao executar query SQL");
    }
    
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    #error_log("[LIST_PUBLIC] ✅ Eventos encontrados: " . count($eventos));

    // Buscar modalidades para cada evento
    #error_log("[LIST_PUBLIC] Buscando modalidades para " . count($eventos) . " eventos...");
    foreach ($eventos as &$evento) {
        #error_log("[LIST_PUBLIC] Processando evento ID: " . $evento['id']);
        $sql_modalidades = "
            SELECT 
                m.nome as nome_modalidade,
                m.distancia,
                m.tipo_prova,
                m.limite_vagas,
                c.nome as nome_categoria,
                c.tipo_publico,
                c.idade_min,
                c.idade_max
            FROM modalidades m
            INNER JOIN categorias c ON m.categoria_id = c.id
            WHERE m.evento_id = ? AND m.ativo = 1
            ORDER BY c.nome, m.nome
        ";
        $stmt_modalidades = $pdo->prepare($sql_modalidades);
        $stmt_modalidades->execute([$evento['id']]);
        $modalidades = $stmt_modalidades->fetchAll(PDO::FETCH_ASSOC);

        $modalidades_formatadas = [];
        foreach ($modalidades as $modalidade) {
            $modalidades_formatadas[] = [
                'nome' => $modalidade['nome_modalidade'],
                'distancia' => $modalidade['distancia'],
                'tipo_prova' => $modalidade['tipo_prova'],
                'categoria' => [
                    'nome' => $modalidade['nome_categoria'],
                    'tipo_publico' => $modalidade['tipo_publico'],
                    'idade_min' => $modalidade['idade_min'],
                    'idade_max' => $modalidade['idade_max']
                ]
            ];
        }
        $evento['modalidades'] = $modalidades_formatadas;

        // Criar tags de modalidades para exibição
        $tags_modalidades = [];
        foreach ($modalidades as $modalidade) {
            $tag = $modalidade['nome_modalidade'];
            if ($modalidade['distancia']) {
                $tag .= " ({$modalidade['distancia']})";
            }
            $tags_modalidades[] = $tag;
        }
        $evento['modalidades_tags'] = $tags_modalidades;
        error_log("[LIST_PUBLIC] Evento ID " . $evento['id'] . " processado com " . count($modalidades_formatadas) . " modalidades");
    }

    $response = [
        'success' => true,
        'eventos' => $eventos,
        'total' => count($eventos)
    ];
    
    ##error_log("[LIST_PUBLIC] ✅ Resposta preparada com " . count($eventos) . " eventos");
    #error_log("[LIST_PUBLIC] Enviando resposta JSON...");
    
    echo json_encode($response);
    error_log("[LIST_PUBLIC] ✅ Resposta enviada com sucesso");
    error_log("============================================");
    
} catch (PDOException $e) {
    error_log("[LIST_PUBLIC] ❌ ERRO PDO: " . $e->getMessage());
    error_log("[LIST_PUBLIC] Código do erro: " . $e->getCode());
    error_log("[LIST_PUBLIC] Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
    error_log("============================================");
} catch (Exception $e) {
    error_log("[LIST_PUBLIC] ❌ ERRO GERAL: " . $e->getMessage());
    error_log("[LIST_PUBLIC] Código do erro: " . $e->getCode());
    error_log("[LIST_PUBLIC] Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
    error_log("============================================");
}
