<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\FreelancerCalculator;

class FreelancerCalculatorTest extends TestCase
{
    /**
     * Test the Wise payment scenario
     */
    public function test_wise_scenario(): void
    {
        $input = [
            'monto_facturado' => 2000.00,
            'metodo_cobro' => 'wise',
            'banco_receptor' => 'agricola',
            'aplicar_iva_comision' => false,
            'gastos_deducibles' => [],
            'inscrito_hacienda' => false,
            'cotiza_isss' => 'no',
            'fondo_emergencia_pct' => 0
        ];

        $res = FreelancerCalculator::calcularSalud($input);

        $this->assertEquals(20.00, $res['comisiones']['plataforma']); // 1%
        $this->assertEquals(3.00, $res['comisiones']['banco']); // Flat withdrawal fee
        $this->assertEquals(23.00, $res['comisiones']['total']);
        $this->assertEquals(1977.00, $res['neto_recibido']);
        $this->assertEquals(98.85, $res['eficiencia']['porcentaje']);
        $this->assertEquals('excelente', $res['eficiencia']['rango']);
    }

    /**
     * Test the Payoneer payment scenario
     */
    public function test_payoneer_scenario(): void
    {
        $input = [
            'monto_facturado' => 2000.00,
            'metodo_cobro' => 'payoneer',
            'banco_receptor' => 'agricola',
            'aplicar_iva_comision' => false,
            'gastos_deducibles' => [],
            'inscrito_hacienda' => false,
            'cotiza_isss' => 'no',
            'fondo_emergencia_pct' => 0
        ];

        $res = FreelancerCalculator::calcularSalud($input);

        // Platform fee: 2000 * 3.99% + 0.49 = 79.80 + 0.49 = $80.29
        $this->assertEquals(80.29, $res['comisiones']['plataforma']);
        // Bank fee: (2000 - 80.29) * 2% = 1919.71 * 0.02 = $38.39
        $this->assertEquals(38.39, $res['comisiones']['banco']);
        $this->assertEquals(118.68, $res['comisiones']['total']);
        $this->assertEquals(1881.32, $res['neto_recibido']);
        $this->assertEquals(94.07, $res['eficiencia']['porcentaje']);
        $this->assertEquals('bueno', $res['eficiencia']['rango']);
    }

    /**
     * Test direct SWIFT scenario with low and high tier receiver fees for Banco Agrícola
     */
    public function test_swift_scenario(): void
    {
        // Tier 1: <= $3000 -> $5.65 fee
        $inputBajo = [
            'monto_facturado' => 2000.00,
            'metodo_cobro' => 'swift',
            'banco_receptor' => 'agricola',
            'aplicar_iva_comision' => false,
            'gastos_deducibles' => [],
            'inscrito_hacienda' => false,
            'cotiza_isss' => 'no',
            'fondo_emergencia_pct' => 0
        ];

        $resBajo = FreelancerCalculator::calcularSalud($inputBajo);
        $this->assertEquals(25.00, $resBajo['comisiones']['corresponsal']);
        $this->assertEquals(5.65, $resBajo['comisiones']['banco']);
        $this->assertEquals(30.65, $resBajo['comisiones']['total']);
        $this->assertEquals(1969.35, $resBajo['neto_recibido']);

        // Tier 2: > $3000 -> $11.30 fee
        $inputAlto = $inputBajo;
        $inputAlto['monto_facturado'] = 4000.00;

        $resAlto = FreelancerCalculator::calcularSalud($inputAlto);
        $this->assertEquals(25.00, $resAlto['comisiones']['corresponsal']);
        $this->assertEquals(11.30, $resAlto['comisiones']['banco']);
        $this->assertEquals(36.30, $resAlto['comisiones']['total']);
        $this->assertEquals(3963.70, $resAlto['neto_recibido']);
    }

    /**
     * Test full health and deductions calculation (Gastos, ISR, ISSS, Emergencia)
     */
    public function test_full_health_deductions(): void
    {
        $input = [
            'monto_facturado' => 2000.00,
            'metodo_cobro' => 'wise',
            'banco_receptor' => 'agricola',
            'aplicar_iva_comision' => false,
            'gastos_deducibles' => [
                ['concepto' => 'Adobe CC', 'monto' => 65.00],
                ['concepto' => 'Internet', 'monto' => 30.00],
                ['concepto' => 'ChatGPT Plus', 'monto' => 20.00],
                ['concepto' => 'Hosting', 'monto' => 15.00],
            ],
            'inscrito_hacienda' => true, // Activa reserva de ISR
            'cotiza_isss' => 'individual', // ISSS = $40.00
            'fondo_emergencia_pct' => 10 // 10% de utilidad operativa
        ];

        $res = FreelancerCalculator::calcularSalud($input);

        // Neto recibido: $1977.00
        // Total gastos: 65 + 30 + 20 + 15 = $130.00
        // Utilidad operativa: 1977 - 130 = $1847.00
        $this->assertEquals(130.00, $res['gastos_operativos']['total']);
        $this->assertEquals(1847.00, $res['utilidad_operativa']);

        // Renta anual proyectada: 1847 * 12 = $22164.00
        // Tramo III LISR: $22,164.00
        // Exceso sobre $9,142.86 = 22164.00 - 9142.86 = 13021.14
        // ISR anual = 13021.14 * 20% + 720.00 = 2604.228 + 720.00 = 3324.23
        // ISR mensual = 3324.23 / 12 = $277.02
        $this->assertEquals(3324.23, $res['isr']['anual_estimado']);
        $this->assertEquals(277.02, $res['isr']['mensual_sugerido']);
        $this->assertEquals(277.02, $res['isr']['aplicado']);

        // ISSS: $40.00
        $this->assertEquals(40.00, $res['isss']['monto']);

        // Fondo de emergencia: 10% de 1847 = $184.70
        $this->assertEquals(184.70, $res['fondo_emergencia']['monto']);

        // Dinero disponible: 1847.00 - 277.02 - 40.00 - 184.70 = $1345.28
        $this->assertEquals(1345.28, $res['dinero_disponible']);

        // Salud financiera: Excelente (con ISR activo + ISSS + Fondo > 0)
        $this->assertEquals('excelente', $res['salud_financiera']['estado']);
    }

    /**
     * Test platform comparison list returning multiple options ordered by net received
     */
    public function test_platform_comparison(): void
    {
        $monto = 2000.00;
        $comparador = FreelancerCalculator::compararPlataformas($monto);

        $this->assertCount(14, $comparador);

        // First platform should be Deel at $2000 (flat fee is cheaper)
        $this->assertEquals('deel', $comparador[0]['id']);
        $this->assertEquals(1993.00, $comparador[0]['neto']);

        // Second platform should be Bitcoin
        $this->assertEquals('bitcoin', $comparador[1]['id']);
        $this->assertEquals(1978.00, $comparador[1]['neto']);

        // Third platform should be Wise
        $this->assertEquals('wise', $comparador[2]['id']);
        $this->assertEquals(1977.00, $comparador[2]['neto']);

        // Verify that Deel and others exist in the list
        $ids = array_column($comparador, 'id');
        $this->assertContains('payoneer', $ids);
        $this->assertContains('swift_agricola', $ids);
        $this->assertContains('swift_bac', $ids);
        $this->assertContains('paypal', $ids);
        $this->assertContains('stripe', $ids);
        $this->assertContains('bitcoin', $ids);
    }
}
