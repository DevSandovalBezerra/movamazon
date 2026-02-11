<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../helpers/email_helper.php';

header('Content-Type: application/json');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$id = isset($payload['id']) ? (int) $payload['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM solicitacoes_evento WHERE id = :id FOR UPDATE");
    $stmt->execute(['id' => $id]);
    $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$solicitacao) {
        throw new RuntimeException('Solicitação não encontrada');
    }

    if ($solicitacao['status'] === 'aprovado') {
        throw new RuntimeException('Solicitação já aprovada');
    }

    $email = $solicitacao['responsavel_email'];
    $nome = $solicitacao['responsavel_nome'];

    $stmtUser = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
    $stmtUser->execute(['email' => $email]);
    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

    $senhaTemporaria = bin2hex(random_bytes(4));
$hash = password_hash($senhaTemporaria, PASSWORD_DEFAULT);

    if ($usuario) {
        $usuarioId = $usuario['id'];
        $updateUser = $pdo->prepare("UPDATE usuarios SET nome_completo = :nome, telefone = :telefone, papel = 'organizador' WHERE id = :id");
        $updateUser->execute([
            'nome' => $nome,
            'telefone' => $solicitacao['responsavel_telefone'],
            'id' => $usuarioId
        ]);
    } else {
        $insertUser = $pdo->prepare("
            INSERT INTO usuarios (
                nome_completo, email, senha, telefone, celular, pais, status, papel, data_cadastro
            ) VALUES (
                :nome, :email, :senha, :telefone, :telefone, 'Brasil', 'ativo', 'organizador', NOW()
            )
        ");
        $insertUser->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => $hash,
            'telefone' => $solicitacao['responsavel_telefone']
        ]);
        $usuarioId = $pdo->lastInsertId();
    }

    $stmtPapel = $pdo->prepare("SELECT id FROM papeis WHERE nome = 'organizador' LIMIT 1");
    $stmtPapel->execute();
    $papel = $stmtPapel->fetch(PDO::FETCH_ASSOC);

    if ($papel) {
        $stmtCheck = $pdo->prepare("SELECT 1 FROM usuario_papeis WHERE usuario_id = :usuario AND papel_id = :papel");
        $stmtCheck->execute(['usuario' => $usuarioId, 'papel' => $papel['id']]);
        if (!$stmtCheck->fetch()) {
            $stmtInsertPapel = $pdo->prepare("INSERT INTO usuario_papeis (usuario_id, papel_id) VALUES (:usuario, :papel)");
            $stmtInsertPapel->execute(['usuario' => $usuarioId, 'papel' => $papel['id']]);
        }
    }

    // Garantir que exista registro em organizadores para este usuário
    $stmtOrg = $pdo->prepare("SELECT id FROM organizadores WHERE usuario_id = :usuario_id LIMIT 1");
    $stmtOrg->execute(['usuario_id' => $usuarioId]);
    $organizadorId = $stmtOrg->fetchColumn();

    if (!$organizadorId) {
        $stmtInsertOrg = $pdo->prepare("
            INSERT INTO organizadores (
                usuario_id, empresa, regiao, modalidade_esportiva, quantidade_eventos, regulamento
            ) VALUES (
                :usuario_id, :empresa, :regiao, :modalidade, :quantidade_eventos, :regulamento
            )
        ");
        $stmtInsertOrg->execute([
            'usuario_id' => $usuarioId,
            'empresa' => $solicitacao['empresa'],
            'regiao' => $solicitacao['regiao'],
            'modalidade' => $solicitacao['modalidade_esportiva'],
            'quantidade_eventos' => $solicitacao['quantidade_eventos'],
            'regulamento' => $solicitacao['regulamento_status']
        ]);
        $organizadorId = $pdo->lastInsertId();
    }

    // Criar evento mínimo em eventos, vinculado ao organizador
    $dataPrevista = $solicitacao['data_prevista'] ?: null;
    $limiteVagas = $solicitacao['estimativa_participantes'] !== null
        ? (int) $solicitacao['estimativa_participantes']
        : null;
    $localSimplificado = trim(($solicitacao['cidade_evento'] ?? '') . ' - ' . ($solicitacao['uf_evento'] ?? ''));

    $stmtEvento = $pdo->prepare("
        INSERT INTO eventos (
            nome,
            descricao,
            data_inicio,
            data_fim,
            categoria,
            genero,
            local,
            cidade,
            estado,
            pais,
            regulamento,
            status,
            organizador_id,
            limite_vagas,
            data_realizacao
        ) VALUES (
            :nome,
            :descricao,
            :data_inicio,
            :data_fim,
            :categoria,
            :genero,
            :local,
            :cidade,
            :estado,
            'Brasil',
            :regulamento,
            :status,
            :organizador_id,
            :limite_vagas,
            :data_realizacao
        )
    ");

    $stmtEvento->execute([
        'nome' => $solicitacao['nome_evento'],
        'descricao' => $solicitacao['descricao_evento'],
        'data_inicio' => $dataPrevista,
        'data_fim' => null,
        'categoria' => $solicitacao['modalidade_esportiva'],
        'genero' => 'Misto',
        'local' => $localSimplificado !== '-' ? $localSimplificado : null,
        'cidade' => $solicitacao['cidade_evento'],
        'estado' => $solicitacao['uf_evento'],
        'regulamento' => $solicitacao['regulamento_status'],
        'status' => 'rascunho',
        'organizador_id' => (int) $organizadorId,
        'limite_vagas' => $limiteVagas,
        'data_realizacao' => $dataPrevista,
    ]);

    $stmtUpdate = $pdo->prepare("UPDATE solicitacoes_evento SET status = 'aprovado', atualizado_em = NOW() WHERE id = :id");
    $stmtUpdate->execute(['id' => $id]);

    $pdo->commit();

    $baseUrl = envValue('APP_URL', '');
    if ($baseUrl === '' && isset($_SERVER['HTTP_HOST'])) {
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    }
    $loginUrl = rtrim($baseUrl, '/') . '/frontend/paginas/auth/login.php';

    $html = '
        <p>Olá ' . htmlspecialchars($nome) . ',</p>
        <p>Você foi designado como organizador do evento <strong>' . htmlspecialchars($solicitacao['nome_evento']) . '</strong> no MovAmazon.</p>
        <p>Use as credenciais abaixo para acessar o painel e completar o cadastro do evento:</p>
        <ul>
            <li><strong>Login:</strong> ' . htmlspecialchars($email) . '</li>
            <li><strong>Senha temporária:</strong> ' . $senhaTemporaria . '</li>
        </ul>
        <p>Faça login em: <a href="' . $loginUrl . '">' . $loginUrl . '</a> e altere a senha assim que possível.</p>
        <p>Equipe MovAmazon.</p>
    ';

    $emailOk = sendEmail($email, 'Acesso como organizador MovAmazon', $html);
    if (!$emailOk) {
        $mask = substr($senhaTemporaria, 0, 2) . str_repeat('*', max(0, strlen($senhaTemporaria) - 4)) . substr($senhaTemporaria, -2);
        error_log('[ADMIN_SOLICITACOES_APPROVE_EMAIL_FAIL] id=' . $id . ' email=' . $email . ' temp_password=' . $mask);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Organizador criado e notificado.',
        'temp_password' => $senhaTemporaria
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[ADMIN_SOLICITACOES_APPROVE] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao aprovar solicitação']);
}

