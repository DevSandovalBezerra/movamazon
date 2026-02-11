<?php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/api/db.php';

class ConfigHelper
{
    private static array $cache = [];

    /**
     * Obtém o valor tipado de uma configuração.
     */
    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        global $pdo;
        $stmt = $pdo->prepare('SELECT valor, tipo FROM config WHERE chave = :chave LIMIT 1');
        $stmt->execute(['chave' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return $default;
        }

        $parsed = self::parseValue($row['valor'], $row['tipo'] ?? 'string');
        self::$cache[$key] = $parsed;
        return $parsed;
    }

    /**
     * Atualiza o valor e registra histórico.
     */
    public static function set(string $key, $value, ?int $userId = null): void
    {
        global $pdo;

        $stmt = $pdo->prepare('SELECT id, valor, tipo FROM config WHERE chave = :chave LIMIT 1');
        $stmt->execute(['chave' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new InvalidArgumentException("Configuração '{$key}' não encontrada.");
        }

        $tipo = $row['tipo'] ?? 'string';
        $novoValorFormatado = self::formatValueForStorage($value, $tipo);

        if ($row['valor'] === $novoValorFormatado) {
            // Nada mudou
            return;
        }

        try {
            $pdo->beginTransaction();

            $update = $pdo->prepare('UPDATE config SET valor = :valor, updated_by = :updated_by, updated_at = NOW() WHERE id = :id');
            $update->execute([
                'valor' => $novoValorFormatado,
                'updated_by' => $userId,
                'id' => $row['id']
            ]);

            $history = $pdo->prepare('INSERT INTO config_historico (config_id, chave, valor_antigo, valor_novo, alterado_por) VALUES (:config_id, :chave, :valor_antigo, :valor_novo, :alterado_por)');
            $history->execute([
                'config_id' => $row['id'],
                'chave' => $key,
                'valor_antigo' => $row['valor'],
                'valor_novo' => $novoValorFormatado,
                'alterado_por' => $userId
            ]);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        self::$cache[$key] = self::parseValue($novoValorFormatado, $tipo);
    }

    /**
     * Converte um valor bruto em valor tipado (útil em listagens).
     */
    public static function castValue($valor, string $tipo)
    {
        return self::parseValue($valor, $tipo);
    }

    /**
     * Limpa o cache em memória (útil para testes).
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    private static function parseValue(?string $valor, string $tipo)
    {
        if ($valor === null) {
            return null;
        }

        switch ($tipo) {
            case 'boolean':
                return filter_var($valor, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            case 'number':
                return is_numeric($valor) ? 0 + $valor : null;
            case 'json':
                $decoded = json_decode($valor, true);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
            case 'encrypted':
                $key = getenv('CONFIG_CRYPT_KEY');
                if (!$key || $valor === '') {
                    return null;
                }
                $payload = json_decode(base64_decode($valor), true);
                if (!is_array($payload) || empty($payload['value'])) {
                    return null;
                }
                $cipher = 'aes-256-gcm';
                $iv = base64_decode($payload['iv'] ?? '');
                $tag = base64_decode($payload['tag'] ?? '');
                $decrypted = openssl_decrypt($payload['value'], $cipher, $key, 0, $iv, $tag);
                return $decrypted === false ? null : $decrypted;
            default:
                return $valor;
        }
    }

    private static function formatValueForStorage($valor, string $tipo): ?string
    {
        if ($valor === null) {
            return null;
        }

        switch ($tipo) {
            case 'boolean':
                return filter_var($valor, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            case 'number':
                if (!is_numeric($valor)) {
                    throw new InvalidArgumentException('Valor numérico inválido.');
                }
                return (string) $valor;
            case 'json':
                $json = json_encode($valor);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new InvalidArgumentException('JSON inválido.');
                }
                return $json;
            case 'encrypted':
                $key = getenv('CONFIG_CRYPT_KEY');
                if (!$key) {
                    throw new RuntimeException('CONFIG_CRYPT_KEY não definido.');
                }
                $cipher = 'aes-256-gcm';
                $iv = random_bytes(openssl_cipher_iv_length($cipher));
                $tag = '';
                $encrypted = openssl_encrypt((string) $valor, $cipher, $key, 0, $iv, $tag);
                if ($encrypted === false) {
                    throw new RuntimeException('Falha ao criptografar valor.');
                }
                return base64_encode(json_encode([
                    'iv' => base64_encode($iv),
                    'tag' => base64_encode($tag),
                    'value' => $encrypted
                ]));
            default:
                return (string) $valor;
        }
    }
}

