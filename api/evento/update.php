<?php
header('Content-Type: application/json');
require_once '../db.php';
require_once __DIR__ . '/../helpers/organizador_context.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Verificar se o usuário está logado como organizador e obter contexto (organizador_id vs usuario_id legado)
$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];

try {
    error_log('📡 API evento/update.php - Iniciando atualização');
    error_log('📋 POST data: ' . json_encode($_POST));

    // Validar campos obrigatórios
    $campos_obrigatorios = ['evento_id', 'nome', 'data_inicio', 'status'];
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            error_log('❌ Campo obrigatório faltando: ' . $campo);
            echo json_encode(['success' => false, 'message' => "Campo '$campo' é obrigatório"]);
            exit();
        }
    }

    error_log('✅ Todos os campos obrigatórios presentes');

    $evento_id = (int)$_POST['evento_id'];
    $nome = trim($_POST['nome']);
    $data_inicio = $_POST['data_inicio'];
    $data_fim = isset($_POST['data_fim']) && !empty($_POST['data_fim']) ? $_POST['data_fim'] : null;
    $hora_inicio = isset($_POST['hora_inicio']) && !empty($_POST['hora_inicio']) ? $_POST['hora_inicio'] : null;
    $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $local = isset($_POST['local']) ? trim($_POST['local']) : '';
    $cidade = isset($_POST['cidade']) ? trim($_POST['cidade']) : '';
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';
    $pais = isset($_POST['pais']) ? trim($_POST['pais']) : 'Brasil';
    $regulamento = isset($_POST['regulamento']) ? trim($_POST['regulamento']) : '';
    $limite_vagas = isset($_POST['limite_vagas']) && !empty($_POST['limite_vagas']) ? (int)$_POST['limite_vagas'] : null;
    $taxa_setup = isset($_POST['taxa_setup']) && !empty($_POST['taxa_setup']) ? (float)$_POST['taxa_setup'] : null;
    $taxa_gratuitas = isset($_POST['taxa_gratuitas']) && !empty($_POST['taxa_gratuitas']) ? (float)$_POST['taxa_gratuitas'] : null;
    $taxa_pagas = isset($_POST['taxa_pagas']) && !empty($_POST['taxa_pagas']) ? (float)$_POST['taxa_pagas'] : null;
    $percentual_repasse = isset($_POST['percentual_repasse']) && !empty($_POST['percentual_repasse']) ? (float)$_POST['percentual_repasse'] : null;
    $exibir_retirada_kit = isset($_POST['exibir_retirada_kit']) ? 1 : 0;
    $data_fim_inscricoes = isset($_POST['data_fim_inscricoes']) && !empty($_POST['data_fim_inscricoes'])
        ? $_POST['data_fim_inscricoes']
        : (isset($_POST['data_fim']) && !empty($_POST['data_fim']) ? $_POST['data_fim'] : null);
    $hora_fim_inscricoes = isset($_POST['hora_fim_inscricoes']) && !empty($_POST['hora_fim_inscricoes']) ? $_POST['hora_fim_inscricoes'] : null;
    $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : 'corrida_rua';
    $genero = isset($_POST['genero']) ? trim($_POST['genero']) : '';
    $cep = isset($_POST['cep']) ? trim($_POST['cep']) : '';
    $logradouro = isset($_POST['logradouro']) ? trim($_POST['logradouro']) : '';
    $numero = isset($_POST['numero']) ? trim($_POST['numero']) : '';
    $url_mapa = isset($_POST['url_mapa']) ? trim($_POST['url_mapa']) : '';
    $status = $_POST['status'];

    error_log('📊 Dados processados - Evento ID: ' . $evento_id . ', Organizador ID: ' . $organizador_id . ', Usuario ID: ' . $usuario_id);

    // Verificar se o evento existe e pertence ao organizador
    $stmt = $pdo->prepare("SELECT id, imagem, data_inicio as data_original FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?)");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    $eventoRow = $stmt->fetch();
    if (!$eventoRow) {
        error_log('❌ Evento não encontrado - ID: ' . $evento_id . ', Organizador ID: ' . $organizador_id . ', Usuario ID: ' . $usuario_id);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou não pertence a você']);
        exit();
    }

    //error_log('✅ Evento encontrado e autorizado');

    // Regra de negócio: só permitir status "ativo" se configurações obrigatórias estiverem completas
    if ($status === 'ativo') {
        require_once __DIR__ . '/validate_event_ready.php';
        $validacao = eventoPodeReceberInscricoes($pdo, $evento_id);
        
        if (!$validacao['pode_receber']) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => 'Não é possível ativar o evento. Conclua as configurações obrigatórias: ' . implode(', ', $validacao['pendencias'])
            ]);
            exit();
        }
    }

    // Validar data apenas se foi alterada (permitir edição de eventos existentes)
    $data_atual = date('Y-m-d');
    $data_original = $eventoRow['data_original'];

    //error_log('📅 Validação de data - Nova: ' . $data_inicio . ', Original: ' . $data_original . ', Atual: ' . $data_atual);

    // Se a data foi alterada, validar que não seja anterior a hoje
    if ($data_inicio !== $data_original && $data_inicio < $data_atual) {
        //error_log('❌ Data inválida - Nova data é anterior a hoje');
        echo json_encode(['success' => false, 'message' => 'A data do evento não pode ser anterior a hoje']);
        exit();
    }

    //error_log('✅ Validação de data aprovada');

    $imagem_antiga = $eventoRow['imagem'];
    $imagem_nova = $imagem_antiga;

    // --- UPLOAD DE IMAGEM DO BANNER ---
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../frontend/assets/img/eventos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        $fileTmp = $_FILES['imagem']['tmp_name'];
        $fileType = mime_content_type($fileTmp);
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg'
        ];
        if (!isset($allowedTypes[$fileType])) {
            echo json_encode(['success' => false, 'message' => 'Formato de imagem não suportado. Use JPG, PNG, WEBP ou SVG.']);
            exit();
        }
        $ext = $allowedTypes[$fileType];
        $fileName = 'evento_' . $evento_id . '.' . $ext;
        $destPath = $uploadDir . $fileName;
        // Remove imagem antiga se for diferente da nova
        if ($imagem_antiga && $imagem_antiga !== $fileName) {
            $oldPath = $uploadDir . $imagem_antiga;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }
        if (!move_uploaded_file($fileTmp, $destPath)) {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar imagem do banner.']);
            exit();
        }
        $imagem_nova = $fileName;
    }

    // Atualizar evento
    $sql = "UPDATE eventos SET 
        nome = ?, data_inicio = ?, data_fim = ?, hora_inicio = ?, descricao = ?,
        local = ?, cidade = ?, estado = ?, pais = ?, regulamento = ?, limite_vagas = ?, taxa_setup = ?, 
        taxa_gratuitas = ?, taxa_pagas = ?, percentual_repasse = ?, exibir_retirada_kit = ?,
        data_fim_inscricoes = ?, hora_fim_inscricoes = ?, categoria = ?, genero = ?,
        cep = ?, logradouro = ?, numero = ?, url_mapa = ?, status = ?, imagem = ?
        WHERE id = ? AND (organizador_id = ? OR organizador_id = ?)";

    //error_log('🔧 Executando UPDATE - SQL: ' . $sql);
    error_log('📋 Parâmetros: ' . json_encode([
        $nome,
        $data_inicio,
        $data_fim,
        $hora_inicio,
        $descricao,
        $local,
        $cidade,
        $estado,
        $pais,
        $regulamento,
        $limite_vagas,
        $taxa_setup,
        $taxa_gratuitas,
        $taxa_pagas,
        $percentual_repasse,
        $exibir_retirada_kit,
        $data_fim_inscricoes,
        $hora_fim_inscricoes,
        $categoria,
        $genero,
        $cep,
        $logradouro,
        $numero,
        $url_mapa,
        $status,
        $imagem_nova,
        $evento_id,
        $organizador_id,
        $usuario_id
    ]));

    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([
        $nome,
        $data_inicio,
        $data_fim,
        $hora_inicio,
        $descricao,
        $local,
        $cidade,
        $estado,
        $pais,
        $regulamento,
        $limite_vagas,
        $taxa_setup,
        $taxa_gratuitas,
        $taxa_pagas,
        $percentual_repasse,
        $exibir_retirada_kit,
        $data_fim_inscricoes,
        $hora_fim_inscricoes,
        $categoria,
        $genero,
        $cep,
        $logradouro,
        $numero,
        $url_mapa,
        $status,
        $imagem_nova,
        $evento_id,
        $organizador_id,
        $usuario_id
    ]);

    if ($resultado) {
        //error_log('✅ Evento atualizado com sucesso - ID: ' . $evento_id);
        echo json_encode([
            'success' => true,
            'message' => 'Evento atualizado com sucesso',
            'evento_id' => $evento_id
        ]);
    } else {
        error_log('❌ Erro ao executar UPDATE - Resultado: ' . ($resultado ? 'true' : 'false'));
        $errorInfo = $stmt->errorInfo();
        error_log('❌ Erro PDO: ' . json_encode($errorInfo));
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar evento']);
    }
} catch (PDOException $e) {
    error_log("Erro ao atualizar evento: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
} catch (Exception $e) {
    error_log("Erro inesperado ao atualizar evento: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro inesperado']);
}
