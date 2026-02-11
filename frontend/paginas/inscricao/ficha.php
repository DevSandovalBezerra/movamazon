<?php
// Obter evento_id da sessão
$evento_id = $_SESSION['inscricao']['evento_id'] ?? null;

if (!$evento_id) {
    echo '<div class="alert alert-danger">Erro: Evento não especificado</div>';
    return;
}

// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Erro: Usuário não logado</div>';
    return;
}

// Buscar dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch();

if (!$usuario) {
    echo '<div class="alert alert-danger">Erro: Dados do usuário não encontrados</div>';
    return;
}

// Buscar modalidades selecionadas
$modalidades_selecionadas = $_SESSION['inscricao']['modalidades_selecionadas'] ?? [];

// ✅ Calcular total das modalidades
$total_modalidades = 0;
if (!empty($modalidades_selecionadas)) {
    foreach ($modalidades_selecionadas as $modalidade) {
        $preco = floatval($modalidade['preco_total'] ?? 0);
        $total_modalidades += $preco;
    }
}

// Buscar questionários do evento (com classificação)
$stmt = $pdo->prepare("
    SELECT 
        qe.id,
        qe.texto,
        qe.tipo,
        qe.tipo_resposta,
        qe.classificacao,
        qe.obrigatorio,
        qe.ordem
    FROM questionario_evento qe
    WHERE qe.evento_id = ? 
    AND qe.ativo = 1 
    AND qe.status_site = 'publicada'
    ORDER BY qe.classificacao DESC, qe.ordem, qe.id
");
$stmt->execute([$evento_id]);
$questionarios = $stmt->fetchAll();

// Separar perguntas por classificação
$perguntas_atleta = array_filter($questionarios, function($q) {
    return ($q['classificacao'] ?? 'evento') === 'atleta';
});
$perguntas_evento = array_filter($questionarios, function($q) {
    return ($q['classificacao'] ?? 'evento') === 'evento';
});

// Buscar produtos extras disponíveis para o evento
$stmt = $pdo->prepare("
    SELECT 
        pe.id,
        pe.nome,
        pe.descricao,
        pe.valor,
        pe.categoria
    FROM produtos_extras pe
    WHERE pe.evento_id = ? 
      AND pe.ativo = 1
    ORDER BY pe.nome
");
$stmt->execute([$evento_id]);
$produtos_extras = $stmt->fetchAll();

// Função para mascarar CPF com asteriscos para privacidade
function mascararCPF($cpf)
{
    // Limpar formatação se houver
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) == 11) {
        // Formato: 123.***.***-45 (primeiros 3 + asteriscos + últimos 2)
        return substr($cpf, 0, 3) . '.***.***-' . substr($cpf, -2);
    }
    return $cpf ? '***.***.***-**' : 'N/A';
}

// Função para renderizar campo de questionário
function renderizarCampoQuestionario($pergunta) {
    $html = '';
    $required = $pergunta['obrigatorio'] ? 'required' : '';
    $id = $pergunta['id'];
    
    switch ($pergunta['tipo_resposta']) {
        case 'radio':
            $html = '<div class="resposta-radio">
                <label class="radio-option">
                    <input type="radio" name="pergunta_' . $id . '" value="sim" ' . $required . '>
                    <span class="radio-label">SIM</span>
                </label>
                <label class="radio-option">
                    <input type="radio" name="pergunta_' . $id . '" value="nao" ' . $required . '>
                    <span class="radio-label">NÃO</span>
                </label>
            </div>';
            break;

        case 'texto_aberto':
            $html = '<input type="text" class="form-control" name="pergunta_' . $id . '" placeholder="Digite sua resposta" ' . $required . '>';
            break;

        case 'textarea':
            $html = '<textarea class="form-control" name="pergunta_' . $id . '" rows="3" placeholder="Digite sua resposta" ' . $required . '></textarea>';
            break;

        case 'checkbox':
            $html = '<div class="resposta-checkbox">
                <label class="checkbox-option">
                    <input type="checkbox" name="pergunta_' . $id . '[]" value="sim" ' . $required . '>
                    <span class="checkbox-label">SIM</span>
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="pergunta_' . $id . '[]" value="nao" ' . $required . '>
                    <span class="checkbox-label">NÃO</span>
                </label>
            </div>';
            break;

        case 'dropdown':
            $html = '<select class="form-control" name="pergunta_' . $id . '" ' . $required . '>
                <option value="">Selecione uma opção</option>
                <option value="sim">SIM</option>
                <option value="nao">NÃO</option>
            </select>';
            break;

        default:
            $html = '<input type="text" class="form-control" name="pergunta_' . $id . '" placeholder="Digite sua resposta" ' . $required . '>';
            break;
    }
    
    return $html;
}
?>

