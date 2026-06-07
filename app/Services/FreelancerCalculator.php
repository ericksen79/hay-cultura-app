<?php

namespace App\Services;

class FreelancerCalculator
{
    /**
     * Calores predeterminados de comisiones de plataforma en El Salvador
     */
    private static array $platformDefaults = [
        'payoneer'      => ['pct' => 0.0399, 'fijo' => 0.49],
        'wise'          => ['pct' => 0.01,   'fijo' => 0.00],
        'paypal'        => ['pct' => 0.054,  'fijo' => 0.30],
        'stripe'        => ['pct' => 0.039,  'fijo' => 0.30],
        'deel'          => ['pct' => 0.00,   'fijo' => 5.00],
        'upwork'        => ['pct' => 0.10,   'fijo' => 2.00],
        'western_union' => ['pct' => 0.04,   'fijo' => 0.00],
        'swift'         => ['pct' => 0.00,   'fijo' => 0.00],
        'bitcoin'       => ['pct' => 0.01,   'fijo' => 0.00],
        'otro'          => ['pct' => 0.00,   'fijo' => 0.00],
    ];

    /**
     * Tarifas de bancos en El Salvador por recibir transferencias SWIFT
     */
    private static array $bankSwiftFees = [
        'agricola'   => ['base' => 25.00, 'receptor_bajo' => 5.65, 'receptor_alto' => 11.30, 'limite' => 3000.00],
        'bac'        => ['base' => 25.00, 'receptor' => 35.00],
        'cuscatlan'  => ['base' => 25.00, 'receptor' => 12.50],
        'promerica'  => ['base' => 25.00, 'receptor' => 20.00],
        'atlantida'  => ['base' => 25.00, 'receptor' => 12.50],
        'industrial' => ['base' => 25.00, 'receptor' => 10.00],
        'otro'       => ['base' => 25.00, 'receptor' => 15.00],
    ];

