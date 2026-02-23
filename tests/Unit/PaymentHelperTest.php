<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once BASE_PATH . '/api/mercadolivre/payment_helper.php';

final class PaymentHelperTest extends TestCase
{
    public function testConstructorThrowsWhenTokenIsMissing(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Access token do Mercado Pago nao configurado');

        new PaymentHelper(['accesstoken' => '']);
    }

    public function testConsultarStatusPagamentoThrowsWhenIdIsEmpty(): void
    {
        $helper = new PaymentHelper(['accesstoken' => 'token-test'], $this->noopExecutor());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('obrigatorio');
        $helper->consultarStatusPagamento('');
    }

    public function testConsultarStatusPagamentoWithExternalReferenceResolvesPaymentId(): void
    {
        $executor = function (string $method, string $url): array {
            if (str_contains($url, '/payments/search')) {
                return [
                    'http_code' => 200,
                    'body' => json_encode(['results' => [['id' => '987', 'date_created' => '2026-01-01T00:00:00Z']]]),
                    'error' => '',
                ];
            }

            return [
                'http_code' => 200,
                'body' => json_encode(['id' => 987, 'status' => 'approved']),
                'error' => '',
            ];
        };

        $helper = new PaymentHelper(['accesstoken' => 'token-test'], $executor);
        $result = $helper->consultarStatusPagamento('MOVAMAZON_10');

        self::assertSame(987, $result['id']);
        self::assertSame('approved', $result['status']);
    }

    public function testMapeamentosDeStatus(): void
    {
        self::assertSame('pago', PaymentHelper::mapearStatus('approved'));
        self::assertSame('pendente', PaymentHelper::mapearStatus('unknown'));
        self::assertSame('cancelado', PaymentHelper::mapearStatusPagamentosML('refunded'));
    }

    private function noopExecutor(): callable
    {
        return static function (): array {
            return [
                'http_code' => 200,
                'body' => '{}',
                'error' => '',
            ];
        };
    }
}
