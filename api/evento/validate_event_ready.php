<?php
/**
 * Função centralizada para validar se um evento pode receber inscrições
 * 
 * Configurações OBRIGATÓRIAS (mínimas):
 * 1. Modalidades - Sem isso não há como escolher a prova
 * 2. Lotes de Inscrição - Define preço e período de inscrição
 * 3. Programação - Data, hora e local do evento
 * 
 * Configurações CONDICIONAIS:
 * 4. Tamanhos (Camisas) - Obrigatório apenas se evento oferecer camisa
 * 
 * @param PDO $pdo Conexão com banco de dados
 * @param int $evento_id ID do evento
 * @return array ['pode_receber' => bool, 'pendencias' => array, 'detalhes' => array]
 */
function eventoPodeReceberInscricoes($pdo, $evento_id) {
    $pendencias = [];
    $detalhes = [];
    
    // 1. Verificar Modalidades (OBRIGATÓRIO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM modalidades WHERE evento_id = ? AND ativo = 1");
    $stmt->execute([$evento_id]);
    $count_modalidades = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $detalhes['modalidades'] = $count_modalidades;
    if ($count_modalidades <= 0) {
        $pendencias[] = 'Modalidades';
    }
    
    // 2. Verificar Lotes de Inscrição (OBRIGATÓRIO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM lotes_inscricao WHERE evento_id = ? AND ativo = 1");
    $stmt->execute([$evento_id]);
    $count_lotes = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $detalhes['lotes'] = $count_lotes;
    if ($count_lotes <= 0) {
        $pendencias[] = 'Lotes de Inscrição';
    }
    
    // 3. Verificar Programação (OBRIGATÓRIO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM programacao_evento WHERE evento_id = ? AND ativo = 1");
    $stmt->execute([$evento_id]);
    $count_programacao = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $detalhes['programacao'] = $count_programacao;
    if ($count_programacao <= 0) {
        $pendencias[] = 'Programação';
    }
    
    // 4. Verificar se evento oferece camisa (para validar tamanhos condicionalmente)
    $stmt = $pdo->prepare("SELECT exibir_retirada_kit FROM eventos WHERE id = ?");
    $stmt->execute([$evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    $tem_kit = $evento && $evento['exibir_retirada_kit'] == 1;
    
    // 5. Verificar Tamanhos (CONDICIONAL - só se evento tem kit/camisa)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM camisas WHERE evento_id = ? AND ativo = 1");
    $stmt->execute([$evento_id]);
    $count_tamanhos = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $detalhes['tamanhos'] = $count_tamanhos;
    $detalhes['tem_kit'] = $tem_kit;
    
    // Se evento oferece kit mas não tem tamanhos configurados, é pendência
    if ($tem_kit && $count_tamanhos <= 0) {
        $pendencias[] = 'Tamanhos de Camisa';
    }
    
    return [
        'pode_receber' => empty($pendencias),
        'pendencias' => $pendencias,
        'detalhes' => $detalhes
    ];
}

