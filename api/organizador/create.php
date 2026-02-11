<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/email_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    $data = $_POST;
}

$trim = function ($key) use ($data) {
    return isset($data[$key]) ? trim((string)$data[$key]) : '';
};

$required = [
    'nome',
    'email',
    'telefone',
    'empresa',
    'regiao',
    'modalidade',
    'nome_evento',
    'cidade_evento',
    'uf_evento',
    'quantidade_eventos',
    'regulamento',
    'aceite_politica'
];

$missing = [];
foreach ($required as $field) {
    if ($field === 'aceite_politica' && empty($data[$field])) {
        $missing[] = $field;
        continue;
    }
    if ($field !== 'aceite_politica' && $trim($field) === '') {
        $missing[] = $field;
    }
}

if ($missing) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Campos obrigatórios não preenchidos',
        'fields' => $missing
    ]);
    exit;
}

if (!filter_var($trim('email'), FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email inválido']);
    exit;
}

$ddi = $trim('telefone_ddi') ?: '+55';
$telefoneBruto = $trim('telefone');
$telefone = trim($ddi . ' ' . $telefoneBruto);

$dataPrevista = $trim('data_prevista');
if ($dataPrevista !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataPrevista)) {
    $dataPrevista = null;
}

$estimativaParticipantes = $trim('estimativa_participantes');
$estimativaParticipantes = $estimativaParticipantes !== '' ? (int) $estimativaParticipantes : null;

