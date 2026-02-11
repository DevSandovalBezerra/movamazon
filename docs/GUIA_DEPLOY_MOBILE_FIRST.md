# Guia de Atualização na Hospedagem - Mobile First

## Objetivo
Aplicar a implementação mobile-first com menu móvel, bottom navigation e ajustes de layout responsivo.

## 1) Arquivos para subir na hospedagem
Suba exatamente estes arquivos:

### Novos arquivos
- `frontend/assets/css/mobile-only.css`
- `frontend/includes/mobile-menu.php`
- `frontend/includes/mobile-bottom-nav.php`

### Arquivos alterados
- `frontend/includes/header.php`
- `frontend/paginas/participante/index.php`
- `frontend/paginas/admin/index.php`
- `frontend/paginas/participante/dashboard.php`
- `frontend/paginas/admin/dashboard.php`
- `frontend/paginas/public/index.php`
- `frontend/includes/admin_sidebar.php`

## 2) Ordem recomendada de upload
1. **CSS primeiro**  
   - `frontend/assets/css/mobile-only.css`
2. **Includes globais**  
   - `frontend/includes/header.php`  
   - `frontend/includes/mobile-menu.php`  
   - `frontend/includes/mobile-bottom-nav.php`
3. **Layouts base**  
   - `frontend/paginas/participante/index.php`  
   - `frontend/paginas/admin/index.php`
4. **Páginas específicas**  
   - `frontend/paginas/participante/dashboard.php`  
   - `frontend/paginas/admin/dashboard.php`  
   - `frontend/paginas/public/index.php`
5. **Sidebar admin**  
   - `frontend/includes/admin_sidebar.php`

## 3) Limpeza de cache
Após o upload:
- Limpar cache do navegador (CTRL+F5)
- Se houver CDN, limpar o cache dos assets CSS

## 4) Teste rápido pós-deploy
Testar em 3 resoluções:
1. **Mobile 360px**  
   - Ver hamburger menu
   - Ver bottom navigation
2. **Tablet 768px**  
   - Sidebar desktop oculto
   - Layout em 2 colunas
3. **Desktop 1024px+**  
   - Sidebar desktop visível
   - Layout original preservado

## 5) Checklist funcional
- [ ] Hamburger abre/fecha
- [ ] Overlay fecha o menu
- [ ] Bottom nav aparece no mobile
- [ ] Dashboard participante com grid responsivo
- [ ] Dashboard admin com grid responsivo
- [ ] Página pública com imagens lazy

## 6) Rollback rápido (se necessário)
Em caso de problema, revertendo apenas:
- `frontend/includes/header.php`
- `frontend/assets/css/mobile-only.css`
- `frontend/includes/mobile-menu.php`
- `frontend/includes/mobile-bottom-nav.php`

Isso já restaura a navegação antiga.
