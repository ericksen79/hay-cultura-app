<?php

namespace App\Services;

class IVACalculator
{
    const TASA = 0.13;

    public static function calcular(float $ventasGravadas, float $comprasConCF): array
    {
        $debito  = round($ventasGravadas * self::TASA, 2);
        $credito = round($comprasConCF   * self::TASA, 2);
        $diff    = round($debito - $credito, 2);

        return [
            'ventas_gravadas' => $ventasGravadas,
            'compras_con_cf'  => $comprasConCF,
            'tasa'            => '13%',
            'debito_fiscal'   => $debito,
            'credito_fiscal'  => $credito,
            'diferencia'      => $diff,
            'iva_pagar'       => max(0, $diff),
            'saldo_a_favor'   => $diff < 0 ? abs($diff) : 0,
            'hay_deuda'       => $diff > 0,
            'estado'          => $diff > 0
                ? 'Debes pagar $'.number_format($diff,2).' a Hacienda'
                : 'Saldo a favor de $'.number_format(abs($diff),2),
            'desglose' => [
                'formula_debito'  => '$'.number_format($ventasGravadas,2).' × 13% = $'.number_format($debito,2),
                'formula_credito' => '$'.number_format($comprasConCF,2).' × 13% = $'.number_format($credito,2),
                'formula_final'   => '$'.number_format($debito,2).' − $'.number_format($credito,2).' = $'.number_format($diff,2),
            ],
        ];
    }
}