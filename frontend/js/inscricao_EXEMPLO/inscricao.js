// Orquestrador do sistema de inscrição - usa arquivos específicos de cada fase

// Base dinâmico para APIs (funciona em subpasta como /movamazonas e em raiz)
if (!window.API_BASE) {
  (function () {
    var path = window.location.pathname || '';
    var idx = path.indexOf('/frontend/');
    window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
  })();
}

// Configuração das etapas (Fase 3 - Identificação removida - usuários são redirecionados para login/registro)
const inscricaoSteps = [{
    id: 'modalidade',
    title: 'Escolha da Modalidade',
    file: 'modalidade.js',
    class: 'EtapaModalidade'
  },
  {
    id: 'termos',
    title: 'Termos e Condições',
    file: 'termos.js',
    class: 'EtapaTermos'
  },
  {
    id: 'ficha',
    title: 'Ficha de Inscrição',
    file: 'ficha.js',
    class: 'EtapaFicha'
  },
  {
    id: 'pagamento',
    title: 'Pagamento',
    file: 'pagamento.js',
    class: 'EtapaPagamento'
  }
];

class InscricaoFormManager {
  constructor(containerId) {
    this.container = document.getElementById(containerId);
    this.currentStep = 0;
    this.formData = {};
    this.etapas = {};
    this.init();
  }

  init() {
    this.carregarScripts();
    this.renderStep();
  }

  async carregarScripts() {
    // Carregar scripts das etapas dinamicamente
    for (const step of inscricaoSteps) {
      if (!document.querySelector(`script[src*="${step.file}"]`)) {
        await this.carregarScript(`/frontend/js/inscricao/${step.file}`);
      }
    }
  }

