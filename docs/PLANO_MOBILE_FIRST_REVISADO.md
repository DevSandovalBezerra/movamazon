# Plano Mobile-First - MovAmazon (Revisado)

## Objetivo
Aplicar mobile-first em todas as páginas do frontend mantendo o fluxo atual e sem alterar regras de negócio, com foco especial no comportamento do menu.

## Premissas
- Não alterar lógica de negócio, endpoints ou regras de validação.
- Alterações apenas de layout, estrutura e responsividade.
- Manter compatibilidade com Tailwind e CSS custom já existente.
- Evitar mudanças em layout de desktop quando possível.

## Diagnóstico Atual
- Sidebar fixa não colapsa em mobile.
- Falta de hamburger e navegação simplificada em telas pequenas.
- Breakpoints inconsistentes entre páginas.
- Botões e alvos de toque abaixo de 48px em vários fluxos.

## Arquivos-Chave (Real)
### Base global
- `frontend/includes/header.php` (inclui CSS e scripts globais)
- `frontend/assets/css/mobile-only.css` (CSS mobile-first real)

### Participante
- `frontend/paginas/participante/index.php` (layout base participante)
- `frontend/paginas/participante/dashboard.php`
- `frontend/paginas/participante/meus-treinos.php`

### Admin
- `frontend/paginas/admin/index.php` (layout base admin)
- `frontend/paginas/admin/dashboard.php`

### Menus e navegação
- `frontend/includes/mobile-menu.php`
- `frontend/includes/mobile-bottom-nav.php`

## Estratégia Mobile-First
### 1) Sistema de Menu (Crítico)
**Objetivo:** menu funcional com hamburger e overlay, com fechamento por toque/ESC.

**Implementação:**
- Hamburger fixo no topo (mobile).
- Menu lateral em overlay.
- Gestos de swipe (esquerda → abre, direita → fecha).
- Overlay escurecido para foco.

**Arquivos alvo:**
- `frontend/includes/header.php`
- `frontend/includes/mobile-menu.php`
- `frontend/includes/mobile-bottom-nav.php`

### 2) Breakpoints Mobile-First
**Padrão recomendado:**
- Antes: `md:grid-cols-2 lg:grid-cols-3`
- Depois: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`

**Páginas prioritárias:**
- `frontend/paginas/participante/dashboard.php`
- `frontend/paginas/admin/dashboard.php`
- `frontend/paginas/organizador/dashboard.php`

### 3) Touch-Friendly
**Regras mínimas:**
- Botões: 48x48px
- Ícones clicáveis: 44x44px
- Espaçamento entre itens: mínimo 8px
- Inputs: altura mínima de 44px

**Locais críticos:**
- Formulários de inscrição
- Botões de ação rápida nos dashboards
- Barra inferior (bottom nav)

### 4) Navegação Mobile
**Bottom navigation:**
- Máximo de 4–5 itens
- Itens por perfil (participante/admin)
- Ações secundárias permanecem no hamburger

### 5) Performance Mobile
**Ações objetivas:**
- Lazy loading de imagens de eventos
- Reduzir sombras e efeitos pesados em mobile
- Garantir carregamento de CSS mobile-first antes de customizações

## Impacto no Fluxo (Checklist)
- Não alterar URLs ou rotas existentes
- Não alterar validações de API
- Não alterar regras de autorização
- Manter classes existentes quando possível
- Garantir desktop intacto (regressão zero)

## Ordem de Execução
1) Menu mobile e overlay (header + mobile menu)
2) Bottom navigation por perfil
3) Ajustes mobile-first nos dashboards
4) Ajustes mobile-first nas páginas de fluxo crítico (inscrição/pagamento)
5) Ajustes de performance (imagens e sombras)

## Resultado Esperado
- 100% funcional em telas 320px+
- Acesso rápido às ações essenciais
- Layout consistente entre perfis
- Experiência mobile estável sem regressão no desktop
