<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\FreelancerCalculator;

class FreelancerController extends Controller
{
    /**
     * Calcula el flujo de dinero, comisiones y salud financiera del freelancer.
     */
    public function calcularSalud(Request $request): JsonResponse
    {
        $d = $request->validate([
            'monto_facturado'        => 'required|numeric|min:0',
            'metodo_cobro'           => 'required|string|max:50',
            'banco_receptor'         => 'required|string|max:50',
            'fee_porcentual_manual'  => 'nullable|numeric|min:0|max:100',
            'fee_fijo_manual'        => 'nullable|numeric|min:0',
            'aplicar_iva_comision'   => 'nullable|boolean',
            'inscrito_hacienda'      => 'nullable|boolean',
            'cotiza_isss'            => 'required|string|in:no,individual,familiar',
            'fondo_emergencia_pct'   => 'required|numeric|min:0|max:100',
            'gastos_deducibles'      => 'nullable|array',
            'gastos_deducibles.*.concepto' => 'nullable|string|max:100',
            'gastos_deducibles.*.monto'    => 'nullable|numeric|min:0',
        ]);

        $resultado = FreelancerCalculator::calcularSalud($d);

        return response()->json([
            'ok'        => true,
            'resultado' => $resultado
        ]);
    }

    /**
     * Compara el neto recibido para un monto en todas las plataformas de cobro internacional.
     */
    public function comparar(Request $request): JsonResponse
    {
        $d = $request->validate([
            'monto' => 'required|numeric|min:0'
        ]);

        $resultados = FreelancerCalculator::compararPlataformas((float)$d['monto']);

        return response()->json([
            'ok'         => true,
            'resultados' => $resultados
        ]);
    }
}
