<?php
// Verificar se usuário já está logado
$usuario_logado = null;
if (isset($_SESSION['usuario_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario_logado = $stmt->fetch();
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="inscricao-header mb-4">
                <h2 class="text-center mb-3">Identificação</h2>
                <p class="text-center text-muted">Faça login ou crie sua conta para continuar</p>
            </div>

            <?php if ($usuario_logado): ?>
                <!-- Usuário já logado -->
                <div class="usuario-logado-container">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Usuário identificado:</strong> <?php echo htmlspecialchars($usuario_logado['nome']); ?>
                    </div>
                    
                    <div class="usuario-card">
                        <div class="usuario-info">
                            <div class="avatar-container">
                                <i class="fas fa-user-circle fa-3x text-primary"></i>
                            </div>
                            <div class="dados-usuario">
                                <h4><?php echo htmlspecialchars($usuario_logado['nome']); ?></h4>
                                <p><strong>E-mail:</strong> <?php echo htmlspecialchars($usuario_logado['email']); ?></p>
                                <p><strong>CPF:</strong> <?php echo $this->mascararCPF($usuario_logado['cpf']); ?></p>
                                <p><strong>Data Nasc:</strong> <?php echo date('d/m/Y', strtotime($usuario_logado['data_nascimento'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="acoes-usuario">
                            <button class="btn btn-outline-secondary" onclick="alterarUsuario()">
                                <i class="fas fa-edit"></i> Alterar Usuário
                            </button>
                            <button class="btn btn-primary" onclick="confirmarUsuario()">
                                <i class="fas fa-check"></i> Confirmar e Continuar
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Sistema de Login/Registro -->
                <div class="identificacao-container">
                    <div class="tabs-container">
                        <div class="tabs">
                            <button class="tab-btn active" data-tab="login">
                                <i class="fas fa-sign-in-alt"></i> Já tenho cadastro
                            </button>
                            <button class="tab-btn" data-tab="registro">
                                <i class="fas fa-user-plus"></i> Novo cadastro
                            </button>
                        </div>
                        
                        <!-- Tab Login -->
                        <div class="tab-content active" id="login">
                            <form class="login-form" id="loginForm">
                                <div class="form-group">
                                    <label for="loginIdentificacao">
                                        <i class="fas fa-envelope"></i> E-mail ou CPF
                                    </label>
                                    <input type="text" 
                                           id="loginIdentificacao" 
                                           name="identificacao" 
                                           class="form-control" 
                                           required 
                                           placeholder="Digite seu e-mail ou CPF">
                                </div>
                                
                                <div class="form-group">
                                    <label for="loginSenha">
                                        <i class="fas fa-lock"></i> Senha
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               id="loginSenha" 
                                               name="senha" 
                                               class="form-control" 
                                               required 
                                               placeholder="Digite sua senha">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="loginSenha">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <a href="#" class="forgot-password" onclick="recuperarSenha()">
                                        <i class="fas fa-question-circle"></i> Esqueceu sua senha?
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-sign-in-alt"></i> Entrar
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Tab Registro -->
                        <div class="tab-content" id="registro">
                            <form class="registro-form" id="registroForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="registroNome">
                                                <i class="fas fa-user"></i> Nome Completo *
                                            </label>
                                            <input type="text" 
                                                   id="registroNome" 
                                                   name="nome" 
                                                   class="form-control" 
                                                   required 
                                                   placeholder="Digite seu nome completo">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="registroCPF">
                                                <i class="fas fa-id-card"></i> CPF *
                                            </label>
                                            <input type="text" 
                                                   id="registroCPF" 
                                                   name="cpf" 
                                                   class="form-control" 
                                                   required 
                                                   placeholder="000.000.000-00"
                                                   maxlength="14">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="registroEmail">
                                                <i class="fas fa-envelope"></i> E-mail *
                                            </label>
                                            <input type="email" 
                                                   id="registroEmail" 
                                                   name="email" 
                                                   class="form-control" 
                                                   required 
                                                   placeholder="seu@email.com">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="registroDataNasc">
                                                <i class="fas fa-calendar"></i> Data de Nascimento *
                                            </label>
                                            <input type="date" 
                                                   id="registroDataNasc" 
                                                   name="data_nascimento" 
                                                   class="form-control" 
                                                   required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="registroTelefone">
                                                <i class="fas fa-phone"></i> Telefone
                                            </label>
                                            <input type="tel" 
                                                   id="registroTelefone" 
                                                   name="telefone" 
                                                   class="form-control" 
                                                   placeholder="(00) 00000-0000">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="registroSexo">
                                                <i class="fas fa-venus-mars"></i> Sexo
                                            </label>
                                            <select id="registroSexo" name="sexo" class="form-control">
                                                <option value="">Selecione</option>
                                                <option value="M">Masculino</option>
                                                <option value="F">Feminino</option>
                                                <option value="O">Outro</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="registroSenha">
                                                <i class="fas fa-lock"></i> Senha *
                                            </label>
                                            <div class="input-group">
                                                <input type="password" 
                                                       id="registroSenha" 
                                                       name="senha" 
                                                       class="form-control" 
                                                       required 
                                                       placeholder="Mínimo 6 caracteres"
                                                       minlength="6">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="registroSenha">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="registroConfirmarSenha">
                                                <i class="fas fa-lock"></i> Confirmar Senha *
                                            </label>
                                            <div class="input-group">
                                                <input type="password" 
                                                       id="registroConfirmarSenha" 
                                                       name="confirmar_senha" 
                                                       class="form-control" 
                                                       required 
                                                       placeholder="Confirme sua senha"
                                                       minlength="6">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="registroConfirmarSenha">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-user-plus"></i> Criar Conta
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="navegacao-etapas mt-4">
                <div class="d-flex justify-content-between">
                    <button class="btn btn-secondary" onclick="voltarEtapa()">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button class="btn btn-primary" onclick="prosseguirEtapa()" id="btn-prosseguir" disabled>
                        Próximo <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.inscricao-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
}

.usuario-logado-container {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.usuario-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
}

.usuario-info {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.avatar-container {
    flex-shrink: 0;
}

.dados-usuario h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.dados-usuario p {
    margin: 5px 0;
    color: #666;
}

.acoes-usuario {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
}

.identificacao-container {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.tabs-container {
    max-width: 600px;
    margin: 0 auto;
}

.tabs {
    display: flex;
    margin-bottom: 30px;
    border-bottom: 2px solid #e9ecef;
}

.tab-btn {
    background: none;
    border: none;
    padding: 15px 25px;
    font-size: 16px;
    font-weight: 500;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
}

.tab-btn:hover {
    color: #007bff;
}

.tab-btn.active {
    color: #007bff;
    border-bottom-color: #007bff;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group label i {
    margin-right: 8px;
    color: #007bff;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.input-group {
    position: relative;
}

.toggle-password {
    border-left: none;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.form-actions {
    margin-top: 30px;
}

.forgot-password {
    display: block;
    text-align: center;
    margin-bottom: 20px;
    color: #007bff;
    text-decoration: none;
}

.forgot-password:hover {
    text-decoration: underline;
}

.btn {
    padding: 12px 25px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background: #0056b3;
    border-color: #0056b3;
    transform: translateY(-2px);
}

.btn-success {
    background: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background: #1e7e34;
    border-color: #1e7e34;
    transform: translateY(-2px);
}

.navegacao-etapas {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Responsividade */
@media (max-width: 768px) {
    .usuario-info {
        flex-direction: column;
        text-align: center;
    }
    
    .acoes-usuario {
        flex-direction: column;
    }
    
    .tabs {
        flex-direction: column;
    }
    
    .tab-btn {
        text-align: center;
        border-bottom: none;
        border-right: 3px solid transparent;
    }
    
    .tab-btn.active {
        border-right-color: #007bff;
        border-bottom-color: transparent;
    }
}
</style>

<?php
// Função para mascarar CPF
function mascararCPF($cpf) {
    if (strlen($cpf) === 11) {
        return substr($cpf, 0, 3) . '.***.***-' . substr($cpf, -2);
    }
    return $cpf;
}
?>
