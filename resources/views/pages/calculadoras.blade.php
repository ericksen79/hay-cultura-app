@extends('layouts.app')

@section('title', 'Calculadoras')
@section('meta_description', 'Calculadoras de ISR, IVA, utilidad mensual y retiro seguro para freelancers en El Salvador.')

@section('content')

<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
    <h1 class="text-2xl font-semibold text-surface-dark mb-1">Calculadoras</h1>
    <p class="text-sm text-surface-medium">
        Selecciona la que necesitas. Los resultados aparecen al instante.
    </p>
</section>

<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">

    @php
        $tabs = [
            ['id' => 'salud',    'label' => 'Salud financiera', 'icon' => 'monitoring'],
            ['id' => 'utilidad', 'label' => 'Utilidad',         'icon' => 'trending_up'],
            ['id' => 'isr',      'label' => 'ISR',              'icon' => 'receipt_long'],
            ['id' => 'iva',      'label' => 'IVA',              'icon' => 'percent'],
            ['id' => 'retiro',   'label' => 'Retiro seguro',    'icon' => 'savings'],
            ['id' => 'gastos',   'label' => 'Gastos',           'icon' => 'category'],
        ];
    @endphp

    {{-- Tabs --}}
    <div class="flex gap-2 overflow-x-auto pb-2 mb-8 scrollbar-hide" role="tablist">
        @foreach($tabs as $t)
            <button onclick="mostrarTab('{{ $t['id'] }}')"
                    id="btn-{{ $t['id'] }}"
                    role="tab"
                    class="tab-btn inline-flex items-center gap-1.5 text-xs font-medium px-4 py-2
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

{{-- Script de tabs — vanilla JS, sin dependencias --}}
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

// Mostrar salud por defecto al cargar
document.addEventListener('DOMContentLoaded', function() {
    mostrarTab('salud');
});
</script>

@endsection