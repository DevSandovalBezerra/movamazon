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

// Verificar se o usuário está logado como organizador
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Validar campos obrigatórios
    $campos_obrigatorios = ['nome', 'data_inicio', 'hora_inicio', 'local', 'cidade', 'estado', 'status', 'data_realizacao'];

    foreach ($campos_obrigatorios as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo json_encode(['success' => false, 'message' => "Campo '$campo' é obrigatório"]);
            exit();
        }
    }

    // Validar data de início das inscrições
    $data_inicio = $_POST['data_inicio'];
    $data_atual = date('Y-m-d');
    if ($data_inicio < $data_atual) {
        echo json_encode(['success' => false, 'message' => 'A data de início das inscrições não pode ser anterior a hoje']);
        exit();
    }

    // Preparar dados para inserção
    $nome = trim($_POST['nome']);
    $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $data_inicio = $_POST['data_inicio']; // data_inicio na tabela = início das inscrições
    $data_fim = isset($_POST['data_fim']) && !empty($_POST['data_fim']) ? $_POST['data_fim'] : $data_inicio; // data_fim na tabela = fim das inscrições
    $hora_inicio = $_POST['hora_inicio'];
    $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : 'corrida_rua';
    $genero = isset($_POST['genero']) ? trim($_POST['genero']) : '';
    $local = trim($_POST['local']);
    $logradouro = isset($_POST['logradouro']) ? trim($_POST['logradouro']) : '';
    $numero = isset($_POST['numero']) ? trim($_POST['numero']) : '';
    $cidade = trim($_POST['cidade']);
    $estado = $_POST['estado'];
    $cep = isset($_POST['cep']) ? trim($_POST['cep']) : '';
    $pais = isset($_POST['pais']) ? trim($_POST['pais']) : 'Brasil';
    $regulamento = isset($_POST['regulamento']) ? trim($_POST['regulamento']) : '';
    $status = $_POST['status'];
    $taxa_setup = isset($_POST['taxa_setup']) && !empty($_POST['taxa_setup']) ? (float)$_POST['taxa_setup'] : null;
    $percentual_repasse = isset($_POST['percentual_repasse']) && !empty($_POST['percentual_repasse']) ? (float)$_POST['percentual_repasse'] : null;
    $exibir_retirada_kit = isset($_POST['exibir_retirada_kit']) ? (int)$_POST['exibir_retirada_kit'] : 0;
    $taxa_gratuitas = isset($_POST['taxa_gratuitas']) && !empty($_POST['taxa_gratuitas']) ? (float)$_POST['taxa_gratuitas'] : null;
    $taxa_pagas = isset($_POST['taxa_pagas']) && !empty($_POST['taxa_pagas']) ? (float)$_POST['taxa_pagas'] : null;
    $limite_vagas = isset($_POST['limite_vagas']) && !empty($_POST['limite_vagas']) ? (int)$_POST['limite_vagas'] : null;
    $hora_fim_inscricoes = isset($_POST['hora_fim_inscricoes']) && !empty($_POST['hora_fim_inscricoes']) ? $_POST['hora_fim_inscricoes'] : null;
    $data_realizacao = $_POST['data_realizacao'];
    $hora_corrida = isset($_POST['hora_corrida']) && !empty($_POST['hora_corrida']) ? $_POST['hora_corrida'] : null;
    $url_mapa = isset($_POST['url_mapa']) ? trim($_POST['url_mapa']) : '';
    $imagem = isset($_POST['imagem']) ? trim($_POST['imagem']) : '';

    // Inserir evento (sem imagem ainda)
    $pdo->beginTransaction();
    $sql = "INSERT INTO eventos (
        nome, descricao, data_inicio, data_fim, categoria, genero, local, 
        cep, url_mapa, logradouro, numero, cidade, estado, pais, regulamento, 
        status, organizador_id, taxa_setup, percentual_repasse, exibir_retirada_kit, 
        taxa_gratuitas, taxa_pagas, limite_vagas, hora_fim_inscricoes, 
        hora_inicio, data_realizacao, hora_corrida, imagem, data_criacao
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([
        $nome,
        $descricao,
        $data_inicio,
        $data_fim,
        $categoria,
        $genero,
        $local,
        $cep,
        $url_mapa,
        $logradouro,
        $numero,
        $cidade,
        $estado,
        $pais,
        $regulamento,
        $status,
        $organizador_id,
        $taxa_setup,
        $percentual_repasse,
        $exibir_retirada_kit,
        $taxa_gratuitas,
        $taxa_pagas,
        $limite_vagas,
        $hora_fim_inscricoes,
        $hora_inicio,
        $data_realizacao,
        $hora_corrida,
        $imagem,
        date('Y-m-d H:i:s')
    ]);

    if (!$resultado) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar evento']);
        exit();
    }

    $evento_id = $pdo->lastInsertId();

    // Upload de imagem do evento (opcional)
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__, 2) . '/frontend/assets/img/eventos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $tmpPath = $_FILES['imagem']['tmp_name'];
        $mime = @mime_content_type($tmpPath);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];
        if (!isset($allowed[$mime])) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Formato de imagem não suportado. Use JPG, PNG ou WEBP.']);
            exit();
        }

        $ext = $allowed[$mime];
        $fileName = 'evento_' . $evento_id . '.' . $ext;
        $destPath = $uploadDir . $fileName;

        if (!move_uploaded_file($tmpPath, $destPath)) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar imagem do evento.']);
            exit();
        }

        // Atualizar campo imagem com o nome do arquivo
        $up = $pdo->prepare('UPDATE eventos SET imagem = ? WHERE id = ?');
        if (!$up->execute([$fileName, $evento_id])) {
            // tentativa de limpeza do arquivo
            @unlink($destPath);
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar imagem do evento.']);
            exit();
        }
    }

    // Upload de regulamento (opcional)
    $regulamento_arquivo = null;
    if (isset($_FILES['regulamento_arquivo']) && $_FILES['regulamento_arquivo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__, 2) . '/frontend/assets/docs/regulamentos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $tmpPath = $_FILES['regulamento_arquivo']['tmp_name'];
        $originalName = $_FILES['regulamento_arquivo']['name'];
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Validar extensão
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Formato de regulamento não suportado. Use PDF, DOC ou DOCX.']);
            exit();
        }

        // Validar tamanho (10MB)
        if ($_FILES['regulamento_arquivo']['size'] > 10 * 1024 * 1024) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Arquivo de regulamento muito grande. Tamanho máximo: 10MB.']);
            exit();
        }

        $fileName = 'regulamento_' . $evento_id . '_' . time() . '.' . $fileExtension;
        $destPath = $uploadDir . $fileName;

        if (!move_uploaded_file($tmpPath, $destPath)) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar regulamento do evento.']);
            exit();
        }

        $regulamento_arquivo = 'frontend/assets/docs/regulamentos/' . $fileName;

        // Atualizar campo regulamento_arquivo com o caminho do arquivo
        $up = $pdo->prepare('UPDATE eventos SET regulamento_arquivo = ? WHERE id = ?');
        if (!$up->execute([$regulamento_arquivo, $evento_id])) {
            @unlink($destPath);
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar regulamento do evento.']);
            exit();
        }
    }

    $pdo->commit();

    // Log da criação
    error_log("Evento criado - ID: $evento_id, Nome: $nome, Organizador: $organizador_id");

    echo json_encode([
        'success' => true,
        'message' => 'Evento criado com sucesso',
        'evento_id' => $evento_id
    ]);
} catch (PDOException $e) {
    error_log("Erro ao criar evento: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
} catch (Exception $e) {
    error_log("Erro inesperado ao criar evento: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro inesperado']);
}
