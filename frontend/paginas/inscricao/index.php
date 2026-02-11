<?php
// ✅ CRÍTICO: Iniciar output buffering ANTES de session_start para evitar erro de headers
if (!ob_get_level()) {
    ob_start();
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../../api/db.php';

// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    $evento_id = $_GET['evento_id'] ?? 0;
    header("Location: login-inscricao.php?evento_id={$evento_id}");
    exit;
}

// Verificar se evento foi selecionado
if (!isset($_GET['evento_id']) || empty($_GET['evento_id'])) {
    header('Location: /frontend/paginas/public/index.php');
    exit;
}

$evento_id = (int)$_GET['evento_id'];

// Buscar dados do evento
$stmt = $pdo->prepare("SELECT * FROM eventos WHERE id = ? AND status = 'ativo'");
$stmt->execute([$evento_id]);
$evento = $stmt->fetch();

if (!$evento) {
    header('Location: /frontend/paginas/public/index.php');
    exit;
}

// Inicializar sessão de inscrição apenas se não existir
if (!isset($_SESSION['inscricao']) || !isset($_SESSION['inscricao']['evento_id']) || (int)$_SESSION['inscricao']['evento_id'] !== $evento_id) {
    $_SESSION['inscricao'] = [
        'evento_id' => $evento_id,
        'etapa_atual' => 1,
        'dados' => [],
        'modalidades_selecionadas' => [],
        'produtos_extras' => [],
        'cupom_aplicado' => null,
        'valor_desconto' => 0.00
    ];
}

// Controle de etapa - sempre iniciar na etapa 1
$etapa_url = isset($_GET['etapa']) ? (int)$_GET['etapa'] : 1;
if ($etapa_url < 1 || $etapa_url > 4) {
    // Etapa inválida: redireciona para etapa 1
    header('Location: index.php?evento_id=' . $evento_id . '&etapa=1');
    exit;
}

// ✅ VALIDAÇÕES CRÍTICAS PARA ETAPA 4 (PAGAMENTO) - ANTES DE QUALQUER OUTPUT
if ($etapa_url == 4) {
    // Verificar se usuário está logado (dupla verificação)
    if (!isset($_SESSION['user_id'])) {
        header('Location: login-inscricao.php?evento_id=' . $evento_id);
        exit;
    }
    
    // Verificar se há dados de inscrição na sessão
    if (!isset($_SESSION['inscricao']) || empty($_SESSION['inscricao']['modalidades_selecionadas'])) {
        header('Location: index.php?evento_id=' . $evento_id . '&etapa=1');
        exit;
    }
    
    // Verificar se o evento ainda está ativo
    $stmt_check = $pdo->prepare("SELECT id, nome, status FROM eventos WHERE id = ? AND status = 'ativo'");
    $stmt_check->execute([$evento_id]);
    $evento_check = $stmt_check->fetch();
    
    if (!$evento_check) {
        header('Location: ../public/index.php?erro=evento_nao_encontrado');
        exit;
    }
}

// Marcar que estamos incluindo arquivos de etapa
$GLOBALS['INSCRICAO_INCLUDED'] = true;

// Etapa 4 (Pagamento): CSP para CDNs (SweetAlert2, Alpine) + SDK Mercado Pago (referenciado; Checkout Pro é redirect)
if ($etapa_url == 4) {
    $csp = "default-src 'self'; " .
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' blob: https://cdn.jsdelivr.net https://unpkg.com https://infird.com https://sdk.mercadopago.com https://www.mercadopago.com https://www.mercadopago.com.br https://http2.mlstatic.com; " .
        "frame-src 'self' https://www.mercadopago.com https://www.mercadopago.com.br https://www.mercadolibre.com; " .
        "connect-src 'self' https://api.mercadopago.com https://api.mercadolibre.com https://www.mercadolibre.com https://http2.mlstatic.com https://www.google-analytics.com https://www.googletagmanager.com; " .
        "img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com https://http2.mlstatic.com; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com;";
    header("Content-Security-Policy: " . $csp);
}

// Incluir header específico para inscrição
include 'includes/header-inscricao.php';
?>