<div class="container-fluid" id="ficha-inscricao">
    <div class="row">
        <div class="col-12">
            <div class="inscricao-header mb-4">
                <h2 class="text-center mb-3">Ficha de Inscrição</h2>
                <p class="text-center text-muted">Confirme seus dados e responda o questionário</p>
            </div>

            <div class="row">
                <!-- Coluna Esquerda: Dados e Questionário -->
                <div class="col-lg-8">
                    <!-- Resumo da Modalidade -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-route"></i>
                                Modalidades Selecionadas
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($modalidades_selecionadas)): ?>
                                <?php foreach ($modalidades_selecionadas as $modalidade):
                                    $preco = floatval($modalidade['preco_total'] ?? 0);
                                    $preco_formatado = 'R$ ' . number_format($preco, 2, ',', '.');
                                ?>
                                    <div class="modalidade-resumo-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($modalidade['nome'] ?? 'N/A'); ?></strong>
                                                <span class="badge badge-primary ml-2">Modalidade</span>
                                            </div>
                                            <span class="preco-modalidade"><?php echo $preco_formatado; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Total Inscrição:</strong>
                                    <strong class="text-primary">R$ <?php echo number_format($total_modalidades, 2, ',', '.'); ?></strong>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Nenhuma modalidade selecionada</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Dados do Participante -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user text-success"></i>
                                Dados do Participante
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome_completo'] ?? 'N/A'); ?></p>
                                    <p><strong>E-mail:</strong> <?php echo htmlspecialchars($usuario['email'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>CPF:</strong> <?php echo mascararCPF($usuario['documento'] ?? ''); ?></p>
                                    <p><strong>Data Nasc:</strong> <?php echo isset($usuario['data_nascimento']) ? date('d/m/Y', strtotime($usuario['data_nascimento'])) : 'N/A'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Questionário do Evento - Separado por Classificação -->
                    <form id="questionarioForm">
                        <?php if (empty($questionarios)): ?>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle"></i>
                                        Nenhuma pergunta disponível para este evento.
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            
                            <!-- DADOS DO ATLETA -->
                            <?php if (!empty($perguntas_atleta)): ?>
                            <div class="card mb-4 border-l-4" style="border-left: 4px solid #10b981 !important;">
                                <div class="card-header" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-bottom: 2px solid #10b981;">
                                    <h5 class="mb-0 flex items-center gap-2" style="color: #065f46;">
                                        <i class="fas fa-user-alt" style="color: #10b981;"></i>
                                        Dados do Atleta
                                        <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full" style="background: #10b981; color: white;">
                                            <?php echo count($perguntas_atleta); ?> pergunta<?php echo count($perguntas_atleta) > 1 ? 's' : ''; ?>
                                        </span>
                                    </h5>
                                    <p class="text-sm mt-1" style="color: #047857;">Informações pessoais e de contato de emergência</p>
                                </div>
                                <div class="card-body">
                                    <div class="questionario-grid">
                                        <?php foreach ($perguntas_atleta as $pergunta): ?>
                                            <div class="pergunta-card" style="border-left: 3px solid #10b981; background: linear-gradient(to right, #f0fdf4, white);">
                                                <label class="pergunta-label">
                                                    <?php echo htmlspecialchars($pergunta['texto']); ?>
                                                    <?php if ($pergunta['obrigatorio']): ?>
                                                        <span class="text-danger">*</span>
                                                    <?php endif; ?>
                                                </label>
                                                <?php echo renderizarCampoQuestionario($pergunta); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- SOBRE O EVENTO -->
                            <?php if (!empty($perguntas_evento)): ?>
                            <div class="card mb-4 border-l-4" style="border-left: 4px solid #3b82f6 !important;">
                                <div class="card-header" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-bottom: 2px solid #3b82f6;">
                                    <h5 class="mb-0 flex items-center gap-2" style="color: #1e40af;">
                                        <i class="fas fa-calendar-alt" style="color: #3b82f6;"></i>
                                        Sobre o Evento
                                        <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full" style="background: #3b82f6; color: white;">
                                            <?php echo count($perguntas_evento); ?> pergunta<?php echo count($perguntas_evento) > 1 ? 's' : ''; ?>
                                        </span>
                                    </h5>
                                    <p class="text-sm mt-1" style="color: #1d4ed8;">Perguntas sobre sua participação e conhecimento do evento</p>
                                </div>
                                <div class="card-body">
                                    <div class="questionario-grid">
                                        <?php foreach ($perguntas_evento as $pergunta): ?>
                                            <div class="pergunta-card" style="border-left: 3px solid #3b82f6; background: linear-gradient(to right, #eff6ff, white);">
                                                <label class="pergunta-label">
                                                    <?php echo htmlspecialchars($pergunta['texto']); ?>
                                                    <?php if ($pergunta['obrigatorio']): ?>
                                                        <span class="text-danger">*</span>
                                                    <?php endif; ?>
                                                </label>
                                                <?php echo renderizarCampoQuestionario($pergunta); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                        <?php endif; ?>
                    </form>

                    <!-- Seleção de Tamanho de Camiseta -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-tshirt text-warning"></i>
                                Seleção de Tamanho de Camiseta
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="tamanho-dropdown-container">
                                <label for="tamanho_camiseta" class="form-label">
                                    <i class="fas fa-ruler text-muted me-2"></i>
                                    Escolha o tamanho da sua camiseta
                                </label>
                                <select class="form-select tamanho-select"
                                    id="tamanho_camiseta"
                                    name="tamanho_camiseta"
                                    required>
                                    <option value="">Selecione o tamanho</option>
                                    <?php
                                    $tamanhos = [
                                        'PP' => 'PP - Extra Pequeno',
                                        'P' => 'P - Pequeno',
                                        'M' => 'M - Médio',
                                        'G' => 'G - Grande',
                                        'GG' => 'GG - Extra Grande',
                                        'XG' => 'XG - Extra Extra Grande'
                                    ];
                                    foreach ($tamanhos as $valor => $texto):
                                    ?>
                                        <option value="<?php echo $valor; ?>"><?php echo $texto; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Consulte a tabela de medidas na descrição do evento
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Direita: Resumo e Cupom -->
                <div class="col-lg-4">
                    <!-- Cupom de Desconto -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-tag text-success"></i>
                                Cupom de Desconto
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="input-group">
                                <input type="text"
                                    class="form-control"
                                    id="cupomCodigo"
                                    placeholder="Digite o código do cupom">
                                <div class="input-group-append">
                                    <button class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 transition-all duration-200 border border-green-600" type="button" onclick="aplicarCupom()">
                                        <i class="fas fa-check"></i> Aplicar
                                    </button>
                                </div>
                            </div>
                            <div id="cupomResultado" class="mt-2"></div>
                        </div>
                    </div>

                    <!-- Produtos Extras -->
                    <?php if (!empty($produtos_extras)): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-plus-circle text-info"></i>
                                    Produtos Adicionais
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($produtos_extras as $produto): ?>
                                    <div class="produto-extra-item mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($produto['nome']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($produto['descricao']); ?></small>
                                            </div>
                                            <div class="text-right">
                                                <span class="preco-produto">R$ <?php echo number_format($produto['valor'], 2, ',', '.'); ?></span>
                                                <br>
                                                <button class="bg-blue-600 text-white px-3 py-1 rounded-lg text-sm font-semibold hover:bg-blue-700 transition-all duration-200 border border-blue-600 mt-1"
                                                    onclick="adicionarProdutoExtra(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>', <?php echo $produto['valor']; ?>)">
                                                    <i class="fas fa-plus"></i> Adicionar
                                                </button>
                                            </div>
                                        </div>
                                        <!-- Imagem do produto removida temporariamente - estrutura da tabela produtos_extras não possui foto_produto -->
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Resumo da Compra -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calculator"></i>
                                Resumo da Compra
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="resumoCompra">
                                <!-- Será preenchido via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="navegacao-etapas mt-4">
                <div class="flex justify-between">
                    <button class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-all duration-200" onclick="voltarEtapa()">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-all duration-200 shadow-sm hover:shadow-md" onclick="console.log('[FICHA] onclick Próximo'); if (typeof salvarFicha === 'function') { salvarFicha(); } else { console.error('[FICHA] salvarFicha não definida'); }" id="btn-prosseguir">
                        Próximo <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos específicos para página de ficha - maior especificidade */
    #ficha-inscricao .inscricao-header,
    .inscricao-container .inscricao-wrapper .inscricao-header {
        background: linear-gradient(135deg, #0b4340 0%, #10B981 100%) !important;
        color: white !important;
        padding: 30px !important;
        border-radius: 15px !important;
        margin-bottom: 30px !important;
    }

    #ficha-inscricao .inscricao-header h2,
    .inscricao-container .inscricao-wrapper .inscricao-header h2 {
        color: white !important;
    }

    #ficha-inscricao .inscricao-header .text-muted,
    .inscricao-container .inscricao-wrapper .inscricao-header .text-muted {
        color: rgba(255, 255, 255, 0.9) !important;
    }

    #ficha-inscricao .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    #ficha-inscricao .card-header {
        background: linear-gradient(135deg, #0b4340 0%, #10B981 100%) !important;
        border-bottom: 2px solid #0b4340 !important;
        border-radius: 15px 15px 0 0 !important;
        padding: 20px !important;
        color: white !important;
    }

    #ficha-inscricao .card-header h5 {
        margin: 0 !important;
        color: white !important;
        font-weight: 600 !important;
    }

    #ficha-inscricao .card-header i {
        color: white !important;
        margin-right: 10px;
    }

    .card-body {
        padding: 25px;
        background: white;
    }

    .card-body p,
    .card-body strong {
        color: #333;
    }

    .modalidade-resumo-item {
        padding: 10px 0;
        border-bottom: 1px solid #f1f1f1;
    }

    .modalidade-resumo-item:last-child {
        border-bottom: none;
    }

    .preco-modalidade {
        font-weight: 600;
        color: #0b4340;
        font-size: 16px;
    }

    .text-primary {
        color: #0b4340 !important;
    }

    /* Estilos para Dropdown de Tamanho de Camiseta */
    .tamanho-dropdown-container {
        max-width: 400px;
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
        display: block;
    }

    .tamanho-select {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 15px 20px;
        font-size: 16px;
        font-weight: 500;
        background: white;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .tamanho-select:focus {
        border-color: #0b4340;
        box-shadow: 0 0 0 3px rgba(11, 67, 64, 0.1);
        outline: none;
    }

    .tamanho-select:hover {
        border-color: #0b4340;
        box-shadow: 0 4px 8px rgba(11, 67, 64, 0.1);
    }

    .form-text {
        font-size: 14px;
        color: #6c757d;
        margin-top: 8px;
        display: flex;
        align-items: center;
    }

    .produto-extra-item {
        padding: 15px;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        background: #f8f9fa;
    }

    .preco-produto {
        font-weight: 600;
        color: #0b4340;
        font-size: 16px;
    }

    #cupomResultado {
        min-height: 20px;
    }

    .cupom-sucesso {
        color: #0b4340;
        font-weight: 600;
    }

    .cupom-erro {
        color: #dc3545;
        font-weight: 600;
    }

    .navegacao-etapas {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .tamanho-dropdown-container {
            max-width: 100%;
        }

        .tamanho-select {
            padding: 12px 16px;
            font-size: 14px;
        }

        .produto-extra-item {
            text-align: center;
        }

        .produto-extra-item .d-flex {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>

<script src="../../js/inscricao/ficha.js"></script>
<script>
    // Variáveis globais (cupom é gerenciado exclusivamente por EtapaFicha em ficha.js)
    window.produtosExtrasSelecionados = [];

    // Única lógica de cupom: ficha.js (EtapaFicha). Botão "Aplicar" chama esta função.
    window.aplicarCupom = function() {
        if (window.etapaFicha && typeof window.etapaFicha.aplicarCupom === 'function') {
            window.etapaFicha.aplicarCupom();
        } else {
            var el = document.getElementById('cupomResultado');
            if (el) el.innerHTML = '<small class="cupom-erro">Recarregue a página para aplicar o cupom.</small>';
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof EtapaFicha !== 'undefined') {
            window.etapaFicha = new EtapaFicha();
        }
        atualizarResumoCompra();

        const tamanhoSelect = document.getElementById('tamanho_camiseta');
        if (tamanhoSelect) {
            tamanhoSelect.addEventListener('change', function() {
                atualizarResumoCompra();
            });
        }
    });

    // Adicionar produto extra
    function adicionarProdutoExtra(id, nome, valor) {
        const produto = {
            id: parseInt(id),
            nome: String(nome),
            valor: parseFloat(valor)
        };
        window.produtosExtrasSelecionados.push(produto);

        // Debug detalhado
        console.log('[FICHA] ✅ Produto extra adicionado:', produto);
        console.log('[FICHA] Lista completa de produtos extras:', window.produtosExtrasSelecionados);
        console.log('[FICHA] Total de produtos:', window.produtosExtrasSelecionados.length);

        atualizarResumoCompra();

        // Atualizar botão
        const botao = event.target;
        botao.innerHTML = '<i class="fas fa-check"></i> Adicionado';
        botao.classList.remove('bg-blue-600', 'border-blue-600', 'hover:bg-blue-700');
        botao.classList.add('bg-green-600', 'border-green-600', 'hover:bg-green-700', 'cursor-not-allowed');
        botao.disabled = true;
    }

    // Atualizar resumo da compra
    function atualizarResumoCompra() {
        const resumoDiv = document.getElementById('resumoCompra');

        // Calcular total da inscrição
        const totalInscricao = <?php echo is_numeric($total_modalidades ?? 0) ? $total_modalidades : 0; ?>;

        // Calcular total dos produtos extras
        const totalProdutosExtras = window.produtosExtrasSelecionados.reduce((sum, produto) => sum + produto.valor, 0);

        // Debug
        console.log('Resumo - Produtos extras selecionados:', window.produtosExtrasSelecionados);
        console.log('Resumo - Total produtos extras:', totalProdutosExtras);

        // Calcular subtotal
        const subtotal = totalInscricao + totalProdutosExtras;

        // Desconto vem exclusivamente do ficha.js (EtapaFicha)
        const valorDesconto = (window.etapaFicha && typeof window.etapaFicha.getValorDesconto === 'function') ? window.etapaFicha.getValorDesconto() : 0;
        const total = subtotal - valorDesconto;

        let html = `
        <div class="resumo-item">
            <div class="d-flex justify-content-between">
                <span>Inscrição:</span>
                <span>R$ ${totalInscricao.toFixed(2).replace('.', ',')}</span>
            </div>
        </div>
    `;

        if (totalProdutosExtras > 0) {
            html += `
            <div class="resumo-item">
                <div class="d-flex justify-content-between">
                    <span>Produtos Extras:</span>
                    <span>R$ ${totalProdutosExtras.toFixed(2).replace('.', ',')}</span>
                </div>
            </div>
        `;
        }

        if (valorDesconto > 0) {
            html += `
            <div class="resumo-item">
                <div class="d-flex justify-content-between text-success">
                    <span>Desconto:</span>
                    <span>-R$ ${valorDesconto.toFixed(2).replace('.', ',')}</span>
                </div>
            </div>
        `;
        }

        html += `
        <hr>
        <div class="resumo-total">
            <div class="d-flex justify-content-between">
                <strong>Total:</strong>
                <strong style="color: #0b4340;">R$ ${total.toFixed(2).replace('.', ',')}</strong>
            </div>
        </div>
    `;

        resumoDiv.innerHTML = html;
    }

    // ✅ Função para salvar dados da ficha
    function salvarFicha() {
        const timestamp = Date.now();
        console.log('[FICHA] salvarFicha() INICIO timestamp=', timestamp);
        
        // Validar formulário
        if (!validarFicha()) {
            console.log('[FICHA] salvarFicha validarFicha=false, abortando');
            return;
        }
        console.log('[FICHA] salvarFicha validarFicha=ok, coletando dados');

        // Coletar dados do formulário
        const tamanhoCamiseta = document.getElementById('tamanho_camiseta')?.value;
        const respostasQuestionario = {};

        // Coletar respostas do questionário dinamicamente
        const camposQuestionario = document.querySelectorAll('input[name^="pergunta_"], textarea[name^="pergunta_"], select[name^="pergunta_"]');
        console.log('📋 [SALVAR_FICHA] Campos questionário encontrados:', camposQuestionario.length);

        camposQuestionario.forEach(campo => {
            const perguntaId = campo.name.replace('pergunta_', '').replace('[]', '');

            if (campo.type === 'radio') {
                const radioSelecionado = document.querySelector(`[name="${campo.name}"]:checked`);
                if (radioSelecionado) {
                    respostasQuestionario[perguntaId] = radioSelecionado.value;
                }
            } else if (campo.type === 'checkbox') {
                const checkboxesSelecionados = document.querySelectorAll(`[name="${campo.name}"]:checked`);
                const valores = Array.from(checkboxesSelecionados).map(cb => cb.value);
                respostasQuestionario[perguntaId] = valores;
            } else {
                respostasQuestionario[perguntaId] = campo.value || '';
            }
        });

        // Coletar produtos extras selecionados
        const produtosExtras = window.produtosExtrasSelecionados || [];

        // Cupom e desconto vêm exclusivamente do ficha.js (EtapaFicha)
        const cupomObj = (window.etapaFicha && typeof window.etapaFicha.getCupomAplicado === 'function') ? window.etapaFicha.getCupomAplicado() : null;
        const cupomCodigo = cupomObj && (cupomObj.codigo || cupomObj.codigo_remessa) ? (cupomObj.codigo || cupomObj.codigo_remessa) : null;
        const valorDescontoFicha = (window.etapaFicha && typeof window.etapaFicha.getValorDesconto === 'function') ? window.etapaFicha.getValorDesconto() : 0;

        const dadosFicha = {
            tamanho_camiseta: tamanhoCamiseta,
            respostas_questionario: respostasQuestionario,
            produtos_extras: produtosExtras,
            cupom_aplicado: cupomCodigo,
            valor_desconto: valorDescontoFicha
        };

        // Debug detalhado
        console.log('📦 [SALVAR_FICHA] ========== ENVIANDO DADOS ==========');
        console.log('📦 [SALVAR_FICHA] Dados da ficha:', dadosFicha);
        console.log('📦 [SALVAR_FICHA] Produtos extras selecionados:', window.produtosExtrasSelecionados);
        console.log('📦 [SALVAR_FICHA] Quantidade de produtos:', window.produtosExtrasSelecionados.length);
        console.log('📦 [SALVAR_FICHA] Total produtos extras: R$', window.produtosExtrasSelecionados.reduce((sum, p) => sum + (p.valor || 0), 0));
        console.log('📦 [SALVAR_FICHA] Estrutura dos produtos:', JSON.stringify(window.produtosExtrasSelecionados, null, 2));

        // Mostrar loading
        const btnProximo = document.getElementById('btn-prosseguir');
        if (btnProximo) {
            btnProximo.disabled = true;
            btnProximo.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        }

        // Enviar dados
        console.log('📤 [SALVAR_FICHA] Enviando dados para salvar_ficha.php...');
        console.log('📤 [SALVAR_FICHA] Dados:', dadosFicha);
        
        fetch('salvar_ficha.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(dadosFicha)
            })
            .then(response => {
                console.log('📥 [SALVAR_FICHA] Response recebida:', response.status, response.statusText);
                
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('❌ [SALVAR_FICHA] Erro HTTP:', response.status, text);
                        throw new Error(`Erro HTTP ${response.status}: ${text}`);
                    });
                }
                
                return response.json();
            })
            .then(data => {
                const timestampFim = Date.now();
                const duracao = timestampFim - timestamp;
                
                console.log('[FICHA] ========== SALVAR_FICHA FINALIZADO ==========');
                console.log('[FICHA] Timestamp fim:', timestampFim);
                console.log('[FICHA] Duração total:', duracao + 'ms');
                console.log('[FICHA] Performance:', duracao < 1000 ? '🚀 EXCELENTE (<1s)' : duracao < 2000 ? '✓ BOM (<2s)' : '⚠️ LENTO (>2s)');
                console.log('[FICHA] Resposta:', data);
                
                if (data.success) {
                    console.log('✅ [SALVAR_FICHA] ========== SUCESSO ==========');
                    console.log('✅ Inscrição ID:', data.inscricao_id);
                    console.log('✅ Debug info:', data.debug);
                    console.log('✅ Redirecionando para etapa 4 (Pagamento)...');
                    
                    // Redirecionar para pagamento
                    window.location.href = 'index.php?evento_id=<?php echo $evento_id; ?>&etapa=4';
                } else {
                    console.error('❌ [SALVAR_FICHA] ========== ERRO ==========');
                    console.error('❌ Mensagem:', data.message);
                    console.error('❌ Duração até erro:', duracao + 'ms');
                    
                    if (btnProximo) {
                        btnProximo.disabled = false;
                        btnProximo.innerHTML = 'Próximo <i class="fas fa-arrow-right"></i>';
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: data.message || 'Erro ao salvar ficha'
                    });
                }
            })
            .catch(error => {
                console.error('❌ [SALVAR_FICHA] Erro no fetch:', error);
                console.error('❌ [SALVAR_FICHA] Stack:', error.stack);
                
                if (btnProximo) {
                    btnProximo.disabled = false;
                    btnProximo.innerHTML = 'Próximo <i class="fas fa-arrow-right"></i>';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de conexão',
                    text: 'Não foi possível conectar ao servidor: ' + error.message
                });
            });
    }

    // Validar formulário antes de prosseguir
    function validarFicha() {
        console.log('[FICHA] validarFicha()');
        const tamanhoSelect = document.getElementById('tamanho_camiseta');
        const tamanhoVal = tamanhoSelect ? tamanhoSelect.value : null;
        console.log('[FICHA] validarFicha tamanho_camiseta elemento=', !!tamanhoSelect, 'value=', tamanhoVal);
        if (!tamanhoSelect || !tamanhoSelect.value || !String(tamanhoSelect.value).trim()) {
            console.log('[FICHA] validarFicha falhou: tamanho não selecionado');
            Swal.fire({
                icon: 'warning',
                title: 'Selecione o tamanho',
                text: 'Por favor, selecione o tamanho da camiseta'
            });
            return false;
        }

        // Verificar campos obrigatórios do questionário dinâmico
        const camposObrigatorios = document.querySelectorAll('input[required], textarea[required], select[required]');

        const getLabelTexto = function(c) {
            try {
                const card = c.closest('.pergunta-card');
                const label = card ? card.querySelector('.pergunta-label') : null;
                return (label && label.textContent) ? label.textContent.replace(/\*/g, '').trim() : 'Campo obrigatório';
            } catch (e) {
                return 'Campo obrigatório';
            }
        };

        for (let campo of camposObrigatorios) {
            if (campo.name === 'tamanho_camiseta') continue;

            if (campo.type === 'radio') {
                const radioSelecionado = document.querySelector(`[name="${campo.name}"]:checked`);
                if (!radioSelecionado) {
                    console.log('[FICHA] validarFicha falhou: radio obrigatório não selecionado name=', campo.name);
                    Swal.fire({ icon: 'warning', title: 'Campo obrigatório', text: 'Por favor, responda a pergunta obrigatória: ' + getLabelTexto(campo) });
                    campo.focus();
                    return false;
                }
            } else if (campo.type === 'checkbox') {
                const checkboxSelecionado = document.querySelector(`[name="${campo.name}"]:checked`);
                if (!checkboxSelecionado) {
                    console.log('[FICHA] validarFicha falhou: checkbox obrigatório não selecionado name=', campo.name);
                    Swal.fire({ icon: 'warning', title: 'Campo obrigatório', text: 'Por favor, responda a pergunta obrigatória: ' + getLabelTexto(campo) });
                    campo.focus();
                    return false;
                }
            } else {
                if (!(campo.value && String(campo.value).trim())) {
                    console.log('[FICHA] validarFicha falhou: campo obrigatório vazio name=', campo.name);
                    Swal.fire({ icon: 'warning', title: 'Campo obrigatório', text: 'Por favor, preencha a pergunta obrigatória: ' + getLabelTexto(campo) });
                    campo.focus();
                    return false;
                }
            }
        }

        console.log('[FICHA] validarFicha ok');
        return true;
    }
