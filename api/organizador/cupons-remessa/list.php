<?php
error_log('🔍 [Cupons] API list.php - Iniciando requisição');
require_once '../../auth/auth.php';
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';
header('Content-Type: application/json');

if (!isOrganizador()) {
    error_log('❌ [Cupons] API list.php - Acesso negado - não é organizador');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

error_log('✅ [Cupons] API list.php - Autenticação válida: user_id=' . ($_SESSION['user_id'] ?? 'N/A'));

$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];

error_log('🔧 [Cupons] API list.php - Contexto: usuario_id=' . $usuario_id . ', organizador_id=' . $organizador_id);

$evento_id = $_GET['evento_id'] ?? null;
$status = $_GET['status'] ?? null;
$data_inicio = $_GET['data_inicio'] ?? null;
$data_fim = $_GET['data_fim'] ?? null;

error_log('📋 [Cupons] API list.php - Parâmetros recebidos: evento_id=' . ($evento_id ?? 'null') . ', status=' . ($status ?? 'null') . ', data_inicio=' . ($data_inicio ?? 'null') . ', data_fim=' . ($data_fim ?? 'null'));

$where = ['1=1'];
$params = [];

$where[] = '(
    evento_id IS NULL OR evento_id IN (SELECT id FROM eventos WHERE (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL)
)';
$params[] = $organizador_id;
$params[] = $usuario_id;

if ($evento_id) {
    $where[] = 'evento_id = ?';
    $params[] = $evento_id;
}
if ($status) {
    $where[] = 'status = ?';
    $params[] = $status;
}
if ($data_inicio) {
    $where[] = 'data_inicio >= ?';
    $params[] = $data_inicio;
}
if ($data_fim) {
    $where[] = 'data_validade <= ?';
    $params[] = $data_fim;
}

$sql = 'SELECT * FROM cupons_remessa WHERE ' . implode(' AND ', $where) . ' ORDER BY data_criacao DESC';
error_log('📝 [Cupons] API list.php - SQL: ' . $sql);
error_log('📝 [Cupons] API list.php - Parâmetros SQL: ' . json_encode($params));

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $remessas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log('✅ [Cupons] API list.php - Query executada com sucesso. Total de cupons encontrados: ' . count($remessas));
    
    if (count($remessas) > 0) {
        error_log('📦 [Cupons] API list.php - Primeiro cupom: ' . json_encode($remessas[0]));
    } else {
        error_log('⚠️ [Cupons] API list.php - Nenhum cupom encontrado com os filtros aplicados');
        
        // Debug: verificar se existem cupons na tabela
        $stmtDebug = $pdo->prepare('SELECT COUNT(*) as total FROM cupons_remessa');
        $stmtDebug->execute();
        $totalGeral = $stmtDebug->fetch(PDO::FETCH_ASSOC);
        error_log('🔍 [Cupons] API list.php - Total de cupons na tabela (sem filtros): ' . $totalGeral['total']);
        
        // Debug: verificar eventos do organizador
        $stmtEventos = $pdo->prepare('SELECT id, nome FROM eventos WHERE (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL');
        $stmtEventos->execute([$organizador_id, $usuario_id]);
        $eventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);
        error_log('🔍 [Cupons] API list.php - Eventos do organizador: ' . json_encode($eventos));
        
        // Debug: verificar cupons sem evento_id
        $stmtCuponsNull = $pdo->prepare('SELECT COUNT(*) as total FROM cupons_remessa WHERE evento_id IS NULL');
        $stmtCuponsNull->execute();
        $totalNull = $stmtCuponsNull->fetch(PDO::FETCH_ASSOC);
        error_log('🔍 [Cupons] API list.php - Cupons com evento_id NULL: ' . $totalNull['total']);
    }
    
    echo json_encode(['success' => true, 'remessas' => $remessas]);
} catch (Exception $e) {
    error_log('❌ [Cupons] API list.php - Erro ao executar query: ' . $e->getMessage());
    error_log('❌ [Cupons] API list.php - Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar cupons: ' . $e->getMessage()]);
} 
