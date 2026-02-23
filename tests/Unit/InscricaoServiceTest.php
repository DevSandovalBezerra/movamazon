<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once BASE_PATH . '/api/inscricao/inscricao_service.php';

final class InscricaoServiceTest extends TestCase
{
    public function testCalcularValorTotalInscricaoComSeguro(): void
    {
        $total = calcular_valor_total_inscricao(100.0, 20.0, 1, 10.0);
        self::assertSame(135.0, $total);
    }

    public function testCalcularValorTotalInscricaoNaoFicaNegativo(): void
    {
        $total = calcular_valor_total_inscricao(10.0, 0.0, 0, 50.0);
        self::assertSame(0.0, $total);
    }

    public function testCalcularValorDescontoCupomPercentualComTeto(): void
    {
        self::assertSame(25.0, calcular_valor_desconto_cupom(100.0, 25.0, 'percentual'));
        self::assertSame(100.0, calcular_valor_desconto_cupom(100.0, 150.0, 'valor_real'));
    }

    public function testMascararCodigoCupom(): void
    {
        self::assertSame('AB***YZ', mascarar_codigo_cupom('ABCDEFYZ'));
        self::assertSame('****', mascarar_codigo_cupom('AB'));
    }
}
