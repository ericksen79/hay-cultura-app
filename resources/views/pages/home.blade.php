@extends('layouts.app')

@section('title', 'Inicio')
@section('meta_description', 'La mejor calculadora financiera para freelancers, autónomos y micro negocios en El Salvador.')

@section('content')

{{-- Hero --}}
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-10 text-center">
    <span class="inline-flex items-center gap-1.5 bg-brand-primary/10 text-brand-primary
                 text-xs font-medium px-3 py-1 rounded-full mb-5">
        <span class="icon icon-sm">storefront</span>
        Para freelancers · Autónomos · Micro negocios
    </span>

    <h1 class="text-3xl sm:text-4xl font-semibold text-surface-dark leading-tight max-w-2xl mx-auto mb-4">
        Entiende tus finanzas e impuestos en minutos
    </h1>

    <p class="text-surface-medium text-base max-w-xl mx-auto mb-8 leading-relaxed">
        Calculadoras simples basadas en la legislación salvadoreña.
        Sin lenguaje contable complicado. Gratis.
    </p>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="{{ route('calculadoras') }}"
           class="inline-flex items-center gap-2 bg-brand-primary text-white font-medium
                  px-6 py-3 rounded-xl hover:bg-brand-secondary transition-colors text-sm
                  w-full sm:w-auto justify-center">
            <span class="icon icon-sm">calculate</span>
            Comenzar gratis
        </a>
        <a href="#calculadoras"
           class="inline-flex items-center gap-2 border border-brand-primary/30 text-brand-primary
                  font-medium px-6 py-3 rounded-xl hover:bg-brand-primary/5 transition-colors
                  text-sm w-full sm:w-auto justify-center">
            <span class="icon icon-sm">arrow_downward</span>
            Ver calculadoras
        </a>
    </div>
</section>

