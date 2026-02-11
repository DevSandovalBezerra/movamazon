<?php
session_start();
require_once '../../db.php';
require_once '../../security_middleware.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Define require_organizador if not already included
if (!function_exists('require_organizador')) {
    function require_organizador()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            exit();
        }
    }
}

require_organizador();

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];

if (empty($data['nome']) || empty($data['data_inicio']) || empty($data['local']) || empty($data['categoria']) || empty($data['genero'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nome, data de início, local, categoria e gênero do evento são obrigatórios.']);
    exit();
}

$sql = "INSERT INTO eventos (
    nome, descricao, data_inicio, data_fim, categoria, genero, local, cep, 
    url_mapa, logradouro, numero, cidade, estado, pais, regulamento, status, 
    organizador_id, taxa_setup, percentual_repasse, exibir_retirada_kit, 
    taxa_gratuitas, taxa_pagas, limite_vagas, data_fim_inscricoes, hora_fim_inscricoes, 
    hora_inicio, data_realizacao, imagem
) VALUES (
    :nome, :descricao, :data_inicio, :data_fim, :categoria, :genero, :local, :cep,
    :url_mapa, :logradouro, :numero, :cidade, :estado, :pais, :regulamento, :status,
    :organizador_id, :taxa_setup, :percentual_repasse, :exibir_retirada_kit,
    :taxa_gratuitas, :taxa_pagas, :limite_vagas, :data_fim_inscricoes, :hora_fim_inscricoes,
    :hora_inicio, :data_realizacao, :imagem
)";
$stmt = $pdo->prepare($sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao preparar a query.']);
    exit();
}

$params = [
    ':nome' => $data['nome'],
    ':descricao' => $data['descricao'] ?? null,
    ':data_inicio' => $data['data_inicio'],
    ':data_fim' => $data['data_fim'] ?? null,
    ':categoria' => $data['categoria'],
    ':genero' => $data['genero'],
    ':local' => $data['local'],
    ':cep' => $data['cep'] ?? null,
    ':url_mapa' => $data['url_mapa'] ?? null,
    ':logradouro' => $data['logradouro'] ?? null,
    ':numero' => $data['numero'] ?? null,
    ':cidade' => $data['cidade'] ?? null,
    ':estado' => $data['estado'] ?? null,
    ':pais' => $data['pais'] ?? 'Brasil',
    ':regulamento' => $data['regulamento'] ?? null,
    ':status' => $data['status'] ?? 'ativo',
    ':organizador_id' => $organizador_id,
    ':taxa_setup' => $data['taxa_setup'] ?? null,
    ':percentual_repasse' => $data['percentual_repasse'] ?? null,
    ':exibir_retirada_kit' => $data['exibir_retirada_kit'] ?? 0,
    ':taxa_gratuitas' => $data['taxa_gratuitas'] ?? null,
    ':taxa_pagas' => $data['taxa_pagas'] ?? null,
    ':limite_vagas' => $data['limite_vagas'] ?? null,
    ':data_fim_inscricoes' => $data['data_fim_inscricoes'] ?? ($data['data_fim'] ?? null),
    ':hora_fim_inscricoes' => $data['hora_fim_inscricoes'] ?? null,
    ':hora_inicio' => $data['hora_inicio'] ?? null,
    ':data_realizacao' => $data['data_realizacao'] ?? null,
    ':imagem' => $data['imagem'] ?? null
];

if ($stmt->execute($params)) {
    $evento_id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'message' => 'Evento criado com sucesso!', 'evento_id' => $evento_id]);
} else {
    http_response_code(500);
    $error = $stmt->errorInfo();
    echo json_encode(['success' => false, 'message' => 'Erro ao criar o evento.', 'error_info' => $error[2]]);
}
