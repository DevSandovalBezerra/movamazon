<?php
// ✅ NOVO: Buscar termos dinâmicos via API com debug melhorado
$termos_dinamicos = [];
$termos_evento = '';
$termos_modalidades = [];
$debug_info = [];

require_once dirname(__DIR__, 3) . '/api/helpers/url_base.php';

function fetch_json_with_debug($url, &$httpCode = null, &$contentType = null, &$curlError = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($curlError) {
        return false;
    }

    return $response;
}

// Obter evento_id da sessão ou GET (compatibilidade)
$evento_id = $evento_id ?? $_SESSION['inscricao']['evento_id'] ?? $_GET['evento_id'] ?? null;
$modalidades_selecionadas = $modalidades_selecionadas ?? $_SESSION['inscricao']['modalidades_selecionadas'] ?? [];

// Buscar regulamento_arquivo do evento - USAR MESMO ENDPOINT DA PROGRAMAÇÃO
$regulamento_arquivo = null;
$regulamento_url = null;

// Garantir que temos acesso ao $pdo (vem do index.php via require_once)
if (!isset($pdo)) {
    require_once dirname(__DIR__, 3) . '/api/db.php';
}

if ($evento_id) {
    try {
        // Buscar dados básicos do evento (para fallback em solicitacoes_evento)
        $stmt_evento = $pdo->prepare("
            SELECT id, nome, data_realizacao, data_inicio, cidade, estado
            FROM eventos
            WHERE id = ? AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt_evento->execute([$evento_id]);
        $evento_info = $stmt_evento->fetch(PDO::FETCH_ASSOC);

        if ($evento_info) {
            // Verificar se a coluna existe
            $hasRegulamentoArquivo = false;
            try {
                $checkColumn = $pdo->query("SHOW COLUMNS FROM eventos LIKE 'regulamento_arquivo'");
                $hasRegulamentoArquivo = $checkColumn->rowCount() > 0;
            } catch (Exception $e) {
                $hasRegulamentoArquivo = false;
            }

            // 1) Tentar via eventos.regulamento_arquivo
            if ($hasRegulamentoArquivo) {
                $stmt_reg = $pdo->prepare("SELECT regulamento_arquivo FROM eventos WHERE id = ? AND deleted_at IS NULL");
                $stmt_reg->execute([$evento_id]);
                $regulamento_arquivo = $stmt_reg->fetchColumn();
            }

            // 2) Fallback: buscar em solicitacoes_evento (sem depender de email)
            if (empty($regulamento_arquivo)) {
                $link = null;

                // Tentativa 1: nome do evento
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

                // Tentativa 2: data/cidade/uf
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
                    $regulamento_arquivo = (string)$link;
                }
            }

            // Montar URL se houver arquivo
            if (!empty($regulamento_arquivo)) {
                $regulamento_arquivo_trim = trim((string)$regulamento_arquivo);
                error_log("[TERMOS] Regulamento arquivo: " . $regulamento_arquivo_trim);

                // Se o caminho contém frontend/assets/docs/regulamentos/ (legado)
                if (strpos($regulamento_arquivo_trim, 'frontend/assets/docs/regulamentos/') === 0) {
                    $regulamento_url = app_asset_url($regulamento_arquivo_trim);
                    error_log("[TERMOS] URL gerado (frontend/assets - legado): " . $regulamento_url);
                }
                // Se o caminho contém api/uploads/regulamentos/ (novo padrão)
                elseif (strpos($regulamento_arquivo_trim, 'api/uploads/regulamentos/') === 0) {
                    $regulamento_url = app_asset_url($regulamento_arquivo_trim);
                    error_log("[TERMOS] URL gerado (api/uploads - novo): " . $regulamento_url);
                }
                // Caso contrário, usar download.php (apenas nome do arquivo)
                else {
                    $nomeArquivo = basename($regulamento_arquivo_trim);
                    if (!empty($nomeArquivo) && $nomeArquivo !== '.' && $nomeArquivo !== '..') {
                        $regulamento_url = app_api_url('uploads/regulamentos/download.php?file=' . urlencode($nomeArquivo));
                        error_log("[TERMOS] URL gerado (download.php - fallback): " . $regulamento_url);
                    }
                }
                error_log("[TERMOS] URL final do regulamento: " . ($regulamento_url ?? 'vazio'));
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar regulamento: " . $e->getMessage());
    }
}

try {
    // Debug: Informações básicas
    $debug_info['evento_id'] = $evento_id ?? 'NÃO DEFINIDO';
    $debug_info['modalidades_count'] = count($modalidades_selecionadas ?? []);
    $debug_info['modalidades_ids'] = array_column($modalidades_selecionadas ?? [], 'id');

    $debug_info['url_base_resolvido'] = app_url_base();

    // Buscar termos gerais do evento usando URL absoluta
    $url_termos = app_api_url('inscricao/get_termos.php?evento_id=' . $evento_id);
    $debug_info['url_termos'] = $url_termos;

    $http_code_termos = null;
    $content_type_termos = null;
    $curl_error = null;
    $response_termos = fetch_json_with_debug($url_termos, $http_code_termos, $content_type_termos, $curl_error);

    $debug_info['http_code_termos'] = $http_code_termos;
    $debug_info['content_type_termos'] = $content_type_termos;
    if ($curl_error) {
        error_log("Erro cURL ao buscar termos: " . $curl_error);
        $response_termos = false;
    }
    $debug_info['response_termos_length'] = $response_termos !== false ? strlen($response_termos) : 0;

    if ($response_termos) {
        $dados_termos = json_decode($response_termos, true);
        $json_error_termos = json_last_error();
        $json_error_msg_termos = json_last_error_msg();
        $debug_info['dados_termos'] = $dados_termos;
        $debug_info['json_error_termos'] = $json_error_msg_termos;

        if ($json_error_termos !== JSON_ERROR_NONE) {
            $debug_info['termos_gerais_encontrados'] = false;
            $debug_info['erro_termos_gerais'] = 'JSON inválido da API: ' . $json_error_msg_termos;
        } elseif ($dados_termos && !empty($dados_termos['success'])) {
            $termos_evento = $dados_termos['termos']['conteudo'] ?? '';
            $termos_dinamicos[] = [
                'titulo' => $dados_termos['termos']['titulo'] ?? 'Termos Gerais',
                'conteudo' => $termos_evento,
                'versao' => $dados_termos['termos']['versao'] ?? '1.0',
                'tipo' => $dados_termos['termos']['tipo'] ?? 'evento'
            ];
            $debug_info['termos_gerais_encontrados'] = true;
        } else {
            $debug_info['termos_gerais_encontrados'] = false;
            $debug_info['erro_termos_gerais'] = $dados_termos['error'] ?? 'Erro desconhecido';
        }
    } else {
        $debug_info['termos_gerais_encontrados'] = false;
        $debug_info['erro_termos_gerais'] = 'Resposta vazia da API';
    }

    // Buscar termos específicos das modalidades selecionadas
    if (!empty($modalidades_selecionadas)) {
        $debug_info['buscando_termos_modalidades'] = true;

        foreach ($modalidades_selecionadas as $modalidade) {
            $modalidade_id = (int)($modalidade['id'] ?? 0);
            if ($modalidade_id <= 0) {
                continue;
            }

            $url_modalidade = app_api_url('inscricao/get_termos.php?evento_id=' . $evento_id . '&modalidade_id=' . $modalidade_id);
            $debug_info['url_modalidade_' . $modalidade['id']] = $url_modalidade;

            $http_code_modalidade = null;
            $content_type_modalidade = null;
            $curl_error = null;
            $response_modalidade = fetch_json_with_debug($url_modalidade, $http_code_modalidade, $content_type_modalidade, $curl_error);

            $debug_info['http_code_modalidade_' . $modalidade['id']] = $http_code_modalidade;
            $debug_info['content_type_modalidade_' . $modalidade['id']] = $content_type_modalidade;
            if ($curl_error) {
                error_log("Erro cURL ao buscar termos da modalidade {$modalidade['id']}: " . $curl_error);
                $response_modalidade = false;
            }
            $debug_info['response_modalidade_' . $modalidade['id'] . '_length'] = $response_modalidade !== false ? strlen($response_modalidade) : 0;

            if ($response_modalidade) {
                $dados_modalidade = json_decode($response_modalidade, true);
                $json_error_modalidade = json_last_error();
                $json_error_msg_modalidade = json_last_error_msg();
                $debug_info['dados_modalidade_' . $modalidade['id']] = $dados_modalidade;
                $debug_info['json_error_modalidade_' . $modalidade['id']] = $json_error_msg_modalidade;

                if ($json_error_modalidade !== JSON_ERROR_NONE) {
                    $debug_info['termos_modalidade_' . $modalidade['id'] . '_encontrados'] = false;
                    $debug_info['erro_modalidade_' . $modalidade['id']] = 'JSON inválido da API: ' . $json_error_msg_modalidade;
                } elseif ($dados_modalidade && !empty($dados_modalidade['success']) && ($dados_modalidade['termos']['tipo'] ?? '') === 'modalidade') {
                    $termos_modalidades[] = [
                        'titulo' => $dados_modalidade['termos']['titulo'] ?? 'Termos da Modalidade',
                        'conteudo' => $dados_modalidade['termos']['conteudo'] ?? '',
                        'versao' => $dados_modalidade['termos']['versao'] ?? '1.0',
                        'modalidade' => $modalidade['nome'] ?? 'Modalidade'
                    ];
                    $debug_info['termos_modalidade_' . $modalidade['id'] . '_encontrados'] = true;
                } elseif ($dados_modalidade && !empty($dados_modalidade['success'])) {
                    // Sem termo específico por modalidade: não é falha do fluxo.
                    $debug_info['termos_modalidade_' . $modalidade['id'] . '_encontrados'] = false;
                    $debug_info['status_modalidade_' . $modalidade['id']] = 'sem_termo_modalidade';
                } else {
                    $debug_info['termos_modalidade_' . $modalidade['id'] . '_encontrados'] = false;
                    $debug_info['erro_modalidade_' . $modalidade['id']] = $dados_modalidade['error'] ?? 'Erro na resposta da API';
                }
            } else {
                $debug_info['termos_modalidade_' . $modalidade['id'] . '_encontrados'] = false;
                $debug_info['erro_modalidade_' . $modalidade['id']] = 'Resposta vazia da API';
            }
        }
    } else {
        $debug_info['buscando_termos_modalidades'] = false;
        $debug_info['motivo'] = 'Nenhuma modalidade selecionada';
    }
} catch (Exception $e) {
    $debug_info['exception'] = $e->getMessage();
    error_log("Erro ao buscar termos dinâmicos: " . $e->getMessage());
    // Fallback para termos padrão se houver erro
    $termos_evento = '';
    $termos_modalidades = [];
}

// Debug final
$debug_info['termos_dinamicos_count'] = count($termos_dinamicos);
$debug_info['termos_modalidades_count'] = count($termos_modalidades);
$debug_info['usando_fallback'] = (empty($termos_dinamicos) && empty($termos_modalidades));

error_log("DEBUG termos.php - Informações completas: " . json_encode($debug_info, JSON_PRETTY_PRINT));
?>

<div class="container-fluid" id="termos-inscricao">
    <div class="row">
        <div class="col-12">
            <div class="inscricao-header mb-4">
                <h2 class="text-center mb-3">Termos e Condições</h2>
                <p class="text-center text-muted">Leia atentamente todos os termos antes de prosseguir</p>
            </div>

            <div class="termos-container">
                <div class="termos-header mb-4">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Importante:</strong> Role a tela e leia todos os termos antes de marcar o aceite
                    </div>
                </div>

                <div class="termos-content" id="termosScroll">
                    <!-- ✅ NOVO: Termos Dinâmicos do Evento -->
                    <?php if (!empty($termos_dinamicos)): ?>
                        <?php foreach ($termos_dinamicos as $termo): ?>
                            <div class="termo-secao mb-4">
                                <h4 class="termo-titulo">
                                    <i class="fas fa-file-contract text-primary"></i>
                                    <?php echo htmlspecialchars($termo['titulo']); ?>
                                    <small class="text-muted">(v<?php echo htmlspecialchars($termo['versao']); ?>)</small>
                                </h4>
                                <div class="termo-conteudo">
                                    <?php echo $termo['conteudo']; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- ✅ NOVO: Termos Específicos das Modalidades -->
                    <?php if (!empty($termos_modalidades)): ?>
                        <?php foreach ($termos_modalidades as $termo): ?>
                            <div class="termo-secao mb-4">
                                <h4 class="termo-titulo">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                    <?php echo htmlspecialchars($termo['titulo']); ?> - <?php echo htmlspecialchars($termo['modalidade']); ?>
                                    <small class="text-muted">(v<?php echo htmlspecialchars($termo['versao']); ?>)</small>
                                </h4>
                                <div class="termo-conteudo">
                                    <?php echo $termo['conteudo']; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- ✅ Termos Padrão (Fallback) -->
                    <?php if (empty($termos_dinamicos) && empty($termos_modalidades)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Atenção:</strong> Nenhum termo dinâmico encontrado. Exibindo termos padrão.
                        </div>

                        <div class="termo-secao mb-4">
                            <h4 class="termo-titulo">
                                <i class="fas fa-shield-alt text-success"></i>
                                Declaração de Responsabilidade
                            </h4>
                            <div class="termo-conteudo">
                                <p><strong>1. Condição Física:</strong></p>
                                <p>O participante declara estar em condições físicas adequadas para participar do evento, isentando os organizadores de qualquer responsabilidade por problemas de saúde que possam ocorrer durante ou após a participação.</p>

                                <p><strong>2. Segurança:</strong></p>
                                <p>O participante concorda em seguir todas as orientações de segurança fornecidas pelos organizadores e monitores do evento.</p>

                                <p><strong>3. Imagens:</strong></p>
                                <p>O participante autoriza o uso de sua imagem em fotos e vídeos do evento para fins promocionais e de divulgação.</p>

                                <p><strong>4. Regulamento:</strong></p>
                                <p>O participante declara ter lido e concordado com o regulamento completo do evento, disponível no site oficial.</p>
                            </div>
                        </div>

                        <div class="termo-secao mb-4">
                            <h4 class="termo-titulo">
                                <i class="fas fa-gavel text-danger"></i>
                                Regulamento do Evento
                            </h4>
                            <div class="termo-conteudo">
                                <p><strong>Art. 1º - Objetivo:</strong></p>
                                <p>Este regulamento estabelece as normas e condições para participação no evento esportivo.</p>

                                <p><strong>Art. 2º - Participação:</strong></p>
                                <p>A participação é individual e voluntária, sendo obrigatório o cumprimento de todas as regras estabelecidas.</p>

                                <p><strong>Art. 3º - Desclassificação:</strong></p>
                                <p>Será desclassificado o participante que não cumprir as regras ou cometer infrações graves.</p>

                                <p><strong>Art. 4º - Cancelamento:</strong></p>
                                <p>O evento poderá ser cancelado ou alterado por motivos de força maior, sem direito a reembolso.</p>
                            </div>
                        </div>

                        <div class="termo-secao mb-4">
                            <h4 class="termo-titulo">
                                <i class="fas fa-handshake text-info"></i>
                                Política de Reembolso
                            </h4>
                            <div class="termo-conteudo">
                                <p><strong>Cancelamento por parte do participante:</strong></p>
                                <ul>
                                    <li>Até 30 dias antes do evento: 80% de reembolso</li>
                                    <li>Até 15 dias antes do evento: 50% de reembolso</li>
                                    <li>Menos de 15 dias: sem reembolso</li>
                                </ul>

                                <p><strong>Cancelamento por parte da organização:</strong></p>
                                <p>Reembolso integral ou transferência para nova data do evento.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="termos-footer mt-4">
                    <div class="aceite-container">
                        <label class="checkbox-container" id="checkboxContainer">
                            <input type="checkbox" id="aceiteTermos" required>
                            <span class="checkmark"></span>
                            <span class="aceite-texto">
                                Li e concordo com todos os termos, condições<?php if (!empty($regulamento_url)): ?> e <a href="<?php echo htmlspecialchars($regulamento_url); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-semibold" onclick="event.stopPropagation();" title="Clique para visualizar o regulamento do evento">regulamento do evento</a><?php else: ?> e regulamento do evento<?php endif; ?>
                                <?php if (!empty($termos_dinamicos) || !empty($termos_modalidades)): ?>
                                    <br><small class="text-muted">
                                        Versões aceitas:
                                        <?php
                                        $versoes = [];
                                        foreach ($termos_dinamicos as $termo) {
                                            $versoes[] = $termo['versao'];
                                        }
                                        foreach ($termos_modalidades as $termo) {
                                            $versoes[] = $termo['versao'];
                                        }
                                        echo implode(', ', array_unique($versoes));
                                        ?>
                                    </small>
                                <?php endif; ?>
                            </span>
                        </label>

                        <div class="aceite-info mt-2">
                            <small class="text-muted">
                                <i class="fas fa-lock"></i>
                                Seus dados estão protegidos e serão utilizados apenas para fins do evento
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="navegacao-etapas mt-4">
                <div class="flex justify-between">
                    <button class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-all duration-200" onclick="voltarEtapa()">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed" onclick="prosseguirEtapa()" id="btn-prosseguir" disabled>
                        Próximo <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos específicos para página de termos - maior especificidade */
    #termos-inscricao .inscricao-header,
    .inscricao-container .inscricao-wrapper .inscricao-header {
        background: linear-gradient(135deg, #0b4340 0%, #10B981 100%) !important;
        color: white !important;
        padding: 30px !important;
        border-radius: 15px !important;
        margin-bottom: 30px !important;
    }

    #termos-inscricao .inscricao-header h2,
    .inscricao-container .inscricao-wrapper .inscricao-header h2 {
        color: white !important;
    }

    #termos-inscricao .inscricao-header .text-muted,
    .inscricao-container .inscricao-wrapper .inscricao-header .text-muted {
        color: rgba(255, 255, 255, 0.9) !important;
    }

    #termos-inscricao .termos-container {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    #termos-inscricao .termos-header .alert {
        border: none !important;
        border-radius: 10px !important;
        font-size: 16px !important;
        background: linear-gradient(135deg, #0b4340 0%, #10B981 100%) !important;
        color: white !important;
        border-left: 4px solid #10B981 !important;
        padding: 15px 20px !important;
    }

    #termos-inscricao .termos-header .alert i,
    #termos-inscricao .termos-header .alert strong {
        color: white !important;
    }

    #termos-inscricao .termos-content {
        max-height: 500px;
        overflow-y: auto;
        padding: 20px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        background: #f8f9fa;
        margin-bottom: 20px;
    }

    #termos-inscricao .termo-secao {
        background: white;
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #0b4340;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    #termos-inscricao .termo-titulo {
        color: #333;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #termos-inscricao .termo-titulo .text-muted {
        color: #6b7280 !important;
    }

    #termos-inscricao .termo-titulo i {
        color: #0b4340 !important;
    }

    #termos-inscricao .termo-conteudo {
        color: #555;
        line-height: 1.6;
        font-size: 14px;
    }

    #termos-inscricao .termo-conteudo p {
        margin-bottom: 10px;
        color: #555;
    }

    #termos-inscricao .termo-conteudo strong {
        color: #333;
        font-weight: 600;
    }

    #termos-inscricao .termo-conteudo ul {
        margin-left: 20px;
        margin-bottom: 10px;
        color: #555;
    }

    #termos-inscricao .termo-conteudo li {
        color: #555;
    }

    #termos-inscricao .termo-conteudo ul {
        margin-left: 20px;
        margin-bottom: 10px;
        color: white !important;
    }

    .aceite-container {
        text-align: center;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 2px solid #e9ecef;
    }

    .checkbox-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        color: #333;
    }

    .checkbox-container input[type="checkbox"] {
        display: none;
    }

    .checkmark {
        width: 24px;
        height: 24px;
        border: 2px solid #ccc;
        border-radius: 4px;
        background: white;
        position: relative;
        transition: all 0.3s ease;
    }

    #termos-inscricao .checkbox-container input[type="checkbox"]:checked+.checkmark {
        background: #0b4340 !important;
        border-color: #0b4340 !important;
    }

    .checkbox-container input[type="checkbox"]:checked+.checkmark::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 16px;
        font-weight: bold;
    }

    .checkbox-container input[type="checkbox"]:disabled+.checkmark {
        background: #e9ecef;
        border-color: #dee2e6;
        cursor: not-allowed;
    }

    .aceite-texto {
        flex: 1;
        text-align: left;
    }

    .aceite-info {
        color: #6c757d;
        font-size: 13px;
    }

    .navegacao-etapas {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    /* Scrollbar personalizada */
    #termos-inscricao .termos-content::-webkit-scrollbar {
        width: 8px;
    }

    #termos-inscricao .termos-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    #termos-inscricao .termos-content::-webkit-scrollbar-thumb {
        background: #10B981 !important;
        border-radius: 4px;
    }

    #termos-inscricao .termos-content::-webkit-scrollbar-thumb:hover {
        background: #059669 !important;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .termos-container {
            padding: 20px;
        }

        .termos-content {
            max-height: 400px;
            padding: 15px;
        }

        .termo-secao {
            padding: 15px;
        }

        .checkbox-container {
            flex-direction: column;
            gap: 10px;
        }

        .aceite-texto {
            text-align: center;
        }
    }
</style>

<script>
    // Habilitar checkbox e botão quando o usuário interagir
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('aceiteTermos');
        const btnProsseguir = document.getElementById('btn-prosseguir');

        // Habilitar checkbox imediatamente
        checkbox.disabled = false;

        // Habilitar botão quando checkbox for marcado
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                btnProsseguir.disabled = false;
                btnProsseguir.classList.remove('bg-gray-200', 'text-gray-800', 'opacity-50', 'cursor-not-allowed');
                btnProsseguir.classList.add('bg-green-600', 'text-white', 'hover:bg-green-700', 'shadow-sm', 'hover:shadow-md');
            } else {
                btnProsseguir.disabled = true;
                btnProsseguir.classList.remove('bg-green-600', 'text-white', 'hover:bg-green-700', 'shadow-sm', 'hover:shadow-md');
                btnProsseguir.classList.add('bg-gray-200', 'text-gray-800', 'opacity-50', 'cursor-not-allowed');
            }
        });
    });
</script>