{{-- Bento Grid --}}
<section id="calculadoras" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">

    <p class="text-xs font-medium text-brand-muted uppercase tracking-widest mb-5">
        Calculadoras disponibles
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        {{-- Card Freelancer Internacional - Premium Highlight --}}
        <a href="{{ route('freelancer.index') }}"
           class="lg:col-span-3 bg-gradient-to-br from-brand-primary/5 to-brand-secondary/5 rounded-2xl p-6 card-shadow border border-brand-primary/20
                  hover:border-brand-primary transition-all group relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-brand-primary/10 rounded-full blur-2xl"></div>
            <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
                <div class="flex-1">
                    <span class="inline-flex items-center gap-1 bg-brand-primary/15 text-brand-primary text-[10px] font-bold px-2.5 py-0.5 rounded-full mb-3 uppercase tracking-wider">
                        🌎 Nuevo · Especial Autónomos
                    </span>
                    <h2 class="text-lg font-bold text-surface-dark mb-2 group-hover:text-brand-primary transition-colors">
                        Freelancer Internacional SV
                    </h2>
                    <p class="text-xs text-surface-medium leading-relaxed mb-4 max-w-3xl">
                        ¿Trabajas para clientes en el extranjero y cobras por Wise, Payoneer, SWIFT, PayPal o Stripe? Calcula tu eficiencia de cobro, tus comisiones reales en El Salvador, tu reserva óptima de impuestos (ISR) y cotizaciones médicas independientes.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['Flujo de Dinero', 'Comisiones reales', 'Comparador de Plataformas', 'Checklist de Formalización'] as $tag)
                            <span class="text-[10px] font-medium bg-brand-primary/10 text-brand-primary px-2.5 py-1 rounded-lg">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-brand-primary/15 flex items-center justify-center flex-shrink-0">
                    <span class="icon icon-xl text-brand-primary">public</span>
                </div>
            </div>
        </a>

        {{-- Card grande --}}
        <a href="{{ route('calculadoras') }}"
           class="lg:col-span-2 bg-white rounded-2xl p-6 card-shadow border border-surface-light
                  hover:border-brand-light transition-all group">
            <div class="w-10 h-10 rounded-xl bg-brand-primary/10 flex items-center justify-center mb-4">
                <span class="icon icon-lg text-brand-primary">monitoring</span>
            </div>
            <h2 class="text-base font-semibold text-surface-dark mb-2
                       group-hover:text-brand-primary transition-colors">
                Salud financiera del negocio
            </h2>
            <p class="text-sm text-surface-medium leading-relaxed mb-4">
                Ingresa tus ventas y gastos del mes. Obtén tu utilidad real,
                impuestos estimados y cuánto puedes retirar con seguridad.
            </p>
            <div class="flex flex-wrap gap-2">
                @foreach(['Utilidad neta', 'ISR estimado', 'IVA', 'Retiro seguro'] as $tag)
                    <span class="text-xs bg-brand-primary/10 text-brand-primary px-2 py-1 rounded-lg">
                        {{ $tag }}
                    </span>
                @endforeach
            </div>
        </a>

        {{-- ISR --}}
        <a href="{{ route('calculadoras') }}"
           class="bg-white rounded-2xl p-5 card-shadow border border-surface-light
                  hover:border-brand-light transition-all group">
            <div class="w-9 h-9 rounded-xl bg-[#E6F1FB] flex items-center justify-center mb-3">
                <span class="icon text-[#3A6E8A]">receipt_long</span>
            </div>
            <h3 class="text-sm font-semibold text-surface-dark mb-1
                       group-hover:text-brand-primary transition-colors">ISR estimado</h3>
            <p class="text-xs text-surface-medium leading-relaxed">
                Calcula tu impuesto sobre la renta según los tramos del Art. 37 LISR.
            </p>
        </a>

        {{-- IVA --}}
        <a href="{{ route('calculadoras') }}"
           class="bg-white rounded-2xl p-5 card-shadow border border-surface-light
                  hover:border-brand-secondary transition-all group">
            <div class="w-9 h-9 rounded-xl bg-[#E1F5EE] flex items-center justify-center mb-3">
                <span class="icon text-[#377B7B]">percent</span>
            </div>
            <h3 class="text-sm font-semibold text-surface-dark mb-1
                       group-hover:text-brand-secondary transition-colors">Calculadora IVA</h3>
            <p class="text-xs text-surface-medium leading-relaxed">
                Débito fiscal, crédito fiscal y saldo a pagar. Tasa del 13% vigente.
            </p>
        </a>

        {{-- Utilidad --}}
        <a href="{{ route('calculadoras') }}"
           class="bg-white rounded-2xl p-5 card-shadow border border-surface-light
                  hover:border-brand-light transition-all group">
            <div class="w-9 h-9 rounded-xl bg-[#FAEEDA] flex items-center justify-center mb-3">
                <span class="icon text-[#854F0B]">trending_up</span>
            </div>
            <h3 class="text-sm font-semibold text-surface-dark mb-1
                       group-hover:text-brand-primary transition-colors">Utilidad mensual</h3>
            <p class="text-xs text-surface-medium leading-relaxed">
                Ventas menos costos y gastos. Estado de resultados en segundos.
            </p>
        </a>

        {{-- Retiro --}}
        <a href="{{ route('calculadoras') }}"
           class="bg-white rounded-2xl p-5 card-shadow border border-surface-light
                  hover:border-brand-light transition-all group">
            <div class="w-9 h-9 rounded-xl bg-[#FBEAF0] flex items-center justify-center mb-3">
                <span class="icon text-[#993556]">savings</span>
            </div>
            <h3 class="text-sm font-semibold text-surface-dark mb-1
                       group-hover:text-brand-primary transition-colors">Retiro seguro</h3>
            <p class="text-xs text-surface-medium leading-relaxed">
                ¿Cuánto puedes sacar sin descapitalizar tu negocio?
            </p>
        </a>

        {{-- Clasificador --}}
        <a href="{{ route('calculadoras') }}"
           class="bg-white rounded-2xl p-5 card-shadow border border-surface-light
                  hover:border-brand-secondary transition-all group">
            <div class="w-9 h-9 rounded-xl bg-[#E1F5EE] flex items-center justify-center mb-3">
                <span class="icon text-[#377B7B]">category</span>
            </div>
            <h3 class="text-sm font-semibold text-surface-dark mb-1
                       group-hover:text-brand-secondary transition-colors">Clasificador de gastos</h3>
            <p class="text-xs text-surface-medium leading-relaxed">
                ¿Ese gasto es deducible? ¿Es un activo? Descúbrelo al instante.
            </p>
        </a>

    </div>
</section>

{{-- Sección de confianza --}}
<section class="bg-white border-y border-surface-light">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">

            @php
                $puntos = [
                    ['icon' => 'gavel',      'titulo' => 'Legislación salvadoreña', 'texto' => 'Basado en LISR, Ley del IVA y tablas oficiales del Ministerio de Hacienda.'],
                    ['icon' => 'lock',       'titulo' => 'Tus datos son privados',  'texto' => 'No guardamos información. Todo se calcula en el momento sin almacenarse.'],
                    ['icon' => 'person',     'titulo' => 'No reemplaza un contador','texto' => 'Es una guía orientativa. Para declaraciones formales consulta a un profesional.'],
                ];
            @endphp

            @foreach($puntos as $p)
                <div class="flex flex-col items-center text-center">
                    <div class="w-10 h-10 rounded-xl bg-brand-primary/10 flex items-center justify-center mb-3">
                        <span class="icon icon-md text-brand-primary">{{ $p['icon'] }}</span>
                    </div>
                    <h3 class="text-sm font-semibold text-surface-dark mb-2">{{ $p['titulo'] }}</h3>
                    <p class="text-xs text-surface-medium leading-relaxed">{{ $p['texto'] }}</p>
                </div>
            @endforeach

        </div>
    </div>
</section>

@endsection