    /**
     * Realiza el cálculo principal de flujo de dinero internacional y salud financiera.
     */
    public static function calcularSalud(array $d): array
    {
        $facturado = (float)($d['monto_facturado'] ?? 0);
        $metodo    = strtolower($d['metodo_cobro'] ?? 'otro');
        $banco     = strtolower($d['banco_receptor'] ?? 'otro');

        // 1. Comisiones de Plataforma (Fee Plataforma)
        $plataformaDefault = self::$platformDefaults[$metodo] ?? self::$platformDefaults['otro'];
        $feePct  = isset($d['fee_porcentual_manual']) ? (float)$d['fee_porcentual_manual'] / 100 : $plataformaDefault['pct'];
        $feeFijo = isset($d['fee_fijo_manual']) ? (float)$d['fee_fijo_manual'] : $plataformaDefault['fijo'];

        $feePlataforma = round(($facturado * $feePct) + $feeFijo, 2);

        // 2. IVA sobre comisiones (13% opcional)
        $aplicarIva  = filter_var($d['aplicar_iva_comision'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $ivaComision = $aplicarIva ? round($feePlataforma * 0.13, 2) : 0.00;

        // 3. Comisiones de Banco Receptor / Retiro
        $feeBanco = 0.00;
        $feeCorresponsal = 0.00;

        if ($metodo === 'payoneer') {
            // Payoneer cobra un 2% por transferir a bancos locales sobre el monto remanente
            $remanente = max(0.00, $facturado - $feePlataforma - $ivaComision);
            $feeBanco  = round($remanente * 0.02, 2);
        } elseif ($metodo === 'wise') {
            // Wise cobra un retiro plano de transferencia a cuenta local (ej. $3.00)
            $feeBanco = 3.00;
        } elseif ($metodo === 'swift') {
            // SWIFT directa: banco corresponsal ($25) + comisión receptor local
            $feeCorresponsal = 25.00;
            $bankData = self::$bankSwiftFees[$banco] ?? self::$bankSwiftFees['otro'];

            if (isset($bankData['receptor_bajo'])) {
                $feeBanco = ($facturado <= $bankData['limite']) ? $bankData['receptor_bajo'] : $bankData['receptor_alto'];
            } else {
                $feeBanco = $bankData['receptor'] ?? 15.00;
            }
        } else {
            // Otros métodos: aproximaciones estándares de retiro bancario
            if ($metodo === 'paypal' || $metodo === 'stripe') {
                $feeBanco = 5.00; // retiro estándar a tarjeta/banco
            } elseif ($metodo === 'upwork') {
                $feeBanco = 2.00; // retiro directo a cuenta salvadoreña
            } elseif ($metodo === 'deel') {
                $feeBanco = 2.00; // retiro plano
            } elseif ($metodo === 'bitcoin') {
                $feeBanco = 2.00; // transferencia/cashout aproximado
            } else {
                $feeBanco = 0.00;
            }
        }

        // Totales de cobro
        $totalComisiones = round($feePlataforma + $ivaComision + $feeBanco + $feeCorresponsal, 2);
        $netoRecibido    = max(0.00, round($facturado - $totalComisiones, 2));

        // Eficiencia de cobro
        $eficiencia = $facturado > 0 ? round(($netoRecibido / $facturado) * 100, 2) : 0.00;
        
        $rangoEficiencia = 'costoso';
        $mensajeEficiencia = 'Comisiones muy altas. Te recomendamos buscar alternativas de cobro.';
        
        if ($eficiencia >= 97.00) {
            $rangoEficiencia = 'excelente';
            $mensajeEficiencia = 'Excelente eficiencia. Estás maximizando tus ingresos recibidos.';
        } elseif ($eficiencia >= 94.00) {
            $rangoEficiencia = 'bueno';
            $mensajeEficiencia = 'Buen nivel de cobro, pero hay margen para optimizar.';
        } elseif ($eficiencia >= 90.00) {
            $rangoEficiencia = 'mejorable';
            $mensajeEficiencia = 'Eficiencia regular. Podrías ahorrar reduciendo comisiones.';
        }

        // Ahorro potencial (comparación con Wise)
        $ahorroPotencial = 0.00;
        $ahorroMensaje   = '';
        if ($metodo !== 'wise' && $facturado > 0) {
            $wisePlataforma = round($facturado * 0.01, 2);
            $wiseBanco      = 3.00;
            $wiseTotal      = $wisePlataforma + $wiseBanco;
            if ($totalComisiones > $wiseTotal) {
                $ahorroPotencial = round($totalComisiones - $wiseTotal, 2);
                $ahorroMensaje   = "Con Wise hubieras pagado aproximadamente $" . number_format($wiseTotal, 2) . ". ¡Ahorro potencial: $" . number_format($ahorroPotencial, 2) . "!";
            }
        }

        // 4. Gastos operativos deducibles
        $gastosInput = $d['gastos_deducibles'] ?? [];
        $totalGastos = 0.00;
        $gastosDetalle = [];
        foreach ($gastosInput as $g) {
            if (!empty($g['concepto']) && isset($g['monto']) && (float)$g['monto'] > 0) {
                $m = round((float)$g['monto'], 2);
                $totalGastos += $m;
                $gastosDetalle[] = [
                    'concepto' => htmlspecialchars($g['concepto']),
                    'monto'    => $m
                ];
            }
        }

        // 5. Utilidad operativa
        $utilidadOperativa = max(0.00, round($netoRecibido - $totalGastos, 2));

        // 6. Estimación ISR (Art. 37 LISR)
        $rentaAnual = $utilidadOperativa * 12;
        $isrCalculado = ISRCalculator::calcularPersonaNatural($rentaAnual);
        $reservaIsrSugerida = $isrCalculado['mensual'];

        // ¿Está aplicando el ahorro? Depende de si está "Inscrito en Hacienda" o si seleccionó guardar.
        $reservaIsrActiva = filter_var($d['inscrito_hacienda'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $reservaIsrEfectiva = $reservaIsrActiva ? $reservaIsrSugerida : 0.00;

        // 7. ISSS Independiente
        $cotizaIsss = strtolower($d['cotiza_isss'] ?? 'no');
        $isssMonto = 0.00;
        if ($cotizaIsss === 'individual') {
            $isssMonto = 40.00;
        } elseif ($cotizaIsss === 'familiar') {
            $isssMonto = 56.00;
        }

        // 8. Fondo de Emergencia
        $fondoEmergenciaPct = (float)($d['fondo_emergencia_pct'] ?? 0);
        $fondoEmergencia = round($utilidadOperativa * ($fondoEmergenciaPct / 100), 2);

        // 9. Dinero Disponible para Retiro
        $dineroDisponible = max(0.00, round($utilidadOperativa - $reservaIsrEfectiva - $isssMonto - $fondoEmergencia, 2));

        // Indicadores UX - Salud Financiera
        $saludFinanciera = 'riesgo_alto';
        $saludMensaje = 'Retiras todo tu dinero sin reservas de impuestos ni emergencias. Estás en riesgo alto ante cualquier imprevisto o auditoría.';
        
        if ($reservaIsrActiva && $isssMonto > 0 && $fondoEmergencia > 0) {
            $saludFinanciera = 'excelente';
            $saludMensaje = '¡Excelente! Cuentas con reserva de impuestos, cobertura médica de ISSS y fondo de emergencias activo.';
        } elseif ($reservaIsrActiva && $fondoEmergencia > 0) {
            $saludFinanciera = 'excelente'; // Según la especificación, con reserva + fondo = excelente / saludable. Vamos a ajustarlo al prompt.
            $saludMensaje = '¡Excelente! Tienes reserva de ISR y fondo de emergencia activo.';
        } elseif ($reservaIsrActiva) {
            $saludFinanciera = 'saludable';
            $saludMensaje = 'Saludable. Reservas tus impuestos mensualmente, pero te recomendamos activar un fondo de emergencia.';
        } elseif ($isssMonto > 0 || $fondoEmergencia > 0) {
            $saludFinanciera = 'riesgo_moderado';
            $saludMensaje = 'Riesgo Moderado. Tienes algunas protecciones, pero no estás reservando para tu impuesto sobre la renta (ISR).';
        }

        return [
            'monto_facturado'      => $facturado,
            'metodo_cobro'         => $metodo,
            'banco_receptor'       => $banco,
            'comisiones'           => [
                'plataforma'       => $feePlataforma,
                'iva'              => $ivaComision,
                'banco'            => $feeBanco,
                'corresponsal'     => $feeCorresponsal,
                'total'            => $totalComisiones
            ],
            'neto_recibido'        => $netoRecibido,
            'eficiencia'           => [
                'porcentaje'       => $eficiencia,
                'rango'            => $rangoEficiencia,
                'mensaje'          => $mensajeEficiencia,
                'ahorro_potencial' => $ahorroPotencial,
                'ahorro_mensaje'   => $ahorroMensaje
            ],
            'gastos_operativos'    => [
                'total'            => $totalGastos,
                'detalle'          => $gastosDetalle
            ],
            'utilidad_operativa'   => $utilidadOperativa,
            'isr'                  => [
                'anual_estimado'   => $isrCalculado['isr_anual'],
                'mensual_sugerido' => $reservaIsrSugerida,
                'aplicado'         => $reservaIsrEfectiva,
                'activo'           => $reservaIsrActiva,
                'tramo'            => $isrCalculado['tramo'] ?? 'Exento',
                'descripcion'      => $isrCalculado['descripcion'] ?? 'No paga ISR'
            ],
            'isss'                 => [
                'tipo'             => $cotizaIsss,
                'monto'            => $isssMonto
            ],
            'fondo_emergencia'     => [
                'porcentaje'       => $fondoEmergenciaPct,
                'monto'            => $fondoEmergencia
            ],
            'dinero_disponible'    => $dineroDisponible,
            'salud_financiera'     => [
                'estado'           => $saludFinanciera,
                'mensaje'          => $saludMensaje
            ]
        ];
    }

    /**
     * Compara el neto recibido en todas las plataformas principales para un monto determinado.
     */
    public static function compararPlataformas(float $monto): array
    {
        $plataformas = [
            'wise' => [
                'nombre' => 'Wise',
                'logo' => 'payment',
                'calcular' => function($m) {
                    $fee = round($m * 0.01, 2);
                    $retiro = 3.00;
                    return ['fee' => $fee, 'banco' => $retiro, 'neto' => max(0.00, round($m - $fee - $retiro, 2))];
                }
            ],
            'payoneer' => [
                'nombre' => 'Payoneer',
                'logo' => 'account_balance_wallet',
                'calcular' => function($m) {
                    $fee = round(($m * 0.0399) + 0.49, 2);
                    $remanente = max(0.00, $m - $fee);
                    $retiro = round($remanente * 0.02, 2);
                    return ['fee' => $fee, 'banco' => $retiro, 'neto' => max(0.00, round($remanente - $retiro, 2))];
                }
            ],
            'swift_agricola' => [
                'nombre' => 'SWIFT Directo (Banco Agrícola)',
                'logo' => 'language',
                'calcular' => function($m) {
                    $corresponsal = 25.00;
                    $receptor = ($m <= 3000.00) ? 5.65 : 11.30;
                    return ['fee' => $corresponsal, 'banco' => $receptor, 'neto' => max(0.00, round($m - $corresponsal - $receptor, 2))];
                }
            ],
            'swift_bac' => [
                'nombre' => 'SWIFT Directo (BAC Credomatic)',
                'logo' => 'language',
                'calcular' => function($m) {
                    $corresponsal = 25.00;
                    $receptor = 35.00;
                    return ['fee' => $corresponsal, 'banco' => $receptor, 'neto' => max(0.00, round($m - $corresponsal - $receptor, 2))];
                }
            ],
            'swift_cuscatlan' => [
                'nombre' => 'SWIFT Directo (Banco Cuscatlán)',
                'logo' => 'language',
                'calcular' => function($m) {
                    $corresponsal = 25.00;
                    $receptor = 12.50;
                    return ['fee' => $corresponsal, 'banco' => $receptor, 'neto' => max(0.00, round($m - $corresponsal - $receptor, 2))];
                }
            ],
            'swift_promerica' => [
                'nombre' => 'SWIFT Directo (Banco Promerica)',
                'logo' => 'language',
                'calcular' => function($m) {
                    $corresponsal = 25.00;
                    $receptor = 20.00;
                    return ['fee' => $corresponsal, 'banco' => $receptor, 'neto' => max(0.00, round($m - $corresponsal - $receptor, 2))];
                }
            ],
            'swift_industrial' => [
                'nombre' => 'SWIFT Directo (Banco Industrial)',
                'logo' => 'language',
                'calcular' => function($m) {
                    $corresponsal = 25.00;
                    $receptor = 10.00;
                    return ['fee' => $corresponsal, 'banco' => $receptor, 'neto' => max(0.00, round($m - $corresponsal - $receptor, 2))];
                }
            ],
            'western_union' => [
                'nombre' => 'Western Union',
                'logo' => 'payments',
                'calcular' => function($m) {
                    $fee = round($m * 0.04, 2);
                    return ['fee' => $fee, 'banco' => 0.00, 'neto' => max(0.00, round($m - $fee, 2))];
                }
            ],
            'paypal' => [
                'nombre' => 'PayPal',
                'logo' => 'shopping_cart',
                'calcular' => function($m) {
                    $fee = round(($m * 0.054) + 0.30, 2);
                    $retiro = 5.00;
                    return ['fee' => $fee, 'banco' => $retiro, 'neto' => max(0.00, round($m - $fee - $retiro, 2))];
                }
            ],
            'stripe' => [
                'nombre' => 'Stripe',
                'logo' => 'credit_card',
                'calcular' => function($m) {
                    $fee = round(($m * 0.039) + 0.30, 2);
                    $retiro = 5.00;
                    return ['fee' => $fee, 'banco' => $retiro, 'neto' => max(0.00, round($m - $fee - $retiro, 2))];
                }
            ],
            'deel' => [
                'nombre' => 'Deel',
                'logo' => 'work',
                'calcular' => function($m) {
                    $fee = 5.00;
                    $retiro = 2.00;
                    return ['fee' => $fee, 'banco' => $retiro, 'neto' => max(0.00, round($m - $fee - $retiro, 2))];
                }
            ],
            'upwork' => [
                'nombre' => 'Upwork',
                'logo' => 'badge',
                'calcular' => function($m) {
                    $fee = round($m * 0.10, 2);
                    $retiro = 2.00;
                    return ['fee' => $fee, 'banco' => $retiro, 'neto' => max(0.00, round($m - $fee - $retiro, 2))];
                }
            ],
            'bitcoin' => [
                'nombre' => 'Bitcoin / Chivo Wallet',
                'logo' => 'currency_bitcoin',
                'calcular' => function($m) {
                    $fee = round($m * 0.01, 2);
                    $retiro = 2.00;
                    return ['fee' => $fee, 'banco' => $retiro, 'neto' => max(0.00, round($m - $fee - $retiro, 2))];
                }
            ],
            'hugo_cash' => [
                'nombre' => 'Hugo Cash / Pasarela local',
                'logo' => 'storefront',
                'calcular' => function($m) {
                    $fee = round(($m * 0.03) + 0.25, 2);
                    return ['fee' => $fee, 'banco' => 0.00, 'neto' => max(0.00, round($m - $fee, 2))];
                }
            ]
        ];

        $resultados = [];
        foreach ($plataformas as $id => $p) {
            $calc = $p['calcular']($monto);
            $totalFee = round($calc['fee'] + $calc['banco'], 2);
            $eficiencia = $monto > 0 ? round(($calc['neto'] / $monto) * 100, 1) : 0.0;
            
            $resultados[] = [
                'id'         => $id,
                'nombre'     => $p['nombre'],
                'logo'       => $p['logo'],
                'comision'   => $totalFee,
                'neto'       => $calc['neto'],
                'eficiencia' => $eficiencia,
                'detalles'   => [
                    'plataforma'   => $calc['fee'] ?? 0.00,
                    'banco'        => $calc['banco'] ?? 0.00,
                    'corresponsal' => (strpos($id, 'swift') === 0) ? 25.00 : 0.00,
                ],
                'observacion' => self::obtenerObservacionPlataforma($id)
            ];
        }

        // Ordenar de mayor neto recibido a menor neto
        usort($resultados, function($a, $b) {
            return $b['neto'] <=> $a['neto'];
        });

        return $resultados;
    }

    /**
     * Obtiene una observación educativa útil para la plataforma dada.
     */
    private static function obtenerObservacionPlataforma(string $id): string
    {
        return match ($id) {
            'wise' => 'Wise cobra comisiones bajas (1%) y retiro plano de $3.00. Muy recomendado.',
            'payoneer' => 'Payoneer aplica 3.99% + $0.49 por recibir y cobra 2.0% adicional por transferir a bancos de El Salvador.',
            'swift_agricola' => 'El dinero viaja por SWIFT a Banco Agrícola. Aplica comisión corresponsal ($25) y receptor local bajo ($5.65 hasta $3,000, luego $11.30). Recomendado para montos altos.',
            'swift_bac' => 'SWIFT a BAC Credomatic. Aplica corresponsal ($25) y una comisión receptora alta de $35. No recomendado para montos pequeños.',
            'swift_cuscatlan' => 'SWIFT a Banco Cuscatlán. Aplica corresponsal ($25) y comisión receptora de $12.50.',
            'swift_promerica' => 'SWIFT a Banco Promerica. Aplica corresponsal ($25) y comisión receptora de $20.00.',
            'swift_industrial' => 'SWIFT a Banco Industrial. Aplica corresponsal ($25) y comisión receptora de $10.00.',
            'western_union' => 'Western Union aplica una comisión aproximada del 4% sobre el envío. Cobro rápido.',
            'paypal' => 'PayPal retiene 5.4% + $0.30 y cobra un retiro plano de $5.00 a cuenta local. Muy costoso.',
            'stripe' => 'Stripe aplica 3.9% + $0.30 de comisión y cobra un retiro plano de $5.00 a bancos locales.',
            'deel' => 'Deel cobra $5.00 fijos por recibir y $2.00 por transferir directamente a tu banco salvadoreño. Altamente eficiente para montos medianos.',
            'upwork' => 'Upwork retiene 10% por sus servicios sobre el contrato y cobra $2.00 fijos por transferir directamente a cuenta local.',
            'bitcoin' => 'Bajo costo por transferir, pero con alta volatilidad. Requiere cashout a cuenta local (~$2.00).',
            'hugo_cash' => 'Pasarela de pago local con comisión del 3% + $0.25 por procesar cobros de tarjetas extranjeras.',
            default => 'Comisiones estimadas basadas en tarifas promedio.'
        };
    }
}
