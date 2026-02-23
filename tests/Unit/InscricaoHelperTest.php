<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once BASE_PATH . '/api/helpers/inscricao_logger.php';

final class InscricaoHelperTest extends TestCase
{
    private array $serverBackup = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->serverBackup = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        parent::tearDown();
    }

    public function testSanitizeLogDataMasksSensitiveKeysRecursively(): void
    {
        $input = [
            'cpf' => '12345678901',
            'token' => 'abc',
            'nome' => 'Joao',
            'nested' => [
                'password' => '12345678',
                'ok' => 'valor',
            ],
        ];

        $sanitized = sanitizeLogData($input);

        self::assertSame('123***901', $sanitized['cpf']);
        self::assertSame('***', $sanitized['token']);
        self::assertSame('Joao', $sanitized['nome']);
        self::assertSame('123***678', $sanitized['nested']['password']);
        self::assertSame('valor', $sanitized['nested']['ok']);
    }

    public function testGetClientInfoUsesForwardedIpAndUserAgent(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1, 10.0.0.2';
        $_SERVER['REMOTE_ADDR'] = '192.168.1.2';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';

        $client = getClientInfo();

        self::assertSame('10.0.0.1', $client['ip']);
        self::assertSame('PHPUnit', $client['user_agent']);
    }
}
