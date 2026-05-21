<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hay Cultura App') · Calculadora Financiera SV</title>
    <meta name="description" content="@yield('meta_description', 'La mejor calculadora financiera para freelancers y micro negocios en El Salvador.')">
    <meta property="og:title" content="@yield('og_title', 'Hay Cultura App | Finanzas claras para freelancers y PYMEs')">
    <meta property="og:description" content="@yield('og_description', 'Herramientas y calculadoras financieras diseñadas para ayudar a emprendedores salvadoreños a entender utilidad, IVA, ISR y salud financiera de forma simple.')">

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20,400,0,0" />

    {{-- Vite: carga Tailwind compilado + Alpine --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-surface-white text-surface-dark font-sans antialiased min-h-screen flex flex-col">

    {{-- Skip link para accesibilidad --}}
    <a href="#main"
       class="sr-only focus:not-sr-only focus:fixed focus:top-0 focus:left-4 focus:z-50
              focus:bg-brand-primary focus:text-white focus:px-4 focus:py-2 focus:rounded-b-xl
              focus:text-sm focus:font-medium">
        Saltar al contenido
    </a>

    {{-- Navbar --}}
    <nav class="bg-white border-b border-surface-light sticky top-0 z-50"
         role="navigation" aria-label="Navegación principal">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">

                {{-- Logo --}}
                <a href="{{ route('home') }}"
                   class="flex items-center gap-2"
                   aria-label="Hay Cultura App — Inicio">
                    <div class="w-7 h-7 rounded-lg bg-brand-primary flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-xs font-semibold">HC</span>
                    </div>
                    <span class="text-brand-primary font-semibold text-[15px]">
                        Hay Cultura <span class="text-brand-secondary">App</span>
                    </span>
                </a>

                {{-- Links --}}
                <div class="hidden sm:flex items-center gap-6">
                    <a href="{{ route('home') }}"
                       class="text-sm transition-colors {{ request()->routeIs('home') ? 'text-brand-primary font-medium' : 'text-surface-medium hover:text-brand-primary' }}"
                       @if(request()->routeIs('home')) aria-current="page" @endif>
                        Inicio
                    </a>
                    <a href="{{ route('calculadoras') }}"
                       class="text-sm transition-colors {{ request()->routeIs('calculadoras') ? 'text-brand-primary font-medium' : 'text-surface-medium hover:text-brand-primary' }}"
                       @if(request()->routeIs('calculadoras')) aria-current="page" @endif>
                        Calculadoras
                    </a>
                    <a href="{{ route('referencias') }}"
                       class="text-sm transition-colors {{ request()->routeIs('referencias') ? 'text-brand-primary font-medium' : 'text-surface-medium hover:text-brand-primary' }}"
                       @if(request()->routeIs('referencias')) aria-current="page" @endif>
                        Aprende
                    </a>
                    <a href="{{ route('about') }}"
                       class="text-sm transition-colors {{ request()->routeIs('about') ? 'text-brand-primary font-medium' : 'text-surface-medium hover:text-brand-primary' }}"
                       @if(request()->routeIs('about')) aria-current="page" @endif>
                        Nosotros
                    </a>
                </div>

                <a href="{{ route('calculadoras') }}"
                   class="bg-brand-primary text-white text-sm font-medium px-4 py-2 rounded-xl
                          hover:bg-brand-secondary transition-colors">
                    Calcular ahora
                </a>
            </div>
        </div>
    </nav>

    {{-- Contenido principal --}}
    <main id="main" class="flex-1" role="main" tabindex="-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-surface-light mt-16" role="contentinfo">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-brand-primary">Hay Cultura App</p>
                    <p class="text-xs text-surface-medium mt-0.5">
                        Calculadora financiera para independientes en El Salvador 🇸🇻
                    </p>
                </div>
                <div class="text-center sm:text-right">
                    <p class="text-xs text-surface-medium leading-relaxed mb-2">
                        Basado en legislación salvadoreña vigente.<br>
                        No reemplaza la asesoría de un contador.
                    </p>
                    <div class="flex items-center justify-center sm:justify-end gap-4">
                        <a href="{{ route('referencias') }}" class="text-xs text-brand-primary hover:underline">Referencias Legales</a>
                        <a href="{{ route('about') }}" class="text-xs text-brand-primary hover:underline">Sobre Nosotros</a>
                    </div>
                </div>
            </div>
            <div class="border-t border-surface-light mt-6 pt-4 text-center">
                <p class="text-xs text-surface-medium">
                    © {{ date('Y') }} Hay Cultura App · Tus datos son privados · Hecho en El Salvador
                </p>
            </div>
        </div>
    </footer>

    {{-- Estado global entre calculadoras --}}
    <script>
        window.hcState = {
            utilidad_neta: null,
            isr_mensual:   null,
            iva_pagar:     null,
            gastos_fijos:  null,
        };
        window.hcGuardar    = (d) => Object.assign(window.hcState, d);
        window.hcLeer       = (k) => window.hcState[k] ?? null;
        window.hcLimpiarTodo = () => Object.keys(window.hcState).forEach(k => window.hcState[k] = null);
    </script>

</body>
</html>