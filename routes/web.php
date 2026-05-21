<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\CalculatorController;

// Páginas
Route::get('/',              [PageController::class, 'home'])->name('home');
Route::get('/calculadoras',  [PageController::class, 'calculadoras'])->name('calculadoras');

// Carga lazy de cada calculadora (fetch desde el frontend)
Route::get('/calculadoras/tab/{tab}', [PageController::class, 'tab'])->name('calculadoras.tab');

// Endpoints de cálculo (POST)
Route::post('/calcular/salud',    [CalculatorController::class, 'salud'])->name('calcular.salud');
Route::post('/calcular/utilidad', [CalculatorController::class, 'utilidad'])->name('calcular.utilidad');
Route::post('/calcular/isr',      [CalculatorController::class, 'isr'])->name('calcular.isr');
Route::post('/calcular/iva',      [CalculatorController::class, 'iva'])->name('calcular.iva');
Route::post('/calcular/retiro',   [CalculatorController::class, 'retiro'])->name('calcular.retiro');
Route::post('/calcular/gastos',   [CalculatorController::class, 'gastos'])->name('calcular.gastos');