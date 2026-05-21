<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ISRCalculator;
use App\Services\IVACalculator;
use App\Services\ProfitCalculator;
use App\Services\ExpenseClassifier;

class CalculatorController extends Controller
{
    // Salud financiera — calcula todo junto
    public function salud(Request $request): JsonResponse
    {
        $d = $request->validate([
            'ventas_totales'   => 'required|numeric|min:0',
            'costos_directos'  => 'required|numeric|min:0',
            'gastos_fijos'     => 'required|numeric|min:0',
            'gastos_variables' => 'required|numeric|min:0',
            'ventas_gravadas'  => 'nullable|numeric|min:0',
            'compras_cf'       => 'nullable|numeric|min:0',
        ]);

        $utilidad = ProfitCalculator::calcularUtilidad(
            $d['ventas_totales'], $d['costos_directos'],
            $d['gastos_fijos'],   $d['gastos_variables']
        );

        $isr = ISRCalculator::calcularPersonaNatural($utilidad['utilidad_neta'] * 12);

        $iva = null;
        if (!empty($d['ventas_gravadas'])) {
            $iva = IVACalculator::calcular($d['ventas_gravadas'], $d['compras_cf'] ?? 0);
        }

        $retiro = ProfitCalculator::calcularRetiroSeguro(
            $utilidad['utilidad_neta'],
            $isr['mensual'],
            $d['gastos_fijos']
        );

        return response()->json([
            'ok' => true,
            'utilidad' => $utilidad,
            'isr'      => $isr,
            'iva'      => $iva,
            'retiro'   => $retiro,
        ]);
    }

    // Utilidad mensual
    public function utilidad(Request $request): JsonResponse
    {
        $d = $request->validate([
            'ventas'    => 'required|numeric|min:0',
            'costos'    => 'required|numeric|min:0',
            'fijos'     => 'required|numeric|min:0',
            'variables' => 'required|numeric|min:0',
        ]);

        return response()->json([
            'ok'        => true,
            'resultado' => ProfitCalculator::calcularUtilidad(
                $d['ventas'], $d['costos'], $d['fijos'], $d['variables']
            ),
        ]);
    }

    // ISR estimado
    public function isr(Request $request): JsonResponse
    {
        $d = $request->validate([
            'renta_neta_anual' => 'required|numeric|min:0',
            'tipo_persona'     => 'required|in:natural,juridica',
        ]);

        $resultado = $d['tipo_persona'] === 'natural'
            ? ISRCalculator::calcularPersonaNatural($d['renta_neta_anual'])
            : ISRCalculator::calcularPersonaJuridica($d['renta_neta_anual']);

        return response()->json(['ok' => true, 'resultado' => $resultado]);
    }

    // IVA
    public function iva(Request $request): JsonResponse
    {
        $d = $request->validate([
            'ventas_gravadas' => 'required|numeric|min:0',
            'compras_con_cf'  => 'required|numeric|min:0',
        ]);

        return response()->json([
            'ok'        => true,
            'resultado' => IVACalculator::calcular($d['ventas_gravadas'], $d['compras_con_cf']),
        ]);
    }

    // Retiro seguro
    public function retiro(Request $request): JsonResponse
    {
        $d = $request->validate([
            'utilidad_neta'     => 'required|numeric|min:0',
            'reserva_impuestos' => 'required|numeric|min:0',
            'caja_minima'       => 'required|numeric|min:0',
        ]);

        return response()->json([
            'ok'        => true,
            'resultado' => ProfitCalculator::calcularRetiroSeguro(
                $d['utilidad_neta'], $d['reserva_impuestos'], $d['caja_minima']
            ),
        ]);
    }

    // Clasificador de gastos
    public function gastos(Request $request): JsonResponse
    {
        $d = $request->validate([
            'gastos'   => 'required|array|min:1',
            'gastos.*' => 'string|max:100',
        ]);

        return response()->json([
            'ok'         => true,
            'resultados' => ExpenseClassifier::clasificar($d['gastos']),
        ]);
    }
}