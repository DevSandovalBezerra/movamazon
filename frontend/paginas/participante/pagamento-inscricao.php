<?php
// session_start() já foi chamado no index.php

// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Verificar parâmetros
$inscricao_id = $_GET['inscricao_id'] ?? null;
if (!$inscricao_id) {
    header('Location: index.php?page=minhas-inscricoes');
    exit;
}

$pageTitle = 'Pagamento da Inscrição';
// header.php e navbar.php já foram incluídos pelo index.php

// Tailwind CSS já foi incluído pelo index.php
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Pagamento da Inscrição</h1>
            <p class="text-gray-600">Complete o pagamento para confirmar sua participação</p>
        </div>

        <!-- Loading State -->
        <div id="loading" class="text-center py-16">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-brand-green"></div>
            <p class="mt-4 text-gray-600">Carregando dados da inscrição...</p>
        </div>

        <!-- Error State -->
        <div id="error-container" class="hidden">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-red-900 mb-2">Erro ao carregar inscrição</h3>
                <p id="error-message" class="text-red-700 mb-4"></p>
                <a href="index.php?page=minhas-inscricoes" class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    Voltar para Minhas Inscrições
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div id="main-content" class="hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Resumo da Inscrição -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Resumo da Inscrição</h2>

                    <!-- Evento -->
                    <div class="mb-6">
                        <div class="flex items-center mb-3">
                            <img id="evento-imagem" src="" alt="" class="w-16 h-16 object-cover rounded-lg mr-4">
                            <div>
                                <h3 id="evento-nome" class="font-semibold text-gray-900"></h3>
                                <p id="evento-data" class="text-sm text-gray-600"></p>
                                <p id="evento-local" class="text-sm text-gray-600"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Modalidade:</span>
                            <span id="modalidade-nome" class="font-medium"></span>
                        </div>

                        <div id="kit-info" class="flex justify-between hidden">
                            <span class="text-gray-600">Kit:</span>
                            <span id="kit-nome" class="font-medium"></span>
                        </div>

                        <div id="lote-info" class="flex justify-between hidden">
                            <span class="text-gray-600">Lote:</span>
                            <span id="lote-nome" class="font-medium"></span>
                        </div>
                    </div>

                    <!-- Breakdown de Valores -->
                    <div class="border-t pt-4">
                        <h4 class="font-semibold text-gray-900 mb-3">Detalhamento dos Valores</h4>
                        <div class="space-y-2 text-sm" id="breakdown-valores">
                            <!-- Será preenchido via JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Pagamento -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Pagamento</h2>

                    <!-- Resumo de Valores -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="space-y-2 text-sm mb-4" id="resumo-valores">
                            <!-- Será preenchido via JavaScript -->
                        </div>
                        <div class="border-t pt-3 mt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Total a Pagar:</span>
                                <span id="valor-total" class="text-2xl font-bold text-brand-green"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Informações do Usuário -->
                    <div class="mb-6">
                        <h3 class="font-medium text-gray-900 mb-3">Dados para Faturamento</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nome:</span>
                                <span id="usuario-nome" class="font-medium"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span id="usuario-email" class="font-medium"></span>
                            </div>
                        </div>
                    </div>

                    <!-- ROLLBACK: remova a classe hidden de janela-pagamento-mercadopago e defina USE_CHECKOUT_PRO_REDIRECT = false no JS para voltar ao Brick/PIX/Boleto -->
                    <!-- Container da Janela de Pagamento Mercado Pago (oculto quando Checkout Pro redirect está ativo) -->
                    <div id="janela-pagamento-mercadopago" class="hidden">
                        <div class="mb-4 text-center">
                            <img src="../../assets/img/mercadopago-logo.png" alt="Mercado Pago" class="h-8 mx-auto mb-2">
                            <p class="text-sm text-gray-600">Pagamento 100% seguro</p>
                        </div>
                        
                        <!-- Container do Payment Brick -->
                        <div id="paymentBrick_container" class="w-full"></div>
                        
                        <!-- Opção PIX Separada -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="text-center mb-4">
                                <h4 class="text-md font-semibold text-gray-700 mb-2">💳 Pagamento Instantâneo</h4>
                                <p class="text-sm text-gray-600 mb-4">Pague instantaneamente com PIX</p>
                                <button id="btn-pix-pagamento" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transition-all duration-200 flex items-center gap-2 mx-auto">
                                    <span>💳</span>
                                    <span>Pagar com PIX</span>
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Pagamento instantâneo e seguro</p>
                            </div>

                            <!-- Container do PIX (inicialmente oculto) -->
                            <div id="pix-container" class="hidden mt-4"></div>
                        </div>

                        <!-- Opção Boleto Bancário -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="text-center mb-4">
                                <h4 class="text-md font-semibold text-gray-700 mb-2">📄 Pagamento com Boleto</h4>
                                <p class="text-sm text-gray-600 mb-4">Pague com boleto bancário (válido por 3 dias)</p>
                                <button id="btn-boleto-pagamento" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transition-all duration-200 flex items-center gap-2 mx-auto">
                                    <span>📄</span>
                                    <span>Pagar com Boleto</span>
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Compensação em até 2 dias úteis após pagamento</p>
                            </div>

                            <!-- Container do Boleto (inicialmente oculto) -->
                            <div id="boleto-container" class="hidden mt-4"></div>
                        </div>
                        
                        <!-- Container do Status Screen Brick -->
                        <div id="statusScreenBrick_container" class="w-full"></div>
                    </div>

                    <!-- Botão de Pagamento -->
                    <div id="btn-pagar-container" class="space-y-4">
                        <button id="btn-pagar" class="w-full bg-brand-green text-white font-bold py-3 px-6 rounded-lg hover:bg-green-700 transition-colors duration-200 shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Pagar com Mercado Pago
                        </button>
                        <p id="payment-flow-hint" class="text-xs text-gray-500 mt-2 text-center">Você será redirecionado ao Mercado Pago para pagar com cartão, PIX ou boleto.</p>

                        <a href="index.php?page=minhas-inscricoes" class="block w-full text-center bg-gray-100 text-gray-700 font-medium py-2 px-6 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancelar
                        </a>
                    </div>

                    <!-- Informações de Segurança -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-blue-900">Pagamento Seguro</p>
                                <p class="text-sm text-blue-700">Seus dados estão protegidos com criptografia SSL e processados pelo Mercado Pago.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inscricaoId = '<?php echo $inscricao_id; ?>';
        const loadingEl = document.getElementById('loading');
        const errorContainer = document.getElementById('error-container');
        const mainContent = document.getElementById('main-content');
        const errorMessage = document.getElementById('error-message');
        const btnPagar = document.getElementById('btn-pagar');

        // Carregar dados da inscrição
        fetch(`../../../api/participante/get_inscricao.php?inscricao_id=${inscricaoId}`, {
                method: 'GET',
                credentials: 'same-origin', // Incluir cookies de sessão
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                loadingEl.classList.add('hidden');

                if (data.success) {
                    preencherDados(data.inscricao);
                    mainContent.classList.remove('hidden');
                } else {
                    mostrarErro(data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarErro('Erro ao carregar dados da inscrição. Tente novamente.');
            });

        function preencherDados(inscricao) {
            // ✅ Armazenar dados da inscrição globalmente para uso na função PIX
            window.inscricaoData = inscricao;

            // UX: ajustar visual do fluxo conforme modo de pagamento ativo.
            const janelaPagamento = document.getElementById('janela-pagamento-mercadopago');
            const paymentFlowHint = document.getElementById('payment-flow-hint');
            if (janelaPagamento) {
                if (window.USE_CHECKOUT_PRO_REDIRECT) {
                    janelaPagamento.classList.add('hidden');
                } else {
                    janelaPagamento.classList.remove('hidden');
                }
            }
            if (paymentFlowHint) {
                paymentFlowHint.textContent = window.USE_CHECKOUT_PRO_REDIRECT
                    ? 'Você será redirecionado ao Mercado Pago para pagar com cartão, PIX ou boleto.'
                    : 'Escolha abaixo: cartão no formulário, PIX instantâneo ou boleto.';
            }
            
            // Evento
            document.getElementById('evento-nome').textContent = inscricao.evento.nome;
            document.getElementById('evento-data').textContent = new Date(inscricao.evento.data).toLocaleDateString('pt-BR');
            document.getElementById('evento-local').textContent = inscricao.evento.local;

            const eventoImagemEl = document.getElementById('evento-imagem');
            if (inscricao.evento.imagem) {
                const imagemEvento = inscricao.evento.imagem;
                const isUrl = imagemEvento && (imagemEvento.startsWith('http://') || imagemEvento.startsWith('https://') || imagemEvento.startsWith('/'));
                if (isUrl) {
                    eventoImagemEl.src = imagemEvento;
                } else {
                    eventoImagemEl.src = typeof window.getEventImageUrl === 'function' && imagemEvento
                        ? window.getEventImageUrl(imagemEvento)
                        : (imagemEvento ? '../../assets/img/eventos/' + imagemEvento : '../../assets/img/logo.png');
                    eventoImagemEl.onerror = function() {
                        this.src = '../../assets/img/logo.png';
                        this.onerror = null;
                    };
                }
            } else {
                eventoImagemEl.src = '../../assets/img/logo.png';
            }
            eventoImagemEl.alt = inscricao.evento.nome || 'Evento';

            // Modalidade
            document.getElementById('modalidade-nome').textContent = inscricao.modalidade.nome;

            // Kit
            if (inscricao.kit) {
                document.getElementById('kit-info').classList.remove('hidden');
                document.getElementById('kit-nome').textContent = inscricao.kit.nome;
            }

            // Lote
            if (inscricao.lote) {
                document.getElementById('lote-info').classList.remove('hidden');
                document.getElementById('lote-nome').textContent = `Lote ${inscricao.lote.numero}`;
            }

            // Preencher breakdown de valores (lado esquerdo - detalhado)
            preencherBreakdownValores(inscricao);
            
            // Preencher resumo de valores (lado direito - resumido)
            preencherResumoValores(inscricao);

            // Usuário
            document.getElementById('usuario-nome').textContent = inscricao.usuario.nome;
            document.getElementById('usuario-email').textContent = inscricao.usuario.email;

            // Valor Total - usar o valor do breakdown (já calculado corretamente)
            const breakdown = inscricao.breakdown_valores || {};
            const valorTotal = Number(breakdown.valor_total) || Number(inscricao.valor_total) || 0;
            
            if (isNaN(valorTotal) || valorTotal <= 0) {
                console.error('Valor total inválido:', valorTotal, 'Breakdown:', breakdown);
            }
            
            document.getElementById('valor-total').textContent = `R$ ${valorTotal.toFixed(2).replace('.', ',')}`;
            
            // Log para debug
            console.log('💰 Breakdown de valores:', breakdown);
            console.log('💰 Valor total final:', valorTotal);

            // Configurar botão de pagamento
            btnPagar.onclick = function() {
                iniciarPagamento(inscricao);
            };
            
            // ✅ Garantir que os botões PIX e Boleto tenham event listeners após os dados serem carregados
            if (!window.pixButtonSetup) {
                setTimeout(function() {
                    console.log('🔍 Tentando configurar botões de pagamento após carregar dados...');
                    
                    // Configurar botão PIX
                    const btnPix = document.getElementById('btn-pix-pagamento');
                    if (btnPix && typeof window.setupPixButton === 'function') {
                        console.log('✅ Usando setupPixButton para configurar botão PIX');
                        window.setupPixButton();
                    } else if (btnPix && typeof window.gerarPixPagamento === 'function') {
                        if (!btnPix.hasAttribute('data-pix-listener-added')) {
                            console.log('✅ Configurando botão PIX manualmente (fallback)');
                            btnPix.setAttribute('data-pix-listener-added', 'true');
                            btnPix.addEventListener('click', async function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                console.log('✅ Botão PIX clicado (listener manual)!');
                                if (typeof window.gerarPixPagamento === 'function') {
                                    await window.gerarPixPagamento();
                                }
                            });
                        }
                    }
                    
                    // Configurar botão Boleto
                    const btnBoleto = document.getElementById('btn-boleto-pagamento');
                    if (btnBoleto && typeof window.setupBoletoButton === 'function') {
                        console.log('✅ Usando setupBoletoButton para configurar botão Boleto');
                        window.setupBoletoButton();
                    } else if (btnBoleto && typeof window.gerarBoletoPagamento === 'function') {
                        if (!btnBoleto.hasAttribute('data-boleto-listener-added')) {
                            console.log('✅ Configurando botão Boleto manualmente (fallback)');
                            btnBoleto.setAttribute('data-boleto-listener-added', 'true');
                            btnBoleto.addEventListener('click', async function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                console.log('✅ Botão Boleto clicado (listener manual)!');
                                if (typeof window.gerarBoletoPagamento === 'function') {
                                    await window.gerarBoletoPagamento();
                                }
                            });
                        }
                    }
                }, 500);
            }
        }

        // ROLLBACK: Para voltar ao Brick, defina USE_CHECKOUT_PRO_REDIRECT = false em pagamento-inscricao.js e remova a classe hidden de #janela-pagamento-mercadopago
        function iniciarPagamento(inscricao) {
            if (window.USE_CHECKOUT_PRO_REDIRECT && typeof window.iniciarPagamentoRedirect === 'function') {
                window.iniciarPagamentoRedirect(inscricao);
                return;
            }
            if (typeof window.iniciarPagamentoComBrick === 'function') {
                window.iniciarPagamentoComBrick(inscricao);
            } else {
                console.error('Função iniciarPagamentoComBrick não está disponível');
                alert('Erro ao carregar módulo de pagamento. Recarregue a página e tente novamente.');
            }
        }

        function preencherBreakdownValores(inscricao) {
            const breakdownEl = document.getElementById('breakdown-valores');
            breakdownEl.innerHTML = '';
            
            const breakdown = inscricao.breakdown_valores || {};
            const valorBase = Number(breakdown.valor_base) || 0;
            const valorKit = Number(breakdown.valor_kit) || 0;
            const totalExtras = Number(breakdown.total_extras) || 0;
            const temLote = breakdown.tem_lote || false;
            
            // Valor base (lote ou modalidade)
            if (valorBase > 0) {
                const div = document.createElement('div');
                div.className = 'flex justify-between py-1';
                const label = temLote ? `Lote ${inscricao.lote?.numero || ''}` : 'Inscrição';
                div.innerHTML = `
                    <span class="text-gray-600">${label}:</span>
                    <span class="font-medium text-gray-900">R$ ${valorBase.toFixed(2).replace('.', ',')}</span>
                `;
                breakdownEl.appendChild(div);
            }
            
            // Kit
            if (valorKit > 0 && inscricao.kit) {
                const div = document.createElement('div');
                div.className = 'flex justify-between py-1';
                div.innerHTML = `
                    <span class="text-gray-600">Kit (${inscricao.kit.nome}):</span>
                    <span class="font-medium text-gray-900">R$ ${valorKit.toFixed(2).replace('.', ',')}</span>
                `;
                breakdownEl.appendChild(div);
            }
            
            // Produtos Extras (detalhado)
            if (inscricao.produtos_extras && inscricao.produtos_extras.length > 0) {
                inscricao.produtos_extras.forEach(extra => {
                    const quantidade = Number(extra.quantidade) || 0;
                    let subtotal = Number(extra.subtotal) || 0;
                    
                    if (subtotal === 0 && extra.valor && quantidade > 0) {
                        subtotal = Number(extra.valor) * quantidade;
                    }
                    
                    if (isNaN(subtotal) || subtotal <= 0) return;
                    
                    const div = document.createElement('div');
                    div.className = 'flex justify-between py-1';
                    div.innerHTML = `
                        <span class="text-gray-600">${extra.nome || 'Produto extra'} (${quantidade}x):</span>
                        <span class="font-medium text-gray-900">R$ ${subtotal.toFixed(2).replace('.', ',')}</span>
                    `;
                    breakdownEl.appendChild(div);
                });
            }
            
            // Linha de total (se houver múltiplos itens)
            const somaBreakdown = valorBase + valorKit + totalExtras;
            const valorTotal = Number(breakdown.valor_total) || 0;
            
            // Se a soma não bate com o total, mostrar aviso
            if (Math.abs(somaBreakdown - valorTotal) > 0.01 && valorTotal > 0) {
                const div = document.createElement('div');
                div.className = 'flex justify-between py-1 mt-2 pt-2 border-t border-gray-200';
                div.innerHTML = `
                    <span class="text-sm text-gray-500 italic">* Valores já incluídos no total</span>
                `;
                breakdownEl.appendChild(div);
            }
        }
        
        function preencherResumoValores(inscricao) {
            const resumoEl = document.getElementById('resumo-valores');
            resumoEl.innerHTML = '';
            
            const breakdown = inscricao.breakdown_valores || {};
            const valorBase = Number(breakdown.valor_base) || 0;
            const valorKit = Number(breakdown.valor_kit) || 0;
            const totalExtras = Number(breakdown.total_extras) || 0;
            const temLote = breakdown.tem_lote || false;
            const valorTotal = Number(breakdown.valor_total) || 0;
            
            // Calcular soma para verificar coerência
            const somaCalculada = valorBase + valorKit + totalExtras;
            const valoresCoerentes = Math.abs(somaCalculada - valorTotal) < 0.01;
            
            // Valor base
            if (valorBase > 0) {
                const div = document.createElement('div');
                div.className = 'flex justify-between py-1';
                const label = temLote ? `Lote ${inscricao.lote?.numero || ''}` : 'Inscrição';
                div.innerHTML = `
                    <span class="text-gray-600">${label}</span>
                    <span class="font-medium text-gray-900">R$ ${valorBase.toFixed(2).replace('.', ',')}</span>
                `;
                resumoEl.appendChild(div);
            }
            
            // Kit
            if (valorKit > 0) {
                const div = document.createElement('div');
                div.className = 'flex justify-between py-1';
                div.innerHTML = `
                    <span class="text-gray-600">Kit</span>
                    <span class="font-medium text-gray-900">R$ ${valorKit.toFixed(2).replace('.', ',')}</span>
                `;
                resumoEl.appendChild(div);
            }
            
            // Produtos Extras (total)
            if (totalExtras > 0) {
                const div = document.createElement('div');
                div.className = 'flex justify-between py-1';
                div.innerHTML = `
                    <span class="text-gray-600">Produtos Extras</span>
                    <span class="font-medium text-gray-900">R$ ${totalExtras.toFixed(2).replace('.', ',')}</span>
                `;
                resumoEl.appendChild(div);
            }
            
            // Se valores não são coerentes, mostrar aviso
            if (!valoresCoerentes && valorTotal > 0) {
                const div = document.createElement('div');
                div.className = 'text-xs text-gray-500 italic mt-2';
                div.textContent = '* Alguns valores já estão incluídos no total';
                resumoEl.appendChild(div);
            }
        }

        function mostrarErro(mensagem) {
            loadingEl.classList.add('hidden');
            errorMessage.textContent = mensagem;
            errorContainer.classList.remove('hidden');
        }
    });
</script>

<!-- SDK Mercado Pago -->
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script src="https://www.mercadopago.com/v2/security.js" view="checkout"></script>

<!-- Script de Pagamento -->
<script src="../../js/mercadopago-config.js"></script>
<script src="../../js/participante/pagamento-inscricao.js" defer></script>

<?php include '../../includes/footer.php'; ?>