<div class="inscricao-container">
    <div class="inscricao-wrapper">
        <!-- Header -->
        <div class="inscricao-header">
            <h2 class="inscricao-titulo">Inscrição no Evento</h2>
            <p class="inscricao-evento"><?php echo htmlspecialchars($evento['nome']); ?></p>
        </div>

        <!-- Barra de progresso -->
        <?php
        // Definir status das etapas
        $etapas = [
            1 => ['nome' => 'Modalidade', 'status' => 'pendente'],
            2 => ['nome' => 'Termos', 'status' => 'pendente'],
            3 => ['nome' => 'Cadastro', 'status' => 'pendente'],
            4 => ['nome' => 'Pagamento', 'status' => 'pendente']
        ];

        // Marcar etapas concluídas e atual
        for ($i = 1; $i < $etapa_url; $i++) {
            $etapas[$i]['status'] = 'concluida';
        }
        $etapas[$etapa_url]['status'] = 'atual';

        include 'includes/progress_bar.php';
        ?>

        <!-- Conteúdo principal -->
        <div class="inscricao-content">
            <?php
            $etapa_atual = $etapa_url;
            $arquivo_etapa = '';

            switch ($etapa_atual) {
                case 1:
                    $arquivo_etapa = 'modalidade.php';
                    break;
                case 2:
                    $arquivo_etapa = 'termos.php';
                    break;
                case 3:
                    $arquivo_etapa = 'ficha.php';
                    break;
                case 4:
                    $arquivo_etapa = 'pagamento.php';
                    break;
                default:
                    $arquivo_etapa = 'modalidade.php';
            }

            if (file_exists($arquivo_etapa)) {
                include $arquivo_etapa;
            } else {
                echo '<div class="alert alert-danger">Erro: Etapa não encontrada</div>';
            }
            ?>
        </div>
    </div>
</div>

