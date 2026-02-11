<?php
$pageTitle = 'Teste Tailwind CSS';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold text-gray-900 mb-8">Teste do Tailwind CSS</h1>
  
  <!-- Teste das cores customizadas -->
  <div class="mb-8">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Cores Customizadas</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-primary-600 text-white p-4 rounded-lg">
        <p class="font-bold">Primary 600</p>
        <p class="text-sm">#2563eb</p>
      </div>
      <div class="bg-success-600 text-white p-4 rounded-lg">
        <p class="font-bold">Success 600</p>
        <p class="text-sm">#16a34a</p>
      </div>
      <div class="bg-warning-600 text-white p-4 rounded-lg">
        <p class="font-bold">Warning 600</p>
        <p class="text-sm">#d97706</p>
      </div>
      <div class="bg-error-600 text-white p-4 rounded-lg">
        <p class="font-bold">Error 600</p>
        <p class="text-sm">#dc2626</p>
      </div>
    </div>
  </div>

  <!-- Teste dos botões customizados -->
  <div class="mb-8">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Botões Customizados</h2>
    <div class="flex flex-wrap gap-4">
      <button class="btn-primary">Botão Primário</button>
      <button class="btn-secondary">Botão Secundário</button>
      <button class="btn-success">Botão Sucesso</button>
      <button class="btn-warning">Botão Aviso</button>
      <button class="btn-error">Botão Erro</button>
    </div>
  </div>

  <!-- Teste dos cards -->
  <div class="mb-8">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Cards</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Card com Título</h3>
        </div>
        <p class="text-gray-600">Este é um exemplo de card usando as classes customizadas.</p>
      </div>
      <div class="card">
        <p class="text-gray-600">Card simples sem header.</p>
      </div>
    </div>
  </div>

  <!-- Teste dos formulários -->
  <div class="mb-8">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Formulários</h2>
    <div class="max-w-md">
      <div class="form-group">
        <label class="form-label">Nome</label>
        <input type="text" class="form-input" placeholder="Digite seu nome">
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-input" placeholder="Digite seu email">
      </div>
      <div class="form-group">
        <label class="form-label">Mensagem</label>
        <textarea class="form-textarea" placeholder="Digite sua mensagem"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Categoria</label>
        <select class="form-select">
          <option>Selecione uma opção</option>
          <option>Opção 1</option>
          <option>Opção 2</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Teste dos alertas -->
  <div class="mb-8">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Alertas</h2>
    <div class="space-y-4">
      <div class="alert alert-success">
        <strong>Sucesso!</strong> Esta é uma mensagem de sucesso.
      </div>
      <div class="alert alert-warning">
        <strong>Aviso!</strong> Esta é uma mensagem de aviso.
      </div>
      <div class="alert alert-error">
        <strong>Erro!</strong> Esta é uma mensagem de erro.
      </div>
      <div class="alert alert-info">
        <strong>Informação!</strong> Esta é uma mensagem informativa.
      </div>
    </div>
  </div>

  <!-- Teste das classes Tailwind padrão -->
  <div class="mb-8">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Classes Tailwind Padrão</h2>
    <div class="bg-blue-500 text-white p-4 rounded-lg">
      <p>Este é um exemplo usando classes Tailwind padrão (bg-blue-500, text-white, etc.)</p>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?> 
