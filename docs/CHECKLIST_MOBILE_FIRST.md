# Checklist de Implementação Mobile-First

## 1. Menu Mobile
- [ ] Hamburger visível em <1024px
- [ ] Menu abre e fecha por toque
- [ ] Overlay escurecido aparece e fecha menu ao tocar
- [ ] Swipe abre/fecha (opcional)
- [ ] ESC fecha menu
- [ ] Menu não bloqueia desktop

## 2. Bottom Navigation
- [ ] Aparece apenas em <768px
- [ ] Máximo de 5 itens
- [ ] Itens adequados por perfil (participante/admin)
- [ ] Item ativo com destaque
- [ ] Espaço no body para não cobrir conteúdo

## 3. Breakpoints Mobile-First
- [ ] Cards em grid 1 coluna no mobile
- [ ] 2 colunas em tablet (sm)
- [ ] 3+ colunas no desktop (lg)
- [ ] Tabelas com scroll horizontal em mobile

## 4. Touch-Friendly
- [ ] Botões 48x48px mínimo
- [ ] Inputs com altura mínima de 44px
- [ ] Ícones clicáveis com área expandida
- [ ] Espaçamento entre itens >= 8px

## 5. Performance Mobile
- [ ] Lazy loading em imagens de eventos
- [ ] Sombras reduzidas em mobile
- [ ] Carrosséis não travam em devices básicos
- [ ] Tempo de primeira pintura aceitável

## 6. Validação de Fluxo
- [ ] Fluxo de inscrição intacto
- [ ] Fluxo de pagamento intacto
- [ ] Dashboard funcional em mobile e desktop
- [ ] Admin funcional em mobile e desktop
- [ ] Sem regressão visual no desktop

## 7. Testes Mínimos
- [ ] 320px (iPhone SE)
- [ ] 360px (Android comum)
- [ ] 768px (tablet)
- [ ] 1024px (desktop)

## 8. Pós-Deploy
- [ ] Verificar console do navegador
- [ ] Validar performance em 3G/4G
- [ ] Revisar logs se houver erro JS
