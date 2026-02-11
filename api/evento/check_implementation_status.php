<?php
/**
 * API para verificar status de implementação de funcionalidades do evento
 */
header('Content-Type: application/json');
require_once '../db.php';
require_once __DIR__ . '/../helpers/organizador_context.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

try {
    $evento_id = (int)$_GET['id'];
    
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    // Verificar se o evento existe e pertence ao organizador (compatibilidade com legado)
    $stmt = $pdo->prepare("SELECT id, exibir_retirada_kit FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado']);
        exit();
    }
    
    $exibir_retirada_kit = (int)$evento['exibir_retirada_kit'];
    
    // Definir features com suas propriedades
    $features_config = [
        'modalidades' => [
            'nome' => 'Modalidades',
            'obrigatorio' => true,
            'icon' => 'fas fa-running',
            'link' => '?page=modalidades&evento_id=' . $evento_id
        ],
        'lotes' => [
            'nome' => 'Lotes de Inscrição',
            'obrigatorio' => true,
            'icon' => 'fas fa-tags',
            'link' => '?page=lotes-inscricao&evento_id=' . $evento_id
        ],
        'programacao' => [
            'nome' => 'Programação',
            'obrigatorio' => true,
            'icon' => 'fas fa-calendar-alt',
            'link' => '?page=programacao&evento_id=' . $evento_id
        ],
        'kits' => [
            'nome' => 'Kits',
            'obrigatorio' => false,
            'icon' => 'fas fa-box',
            'link' => '?page=kits-evento&evento_id=' . $evento_id
        ],
        'produtos_extras' => [
            'nome' => 'Produtos Extras',
            'obrigatorio' => false,
            'icon' => 'fas fa-gift',
            'link' => '?page=produtos-extras&evento_id=' . $evento_id
        ],
        'tamanhos' => [
            'nome' => 'Tamanhos (Camisas)',
            'obrigatorio' => $exibir_retirada_kit == 1,
            'icon' => 'fas fa-tshirt',
            'link' => '?page=camisas&evento_id=' . $evento_id
        ],
        'questionario' => [
            'nome' => 'Questionário',
            'obrigatorio' => false,
            'icon' => 'fas fa-question-circle',
            'link' => '?page=questionario&evento_id=' . $evento_id
        ],
        'retirada_kits' => [
            'nome' => 'Retirada de Kits',
            'obrigatorio' => false,
            'icon' => 'fas fa-hand-holding',
            'link' => '?page=retirada-kits&evento_id=' . $evento_id
        ],
        'cupons' => [
            'nome' => 'Cupons',
            'obrigatorio' => false,
            'icon' => 'fas fa-ticket-alt',
            'link' => '?page=cupons-remessa&evento_id=' . $evento_id
        ]
    ];
    
    // Verificar cada feature
    $features = [];
    $obrigatorios_total = 0;
    $obrigatorios_concluidos = 0;
    $total_concluidos = 0;
    
    foreach ($features_config as $key => $config) {
        $count = 0;
        
        switch ($key) {
            case 'modalidades':
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM modalidades WHERE evento_id = ? AND ativo = 1");
                $stmt->execute([$evento_id]);
                $count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                break;
            case 'lotes':
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM lotes_inscricao WHERE evento_id = ? AND ativo = 1");
                $stmt->execute([$evento_id]);
                $count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                break;
            case 'kits':
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM kits_eventos WHERE evento_id = ? AND ativo = 1");
                $stmt->execute([$evento_id]);
                $count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                break;
            case 'produtos_extras':
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM produtos_extras WHERE evento_id = ? AND ativo = 1");
                $stmt->execute([$evento_id]);
                $count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                break;
            case 'tamanhos':
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM camisas WHERE evento_id = ? AND ativo = 1");
                $stmt->execute([$evento_id]);
                $count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                break;
            case 'programacao':
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM programacao_evento WHERE evento_id = ? AND ativo = 1");
                $stmt->execute([$evento_id]);
                $count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                break;
            case 'questionario':
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM questionario_evento WHERE evento_id = ? AND ativo = 1");
                $stmt->execute([$evento_id]);
                $count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                break;
            case 'retirada_kits':
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM retirada_kits_evento WHERE evento_id = ? AND ativo = 1");
                $stmt->execute([$evento_id]);
                $count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                break;
            case 'cupons':
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cupons_remessa WHERE evento_id = ? AND status = 'ativo'");
                $stmt->execute([$evento_id]);
                $count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                break;
        }
        
        $configurado = $count > 0;
        $features[] = [
            'id' => $key,
            'nome' => $config['nome'],
            'configurado' => $configurado,
            'obrigatorio' => $config['obrigatorio'],
            'count' => $count,
            'icon' => $config['icon'],
            'link' => $config['link']
        ];
        
        if ($config['obrigatorio']) {
            $obrigatorios_total++;
            if ($configurado) {
                $obrigatorios_concluidos++;
            }
        }
        
        if ($configurado) {
            $total_concluidos++;
        }
    }
    
    // Verificar se evento pode receber inscrições (configurações obrigatórias)
    require_once __DIR__ . '/validate_event_ready.php';
    $validacao = eventoPodeReceberInscricoes($pdo, $evento_id);
    
    $pode_ativar = $validacao['pode_receber'];
    $pendencias_obrigatorias = $validacao['pendencias'];
    
    $response = [
        'success' => true,
        'status' => [
            'evento_id' => $evento_id,
            'features' => $features,
            'progresso' => [
                'total' => count($features),
                'concluidos' => $total_concluidos,
                'obrigatorios_total' => $obrigatorios_total,
                'obrigatorios_concluidos' => $obrigatorios_concluidos,
                'percentual' => count($features) > 0 ? round(($total_concluidos / count($features)) * 100) : 0
            ],
            'pode_ativar' => $pode_ativar,
            'pendencias_obrigatorias' => $pendencias_obrigatorias,
            'detalhes_validacao' => $validacao['detalhes']
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Erro ao verificar status de implementação: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
