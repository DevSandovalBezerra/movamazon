<?php
/**
 * Middleware de autenticação administrativa
 * 
 * Sistema de autenticação usando a tabela `usuario_admin` do banco de dados.
 * Mantém fallback para .env durante período de migração.
 * 
 * @version 2.0 - Migrado para banco de dados
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

function verificarAdmin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['papel'])) {
        return false;
    }
    
    // Verificar se é admin pela sessão
    if ($_SESSION['papel'] === 'admin') {
        // Verificar se o admin ainda existe e está ativo no banco
        global $pdo;
        try {
            $stmt = $pdo->prepare('SELECT id, email, nome_completo, status FROM usuario_admin WHERE id = ? AND status = "ativo"');
            $stmt->execute([$_SESSION['user_id']]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && $admin['email'] === $_SESSION['user_email']) {
                // Atualizar último acesso
                $update = $pdo->prepare('UPDATE usuario_admin SET ultimo_acesso = NOW() WHERE id = ?');
                $update->execute([$_SESSION['user_id']]);
                return true;
            }
        } catch (Exception $e) {
            error_log('[ADMIN_AUTH] Erro ao verificar admin no banco: ' . $e->getMessage());
        }
        
        // Se não encontrou no banco, limpar sessão
        session_destroy();
        return false;
    }
    
    return false;
}

function requererAdmin($redirecionar = true) {
    if (!verificarAdmin()) {
        if ($redirecionar) {
            header('Location: /panel/acesso');
            exit();
        }
        return false;
    }
    return true;
}

function autenticarAdmin($email, $password) {
    global $pdo;
    
    try {
        // Buscar admin no banco de dados
        $stmt = $pdo->prepare('SELECT id, nome_completo, email, senha, status FROM usuario_admin WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Se não encontrou no banco, tentar fallback para .env (migração)
        if (!$admin) {
            $admin_email = envValue('ADMIN_EMAIL');
            $admin_password = envValue('ADMIN_PASSWORD');
            $admin_password_hash = envValue('ADMIN_PASSWORD_HASH');
            
            // Fallback: autenticação via .env (apenas durante migração)
            if (!empty($admin_email) && $email === $admin_email) {
                $senha_valida = false;
                
                if (!empty($admin_password_hash)) {
                    $senha_valida = password_verify($password, $admin_password_hash);
                } elseif (!empty($admin_password)) {
                    $senha_valida = ($password === $admin_password);
                }
                
                if ($senha_valida) {
                    error_log('[ADMIN_AUTH] Login via .env (fallback) - Considere migrar para banco de dados');
                    
                    $_SESSION['user_id'] = 0;
                    $_SESSION['user_email'] = $admin_email;
                    $_SESSION['user_name'] = 'Administrador';
                    $_SESSION['papel'] = 'admin';
                    $_SESSION['login_time'] = time();
                    
                    return [
                        'success' => true,
                        'message' => 'Login realizado com sucesso',
                        'user' => [
                            'email' => $admin_email,
                            'name' => 'Administrador',
                            'role' => 'admin'
                        ]
                    ];
                }
            }
            
            return ['success' => false, 'message' => 'Credenciais inválidas'];
        }
        
        // Verificar se está ativo
        if ($admin['status'] !== 'ativo') {
            return ['success' => false, 'message' => 'Conta de administrador inativa'];
        }
        
        // Verificar senha
        if (!password_verify($password, $admin['senha'])) {
            error_log('[ADMIN_AUTH] Tentativa de login com senha incorreta: ' . $email);
            return ['success' => false, 'message' => 'Credenciais inválidas'];
        }
        
        // Login bem-sucedido - atualizar último acesso
        $update = $pdo->prepare('UPDATE usuario_admin SET ultimo_acesso = NOW() WHERE id = ?');
        $update->execute([$admin['id']]);
        
        // Criar sessão
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_email'] = $admin['email'];
        $_SESSION['user_name'] = $admin['nome_completo'];
        $_SESSION['papel'] = 'admin';
        $_SESSION['login_time'] = time();
        
        error_log('[ADMIN_AUTH] Login admin bem-sucedido: ' . $admin['email'] . ' (ID: ' . $admin['id'] . ')');
        
        return [
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'user' => [
                'id' => $admin['id'],
                'email' => $admin['email'],
                'name' => $admin['nome_completo'],
                'role' => 'admin'
            ]
        ];
        
    } catch (Exception $e) {
        error_log('[ADMIN_AUTH] Erro ao autenticar admin: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Erro ao processar login. Tente novamente.'];
    }
}

