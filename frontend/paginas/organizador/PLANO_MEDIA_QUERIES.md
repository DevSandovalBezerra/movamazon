# Plano de Implementação de Media Queries - Módulo Organizador

## Objetivo
Implementar responsividade completa em todas as páginas do módulo organizador usando media queries para 1200px, 768px e 500px.

## Estrutura

### 1. Arquivo CSS Criado
- **Local**: `frontend/assets/css/organizador-responsive.css`
- **Classes**: `.organizador-*` (pode ser usado em qualquer página)

### 2. Breakpoints Definidos
- **1200px+**: Telas grandes (desktop)
- **768px-1199px**: Tablets
- **500px-767px**: Tablets pequenos/mobile
- **Até 500px**: Mobile pequeno

## Aplicação nas Páginas

### Passo 1: Linkar o CSS
Adicionar na página antes do fechamento do `</head>` ou no início:

```php
<link rel="stylesheet" href="../../../frontend/assets/css/organizador-responsive.css">
```

### Passo 2: Adicionar Classes
Usar as classes `.organizador-*` nos elementos HTML:

#### Exemplo - Container Principal
```php
<div class="organizador-container">
    <!-- conteúdo -->
</div>
```

#### Exemplo - Grid de Cards
```php
<div class="organizador-grid">
    <!-- cards -->
</div>
```

#### Exemplo - Filtros
```php
<div class="organizador-filters">
    <!-- campos de filtro -->
</div>
```

#### Exemplo - Header
```php
<div class="organizador-header">
    <h1>Título</h1>
    <button>Botão</button>
</div>
```

### Passo 3: Modais
Usar classe `.organizador-modal` na modal:
```php
<div id="modalProduto" class="organizador-modal fixed inset-0 z-50 hidden">
```

## Páginas a Atualizar (18 páginas)

1. ✅ `produtos/index.php` - Será usado como REFERÊNCIA
2. `eventos/index.php`
3. `modalidades/index.php`
4. `lotes-inscricao/index.php`
5. `cupons-remessa/index.php`
6. `questionario/index.php`
7. `termos-inscricao/index.php`
8. `kits-templates/index.php`
9. `kits-evento/index.php`
10. `retirada-kits/index.php`
11. `camisas/index.php`
12. `produtos-extras/index.php`
13. `programacao/index.php`
14. `participantes/index.php`
15. `pagamentos/index.php`
16. `relatorios/index.php`
17. `estoque/index.php`
18. `lotes/index.php`

## Ordem de Implementação

### Fase 1: Referência (PRODUTOS)
- Aplicar classes em `produtos/index.php`
- Testar em todas as resoluções
- Validar layout

### Fase 2: Replicação
- Copiar estrutura aplicada em produtos
- Adaptar para cada página específica
- Testar cada página

## Estrutura de Aplicação por Página

### Template Base para Cada Index
```php
<!-- Container com scroll e responsividade -->
<div class="organizador-container h-full overflow-y-auto px-4 py-6">
    
    <!-- Navegação sequencial (tabs) -->
    <div class="organizador-nav-tabs mb-6 flex gap-4">
        <!-- tabs -->
    </div>
    
    <!-- Header da página -->
    <div class="organizador-header flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Título</h1>
            <p class="text-gray-600">Descrição</p>
        </div>
        <button class="organizador-btn btn-primary">Novo</button>
    </div>
    
    <!-- Filtros -->
    <div class="organizador-filters card mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- filtros -->
        </div>
    </div>
    
    <!-- Conteúdo principal -->
    <div class="organizador-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- cards ou lista -->
    </div>
    
    <!-- Modal (com classe organizador-modal) -->
    <div id="modal" class="organizador-modal">
        <!-- conteúdo modal -->
    </div>
</div>
```

## Breakpoints Detalhados

### ≥1200px (Desktop Grande)
- Grid: 4 colunas
- Sidebar: 256px fixo
- Modal: 1000px largura
- Padding: 24px

### 768px-1199px (Tablet)
- Grid: 3 colunas → 2 colunas
- Filtros: empilhar vertical
- Header: stack vertical
- Button: full width quando necessário

### 500px-767px (Tablet Pequeno)
- Grid: 2 colunas → 1 coluna
- Filtros: 1 coluna
- Modal: 95% largura
- Cards: padding reduzido

### ≤500px (Mobile)
- Grid: 1 coluna
- Tudo empilhado
- Modal: fullscreen (100%)
- Tabs: quebra linha se necessário

## Exemplo Completo - produtos/index.php

```php
<?php
$pageTitle = 'Produtos';
$pageSubtitle = 'Gerencie todos os produtos dos seus eventos';
$currentPage = 'produtos';

ob_start();
?>

<!-- LINK DO CSS RESPONSIVE -->
<link rel="stylesheet" href="../../../frontend/assets/css/organizador-responsive.css">

<!-- Container com scroll e responsividade -->
<div class="organizador-container h-full overflow-y-auto px-4 py-6">
    
    <!-- Navegação Sequencial -->
    <div class="organizador-nav-tabs mb-6 flex gap-4">
        <!-- tabs -->
    </div>
    
    <!-- Header -->
    <div class="organizador-header flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestão de Produtos</h1>
            <p class="text-gray-600 mt-1">Gerencie todos os produtos dos seus eventos</p>
        </div>
        <button id="btnNovoProduto" class="organizador-btn btn-primary">
            <i class="fas fa-plus mr-2"></i>Novo Produto
        </button>
    </div>
    
    <!-- Filtros -->
    <div class="organizador-filters card mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- filtros aqui -->
        </div>
    </div>
    
    <!-- Grid de Produtos -->
    <div class="organizador-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="produtos-container">
        <!-- cards -->
    </div>
    
    <!-- Modal com classe -->
    <div id="modalProduto" class="organizador-modal fixed inset-0 z-50 hidden">
        <!-- conteúdo modal -->
    </div>
</div>

<script src="../../../frontend/js/produtos.js"></script>

<?php
$pageContent = ob_get_clean();
echo $pageContent;
?>
```

## Classes CSS Disponíveis

### Container e Layout
- `.organizador-container` - Container principal com max-width responsivo
- `.organizador-grid` - Grid de cards responsivo
- `.organizador-header` - Header com layout flex responsivo
- `.organizador-nav-tabs` - Tabs de navegação sequencial

### Componentes
- `.organizador-card` - Card individual com padding responsivo
- `.organizador-btn` - Botão com largura responsiva
- `.organizador-filters` - Container de filtros responsivo
- `.organizador-modal` - Modal com largura responsiva

## Checklist por Página

Para cada página, verificar:
- [ ] CSS linkado corretamente
- [ ] Container com classe `.organizador-container`
- [ ] Grid com classe `.organizador-grid`
- [ ] Filtros com classe `.organizador-filters`
- [ ] Header com classe `.organizador-header`
- [ ] Botões com classe `.organizador-btn`
- [ ] Modal com classe `.organizador-modal`
- [ ] Testar em 1200px
- [ ] Testar em 768px
- [ ] Testar em 500px
- [ ] Validar funcionalidade

## Próximos Passos

1. Aplicar na página produtos/index.php como REFERÊNCIA
2. Testar em todas as resoluções
3. Documentar ajustes específicos necessários
4. Replicar para outras 17 páginas
5. Validação final

