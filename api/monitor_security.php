<?php
/**
 * Monitor de Segurança para APIs Públicas
 * Analisa logs e gera relatórios de segurança
 */

class SecurityMonitor {
    private $logFile;
    private $reportFile;
    
    public function __construct($logFile = null, $reportFile = null) {
        $this->logFile = $logFile ?: __DIR__ . '/../logs/security.log';
        $this->reportFile = $reportFile ?: __DIR__ . '/../logs/security_report.json';
    }
    
    /**
     * Analisa logs e gera relatório
     */
    public function generateReport($hours = 24) {
        if (!file_exists($this->logFile)) {
            return ['error' => 'Arquivo de log não encontrado'];
        }
        
        $logs = $this->parseLogs($hours);
        $report = $this->analyzeLogs($logs);
        
        // Salvar relatório
        file_put_contents($this->reportFile, json_encode($report, JSON_PRETTY_PRINT));
        
        return $report;
    }
    
    /**
     * Parse logs do arquivo
     */
    private function parseLogs($hours) {
        $logs = [];
        $cutoffTime = time() - ($hours * 3600);
        
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (preg_match('/\[SECURITY\] (.+)/', $line, $matches)) {
                $logData = json_decode($matches[1], true);
                if ($logData && isset($logData['timestamp'])) {
                    $logTime = strtotime($logData['timestamp']);
                    if ($logTime >= $cutoffTime) {
                        $logs[] = $logData;
                    }
                }
            }
        }
        
        return $logs;
    }
    
    /**
     * Analisa logs e gera estatísticas
     */
    private function analyzeLogs($logs) {
        $report = [
            'period' => 'Últimas 24 horas',
            'total_requests' => count($logs),
            'unique_ips' => count(array_unique(array_column($logs, 'ip'))),
            'events' => [],
            'top_ips' => [],
            'suspicious_activity' => [],
            'rate_limit_violations' => 0,
            'blocked_ips' => []
        ];
        
        // Contar eventos
        $eventCounts = [];
        $ipCounts = [];
        $suspiciousIPs = [];
        
        foreach ($logs as $log) {
            $event = $log['event'] ?? 'unknown';
            $ip = $log['ip'] ?? 'unknown';
            
            // Contar eventos
            $eventCounts[$event] = ($eventCounts[$event] ?? 0) + 1;
            
            // Contar IPs
            $ipCounts[$ip] = ($ipCounts[$ip] ?? 0) + 1;
            
            // Identificar atividade suspeita
            if (in_array($event, ['RATE_LIMIT_EXCEEDED', 'SUSPICIOUS_USER_AGENT', 'INVALID_METHOD'])) {
                $suspiciousIPs[$ip] = ($suspiciousIPs[$ip] ?? 0) + 1;
            }
            
            // Contar violações de rate limit
            if ($event === 'RATE_LIMIT_EXCEEDED') {
                $report['rate_limit_violations']++;
            }
            
            // IPs bloqueados
            if ($event === 'RATE_LIMIT_BLOCKED') {
                $report['blocked_ips'][] = $ip;
            }
        }
        
        // Top eventos
        arsort($eventCounts);
        $report['events'] = array_slice($eventCounts, 0, 10, true);
        
        // Top IPs
        arsort($ipCounts);
        $report['top_ips'] = array_slice($ipCounts, 0, 10, true);
        
        // IPs suspeitos
        arsort($suspiciousIPs);
        $report['suspicious_activity'] = array_slice($suspiciousIPs, 0, 10, true);
        
        // Remover duplicatas de IPs bloqueados
        $report['blocked_ips'] = array_unique($report['blocked_ips']);
        
        return $report;
    }
    
    /**
     * Verifica se IP está bloqueado
     */
    public function isIPBlocked($ip) {
        $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($ip) . '.json';
        
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        $rateData = json_decode(file_get_contents($cacheFile), true);
        if (!$rateData) {
            return false;
        }
        
        return isset($rateData['blocked_until']) && time() < $rateData['blocked_until'];
    }
    
    /**
     * Desbloqueia IP manualmente
     */
    public function unblockIP($ip) {
        $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($ip) . '.json';
        
        if (file_exists($cacheFile)) {
            $rateData = json_decode(file_get_contents($cacheFile), true);
            if ($rateData) {
                unset($rateData['blocked_until']);
                file_put_contents($cacheFile, json_encode($rateData));
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Obtém estatísticas em tempo real
     */
    public function getRealTimeStats() {
        $report = $this->generateReport(1); // Última hora
        
        return [
            'requests_last_hour' => $report['total_requests'],
            'unique_ips_last_hour' => $report['unique_ips'],
            'rate_limit_violations' => $report['rate_limit_violations'],
            'blocked_ips_count' => count($report['blocked_ips']),
            'timestamp' => time()
        ];
    }
    
    /**
     * Envia alerta por email (se configurado)
     */
    public function sendAlert($subject, $message) {
        // Implementar envio de email se necessário
        error_log('[SECURITY_ALERT] ' . $subject . ': ' . $message);
    }
}

// Script de linha de comando
if (php_sapi_name() === 'cli') {
    $monitor = new SecurityMonitor();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'report':
                $report = $monitor->generateReport();
                echo json_encode($report, JSON_PRETTY_PRINT);
                break;
                
            case 'stats':
                $stats = $monitor->getRealTimeStats();
                echo json_encode($stats, JSON_PRETTY_PRINT);
                break;
                
            case 'unblock':
                if (isset($argv[2])) {
                    $ip = $argv[2];
                    if ($monitor->unblockIP($ip)) {
                        echo "IP $ip desbloqueado com sucesso\n";
                    } else {
                        echo "IP $ip não estava bloqueado\n";
                    }
                } else {
                    echo "Uso: php monitor_security.php unblock <IP>\n";
                }
                break;
                
            default:
                echo "Uso:\n";
                echo "  php monitor_security.php report  - Gera relatório completo\n";
                echo "  php monitor_security.php stats   - Estatísticas em tempo real\n";
                echo "  php monitor_security.php unblock <IP> - Desbloqueia IP\n";
        }
    } else {
        echo "Monitor de Segurança - MovAmazon\n";
        echo "Use --help para ver opções\n";
    }
}
?> 
