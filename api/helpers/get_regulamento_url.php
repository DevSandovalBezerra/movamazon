<?php
/**
 * Retorna a URL do regulamento do evento, ou null se não disponível.
 * Reutiliza a mesma lógica de termos.php (eventos.regulamento_arquivo + fallback solicitacoes_evento).
 *
 * @param int $evento_id ID do evento
 * @param PDO $pdo Conexão com o banco
 * @return string|null URL do regulamento ou null
 */
function getRegulamentoUrl(int $evento_id, PDO $pdo): ?string
{
    $regulamento_arquivo = null;

    try {
        $stmt_evento = $pdo->prepare("
            SELECT id, nome, data_realizacao, data_inicio, cidade, estado
            FROM eventos
            WHERE id = ? AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt_evento->execute([$evento_id]);
        $evento_info = $stmt_evento->fetch(PDO::FETCH_ASSOC);

        if (!$evento_info) {
            return null;
        }

        $hasRegulamentoArquivo = false;
        try {
            $checkColumn = $pdo->query("SHOW COLUMNS FROM eventos LIKE 'regulamento_arquivo'");
            $hasRegulamentoArquivo = $checkColumn->rowCount() > 0;
        } catch (Exception $e) {
            $hasRegulamentoArquivo = false;
        }

        if ($hasRegulamentoArquivo) {
            $stmt_reg = $pdo->prepare("SELECT regulamento_arquivo FROM eventos WHERE id = ? AND deleted_at IS NULL");
            $stmt_reg->execute([$evento_id]);
            $regulamento_arquivo = $stmt_reg->fetchColumn();
        }

        if (empty($regulamento_arquivo)) {
            $link = null;

            $stSol1 = $pdo->prepare("
                SELECT link_regulamento
                FROM solicitacoes_evento
                WHERE status = 'aprovado'
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
                'nome_evento' => $evento_info['nome'],
                'nome_like' => '%' . $evento_info['nome'] . '%',
            ]);
            $link = $stSol1->fetchColumn();

            if (!$link) {
                $stSol2 = $pdo->prepare("
                    SELECT link_regulamento
                    FROM solicitacoes_evento
                    WHERE status = 'aprovado'
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
                    'data_realizacao' => $evento_info['data_realizacao'] ?: null,
                    'data_inicio' => $evento_info['data_inicio'] ?: null,
                    'cidade' => $evento_info['cidade'] ?: '',
                    'estado' => $evento_info['estado'] ?: '',
                ]);
                $link = $stSol2->fetchColumn();
            }

            if ($link) {
                $regulamento_arquivo = (string) $link;
            }
        }

        if (empty($regulamento_arquivo)) {
            return null;
        }

        $regulamento_arquivo_trim = trim((string) $regulamento_arquivo);
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $project_path = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $request_uri = $_SERVER['REQUEST_URI'];
            if (preg_match('#(/movamazon/)#', $request_uri)) {
                $project_path = '/movamazon';
            }
        }
        if (empty($project_path) && strpos($host, 'localhost') === false && strpos($host, 'movamazon.com.br') !== false) {
            $project_path = '';
        } elseif (empty($project_path)) {
            $project_path = '/movamazon';
        }

        if (strpos($regulamento_arquivo_trim, 'frontend/assets/docs/regulamentos/') === 0) {
            return $protocol . '://' . $host . $project_path . '/' . $regulamento_arquivo_trim;
        }
        if (strpos($regulamento_arquivo_trim, 'api/uploads/regulamentos/') === 0) {
            return $protocol . '://' . $host . $project_path . '/' . $regulamento_arquivo_trim;
        }

        $nomeArquivo = basename($regulamento_arquivo_trim);
        if (!empty($nomeArquivo) && $nomeArquivo !== '.' && $nomeArquivo !== '..') {
            $api_base = $protocol . '://' . $host . $project_path . '/api/';
            return $api_base . 'uploads/regulamentos/download.php?file=' . urlencode($nomeArquivo);
        }

        return null;
    } catch (Exception $e) {
        error_log("Erro ao buscar regulamento (get_regulamento_url): " . $e->getMessage());
        return null;
    }
}
