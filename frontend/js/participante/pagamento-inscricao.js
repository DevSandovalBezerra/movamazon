if (window.getApiBase) { window.getApiBase(); }
(function() {
    'use strict';

    // Checkout Transparente ativo no formulário do participante.
    // ROLLBACK: definir true para voltar ao redirect.
    window.USE_CHECKOUT_PRO_REDIRECT = false;

    // Variáveis globais para MercadoPago
    let mp = null;
    let bricksBuilder = null;
    let inscricaoData = null;

    // Base dinâmico para APIs
    if (!window.API_BASE) {
        (function () {
            var path = window.location.pathname || '';
            var idx = path.indexOf('/frontend/');
            window.API_BASE = idx > 0 ? path.slice(0, idx) + '/api' : '/api';
        })();
    }

    // Função para construir URLs usando API_BASE
    function getApiUrl(endpoint) {
        const url = `${window.API_BASE}/${endpoint}`;
        return url;
    }

    // Função para inicializar MercadoPago usando public key dinâmica
    async function inicializarMercadoPago() {
        return new Promise(async (resolve, reject) => {
            if (mp && bricksBuilder) {
                resolve({ mp, bricksBuilder });
                return;
            }

            try {
                // Buscar configuração do servidor
                const config = await getMercadoPagoConfig();
                
                if (!config.public_key) {
                    throw new Error('Public key não encontrada na configuração');
                }

                if (typeof MercadoPago !== 'undefined') {
                    mp = new MercadoPago(config.public_key);
                    bricksBuilder = mp.bricks();
                    
                    console.log("=== MERCADO PAGO INITIALIZATION ===");
                    console.log("Environment:", config.environment);
                    console.log("Is Production:", config.is_production);
                    
                    window.mp = mp;
                    window.bricksBuilder = bricksBuilder;
                    
                    resolve({ mp, bricksBuilder });
                    return;
                }

                console.log("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€šÃ‚Â³ Aguardando SDK do MercadoPago carregar...");
                let attempts = 0;
                const maxAttempts = 50;
                
                const checkInterval = setInterval(() => {
                    attempts++;
                    
                    if (typeof MercadoPago !== 'undefined') {
                        clearInterval(checkInterval);
                        try {
                            mp = new MercadoPago(config.public_key);
                            bricksBuilder = mp.bricks();
                            
                            console.log("=== MERCADO PAGO INITIALIZATION ===");
                            console.log("Environment:", config.environment);
                            console.log("Is Production:", config.is_production);
                            
                            window.mp = mp;
                            window.bricksBuilder = bricksBuilder;
                            
                            resolve({ mp, bricksBuilder });
                        } catch (error) {
                            console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Erro ao inicializar MercadoPago:", error);
                            reject(error);
                        }
                    } else if (attempts >= maxAttempts) {
                        clearInterval(checkInterval);
                        reject(new Error('SDK do MercadoPago não carregou após 5 segundos'));
                    }
                }, 100);
            } catch (error) {
                console.error("ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Erro ao obter configuração do Mercado Pago:", error);
                reject(error);
            }
        });
    }

    // Criar preferªncia de pagamento
    async function criarPreference(inscricaoId, valorTotal, eventoNome, modalidadeNome, produtosExtras) {
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒâ€šÃ‚Â Dados para criar preferªncia:', {
            inscricaoId,
            valorTotal,
            eventoNome,
            modalidadeNome,
            produtosExtras
        });

        const response = await fetch(getApiUrl('inscricao/create_preference.php'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                inscricao_id: inscricaoId,
                valor_total: valorTotal,
                evento_nome: eventoNome,
                modalidade_nome: modalidadeNome,
                produtos_extras: produtosExtras || [],
                origem: 'participante'
            })
        });

        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¡ Resposta HTTP:', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok
        });

        let data;
        try {
            const text = await response.text();
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒÂ¢ââ€šÂ¬Ã…Â¾ Resposta raw:', text);
            data = JSON.parse(text);
        } catch (parseError) {
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Erro ao fazer parse da resposta:', parseError);
            throw new Error(`Erro ao processar resposta do servidor (Status: ${response.status})`);
        }

        if (!response.ok) {
            const errorMsg = data?.error || data?.message || `HTTP error! status: ${response.status}`;
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Erro na resposta:', {
                status: response.status,
                data: data
            });
            throw new Error(errorMsg);
        }
        
        if (!data.success) {
            const errorMsg = data.error || data.message || 'Erro ao criar preferªncia';
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Erro ao criar preferªncia:', {
                success: data.success,
                error: data.error,
                message: data.message,
                data: data
            });
            throw new Error(errorMsg);
        }

        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Preference criada com sucesso:', data.preference_id, 'init_point:', data.init_point ? 'ok' : 'n/a');
        
        return { preference_id: data.preference_id, init_point: data.init_point || '' };
    }

    // Checkout Pro redirect: criar preferªncia e redirecionar para o MP (ROLLBACK: desative USE_CHECKOUT_PRO_REDIRECT para usar Brick)
    async function iniciarPagamentoRedirect(inscricao) {
        const btnPagar = document.getElementById('btn-pagar');
        if (!btnPagar) return;
        try {
            btnPagar.disabled = true;
            btnPagar.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Redirecionando ao Mercado Pago...
            `;
            const breakdown = inscricao.breakdown_valores || {};
            const valorTotal = Number(breakdown.valor_total) || Number(inscricao.valor_total) || 0;
            if (valorTotal <= 0) throw new Error('Valor total inválido');
            const produtosExtras = (inscricao.produtos_extras || []).map(function(extra) {
                return { nome: extra.nome || 'Produto extra', valor: Number(extra.valor) || 0, quantidade: Number(extra.quantidade) || 1 };
            });
            const result = await criarPreference(inscricao.id, valorTotal, inscricao.evento.nome, inscricao.modalidade.nome, produtosExtras);
            const initPoint = result.init_point || (typeof result === 'string' ? '' : result.initPoint);
            if (initPoint) {
                window.location.href = initPoint;
                return;
            }
            throw new Error('Resposta do servidor sem link de pagamento.');
        } catch (err) {
            console.error('Erro ao iniciar pagamento:', err);
            mostrarErro('Erro ao processar pagamento: ' + (err.message || 'Tente novamente.'));
            btnPagar.disabled = false;
            btnPagar.innerHTML = `
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Pagar com Mercado Pago
            `;
        }
    }
    window.iniciarPagamentoRedirect = iniciarPagamentoRedirect;

    // Renderizar Payment Brick (INATIVO quando USE_CHECKOUT_PRO_REDIRECT = true; ver ROLLBACK no topo)
    async function renderPaymentBrick(bricksBuilder, preferenceId, amount) {
        const settings = {
            initialization: {
                amount: amount,
                preferenceId: preferenceId,
            },
            customization: {
                paymentMethods: {
                    creditCard: "all",
                    debitCard: "all",
                    mercadoPago: "all"
                },
            },
            callbacks: {
                onReady: () => {
                    console.log("ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Payment Brick pronto");
                },
                onSubmit: (formData) => {
                    return new Promise((resolve, reject) => {
                        const payload = {
                            ...formData,
                            device_id: (typeof window.MP_DEVICE_SESSION_ID !== 'undefined' ? window.MP_DEVICE_SESSION_ID : '') || '',
                            inscricao_id: (window.inscricaoData && window.inscricaoData.id) ? String(window.inscricaoData.id) : ''
                        };
                        fetch(getApiUrl('inscricao/process_payment_preference.php'), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(response => response.json())
                        .then((response) => {
                            if (response.status === 'approved' || response.status === 'pending') {
                                renderStatusScreenBrick(bricksBuilder, response.id);
                                resolve();
                            } else {
                                reject(new Error(response?.error || 'Payment failed'));
                            }
                        })
                        .catch((error) => {
                            console.error('Payment error:', error);
                            reject(error);
                        });
                    });
                },
                onError: (error) => {
                    console.error('Brick error:', error);
                    mostrarErro('Erro no pagamento: ' + error.message);
                },
            },
        };

        window.paymentBrickController = await bricksBuilder.create(
            'payment',
            'paymentBrick_container',
            settings
        );
    }

    // Renderizar Status Screen Brick
    async function renderStatusScreenBrick(bricksBuilder, paymentId) {
        const settings = {
            initialization: {
                paymentId: paymentId,
            },
            callbacks: {
                onReady: () => {
                    console.log("ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Status Screen Brick pronto");
                    document.getElementById("paymentBrick_container").style.display = 'none';
                    mostrarMensagemSucesso(paymentId);
                },
                onError: (error) => {
                    console.error('StatusScreen error:', error);
                    mostrarErro('Erro ao exibir status do pagamento: ' + error.message);
                },
            },
        };

        window.statusScreenBrickController = await bricksBuilder.create(
            'statusScreen',
            'statusScreenBrick_container',
            settings
        );
    }

    // Mostrar mensagem de sucesso
    function mostrarMensagemSucesso(paymentId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Pagamento processado!',
                text: 'Vocª receberá um email de confirmação em breve.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0b4340'
            }).then(() => {
                window.location.href = 'index.php?page=minhas-inscricoes';
            });
        } else {
            alert('Pagamento processado com sucesso! Vocª receberá um email de confirmação.');
            window.location.href = 'index.php?page=minhas-inscricoes';
        }
    }

    // Mostrar erro
    function mostrarErro(mensagem) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro no pagamento',
                text: mensagem,
                confirmButtonText: 'OK',
                confirmButtonColor: '#ad1f22'
            });
        } else {
            alert('Erro: ' + mensagem);
        }
    }

    // Inicializar pagamento com Brick (INATIVO quando USE_CHECKOUT_PRO_REDIRECT = true)
    async function iniciarPagamentoComBrick(inscricao) {
        if (window.USE_CHECKOUT_PRO_REDIRECT) {
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€šÃ‚Â­ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Checkout Pro redirect ativo; use o botão para redirecionar.');
            return;
        }
        try {
            const btnPagar = document.getElementById('btn-pagar');
            const btnPagarContainer = document.getElementById('btn-pagar-container');
            const janelaPagamento = document.getElementById('janela-pagamento-mercadopago');

            // Desabilitar botão
            btnPagar.disabled = true;
            btnPagar.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processando...
            `;

            // Calcular valor total
            const breakdown = inscricao.breakdown_valores || {};
            const valorTotal = Number(breakdown.valor_total) || Number(inscricao.valor_total) || 0;

            if (valorTotal <= 0) {
                throw new Error('Valor total inválido');
            }

            // Preparar produtos extras
            const produtosExtras = [];
            if (inscricao.produtos_extras && inscricao.produtos_extras.length > 0) {
                inscricao.produtos_extras.forEach(extra => {
                    produtosExtras.push({
                        nome: extra.nome || 'Produto extra',
                        valor: Number(extra.valor) || 0,
                        quantidade: Number(extra.quantidade) || 1
                    });
                });
            }

            // Inicializar MercadoPago
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€šÃ‚Â³ Inicializando MercadoPago...');
            const { mp: mpInstance, bricksBuilder: bricksBuilderInstance } = await inicializarMercadoPago();
            
            if (!bricksBuilder) {
                bricksBuilder = bricksBuilderInstance;
            }

            // Criar preferªncia
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒâ€šÃ‚Â Criando preferªncia...');
            const prefResult = await criarPreference(
                inscricao.id,
                valorTotal,
                inscricao.evento.nome,
                inscricao.modalidade.nome,
                produtosExtras
            );
            const preferenceId = prefResult && (prefResult.preference_id || prefResult);
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Preference criada:', preferenceId);

            // Ocultar botão e mostrar container do brick
            btnPagarContainer.classList.add('hidden');
            janelaPagamento.classList.remove('hidden');
            
            // ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Garantir que o botão PIX tenha o event listener quando a janela for exibida
            // Usar flag para evitar mºltiplas chamadas
            if (!window.pixButtonSetup && typeof setupPixButton === 'function') {
                setTimeout(function() {
                    setupPixButton();
                }, 500);
            }

            // Aguardar um pouco para garantir que o DOM está atualizado
            await new Promise(resolve => setTimeout(resolve, 100));

            // Renderizar Payment Brick
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â½Ãƒâ€šÃ‚Â¨ Renderizando Payment Brick...');
            const builderToUse = bricksBuilder || bricksBuilderInstance || window.bricksBuilder;
            if (!builderToUse) {
                throw new Error('bricksBuilder não está disponível');
            }

            await renderPaymentBrick(builderToUse, preferenceId, valorTotal);
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Payment Brick renderizado com sucesso!');

        } catch (error) {
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Erro ao inicializar pagamento:', error);
            mostrarErro('Erro ao inicializar pagamento: ' + error.message);
            
            // Restaurar botão
            const btnPagar = document.getElementById('btn-pagar');
            if (btnPagar) {
                btnPagar.disabled = false;
                btnPagar.innerHTML = `
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Pagar com Mercado Pago
                `;
            }
        }
    }

    // Expor função globalmente
    window.iniciarPagamentoComBrick = iniciarPagamentoComBrick;

    // Expor getApiUrl globalmente para uso na função PIX
    window.getApiUrl = getApiUrl;

    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Gerar PIX para pagamento
    async function gerarPixPagamento() {
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â¡ÃƒÂ¢ââ‚¬Å¡Ã‚Â¬ gerarPixPagamento() chamada');
        try {
            const btnPix = document.getElementById('btn-pix-pagamento');
            const pixContainer = document.getElementById('pix-container');

            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Elementos PIX:', { btnPix, pixContainer });

            if (!btnPix || !pixContainer) {
                throw new Error('Elementos PIX não encontrados');
            }

            // Estado de loading
            btnPix.disabled = true;
            btnPix.innerHTML = '<span>ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€šÃ‚Â³</span><span>Gerando PIX...</span>';
            btnPix.style.opacity = '0.7';

            // Mostrar container PIX
            pixContainer.classList.remove('hidden');
            pixContainer.innerHTML = `
                <div style="text-align:center;padding:20px;background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef;">
                    <div style="display:inline-block;width:20px;height:20px;border:2px solid #00a650;border-radius:50%;border-top-color:transparent;animation:spin 1s linear infinite;"></div>
                    <p style="margin:12px 0 0 0;color:#6c757d;font-size:14px;">Gerando QR Code PIX...</p>
                </div>
                <style>
                    @keyframes spin { to { transform: rotate(360deg); } }
                </style>
            `;

            // Scroll para o PIX
            pixContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Obter dados da inscrição
            // Tentar obter de window.inscricaoData primeiro, depois de inscricaoData local
            const dadosInscricao = window.inscricaoData || inscricaoData;
            
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Dados da inscrição:', dadosInscricao);
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â window.inscricaoData:', window.inscricaoData);
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â inscricaoData (local):', inscricaoData);
            
            if (!dadosInscricao || !dadosInscricao.id) {
                console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Dados da inscrição não encontrados');
                throw new Error('Dados da inscrição não encontrados. Recarregue a página.');
            }

            // Calcular valor total
            const breakdown = dadosInscricao.breakdown_valores || {};
            const valorTotal = Number(breakdown.valor_total) || Number(dadosInscricao.valor_total) || 0;
            
            if (valorTotal <= 0) {
                throw new Error('Valor total inválido');
            }

            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬ââ€žÂ¢Ãƒâ€šÃ‚Â° Total para PIX:', valorTotal);
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒâ€šÃ‚Â Inscrição ID:', dadosInscricao.id);

            // Criar PIX via API
            const response = await fetch(getApiUrl('inscricao/create_pix.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    inscricao_id: dadosInscricao.id,
                    valor_total: valorTotal
                })
            });

            if (!response.ok) {
                const errorText = await response.text();
                let errorData;
                try {
                    errorData = JSON.parse(errorText);
                } catch (e) {
                    errorData = { error: errorText || `HTTP ${response.status}: ${response.statusText}` };
                }
                throw new Error(errorData.error || errorData.message || `Erro HTTP ${response.status}`);
            }

            const result = await response.json();
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Resultado PIX:', result);

            if (!result.success) {
                throw new Error(result.error || 'Falha ao gerar PIX');
            }

            // Renderizar interface PIX
            pixContainer.innerHTML = `
                <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 24px; margin: 16px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="background: #00a650; color: white; padding: 8px 16px; border-radius: 20px; display: inline-block; font-size: 14px; font-weight: 600; margin-bottom: 12px;">
                            ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬ââ€žÂ¢Ãƒâ€šÃ‚Â³ PIX Instantâneo
                        </div>
                        <h3 style="margin: 0; color: #2c3e50; font-size: 18px; font-weight: 600;">
                            R$ ${result.transaction_amount.toFixed(2).replace('.', ',')}
                        </h3>
                        <p style="margin: 8px 0 0 0; color: #6c757d; font-size: 14px;">
                            Código: #${result.external_reference}
                        </p>
                    </div>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: inline-block;">
                            <img src="data:image/png;base64, ${result.qr_code_base64}" style="width: 180px; height: 180px; border-radius: 8px;" />
                        </div>
                    </div>
                    
                    <div style="background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 16px; margin: 16px 0;">
                        <label style="display: block; font-size: 14px; font-weight: 600; color: #495057; margin-bottom: 8px;">
                            Código PIX (Copie e cole no seu app)
                        </label>
                        <div style="position: relative;">
                            <textarea readonly style="width: 100%; height: 80px; border: 1px solid #ced4da; border-radius: 6px; padding: 12px; font-family: monospace; font-size: 12px; resize: none; background: #f8f9fa;">${result.qr_code}</textarea>
                            <button onclick="navigator.clipboard.writeText(this.previousElementSibling.value); this.textContent='Copiado!'; setTimeout(() => this.textContent='Copiar', 2000);" style="position: absolute; top: 8px; right: 8px; background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">Copiar</button>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="${result.ticket_url}" target="_blank" style="background: #00a650; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: inline-block; transition: background 0.2s;">
                            ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒâ€šÃ‚Â± Abrir no App
                        </a>
                    </div>
                    
                    <div style="margin-top: 16px; padding: 12px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #2196f3;">
                        <p style="margin: 0; font-size: 13px; color: #1565c0;">
                            <strong>ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬ââ€žÂ¢Ãƒâ€šÃ‚Â¡ Dica:</strong> Escaneie o QR Code com seu app bancário ou copie o código PIX para pagar instantaneamente.
                        </p>
                    </div>
                </div>
            `;

            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ PIX gerado com sucesso!');

        } catch (error) {
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Erro ao gerar PIX:', error);

            const pixContainer = document.getElementById('pix-container');
            if (pixContainer) {
                pixContainer.innerHTML = `
                    <div style="text-align:center;padding:20px;background:#fff5f5;border:1px solid #fed7d7;border-radius:8px;">
                        <div style="color:#e53e3e;font-size:24px;margin-bottom:8px;">ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â</div>
                        <p style="margin:0;color:#c53030;font-size:14px;">Falha ao gerar PIX: ${error.message}</p>
                    </div>
                `;
            }

            // Usar SweetAlert para mostrar erro de forma elegante
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao Gerar PIX',
                    text: error.message || 'Não foi possível gerar o código PIX. Tente novamente ou escolha outra forma de pagamento.',
                    confirmButtonText: 'Entendi',
                    confirmButtonColor: '#0b4340'
                });
            } else {
                mostrarErro('Erro ao gerar PIX: ' + error.message);
            }

        } finally {
            // Restaurar botão
            const btnPix = document.getElementById('btn-pix-pagamento');
            if (btnPix) {
                btnPix.disabled = false;
                btnPix.innerHTML = '<span>ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬ââ€žÂ¢Ãƒâ€šÃ‚Â³</span><span>Pagar com PIX</span>';
                btnPix.style.opacity = '1';
            }
        }
    }

    // Expor função globalmente
    window.gerarPixPagamento = gerarPixPagamento;

    // Flag para evitar mºltiplas configuraçÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes
    let pixButtonSetup = false;
    let pixButtonSetupAttempts = 0;
    const MAX_SETUP_ATTEMPTS = 3;
    
    // Expor flag globalmente
    window.pixButtonSetup = false;

    // Função para adicionar event listener ao botão PIX
    function setupPixButton() {
        // Evitar loops infinitos
        if (pixButtonSetup || window.pixButtonSetup) {
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Botão PIX já configurado, ignorando chamada duplicada');
            return;
        }

        if (pixButtonSetupAttempts >= MAX_SETUP_ATTEMPTS) {
            console.warn('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Máximo de tentativas de setup do botão PIX atingido');
            return;
        }

        pixButtonSetupAttempts++;
        const btnPix = document.getElementById('btn-pix-pagamento');
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Procurando botão PIX (tentativa ' + pixButtonSetupAttempts + '):', btnPix);
        
        if (btnPix) {
            // Verificar se já tem listener (evitar duplicação)
            if (btnPix.hasAttribute('data-pix-listener-added')) {
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Botão PIX já tem listener, ignorando');
                pixButtonSetup = true;
                return;
            }

            // Marcar como configurado ANTES de adicionar listener para evitar loops
            pixButtonSetup = true;
            window.pixButtonSetup = true;
            btnPix.setAttribute('data-pix-listener-added', 'true');
            
            btnPix.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Botão PIX clicado!');
                try {
                    await gerarPixPagamento();
                } catch (error) {
                    console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Erro ao executar gerarPixPagamento:', error);
                }
            });
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Event listener do botão PIX adicionado');
        } else {
            console.warn('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Botão PIX não encontrado ainda (tentativa ' + pixButtonSetupAttempts + ')');
            // Tentar novamente apenas se não excedeu o máximo
            if (pixButtonSetupAttempts < MAX_SETUP_ATTEMPTS) {
                setTimeout(setupPixButton, 500);
            }
        }
    }
    
    // Expor setupPixButton globalmente
    window.setupPixButton = setupPixButton;

    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Verificar se usuário tem CPF cadastrado
    async function verificarCPFUsuario() {
        try {
            const response = await fetch(getApiUrl('participante/verificar_cpf.php'), {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error('Erro ao verificar CPF');
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Erro ao verificar CPF:', error);
            return { success: false, tem_cpf: false, cpf: null };
        }
    }

    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Modal para coletar CPF
    async function coletarCPFModal() {
        return new Promise((resolve, reject) => {
            if (typeof Swal === 'undefined') {
                reject(new Error('SweetAlert2 não está disponível'));
                return;
            }
            
            Swal.fire({
                title: 'CPF Obrigatório',
                html: `
                    <p style="text-align: left; margin-bottom: 16px; color: #6c757d;">
                        Para pagar com boleto bancário, é necessário informar seu CPF.
                    </p>
                    <input 
                        type="text" 
                        id="swal-cpf-input" 
                        class="swal2-input" 
                        placeholder="000.000.000-00"
                        maxlength="14"
                        style="text-align: center; font-size: 16px; letter-spacing: 2px;"
                    >
                    <p style="text-align: left; margin-top: 8px; font-size: 12px; color: #6c757d;">
                        Seus dados estão protegidos e serão salvos apenas para processamento do pagamento.
                    </p>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Salvar e Continuar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusConfirm: false,
                preConfirm: async () => {
                    const cpfInput = document.getElementById('swal-cpf-input');
                    const cpf = cpfInput?.value || '';
                    
                    if (!cpf || cpf.trim() === '') {
                        Swal.showValidationMessage('Por favor, informe seu CPF');
                        return false;
                    }
                    
                    // Validar formato básico
                    const cpfLimpo = cpf.replace(/[^0-9]/g, '');
                    if (cpfLimpo.length !== 11) {
                        Swal.showValidationMessage('CPF deve conter 11 dígitos');
                        return false;
                    }
                    
                    // Salvar CPF
                    try {
                        const response = await fetch(getApiUrl('participante/atualizar_cpf.php'), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({ cpf: cpfLimpo })
                        });
                        
                        const data = await response.json();
                        
                        if (!data.success) {
                            Swal.showValidationMessage(data.message || 'Erro ao salvar CPF');
                            return false;
                        }
                        
                        return true;
                    } catch (error) {
                        Swal.showValidationMessage('Erro ao salvar CPF. Tente novamente.');
                        return false;
                    }
                },
                didOpen: () => {
                    const cpfInput = document.getElementById('swal-cpf-input');
                    if (cpfInput) {
                        // Máscara de CPF
                        cpfInput.addEventListener('input', function(e) {
                            let value = e.target.value.replace(/\D/g, '');
                            if (value.length <= 11) {
                                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                                e.target.value = value;
                            }
                        });
                        
                        cpfInput.focus();
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    resolve();
                } else {
                    reject(new Error('CPF não informado'));
                }
            });
        });
    }

    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Verificar endereço do usuário
    async function verificarEnderecoUsuario() {
        try {
            const response = await fetch(getApiUrl('participante/verificar_endereco.php'), {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error('Erro ao verificar endereço');
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Erro ao verificar endereço:', error);
            return { success: false, endereco_completo: false, campos_faltando: [] };
        }
    }

    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Modal para coletar endereço
    async function coletarEnderecoModal() {
        return new Promise((resolve, reject) => {
            if (typeof Swal === 'undefined') {
                reject(new Error('SweetAlert2 não está disponível'));
                return;
            }
            
            Swal.fire({
                title: 'Endereço Obrigatório',
                html: `
                    <p style="text-align: left; margin-bottom: 16px; color: #6c757d;">
                        Para pagar com boleto bancário, é necessário informar seu endereço completo.
                    </p>
                    <div style="text-align: left;">
                        <input 
                            type="text" 
                            id="swal-cep-input" 
                            class="swal2-input" 
                            placeholder="CEP (00000-000)"
                            maxlength="9"
                            style="margin-bottom: 8px;"
                        >
                        <input 
                            type="text" 
                            id="swal-endereco-input" 
                            class="swal2-input" 
                            placeholder="Logradouro (Rua, Avenida, etc.)"
                            style="margin-bottom: 8px;"
                        >
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                            <input 
                                type="text" 
                                id="swal-numero-input" 
                                class="swal2-input" 
                                placeholder="Nºmero"
                            >
                            <input 
                                type="text" 
                                id="swal-complemento-input" 
                                class="swal2-input" 
                                placeholder="Complemento (opcional)"
                            >
                        </div>
                        <input 
                            type="text" 
                            id="swal-bairro-input" 
                            class="swal2-input" 
                            placeholder="Bairro"
                            style="margin-bottom: 8px;"
                        >
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 8px; margin-bottom: 8px;">
                            <input 
                                type="text" 
                                id="swal-cidade-input" 
                                class="swal2-input" 
                                placeholder="Cidade"
                            >
                            <select 
                                id="swal-uf-input" 
                                class="swal2-input"
                                style="padding: 8px 12px;"
                            >
                                <option value="">UF</option>
                                <option value="AC">AC</option>
                                <option value="AL">AL</option>
                                <option value="AP">AP</option>
                                <option value="AM">AM</option>
                                <option value="BA">BA</option>
                                <option value="CE">CE</option>
                                <option value="DF">DF</option>
                                <option value="ES">ES</option>
                                <option value="GO">GO</option>
                                <option value="MA">MA</option>
                                <option value="MT">MT</option>
                                <option value="MS">MS</option>
                                <option value="MG">MG</option>
                                <option value="PA">PA</option>
                                <option value="PB">PB</option>
                                <option value="PR">PR</option>
                                <option value="PE">PE</option>
                                <option value="PI">PI</option>
                                <option value="RJ">RJ</option>
                                <option value="RN">RN</option>
                                <option value="RS">RS</option>
                                <option value="RO">RO</option>
                                <option value="RR">RR</option>
                                <option value="SC">SC</option>
                                <option value="SP">SP</option>
                                <option value="SE">SE</option>
                                <option value="TO">TO</option>
                            </select>
                        </div>
                    </div>
                    <p style="text-align: left; margin-top: 8px; font-size: 12px; color: #6c757d;">
                        Seus dados estão protegidos e serão salvos apenas para processamento do pagamento.
                    </p>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Salvar e Continuar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusConfirm: false,
                width: '600px',
                preConfirm: async () => {
                    const cep = document.getElementById('swal-cep-input')?.value || '';
                    const endereco = document.getElementById('swal-endereco-input')?.value || '';
                    const numero = document.getElementById('swal-numero-input')?.value || '';
                    const complemento = document.getElementById('swal-complemento-input')?.value || '';
                    const bairro = document.getElementById('swal-bairro-input')?.value || '';
                    const cidade = document.getElementById('swal-cidade-input')?.value || '';
                    const uf = document.getElementById('swal-uf-input')?.value || '';
                    
                    // Validar campos obrigatórios
                    const cepLimpo = cep.replace(/\D/g, '');
                    if (!cepLimpo || cepLimpo.length !== 8) {
                        Swal.showValidationMessage('CEP deve conter 8 dígitos');
                        return false;
                    }
                    
                    if (!endereco || endereco.trim() === '') {
                        Swal.showValidationMessage('Logradouro é obrigatório');
                        return false;
                    }
                    
                    if (!numero || numero.trim() === '') {
                        Swal.showValidationMessage('Nºmero é obrigatório');
                        return false;
                    }
                    
                    if (!bairro || bairro.trim() === '') {
                        Swal.showValidationMessage('Bairro é obrigatório');
                        return false;
                    }
                    
                    if (!cidade || cidade.trim() === '') {
                        Swal.showValidationMessage('Cidade é obrigatória');
                        return false;
                    }
                    
                    if (!uf || uf.length !== 2) {
                        Swal.showValidationMessage('UF é obrigatória');
                        return false;
                    }
                    
                    // Salvar endereço
                    try {
                        const response = await fetch(getApiUrl('participante/atualizar_endereco.php'), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                cep: cepLimpo,
                                endereco: endereco.trim(),
                                numero: numero.trim(),
                                complemento: complemento.trim() || null,
                                bairro: bairro.trim(),
                                cidade: cidade.trim(),
                                uf: uf.toUpperCase()
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (!data.success) {
                            Swal.showValidationMessage(data.message || 'Erro ao salvar endereço');
                            return false;
                        }
                        
                        return true;
                    } catch (error) {
                        Swal.showValidationMessage('Erro ao salvar endereço. Tente novamente.');
                        return false;
                    }
                },
                didOpen: () => {
                    const cepInput = document.getElementById('swal-cep-input');
                    if (cepInput) {
                        // Máscara de CEP
                        cepInput.addEventListener('input', function(e) {
                            let value = e.target.value.replace(/\D/g, '');
                            if (value.length <= 8) {
                                value = value.replace(/(\d{5})(\d)/, '$1-$2');
                                e.target.value = value;
                                
                                // Buscar endereço quando CEP tiver 8 dígitos
                                if (value.replace(/\D/g, '').length === 8) {
                                    buscarEnderecoPorCEPModal(value.replace(/\D/g, ''));
                                }
                            }
                        });
                        
                        cepInput.focus();
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    resolve();
                } else {
                    reject(new Error('Endereço não informado'));
                }
            });
        });
    }

    // Função auxiliar para buscar endereço via ViaCEP no modal
    async function buscarEnderecoPorCEPModal(cep) {
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            
            if (!data.erro) {
                const enderecoInput = document.getElementById('swal-endereco-input');
                const bairroInput = document.getElementById('swal-bairro-input');
                const cidadeInput = document.getElementById('swal-cidade-input');
                const ufInput = document.getElementById('swal-uf-input');
                
                if (enderecoInput) enderecoInput.value = data.logradouro || '';
                if (bairroInput) bairroInput.value = data.bairro || '';
                if (cidadeInput) cidadeInput.value = data.localidade || '';
                if (ufInput) ufInput.value = data.uf || '';
                
                // Focar no campo nºmero após preencher
                const numeroInput = document.getElementById('swal-numero-input');
                if (numeroInput) numeroInput.focus();
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
        }
    }

    // ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Gerar Boleto para pagamento
    async function gerarBoletoPagamento() {
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€¦Ã‚Â¡ÃƒÂ¢ââ‚¬Å¡Ã‚Â¬ gerarBoletoPagamento() chamada');
        try {
            // ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Verificar CPF antes de gerar boleto
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Verificando CPF do usuário...');
            const cpfStatus = await verificarCPFUsuario();
            
            if (!cpfStatus.success || !cpfStatus.tem_cpf) {
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â CPF não encontrado, solicitando...');
                // Exibir modal para coletar CPF
                try {
                    await coletarCPFModal();
                    // Após coletar, verificar novamente
                    const novoStatus = await verificarCPFUsuario();
                    if (!novoStatus.success || !novoStatus.tem_cpf) {
                        throw new Error('CPF é obrigatório para pagamento com boleto');
                    }
                    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ CPF coletado e salvo com sucesso');
                } catch (error) {
                    if (error.message === 'CPF não informado') {
                        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Usuário cancelou a coleta de CPF');
                        return; // Usuário cancelou, não fazer nada
                    }
                    throw error;
                }
            } else {
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ CPF já cadastrado, prosseguindo...');
            }
            
            // ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Verificar endereço antes de gerar boleto
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Verificando endereço do usuário...');
            const enderecoStatus = await verificarEnderecoUsuario();
            
            if (!enderecoStatus.success || !enderecoStatus.endereco_completo) {
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Endereço incompleto, solicitando...');
                // Exibir modal para coletar endereço
                try {
                    await coletarEnderecoModal();
                    // Após coletar, verificar novamente
                    const novoStatus = await verificarEnderecoUsuario();
                    if (!novoStatus.success || !novoStatus.endereco_completo) {
                        throw new Error('Endereço completo é obrigatório para pagamento com boleto');
                    }
                    console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Endereço coletado e salvo com sucesso');
                } catch (error) {
                    if (error.message === 'Endereço não informado') {
                        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Usuário cancelou a coleta de endereço');
                        return; // Usuário cancelou, não fazer nada
                    }
                    throw error;
                }
            } else {
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Endereço já cadastrado, prosseguindo...');
            }

            const btnBoleto = document.getElementById('btn-boleto-pagamento');
            const boletoContainer = document.getElementById('boleto-container');

            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Elementos Boleto:', { btnBoleto, boletoContainer });

            if (!btnBoleto || !boletoContainer) {
                throw new Error('Elementos Boleto não encontrados');
            }

            btnBoleto.disabled = true;
            btnBoleto.innerHTML = '<span>ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€šÃ‚Â³</span><span>Gerando Boleto...</span>';
            btnBoleto.style.opacity = '0.7';

            boletoContainer.classList.remove('hidden');
            boletoContainer.innerHTML = `
                <div style="text-align:center;padding:20px;background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef;">
                    <div style="display:inline-block;width:20px;height:20px;border:2px solid #007bff;border-radius:50%;border-top-color:transparent;animation:spin 1s linear infinite;"></div>
                    <p style="margin:12px 0 0 0;color:#6c757d;font-size:14px;">Gerando boleto bancário...</p>
                </div>
                <style>
                    @keyframes spin { to { transform: rotate(360deg); } }
                </style>
            `;

            boletoContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            const dadosInscricao = window.inscricaoData || inscricaoData;
            
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Dados da inscrição:', dadosInscricao);
            
            if (!dadosInscricao || !dadosInscricao.id) {
                console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Dados da inscrição não encontrados');
                throw new Error('Dados da inscrição não encontrados. Recarregue a página.');
            }

            const breakdown = dadosInscricao.breakdown_valores || {};
            const valorTotal = Number(breakdown.valor_total) || Number(dadosInscricao.valor_total) || 0;
            
            if (valorTotal <= 0) {
                throw new Error('Valor total inválido');
            }

            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬ââ€žÂ¢Ãƒâ€šÃ‚Â° Total para Boleto:', valorTotal);
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒâ€šÃ‚Â Inscrição ID:', dadosInscricao.id);

            const response = await fetch(getApiUrl('inscricao/create_boleto.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    inscricao_id: dadosInscricao.id,
                    valor_total: valorTotal
                })
            });

            if (!response.ok) {
                const errorText = await response.text();
                let errorData;
                try {
                    errorData = JSON.parse(errorText);
                } catch (e) {
                    errorData = { error: errorText || `HTTP ${response.status}: ${response.statusText}` };
                }
                throw new Error(errorData.error || errorData.message || `Erro HTTP ${response.status}`);
            }

            const result = await response.json();
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Resultado Boleto completo:', result);
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Barcode recebido:', result.barcode);
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Barcode tipo:', typeof result.barcode);
            console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Barcode vazio?', !result.barcode || result.barcode === '');

            if (!result.success) {
                const usePix = result.use_pix === true || result.error_code === 'BOLETO_REJECTED_BY_BANK';
                const msg = result.message || result.error || 'Não foi possível gerar o boleto.';
                if (usePix && typeof Swal !== 'undefined') {
                    await Swal.fire({
                        icon: 'info',
                        title: 'Boleto indisponível',
                        text: msg,
                        confirmButtonText: 'Entendi',
                        confirmButtonColor: '#0b4340'
                    });
                    const pixSection = document.querySelector('#opcao-pix, [data-forma="pix"], .secao-pix') || document.querySelector('.border-green-500');
                    if (pixSection) {
                        pixSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    return;
                }
                throw new Error(msg);
            }

            // Verificar se barcode está presente
            if (!result.barcode || result.barcode === '') {
                console.warn('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â ATENÃƒÆ’Ã†â€™ÃƒÂ¢ââ€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ ââ‚¬â„¢O: Barcode não foi retornado pela API!');
                console.warn('ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Dados recebidos:', JSON.stringify(result, null, 2));
            }

            const dataVencimento = result.date_of_expiration ? new Date(result.date_of_expiration).toLocaleDateString('pt-BR') : 'N/A';
            
            // Verificar se boleto está próximo do vencimento (menos de 24 horas)
            let avisoVencimento = '';
            if (result.date_of_expiration) {
                const dataVencimentoObj = new Date(result.date_of_expiration);
                const agora = new Date();
                const horasRestantes = (dataVencimentoObj - agora) / (1000 * 60 * 60);
                
                if (horasRestantes < 24 && horasRestantes > 0) {
                    const horas = Math.round(horasRestantes);
                    avisoVencimento = `
                        <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px; margin: 16px 0; text-align: center;">
                            <strong style="color: #856404; font-size: 14px;">ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Atenção!</strong>
                            <p style="margin: 8px 0 0 0; color: #856404; font-size: 13px;">
                                Seu boleto vence em ${horas} ${horas === 1 ? 'hora' : 'horas'}.<br>
                                Realize o pagamento o quanto antes para garantir sua inscrição.
                            </p>
                        </div>
                    `;
                } else if (horasRestantes <= 0) {
                    avisoVencimento = `
                        <div style="background: #f8d7da; border: 1px solid #dc3545; border-radius: 8px; padding: 12px; margin: 16px 0; text-align: center;">
                            <strong style="color: #721c24; font-size: 14px;">ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Boleto Expirado</strong>
                            <p style="margin: 8px 0 0 0; color: #721c24; font-size: 13px;">
                                Este boleto já expirou. Entre em contato com o suporte para gerar um novo boleto.
                            </p>
                        </div>
                    `;
                }
            }

            boletoContainer.innerHTML = `
                <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 24px; margin: 16px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="background: #007bff; color: white; padding: 8px 16px; border-radius: 20px; display: inline-block; font-size: 14px; font-weight: 600; margin-bottom: 12px;">
                            ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒÂ¢ââ€šÂ¬Ã…Â¾ Boleto Bancário
                        </div>
                        <h3 style="margin: 0; color: #2c3e50; font-size: 18px; font-weight: 600;">
                            R$ ${result.transaction_amount.toFixed(2).replace('.', ',')}
                        </h3>
                        <p style="margin: 8px 0 0 0; color: #6c757d; font-size: 14px;">
                            Vencimento: ${dataVencimento}
                        </p>
                    </div>
                    
                    ${avisoVencimento}
                    
                    <div style="background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 16px; margin: 16px 0;">
                        <label style="display: block; font-size: 14px; font-weight: 600; color: #495057; margin-bottom: 8px;">
                            Código de Barras
                        </label>
                        <div style="position: relative;">
                            <input type="text" readonly value="${result.barcode || 'Código de barras não disponível. Use o link abaixo para visualizar o boleto.'}" style="width: 100%; border: 1px solid #ced4da; border-radius: 6px; padding: 12px; font-family: monospace; font-size: 14px; background: #f8f9fa; color: #212529;" id="boleto-barcode-input">
                            ${result.barcode ? `<button onclick="const input = document.getElementById('boleto-barcode-input'); input.select(); document.execCommand('copy'); this.textContent='Copiado!'; setTimeout(() => this.textContent='Copiar', 2000);" style="position: absolute; top: 8px; right: 8px; background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">Copiar</button>` : ''}
                        </div>
                        ${!result.barcode ? `<div style="margin-top: 8px; padding: 8px; background: #fff3cd; border-radius: 4px; font-size: 12px; color: #856404;">
                            <strong>ÃƒÆ’Ã‚Â¢ÃƒÂ¢ââ€šÂ¬Ã…Â¾Ãƒâ€šÃ‚Â¹ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Nota:</strong> O código de barras será exibido no PDF do boleto. Clique em "Baixar Boleto PDF" para visualizar.
                        </div>` : ''}
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px; display: flex; gap: 12px; justify-content: center;">
                        ${result.ticket_url ? `<a href="${result.ticket_url}" target="_blank" rel="noopener noreferrer" onclick="event.preventDefault(); window.open('${result.ticket_url}', '_blank', 'noopener,noreferrer'); return false;" style="background: #007bff; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: inline-block; transition: background 0.2s; cursor: pointer;">
                            ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒâ€šÃ‚Â¥ Baixar Boleto PDF
                        </a>` : ''}
                        ${result.external_resource_url ? `<a href="${result.external_resource_url}" target="_blank" rel="noopener noreferrer" onclick="event.preventDefault(); window.open('${result.external_resource_url}', '_blank', 'noopener,noreferrer'); return false;" style="background: #28a745; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: inline-block; transition: background 0.2s; cursor: pointer;">
                            ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã‚ÂÃƒÂ¢ââ€šÂ¬ââ‚¬Â Ver Boleto Online
                        </a>` : ''}
                        ${!result.ticket_url && !result.external_resource_url ? `<div style="padding: 12px; background: #fff3cd; border-radius: 8px; color: #856404; font-size: 14px;">
                            <strong>ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Atenção:</strong> O boleto ainda não está disponível. Aguarde alguns instantes e tente novamente.
                        </div>` : ''}
                    </div>
                    
                    <div style="margin-top: 16px; padding: 12px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                        <p style="margin: 0; font-size: 13px; color: #856404;">
                            <strong>ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â Importante:</strong> O boleto vence em ${dataVencimento}. Após o pagamento, a confirmação pode levar até 2 dias ºteis.
                        </p>
                    </div>
                </div>
            `;

            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Boleto gerado com sucesso!');

        } catch (error) {
            console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Erro ao gerar Boleto:', error);

            const boletoContainer = document.getElementById('boleto-container');
            if (boletoContainer) {
                boletoContainer.innerHTML = `
                    <div style="text-align:center;padding:20px;background:#fff5f5;border:1px solid #fed7d7;border-radius:8px;">
                        <div style="color:#e53e3e;font-size:24px;margin-bottom:8px;">ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â</div>
                        <p style="margin:0;color:#c53030;font-size:14px;">Falha ao gerar boleto: ${error.message}</p>
                    </div>
                `;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao Gerar Boleto',
                    text: error.message || 'Não foi possível gerar o boleto. Tente novamente ou escolha outra forma de pagamento.',
                    confirmButtonText: 'Entendi',
                    confirmButtonColor: '#0b4340'
                });
            } else {
                mostrarErro('Erro ao gerar boleto: ' + error.message);
            }

        } finally {
            const btnBoleto = document.getElementById('btn-boleto-pagamento');
            if (btnBoleto) {
                btnBoleto.disabled = false;
                btnBoleto.innerHTML = '<span>ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã…â€œÃƒÂ¢ââ€šÂ¬Ã…Â¾</span><span>Pagar com Boleto</span>';
                btnBoleto.style.opacity = '1';
            }
        }
    }

    // Expor função globalmente
    window.gerarBoletoPagamento = gerarBoletoPagamento;

    // Função para adicionar event listener ao botão Boleto
    function setupBoletoButton() {
        const btnBoleto = document.getElementById('btn-boleto-pagamento');
        console.log('ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢ââ€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â Procurando botão Boleto:', btnBoleto);
        
        if (btnBoleto && !btnBoleto.hasAttribute('data-boleto-listener-added')) {
            btnBoleto.setAttribute('data-boleto-listener-added', 'true');
            
            btnBoleto.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Botão Boleto clicado!');
                try {
                    await gerarBoletoPagamento();
                } catch (error) {
                    console.error('ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒâ€¦ââ‚¬â„¢ Erro ao executar gerarBoletoPagamento:', error);
                }
            });
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Event listener do botão Boleto adicionado');
        }
    }
    
    // Expor setupBoletoButton globalmente
    window.setupBoletoButton = setupBoletoButton;

    // Aguardar DOM estar pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Módulo de pagamento carregado');
            setTimeout(setupPixButton, 300);
            setTimeout(setupBoletoButton, 300);
        });
    } else {
        console.log('ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦ Módulo de pagamento carregado (DOM já pronto)');
        setTimeout(setupPixButton, 300);
        setTimeout(setupBoletoButton, 300);
    }
})();

