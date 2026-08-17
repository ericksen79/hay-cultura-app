<?php

namespace App\Services;

class ProfitCalculator
{
    public static function calcularUtilidad(float $ventas, float $costos, float $fijos, float $variables): array
    {
        $bruta = $ventas - $costos;
        $gastos = $fijos + $variables;
        $operativa = $bruta - $gastos;
        $reservaISR = $operativa > 0 ? round($operativa * 0.15, 2) : 0;
        $neta = $operativa - $reservaISR;
        $margen = $ventas > 0 ? round(($neta / $ventas) * 100, 1) : 0;

        return [
            'ventas' => $ventas,
            'costos' => $costos,
            'gastos_fijos' => $fijos,
            'gastos_variables' => $variables,
            'utilidad_bruta' => round($bruta, 2),
            'gastos_operativos' => round($gastos, 2),
            'utilidad_operativa' => round($operativa, 2),
            'reserva_isr' => $reservaISR,
            'utilidad_neta' => round($neta, 2),
            'margen_ganancia' => $margen,
            'punto_equilibrio' => round($costos + $gastos, 2),
            'es_rentable' => $neta > 0,
            'salud' => self::salud($margen),
        ];
    }

    public static function calcularRetiroSeguro(float $utilidadNeta, float $reservaImpuestos, float $cajaMinima): array
    {
        $retiro = max(0, $utilidadNeta - $reservaImpuestos - $cajaMinima);
        $pct = $utilidadNeta > 0 ? round(($retiro / $utilidadNeta) * 100, 1) : 0;
        $situacion = match (true) {
            $retiro <= 0 => 'sin_retiro',
            $pct < 30 => 'ajustado',
            $pct < 60 => 'moderado',
            default => 'holgado',
        };

        return [
            'utilidad_neta' => $utilidadNeta,
            'reserva_impuestos' => $reservaImpuestos,
            'caja_minima' => $cajaMinima,
            'retiro_seguro' => round($retiro, 2),
            'porcentaje_retiro' => $pct,
            'situacion' => $situacion,
            'mensaje' => match ($situacion) {
                'sin_retiro' => 'Este mes no se recomienda retirar.',
                'ajustado' => 'Retira solo lo indispensable este mes.',
                'moderado' => 'Puedes retirar con moderación.',
                'holgado' => 'Buena posición para retirar.',
            },
            'desglose' => [
                'formula' => '$'.number_format($utilidadNeta, 2).' − $'.number_format($reservaImpuestos, 2).' − $'.number_format($cajaMinima, 2).' = $'.number_format($retiro, 2),
            ],
        ];
    }

    private static function salud(float $margen): array
    {
        return match (true) {
            $margen < 0 => ['estado' => 'critico',   'texto' => 'Crítico',    'mensaje' => 'El negocio está perdiendo dinero. Revisa urgente tus costos.', 'color' => 'red',   'emoji' => '🔴'],
            $margen < 5 => ['estado' => 'riesgo',    'texto' => 'En riesgo',  'mensaje' => 'Margen muy bajo. Cualquier imprevisto puede generar pérdidas.', 'color' => 'amber', 'emoji' => '🟡'],
            $margen < 15 => ['estado' => 'estable',   'texto' => 'Estable',    'mensaje' => 'Margen aceptable. Busca optimizar costos para mejorar.',        'color' => 'blue',  'emoji' => '🔵'],
            $margen < 30 => ['estado' => 'saludable', 'texto' => 'Saludable',  'mensaje' => 'Buen margen. El negocio tiene solidez financiera.',             'color' => 'green', 'emoji' => '🟢'],
            default => ['estado' => 'excelente', 'texto' => 'Excelente',  'mensaje' => 'Margen excepcional. Considera reinvertir en crecimiento.',      'color' => 'green', 'emoji' => '✅'],
        };
    }
}
