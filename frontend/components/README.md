# Componentes shadcn/ui Adaptados

Componentes baseados em shadcn/ui, adaptados para uso com PHP e Tailwind CSS.

## 📦 Componentes Disponíveis

### Button (`ui/button.php`)
Botões estilizados e acessíveis.

**Uso:**
```php
<?php include '../../components/ui/button.php'; ?>

<?= shadcn_button('Salvar', [
    'variant' => 'primary',
    'type' => 'submit',
    'icon' => 'fas fa-save',
    'onclick' => 'salvar()'
]) ?>
```

**Variantes:** `primary`, `secondary`, `destructive`, `outline`, `ghost`, `link`  
**Tamanhos:** `default`, `sm`, `lg`, `icon`

---

### Dialog (`ui/dialog.php`)
Modais acessíveis e responsivos.

**Uso:**
```php
<?php include '../../components/ui/dialog.php'; ?>

<?= shadcn_dialog('modal-exemplo', 'Título', 'Conteúdo aqui...', 
    shadcn_button('Fechar', ['variant' => 'outline', 'onclick' => 'closeDialog("modal-exemplo")']),
    ['size' => 'lg']
) ?>
```

**Tamanhos:** `sm`, `default`, `lg`, `xl`, `full`

---

### Input (`ui/input.php`)
Campos de entrada estilizados.

**Uso:**
```php
<?php include '../../components/ui/input.php'; ?>

<?= shadcn_input([
    'name' => 'email',
    'label' => 'Email',
    'type' => 'email',
    'placeholder' => 'seu@email.com',
    'required' => true,
    'error' => isset($errors['email']) ? $errors['email'] : ''
]) ?>
```

---

### Badge (`ui/badge.php`)
Etiquetas de status e categorias.

**Uso:**
```php
<?php include '../../components/ui/badge.php'; ?>

<?= shadcn_badge('Ativo', ['variant' => 'success']) ?>
<?= shadcn_badge('Pendente', ['variant' => 'warning']) ?>
<?= shadcn_badge('Cancelado', ['variant' => 'destructive']) ?>
```

**Variantes:** `default`, `secondary`, `success`, `destructive`, `warning`, `outline`, `active`, `inactive`, `pending`

---

## 🚀 Como Usar

1. Inclua o componente no início do arquivo PHP:
```php
<?php include '../../components/ui/button.php'; ?>
```

2. Use a função do componente:
```php
<?= shadcn_button('Texto do Botão', ['variant' => 'primary']) ?>
```

3. Para componentes interativos (Dialog), inclua também o utils.js:
```html
<script src="../../components/lib/utils.js" type="module"></script>
```

---

## 🎨 Personalização

Todos os componentes aceitam a opção `class` para adicionar classes customizadas:

```php
<?= shadcn_button('Customizado', [
    'variant' => 'primary',
    'class' => 'shadow-lg hover:shadow-xl'
]) ?>
```

---

## 📚 Documentação Completa

Veja `docs/GUIA_SHADCN_UI.md` para documentação completa e exemplos avançados.

