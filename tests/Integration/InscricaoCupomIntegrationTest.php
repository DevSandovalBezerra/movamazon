<?php

declare(strict_types=1);

require_once BASE_PATH . '/api/inscricao/inscricao_service.php';
require_once __DIR__ . '/DatabaseIntegrationTestCase.php';

final class InscricaoCupomIntegrationTest extends DatabaseIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        if ((getenv('RUN_INTEGRATION_TESTS') ?: '0') !== '1') {
            return;
        }

        self::integrationPdo()->exec(
            "CREATE TABLE IF NOT EXISTS cupons_remessa (
                id INT AUTO_INCREMENT PRIMARY KEY,
                titulo VARCHAR(255) NULL,
                codigo_remessa VARCHAR(120) NOT NULL,
                valor_desconto DECIMAL(12,2) NOT NULL DEFAULT 0,
                tipo_valor VARCHAR(20) NOT NULL DEFAULT 'valor_real',
                tipo_desconto VARCHAR(30) NULL,
                max_uso INT NOT NULL DEFAULT 1,
                usos_atuais INT NOT NULL DEFAULT 0,
                evento_id INT NULL,
                data_inicio DATE NOT NULL,
                data_validade DATE NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'ativo'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public function testBuscarCupomValidoParaEventoRetornaRegistro(): void
    {
        $pdo = self::integrationPdo();
        $stmt = $pdo->prepare(
            "INSERT INTO cupons_remessa
                (titulo, codigo_remessa, valor_desconto, tipo_valor, tipo_desconto, max_uso, usos_atuais, evento_id, data_inicio, data_validade, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            'Cupom Teste',
            'DESC10',
            10.00,
            'valor_real',
            'fixo',
            50,
            1,
            200,
            date('Y-m-d', strtotime('-1 day')),
            date('Y-m-d', strtotime('+1 day')),
            'ativo',
        ]);

        $cupom = buscar_cupom_valido_para_evento($pdo, 200, 'DESC10');

        self::assertIsArray($cupom);
        self::assertSame('DESC10', $cupom['codigo_remessa']);
    }

    public function testBuscarCupomValidoParaEventoRetornaNullQuandoExpirado(): void
    {
        $pdo = self::integrationPdo();
        $stmt = $pdo->prepare(
            "INSERT INTO cupons_remessa
                (titulo, codigo_remessa, valor_desconto, tipo_valor, tipo_desconto, max_uso, usos_atuais, evento_id, data_inicio, data_validade, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            'Cupom Expirado',
            'OLD50',
            50.00,
            'percentual',
            'percentual',
            20,
            0,
            201,
            date('Y-m-d', strtotime('-10 day')),
            date('Y-m-d', strtotime('-1 day')),
            'ativo',
        ]);

        $cupom = buscar_cupom_valido_para_evento($pdo, 201, 'OLD50');

        self::assertNull($cupom);
    }
}
