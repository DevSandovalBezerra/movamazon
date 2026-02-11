<?php

class RateLimiter {
    private const MAX_ATTEMPTS = 5;
    private const BLOCK_DURATION = 900;
    private const LOG_FILE = __DIR__ . '/../../logs/admin_login_attempts.log';
    
    public static function checkRateLimit($identifier = null) {
        if ($identifier === null) {
            $identifier = self::getClientIdentifier();
        }
        
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }
        
        self::cleanOldAttempts();
        
        $attempts = $_SESSION['login_attempts'][$identifier] ?? [];
        $recentAttempts = count($attempts);
        
        if ($recentAttempts >= self::MAX_ATTEMPTS) {
            $firstAttemptTime = min($attempts);
            $timeSinceFirst = time() - $firstAttemptTime;
            
            if ($timeSinceFirst < self::BLOCK_DURATION) {
                $remainingTime = self::BLOCK_DURATION - $timeSinceFirst;
                return [
                    'allowed' => false,
                    'remaining_time' => $remainingTime,
                    'attempts' => $recentAttempts
                ];
            } else {
                unset($_SESSION['login_attempts'][$identifier]);
            }
        }
        
        return [
            'allowed' => true,
            'attempts' => $recentAttempts
        ];
    }
    
    public static function recordAttempt($email, $success = false, $identifier = null) {
        if ($identifier === null) {
            $identifier = self::getClientIdentifier();
        }
        
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }
        
        if (!isset($_SESSION['login_attempts'][$identifier])) {
            $_SESSION['login_attempts'][$identifier] = [];
        }
        
        $_SESSION['login_attempts'][$identifier][] = time();
        
        self::logAttempt($email, $success, $identifier);
        
        if ($success) {
            unset($_SESSION['login_attempts'][$identifier]);
        }
    }
    
    private static function cleanOldAttempts() {
        if (!isset($_SESSION['login_attempts'])) {
            return;
        }
        
        $currentTime = time();
        
        foreach ($_SESSION['login_attempts'] as $identifier => $attempts) {
            $_SESSION['login_attempts'][$identifier] = array_filter(
                $attempts,
                function($timestamp) use ($currentTime) {
                    return ($currentTime - $timestamp) < self::BLOCK_DURATION;
                }
            );
            
            if (empty($_SESSION['login_attempts'][$identifier])) {
                unset($_SESSION['login_attempts'][$identifier]);
            }
        }
    }
    
    private static function getClientIdentifier() {
        $ip = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        return md5($ip . $userAgent);
    }
    
    private static function getClientIP() {
        $ip = '0.0.0.0';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ipList[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
    
    private static function logAttempt($email, $success, $identifier) {
        $logDir = dirname(self::LOG_FILE);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $ip = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $timestamp = date('Y-m-d H:i:s');
        $status = $success ? 'SUCCESS' : 'FAILED';
        
        $logEntry = sprintf(
            "[%s] %s | IP: %s | Email: %s | Identifier: %s | User-Agent: %s\n",
            $timestamp,
            $status,
            $ip,
            $email,
            substr($identifier, 0, 8),
            substr($userAgent, 0, 100)
        );
        
        file_put_contents(self::LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public static function getRemainingTimeFormatted($seconds) {
        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;
        
        if ($minutes > 0) {
            return sprintf('%d minuto(s) e %d segundo(s)', $minutes, $secs);
        }
        return sprintf('%d segundo(s)', $secs);
    }
}

