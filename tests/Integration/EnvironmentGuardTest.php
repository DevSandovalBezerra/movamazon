<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class EnvironmentGuardTest extends TestCase
{
    public function testIntegrationSuiteIsExplicitlyEnabled(): void
    {
        if ((getenv('RUN_INTEGRATION_TESTS') ?: '0') !== '1') {
            $this->markTestSkipped('Set RUN_INTEGRATION_TESTS=1 to execute integration tests.');
        }

        self::assertTrue(true);
    }
}
