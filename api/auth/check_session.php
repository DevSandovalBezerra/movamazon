<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db.php';

//error_log('=== INÍCIO API CHECK SESSION ===');
//error_log('Timestamp: ' . date('Y-m-d H:i:s'));

try {
    session_start();
    //error_log('Sessão iniciada - ID: ' . session_id());
    //error_log('Dados da sessão: ' . json_encode($_SESSION));
    
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        //error_log('User ID encontrado na sessão: ' . $_SESSION['user_id']);
        // Buscar dados atualizados do usuário
        //error_log('Buscando dados do usuário no banco...');
        $sql = "
            SELECT 
                id,
                nome_completo,
                email,
                data_nascimento,
                documento,
                telefone,
                celular,
                status,
                papel
            FROM usuarios 
            WHERE id = ? AND status = 'ativo'
            LIMIT 1
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            //error_log('Usuário encontrado: ' . $usuario['nome_completo'] . ' (' . $usuario['email'] . ')');
            $response = [
                'success' => true,
                'logged_in' => true,
                'user' => $usuario
            ];
            //error_log('=== FIM API CHECK SESSION - LOGADO ===');
            //error_log('Resposta: ' . json_encode($response));
            echo json_encode($response);
        } else {
            //error_log('Usuário não encontrado ou inativo no banco, destruindo sessão...');
            // Usuário não encontrado ou inativo
            session_destroy();
            $response = [
                'success' => true,
                'logged_in' => false,
                'message' => 'Usuário não encontrado ou inativo'
            ];
            //error_log('=== FIM API CHECK SESSION - USUÁRIO INATIVO ===');
            //error_log('Resposta: ' . json_encode($response));
            echo json_encode($response);
        }
    } else {
        //error_log('Nenhum user_id encontrado na sessão');
        $response = [
            'success' => true,
            'logged_in' => false,
            'message' => 'Usuário não logado'
        ];
        //error_log('=== FIM API CHECK SESSION - NÃO LOGADO ===');
        //error_log('Resposta: ' . json_encode($response));
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    error_log('ERRO na verificação de sessão: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    http_response_code(500);
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    
    error_log('=== FIM API CHECK SESSION - ERRO ===');
    error_log('Resposta: ' . json_encode($response));
    echo json_encode($response);
}
?>
