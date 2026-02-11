<?php
/**
 * Script de migração WEB: Popular tabela kit_modalidade_evento
 * com os dados existentes do campo modalidade_evento_id da tabela kits_eventos
 * 
 * Acesse via navegador: http://localhost/movamazon/scripts/migrar_modalidades_kits_web.php
 * 
 * Este script deve ser executado apenas uma vez para migrar dados legados
 */

session_start();

// Verificar se está logado como organizador ou admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['papel'], ['organizador', 'admin'])) {
    http_response_code(403);
    die('Acesso negado. Você precisa estar logado como organizador ou admin.');
}

require_once __DIR__ . '/../api/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migração de Modalidades dos Kits</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #10B981;
            padding-bottom: 10px;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #2196F3;
        }
        .success {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #4CAF50;
            color: #2e7d32;
        }
        .warning {
            background: #fff3e0;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #FF9800;
            color: #e65100;
        }
        .error {
            background: #ffebee;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #f44336;
            color: #c62828;
        }
        .log {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        button {
            background: #10B981;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        button:hover {
            background: #0b4340;
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Migração de Modalidades dos Kits</h1>
        
        <div class="info">
            <strong>O que este script faz:</strong><br>
            Migra os dados existentes do campo <code>modalidade_evento_id</code> da tabela <code>kits_eventos</code> 
            para a tabela de relacionamento N:N <code>kit_modalidade_evento</code>.
        </div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['executar'])) {
    echo '<div class="log">';
    echo "Iniciando migração de modalidades dos kits...\n";
    echo str_repeat('=', 60) . "\n\n";
    
    try {
        // Buscar todos os kits que têm modalidade_evento_id mas não têm registro na tabela N:N
        $sql = "
            SELECT k.id, k.modalidade_evento_id, k.evento_id, k.nome as kit_nome
            FROM kits_eventos k
            WHERE k.modalidade_evento_id IS NOT NULL 
            AND k.modalidade_evento_id > 0
            AND NOT EXISTS (
                SELECT 1 
                FROM kit_modalidade_evento kme 
                WHERE kme.kit_id = k.id
            )
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $kits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Encontrados " . count($kits) . " kits para migrar.\n\n";
        
        if (empty($kits)) {
            echo '<div class="success">✅ Nenhum kit precisa ser migrado. Todos os kits já estão sincronizados!</div>';
        } else {
            $pdo->beginTransaction();
            
            $stmt_insert = $pdo->prepare("
                INSERT INTO kit_modalidade_evento (kit_id, modalidade_evento_id) 
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE kit_id = kit_id
            ");
            
            $migrados = 0;
            $erros = 0;
            
            foreach ($kits as $kit) {
                // Verificar se a modalidade existe
                $stmt_check = $pdo->prepare("SELECT id, nome FROM modalidades WHERE id = ? AND evento_id = ?");
                $stmt_check->execute([$kit['modalidade_evento_id'], $kit['evento_id']]);
                $modalidade = $stmt_check->fetch(PDO::FETCH_ASSOC);
                
                if ($modalidade) {
                    try {
                        $stmt_insert->execute([$kit['id'], $kit['modalidade_evento_id']]);
                        $migrados++;
                        echo "✅ Kit ID {$kit['id']} ({$kit['kit_nome']}) migrado com modalidade {$kit['modalidade_evento_id']} ({$modalidade['nome']})\n";
                    } catch (PDOException $e) {
                        $erros++;
                        echo "❌ ERRO ao migrar Kit ID {$kit['id']}: " . $e->getMessage() . "\n";
                    }
                } else {
                    $erros++;
                    echo "⚠️  AVISO: Modalidade {$kit['modalidade_evento_id']} não encontrada para kit {$kit['id']} ({$kit['kit_nome']})\n";
                }
            }
            
            if ($erros === 0) {
                $pdo->commit();
                echo "\n" . str_repeat('=', 60) . "\n";
                echo '<div class="success">';
                echo "✅ Migração concluída com sucesso!\n";
                echo "📊 {$migrados} kits migrados.\n";
                echo '</div>';
            } else {
                $pdo->rollBack();
                echo "\n" . str_repeat('=', 60) . "\n";
                echo '<div class="error">';
                echo "❌ Migração interrompida devido a erros.\n";
                echo "📊 {$migrados} kits migrados, {$erros} erros encontrados.\n";
                echo "🔄 Rollback executado. Nenhuma alteração foi salva.\n";
                echo '</div>';
            }
        }
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "\n" . str_repeat('=', 60) . "\n";
        echo '<div class="error">';
        echo "❌ ERRO: " . $e->getMessage() . "\n";
        echo '</div>';
    }
    
    echo '</div>';
    echo '<br><a href="' . $_SERVER['PHP_SELF'] . '"><button>Voltar</button></a>';
} else {
    // Mostrar estatísticas antes de executar
    try {
        $sql_total = "SELECT COUNT(*) as total FROM kits_eventos WHERE modalidade_evento_id IS NOT NULL AND modalidade_evento_id > 0";
        $stmt_total = $pdo->prepare($sql_total);
        $stmt_total->execute();
        $total_kits = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
        
        $sql_ja_migrados = "
            SELECT COUNT(DISTINCT k.id) as total 
            FROM kits_eventos k
            INNER JOIN kit_modalidade_evento kme ON k.id = kme.kit_id
            WHERE k.modalidade_evento_id IS NOT NULL AND k.modalidade_evento_id > 0
        ";
        $stmt_migrados = $pdo->prepare($sql_ja_migrados);
        $stmt_migrados->execute();
        $ja_migrados = $stmt_migrados->fetch(PDO::FETCH_ASSOC)['total'];
        
        $pendentes = $total_kits - $ja_migrados;
        
        echo '<div class="info">';
        echo "<strong>Estatísticas:</strong><br>";
        echo "📦 Total de kits com modalidade: <strong>{$total_kits}</strong><br>";
        echo "✅ Já migrados: <strong>{$ja_migrados}</strong><br>";
        echo "⏳ Pendentes: <strong>{$pendentes}</strong><br>";
        echo '</div>';
        
        if ($pendentes > 0) {
            echo '<form method="POST">';
            echo '<button type="submit" name="executar" value="1">🚀 Executar Migração</button>';
            echo '</form>';
        } else {
            echo '<div class="success">✅ Todos os kits já foram migrados! Não há nada a fazer.</div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="error">❌ Erro ao verificar estatísticas: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

    </div>
</body>
</html>

