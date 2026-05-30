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

    public function about()
    {
        return view('pages.about');
    }

    public function referencias()
    {
        return view('pages.referencias');
    }

    public function freelancer()
    {
        return view('pages.freelancer');
    }

    // Carga lazy — devuelve solo el HTML del componente sin layout
    public function tab(string $tab)
    {
        $validos = ['salud', 'utilidad', 'isr', 'iva', 'retiro', 'gastos'];

        abort_unless(in_array($tab, $validos), 404);

        return view('components.calc-' . $tab);
    }
}