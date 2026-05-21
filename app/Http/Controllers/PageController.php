<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home');
    }

    public function calculadoras()
    {
        return view('pages.calculadoras');
    }

    // Carga lazy — devuelve solo el HTML del componente sin layout
    public function tab(string $tab)
    {
        $validos = ['salud', 'utilidad', 'isr', 'iva', 'retiro', 'gastos'];

        abort_unless(in_array($tab, $validos), 404);

        return view('components.calc-' . $tab);
    }
}