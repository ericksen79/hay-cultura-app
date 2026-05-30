@extends('layouts.app')

@section('title', 'Freelancer Internacional SV')
@section('meta_description', 'Calculadora de salud financiera, comparador de comisiones de cobro y checklist de formalización para freelancers salvadoreños.')

@section('content')

<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
    <div class="flex items-center gap-2.5 mb-2">
        <span class="inline-flex items-center gap-1.5 bg-brand-primary/10 text-brand-primary text-xs font-semibold px-3 py-1 rounded-full">
            <span class="icon icon-sm">public</span>
            Freelancer Internacional SV
        </span>
    </div>
    <h1 class="text-2xl font-bold text-surface-dark mb-1">Freelancer Internacional SV</h1>
    <p class="text-sm text-surface-medium">
        Herramientas y guías financieras optimizadas para trabajadores remotos, desarrolladores, diseñadores y prestadores de servicios profesionales en El Salvador que cobran desde el exterior.
    </p>
</section>

<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">

    @php
        $tabs = [
            ['id' => 'freelancer',    'label' => 'Salud Financiera Freelancer', 'icon' => 'monitoring'],
            ['id' => 'comparator',    'label' => 'Comparador de Plataformas',  'icon' => 'compare_arrows'],
            ['id' => 'formalizacion', 'label' => 'Checklist de Formalización',  'icon' => 'fact_check'],
        ];
    @endphp

    {{-- Tabs --}}
    <div class="flex gap-2 overflow-x-auto pb-2 mb-8 scrollbar-hide" role="tablist">
        @foreach($tabs as $t)
            <button onclick="mostrarTab('{{ $t['id'] }}')"
                    id="btn-{{ $t['id'] }}"
                    role="tab"
                    class="tab-btn inline-flex items-center gap-1.5 text-xs font-medium px-4 py-2.5
                           rounded-xl whitespace-nowrap transition-all duration-150 border flex-shrink-0
                           bg-white text-surface-medium border-surface-light hover:border-brand-light">
                <span class="icon icon-sm">{{ $t['icon'] }}</span>
                {{ $t['label'] }}
            </button>
        @endforeach
    </div>

    {{-- Paneles --}}
    @foreach($tabs as $t)
        <div id="panel-{{ $t['id'] }}"
             class="tab-panel"
             role="tabpanel"
             style="display:none;">
            @include('components.calc-' . $t['id'])
        </div>
    @endforeach

</section>

{{-- Script de tabs para Freelancer --}}
<script>
function mostrarTab(id) {
    // Ocultar todos los paneles
    document.querySelectorAll('.tab-panel').forEach(function(p) {
        p.style.display = 'none';
    });

    // Resetear todos los botones
    document.querySelectorAll('.tab-btn').forEach(function(b) {
        b.className = b.className
            .replace('bg-brand-primary', 'bg-white')
            .replace('text-white', 'text-surface-medium')
            .replace('border-brand-primary', 'border-surface-light');
    });

    // Mostrar el panel activo
    var panel = document.getElementById('panel-' + id);
    if (panel) panel.style.display = 'block';

    // Activar el botón correspondiente
    var btn = document.getElementById('btn-' + id);
    if (btn) {
        btn.className = btn.className
            .replace('bg-white', 'bg-brand-primary')
            .replace('text-surface-medium', 'text-white')
            .replace('border-surface-light', 'border-brand-primary');
    }

    // Notificar a los componentes Alpine que la pestaña cambió
    window.dispatchEvent(new CustomEvent('tab-cambiado', { detail: id }));
}

// Mostrar 'freelancer' por defecto al cargar
document.addEventListener('DOMContentLoaded', function() {
    // Si hay un hash en la URL (ej. #comparator), ir a esa pestaña
    var hash = window.location.hash.replace('#', '');
    if (hash === 'comparator' || hash === 'formalizacion') {
        mostrarTab(hash);
    } else {
        mostrarTab('freelancer');
    }
});
</script>

@endsection
