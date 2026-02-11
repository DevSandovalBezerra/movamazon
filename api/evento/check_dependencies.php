<?php
header('Content-Type: application/json');
require_once '../db.php';
require_once '../middleware/auth.php';

// Verificar autenticação usando middleware centralizado
verificarAutenticacao('organizador');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    // Validar parâmetros
    if (!isset($_GET['evento_id']) || empty($_GET['evento_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
        exit();
    }
    
    $evento_id = (int)$_GET['evento_id'];
    $organizador_id = $_SESSION['user_id'];
    
    // Verificar se o evento existe e pertence ao organizador
    $stmt = $pdo->prepare("SELECT id, nome, status FROM eventos WHERE id = ? AND organizador_id = ? AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou não pertence a você']);
        exit();
    }
    
    $dependencias = [];
    $pode_excluir = true;
    $motivo_bloqueio = '';
    
    // 1. VERIFICAR INSCRIÇÕES (CRÍTICO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM inscricoes WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $inscricoes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($inscricoes['total'] > 0) {
        $dependencias[] = [
            'tabela' => 'inscricoes',
            'total' => (int)$inscricoes['total'],
            'nivel' => 'CRÍTICO',
            'descricao' => 'Inscrições de participantes',
            'bloqueia_exclusao' => true
        ];
        $pode_excluir = false;
        $motivo_bloqueio = 'Evento possui inscrições de participantes';
    }
    
    // 2. VERIFICAR REPASSES FINANCEIROS (CRÍTICO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM repasse_organizadores WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $repasses = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($repasses['total'] > 0) {
        $dependencias[] = [
            'tabela' => 'repasse_organizadores',
            'total' => (int)$repasses['total'],
            'nivel' => 'CRÍTICO',
            'descricao' => 'Dados financeiros de repasse',
            'bloqueia_exclusao' => true
        ];
        $pode_excluir = false;
        $motivo_bloqueio = 'Evento possui dados financeiros de repasse';
    }
    
    // 3. VERIFICAR KITS DO EVENTO (ALTO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM kits_eventos WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $kits = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($kits['total'] > 0) {
        $dependencias[] = [
            'tabela' => 'kits_eventos',
            'total' => (int)$kits['total'],
            'nivel' => 'ALTO',
            'descricao' => 'Kits associados ao evento',
            'bloqueia_exclusao' => false
        ];
    }
    
    // 4. VERIFICAR LOTES DE INSCRIÇÃO (ALTO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM lotes_inscricao WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $lotes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lotes['total'] > 0) {
        $dependencias[] = [
            'tabela' => 'lotes_inscricao',
            'total' => (int)$lotes['total'],
            'nivel' => 'ALTO',
            'descricao' => 'Lotes de inscrição configurados',
            'bloqueia_exclusao' => false
        ];
    }
    
    // 5. VERIFICAR PRODUTOS EXTRAS (ALTO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM produtos_extras WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $produtos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($produtos['total'] > 0) {
        $dependencias[] = [
            'tabela' => 'produtos_extras',
            'total' => (int)$produtos['total'],
            'nivel' => 'ALTO',
            'descricao' => 'Produtos extras configurados',
            'bloqueia_exclusao' => false
        ];
    }
    
    // 6. VERIFICAR CUPONS DE DESCONTO (MÉDIO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cupons_remessa WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $cupons = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cupons['total'] > 0) {
        $dependencias[] = [
            'tabela' => 'cupons_remessa',
            'total' => (int)$cupons['total'],
            'nivel' => 'MÉDIO',
            'descricao' => 'Cupons de desconto configurados',
            'bloqueia_exclusao' => false
        ];
    }
    
    // 7. VERIFICAR FORMAS DE PAGAMENTO (MÉDIO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM formas_pagamento_evento WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $pagamentos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pagamentos['total'] > 0) {
        $dependencias[] = [
            'tabela' => 'formas_pagamento_evento',
            'total' => (int)$pagamentos['total'],
            'nivel' => 'MÉDIO',
            'descricao' => 'Formas de pagamento configuradas',
            'bloqueia_exclusao' => false
        ];
    }
    
    // 8. VERIFICAR PROGRAMAÇÃO DO EVENTO (MÉDIO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM programacao_evento WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $programacao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($programacao['total'] > 0) {
        $dependencias[] = [
            'tabela' => 'programacao_evento',
            'total' => (int)$programacao['total'],
            'nivel' => 'MÉDIO',
            'descricao' => 'Programação do evento configurada',
            'bloqueia_exclusao' => false
        ];
    }
    
    // 9. VERIFICAR QUESTIONÁRIO DO EVENTO (MÉDIO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM questionario_evento WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $questionario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($questionario['total'] > 0) {
        $dependencias[] = [
            'tabela' => 'questionario_evento',
            'total' => (int)$questionario['total'],
            'nivel' => 'MÉDIO',
            'descricao' => 'Questionário do evento configurado',
            'bloqueia_exclusao' => false
        ];
    }
    
    // 10. VERIFICAR TERMOS DO EVENTO (MÉDIO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM termos_eventos WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $termos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($termos['total'] > 0) {
        $dependencias[] = [
            'tabela' => 'termos_eventos',
            'total' => (int)$termos['total'],
            'nivel' => 'MÉDIO',
            'descricao' => 'Termos e condições configurados',
            'bloqueia_exclusao' => false
        ];
    }
    
    // 11. VERIFICAR RETIRADA DE KITS (MÉDIO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM retirada_kits_evento WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $retirada = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($retirada['total'] > 0) {
        $dependencias[] = [
            'tabela' => 'retirada_kits_evento',
            'total' => (int)$retirada['total'],
            'nivel' => 'MÉDIO',
            'descricao' => 'Locais de retirada de kits configurados',
            'bloqueia_exclusao' => false
        ];
    }
    
    // 12. VERIFICAR PERÍODOS DE INSCRIÇÃO (MÉDIO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM periodos_inscricao WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $periodos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($periodos['total'] > 0) {
        $dependencias[] = [
            'tabela' => 'periodos_inscricao',
            'total' => (int)$periodos['total'],
            'nivel' => 'MÉDIO',
            'descricao' => 'Períodos de inscrição configurados',
            'bloqueia_exclusao' => false
        ];
    }
    
    // Log da verificação
    // logSeguranca('Verificou dependências do evento', "Evento ID: {$evento_id}, Pode excluir: " . ($pode_excluir ? 'Sim' : 'Não'));
    error_log('🔍 API check_dependencies.php - Evento ID: ' . $evento_id . ', Pode excluir: ' . ($pode_excluir ? 'Sim' : 'Não'));
    
    echo json_encode([
        'success' => true,
        'data' => [
            'evento' => $evento,
            'pode_excluir' => $pode_excluir,
            'motivo_bloqueio' => $motivo_bloqueio,
            'dependencias' => $dependencias,
            'total_dependencias' => count($dependencias)
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao verificar dependências do evento: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
} catch (Exception $e) {
    error_log("Erro inesperado ao verificar dependências: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro inesperado']);
}
?>
