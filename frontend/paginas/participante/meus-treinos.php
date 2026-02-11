<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /frontend/auth/login.php');
    exit();
}

require_once __DIR__ . '/../../../api/db.php';
require_once __DIR__ . '/../../../api/helpers/config_helper.php';

$usuario_id = $_SESSION['user_id'];
$inscricoes_com_dados = [];

try {
    // Verificar configuração de exigência de inscrição
    $exigir_inscricao = ConfigHelper::get('treino.exigir_inscricao', true);
    
    if ($exigir_inscricao) {
        // MODO PRODUÇÃO: Buscar apenas inscrições confirmadas
        error_log('[MEUS_TREINOS] Modo PRODUÇÃO: buscando inscrições confirmadas');
        $sql = "
            SELECT 
                i.id as inscricao_id,
                i.numero_inscricao,
                i.status,
                i.status_pagamento,
                e.id as evento_id,
                e.nome as evento_nome,
                COALESCE(e.data_realizacao, e.data_inicio) as evento_data,
                e.local as evento_local,
                e.imagem as evento_imagem,
                m.nome as modalidade_nome,
                k.nome as kit_nome
            FROM inscricoes i
            JOIN eventos e ON i.evento_id = e.id
            JOIN modalidades m ON i.modalidade_evento_id = m.id
            LEFT JOIN kits_eventos k ON i.kit_id = k.id
            WHERE i.usuario_id = ? 
            AND (i.status = 'confirmada' OR i.status_pagamento = 'pago')
            ORDER BY COALESCE(e.data_realizacao, e.data_inicio) DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $inscricoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // REGRA PROVISÓRIA: Exigir inscrição desativado
        error_log('[MEUS_TREINOS] ⚠️ REGRA PROVISÓRIA: Inscrição não exigida para gerar treino');
        $inscricoes = [[
            'inscricao_id' => 999,
            'numero_inscricao' => 'PROV-001',
            'status' => 'pendente',
            'status_pagamento' => 'pendente',
            'evento_id' => 999,
            'evento_nome' => 'Evento de Preparação',
            'evento_data' => date('Y-m-d', strtotime('+90 days')),
            'evento_local' => 'Local a definir',
            'evento_imagem' => null,
            'modalidade_nome' => '10km',
            'kit_nome' => 'Kit Padrão'
        ]];
    }

    // Para cada inscrição, verificar anamnese e treino
    foreach ($inscricoes as $inscricao) {
        $inscricao_id = $inscricao['inscricao_id'];
        
        // Verificar se existe anamnese específica para a inscrição OU anamnese geral do usuário
        $stmt_anamnese = $pdo->prepare("
            SELECT id FROM anamneses 
            WHERE usuario_id = ? 
            AND (inscricao_id = ? OR inscricao_id IS NULL)
            ORDER BY 
                CASE WHEN inscricao_id = ? THEN 1 ELSE 2 END,
                data_anamnese DESC
            LIMIT 1
        ");
        $stmt_anamnese->execute([$usuario_id, $inscricao_id, $inscricao_id]);
        $tem_anamnese = $stmt_anamnese->fetch() !== false;
        
        // Verificar se existe treino gerado
        $stmt_treino = $pdo->prepare("
            SELECT id FROM planos_treino_gerados 
            WHERE usuario_id = ? AND inscricao_id = ?
            LIMIT 1
        ");
        $stmt_treino->execute([$usuario_id, $inscricao_id]);
        $tem_treino = $stmt_treino->fetch() !== false;
        
        $inscricao['tem_anamnese'] = $tem_anamnese;
        $inscricao['tem_treino'] = $tem_treino;
        
        $inscricoes_com_dados[] = $inscricao;
    }
} catch (Exception $e) {
    error_log("Erro ao buscar inscrições para treinos: " . $e->getMessage());
    $inscricoes_com_dados = [];
}
?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <?php if (!ConfigHelper::get('treino.exigir_inscricao', true)): ?>
    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <strong>REGRA PROVISÓRIA ATIVA:</strong> Inscrição não é exigida para gerar treino no momento.
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 sm:mb-6 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold mb-2">Meus Treinos de Preparação</h1>
            <p class="text-gray-600">Preencha sua anamnese e receba um treino personalizado para cada corrida</p>
        </div>
        <?php if (!empty($inscricoes_com_dados) && $inscricoes_com_dados[0]['tem_anamnese'] && !$inscricoes_com_dados[0]['tem_treino']): ?>
        <button id="btn-gerar-ultima-corrida" class="bg-brand-green text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2 touch-target" onclick="gerarTreinoParaInscricao(<?php echo $inscricoes_com_dados[0]['inscricao_id']; ?>, event)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Gerar Treino para Última Corrida
        </button>
        <?php endif; ?>
    </div>

    <?php if (empty($inscricoes_com_dados)): ?>
    <div class="text-center py-16 bg-white rounded-lg shadow-lg">
        <div class="max-w-md mx-auto">
            <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Nenhuma inscrição confirmada</h3>
            <p class="text-gray-600 mb-6">Você precisa ter pelo menos uma inscrição confirmada para gerar um treino.</p>
            <a href="/frontend/paginas/public/index.php" class="inline-block bg-brand-green text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition-colors">
                Explorar Eventos
            </a>
        </div>
    </div>
    <?php else: ?>
        <?php 
        $ultimaInscricao = $inscricoes_com_dados[0];
        $dataEvento = new DateTime($ultimaInscricao['evento_data']);
        $dataFormatada = $dataEvento->format('d/m/Y');
        ?>
        
        <?php if ($ultimaInscricao['tem_treino']): ?>
        <div class="mb-6">
            <div class="bg-gradient-to-r from-purple-50 to-blue-50 border-2 border-purple-500 rounded-lg shadow-lg p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
                    <div class="flex-grow">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-purple-600 text-white">
                                TREINO DISPONÍVEL
                            </span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($ultimaInscricao['evento_nome']); ?></h3>
                        <p class="text-base sm:text-lg text-gray-700 mb-1"><?php echo htmlspecialchars($ultimaInscricao['modalidade_nome']); ?></p>
                        <p class="text-sm text-gray-600 mb-4"><?php echo $dataFormatada; ?> - <?php echo htmlspecialchars($ultimaInscricao['evento_local'] ?? ''); ?></p>
                        <div class="flex gap-2 mb-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                ✓ Anamnese Preenchida
                            </span>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                ✓ Treino Gerado
                            </span>
                        </div>
                        <p class="text-sm text-purple-700 mb-4">
                            <strong>Seu treino personalizado está pronto!</strong> Clique no botão abaixo para visualizar.
                        </p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <a href="?page=ver-treino&inscricao_id=<?php echo $ultimaInscricao['inscricao_id']; ?>" 
                           class="bg-purple-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-purple-700 transition-colors text-center whitespace-nowrap touch-target">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Ver Meu Treino
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif (!$ultimaInscricao['tem_treino']): ?>
        <div class="mb-6">
            <div class="bg-gradient-to-r from-green-50 to-blue-50 border-2 border-brand-green rounded-lg shadow-lg p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
                    <div class="flex-grow">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-brand-green text-white">
                                ÚLTIMA CORRIDA INSCRITA
                            </span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($ultimaInscricao['evento_nome']); ?></h3>
                        <p class="text-base sm:text-lg text-gray-700 mb-1"><?php echo htmlspecialchars($ultimaInscricao['modalidade_nome']); ?></p>
                        <p class="text-sm text-gray-600 mb-4"><?php echo $dataFormatada; ?> - <?php echo htmlspecialchars($ultimaInscricao['evento_local'] ?? ''); ?></p>
                        <div class="flex gap-2 mb-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $ultimaInscricao['tem_anamnese'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo $ultimaInscricao['tem_anamnese'] ? '✓ Anamnese Preenchida' : '⚠ Anamnese Pendente'; ?>
                            </span>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                <?php echo $ultimaInscricao['tem_treino'] ? '✓ Treino Gerado' : 'Aguardando Geração'; ?>
                            </span>
                        </div>
                        <?php if (!$ultimaInscricao['tem_anamnese']): ?>
                            <p class="text-sm text-yellow-700 mb-4">
                                <strong>Próximo passo:</strong> Preencha sua anamnese para gerar um treino personalizado.
                            </p>
                        <?php elseif (!$ultimaInscricao['tem_treino']): ?>
                            <p class="text-sm text-green-700 mb-4">
                                <strong>Pronto para gerar!</strong> Sua anamnese está completa. Clique no botão abaixo para gerar seu treino personalizado.
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col gap-2">
                        <?php if (!$ultimaInscricao['tem_anamnese']): ?>
                            <a href="?page=anamnese&inscricao_id=<?php echo $ultimaInscricao['inscricao_id']; ?>" 
                               class="bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors text-center whitespace-nowrap touch-target">
                                Preencher Anamnese
                            </a>
                        <?php endif; ?>
                        <?php if ($ultimaInscricao['tem_anamnese'] && !$ultimaInscricao['tem_treino']): ?>
                            <button onclick="gerarTreinoParaInscricao(<?php echo $ultimaInscricao['inscricao_id']; ?>, event)" 
                                    class="bg-brand-green text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition-colors whitespace-nowrap touch-target">
                                <span class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Gerar Treino Agora
                                </span>
                            </button>
                        <?php endif; ?>
                        <?php if ($ultimaInscricao['tem_treino']): ?>
                            <a href="?page=ver-treino&inscricao_id=<?php echo $ultimaInscricao['inscricao_id']; ?>" 
                               class="bg-purple-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-purple-700 transition-colors text-center whitespace-nowrap touch-target">
                                Ver Meu Treino
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php 
        $inscricoes_com_treino = array_filter($inscricoes_com_dados, function($i) { return $i['tem_treino']; });
        if (!empty($inscricoes_com_treino)): 
        ?>
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Treinos Gerados</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($inscricoes_com_treino as $inscricao): ?>
                    <?php 
                    $dataEvento = new DateTime($inscricao['evento_data']);
                    $dataFormatada = $dataEvento->format('d/m/Y');
                    ?>
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 hover:shadow-lg transition-shadow border-l-4 border-purple-500">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-grow">
                                <h3 class="text-lg font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($inscricao['evento_nome']); ?></h3>
                                <p class="text-sm text-gray-600 mb-1"><?php echo htmlspecialchars($inscricao['modalidade_nome']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo $dataFormatada; ?></p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                ✓ Treino Disponível
                            </span>
                        </div>
                        <a href="?page=ver-treino&inscricao_id=<?php echo $inscricao['inscricao_id']; ?>" 
                           class="block w-full bg-purple-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors text-center touch-target">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Ver Treino Completo
                            </span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (count($inscricoes_com_dados) > 1): ?>
        <div class="space-y-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Outras Corridas</h2>
            <?php foreach (array_slice($inscricoes_com_dados, 1) as $inscricao): ?>
                <?php 
                $dataEvento = new DateTime($inscricao['evento_data']);
                $dataFormatada = $dataEvento->format('d/m/Y');
                ?>
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 hover:shadow-lg transition-shadow">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex-grow">
                            <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($inscricao['evento_nome']); ?></h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($inscricao['modalidade_nome']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo $dataFormatada; ?> - <?php echo htmlspecialchars($inscricao['evento_local'] ?? ''); ?></p>
                            <div class="mt-3 flex gap-2">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $inscricao['tem_anamnese'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $inscricao['tem_anamnese'] ? '✓ Anamnese' : '⚠ Sem Anamnese'; ?>
                                </span>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $inscricao['tem_treino'] ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $inscricao['tem_treino'] ? '✓ Treino Gerado' : 'Sem Treino'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <?php if (!$inscricao['tem_anamnese']): ?>
                                <a href="?page=anamnese&inscricao_id=<?php echo $inscricao['inscricao_id']; ?>" 
                                   class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors touch-target">
                                    Preencher Anamnese
                                </a>
                            <?php endif; ?>
                            <?php if ($inscricao['tem_anamnese'] && !$inscricao['tem_treino']): ?>
                                <button onclick="gerarTreinoParaInscricao(<?php echo $inscricao['inscricao_id']; ?>, event)" 
                                        class="bg-green-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-700 transition-colors touch-target">
                                    Gerar Treino
                                </button>
                            <?php endif; ?>
                            <?php if ($inscricao['tem_treino']): ?>
                                <a href="?page=ver-treino&inscricao_id=<?php echo $inscricao['inscricao_id']; ?>" 
                                   class="bg-purple-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors touch-target">
                                    Ver Treino
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script type="module">
    import { gerarTreino } from '../../js/participante/treinos.js';

    window.gerarTreinoParaInscricao = async function(inscricaoId, event) {
        const button = event ? event.target.closest('button') : null;
        const originalText = button ? button.innerHTML : 'Gerar Treino';
        const originalDisabled = button ? button.disabled : false;
        
        if (button) {
            button.disabled = true;
            button.innerHTML = '<span class="flex items-center gap-2"><span class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span> Gerando...</span>';
        }

        try {
            if (typeof Swal !== 'undefined') {
                const confirmResult = await Swal.fire({
                    icon: 'question',
                    title: 'Gerar Treino Personalizado?',
                    text: 'Isso pode levar alguns segundos. Deseja continuar?',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, Gerar Treino',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#10b981'
                });

                if (!confirmResult.isConfirmed) {
                    if (button) {
                        button.disabled = originalDisabled;
                        button.innerHTML = originalText;
                    }
                    return;
                }

                Swal.fire({
                    icon: 'info',
                    title: 'Gerando seu treino...',
                    text: 'Por favor, aguarde. Isso pode levar alguns segundos.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            } else if (!confirm('Deseja gerar um treino personalizado para esta corrida? Isso pode levar alguns segundos.')) {
                if (button) {
                    button.disabled = originalDisabled;
                    button.innerHTML = originalText;
                }
                return;
            }

            const resultado = await gerarTreino(inscricaoId);
            
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }

            if (resultado.success) {
                if (typeof Swal !== 'undefined') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Treino Gerado!',
                        text: 'Seu treino personalizado foi criado com sucesso.',
                        confirmButtonText: 'Ver Treino',
                        confirmButtonColor: '#10b981'
                    });
                    window.location.reload();
                } else {
                    alert('Treino gerado com sucesso!');
                    window.location.reload();
                }
            } else {
                const mensagem = resultado.message || 'Erro desconhecido ao gerar treino';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro ao Gerar Treino',
                        text: mensagem,
                        confirmButtonColor: '#ef4444'
                    });
                } else {
                    alert('Erro ao gerar treino: ' + mensagem);
                }
                if (button) {
                    button.disabled = originalDisabled;
                    button.innerHTML = originalText;
                }
            }
        } catch (error) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            const mensagem = error.message || 'Erro desconhecido';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao Gerar Treino',
                    text: mensagem,
                    confirmButtonColor: '#ef4444'
                });
            } else {
                alert('Erro ao gerar treino: ' + mensagem);
            }
            if (button) {
                button.disabled = originalDisabled;
                button.innerHTML = originalText;
            }
        }
    };
</script>