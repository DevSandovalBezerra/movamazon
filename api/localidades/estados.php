<?php
header('Content-Type: application/json');
require_once '../db.php';

// Cache por 24 horas
$cache_file = __DIR__ . '/cache_estados.json';
$cache_duration = 24 * 60 * 60; // 24 horas

// Verificar se o cache existe e é válido
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
    $cached_data = json_decode(file_get_contents($cache_file), true);
    if ($cached_data && isset($cached_data['estados'])) {
        echo json_encode($cached_data);
        exit;
    }
}

try {
    // Consultar API do IBGE
    $url = 'https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome';
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'method' => 'GET',
            'header' => 'User-Agent: MovAmazon/1.0'
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        throw new Exception('Erro ao consultar API do IBGE');
    }
    
    $estados_ibge = json_decode($response, true);
    
    if (!$estados_ibge) {
        throw new Exception('Resposta inválida da API do IBGE');
    }
    
    // Normalizar dados
    $estados = [];
    foreach ($estados_ibge as $estado) {
        $estados[] = [
            'id' => $estado['id'],
            'sigla' => $estado['sigla'],
            'nome' => $estado['nome']
        ];
    }
    
    $result = [
        'success' => true,
        'estados' => $estados,
        'total' => count($estados),
        'cached_at' => date('Y-m-d H:i:s')
    ];
    
    // Salvar no cache
    file_put_contents($cache_file, json_encode($result));
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Erro ao carregar estados: " . $e->getMessage());
    
    // Tentar retornar cache expirado se disponível
    if (file_exists($cache_file)) {
        $cached_data = json_decode(file_get_contents($cache_file), true);
        if ($cached_data) {
            echo json_encode($cached_data);
            exit;
        }
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar estados',
        'error' => $e->getMessage()
    ]);
}
?>
