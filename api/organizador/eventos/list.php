<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once '../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

error_log('🚀 API eventos/list.php - Iniciando requisição');

// Verificar autenticação e permissões usando middleware centralizado
verificarAutenticacao('organizador');

error_log('✅ API eventos/list.php - Autenticação válida: user_id=' . $_SESSION['user_id'] . ', papel=' . $_SESSION['papel']);

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    error_log('🔧 API eventos/list.php - Organizador ID: ' . $organizador_id);
    
   // error_log('✅ API eventos/list.php - Conexão com banco estabelecida');

    // Parâmetros da requisição
    $status = $_GET['status'] ?? null;
    $data = $_GET['data'] ?? null;
    $busca = $_GET['busca'] ?? null;
    $pagina = max(1, intval($_GET['pagina'] ?? 1));
    $porPagina = min(max(10, intval($_GET['por_pagina'] ?? 10)), 100);
    
    error_log('📋 API eventos/list.php - Parâmetros: status=' . $status . ', data=' . $data . ', busca=' . $busca . ', pagina=' . $pagina . ', porPagina=' . $porPagina);

    // Construir query base
    $baseQuery = "SELECT 
                    id,
                    nome,
                    descricao,
                    data_inicio,
                    data_fim,
                    local,
                    cidade,
                    estado,
                    pais,
                    status,
                    data_criacao,
                    hora_inicio,
                    imagem,
                    deleted_at
                FROM eventos 
                WHERE (organizador_id = :organizador_id OR organizador_id = :usuario_id) AND deleted_at IS NULL";

    $params = [':organizador_id' => $organizador_id, ':usuario_id' => $usuario_id];
    $whereClauses = [];

    // Aplicar filtros
    if (!empty($status) && in_array($status, ['ativo', 'inativo', 'cancelado', 'finalizado', 'pausado', 'rascunho'])) {
        $whereClauses[] = "status = :status";
        $params[':status'] = $status;
    }

    if (!empty($data) && in_array($data, ['hoje', 'semana', 'mes', 'ano'])) {
        $hoje = date('Y-m-d');
        switch ($data) {
            case 'hoje':
                $whereClauses[] = "DATE(data_inicio) = :data_hoje";
                $params[':data_hoje'] = $hoje;
                break;
            case 'semana':
                $whereClauses[] = "data_inicio >= :data_semana_inicio AND data_inicio <= DATE_ADD(:data_semana_fim, INTERVAL 7 DAY)";
                $params[':data_semana_inicio'] = $hoje;
                $params[':data_semana_fim'] = $hoje;
                break;
            case 'mes':
                $whereClauses[] = "data_inicio >= :data_mes_inicio AND data_inicio <= DATE_ADD(:data_mes_fim, INTERVAL 1 MONTH)";
                $params[':data_mes_inicio'] = $hoje;
                $params[':data_mes_fim'] = $hoje;
                break;
            case 'ano':
                $whereClauses[] = "YEAR(data_inicio) = YEAR(:data_ano)";
                $params[':data_ano'] = $hoje;
                break;
        }
    }

    if (!empty($busca)) {
        $whereClauses[] = "(nome LIKE :busca OR descricao LIKE :busca OR local LIKE :busca)";
        $params[':busca'] = "%{$busca}%";
    }

    // Construir query final com WHERE clauses
    $sql = $baseQuery;
    if (!empty($whereClauses)) {
        $sql .= " AND " . implode(" AND ", $whereClauses);
    }
    
    error_log('🔍 API eventos/list.php - Query principal: ' . $sql);
    error_log('🔍 API eventos/list.php - Parâmetros: ' . json_encode($params));

    // Query para contagem total (mesmos filtros)
    $countQuery = "SELECT COUNT(*) as total FROM eventos WHERE (organizador_id = :organizador_id OR organizador_id = :usuario_id) AND deleted_at IS NULL";
    if (!empty($whereClauses)) {
        $countQuery .= " AND " . implode(" AND ", $whereClauses);
    }
    
   // error_log('🔢 API eventos/list.php - Query contagem: ' . $countQuery);

    // Executar contagem
    try {
       // error_log('🔢 API eventos/list.php - Executando contagem...');
        $countStmt = $pdo->prepare($countQuery);
        
        // Bind dos parâmetros
        foreach ($params as $key => $value) {
            $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $countStmt->bindValue($key, $value, $paramType);
        }
        
        $countStmt->execute();
        $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
        
        $totalRegistros = (int)($countResult['total'] ?? 0);
        $totalPaginas = ceil($totalRegistros / $porPagina);
        
       // error_log("✅ API eventos/list.php - Total registros: {$totalRegistros}, Total páginas: {$totalPaginas}");
        
    } catch (PDOException $e) {
       // error_log('❌ API eventos/list.php - Erro na contagem: ' . $e->getMessage());
        $totalRegistros = 0;
        $totalPaginas = 0;
    }

    // Adicionar ordenação e paginação à query principal
    $sql .= " ORDER BY data_criacao DESC";
    
    $offset = ($pagina - 1) * $porPagina;
    $sql .= " LIMIT :limit OFFSET :offset";
    $params[':limit'] = $porPagina;
    $params[':offset'] = $offset;

    // Executar query principal
    try {
       // error_log('🔍 API eventos/list.php - Executando query principal...');
        $stmt = $pdo->prepare($sql);
        
        // Bind dos parâmetros
        foreach ($params as $key => $value) {
            $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $paramType);
        }
        
        $stmt->execute();
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log('✅ API eventos/list.php - Eventos encontrados: ' . count($eventos));
        
        // Debug específico para evento Sauim
        foreach ($eventos as $evento) {
            if (stripos($evento['nome'], 'sauim') !== false) {
                error_log('🚨 API eventos/list.php - EVENTO SAUIM ENCONTRADO: ' . json_encode($evento));
                error_log('🚨 API eventos/list.php - deleted_at value: ' . var_export($evento['deleted_at'], true));
            }
        }
        
        // Log de todos os eventos para debug
        error_log('📋 API eventos/list.php - Lista completa de eventos: ' . json_encode(array_map(function($e) { return ['id' => $e['id'], 'nome' => $e['nome'], 'deleted_at' => $e['deleted_at']]; }, $eventos)));
        
        // Formatar datas e outros campos se necessário
        foreach ($eventos as &$evento) {
            $evento['data_inicio_formatada'] = date('d/m/Y', strtotime($evento['data_inicio']));
            // Outras formatações...
        }
        
    } catch (PDOException $e) {
       // error_log('❌ API eventos/list.php - Erro ao buscar eventos: ' . $e->getMessage());
        $eventos = [];
    }

    // Retornar resposta
   // error_log('✅ API eventos/list.php - Retornando resposta com ' . count($eventos) . ' eventos');
    echo json_encode([
        'success' => true,
        'data' => [
            'eventos' => $eventos,
            'paginacao' => [
                'pagina_atual' => $pagina,
                'por_pagina' => $porPagina,
                'total_registros' => $totalRegistros,
                'total_paginas' => $totalPaginas
            ]
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log('❌ API eventos/list.php - Erro fatal: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno no servidor'
    ]);
} 
