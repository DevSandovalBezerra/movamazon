<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once BASE_PATH . '/api/financeiro/financeiro_service.php';

final class FinanceiroServiceTest extends TestCase
{
    public function testFinDecimalHandlesDifferentFormats(): void
    {
        self::assertSame(0.0, fin_decimal(null));
        self::assertSame(0.0, fin_decimal(''));
        self::assertSame(0.0, fin_decimal('abc'));
        self::assertSame(10.0, fin_decimal(10));
        self::assertSame(-12.35, fin_decimal(-12.345));
        self::assertSame(1234.56, fin_decimal('1.234,56'));
        self::assertSame(1234.56, fin_decimal('1,234.56'));
    }

    public function testFinToDatetimeConvertsAndRejectsInvalidValues(): void
    {
        self::assertNull(fin_to_datetime(null));
        self::assertNull(fin_to_datetime(''));
        self::assertNull(fin_to_datetime('not-a-date'));
        self::assertSame('2025-01-02 03:04:05', fin_to_datetime('2025-01-02 03:04:05'));
    }

    public function testFinStatusSaldoConsiderado(): void
    {
        self::assertTrue(fin_status_saldo_considerado('disponivel'));
        self::assertTrue(fin_status_saldo_considerado('liquidado'));
        self::assertFalse(fin_status_saldo_considerado('pendente'));
        self::assertFalse(fin_status_saldo_considerado('bloqueado'));
    }

    public function testFinNormalizarMetadata(): void
    {
        self::assertNull(fin_normalizar_metadata(null));
        self::assertNull(fin_normalizar_metadata(''));
        self::assertSame('abc', fin_normalizar_metadata('abc'));

        $json = fin_normalizar_metadata(['a' => 1, 'b' => 'x']);
        self::assertIsString($json);
        self::assertSame(['a' => 1, 'b' => 'x'], json_decode($json, true, 512, JSON_THROW_ON_ERROR));
    }
}
