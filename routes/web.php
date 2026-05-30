<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\FreelancerController;

// Páginas
Route::get('/',              [PageController::class, 'home'])->name('home');
Route::get('/calculadoras',  [PageController::class, 'calculadoras'])->name('calculadoras');
Route::get('/freelancer',    [PageController::class, 'freelancer'])->name('freelancer.index');
Route::get('/sobre-nosotros',[PageController::class, 'about'])->name('about');
Route::get('/referencias',   [PageController::class, 'referencias'])->name('referencias');

// Carga lazy de cada calculadora (fetch desde el frontend)
Route::get('/calculadoras/tab/{tab}', [PageController::class, 'tab'])->name('calculadoras.tab');

// Endpoints de cálculo (POST)
Route::post('/calcular/salud',    [CalculatorController::class, 'salud'])->name('calcular.salud');
Route::post('/calcular/utilidad', [CalculatorController::class, 'utilidad'])->name('calcular.utilidad');
Route::post('/calcular/isr',      [CalculatorController::class, 'isr'])->name('calcular.isr');
Route::post('/calcular/iva',      [CalculatorController::class, 'iva'])->name('calcular.iva');
Route::post('/calcular/retiro',   [CalculatorController::class, 'retiro'])->name('calcular.retiro');
Route::post('/calcular/gastos',   [CalculatorController::class, 'gastos'])->name('calcular.gastos');

// Endpoints de cálculo de freelancer (POST)
Route::post('/calcular/freelancer/salud',    [FreelancerController::class, 'calcularSalud'])->name('calcular.freelancer.salud');
Route::post('/calcular/freelancer/comparar', [FreelancerController::class, 'comparar'])->name('calcular.freelancer.comparar');