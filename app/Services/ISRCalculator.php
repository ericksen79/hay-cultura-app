<?php

namespace App\Services;

class ISRCalculator
{
    // Tramos Art. 37 LISR — Personas Naturales
    private static array $tramos = [
        [
            'nombre' => 'Tramo I — Exento',
            'desde' => 0.01,
            'hasta' => 4064.00,
            'porcentaje' => 0,
            'exceso' => 0,
            'cuota_fija' => 0,
            'descripcion' => 'Renta menor a $4,064.00 — No paga ISR',
        ],
        [
            'nombre' => 'Tramo II — 10%',
            'desde' => 4064.01,
            'hasta' => 9142.86,
            'porcentaje' => 0.10,
            'exceso' => 4064.00,
            'cuota_fija' => 212.12,
            'descripcion' => '10% sobre el exceso de $4,064.00 + $212.12',
        ],
        [
            'nombre' => 'Tramo III — 20%',
            'desde' => 9142.87,
            'hasta' => 22857.14,
            'porcentaje' => 0.20,
            'exceso' => 9142.86,
            'cuota_fija' => 720.00,
            'descripcion' => '20% sobre el exceso de $9,142.86 + $720.00',
        ],
        [
            'nombre' => 'Tramo IV — 30%',
            'desde' => 22857.15,
            'hasta' => null,
            'porcentaje' => 0.30,
            'exceso' => 22857.14,
            'cuota_fija' => 3462.86,
            'descripcion' => '30% sobre el exceso de $22,857.14 + $3,462.86',
        ],
    ];

    // Persona natural — Art. 37 LISR
    public static function calcularPersonaNatural(float $rentaNetaAnual): array
    {
        if ($rentaNetaAnual <= 0) {
            return self::vacio('natural');
        }

        $tramo = null;
        foreach (self::$tramos as $t) {
            if ($rentaNetaAnual >= $t['desde'] && ($t['hasta'] === null || $rentaNetaAnual <= $t['hasta'])) {
                $tramo = $t;
                break;
            }
        }

        if (! $tramo || $tramo['porcentaje'] === 0) {
            return array_merge(self::vacio('natural'), [
                'tramo' => $tramo['nombre'] ?? 'Exento',
                'descripcion' => $tramo['descripcion'] ?? 'No paga ISR',
                'exento' => true,
            ]);
        }

        $exceso = $rentaNetaAnual - $tramo['exceso'];
        $isrAnual = round(($exceso * $tramo['porcentaje']) + $tramo['cuota_fija'], 2);

        return [
            'tipo' => 'natural',
            'renta_neta' => $rentaNetaAnual,
            'isr_anual' => $isrAnual,
            'mensual' => round($isrAnual / 12, 2),
            'tramo' => $tramo['nombre'],
            'descripcion' => $tramo['descripcion'],
            'exento' => false,
            'desglose' => [
                'formula' => '$'.number_format($exceso, 2).' × '.($tramo['porcentaje'] * 100).'% + $'.number_format($tramo['cuota_fija'], 2),
            ],
        ];
    }

    // Persona jurídica — Art. 41 LISR
    public static function calcularPersonaJuridica(float $rentaNetaAnual): array
    {
        if ($rentaNetaAnual <= 0) {
            return self::vacio('juridica');
        }

        $tasa = $rentaNetaAnual <= 150000 ? 0.25 : 0.30;
        $isrAnual = round($rentaNetaAnual * $tasa, 2);

        return [
            'tipo' => 'juridica',
            'renta_neta' => $rentaNetaAnual,
            'isr_anual' => $isrAnual,
            'mensual' => round($isrAnual / 12, 2),
            'tasa' => ($tasa * 100).'%',
            'descripcion' => 'Tasa del '.($tasa * 100).'% sobre renta neta anual (Art. 41 LISR)',
            'exento' => false,
            'desglose' => ['formula' => '$'.number_format($rentaNetaAnual, 2).' × '.($tasa * 100).'%'],
        ];
    }

    private static function vacio(string $tipo): array
    {
        return ['tipo' => $tipo, 'renta_neta' => 0, 'isr_anual' => 0.00, 'mensual' => 0.00, 'exento' => true, 'descripcion' => 'Sin renta imponible'];
    }
}
