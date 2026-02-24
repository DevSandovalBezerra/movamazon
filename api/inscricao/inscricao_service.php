<?php

if (!function_exists('calcular_valor_total_inscricao')) {
    /**
     * Calcula o valor total da inscricao.
     */
    function calcular_valor_total_inscricao(
        float $valorModalidades,
        float $valorExtras,
        int $seguroContratado,
        float $valorDesconto,
        float $valorSeguroPadrao = 25.0
    ): float {
        $valorSeguro = $seguroContratado ? $valorSeguroPadrao : 0.0;
        return max(0, $valorModalidades + $valorExtras + $valorSeguro - $valorDesconto);
    }
}

if (!function_exists('calcular_valor_desconto_cupom')) {
    /**
     * Calcula o desconto em reais com base no tipo do cupom.
     */
    function calcular_valor_desconto_cupom(
        float $valorTotal,
        float $valorDescontoBase,
        string $tipoValor
    ): float {
        if ($tipoValor === 'percentual') {
            $valorDesconto = $valorTotal > 0 ? round($valorTotal * ($valorDescontoBase / 100), 2) : 0.0;
        } else {
            $valorDesconto = $valorDescontoBase;
        }

        if ($valorTotal > 0 && $valorDesconto > $valorTotal) {
            return $valorTotal;
        }

        return $valorDesconto;
    }
}

if (!function_exists('mascarar_codigo_cupom')) {
    /**
     * Mascara um codigo de cupom para log.
     */
    function mascarar_codigo_cupom(string $codigo): string
    {
        return strlen($codigo) > 4
            ? substr($codigo, 0, 2) . '***' . substr($codigo, -2)
            : '****';
    }
}

if (!function_exists('buscar_cupom_valido_para_evento')) {
    /**
     * Busca cupom valido para o evento (ou global) no momento atual.
     */
    function buscar_cupom_valido_para_evento(PDO $pdo, int $eventoId, string $codigo): ?array
    {
        $hoje = date('Y-m-d');
        $sql = "SELECT id, titulo, codigo_remessa, valor_desconto, tipo_valor, tipo_desconto, max_uso, usos_atuais, evento_id, data_inicio, data_validade
                FROM cupons_remessa
                WHERE (evento_id = ? OR evento_id IS NULL)
                  AND codigo_remessa = ?
                  AND status = 'ativo'
                  AND ? >= data_inicio
                  AND ? <= data_validade
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$eventoId, $codigo, $hoje, $hoje]);
        $cupom = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($cupom) ? $cupom : null;
    }
}
