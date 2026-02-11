<?php
header('Content-Type: application/json');
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';
require_once __DIR__ . '/../../helpers/file_utils.php';

error_log('INICIO aplicar-template.php');

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    error_log('Acesso negado: não autenticado ou papel incorreto');
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('Método não permitido: ' . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit();
}
 
try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    // Receber dados do JSON
    $input = json_decode(file_get_contents('php://input'), true);
    error_log('Payload recebido: ' . json_encode($input));
    $template_id = (int)($input['template_id'] ?? 0);
    $evento_id = (int)($input['evento_id'] ?? 0);
    $modalidades = $input['modalidades'] ?? [];
    
    if (!$template_id || !$evento_id || empty($modalidades)) {
        error_log('Dados obrigatórios ausentes');
        echo json_encode(['success' => false, 'error' => 'Template, evento e modalidades são obrigatórios']);
        exit();
    }
    
    // Verificar se o template existe
    $stmt = $pdo->prepare("SELECT id, nome, preco_base, foto_kit FROM kit_templates WHERE id = ?");
    $stmt->execute([$template_id]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log('Template encontrado: ' . json_encode($template));
    
    if (!$template) {
        error_log('Template não encontrado');
        echo json_encode(['success' => false, 'error' => 'Template não encontrado']);
        exit();
    }
    
    // Verificar se o evento pertence ao organizador (novo + legado)
    $stmt = $pdo->prepare("SELECT id, nome FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log('Evento encontrado: ' . json_encode($evento));
    
    if (!$evento) {
        error_log('Evento não encontrado ou não autorizado');
        echo json_encode(['success' => false, 'error' => 'Evento não encontrado ou não autorizado']);
        exit();
    }
    
    // Verificar se as modalidades existem no evento
    $placeholders = str_repeat('?,', count($modalidades) - 1) . '?';
    $sql = "SELECT id, nome FROM modalidades WHERE id IN ($placeholders) AND evento_id = ?";
    error_log('SQL modalidades: ' . $sql . ' | Params: ' . json_encode(array_merge($modalidades, [$evento_id])));
    $params = array_merge($modalidades, [$evento_id]);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $modalidades_validas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log('Modalidades válidas: ' . json_encode($modalidades_validas));
    
    if (count($modalidades_validas) !== count($modalidades)) {
        error_log('Modalidades não conferem');
        echo json_encode(['success' => false, 'error' => 'Uma ou mais modalidades não foram encontradas']);
        exit();
    }
    
    // Buscar produtos do template
    $stmt = $pdo->prepare("
        SELECT ktp.produto_id, ktp.quantidade, ktp.ordem, p.nome as produto_nome
        FROM kit_template_produtos ktp
        INNER JOIN produtos p ON ktp.produto_id = p.id
        WHERE ktp.kit_template_id = ? 
        ORDER BY ktp.ordem ASC
    ");
    $stmt->execute([$template_id]);
    $produtos_template = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log('Produtos do template: ' . json_encode($produtos_template));
    
    if (empty($produtos_template)) {
        error_log('Template não possui produtos');
        echo json_encode(['success' => false, 'error' => 'Template não possui produtos']);
        exit();
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    error_log('Transação iniciada');
    
    $kits_criados = 0;
    
    foreach ($modalidades_validas as $modalidade) {
        // Verificar se já existe um kit para esta modalidade
        $stmt = $pdo->prepare("SELECT id FROM kits_eventos WHERE evento_id = ? AND modalidade_evento_id = ?");
        $stmt->execute([$evento_id, $modalidade['id']]);
        $kit_existente = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log('Kit existente para modalidade ' . $modalidade['id'] . ': ' . json_encode($kit_existente));
        
        if ($kit_existente) {
            continue; // Pular modalidades que já têm kit
        }
        
        // Criar kit
        $nome_kit = $template['nome'] . ' - ' . $modalidade['nome'];
        $sql = "INSERT INTO kits_eventos (nome, descricao, evento_id, modalidade_evento_id, kit_template_id, valor, foto_kit, disponivel_venda, preco_calculado, ativo, data_criacao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
        error_log('SQL inserir kit: ' . $sql . ' | Params: ' . json_encode([
            $nome_kit,
            $template['nome'] . ' aplicado em ' . $modalidade['nome'],
            $evento_id,
            $modalidade['id'],
            $template_id,
            $template['preco_base'],
            $template['foto_kit'],
            1, // disponivel_venda
            $template['preco_base'] // preco_calculado
        ]));
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nome_kit,
            $template['nome'] . ' aplicado em ' . $modalidade['nome'],
            $evento_id,
            $modalidade['id'],
            $template_id,
            $template['preco_base'],
            $template['foto_kit'],
            1, // disponivel_venda
            $template['preco_base'] // preco_calculado
        ]);
        
        $kit_id = $pdo->lastInsertId();
        if (!empty($template['foto_kit'])) {
            $upload_dir = '../../../frontend/assets/img/kits/';
            $source_path = $upload_dir . $template['foto_kit'];
            $extension = pathinfo($template['foto_kit'], PATHINFO_EXTENSION) ?: 'png';
            $new_filename = gerarNomeKit('evento', $kit_id, $evento_id, $extension);
            $target_path = $upload_dir . $new_filename;

            if (is_file($source_path)) {
                copy($source_path, $target_path);
                $stmtFoto = $pdo->prepare("UPDATE kits_eventos SET foto_kit = ? WHERE id = ?");
                $stmtFoto->execute([$new_filename, $kit_id]);
            } else {
                error_log("Foto base não encontrada: {$source_path}");
            }
        }
        error_log('Kit criado ID: ' . $kit_id);
        
        // Inserir também na tabela de relacionamento N:N para consistência
        $stmt_rel = $pdo->prepare("INSERT INTO kit_modalidade_evento (kit_id, modalidade_evento_id) VALUES (?, ?)");
        $stmt_rel->execute([$kit_id, $modalidade['id']]);
        error_log('Relacionamento N:N criado para kit ' . $kit_id . ' e modalidade ' . $modalidade['id']);
        
        // Adicionar produtos ao kit
        $sql = "INSERT INTO kit_produtos (kit_id, produto_id, quantidade, ordem, ativo) 
                VALUES (?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        foreach ($produtos_template as $produto) {
            error_log('SQL inserir produto kit: ' . $sql . ' | Params: ' . json_encode([
                $kit_id,
                $produto['produto_id'],
                $produto['quantidade'],
                $produto['ordem']
            ]));
            $stmt->execute([
                $kit_id,
                $produto['produto_id'],
                $produto['quantidade'],
                $produto['ordem']
            ]);
        }
        
        $kits_criados++;
    }
    
    // Commit da transação
    $pdo->commit();
    error_log('Transação commitada com sucesso');
    
    echo json_encode([
        'success' => true,
        'message' => "Template aplicado com sucesso! {$kits_criados} kit(s) criado(s).",
        'data' => [
            'template_nome' => $template['nome'],
            'evento_nome' => $evento['nome'],
            'modalidades_aplicadas' => count($modalidades_validas),
            'kits_criados' => $kits_criados
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    error_log('Exception capturada: ' . $e->getMessage());
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
        error_log('Rollback executado');
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?> 
