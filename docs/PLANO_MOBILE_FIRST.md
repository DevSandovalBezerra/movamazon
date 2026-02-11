# Plano Mobile-First - MovAmazon

## Análise Atual
O sistema já possui base sólida com Tailwind CSS e viewport mobile, mas precisa de melhorias para mobile-first.

## Objetivos
1. Menu responsivo com hamburger para mobile
2. Breakpoints otimizados: mobile-first approach
3. Touch-friendly elements (48px mínimo)
4. Performance mobile otimizada
5. Navegação intuitiva em telas pequenas

## Execução por Área

### 1. Sistema de Menu (CRÍTICO)
**Problemas atuais:**
- Sidebar fixo de 256px que não colapsa
- Navegação horizontal em telas pequenas impossível
- Falta de hamburger menu

**Solução:**
```css
/* Menu mobile com hamburger */
@media (max-width: 768px) {
  .mobile-menu {
    @apply flex flex-col fixed top-0 left-0 w-full h-full bg-brand-green z-50 transform transition-transform duration-300 ease-in-out;
    transform: translateX(-100%);
  }
  
  .mobile-menu.open {
    transform: translateX(0);
  }
  
  .hamburger-btn {
    @apply fixed top-4 left-4 z-60 w-12 h-12 bg-brand-green text-white rounded-lg shadow-lg;
  }
}
```

**Arquivos a modificar:**
- `frontend/includes/navbar.php`
- `frontend/includes/sidebar.php`
- `frontend/includes/admin_header.php`

### 2. Breakpoints Mobile-First
**Converter de:**
`md:grid-cols-2 lg:grid-cols-3` (desktop-first)

**Para:**
`grid-cols-1 sm:grid-cols-2 lg:grid-cols-3` (mobile-first)

**Arquivos prioritários:**
- `frontend/paginas/participante/dashboard.php`
- `frontend/paginas/admin/dashboard.php`
- `frontend/paginas/organizador/dashboard.php`

### 3. Touch-Friendly Elements
**Implementar:**
- Botões mínimo 48px x 48px
- Espaçamento entre elementos: 8px mínimo
- Touch target de 44px para ícones

### 4. Performance Mobile
**Ações:**
- Lazy loading para imagens
- Minificar CSS/JS
- Comprimir assets via build system

### 5. Navegação Mobile
**Features:**
- Bottom navigation para telas pequenas
- Swipe gestures para navegação
- Acesso rápido ações principais

## Ordem de Implementação

1. **Menu mobile com hamburger** (CRÍTICO)
2. **Breakpoints mobile-first** em dashboards
3. **Touch-friendly elements** em formulários
4. **Navegação mobile** em páginas principais

## Resultado Esperado
- 100% funcional em telas 320px+
- Experiência tátil otimizada
- Performance mobile aprimorada
- Navegação intuitiva e fluida

## Arquivos Chave
- Menu: `frontend/includes/navbar.php`, `frontend/includes/admin_header.php`
- Layout: Todos `dashboard.php` e páginas principais
- CSS: `frontend/assets/css/mobile.css` (novo arquivo)