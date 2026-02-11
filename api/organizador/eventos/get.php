<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once '../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Verificar autenticação e permissões usando middleware centralizado
verificarAutenticacao('organizador');

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    $evento_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    error_log('📡 API eventos/get.php - Buscando evento - ID: ' . $evento_id);
    error_log('📡 API eventos/get.php - Organizador ID da sessão: ' . $organizador_id);

    if (!$evento_id) {
        throw new Exception('ID do evento é obrigatório');
    }

    // Compatibilidade: alguns bancos ainda não possuem a coluna eventos.regulamento_arquivo
    $hasRegulamentoArquivo = false;
    try {
        $stCol = $pdo->prepare("
            SELECT COUNT(*) 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'eventos' 
              AND COLUMN_NAME = 'regulamento_arquivo'
        ");
        $stCol->execute();
        $hasRegulamentoArquivo = ((int)$stCol->fetchColumn() > 0);
    } catch (Throwable $e) {
        // Se não conseguir verificar, assume que não existe e segue sem quebrar
        $hasRegulamentoArquivo = false;
    }

    // Buscar dados do evento com informações do organizador
    $sql = "SELECT 
                e.id,
                e.nome,
                e.categoria,
                e.genero,
                e.descricao,
                e.data_inicio,
                e.data_fim,
                e.local,
                e.cep,
                e.url_mapa,
                e.logradouro,
                e.numero,
                e.cidade,
                e.estado,
                e.pais,
                e.regulamento,
                " . ($hasRegulamentoArquivo ? "e.regulamento_arquivo" : "NULL AS regulamento_arquivo") . ",
                e.status,
                e.taxa_setup,
                e.taxa_gratuitas,
                e.limite_vagas,
                e.hora_fim_inscricoes,
                e.data_fim_inscricoes,
                e.taxa_pagas,
                e.percentual_repasse,
                e.exibir_retirada_kit,
                e.hora_inicio,
                e.imagem,
                e.data_criacao,
                e.data_realizacao,
                e.organizador_id,
                o.empresa as organizador_nome
            FROM eventos e
            LEFT JOIN organizadores o ON o.id = ?
            WHERE e.id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$organizador_id, $evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$evento) {
        throw new Exception('Evento não encontrado');
    }

    error_log('📡 API eventos/get.php - Evento encontrado - Organizador ID: ' . $evento['organizador_id']);
    error_log('📡 API eventos/get.php - Organizador Nome: ' . ($evento['organizador_nome'] ?: 'VAZIO'));

    // Verificar se o organizador tem permissão para acessar este evento
    if ($evento['organizador_id'] != $organizador_id && $evento['organizador_id'] != $usuario_id) {
        error_log('📡 API eventos/get.php - ERRO: Organizador não autorizado');
        throw new Exception('Evento não autorizado');
    }

    // Formatar dados
    $evento['data_inicio_formatada'] = $evento['data_inicio'] ? date('Y-m-d', strtotime($evento['data_inicio'])) : '';
    $evento['data_fim_formatada'] = $evento['data_fim'] && $evento['data_fim'] !== '0000-00-00' ? date('Y-m-d', strtotime($evento['data_fim'])) : '';
    $evento['hora_inicio_formatada'] = $evento['hora_inicio'] ? date('H:i', strtotime($evento['hora_inicio'])) : '';

    // URL da imagem
    if ($evento['imagem']) {
        $evento['imagem_url'] = '../../assets/img/eventos/' . $evento['imagem'];
    } else {
        $evento['imagem_url'] = '../../assets/img/default-event.jpg';
    }

    // Fallback definitivo: se o evento não tem regulamento_arquivo, buscar da solicitação aprovada (link_regulamento)
    // Isso resolve casos em que o arquivo existe em api/uploads/regulamentos, mas não foi gravado em eventos.
    if (empty($evento['regulamento_arquivo'])) {
        $userEmail = (string)($_SESSION['user_email'] ?? ($_SESSION['email'] ?? ''));

        if ($userEmail !== '') {
            try {
                // Tentativa 1: casar pelo nome do evento (tolerante a variações)
                $stSol1 = $pdo->prepare("
                    SELECT link_regulamento
                    FROM solicitacoes_evento
                    WHERE status = 'aprovado'
                      AND responsavel_email = :email
                      AND link_regulamento IS NOT NULL
                      AND link_regulamento <> ''
                      AND (
                            LOWER(TRIM(nome_evento)) = LOWER(TRIM(:nome_evento))
                         OR LOWER(nome_evento) LIKE LOWER(:nome_like)
                      )
                    ORDER BY atualizado_em DESC, id DESC
                    LIMIT 1
                ");
                $stSol1->execute([
                    'email' => $userEmail,
                    'nome_evento' => $evento['nome'],
                    'nome_like' => '%' . $evento['nome'] . '%',
                ]);
                $link = $stSol1->fetchColumn();

                // Tentativa 2: casar por data/cidade/uf (mais robusto quando nome diverge)
                if (!$link) {
                    $stSol2 = $pdo->prepare("
                        SELECT link_regulamento
                        FROM solicitacoes_evento
                        WHERE status = 'aprovado'
                          AND responsavel_email = :email
                          AND link_regulamento IS NOT NULL
                          AND link_regulamento <> ''
                          AND (
                                (:data_realizacao IS NOT NULL AND data_prevista = :data_realizacao)
                             OR (:data_inicio IS NOT NULL AND data_prevista = :data_inicio)
                          )
                          AND (
                                cidade_evento = :cidade
                             OR :cidade = ''
                          )
                          AND (
                                uf_evento = :estado
                             OR :estado = ''
                          )
                        ORDER BY atualizado_em DESC, id DESC
                        LIMIT 1
                    ");
                    $stSol2->execute([
                        'email' => $userEmail,
                        'data_realizacao' => $evento['data_realizacao'] ?: null,
                        'data_inicio' => $evento['data_inicio'] ?: null,
                        'cidade' => $evento['cidade'] ?: '',
                        'estado' => $evento['estado'] ?: '',
                    ]);
                    $link = $stSol2->fetchColumn();
                }

                // Tentativa 3: último aprovado do organizador (fallback final)
                if (!$link) {
                    $stSol3 = $pdo->prepare("
                        SELECT link_regulamento
                        FROM solicitacoes_evento
                        WHERE status = 'aprovado'
                          AND responsavel_email = :email
                          AND link_regulamento IS NOT NULL
                          AND link_regulamento <> ''
                        ORDER BY atualizado_em DESC, id DESC
                        LIMIT 1
                    ");
                    $stSol3->execute(['email' => $userEmail]);
                    $link = $stSol3->fetchColumn();
                }

                if ($link) {
                    // armazenado na resposta como se fosse regulamento_arquivo (nome do arquivo)
                    $evento['regulamento_arquivo'] = (string)$link;
                }
            } catch (Throwable $e) {
                // não falhar a API por causa desse fallback
            }
        }
    }

    error_log('✅ Evento encontrado: ' . $evento['nome']);
    error_log('🔍 DEBUG - Dados do evento retornados: ' . json_encode($evento));

    echo json_encode([
        'success' => true,
        'data' => $evento
    ]);
} catch (Exception $e) {
    error_log('💥 Erro ao buscar evento: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
