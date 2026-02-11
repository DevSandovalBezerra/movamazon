<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste CSS Completo - MovAmazon</title>
    <link href="assets/css/tailwind.min.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
    <link href="assets/css/inscricao.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="bg-gray-50">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-brand-green mb-4">Teste CSS Completo MovAmazon</h1>

        <!-- Teste Tailwind + Custom CSS -->
        <div class="card mb-4">
            <h2 class="card-title">Card de Teste (Tailwind + Custom)</h2>
            <p class="text-gray-600">Este é um teste para verificar se o CSS está funcionando corretamente.</p>
            <button class="btn-primary mt-4">Botão Custom CSS</button>
        </div>

        <!-- Teste Inscrição CSS -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-user"></i> Teste Inscrição CSS</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" class="form-control" placeholder="teste@exemplo.com">
                </div>
                <button class="btn btn-primary">Botão Inscrição</button>
                <button class="btn btn-success">Sucesso</button>
                <button class="btn btn-danger">Erro</button>
            </div>
        </div>

        <!-- Teste Main CSS -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="text-center">
                <h3>Main CSS Utilitários</h3>
                <p class="text-primary">Texto primário</p>
                <p class="text-success">Texto sucesso</p>
            </div>
            <div class="w-100 text-right">
                <span class="badge badge-primary">Badge</span>
                <span class="badge badge-success">Sucesso</span>
            </div>
        </div>

        <!-- Teste Responsividade -->
        <div class="alert alert-info">
            <strong>Info:</strong> Todos os CSS estão carregados e funcionando!
        </div>
    </div>
</body>

</html>