// Upload opcional do regulamento (PDF/DOC)
$regulamentoArquivo = null;
if (!empty($_FILES['arquivo_regulamento']) && $_FILES['arquivo_regulamento']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = dirname(__DIR__) . '/uploads/regulamentos';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    $originalName = $_FILES['arquivo_regulamento']['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExt = ['pdf', 'doc', 'docx'];

    if (in_array($ext, $allowedExt, true)) {
        $baseName = 'regulamento_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $baseName;

        if (move_uploaded_file($_FILES['arquivo_regulamento']['tmp_name'], $destPath)) {
            // Armazena apenas o nome do arquivo; caminho completo é resolvido na aplicação
            $regulamentoArquivo = $baseName;
        }
    }
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO solicitacoes_evento (
            responsavel_nome,
            responsavel_email,
            responsavel_telefone,
            responsavel_documento,
            responsavel_rg,
            responsavel_cargo,
            empresa,
            regiao,
            cidade_evento,
            uf_evento,
            modalidade_esportiva,
            quantidade_eventos,
            nome_evento,
            data_prevista,
            estimativa_participantes,
            regulamento_status,
            link_regulamento,
            possui_autorizacao,
            link_autorizacao,
            necessidades,
            descricao_evento,
            indicacao,
            preferencia_contato,
            documentos_link
        ) VALUES (
            :responsavel_nome,
            :responsavel_email,
            :responsavel_telefone,
            :responsavel_documento,
            :responsavel_rg,
            :responsavel_cargo,
            :empresa,
            :regiao,
            :cidade_evento,
            :uf_evento,
            :modalidade_esportiva,
            :quantidade_eventos,
            :nome_evento,
            :data_prevista,
            :estimativa_participantes,
            :regulamento_status,
            :link_regulamento,
            :possui_autorizacao,
            :link_autorizacao,
            :necessidades,
            :descricao_evento,
            :indicacao,
            :preferencia_contato,
            :documentos_link
        )
    ");

    $stmt->execute([
        'responsavel_nome' => $trim('nome'),
        'responsavel_email' => $trim('email'),
        'responsavel_telefone' => $telefone,
        'responsavel_documento' => $trim('documento'),
        'responsavel_rg' => $trim('rg'),
        'responsavel_cargo' => $trim('cargo'),
        'empresa' => $trim('empresa'),
        'regiao' => strtoupper($trim('regiao')),
        'cidade_evento' => $trim('cidade_evento'),
        'uf_evento' => strtoupper($trim('uf_evento')),
        'modalidade_esportiva' => $trim('modalidade'),
        'quantidade_eventos' => $trim('quantidade_eventos'),
        'nome_evento' => $trim('nome_evento'),
        'data_prevista' => $dataPrevista ?: null,
        'estimativa_participantes' => $estimativaParticipantes,
        'regulamento_status' => $trim('regulamento'),
        'link_regulamento' => $regulamentoArquivo ?: null,
        'possui_autorizacao' => $trim('possui_autorizacao') ?: null,
        'link_autorizacao' => $trim('link_autorizacao') ?: null,
        'necessidades' => $trim('necessidades'),
        'descricao_evento' => $trim('descricao_evento'),
        'indicacao' => $trim('indicacao'),
        'preferencia_contato' => $trim('preferencia_contato'),
        'documentos_link' => $trim('documentos_link')
    ]);

    $solicitacaoId = $pdo->lastInsertId();

    // Mantém compatibilidade com leads antigos
    $stmtLead = $pdo->prepare("
        INSERT INTO leads_organizadores (
            nome_completo,
            email,
            telefone,
            empresa,
            regiao,
            modalidade_esportiva,
            quantidade_eventos,
            nome_evento,
            regulamento,
            indicacao,
            data_criacao
        ) VALUES (
            :nome,
            :email,
            :telefone,
            :empresa,
            :regiao,
            :modalidade,
            :quantidade_eventos,
            :nome_evento,
            :regulamento,
            :indicacao,
            NOW()
        )
    ");

    $stmtLead->execute([
        'nome' => $trim('nome'),
        'email' => $trim('email'),
        'telefone' => $telefone,
        'empresa' => $trim('empresa'),
        'regiao' => strtoupper($trim('regiao')),
        'modalidade' => $trim('modalidade'),
        'quantidade_eventos' => $trim('quantidade_eventos'),
        'nome_evento' => $trim('nome_evento'),
        'regulamento' => $trim('regulamento'),
        'indicacao' => $trim('indicacao') ?: null
    ]);

    $pdo->commit();

    $adminEmail = envValue('ADMIN_EMAIL', EMAIL_FROM_ADDRESS);
    $responsavelNome = htmlspecialchars($trim('nome'));

    $resumoHtml = '
        <h2>Nova solicitação de evento</h2>
        <p>ID da solicitação: <strong>#' . $solicitacaoId . '</strong></p>
        <table border="0" cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
            <tr><td><strong>Responsável</strong></td><td>' . $responsavelNome . '</td></tr>
            <tr><td><strong>Email</strong></td><td>' . htmlspecialchars($trim('email')) . '</td></tr>
            <tr><td><strong>Telefone</strong></td><td>' . htmlspecialchars($telefone) . '</td></tr>
            <tr><td><strong>Empresa</strong></td><td>' . htmlspecialchars($trim('empresa')) . '</td></tr>
            <tr><td><strong>Evento</strong></td><td>' . htmlspecialchars($trim('nome_evento')) . '</td></tr>
            <tr><td><strong>Cidade/UF</strong></td><td>' . htmlspecialchars($trim('cidade_evento')) . '/' . htmlspecialchars(strtoupper($trim('uf_evento'))) . '</td></tr>
            <tr><td><strong>Data prevista</strong></td><td>' . ($dataPrevista ?: 'Não informado') . '</td></tr>
            <tr><td><strong>Estimativa participantes</strong></td><td>' . ($estimativaParticipantes ?: 'Não informado') . '</td></tr>
        </table>
        <p>Veja a solicitação completa no painel assim que o módulo estiver disponível.</p>
    ';

    $checklist = "Checklist para cadastro do evento\n\n" .
        "- Documento oficial do responsável (RG e CPF/CNPJ)\n" .
        "- Regulamento atualizado e assinado\n" .
        "- Autorização da prefeitura/órgãos competentes\n" .
        "- Mapa do percurso com pontos de apoio\n" .
        "- Plano de segurança e atendimento médico\n" .
        "- Informações bancárias para repasses\n" .
        "- Logomarca em alta resolução\n";

    sendEmail(
        $adminEmail,
        'MovAmazonas - Nova solicitação #' . $solicitacaoId,
        $resumoHtml,
        [
            [
                'content' => $checklist,
                'name' => 'Checklist_Solicitacao_' . $solicitacaoId . '.txt',
                'type' => 'text/plain'
            ]
        ]
    );

    $solicitanteHtml = '
        <p>Olá ' . $responsavelNome . ',</p>
        <p>Recebemos sua solicitação para cadastrar o evento <strong>' . htmlspecialchars($trim('nome_evento')) . '</strong>.</p>
        <p>Nos próximos dias nossa equipe entrará em contato para prosseguir com a criação do evento. Prepare os documentos do checklist em anexo.</p>
        <p>Atenciosamente,<br>Equipe MovAmazonas</p>
    ';

    sendEmail(
        $trim('email'),
        'Recebemos sua solicitação - MovAmazonas',
        $solicitanteHtml,
        [
            [
                'content' => $checklist,
                'name' => 'Checklist_Cadastro_Evento.txt',
                'type' => 'text/plain'
            ]
        ]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Solicitação enviada! Você receberá um e-mail com o checklist necessário.',
        'id' => $solicitacaoId
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[ORGANIZADOR_SOLICITACAO] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao salvar dados.']);
}
