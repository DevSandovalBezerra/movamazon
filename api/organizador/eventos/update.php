<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];
$input = json_decode(file_get_contents('php://input'), true);
error_log('📊 Input: ' . json_encode($input));

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$evento_id = $input['id'] ?? null;
$nome = $input['nome'] ?? null;
$categoria = $input['categoria'] ?? null;
$data_inicio = $input['data_inicio'] ?? null;
$hora_inicio = $input['hora_inicio'] ?? null;
$local = $input['local'] ?? null;
$cep = $input['cep'] ?? null;
$url_mapa = $input['url_mapa'] ?? null;
$logradouro = $input['logradouro'] ?? null;
$numero = $input['numero'] ?? null;
$cidade = $input['cidade'] ?? null;
$estado = $input['estado'] ?? null;
$pais = $input['pais'] ?? null;
$limite_vagas = $input['limite_vagas'] ?? null;
$data_fim_inscricoes = $input['data_fim'] ?? null;
$hora_fim_inscricoes = $input['hora_fim_inscricoes'] ?? null;
$taxa_gratuitas = $input['taxa_gratuitas'] ?? null;
$taxa_pagas = $input['taxa_pagas'] ?? null;
$taxa_setup = $input['taxa_setup'] ?? null;
$percentual_repasse = $input['percentual_repasse'] ?? null;
$exibir_retirada_kit = $input['exibir_retirada_kit'] ?? null;
$status = $input['status'] ?? null;

if (!$evento_id || !$nome) {
    echo json_encode(['success' => false, 'message' => 'ID e nome do evento são obrigatórios']);
    exit;
}

try {
    // Verificar se o evento pertence ao organizador (compatível com legado: organizador_id pode estar como usuarios.id)
    $stmt = $pdo->prepare("SELECT id, status as status_atual FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    $eventoCheck = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log('📊 Evento encontrado: ' . json_encode($eventoCheck));
    if (!$eventoCheck) {
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou não autorizado']);
        exit;
    }
    
    // Se está tentando ativar o evento, validar checklist obrigatório
    if ($status === 'ativo' && $eventoCheck['status_atual'] !== 'ativo') {
        require_once __DIR__ . '/../../evento/validate_event_ready.php';
        $validacao = eventoPodeReceberInscricoes($pdo, $evento_id);
        
        if (!$validacao['pode_receber']) {
            $pendencias = implode(', ', $validacao['pendencias']);
            echo json_encode([
                'success' => false, 
                'message' => 'Não é possível ativar o evento. Configure primeiro: ' . $pendencias,
                'pendencias' => $validacao['pendencias']
            ]);
            exit;
        }
    }

    // Validar que campos de programação não sejam enviados acidentalmente
    $camposProgramacao = ['hora_fim', 'latitude', 'longitude', 'tipo', 'titulo', 'ordem'];
    foreach ($camposProgramacao as $campo) {
        if (isset($input[$campo])) {
            error_log("⚠️ AVISO: Campo de programação '{$campo}' recebido na API de eventos. Ignorando.");
            unset($input[$campo]);
        }
    }
    
    // Construir query de atualização dinamicamente
    $campos = [];
    $valores = [];
    
    $campos[] = "nome = ?";
    $valores[] = $nome;
    
    if ($categoria !== null) { $campos[] = "categoria = ?"; $valores[] = $categoria; }
    if ($data_inicio !== null) { $campos[] = "data_inicio = ?"; $valores[] = $data_inicio; }
    if ($hora_inicio !== null) { $campos[] = "hora_inicio = ?"; $valores[] = $hora_inicio; }
    if ($local !== null) { $campos[] = "local = ?"; $valores[] = $local; }
    if ($cep !== null) { $campos[] = "cep = ?"; $valores[] = $cep; }
    if ($url_mapa !== null) { $campos[] = "url_mapa = ?"; $valores[] = $url_mapa; }
    if ($logradouro !== null) { $campos[] = "logradouro = ?"; $valores[] = $logradouro; }
    if ($numero !== null) { $campos[] = "numero = ?"; $valores[] = $numero; }
    if ($cidade !== null) { $campos[] = "cidade = ?"; $valores[] = $cidade; }
    if ($estado !== null) { $campos[] = "estado = ?"; $valores[] = $estado; }
    if ($pais !== null) { $campos[] = "pais = ?"; $valores[] = $pais; }
    if ($limite_vagas !== null) { $campos[] = "limite_vagas = ?"; $valores[] = $limite_vagas; }
    if ($data_fim_inscricoes !== null) { $campos[] = "data_fim = ?"; $valores[] = $data_fim_inscricoes; }
    if ($hora_fim_inscricoes !== null) { $campos[] = "hora_fim_inscricoes = ?"; $valores[] = $hora_fim_inscricoes; }
    if ($taxa_gratuitas !== null) { $campos[] = "taxa_gratuitas = ?"; $valores[] = $taxa_gratuitas; }
    if ($taxa_pagas !== null) { $campos[] = "taxa_pagas = ?"; $valores[] = $taxa_pagas; }
    if ($taxa_setup !== null) { $campos[] = "taxa_setup = ?"; $valores[] = $taxa_setup; }
    if ($percentual_repasse !== null) { $campos[] = "percentual_repasse = ?"; $valores[] = $percentual_repasse; }
    if ($exibir_retirada_kit !== null) { $campos[] = "exibir_retirada_kit = ?"; $valores[] = $exibir_retirada_kit; }
    if ($status !== null) { $campos[] = "status = ?"; $valores[] = $status; }
    if (array_key_exists('imagem', $input)) {
        $campos[] = "imagem = ?";
        $valores[] = ($input['imagem'] === '' || $input['imagem'] === null) ? null : $input['imagem'];
    }
    
    $valores[] = $evento_id;
    
    $sql = "UPDATE eventos SET " . implode(", ", $campos) . " WHERE id = ?";
    error_log("📝 UPDATE eventos (id={$evento_id}): " . implode(", ", array_map(function($c) { return str_replace(" = ?", "", $c); }, $campos)));
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    // Buscar evento atualizado
    $stmt = $pdo->prepare("SELECT * FROM eventos WHERE id = ?");
    $stmt->execute([$evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log('📊 Evento atualizado: ' . json_encode($evento));
    echo json_encode([
        'success' => true,
        'data' => $evento
    ]);
} catch (PDOException $e) {
    error_log('Erro ao atualizar evento: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
