<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ExpenseClassifier;

class ExpenseClassifierTest extends TestCase
{
    /**
     * Test classification of deductibles, non-deductibles, and active assets.
     */
    public function test_expense_classification_and_pricing(): void
    {
        $gastos = [
            ['nombre' => 'Licencia de Figma', 'precio' => 15.00],
            ['nombre' => 'Almuerzo personal', 'precio' => 12.50],
            ['nombre' => 'Laptop Dell', 'precio' => 1200.00],
            ['nombre' => 'Insumo desconocido', 'precio' => 45.00],
        ];

        $resultados = ExpenseClassifier::clasificar($gastos);

        $this->assertCount(4, $resultados);

        // Figma should be deductible
        $figma = $resultados[0];
        $this->assertEquals('deducible', $figma['tipo']);
        $this->assertEquals(15.00, $figma['precio']);
        $this->assertEquals('Art. 29 LISR', $figma['base_legal']);

        // Personal lunch should be non-deductible
        $almuerzo = $resultados[1];
        $this->assertEquals('no_deducible', $almuerzo['tipo']);
        $this->assertEquals(12.50, $almuerzo['precio']);
        $this->assertEquals('Art. 29-A LISR', $almuerzo['base_legal']);

        // Laptop should be an active asset with 50% depreciation rate
        $laptop = $resultados[2];
        $this->assertEquals('activo', $laptop['tipo']);
        $this->assertEquals(1200.00, $laptop['precio']);
        $this->assertEquals(50.0, $laptop['porcentaje_depreciacion']);
        $this->assertEquals(600.00, $laptop['depreciacion_anual']);
        $this->assertEquals(50.00, $laptop['depreciacion_mensual']);
        $this->assertEquals('Art. 30 LISR', $laptop['base_legal']);

        // Unknown should be 'revisar'
        $unknown = $resultados[3];
        $this->assertEquals('revisar', $unknown['tipo']);
        $this->assertEquals(45.00, $unknown['precio']);
    }

    /**
     * Test specific Salvadoran depreciation rates under Art. 30 LISR.
     */
    public function test_depreciation_rates_by_category(): void
    {
        // 50% for computers/software
        $this->assertEquals(50.0, ExpenseClassifier::obtenerPorcentajeDepreciacion('Computadora de oficina'));
        $this->assertEquals(50.0, ExpenseClassifier::obtenerPorcentajeDepreciacion('Licencia de software'));

        // 25% for vehicles
        $this->assertEquals(25.0, ExpenseClassifier::obtenerPorcentajeDepreciacion('Furgoneta de reparto'));
        $this->assertEquals(25.0, ExpenseClassifier::obtenerPorcentajeDepreciacion('Motocicleta repartidor'));

        // 5% for buildings
        $this->assertEquals(5.0, ExpenseClassifier::obtenerPorcentajeDepreciacion('Local comercial centro'));
        $this->assertEquals(5.0, ExpenseClassifier::obtenerPorcentajeDepreciacion('Bodega negocio'));

        // 20% for other tools, machinery and furniture (default)
        $this->assertEquals(20.0, ExpenseClassifier::obtenerPorcentajeDepreciacion('Silla ergonómica'));
        $this->assertEquals(20.0, ExpenseClassifier::obtenerPorcentajeDepreciacion('Horno industrial de pan'));
    }
}
