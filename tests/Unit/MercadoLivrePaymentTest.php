<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once BASE_PATH . '/api/mercadolivre/MercadoLivrePayment.php';

final class MercadoLivrePaymentTest extends TestCase
{
    public function testConstructorThrowsWhenAccessTokenIsMissing(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Access token nao configurado');

        new MercadoLivrePayment($this->buildConfig(['accesstoken' => '']));
    }

    public function testCriarPagamentoReturnsErrorWhenRequiredFieldIsMissing(): void
    {
        $service = new MercadoLivrePayment($this->buildConfig());

        $result = $service->criarPagamento([
            'modalidade_nome' => '5K',
            'valor_total' => 100.0,
            'nome_participante' => 'Alice',
            'email' => 'alice@example.com',
        ]);

        self::assertFalse($result['success']);
        self::assertStringContainsString('Campo obrigatorio nao informado', $result['error']);
    }

    public function testCriarPagamentoReturnsErrorForInvalidEmail(): void
    {
        $service = new MercadoLivrePayment($this->buildConfig());

        $result = $service->criarPagamento([
            'id' => 10,
            'modalidade_nome' => '5K',
            'valor_total' => 100.0,
            'nome_participante' => 'Alice',
            'email' => 'invalid-email',
        ]);

        self::assertFalse($result['success']);
        self::assertStringContainsString('Email invalido', $result['error']);
    }

    public function testCriarPagamentoBuildsPreferencePayloadAndSavesRepository(): void
    {
        $capturedRequest = [];
        $saved = [];

        $requestExecutor = function (
            string $method,
            string $apiUrl,
            string $endpoint,
            ?array $data,
            string $accessToken
        ) use (&$capturedRequest): array {
            $capturedRequest = [
                'method' => $method,
                'api_url' => $apiUrl,
                'endpoint' => $endpoint,
                'data' => $data,
                'token' => $accessToken,
            ];

            return [
                'id' => 'pref_123',
                'init_point' => 'https://pay.example/init',
            ];
        };

        $repository = function (int $inscricaoId, array $response) use (&$saved): void {
            $saved = [
                'inscricao_id' => $inscricaoId,
                'response' => $response,
            ];
        };

        $service = new MercadoLivrePayment($this->buildConfig(), $requestExecutor, $repository);

        $result = $service->criarPagamento([
            'id' => 10,
            'modalidade_nome' => '10K',
            'valor_total' => 149.9,
            'nome_participante' => 'Alice',
            'email' => 'alice@example.com',
        ]);

        self::assertTrue($result['success']);
        self::assertSame('pref_123', $result['preference_id']);
        self::assertSame('POST', $capturedRequest['method']);
        self::assertSame('/checkout/preferences', $capturedRequest['endpoint']);
        self::assertSame('MOVAMAZON_10', $capturedRequest['data']['external_reference']);
        self::assertSame(149.9, $capturedRequest['data']['items'][0]['unit_price']);
        self::assertSame('https://example.test/webhook', $capturedRequest['data']['notification_url']);
        self::assertSame(10, $saved['inscricao_id']);
        self::assertSame('pref_123', $saved['response']['id']);
    }

    private function buildConfig(array $overrides = []): array
    {
        $base = [
            'accesstoken' => 'token-test',
            'url_notification_api' => 'https://example.test/webhook',
            'environment' => 'test',
            'has_valid_tokens' => true,
            'back_urls' => [
                'success' => 'https://example.test/success',
                'pending' => 'https://example.test/pending',
                'failure' => 'https://example.test/failure',
            ],
            'payment_methods' => [],
            'item_defaults' => [
                'title' => 'Inscricao MovAmazon',
                'description' => 'Inscricao',
                'picture_url' => 'https://example.test/logo.png',
                'category_id' => 'sports',
                'currency_id' => 'BRL',
            ],
            'external_reference_prefix' => 'MOVAMAZON_',
            'api_url' => 'https://api.mercadopago.com',
        ];

        return array_replace_recursive($base, $overrides);
    }
}