  carregarScript(src) {
    return new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = src;
      script.onload = resolve;
      script.onerror = reject;
      document.head.appendChild(script);
    });
  }

  renderStep() {
    const step = inscricaoSteps[this.currentStep];
    this.container.innerHTML = `
      <div id="etapa-${step.id}" class="p-6">
        <h4 class="mb-4 text-lg font-semibold text-gray-800">${step.title}</h4>
        <div id="conteudo-${step.id}" class="etapa-conteudo">
          <div class="text-center text-gray-500">Carregando ${step.title.toLowerCase()}...</div>
        </div>
        <div class="mt-6 flex items-center justify-between">
          <button type="button" class="inline-flex items-center rounded-lg bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed" ${this.currentStep === 0 ? 'disabled' : ''} id="btnAnterior">Anterior</button>
          ${this.currentStep === inscricaoSteps.length - 1 ?
            '<button type="button" id="btnFinalizar" class="inline-flex items-center rounded-lg bg-brand-green px-5 py-2 font-semibold text-white hover:bg-green-700">Finalizar Inscrição</button>' :
            '<button type="button" id="btnProximo" class="inline-flex items-center rounded-lg bg-brand-green px-5 py-2 font-semibold text-white hover:bg-green-700">Próximo</button>'
          }
        </div>
      </div>
    `;
    this.updateProgressBar();
    this.setupListeners();
    this.inicializarEtapa();
  }

  setupListeners() {
    const btnProximo = document.getElementById('btnProximo');
    if (btnProximo) {
      btnProximo.addEventListener('click', () => {
        this.prosseguirEtapa();
      });
    }

    const btnAnterior = document.getElementById('btnAnterior');
    if (btnAnterior) {
      btnAnterior.addEventListener('click', () => {
        this.voltarEtapa();
      });
    }

    const btnFinalizar = document.getElementById('btnFinalizar');
    if (btnFinalizar) {
      btnFinalizar.addEventListener('click', () => {
        this.finalizarInscricao();
      });
    }
  }

  inicializarEtapa() {
    const step = inscricaoSteps[this.currentStep];
    const container = document.getElementById(`conteudo-${step.id}`);

    if (!container) return;

    // Carregar conteúdo específico da etapa
    switch (step.id) {
      case 'modalidade':
        this.carregarModalidades(container);
        break;
      case 'termos':
        this.carregarTermos(container);
        break;
      case 'ficha':
        this.carregarFicha(container);
        break;
      case 'pagamento':
        this.carregarPagamento(container);
        break;
    }
  }

  // Métodos para carregar conteúdo de cada etapa
  carregarModalidades(container) {
    fetch(`${window.API_BASE}/api/inscricao/list_modalidades_kits.php?evento_id=${window.eventoId || ''}`)
      .then(res => res.json())
      .then(resp => {
        if (!resp.success) {
          container.innerHTML = '<div class="text-center text-red-500">Erro ao carregar modalidades</div>';
          return;
        }
        console.log('🔵 [DEBUG] Opções:', resp.opcoes);
        if (resp.opcoes.length === 0) {
          container.innerHTML = '<div class="text-center text-gray-500">Nenhuma modalidade disponível</div>';
          return;
        }

        container.innerHTML = this.renderizarCardsModalidades(resp.opcoes);
        this.setupListenersCards();
      })
      .catch(() => {
        container.innerHTML = '<div class="text-center text-red-500">Erro ao carregar modalidades</div>';
      });
  }

  carregarTermos(container) {
    const modalidadeId = this.formData.modalidade_id;
    if (!modalidadeId) {
      container.innerHTML = '<div class="text-center text-red-500">Erro: Modalidade não selecionada</div>';
      return;
    }

    fetch(`${window.API_BASE}/api/inscricao/get_termos.php?evento_id=${window.eventoId || ''}&modalidade_id=${modalidadeId}`)
      .then(res => res.json())
      .then(resp => {
        if (!resp.success) {
          container.innerHTML = '<div class="text-center text-red-500">Erro ao carregar termos</div>';
          return;
        }

        container.innerHTML = this.renderizarTermos(resp.termos);
      })
      .catch(() => {
        container.innerHTML = '<div class="text-center text-red-500">Erro ao carregar termos</div>';
      });
  }

  carregarIdentificacao(container) {
    //console.log('🔵 [DEBUG] Carregando identificação...');
    //console.log('🔵 [DEBUG] Container:', container);

    container.innerHTML = `
      <div class="space-y-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h5 class="font-semibold text-blue-800 mb-2">Faça login ou crie sua conta</h5>
          <p class="text-sm text-blue-600">Para continuar com a inscrição, você precisa estar logado.</p>
        </div>
        
        <!-- Tabs de Login/Registro -->
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8">
            <button class="tab-btn active py-2 px-1 border-b-2 border-brand-green font-medium text-sm text-brand-green" data-tab="login">
              Entrar
            </button>
            <button class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="registro">
              Criar Conta
            </button>
          </nav>
        </div>

        <!-- Conteúdo das Tabs -->
        <div class="tab-content active" id="login" style="display: block !important;">
          <form id="loginForm" class="space-y-4">
            <div>
              <label for="loginIdentificacao" class="block text-sm font-medium text-gray-700 mb-1">
                E-mail ou CPF
              </label>
              <input type="text" id="loginIdentificacao" name="identificacao" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent">
            </div>
            
            <div>
              <label for="loginSenha" class="block text-sm font-medium text-gray-700 mb-1">
                Senha
              </label>
              <div class="relative">
                <input type="password" id="loginSenha" name="senha" required
                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent">
                <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center">
                  <i class="fas fa-eye text-gray-400"></i>
                </button>
              </div>
            </div>
            
            <div class="flex items-center justify-between">
              <button type="button" onclick="recuperarSenha()" class="text-sm text-brand-green hover:text-green-700">
                Esqueci minha senha
              </button>
            </div>
            
            <button type="submit" class="w-full bg-brand-green text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-brand-green focus:ring-offset-2">
              Entrar
            </button>
          </form>
        </div>

        <div class="tab-content" id="registro" style="display: none !important;">
          <form id="registroForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="registroNome" class="block text-sm font-medium text-gray-700 mb-1">
                  Nome Completo *
                </label>
                <input type="text" id="registroNome" name="nome_completo" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent">
              </div>
              
              <div>
                <label for="registroEmail" class="block text-sm font-medium text-gray-700 mb-1">
                  E-mail *
                </label>
                <input type="email" id="registroEmail" name="email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent">
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="registroCPF" class="block text-sm font-medium text-gray-700 mb-1">
                  CPF
                </label>
                <input type="text" id="registroCPF" name="documento" placeholder="000.000.000-00"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent">
              </div>
              
              <div>
                <label for="registroDataNasc" class="block text-sm font-medium text-gray-700 mb-1">
                  Data de Nascimento *
                </label>
                <input type="date" id="registroDataNasc" name="data_nascimento" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent">
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="registroSexo" class="block text-sm font-medium text-gray-700 mb-1">
                  Sexo *
                </label>
                <select id="registroSexo" name="sexo" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent">
                  <option value="">Selecione</option>
                  <option value="Masculino">Masculino</option>
                  <option value="Feminino">Feminino</option>
                  <option value="Outro">Outro</option>
                </select>
              </div>
              
              <div>
                <label for="registroTelefone" class="block text-sm font-medium text-gray-700 mb-1">
                  Telefone *
                </label>
                <input type="tel" id="registroTelefone" name="telefone" required placeholder="(00) 00000-0000"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent">
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="registroSenha" class="block text-sm font-medium text-gray-700 mb-1">
                  Senha *
                </label>
                <div class="relative">
                  <input type="password" id="registroSenha" name="senha" required
                         class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent">
                  <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center">
                    <i class="fas fa-eye text-gray-400"></i>
                  </button>
                </div>
              </div>
              
              <div>
                <label for="registroConfirmarSenha" class="block text-sm font-medium text-gray-700 mb-1">
                  Confirmar Senha *
                </label>
                <div class="relative">
                  <input type="password" id="registroConfirmarSenha" name="confirmar_senha" required
                         class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent">
                  <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center">
                    <i class="fas fa-eye text-gray-400"></i>
                  </button>
                </div>
              </div>
            </div>
            
            <button type="submit" class="w-full bg-brand-green text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-brand-green focus:ring-offset-2">
              Criar Conta
            </button>
          </form>
        </div>
        
        <!-- Interface do usuário logado -->
        <div class="usuario-logado-container" style="display: none;">
          <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <h5 class="font-semibold text-green-800">Usuário identificado</h5>
                <p class="text-sm text-green-600" id="usuario-nome">Nome do usuário</p>
                <p class="text-sm text-green-600" id="usuario-email">email@exemplo.com</p>
              </div>
              <button type="button" onclick="alterarUsuario()" class="text-sm text-green-700 hover:text-green-900 underline">
                Alterar usuário
              </button>
            </div>
          </div>
        </div>
      </div>
    `;

    console.log('🔵 [DEBUG] HTML renderizado, verificando elementos...');
    console.log('🔵 [DEBUG] Tab buttons encontradas:', document.querySelectorAll('.tab-btn').length);
    console.log('🔵 [DEBUG] Tab contents encontradas:', document.querySelectorAll('.tab-content').length);

    // BINDAR EVENTOS DIRETAMENTE - SEM DEPENDER DA CLASSE
    setTimeout(() => {
      console.log('🔵 [DEBUG] Bindando eventos diretamente...');
      this.bindarEventosIdentificacao();
    }, 100);
  }

  bindarEventosIdentificacao() {
    console.log('🔵 [DEBUG] Bindando eventos de identificação diretamente...');

    // Event listeners para tabs
    const tabBtns = document.querySelectorAll('.tab-btn');
    console.log('🔵 [DEBUG] Tab buttons encontradas para bind:', tabBtns.length);

    tabBtns.forEach(btn => {
      btn.addEventListener('click', (e) => {
        console.log('🔵 [DEBUG] Tab clicada:', e.target.dataset.tab);
        this.trocarTabIdentificacao(e.target.dataset.tab);
      });
    });

    console.log('🔵 [DEBUG] Eventos de identificação bindados com sucesso!');
  }

  trocarTabIdentificacao(tabName) {
    console.log('🔵 [DEBUG] Trocando para tab:', tabName);

    // Remover active de todas as tabs
    const allTabBtns = document.querySelectorAll('.tab-btn');
    const allTabContents = document.querySelectorAll('.tab-content');

    allTabBtns.forEach(btn => {
      btn.classList.remove('active');
    });
    allTabContents.forEach(content => {
      content.classList.remove('active');
      content.style.display = 'none';
    });

    // Ativar tab selecionada
    const tabBtn = document.querySelector(`[data-tab="${tabName}"]`);
    const tabContent = document.getElementById(tabName);

    if (tabBtn) {
      tabBtn.classList.add('active');
      console.log('🔵 [DEBUG] Tab button ativada:', tabName);
    }

    if (tabContent) {
      tabContent.classList.add('active');
      tabContent.style.display = 'block';
      console.log('🔵 [DEBUG] Tab content ativada:', tabName);
    }
  }

  carregarFicha(container) {
    container.innerHTML = `
      <div class="space-y-4">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
          <h5 class="font-semibold text-green-800 mb-2">Confirme seus dados</h5>
          <p class="text-sm text-green-600">Verifique e complete as informações necessárias.</p>
        </div>
        <div id="form-ficha">
          <!-- Conteúdo será carregado pelo ficha.js -->
        </div>
      </div>
    `;

    // Inicializar etapa de ficha
    if (window.EtapaFicha) {
      this.etapas.ficha = new window.EtapaFicha();
    }
  }

  carregarPagamento(container) {
    container.innerHTML = `
      <div class="space-y-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <h5 class="font-semibold text-yellow-800 mb-2">Finalize sua inscrição</h5>
          <p class="text-sm text-yellow-600">Escolha a forma de pagamento e confirme sua inscrição.</p>
        </div>
        <div id="form-pagamento">
          <!-- Conteúdo será carregado pelo pagamento.js -->
        </div>
      </div>
    `;

    // Inicializar etapa de pagamento
    if (window.EtapaPagamento) {
      this.etapas.pagamento = new window.EtapaPagamento();
    }
  }

  updateProgressBar() {
    const steps = document.querySelectorAll('.progress-steps .step');
    const connectors = document.querySelectorAll('.progress-steps .step-connector');
    if (!steps || steps.length === 0) return;
    const total = steps.length;
    const currentIndex = this.currentStep; // 0-based
    steps.forEach((el, idx) => {
      el.classList.remove('pendente', 'atual', 'concluida');
      if (idx < currentIndex) {
        el.classList.add('concluida');
      } else if (idx === currentIndex) {
        el.classList.add('atual');
      } else {
        el.classList.add('pendente');
      }
    });
    if (connectors && connectors.length) {
      connectors.forEach((c, idx) => {
        if (idx < currentIndex) c.classList.add('completed');
        else c.classList.remove('completed');
      });
    }
  }

  // Métodos de navegação
  prosseguirEtapa() {
    const step = inscricaoSteps[this.currentStep];

    // Validar etapa atual
    if (!this.validarEtapaAtual()) {
      return;
    }

    // Salvar dados da etapa atual
    this.salvarDadosEtapa();

    // Avançar para próxima etapa
    if (this.currentStep < inscricaoSteps.length - 1) {
      this.currentStep++;
      this.renderStep();
    }
  }

  voltarEtapa() {
    if (this.currentStep > 0) {
      this.currentStep--;
      this.renderStep();
    }
  }

  validarEtapaAtual() {
    const step = inscricaoSteps[this.currentStep];

    switch (step.id) {
      case 'modalidade':
        const cardSelecionado = document.querySelector('.card-modalidade.border-brand-green');
        if (!cardSelecionado) {
          Swal.fire({
            icon: 'warning',
            title: 'Selecione uma modalidade',
            text: 'Por favor, escolha uma modalidade para continuar!'
          });
          return false;
        }
        break;

      case 'termos':
        const checkboxTermos = document.getElementById('aceite-termos');
        if (!checkboxTermos || !checkboxTermos.checked) {
          Swal.fire({
            icon: 'warning',
            title: 'Aceite os termos',
            text: 'Você deve aceitar os termos e condições para continuar'
          });
          return false;
        }
        break;

      case 'identificacao':
        if (this.etapas.identificacao && !this.etapas.identificacao.getUsuarioIdentificado()) {
          Swal.fire({
            icon: 'warning',
            title: 'Login necessário',
            text: 'Você deve fazer login ou criar uma conta para continuar'
          });
          return false;
        }
        break;

      case 'ficha':
        if (this.etapas.ficha && !this.etapas.ficha.validarFormulario()) {
          Swal.fire({
            icon: 'warning',
            title: 'Campos obrigatórios',
            text: 'Preencha todos os campos obrigatórios'
          });
          return false;
        }
        break;

      case 'pagamento':
        if (this.etapas.pagamento && !this.etapas.pagamento.validarFormulario()) {
          Swal.fire({
            icon: 'warning',
            title: 'Campos de pagamento',
            text: 'Preencha todos os campos de pagamento'
          });
          return false;
        }
        break;
    }

    return true;
  }

  salvarDadosEtapa() {
    const step = inscricaoSteps[this.currentStep];

    switch (step.id) {
      case 'modalidade':
        const cardSelecionado = document.querySelector('.card-modalidade.border-brand-green');
        if (cardSelecionado) {
          this.formData.modalidade_id = cardSelecionado.dataset.modalidadeId;
          this.formData.categoria_id = cardSelecionado.dataset.categoriaId;
          this.formData.tipo_publico = cardSelecionado.dataset.tipoPublico;
          this.formData.kit_id = cardSelecionado.dataset.kitId || null;
          this.formData.preco = cardSelecionado.dataset.preco;
          this.formData.taxa_servico = cardSelecionado.dataset.taxa;
          this.formData.quem_paga_taxa = cardSelecionado.dataset.quemPagaTaxa;
        }
        break;

      case 'termos':
        const checkboxTermos = document.getElementById('aceite-termos');
        if (checkboxTermos) {
          this.formData.aceite_termos = checkboxTermos.checked;
          this.formData.termos_id = checkboxTermos.dataset.termosId;
          this.formData.versao_termos = checkboxTermos.dataset.versaoTermos;
          this.formData.data_aceite_termos = new Date().toISOString();
        }
        break;

      case 'identificacao':
        if (this.etapas.identificacao) {
          const dadosUsuario = this.etapas.identificacao.getDadosUsuario();
          if (dadosUsuario) {
            this.formData.usuario_id = dadosUsuario.id;
            this.formData.usuario_dados = dadosUsuario;
          }
        }
        break;

      case 'ficha':
        if (this.etapas.ficha) {
          // Integrar com dados da ficha
          this.formData.tamanho_camiseta = this.etapas.ficha.getTamanhoCamiseta();
          this.formData.respostas_questionario = this.etapas.ficha.getRespostasQuestionario();
          this.formData.produtos_extras = this.etapas.ficha.getProdutosExtras();
          this.formData.cupom_aplicado = this.etapas.ficha.getCupomAplicado();
          this.formData.valor_desconto = this.etapas.ficha.getValorDesconto();
        }
        break;

      case 'pagamento':
        if (this.etapas.pagamento) {
          this.formData.dados_pagamento = this.etapas.pagamento.getDadosPagamento();
        }
        break;
    }
  }

  // Métodos de renderização (mantidos da implementação anterior)
  renderizarCardsModalidades(opcoes) {
    let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';

    opcoes.forEach(opcao => {
      if (opcao.kits.length === 0) {
        html += this.renderizarCardModalidade(opcao, null);
      } else {
        opcao.kits.forEach(kit => {
          html += this.renderizarCardModalidade(opcao, kit);
        });
      }
    });

    html += '</div>';
    return html;
  }

  renderizarCardModalidade(opcao, kit) {
    const preco = kit ? kit.preco_lote : opcao.preco_lote;
    const taxa = kit ? kit.taxa_servico : opcao.taxa_servico;
    const quemPagaTaxa = kit ? kit.quem_paga_taxa : opcao.quem_paga_taxa;
    const itens = kit ? kit.itens : [];
    const foto = kit ? kit.foto_kit : null;

    return `
      <div class="card-modalidade bg-white border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-brand-green transition-colors" 
           data-modalidade-id="${opcao.modalidade_id}" 
           data-categoria-id="${opcao.categoria_id}" 
           data-tipo-publico="${opcao.tipo_publico}"
           ${kit ? `data-kit-id="${kit.kit_id}"` : ''}
           data-preco="${preco}"
           data-taxa="${taxa}"
           data-quem-paga-taxa="${quemPagaTaxa}">
        
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1">
            <h3 class="font-semibold text-gray-800">${opcao.modalidade_nome}</h3>
            <p class="text-sm text-gray-600">${opcao.distancia} • ${opcao.categoria_nome}</p>
            ${kit ? `<p class="text-sm text-brand-green font-medium">${kit.kit_nome}</p>` : ''}
          </div>
          <div class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center">
            ${foto ? `<img src="${window.API_BASE}/frontend/assets/img/kits/${foto}" alt="Kit" class="w-32 h-32 object-contain">` : '<div class="w-32 h-32 bg-gray-300 rounded"></div>'}
          </div>
        </div>
        
        ${itens.length > 0 ? `
          <div class="mb-3">
            <p class="text-xs text-gray-500 mb-1">Inclui:</p>
            <div class="flex flex-wrap gap-1">
              ${itens.map(item => `<span class="text-xs bg-gray-100 px-2 py-1 rounded">${item}</span>`).join('')}
            </div>
          </div>
        ` : ''}
        
        <div class="flex items-center justify-between">
          <div>
            <p class="text-lg font-bold text-brand-green">R$ ${preco.toFixed(2)}</p>
            ${quemPagaTaxa === 'participante' && taxa > 0 ? `<p class="text-xs text-gray-500">+ R$ ${taxa.toFixed(2)} taxa</p>` : ''}
          </div>
          <div class="w-6 h-6 border-2 border-gray-300 rounded-full flex items-center justify-center">
            <div class="w-3 h-3 bg-brand-green rounded-full hidden"></div>
          </div>
        </div>
      </div>
    `;
  }

  setupListenersCards() {
    const cards = document.querySelectorAll('.card-modalidade');
    cards.forEach(card => {
      card.addEventListener('click', () => {
        cards.forEach(c => {
          c.classList.remove('border-brand-green', 'bg-green-50');
          c.classList.add('border-gray-200');
          c.querySelector('.w-3.h-3').classList.add('hidden');
        });

        card.classList.remove('border-gray-200');
        card.classList.add('border-brand-green', 'bg-green-50');
        card.querySelector('.w-3.h-3').classList.remove('hidden');

        const btnProximo = document.getElementById('btnProximo');
        if (btnProximo) {
          btnProximo.disabled = false;
        }
      });
    });
  }

  renderizarTermos(termos) {
    return `
      <div class="bg-gray-50 rounded-lg p-4 mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">${termos.titulo}</h3>
        <div class="max-h-64 overflow-y-auto text-sm text-gray-700 leading-relaxed border rounded p-3 bg-white">
          ${termos.conteudo.replace(/\n/g, '<br>')}
        </div>
        <div class="mt-3 text-xs text-gray-500">
          Versão: ${termos.versao} | Tipo: ${termos.tipo}
        </div>
      </div>
      
      <div class="mb-4">
        <label class="flex items-start gap-3 cursor-pointer">
          <input 
            class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-green focus:ring-brand-green" 
            type="checkbox" 
            id="aceite-termos" 
            name="aceite-termos" 
            required
            data-termos-id="${termos.id}"
            data-versao-termos="${termos.versao}">
          <span class="text-sm text-gray-700">
            Li e aceito os <strong>Termos e Condições</strong> acima *
          </span>
        </label>
      </div>
    `;
  }

  finalizarInscricao() {
    if (!this.validarEtapaAtual()) {
      return;
    }

    this.salvarDadosEtapa();

    // Enviar inscrição
    fetch(`${window.API_BASE}/api/inscricao/create.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(this.formData)
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.success) {
          this.container.innerHTML = '<div class="p-6 text-green-700 bg-green-50 rounded-lg">Inscrição realizada com sucesso!</div>';
        } else {
          this.container.innerHTML = `<div class="p-6 text-red-700 bg-red-50 rounded-lg">${resp.error || 'Erro ao salvar inscrição.'}</div>`;
        }
      })
      .catch(() => {
        this.container.innerHTML = '<div class="p-6 text-red-700 bg-red-50 rounded-lg">Erro de conexão ao salvar inscrição.</div>';
      });
  }


}

// Inicialização automática ao abrir o modal
window.renderInscricaoForm = function () {
  new InscricaoFormManager('container-etapa-inscricao');
};

if (!window.SistemaInscricao) {
  window.SistemaInscricao = InscricaoFormManager;
}