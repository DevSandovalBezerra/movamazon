<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security_middleware.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

$usuario_id = $_SESSION['user_id'];

try {
    $stmt_check = $pdo->prepare("SELECT id, foto_perfil FROM usuarios WHERE id = ? AND status = 'ativo'");
    $stmt_check->execute([$usuario_id]);
    $usuario_existe = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$usuario_existe) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
        exit();
    }

    $dados = [];
    if (!empty($_FILES)) {
        $dados = $_POST;
    } else {
        $dados = json_decode(file_get_contents('php://input'), true);
        if (!$dados) {
            $dados = [];
        }
    }

    $campos_update = [];
    $valores = [];

    $campos_permitidos = [
        'nome_completo', 'telefone', 'celular', 'data_nascimento', 'endereco', 
        'numero', 'complemento', 'bairro', 'cidade', 'uf', 'cep', 'pais', 'sexo'
    ];

    // Validação e processamento do nome_completo
    if (isset($dados['nome_completo'])) {
        $nome_completo = trim($dados['nome_completo']);
        if (empty($nome_completo)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nome completo é obrigatório.']);
            exit();
        }
        if (strlen($nome_completo) < 3) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nome completo deve ter no mínimo 3 caracteres.']);
            exit();
        }
        $campos_update[] = "nome_completo = ?";
        $valores[] = $nome_completo;
    }

    // Validação e processamento do CPF (OBRIGATÓRIO para boleto)
    if (isset($dados['cpf']) && !empty($dados['cpf'])) {
        // Limpar formatação do CPF (remover pontos e traços)
        $cpf = preg_replace('/[^0-9]/', '', $dados['cpf']);
        
        // Validar se tem 11 dígitos
        if (strlen($cpf) !== 11) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'CPF deve conter 11 dígitos.']);
            exit();
        }
        
        $campos_update[] = "documento = ?";
        $valores[] = $cpf;
        
        $campos_update[] = "tipo_documento = ?";
        $valores[] = 'CPF';
    } else {
        // CPF é obrigatório - verificar se já existe no banco
        $stmt_cpf = $pdo->prepare("SELECT documento FROM usuarios WHERE id = ?");
        $stmt_cpf->execute([$usuario_id]);
        $usuario_cpf = $stmt_cpf->fetch(PDO::FETCH_ASSOC);
        
        if (empty($usuario_cpf['documento']) || strlen(preg_replace('/[^0-9]/', '', $usuario_cpf['documento'])) !== 11) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'CPF é obrigatório para pagamento com boleto.']);
            exit();
        }
    }

    // Campos obrigatórios para boleto
    $campos_obrigatorios_boleto = ['cep', 'endereco', 'numero', 'bairro', 'cidade', 'uf'];
    $campos_faltando = [];
    
    // Primeiro, verificar quais campos obrigatórios estão faltando (no banco ou no request)
    foreach ($campos_obrigatorios_boleto as $campo) {
        $valor_enviado = isset($dados[$campo]) ? trim($dados[$campo]) : null;
        $valor_valido = false;
        
        if ($campo === 'cep') {
            if ($valor_enviado) {
                $cep_limpo = preg_replace('/[^0-9]/', '', $valor_enviado);
                $valor_valido = !empty($cep_limpo) && strlen($cep_limpo) === 8;
            }
        } elseif ($campo === 'uf') {
            if ($valor_enviado) {
                $uf_limpo = strtoupper(trim($valor_enviado));
                $valor_valido = !empty($uf_limpo) && strlen($uf_limpo) === 2;
            }
        } else {
            $valor_valido = !empty($valor_enviado);
        }
        
        // Se não foi enviado ou é inválido, verificar se existe no banco
        if (!$valor_valido) {
            $stmt_check = $pdo->prepare("SELECT $campo FROM usuarios WHERE id = ?");
            $stmt_check->execute([$usuario_id]);
            $valor_existente = $stmt_check->fetchColumn();
            
            if ($campo === 'cep') {
                $cep_existente = preg_replace('/[^0-9]/', '', $valor_existente ?? '');
                $valor_valido = !empty($cep_existente) && strlen($cep_existente) === 8;
            } elseif ($campo === 'uf') {
                $uf_existente = strtoupper(trim($valor_existente ?? ''));
                $valor_valido = !empty($uf_existente) && strlen($uf_existente) === 2;
            } else {
                $valor_existente_limpo = trim((string)($valor_existente ?? ''));
                $valor_valido = !empty($valor_existente_limpo);
            }
            
            if (!$valor_valido) {
                $campos_faltando[] = ucfirst($campo === 'uf' ? 'Estado (UF)' : $campo);
            }
        }
    }
    
    // Se há campos obrigatórios faltando, retornar erro ANTES de atualizar
    if (!empty($campos_faltando)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Os seguintes campos são obrigatórios para pagamento com boleto: ' . implode(', ', $campos_faltando) . '.',
            'campos_faltando' => $campos_faltando
        ]);
        exit();
    }
    
    // Processar campos para atualização (após validação)
    foreach ($campos_permitidos as $campo) {
        if ($campo === 'nome_completo') {
            continue; // Já processado acima
        }
        
        if (isset($dados[$campo])) {
            $valor = trim($dados[$campo]);
            
            // Limpar e validar campos específicos
            if ($campo === 'cep') {
                $valor = preg_replace('/[^0-9]/', '', $valor);
            } elseif ($campo === 'uf') {
                $valor = strtoupper(trim($valor));
            }
            
            $campos_update[] = "$campo = ?";
            $valores[] = $valor ?: null;
        }
    }

    $foto_perfil = $usuario_existe['foto_perfil'];

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../frontend/assets/img/perfis/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_info = pathinfo($_FILES['foto_perfil']['name']);
        $extension = strtolower($file_info['extension']);

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowed_extensions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Formato de imagem não suportado. Use: JPG, PNG ou WEBP']);
            exit();
        }

        if ($_FILES['foto_perfil']['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Tamanho máximo: 5MB']);
            exit();
        }

        $filename = 'perfil_' . $usuario_id . '_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $filepath)) {
            if ($foto_perfil && file_exists(__DIR__ . '/../../' . $foto_perfil)) {
                unlink(__DIR__ . '/../../' . $foto_perfil);
            }
            $foto_perfil = 'frontend/assets/img/perfis/' . $filename;
            $campos_update[] = "foto_perfil = ?";
            $valores[] = $foto_perfil;
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload da imagem']);
            exit();
        }
    }

    if (empty($campos_update)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nenhum campo válido para atualizar.']);
        exit();
    }

    $valores[] = $usuario_id;

    $sql = "UPDATE usuarios SET " . implode(', ', $campos_update) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    $stmt_get = $pdo->prepare("
        SELECT 
            id, nome_completo, email, telefone, celular, data_nascimento,
            endereco, numero, complemento, bairro, cidade, uf, cep, pais, sexo, foto_perfil
        FROM usuarios 
        WHERE id = ?
    ");
    $stmt_get->execute([$usuario_id]);
    $usuario_atualizado = $stmt_get->fetch(PDO::FETCH_ASSOC);

    $_SESSION['user_name'] = $usuario_atualizado['nome_completo'];
    $_SESSION['user_email'] = $usuario_atualizado['email'];

    error_log("[UPDATE_PERFIL] Perfil atualizado para usuário ID: $usuario_id");

    echo json_encode([
        'success' => true,
        'message' => 'Perfil atualizado com sucesso.',
        'usuario' => $usuario_atualizado
    ]);

} catch (Exception $e) {
    error_log("[UPDATE_PERFIL] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar perfil.']);
}

