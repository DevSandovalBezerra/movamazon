<?php
header('Content-Type: application/json; charset=utf-8');
error_log('[DASHBOARD] Iniciando API do dashboard');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/organizador_context.php';
session_start();

// Logs de depuração

try {
    // Usar o mesmo contexto que "Meus Eventos" usa
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    error_log('[DASHBOARD] Usuário ID: ' . $usuario_id . ', Organizador ID: ' . $organizador_id);

    // Buscar dados do organizador
    $stmt = $pdo->prepare('SELECT id, nome_completo FROM usuarios WHERE id = ?');
    $stmt->execute([$usuario_id]);
    $organizador = $stmt->fetch(PDO::FETCH_ASSOC);
    //error_log('[DASHBOARD] Organizador encontrado: ' . ($organizador ? 'SIM' : 'NÃO'));

    if (!$organizador) {
        echo json_encode(['success' => false, 'message' => 'Organizador não encontrado']);
        exit;
    }

    // Função para verificar se evento tem todas as etapas concluídas
    function eventoCompleto($pdo, $evento_id)
    {
        $checks = [
            'modalidades' => "SELECT COUNT(*) as count FROM modalidades WHERE evento_id = ? AND ativo = 1",
            'lotes' => "SELECT COUNT(*) as count FROM lotes_inscricao WHERE evento_id = ? AND ativo = 1",
            'kits' => "SELECT COUNT(*) as count FROM kits_eventos WHERE evento_id = ? AND ativo = 1",
            'produtos_extras' => "SELECT COUNT(*) as count FROM produtos_extras WHERE evento_id = ? AND ativo = 1",
            'tamanhos' => "SELECT COUNT(*) as count FROM camisas WHERE evento_id = ? AND ativo = 1",
            'programacao' => "SELECT COUNT(*) as count FROM programacao_evento WHERE evento_id = ? AND ativo = 1",
            'questionario' => "SELECT COUNT(*) as count FROM questionario_evento WHERE evento_id = ? AND ativo = 1",
            'retirada_kits' => "SELECT COUNT(*) as count FROM retirada_kits_evento WHERE evento_id = ? AND ativo = 1",
            'cupons' => "SELECT COUNT(*) as count FROM cupons_remessa WHERE evento_id = ? AND status = 'ativo'"
        ];

        foreach ($checks as $sqlCheck) {
            $stmt = $pdo->prepare($sqlCheck);
            $stmt->execute([$evento_id]);
            $count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
            if ($count <= 0) {
                return false; // Evento incompleto
            }
        }
        return true; // Evento completo (9/9 etapas)
    }

    // Buscar eventos do organizador (usando mesma lógica de "Meus Eventos")
    $stmt = $pdo->prepare('
        SELECT 
            e.id,
            e.nome as name,
            e.descricao,
            e.data_inicio as date,
            e.hora_inicio,
            e.local,
            e.cidade,
            e.estado,
            e.imagem as image,
            e.limite_vagas as maxRegistrations,
            e.status,
            COUNT(i.id) as registrations
        FROM eventos e
        LEFT JOIN inscricoes i ON e.id = i.evento_id
        WHERE (e.organizador_id = :organizador_id OR e.organizador_id = :usuario_id) AND e.deleted_at IS NULL
        GROUP BY e.id
        ORDER BY e.data_inicio DESC
    ');

    $stmt->execute([
        ':organizador_id' => $organizador_id,
        ':usuario_id' => $usuario_id
    ]);

    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //error_log('[DASHBOARD] Eventos encontrados: ' . count($eventos));

    // Separar eventos completos e incompletos para estatísticas
    $eventosCompletos = [];
    $eventosIncompletos = [];
    foreach ($eventos as $evento) {
        if (eventoCompleto($pdo, $evento['id'])) {
            $eventosCompletos[] = $evento;
        } else {
            $eventosIncompletos[] = $evento;
        }
    }

    //error_log('[DASHBOARD] Eventos completos (9/9 etapas): ' . count($eventosCompletos));
    //error_log('[DASHBOARD] Eventos incompletos: ' . count($eventosIncompletos));

    // Calcular totais (TODOS os eventos, não apenas completos)
    $totalEventos = count($eventos);
    
    // Buscar métricas de inscrições por status
    $stmt_inscricoes = $pdo->prepare('
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN i.status = "confirmada" AND i.status_pagamento = "pago" THEN 1 ELSE 0 END) as confirmadas_pagas,
            SUM(CASE WHEN i.status = "confirmada" AND i.status_pagamento = "pendente" THEN 1 ELSE 0 END) as confirmadas_pendentes,
            SUM(CASE WHEN i.status = "pendente" THEN 1 ELSE 0 END) as pendentes_confirmacao,
            SUM(CASE WHEN i.status = "cancelada" OR i.status_pagamento = "cancelado" OR i.status_pagamento = "rejeitado" THEN 1 ELSE 0 END) as canceladas,
            SUM(CASE WHEN i.status = "confirmada" AND i.status_pagamento = "pago" THEN i.valor_total ELSE 0 END) as receita_confirmada,
            SUM(CASE WHEN i.status_pagamento = "pendente" THEN i.valor_total ELSE 0 END) as receita_pendente,
            SUM(CASE WHEN i.status = "cancelada" OR i.status_pagamento = "cancelado" OR i.status_pagamento = "rejeitado" THEN i.valor_total ELSE 0 END) as receita_cancelada
        FROM inscricoes i
        INNER JOIN eventos e ON i.evento_id = e.id
        WHERE (e.organizador_id = :organizador_id OR e.organizador_id = :usuario_id) 
        AND e.deleted_at IS NULL
    ');
    $stmt_inscricoes->execute([
        ':organizador_id' => $organizador_id,
        ':usuario_id' => $usuario_id
    ]);
    $inscricoes_stats = $stmt_inscricoes->fetch(PDO::FETCH_ASSOC);
    
    // Calcular totais
    $totalInscritos = (int)($inscricoes_stats['total'] ?? 0);
    $confirmadasPagas = (int)($inscricoes_stats['confirmadas_pagas'] ?? 0);
    $confirmadasPendentes = (int)($inscricoes_stats['confirmadas_pendentes'] ?? 0);
    $pendentesConfirmacao = (int)($inscricoes_stats['pendentes_confirmacao'] ?? 0);
    $canceladas = (int)($inscricoes_stats['canceladas'] ?? 0);
    
    // Receitas
    $receitaConfirmada = (float)($inscricoes_stats['receita_confirmada'] ?? 0);
    $receitaPendente = (float)($inscricoes_stats['receita_pendente'] ?? 0);
    $receitaCancelada = (float)($inscricoes_stats['receita_cancelada'] ?? 0);
    
    // Taxa de conversão (inscrições pagas / total iniciadas)
    $taxaConversao = 0;
    if ($totalInscritos > 0) {
        $taxaConversao = round(($confirmadasPagas / $totalInscritos) * 100, 1);
    }
    
    // Taxa de cancelamento
    $taxaCancelamento = 0;
    if ($totalInscritos > 0) {
        $taxaCancelamento = round(($canceladas / $totalInscritos) * 100, 1);
    }
    
    // Calcular taxa de ocupação média (apenas eventos completos e apenas inscrições confirmadas e pagas)
    $taxaOcupacao = 0;
    if (count($eventosCompletos) > 0) {
        $somaTaxas = 0;
        foreach ($eventosCompletos as $evento) {
            if ($evento['maxRegistrations'] > 0) {
                // Buscar apenas inscrições confirmadas e pagas para este evento
                $stmt_ocupacao = $pdo->prepare('
                    SELECT COUNT(*) as count
                    FROM inscricoes
                    WHERE evento_id = ? 
                    AND status = "confirmada" 
                    AND status_pagamento = "pago"
                ');
                $stmt_ocupacao->execute([$evento['id']]);
                $ocupacao = (int)$stmt_ocupacao->fetch(PDO::FETCH_ASSOC)['count'];
                $somaTaxas += ($ocupacao / $evento['maxRegistrations']) * 100;
            }
        }
        $taxaOcupacao = round($somaTaxas / count($eventosCompletos), 1);
    }
    
    // Comparações temporais (mês anterior)
    $mes_atual = date('Y-m');
    $mes_anterior = date('Y-m', strtotime('-1 month'));
    
    $stmt_comparacao = $pdo->prepare('
        SELECT 
            SUM(CASE WHEN DATE_FORMAT(i.data_inscricao, "%Y-%m") = :mes_atual AND i.status = "confirmada" AND i.status_pagamento = "pago" THEN 1 ELSE 0 END) as inscricoes_mes_atual,
            SUM(CASE WHEN DATE_FORMAT(i.data_inscricao, "%Y-%m") = :mes_anterior AND i.status = "confirmada" AND i.status_pagamento = "pago" THEN 1 ELSE 0 END) as inscricoes_mes_anterior,
            SUM(CASE WHEN DATE_FORMAT(i.data_inscricao, "%Y-%m") = :mes_atual2 AND i.status = "confirmada" AND i.status_pagamento = "pago" THEN i.valor_total ELSE 0 END) as receita_mes_atual,
            SUM(CASE WHEN DATE_FORMAT(i.data_inscricao, "%Y-%m") = :mes_anterior2 AND i.status = "confirmada" AND i.status_pagamento = "pago" THEN i.valor_total ELSE 0 END) as receita_mes_anterior
        FROM inscricoes i
        INNER JOIN eventos e ON i.evento_id = e.id
        WHERE (e.organizador_id = :organizador_id OR e.organizador_id = :usuario_id) 
        AND e.deleted_at IS NULL
    ');
    $stmt_comparacao->execute([
        ':mes_atual' => $mes_atual,
        ':mes_anterior' => $mes_anterior,
        ':mes_atual2' => $mes_atual,
        ':mes_anterior2' => $mes_anterior,
        ':organizador_id' => $organizador_id,
        ':usuario_id' => $usuario_id
    ]);
    $comparacao = $stmt_comparacao->fetch(PDO::FETCH_ASSOC);
    
    $inscricoes_mes_atual = (int)($comparacao['inscricoes_mes_atual'] ?? 0);
    $inscricoes_mes_anterior = (int)($comparacao['inscricoes_mes_anterior'] ?? 0);
    $receita_mes_atual = (float)($comparacao['receita_mes_atual'] ?? 0);
    $receita_mes_anterior = (float)($comparacao['receita_mes_anterior'] ?? 0);
    
    // Calcular variação percentual
    $variacao_inscricoes = 0;
    if ($inscricoes_mes_anterior > 0) {
        $variacao_inscricoes = round((($inscricoes_mes_atual - $inscricoes_mes_anterior) / $inscricoes_mes_anterior) * 100, 1);
    } elseif ($inscricoes_mes_atual > 0) {
        $variacao_inscricoes = 100;
    }
    
    $variacao_receita = 0;
    if ($receita_mes_anterior > 0) {
        $variacao_receita = round((($receita_mes_atual - $receita_mes_anterior) / $receita_mes_anterior) * 100, 1);
    } elseif ($receita_mes_atual > 0) {
        $variacao_receita = 100;
    }

    // Formatar dados para o frontend (TODOS os eventos) com métricas por status
    $eventosFormatados = [];
    foreach ($eventos as $evento) {
        $isCompleto = eventoCompleto($pdo, $evento['id']);
        
        // Buscar métricas específicas deste evento
        $stmt_evento_inscricoes = $pdo->prepare('
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = "confirmada" AND status_pagamento = "pago" THEN 1 ELSE 0 END) as confirmadas_pagas,
                SUM(CASE WHEN status = "confirmada" AND status_pagamento = "pago" THEN valor_total ELSE 0 END) as receita_confirmada
            FROM inscricoes
            WHERE evento_id = ?
        ');
        $stmt_evento_inscricoes->execute([$evento['id']]);
        $evento_stats = $stmt_evento_inscricoes->fetch(PDO::FETCH_ASSOC);
        
        $inscricoes_confirmadas = (int)($evento_stats['confirmadas_pagas'] ?? 0);
        $receita_evento = (float)($evento_stats['receita_confirmada'] ?? 0);
        $taxa_ocupacao_evento = 0;
        if ($evento['maxRegistrations'] > 0) {
            $taxa_ocupacao_evento = round(($inscricoes_confirmadas / $evento['maxRegistrations']) * 100, 1);
        }
        
        $eventosFormatados[] = [
            'id' => $evento['id'],
            'name' => $evento['name'],
            'descricao' => $evento['descricao'],
            'date' => $evento['date'],
            'hora_inicio' => $evento['hora_inicio'],
            'local' => $evento['local'],
            'cidade' => $evento['cidade'] ?: '',
            'estado' => $evento['estado'] ?: '',
            'registrations' => (int)$evento['registrations'],
            'inscricoes_confirmadas' => $inscricoes_confirmadas,
            'receita_confirmada' => $receita_evento,
            'taxa_ocupacao' => $taxa_ocupacao_evento,
            'image' => $evento['image'] ?: 'default-event.jpg',
            'status' => $evento['status'] ?: 'ativo',
            'maxRegistrations' => (int)$evento['maxRegistrations'] ?: 1000,
            'completo' => $isCompleto
        ];
    }

    // Buscar atividades recentes (últimas 5 inscrições de todos os eventos)
    $stmt = $pdo->prepare('
        SELECT 
            i.data_inscricao as data,
            CONCAT("Nova inscrição em ", e.nome) as titulo,
            "fa-user-plus" as icone
        FROM inscricoes i
        INNER JOIN eventos e ON i.evento_id = e.id
        WHERE (e.organizador_id = :organizador_id OR e.organizador_id = :usuario_id) AND e.deleted_at IS NULL
        ORDER BY i.data_inscricao DESC
        LIMIT 5
    ');
    $stmt->execute([
        ':organizador_id' => $organizador_id,
        ':usuario_id' => $usuario_id
    ]);
    $atividadesCompletas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'data' => [
            'organizador' => [
                'id' => $organizador['id'],
                'nome' => $organizador['nome_completo']
            ],
            'estatisticas' => [
                'totalEventos' => $totalEventos,
                'totalInscritos' => $totalInscritos,
                'inscricoes_confirmadas_pagas' => $confirmadasPagas,
                'inscricoes_confirmadas_pendentes' => $confirmadasPendentes,
                'inscricoes_pendentes_confirmacao' => $pendentesConfirmacao,
                'inscricoes_canceladas' => $canceladas,
                'receita_confirmada' => $receitaConfirmada,
                'receita_pendente' => $receitaPendente,
                'receita_cancelada' => $receitaCancelada,
                'taxa_conversao' => $taxaConversao,
                'taxa_cancelamento' => $taxaCancelamento,
                'taxaOcupacao' => $taxaOcupacao,
                'eventos_completos' => count($eventosCompletos),
                'eventos_incompletos' => count($eventosIncompletos),
                'comparacao' => [
                    'inscricoes' => [
                        'mes_atual' => $inscricoes_mes_atual,
                        'mes_anterior' => $inscricoes_mes_anterior,
                        'variacao_percentual' => $variacao_inscricoes
                    ],
                    'receita' => [
                        'mes_atual' => $receita_mes_atual,
                        'mes_anterior' => $receita_mes_anterior,
                        'variacao_percentual' => $variacao_receita
                    ]
                ]
            ],
            'eventos' => $eventosFormatados,
            'atividades' => $atividadesCompletas
        ]
    ];


    //error_log('[DASHBOARD] Resposta: ' . json_encode($response));
    echo json_encode($response);
} catch (Exception $e) {
    error_log('[DASHBOARD] ERRO: ' . $e->getMessage());
    error_log('[DASHBOARD] Stack trace: ' . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar dados: ' . $e->getMessage()]);
}
