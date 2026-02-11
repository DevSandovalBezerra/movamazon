<?php
// Debug: verificar se evento_id está disponível
if (!isset($evento_id) || empty($evento_id)) {
    error_log("ERRO: evento_id não está definido em modalidade.php");
    $evento_id = 1; // Fallback para teste
}

error_log("Modalidade.php - evento_id: " . $evento_id);

// Funções utilitárias para resolver URL de imagens de kit
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_path = $_SERVER['SCRIPT_NAME'] ?? '';
    $script_dir = $script_path ? dirname($script_path) : '';
    $pattern = '#/frontend/paginas/inscricao(?:/.*)?$#';
    $base_path = preg_replace($pattern, '', $script_dir, 1);
    $base_path = rtrim($base_path, '/');
    if ($base_path === '.' || $base_path === false) {
        $base_path = '';
    }

    return $protocol . '://' . $host . ($base_path === '' ? '' : $base_path);
}

function resolverNomeArquivoKit($foto_kit, $root_dir) {
    if (empty($foto_kit)) {
        return null;
    }

    // Se já é uma URL completa (http/https), retorna como está
    if (preg_match('#^https?://#i', $foto_kit)) {
        return $foto_kit;
    }

    // Extrai apenas o nome do arquivo
    $nome_original = basename(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $foto_kit));
    
    // Tenta verificar se arquivo existe no servidor
    $kit_dir = $root_dir . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'kits' . DIRECTORY_SEPARATOR;
    $caminho = $kit_dir . $nome_original;

    // Debug: log para diagnóstico (apenas em desenvolvimento)
    if (defined('DEBUG') && DEBUG) {
        error_log("[resolverNomeArquivoKit] foto_kit: $foto_kit");
        error_log("[resolverNomeArquivoKit] root_dir: $root_dir");
        error_log("[resolverNomeArquivoKit] caminho completo: $caminho");
        error_log("[resolverNomeArquivoKit] arquivo existe: " . (is_file($caminho) ? 'SIM' : 'NÃO'));
    }

    // Se arquivo existe, retorna o nome
    if (is_file($caminho)) {
        return $nome_original;
    }

    // Fallback: mesmo se is_file() falhar, retorna o nome do arquivo
    // O navegador tentará carregar e mostrará placeholder se não existir
    // Isso evita mostrar placeholder quando a imagem existe mas is_file() falha
    return $nome_original;
}

$base_url = getBaseUrl();
$root_dir = dirname(__DIR__, 3);