</script>

<style>
    .resumo-item {
        padding: 8px 0;
        border-bottom: 1px solid #f1f1f1;
    }

    .resumo-item:last-child {
        border-bottom: none;
    }

    .resumo-total {
        padding: 15px 0;
        font-size: 18px;
    }

    /* Estilos do Questionário */
    .questionario-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .pergunta-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        transition: all 0.3s ease;
    }

    .pergunta-card:hover {
        box-shadow: 0 2px 8px rgba(11, 67, 64, 0.1);
        border-color: #0b4340;
    }

    .pergunta-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        display: block;
        line-height: 1.4;
    }

    .resposta-radio {
        display: flex;
        gap: 15px;
        margin-top: 10px;
    }

    .radio-option {
        display: flex;
        align-items: center;
        cursor: pointer;
        padding: 8px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: white;
        transition: all 0.3s ease;
        flex: 1;
        justify-content: center;
    }

    .radio-option:hover {
        border-color: #0b4340;
        background: #f0f9f8;
    }

    .radio-option input[type="radio"] {
        display: none;
    }

    .radio-option input[type="radio"]:checked+.radio-label {
        color: white;
        font-weight: 600;
    }

    .radio-option input[type="radio"]:checked {
        background: #0b4340;
        border-color: #0b4340;
    }

    .radio-option:has(input[type="radio"]:checked) {
        background: #0b4340;
        border-color: #0b4340;
    }

    .radio-option:has(input[type="radio"]:checked) .radio-label {
        color: white;
        font-weight: 600;
    }

    .radio-label {
        font-weight: 500;
        color: #666;
        transition: all 0.3s ease;
    }

    /* Estilos para Checkbox */
    .resposta-checkbox {
        display: flex;
        gap: 15px;
        margin-top: 10px;
    }

    .checkbox-option {
        display: flex;
        align-items: center;
        cursor: pointer;
        padding: 8px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: white;
        transition: all 0.3s ease;
        flex: 1;
        justify-content: center;
    }

    .checkbox-option:hover {
        border-color: #0b4340;
        background: #f0f9f8;
    }

    .checkbox-option input[type="checkbox"] {
        display: none;
    }

    .checkbox-option input[type="checkbox"]:checked+.checkbox-label {
        color: white;
        font-weight: 600;
    }

    .checkbox-option input[type="checkbox"]:checked {
        background: #0b4340;
        border-color: #0b4340;
    }

    .checkbox-option:has(input[type="checkbox"]:checked) {
        background: #0b4340;
        border-color: #0b4340;
    }

    .checkbox-option:has(input[type="checkbox"]:checked) .checkbox-label {
        color: white;
        font-weight: 600;
    }

    .checkbox-label {
        font-weight: 500;
        color: #666;
        transition: all 0.3s ease;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #0b4340;
        box-shadow: 0 0 0 3px rgba(11, 67, 64, 0.1);
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .questionario-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .pergunta-card {
            padding: 15px;
        }

        .resposta-radio,
        .resposta-checkbox {
            flex-direction: column;
            gap: 10px;
        }

        .radio-option,
        .checkbox-option {
            padding: 10px 15px;
        }
    }
</style>