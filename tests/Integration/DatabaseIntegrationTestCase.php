<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

abstract class DatabaseIntegrationTestCase extends TestCase
{
    private static ?PDO $pdo = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if ((getenv('RUN_INTEGRATION_TESTS') ?: '0') !== '1') {
            return;
        }

        testing_integration_create_database_if_missing();
        self::$pdo = testing_integration_pdo();
    }

    protected function setUp(): void
    {
        parent::setUp();
        if ((getenv('RUN_INTEGRATION_TESTS') ?: '0') !== '1') {
            $this->markTestSkipped('Set RUN_INTEGRATION_TESTS=1 to execute integration tests.');
        }

        self::integrationPdo()->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ((getenv('RUN_INTEGRATION_TESTS') ?: '0') !== '1') {
            parent::tearDown();
            return;
        }

        if (self::$pdo instanceof PDO && self::$pdo->inTransaction()) {
            self::$pdo->rollBack();
        }

        parent::tearDown();
    }

    protected static function integrationPdo(): PDO
    {
        if (!self::$pdo instanceof PDO) {
            throw new RuntimeException('Integration PDO not initialized.');
        }

        return self::$pdo;
    }
}