// Buscar modalidades disponíveis para o evento com preços dos lotes
// FILTRO: Apenas modalidades que possuem kit de evento associado serão exibidas
$stmt = $pdo->prepare("
    SELECT 
        m.id,
        m.nome as modalidade_nome,
        m.descricao,
        m.distancia,
        c.nome as categoria_nome,
        c.tipo_publico,
        li.id as lote_id,
        li.preco as preco_modalidade,
        li.numero_lote,
        li.data_inicio,
        li.data_fim,
        ke.id as kit_id,
        ke.nome as kit_nome,
        ke.foto_kit,
        ke.descricao as kit_descricao
    FROM modalidades m
    INNER JOIN categorias c ON m.categoria_id = c.id
    LEFT JOIN lotes_inscricao li ON m.id = li.modalidade_id 
        AND li.evento_id = ? 
        AND li.ativo = 1
    INNER JOIN kits_eventos ke ON ke.modalidade_evento_id = m.id 
        AND ke.evento_id = ? 
        AND ke.ativo = 1
    WHERE m.evento_id = ? 
    AND m.ativo = 1 
    ORDER BY c.nome, m.nome, li.numero_lote
");
$stmt->execute([$evento_id, $evento_id, $evento_id]);
$modalidades = $stmt->fetchAll();

// Debug: verificar se modalidades foram encontradas
error_log("Modalidades encontradas: " . count($modalidades));
foreach($modalidades as $mod) {
    error_log("Modalidade: " . $mod['modalidade_nome'] . " - Categoria: " . $mod['categoria_nome'] . " - Preço: " . ($mod['preco_modalidade'] ?? 'N/A') . " - Lote: " . ($mod['lote_id'] ?? 'N/A'));
}

// Buscar produtos dos kits encontrados
$kit_ids_raw = array_column($modalidades, 'kit_id');
$kit_ids_filtered = array_filter(array_unique($kit_ids_raw), function($id) {
    return !is_null($id) && $id !== '' && $id > 0;
});
$kit_ids = array_values($kit_ids_filtered); // Reindexar array
$produtos_por_kit = [];

if (!empty($kit_ids)) {
    $placeholders = implode(',', array_fill(0, count($kit_ids), '?'));
    $stmt_produtos = $pdo->prepare("
        SELECT 
            kp.kit_id,
            p.nome as produto_nome,
            kp.ordem
        FROM kit_produtos kp
        INNER JOIN produtos p ON kp.produto_id = p.id
        WHERE kp.kit_id IN ($placeholders) 
        AND kp.ativo = 1 
        AND p.ativo = 1
        ORDER BY kp.kit_id, kp.ordem ASC
    ");
    $stmt_produtos->execute($kit_ids);
    $produtos = $stmt_produtos->fetchAll(PDO::FETCH_ASSOC);
    
    // Agrupar por kit_id
    foreach ($produtos as $produto) {
        $produtos_por_kit[$produto['kit_id']][] = [
            'nome' => $produto['produto_nome'],
            'ordem' => $produto['ordem']
        ];
    }
}

// Adicionar produtos a cada modalidade
foreach ($modalidades as &$modalidade) {
    $kit_id = $modalidade['kit_id'] ?? null;
    $modalidade['produtos'] = $kit_id && isset($produtos_por_kit[$kit_id]) 
        ? $produtos_por_kit[$kit_id] 
        : [];
}
unset($modalidade); // Importante: remover referência
?>

<div class="modalidade-etapa">
    <h2 class="text-center mb-4">Escolha sua Modalidade</h2>
    <p class="text-center text-muted mb-4">Selecione a modalidade que deseja participar no evento</p>
    
    <form id="form-escolha-modalidade">
        <?php if (empty($modalidades)): ?>
            <div class="text-center py-12">
                <i class="fas fa-info-circle text-gray-400 text-5xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Nenhuma modalidade disponível</h3>
                <p class="text-gray-500">Não há modalidades com kit de evento configurado para este evento no momento.</p>
                <p class="text-sm text-gray-400 mt-2">Entre em contato com o organizador para mais informações.</p>
            </div>
        <?php else: ?>
        <div class="modalidades-grid">
            <?php foreach ($modalidades as $modalidade): ?>
                <div class="modalidade-card" 
                     data-modalidade-id="<?php echo $modalidade['id']; ?>"
                     data-lote-id="<?php echo $modalidade['lote_id'] ?? ''; ?>"
                     data-preco="<?php echo $modalidade['preco_modalidade'] ?? 0; ?>"
                     data-lote-numero="<?php echo $modalidade['numero_lote'] ?? ''; ?>"
                     data-data-fim-lote="<?php echo $modalidade['data_fim'] ?? ''; ?>">
                    <div class="modalidade-header">
                        <div class="modalidade-titulo">
                            <h3><?php echo htmlspecialchars($modalidade['modalidade_nome']); ?></h3>
                            <p class="modalidade-meta">
                                <?php 
                                $distancia = $modalidade['distancia'] ?? '';
                                if ($distancia) {
                                    $distancia = trim($distancia);
                                    if (stripos($distancia, 'km') === false) {
                                        $distancia .= ' km';
                                    }
                                    echo htmlspecialchars($distancia);
                                } else {
                                    echo 'Distância não definida';
                                }
                                ?> • 
                                <?php echo htmlspecialchars($modalidade['categoria_nome']); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="kit-section">
                        <div class="kit-image-wrapper">
                            <?php
                            $foto_kit = $modalidade['foto_kit'] ?? '';
                            $arquivo_kit = resolverNomeArquivoKit($foto_kit, $root_dir);
                            $kit_src = null;
                            
                            if ($arquivo_kit) {
                                // Se já é URL completa, usa diretamente
                                if (preg_match('#^https?://#i', $arquivo_kit)) {
                                    $kit_src = $arquivo_kit;
                                } else {
                                    // Constrói URL completa
                                    $kit_src = $base_url . '/frontend/assets/img/kits/' . rawurlencode($arquivo_kit);
                                }
                            }
                            ?>
                            <?php if ($kit_src): ?>
                                <img src="<?php echo htmlspecialchars($kit_src); ?>" 
                                     alt="<?php echo htmlspecialchars($modalidade['kit_nome']); ?>" 
                                     class="kit-image">
                            <?php else: ?>
                                <img src="<?php echo htmlspecialchars($base_url . '/frontend/assets/img/kits/placeholder.png'); ?>"
                                     alt="<?php echo htmlspecialchars($modalidade['kit_nome']); ?>"
                                     class="kit-image">
                            <?php endif; ?>
                        </div>
                        
                        <div class="kit-content">
                            <?php if (!empty($modalidade['produtos'])): ?>
                                <div class="kit-produtos">
                                    <?php foreach ($modalidade['produtos'] as $produto): ?>
                                        <span class="produto-badge"><?= htmlspecialchars($produto['nome']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="modalidade-footer">
                        <div class="preco-info">
                            <?php if ($modalidade['preco_modalidade'] && $modalidade['preco_modalidade'] > 0): ?>
                                <div class="preco-valor">
                                    R$ <?php echo number_format($modalidade['preco_modalidade'], 2, ',', '.'); ?>
                                </div>
                                <div class="preco-detalhes">
                                    <span class="tipo-publico"><?php echo ucfirst(str_replace('_', ' ', $modalidade['tipo_publico'])); ?></span>
                                    <?php if ($modalidade['numero_lote']): ?>
                                        <span class="lote-info">Lote <?php echo $modalidade['numero_lote']; ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="preco-indisponivel">Preço não disponível</div>
                            <?php endif; ?>
                        </div>
                        <input type="radio" 
                               name="modalidade_id" 
                               value="<?php echo $modalidade['id']; ?>" 
                               class="modalidade-radio"
                               required>
                    </div>
            </div>
                    <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($modalidades)): ?>
        <div class="text-center mt-4">
            <button type="button" id="btn-prosseguir-modalidade" class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition-all duration-200 shadow-sm hover:shadow-md text-lg">
                <i class="fas fa-arrow-right"></i> Continuar
            </button>
        </div>
        <?php endif; ?>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Event listeners para cards de modalidade
    const modalidadeCards = document.querySelectorAll('.modalidade-card');
    modalidadeCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remover seleção anterior
            modalidadeCards.forEach(c => c.classList.remove('selected'));
            
            // Selecionar card atual
            this.classList.add('selected');
            
            // Marcar radio button
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
        });
    });
    
    // Event listener para botão continuar
    document.getElementById('btn-prosseguir-modalidade').addEventListener('click', function() {
        const modalidadeSelecionada = document.querySelector('input[name="modalidade_id"]:checked');
        
        if (!modalidadeSelecionada) {
            Swal.fire({
                icon: 'warning',
                title: 'Selecione uma modalidade',
                text: 'Por favor, escolha uma modalidade para continuar!'
            });
            return;
        }
        
        // Salvar modalidade selecionada na sessão
        const modalidadeId = modalidadeSelecionada.value;
        const modalidadeCard = modalidadeSelecionada.closest('.modalidade-card');
        const modalidadeNome = modalidadeCard.querySelector('h3').textContent;
        
        // ✅ Buscar dados do dataset do card
        const precoElement = modalidadeCard.querySelector('.preco-valor');
        const loteId = modalidadeCard.dataset.loteId;
        const precoNumerico = parseFloat(modalidadeCard.dataset.preco) || 0;
        const loteNumero = modalidadeCard.dataset.loteNumero;
        const dataFimLote = modalidadeCard.dataset.dataFimLote;
        
        // Debug
        console.log('DEBUG modalidade:', {
            modalidadeId,
            modalidadeNome,
            loteId,
            precoNumerico,
            loteNumero,
            dataFimLote
        });
        
        // ✅ Validação de preço
        if (precoNumerico <= 0 || !loteId) {
            Swal.fire({
                icon: 'error',
                title: 'Preço inválido',
                text: 'Esta modalidade não possui preço válido'
            });
            return;
        }
        
        // ✅ Verificar se lote ainda está válido (comentado temporariamente para teste)
        // if (dataFimLote && new Date(dataFimLote) < new Date()) {
        //     Swal.fire({
        //         icon: 'error',
        //         title: 'Lote expirado',
        //         text: 'Este lote de inscrição já expirou'
        //     });
        //     return;
        // }
        
        // Atualizar sessão via AJAX
        fetch('salvar_modalidade.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                modalidade_id: modalidadeId,
                modalidade_nome: modalidadeNome,
                preco_total: precoNumerico, // ✅ Numérico
                lote_id: loteId,
                lote_numero: loteNumero,
                data_fim_lote: dataFimLote
            })
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                // Prosseguir para próxima etapa
                prosseguirEtapa();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message || 'Erro ao salvar modalidade'
                });
            }
        }).catch(error => {
            console.error('Erro:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro de conexão',
                text: 'Não foi possível conectar ao servidor'
            });
        });
    });
});
</script>

<style>
.preco-total {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
}

.preco-detalhado {
    margin-top: 5px;
    font-size: 0.85rem;
}

.preco-detalhado small {
    color: #6c757d;
}
</style>
