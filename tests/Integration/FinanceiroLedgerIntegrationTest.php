<?php

declare(strict_types=1);

require_once BASE_PATH . '/api/financeiro/financeiro_service.php';
require_once __DIR__ . '/DatabaseIntegrationTestCase.php';

final class FinanceiroLedgerIntegrationTest extends DatabaseIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        if ((getenv('RUN_INTEGRATION_TESTS') ?: '0') !== '1') {
            return;
        }

        self::integrationPdo()->exec(
            "CREATE TABLE IF NOT EXISTS financeiro_ledger (
                id INT AUTO_INCREMENT PRIMARY KEY,
                evento_id INT NOT NULL,
                direcao VARCHAR(20) NOT NULL,
                valor DECIMAL(12,2) NOT NULL,
                status VARCHAR(20) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public function testFinSaldoDisponivelCalculaComBloqueio(): void
    {
        $pdo = self::integrationPdo();

        $stmt = $pdo->prepare(
            'INSERT INTO financeiro_ledger (evento_id, direcao, valor, status) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([100, 'credito', 100.00, 'disponivel']);
        $stmt->execute([100, 'debito', 10.00, 'liquidado']);
        $stmt->execute([100, 'debito', 5.00, 'bloqueado']);
        $stmt->execute([100, 'credito', 50.00, 'pendente']);

        $saldo = fin_saldo_disponivel($pdo, 100);
        self::assertSame(85.0, $saldo);
    }
}