<script>
    // Definir API_BASE para módulos JavaScript
    if (!window.API_BASE) {
        (function () {
            var path = window.location.pathname || '';
            var idx = path.indexOf('/frontend/');
            window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
        })();
    }
    
    // Funções globais de navegação
    function prosseguirEtapa() {
        const etapaAtual = <?php echo $etapa_url; ?>;
        const proximaEtapa = etapaAtual + 1;

        if (validarEtapaAtual()) {
            // Tentativa: persistir pré-inscrição quando avançando a partir da Ficha
            if (etapaAtual === 3) {
                try {
                    const produtos = (window.produtosExtrasSelecionados || []).map(p => ({
                        id: p.id,
                        nome: p.nome,
                        valor: p.valor,
                        quantidade: 1
                    }));
                    const payload = {
                        evento_id: <?php echo (int)$evento_id; ?>,
                        modalidade_id: <?php echo isset($modalidades[0]['id']) ? (int)$modalidades[0]['id'] : 'null'; ?>,
                        tamanho_camiseta: document.querySelector('input[name="tamanho_camiseta"]:checked')?.value || 'P',
                        valor_modalidades: <?php echo json_encode(0); ?>,
                        valor_extras: produtos.reduce((s, p) => s + (p.valor || 0), 0),
                        valor_desconto: 0,
                        cupom: null,
                        produtos_extras: produtos,
                        seguro: 0
                    };
                    fetch((window.API_BASE || '') + '/api/inscricao/precreate.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        })
                        .catch(() => {});
                } catch (e) {}
            }
            fetch("atualizar_etapa.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        etapa: proximaEtapa
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (window.inscricaoModules && window.inscricaoModules.progressTracker) {
                            window.inscricaoModules.progressTracker.setEtapaAtual(proximaEtapa);
                            if (etapaAtual < proximaEtapa) {
                                window.inscricaoModules.progressTracker.marcarComoCompleta(etapaAtual);
                            }
                        }
                        window.location.href = "index.php?evento_id=<?php echo $evento_id; ?>&etapa=" + proximaEtapa;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao prosseguir',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error("Erro:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de comunicação',
                        text: 'Não foi possível conectar ao servidor'
                    });
                });
        }
    }

    function voltarEtapa() {
        const etapaAtual = <?php echo $etapa_url; ?>;
        const etapaAnterior = etapaAtual - 1;

        if (etapaAnterior >= 1) {
            fetch("atualizar_etapa.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        etapa: etapaAnterior
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (window.inscricaoModules && window.inscricaoModules.progressTracker) {
                            window.inscricaoModules.progressTracker.setEtapaAtual(etapaAnterior);
                        }
                        window.location.href = "index.php?evento_id=<?php echo $evento_id; ?>&etapa=" + etapaAnterior;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao voltar',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error("Erro:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de comunicação',
                        text: 'Não foi possível conectar ao servidor'
                    });
                });
        }
    }

    function validarEtapaAtual() {
        const etapaAtual = <?php echo $etapa_url; ?>;

        switch (etapaAtual) {
            case 1: // Modalidade
                const modalidadeSelecionada = document.querySelector("input[name='modalidade_id']:checked");
                if (!modalidadeSelecionada) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selecione uma modalidade',
                        text: 'Por favor, escolha uma modalidade para continuar!'
                    });
                    return false;
                }
                break;

            case 2: // Termos
                const termosAceitos = document.getElementById("aceiteTermos");
                if (!termosAceitos || !termosAceitos.checked) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Aceite os termos',
                        text: 'Você deve aceitar os termos e condições para continuar'
                    });
                    return false;
                }
                break;

            case 3: // Ficha (aceita select ou radio para tamanho de camiseta)
                const tamanhoCamisetaEl = document.querySelector("input[name='tamanho_camiseta']:checked") || document.querySelector("select[name='tamanho_camiseta']");
                const tamanhoValido = tamanhoCamisetaEl && (tamanhoCamisetaEl.tagName === 'SELECT' ? tamanhoCamisetaEl.value.trim() !== '' : tamanhoCamisetaEl.checked);
                if (!tamanhoValido) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selecione o tamanho',
                        text: 'Por favor, selecione o tamanho da camiseta'
                    });
                    return false;
                }

                // Verificar campos obrigatórios do questionário
                const camposObrigatorios = [{
                        name: 'extincao_sauim',
                        message: 'Por favor, responda sobre a extinção do Sauim-de-coleira'
                    },
                    {
                        name: 'equipe',
                        message: 'Por favor, informe o nome da equipe'
                    },
                    {
                        name: 'contato_emergencia',
                        message: 'Por favor, informe o contato de emergência'
                    },
                    {
                        name: 'apto_fisicamente',
                        message: 'Por favor, confirme que está apto fisicamente'
                    },
                    {
                        name: 'nome_peito',
                        message: 'Por favor, informe o nome para o número de peito'
                    }
                ];

                for (let campo of camposObrigatorios) {
                    const elemento = document.querySelector(`[name="${campo.name}"]`);
                    if (!elemento) continue;

                    if (elemento.type === 'radio') {
                        const radioSelecionado = document.querySelector(`[name="${campo.name}"]:checked`);
                        if (!radioSelecionado) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Campo obrigatório',
                                text: campo.message
                            });
                            elemento.focus();
                            return false;
                        }
                    } else {
                        if (!elemento.value.trim()) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Campo obrigatório',
                                text: campo.message
                            });
                            elemento.focus();
                            return false;
                        }
                    }
                }
                break;

            case 4: // Pagamento
                const dadosPagamento = document.getElementById("formPagamento");
                if (dadosPagamento && !dadosPagamento.checkValidity()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campos obrigatórios',
                        text: 'Por favor, preencha todos os campos obrigatórios do pagamento'
                    });
                    return false;
                }
                break;
        }

        return true;
    }

    window.eventoId = <?php echo (int)$evento_id; ?>;
    window.etapaAtual = <?php echo (int)$etapa_url; ?>;
</script>

<script type="module">
    import { initInscricaoModules } from '../../js/inscricao/init-modules.js';
    
    document.addEventListener('DOMContentLoaded', () => {
        if (window.eventoId) {
            initInscricaoModules(window.eventoId);
        }
    });
</script>

<?php include 'includes/footer-inscricao.php'; ?>
