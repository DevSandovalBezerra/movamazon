<?php
/**
 * Sistema de Cache Inteligente para APIs Públicas
 * Reduz carga no banco e melhora performance
 */

class CacheManager {
    private $cacheDir;
    private $defaultTTL;
    
    public function __construct($cacheDir = null, $defaultTTL = 300) {
        $this->cacheDir = $cacheDir ?: sys_get_temp_dir() . '/api_cache';
        $this->defaultTTL = $defaultTTL;
        $this->ensureCacheDirectory();
    }
    
    /**
     * Gera chave de cache baseada em parâmetros
     */
    public function generateKey($data, $prefix = 'api') {
        $hash = md5(serialize($data));
        return $prefix . '_' . $hash;
    }
    
    /**
     * Verifica se cache existe e é válido
     */
    public function exists($key) {
        $cacheFile = $this->getCacheFilePath($key);
        
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        $cacheData = $this->loadCache($cacheFile);
        if (!$cacheData) {
            return false;
        }
        
        // Verificar se expirou
        if (time() > $cacheData['expires']) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }
    
    /**
     * Obtém dados do cache
     */
    public function get($key) {
        $cacheFile = $this->getCacheFilePath($key);
        $cacheData = $this->loadCache($cacheFile);
        
        if (!$cacheData) {
            return null;
        }
        
        return $cacheData['data'];
    }
    
    /**
     * Salva dados no cache
     */
    public function set($key, $data, $ttl = null) {
        $ttl = $ttl ?: $this->defaultTTL;
        $cacheFile = $this->getCacheFilePath($key);
        
        $cacheData = [
            'data' => $data,
            'created' => time(),
            'expires' => time() + $ttl,
            'ttl' => $ttl
        ];
        
        return file_put_contents($cacheFile, json_encode($cacheData), LOCK_EX);
    }
    
    /**
     * Remove cache específico
     */
    public function delete($key) {
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        return true;
    }
    
    /**
     * Limpa cache expirado
     */
    public function cleanup() {
        $files = glob($this->cacheDir . '/*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $cacheData = $this->loadCache($file);
            if (!$cacheData || time() > $cacheData['expires']) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Obtém estatísticas do cache
     */
    public function getStats() {
        $files = glob($this->cacheDir . '/*.cache');
        $totalSize = 0;
        $expiredCount = 0;
        $validCount = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            $cacheData = $this->loadCache($file);
            
            if (!$cacheData || time() > $cacheData['expires']) {
                $expiredCount++;
            } else {
                $validCount++;
            }
        }
        
        return [
            'total_files' => count($files),
            'valid_files' => $validCount,
            'expired_files' => $expiredCount,
            'total_size' => $totalSize,
            'cache_dir' => $this->cacheDir
        ];
    }
    
    /**
     * Carrega dados do cache
     */
    private function loadCache($file) {
        if (!file_exists($file)) {
            return null;
        }
        
        $content = file_get_contents($file);
        if (!$content) {
            return null;
        }
        
        $data = json_decode($content, true);
        if (!$data || !isset($data['data'])) {
            return null;
        }
        
        return $data;
    }
    
    /**
     * Gera caminho do arquivo de cache
     */
    private function getCacheFilePath($key) {
        return $this->cacheDir . '/' . $key . '.cache';
    }
    
    /**
     * Garante que o diretório de cache existe
     */
    private function ensureCacheDirectory() {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
}

/**
 * Wrapper para cache de eventos
 */
class EventCache {
    private $cacheManager;
    
    public function __construct() {
        $this->cacheManager = new CacheManager();
    }
    
    /**
     * Obtém eventos do cache ou do banco
     */
    public function getEvents($filters = []) {
        $cacheKey = $this->cacheManager->generateKey($filters, 'events');
        
        // Tentar obter do cache
        if ($this->cacheManager->exists($cacheKey)) {
            return $this->cacheManager->get($cacheKey);
        }
        
        // Se não existe no cache, retorna null para buscar do banco
        return null;
    }
    
    /**
     * Salva eventos no cache
     */
    public function setEvents($filters, $data, $ttl = 300) {
        $cacheKey = $this->cacheManager->generateKey($filters, 'events');
        return $this->cacheManager->set($cacheKey, $data, $ttl);
    }
    
    /**
     * Invalida cache de eventos (quando há mudanças)
     */
    public function invalidateEvents() {
        $files = glob($this->cacheManager->cacheDir . '/events_*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
?> 
