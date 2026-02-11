<?php
header('Content-Type: application/json');

require_once '../db.php';

// Função para encontrar imagem do evento
function getEventoImagem($eventoId, $imagemNome = null) {
    if (!$imagemNome) {
        return null;
    }
    
    $uploadDir = __DIR__ . '/../../frontend/assets/img/eventos/';
    $extensoes = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    
    // Se já tem extensão no nome, verifica se existe
    $ext = pathinfo($imagemNome, PATHINFO_EXTENSION);
    if ($ext && file_exists($uploadDir . $imagemNome)) {
        return $imagemNome;
    }
    
    // Se não tem extensão ou arquivo não existe, tenta diferentes extensões
    $nomeBase = pathinfo($imagemNome, PATHINFO_FILENAME);
    if (!$nomeBase) {
        $nomeBase = 'evento_' . $eventoId;
    }
    
    foreach ($extensoes as $ext) {
        $arquivo = $nomeBase . '.' . $ext;
        if (file_exists($uploadDir . $arquivo)) {
            return $arquivo;
        }
    }
    
    return null; // Nenhuma imagem encontrada
}

try {
    // Verificar se foi solicitado um evento específico
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $evento_id = (int)$_GET['id'];
        
        // Buscar evento com todos os campos da tabela eventos
        $sql = "SELECT e.*, u.nome_completo as organizador
                FROM eventos e
                LEFT JOIN usuarios u ON e.organizador_id = u.id
                WHERE e.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evento_id]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$evento) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Evento não encontrado']);
            exit;
        }
        
        // imagem: retornar valor do banco (nome do arquivo); frontend monta URL e trata 404
        // (não sobrescrever com getEventoImagem para evitar null quando path do servidor difere)
        
        // Garantir que campos booleanos sejam tratados corretamente
        $evento['exibir_retirada_kit'] = (bool)$evento['exibir_retirada_kit'];
        
        echo json_encode(['success' => true, 'evento' => $evento]);
        exit;
    }
    
    // Listar eventos (comportamento original)
    $sql = "SELECT e.id, e.nome, e.data_inicio, e.local, e.limite_vagas, e.status, e.imagem, u.empresa as organizadora
            FROM eventos e
            LEFT JOIN organizadores u ON e.organizador_id = u.usuario_id
            ORDER BY e.data_inicio DESC LIMIT 10";
    $stmt = $pdo->query($sql);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($eventos as &$evento) {
        // imagem: manter valor do banco (nome do arquivo); frontend monta URL
        
        // Valor mínimo da modalidade (apenas modalidades principais, sem KIT)
        $modalidade = $pdo->prepare("SELECT MIN(valor) as valor_min FROM modalidades_evento me JOIN modalidades m ON me.modalidade_id = m.id WHERE me.evento_id = ? AND m.nome_modalidade NOT LIKE '%KIT%'");
        $modalidade->execute([$evento['id']]);
        $row = $modalidade->fetch(PDO::FETCH_ASSOC);
        $evento['valor_min'] = $row && $row['valor_min'] !== null ? number_format($row['valor_min'], 2, ',', '.') : null;

        // Modalidades principais (sem KIT)
        $modalidades = $pdo->prepare("SELECT DISTINCT m.nome_modalidade FROM modalidades_evento me JOIN modalidades m ON me.modalidade_id = m.id WHERE me.evento_id = ? AND m.nome_modalidade NOT LIKE '%KIT%' ORDER BY m.nome_modalidade");
        $modalidades->execute([$evento['id']]);
        $evento['modalidades'] = $modalidades->fetchAll(PDO::FETCH_COLUMN);

        // Hora início (mock: pode ser adicionado na tabela eventos depois)
        $evento['hora_inicio'] = '07:00';

        // Número de inscritos
        $inscritos = $pdo->prepare("SELECT COUNT(*) FROM inscricoes WHERE evento_id = ? AND status = 'confirmada'");
        $inscritos->execute([$evento['id']]);
        $evento['inscritos'] = (int)$inscritos->fetchColumn();
    }

    echo json_encode(['success' => true, 'eventos' => $eventos]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar eventos: ' . $e->getMessage()]);
    exit;
} 
