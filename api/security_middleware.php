<?php
/**
 * Middleware de Segurança para APIs Públicas
 * Protege contra ataques comuns e abusos
 */

class SecurityMiddleware {
    private $config;
    private $logFile;
    
    public function __construct($config = []) {
        $this->config = array_merge([
            'max_requests_per_minute' => 60,
            'max_requests_per_hour' => 1000,
            'block_duration' => 3600,
            'log_file' => __DIR__ . '/../logs/security.log',
            'cache_dir' => sys_get_temp_dir()
        ], $config);
        
        $this->logFile = $this->config['log_file'];
        $this->ensureLogDirectory();
    }
    
    /**
     * Executa todas as verificações de segurança
     */
    public function validate() {
        $clientIP = $this->getClientIP();
        
        // Log de acesso
        $this->logSecurityEvent($clientIP, 'API_ACCESS', 'Tentativa de acesso');
        
        // Verificações de segurança
        if (!$this->checkRateLimit($clientIP)) {
            $this->denyAccess(429, 'Muitas requisições. Tente novamente em alguns minutos.');
        }
        
        if (!$this->validateUserAgent()) {
            $this->denyAccess(403, 'User-Agent inválido');
        }
        
        if (!$this->validateMethod()) {
            $this->denyAccess(405, 'Método HTTP não permitido');
        }
        
        if (!$this->validateOrigin()) {
            $this->denyAccess(403, 'Origem não permitida');
        }
        
        // Adicionar headers de segurança
        $this->addSecurityHeaders();
        
        return true;
    }
    
    /**
     * Obtém IP real do cliente
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Verifica rate limiting
     */
    private function checkRateLimit($ip) {
        $cacheFile = $this->config['cache_dir'] . '/rate_limit_' . md5($ip) . '.json';
        $now = time();
        
        // Carregar dados
        $rateData = [];
        if (file_exists($cacheFile)) {
            $rateData = json_decode(file_get_contents($cacheFile), true) ?: [];
        }
        
        // Limpar dados antigos
        $rateData['requests'] = array_filter($rateData['requests'] ?? [], function($timestamp) use ($now) {
            return ($now - $timestamp) < 60;
        });
        
        $rateData['hourly'] = array_filter($rateData['hourly'] ?? [], function($timestamp) use ($now) {
            return ($now - $timestamp) < 3600;
        });
        
        // Verificar bloqueio
        if (isset($rateData['blocked_until']) && $now < $rateData['blocked_until']) {
            $this->logSecurityEvent($ip, 'RATE_LIMIT_BLOCKED', 'IP ainda bloqueado');
            return false;
        }
        
        // Adicionar requisição
        $rateData['requests'][] = $now;
        $rateData['hourly'][] = $now;
        
        // Verificar limites
        $requestsPerMinute = count($rateData['requests']);
        $requestsPerHour = count($rateData['hourly']);
        
        if ($requestsPerMinute > $this->config['max_requests_per_minute'] || 
            $requestsPerHour > $this->config['max_requests_per_hour']) {
            $rateData['blocked_until'] = $now + $this->config['block_duration'];
            $this->logSecurityEvent($ip, 'RATE_LIMIT_EXCEEDED', "Min: $requestsPerMinute, Hora: $requestsPerHour");
        }
        
        // Salvar dados
        file_put_contents($cacheFile, json_encode($rateData));
        
        return !isset($rateData['blocked_until']);
    }
    
    /**
     * Valida User-Agent
     */
    private function validateUserAgent() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Verificar se está vazio ou muito curto
        if (empty($userAgent) || strlen($userAgent) < 10) {
            return false;
        }
        
        // Verificar padrões suspeitos
        $suspiciousPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget',
            'python', 'java', 'perl', 'ruby', 'php'
        ];
        
        $userAgentLower = strtolower($userAgent);
        foreach ($suspiciousPatterns as $pattern) {
            if (strpos($userAgentLower, $pattern) !== false) {
                $this->logSecurityEvent($this->getClientIP(), 'SUSPICIOUS_USER_AGENT', "Padrão suspeito: $pattern");
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Valida método HTTP
     */
    private function validateMethod() {
        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        return in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], true);
    }
    
    /**
     * Valida origem da requisição (CORS básico)
     */
    private function validateOrigin() {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
        // Permitir requisições sem origem (navegadores antigos)
        if (empty($origin) && empty($referer)) {
            return true;
        }
        
        // Lista de domínios permitidos
        $allowedEnv = getenv('CORS_ALLOWED_DOMAINS');
        if ($allowedEnv) {
            $allowedDomains = array_map('trim', explode(',', $allowedEnv));
        } else {
            $allowedDomains = [
                'localhost',
                '127.0.0.1',
                'movamazon.com',
                'www.movamazon.com',
                'movamazon.com.br',
                'www.movamazon.com.br'
            ];
        }
        
        foreach ($allowedDomains as $domain) {
            if (strpos($origin, $domain) !== false || strpos($referer, $domain) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Adiciona headers de segurança
     */
    private function addSecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Content-Security-Policy: default-src \'self\'');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    /**
     * Registra evento de segurança
     */
    private function logSecurityEvent($ip, $event, $details = '') {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'event' => $event,
            'details' => $details,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            'origin' => $_SERVER['HTTP_ORIGIN'] ?? ''
        ];
        
        $logMessage = '[SECURITY] ' . json_encode($logEntry) . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Garante que o diretório de logs existe
     */
    private function ensureLogDirectory() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Negar acesso com código de status específico
     */
    private function denyAccess($statusCode, $message) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'timestamp' => time()
        ]);
        exit;
    }
}
