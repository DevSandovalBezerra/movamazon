<?php
header('Content-Type: application/json');
require_once '../../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

try {
    $organizador_id = $_SESSION['user_id'];
    
    //error_log('📡 API evento/list.php - Iniciando requisição - Organizador ID: ' . $organizador_id);
    
    // Parâmetros de filtro
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $data = isset($_GET['data']) ? $_GET['data'] : '';
    $busca = isset($_GET['busca']) ? $_GET['busca'] : '';
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
    
    //error_log('📋 API evento/list.php - Filtros: status=' . $status . ', data=' . $data . ', busca=' . $busca . ', pagina=' . $pagina . ', limite=' . $limite);
    
    // Validação e sanitização dos parâmetros
    $status = isset($_GET['status']) ? filter_var($_GET['status'], FILTER_SANITIZE_STRING) : '';
    $data = isset($_GET['data']) ? filter_var($_GET['data'], FILTER_SANITIZE_STRING) : '';
    $busca = isset($_GET['busca']) ? filter_var($_GET['busca'], FILTER_SANITIZE_STRING) : '';
    $pagina = isset($_GET['pagina']) ? filter_var($_GET['pagina'], FILTER_VALIDATE_INT, [
        'options' => [
            'default' => 1,
            'min_range' => 1
        ]
    ]) : 1;
    $limite = isset($_GET['limite']) ? filter_var($_GET['limite'], FILTER_VALIDATE_INT, [
        'options' => [
            'default' => 10,
            'min_range' => 1,
            'max_range' => 100
        ]
    ]) : 10;
    
    // Construir query base com prepared statements
    $sql = "SELECT 
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
                imagem
            FROM eventos 
            WHERE organizador_id = :organizador_id";
    
    $params = [':organizador_id' => $organizador_id];
    
    // Aplicar filtros
    if (!empty($status) && in_array($status, ['ativo', 'inativo', 'cancelado', 'finalizado', 'pausado', 'rascunho'])) {
        $sql .= " AND status = :status";
        $params[':status'] = $status;
    }
    
    if (!empty($data) && in_array($data, ['hoje', 'semana', 'mes', 'ano'])) {
        $hoje = date('Y-m-d');
        switch ($data) {
            case 'hoje':
                $sql .= " AND DATE(data_inicio) = :data_hoje";
                $params[':data_hoje'] = $hoje;
                break;
            case 'semana':
                $sql .= " AND data_inicio >= :data_semana_inicio AND data_inicio <= DATE_ADD(:data_semana_fim, INTERVAL 7 DAY)";
                $params[':data_semana_inicio'] = $hoje;
                $params[':data_semana_fim'] = $hoje;
                break;
            case 'mes':
                $sql .= " AND data_inicio >= :data_mes_inicio AND data_inicio <= DATE_ADD(:data_mes_fim, INTERVAL 1 MONTH)";
                $params[':data_mes_inicio'] = $hoje;
                $params[':data_mes_fim'] = $hoje;
                break;
            case 'ano':
                $sql .= " AND YEAR(data_inicio) = YEAR(:data_ano)";
                $params[':data_ano'] = $hoje;
                break;
        }
    }
    
    if (!empty($busca)) {
        $sql .= " AND (nome LIKE :busca OR descricao LIKE :busca OR local LIKE :busca)";
        $params[':busca'] = "%{$busca}%";
    }
    
    // Contar total de registros para paginação (usando os mesmos filtros)
    $countQuery = str_replace(
        'SELECT id, nome, descricao, data_inicio, data_fim, local, cidade, estado, pais, status, data_criacao, hora_inicio, imagem', 
        'SELECT COUNT(*) as total', 
        $sql
    );
    
    $countParams = $params; // Reutilizar parâmetros da query principal
    
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($countParams);
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    
   /*  error_log('🔢 API evento/list.php - Query contagem: ' . $countQuery);
    error_log('🔢 API evento/list.php - Parâmetros contagem: ' . json_encode($countParams));
    error_log('🔢 API evento/list.php - Resultado contagem: ' . json_encode($countResult)); */
    
    if (!$countResult || !isset($countResult['total'])) {
        //error_log('❌ API evento/list.php - Erro na contagem: resultado inválido');
        $totalRegistros = 0;
    } else {
        $totalRegistros = $countResult['total'];
        error_log('✅ API evento/list.php - Total registros: ' . $totalRegistros);
    }
    
    // Adicionar ordenação e paginação
    $sql .= " ORDER BY data_criacao DESC LIMIT :limite OFFSET :offset";
    $offset = ($pagina - 1) * $limite;
    
    // Adicionar parâmetros de paginação
    $params[':limite'] = $limite;
    $params[':offset'] = $offset;
    
    // Preparar e executar a query principal
    $stmt = $pdo->prepare($sql);
    
    // Bind dos parâmetros com tipos explícitos
    foreach ($params as $key => $value) {
        if ($key === ':limite' || $key === ':offset') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    
    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
       /*      error_log('📊 API evento/list.php - Query principal: ' . $sql);
            error_log('📊 API evento/list.php - Parâmetros principais: ' . json_encode($params));
            error_log('📊 API evento/list.php - Eventos encontrados: ' . count($eventos)); */
    
    // Processar dados
    foreach ($eventos as &$evento) {
        // Formatar datas
        if ($evento['data_inicio']) {
            $evento['data_inicio_formatada'] = date('d/m/Y', strtotime($evento['data_inicio']));
        }
        if ($evento['data_fim']) {
            $evento['data_fim_formatada'] = date('d/m/Y', strtotime($evento['data_fim']));
        }
        if ($evento['data_criacao']) {
            $evento['data_criacao_formatada'] = date('d/m/Y H:i', strtotime($evento['data_criacao']));
        }
        
        // Status traduzido
        $status_traduzido = [
            'ativo' => 'Ativo',
            'inativo' => 'Inativo',
            'cancelado' => 'Cancelado',
            'finalizado' => 'Finalizado',
            'pausado' => 'Pausado',
            'rascunho' => 'Rascunho'
        ];
        $evento['status_traduzido'] = $status_traduzido[$evento['status']] ?? $evento['status'];
        
        // Imagem
        if ($evento['imagem']) {
            $evento['imagem_url'] = '../../assets/img/eventos/' . $evento['imagem'];
        } else {
            $evento['imagem_url'] = '../../assets/img/default-event.jpg';
        }
    }
    
    // Calcular informações de paginação
    $totalPaginas = ceil($totalRegistros / $limite);
    
    //error_log('✅ API eventos/list.php - Sucesso: ' . count($eventos) . ' eventos, ' . $totalPaginas . ' páginas');
    
    echo json_encode([
        'success' => true,
        'data' => [
            'eventos' => $eventos,
            'paginacao' => [
                'pagina_atual' => $pagina,
                'total_paginas' => $totalPaginas,
                'total_registros' => $totalRegistros,
                'registros_por_pagina' => $limite
            ]
        ]
    ]);
    
} catch (PDOException $e) {
   // error_log('Erro no banco de dados ao listar eventos: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco de dados']);
} catch (Exception $e) {
    error_log('Erro ao listar eventos: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
