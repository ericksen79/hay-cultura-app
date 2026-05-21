<div x-data="{
    cargando: false, mostrar: false, reset: false, error: null,
    utilidad: null, isr: null, iva: null, retiro: null, tieneIva: false,

    async calcular() {
        this.cargando = true; this.error = null;
        const v  = document.getElementById('sf-ventas').value;
        const c  = document.getElementById('sf-costos').value;
        const f  = document.getElementById('sf-fijos').value;
        const va = document.getElementById('sf-variables').value;
        if (!v || !c || !f || !va) {
            this.error = 'Completa ventas, costos y gastos para ver el panorama completo.';
            this.cargando = false; return;
        }
        try {
            const res = await fetch('{{ route('calcular.salud') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({
                    ventas_totales: v, costos_directos: c,
                    gastos_fijos: f,   gastos_variables: va,
                    ventas_gravadas: this.tieneIva ? (document.getElementById('sf-gravadas')?.value || 0) : 0,
                    compras_cf:      this.tieneIva ? (document.getElementById('sf-cf')?.value || 0) : 0,
                })
            });
            const j = await res.json();
            if (j.ok) {
                this.utilidad = j.utilidad; this.isr = j.isr;
                this.iva = j.iva; this.retiro = j.retiro;
                this.mostrar = true;
                window.hcGuardar({ utilidad_neta: j.utilidad.utilidad_neta, isr_mensual: j.isr.mensual, gastos_fijos: j.utilidad.gastos_fijos });
            } else { this.error = 'Error en el cálculo. Verifica los valores.'; }
        } catch { this.error = 'No se pudo conectar. Revisa tu conexión.'; }
        finally { this.cargando = false; }
    },

    limpiar() {
        if (!this.reset) { this.reset = true; setTimeout(() => this.reset = false, 3000); return; }
        ['sf-ventas','sf-costos','sf-fijos','sf-variables','sf-gravadas','sf-cf'].forEach(id => {
            const el = document.getElementById(id); if (el) el.value = '';
        });
        this.utilidad = null; this.isr = null; this.iva = null; this.retiro = null;
        this.mostrar = false; this.error = null; this.reset = false; this.tieneIva = false;
        window.hcLimpiarTodo();
    }
}">

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- ===== FORMULARIO (2/5) ===== --}}
    <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-surface-light card-shadow">

        <div class="flex items-start justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="icon icon-lg text-brand-primary">monitoring</span>
                <div>
                    <h2 class="text-base font-semibold text-surface-dark">Salud financiera</h2>
                    <p class="text-xs text-surface-medium">El panorama completo de tu mes</p>
                </div>
            </div>
            <button type="button" @click="limpiar()"
                    class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg border transition-colors flex-shrink-0 ml-2"
                    :class="reset ? 'border-red-300 text-red-500 bg-red-50' : 'border-surface-light text-surface-medium hover:border-brand-light hover:text-brand-primary'">
                <span class="icon icon-sm">restart_alt</span>
                <span x-text="reset ? '¿Confirmar?' : 'Limpiar'"></span>
            </button>
        </div>

        {{-- Mini flujo visual --}}
        <div class="bg-surface-light/40 rounded-xl p-3 mb-5 mt-2">
            <p class="text-[10px] text-surface-medium font-medium uppercase tracking-wider mb-2">Esta calculadora analiza</p>
            <div class="space-y-1">
                @php
                    $pasos = [
                        ['icon' => 'payments',         'texto' => 'Cuánto ganaste realmente'],
                        ['icon' => 'receipt_long',     'texto' => 'Cuánto guardar para el ISR'],
                        ['icon' => 'percent',          'texto' => 'Tu IVA del período (opcional)'],
                        ['icon' => 'savings',          'texto' => 'Cuánto puedes retirar con seguridad'],
                    ];
                @endphp
                @foreach($pasos as $i => $p)
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full bg-brand-primary/15 flex items-center justify-center flex-shrink-0">
                            <span class="text-[9px] font-medium text-brand-primary">{{ $i + 1 }}</span>
                        </div>
                        <span class="icon icon-sm text-brand-primary">{{ $p['icon'] }}</span>
                        <p class="text-xs text-surface-medium">{{ $p['texto'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <p class="text-[10px] font-medium text-surface-medium/60 uppercase tracking-wider mb-3">Ingresos</p>

        @include('components.input-field', [
            'id'      => 'sf-ventas',
            'label'   => 'Total que cobraste este mes',
            'prefix'  => '$',
            'hint'    => 'Sin incluir el 13% de IVA',
            'helper'  => 'Ej: si facturaste $1,130 con IVA, escribe $1,000',
        ])

        <p class="text-[10px] font-medium text-surface-medium/60 uppercase tracking-wider mb-3 mt-2">Costos y gastos</p>

        @include('components.input-field', [
            'id'      => 'sf-costos',
            'label'   => 'Costo de lo que vendiste',
            'prefix'  => '$',
            'hint'    => 'Lo que pagaste para producir o entregar',
            'helper'  => 'Freelancer: licencias, hosting. Pastelería: ingredientes, materiales.',
        ])

        @include('components.input-field', [
            'id'      => 'sf-fijos',
            'label'   => 'Gastos fijos',
            'prefix'  => '$',
            'hint'    => 'Los que pagas siempre, vendas o no vendas',
            'helper'  => 'Alquiler, internet, planilla, suscripciones.',
        ])

        @include('components.input-field', [
            'id'      => 'sf-variables',
            'label'   => 'Gastos variables',
            'prefix'  => '$',
            'hint'    => 'Los que cambian según cuánto trabajaste',
            'helper'  => 'Gasolina, envíos, materiales extra.',
        ])

        {{-- Toggle IVA --}}
        <label class="flex items-center gap-3 cursor-pointer mt-1 mb-4">
            <div class="relative flex-shrink-0">
                <input type="checkbox" x-model="tieneIva" class="sr-only">
                <div class="w-9 h-5 rounded-full transition-colors" :class="tieneIva ? 'bg-brand-primary' : 'bg-surface-light'"></div>
                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform" :class="tieneIva ? 'translate-x-4' : 'translate-x-0'"></div>
            </div>
            <div>
                <span class="text-xs font-medium text-surface-dark">Calcular mi IVA también</span>
                <p class="text-[10px] text-surface-medium">Actívalo si emites facturas con crédito fiscal</p>
            </div>
        </label>

        <div x-show="tieneIva" x-transition>
            <p class="text-[10px] font-medium text-surface-medium/60 uppercase tracking-wider mb-3">IVA del período</p>
            @include('components.input-field', [
                'id'      => 'sf-gravadas',
                'label'   => 'Ventas sujetas al 13% de IVA',
                'prefix'  => '$',
                'hint'    => 'Sin IVA incluido',
                'helper'  => 'Si tienes el total con IVA, divídelo entre 1.13.',
            ])
            @include('components.input-field', [
                'id'      => 'sf-cf',
                'label'   => 'Compras con comprobante de CF',
                'prefix'  => '$',
                'hint'    => 'Solo las que tienen CCF de tu proveedor',
                'tooltip' => 'El crédito fiscal solo aplica si tu proveedor te emitió un Comprobante de Crédito Fiscal (CCF). Las facturas normales de consumidor no generan crédito.',
            ])
        </div>

        <div role="alert" x-show="error" class="mb-3">
            <p class="text-xs text-red-500 flex items-center gap-1">
                <span class="icon icon-sm">error</span><span x-text="error"></span>
            </p>
        </div>

        <button type="button" @click="calcular()" :disabled="cargando"
                class="w-full inline-flex items-center justify-center gap-2 bg-brand-primary text-white
                       text-sm font-medium py-3 rounded-xl hover:bg-brand-secondary transition-colors
                       disabled:opacity-50 disabled:cursor-not-allowed">
            <span class="icon icon-sm" x-show="!cargando">play_arrow</span>
            <span class="icon icon-sm animate-spin" x-show="cargando">progress_activity</span>
            <span x-text="cargando ? 'Analizando tu negocio...' : 'Ver salud financiera'"></span>
        </button>
    </div>

    {{-- ===== RESULTADOS (3/5) ===== --}}
    <div class="lg:col-span-3 space-y-4" role="region" aria-live="polite">

        <div x-show="!mostrar"
             class="bg-surface-light/30 rounded-2xl p-8 border border-dashed border-surface-light
                    flex flex-col items-center justify-center gap-3 min-h-64 text-center">
            <span class="icon icon-xl text-surface-light">monitoring</span>
            <p class="text-sm text-surface-medium max-w-xs">
                Completa los datos de tu negocio y verás aquí el diagnóstico completo del mes.
            </p>
        </div>

        <template x-if="mostrar && utilidad">
            <div class="fade-in space-y-4">

                {{-- Estado general con lenguaje simple --}}
                <div :class="{
                        'bg-[#E1F5EE] border-[#9DBFBF]': utilidad.salud.color === 'green',
                        'bg-[#E6F1FB] border-[#7CC0E4]': utilidad.salud.color === 'blue',
                        'bg-[#FAEEDA] border-[#F0C97A]': utilidad.salud.color === 'amber',
                        'bg-[#FCEBEB] border-[#E8A8A8]': utilidad.salud.color === 'red',
                     }"
                     class="rounded-2xl p-5 border">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-medium text-surface-medium mb-1">Estado de tu negocio este mes</p>
                            <p class="text-xl font-semibold text-surface-dark" x-text="utilidad.salud.texto"></p>
                            <p class="text-xs text-surface-medium mt-1.5" x-text="utilidad.salud.mensaje"></p>
                        </div>
                        {{-- Indicador visual de salud --}}
                        <div class="text-right flex-shrink-0">
                            <p class="text-2xl font-semibold"
                               :class="{
                                   'text-[#085041]': utilidad.salud.color === 'green',
                                   'text-[#185FA5]': utilidad.salud.color === 'blue',
                                   'text-[#854F0B]': utilidad.salud.color === 'amber',
                                   'text-[#A32D2D]': utilidad.salud.color === 'red',
                               }"
                               x-text="utilidad.margen_ganancia + '%'"></p>
                            <p class="text-[10px] text-surface-medium">margen</p>
                        </div>
                    </div>
                    {{-- Barra de salud --}}
                    <div class="mt-3">
                        <div class="w-full bg-white/50 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full transition-all duration-700"
                                 :class="{
                                     'bg-[#0F6E56]': utilidad.salud.color === 'green',
                                     'bg-[#185FA5]': utilidad.salud.color === 'blue',
                                     'bg-[#854F0B]': utilidad.salud.color === 'amber',
                                     'bg-[#A32D2D]': utilidad.salud.color === 'red',
                                 }"
                                 :style="'width: ' + Math.min(Math.max(utilidad.margen_ganancia, 0), 100) + '%'"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-surface-medium/60 mt-1">
                            <span>Crítico</span><span>Estable</span><span>Excelente</span>
                        </div>
                    </div>
                </div>

                {{-- Banner datos compartidos --}}
                <div class="bg-brand-primary/8 border border-brand-primary/20 rounded-xl px-4 py-3 flex items-center gap-2">
                    <span class="icon icon-sm text-brand-primary">check_circle</span>
                    <p class="text-xs text-brand-primary">Datos guardados — puedes usarlos en <strong>ISR</strong>, <strong>IVA</strong> y <strong>Retiro seguro</strong>.</p>
                </div>

                {{-- Flujo del dinero --}}
                <div class="bg-white border border-surface-light rounded-2xl p-4">
                    <p class="text-xs font-medium text-surface-dark mb-3 flex items-center gap-1">
                        <span class="icon icon-sm text-brand-primary">account_tree</span>
                        Así se distribuyó tu dinero
                    </p>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-brand-primary flex-shrink-0"></div>
                                <span class="text-xs text-surface-medium">Ventas</span>
                            </div>
                            <span class="text-xs font-medium text-surface-dark" x-text="'$' + utilidad.ventas.toFixed(2)"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-[#854F0B] flex-shrink-0"></div>
                                <span class="text-xs text-surface-medium">− Costos de producción</span>
                            </div>
                            <span class="text-xs font-medium text-[#854F0B]" x-text="'−$' + utilidad.costos.toFixed(2)"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-[#854F0B] flex-shrink-0"></div>
                                <span class="text-xs text-surface-medium">− Gastos operativos</span>
                            </div>
                            <span class="text-xs font-medium text-[#854F0B]" x-text="'−$' + utilidad.gastos_operativos.toFixed(2)"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-[#A32D2D] flex-shrink-0"></div>
                                <span class="text-xs text-surface-medium">− Reserva para impuestos</span>
                            </div>
                            <span class="text-xs font-medium text-[#A32D2D]" x-text="'−$' + utilidad.reserva_isr.toFixed(2)"></span>
                        </div>
                        <div class="border-t border-surface-light pt-2 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-[#0F6E56] flex-shrink-0"></div>
                                <span class="text-xs font-medium text-surface-dark">Tu ganancia real</span>
                            </div>
                            <span class="text-sm font-semibold"
                                  :class="utilidad.es_rentable ? 'text-[#0F6E56]' : 'text-[#A32D2D]'"
                                  x-text="'$' + utilidad.utilidad_neta.toFixed(2)"></span>
                        </div>
                    </div>
                </div>

                {{-- ISR + Retiro --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-[#FCEBEB] rounded-xl p-4">
                        <div class="flex items-center gap-1 mb-1">
                            <span class="icon icon-sm text-[#A32D2D]">savings</span>
                            <p class="text-xs text-[#A32D2D] font-medium">Guarda para Hacienda</p>
                        </div>
                        <p class="text-xl font-semibold text-[#501313]" x-text="'$' + isr.mensual.toFixed(2)"></p>
                        <p class="text-[10px] text-[#A32D2D] opacity-70 mt-1">Cada mes, sin excepción</p>
                    </div>
                    <div class="bg-[#E6F1FB] rounded-xl p-4">
                        <div class="flex items-center gap-1 mb-1">
                            <span class="icon icon-sm text-[#185FA5]">output</span>
                            <p class="text-xs text-[#185FA5] font-medium">Puedes retirar</p>
                        </div>
                        <p class="text-xl font-semibold text-[#042C53]" x-text="'$' + retiro.retiro_seguro.toFixed(2)"></p>
                        <p class="text-[10px] text-[#185FA5] opacity-80 mt-1" x-text="retiro.mensaje"></p>
                    </div>
                </div>

                {{-- IVA si aplica --}}
                <template x-if="iva">
                    <div :class="iva.hay_deuda ? 'bg-[#FAEEDA]' : 'bg-[#E1F5EE]'" class="rounded-xl p-4 flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-1 mb-1">
                                <span class="icon icon-sm" :class="iva.hay_deuda ? 'text-[#854F0B]' : 'text-[#085041]'">percent</span>
                                <p class="text-xs font-medium" :class="iva.hay_deuda ? 'text-[#854F0B]' : 'text-[#085041]'">IVA del período</p>
                            </div>
                            <p class="text-xl font-semibold" :class="iva.hay_deuda ? 'text-[#633806]' : 'text-[#042C53]'"
                               x-text="(iva.hay_deuda ? 'Pagar: $' : 'A favor: $') + (iva.hay_deuda ? iva.iva_pagar : iva.saldo_a_favor).toFixed(2)">
                            </p>
                        </div>
                        <div class="text-right text-xs text-surface-medium">
                            <p>Débito: <span class="font-medium" x-text="'$' + iva.debito_fiscal.toFixed(2)"></span></p>
                            <p>Crédito: <span class="font-medium" x-text="'$' + iva.credito_fiscal.toFixed(2)"></span></p>
                        </div>
                    </div>
                </template>

                {{-- Punto de equilibrio con contexto --}}
                <div class="bg-white border border-surface-light rounded-xl p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-1 mb-1">
                                <span class="icon icon-sm text-brand-primary">balance</span>
                                <p class="text-xs font-medium text-surface-dark">Para no perder dinero este mes</p>
                            </div>
                            <p class="text-xs text-surface-medium">necesitas vender al menos</p>
                        </div>
                        <p class="text-lg font-semibold text-brand-primary"
                           x-text="'$' + utilidad.punto_equilibrio.toFixed(2)"></p>
                    </div>
                    <div class="mt-2 h-1.5 bg-surface-light rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700"
                             :class="utilidad.ventas >= utilidad.punto_equilibrio ? 'bg-brand-secondary' : 'bg-red-400'"
                             :style="'width: ' + Math.min((utilidad.ventas / utilidad.punto_equilibrio * 100), 100) + '%'"></div>
                    </div>
                    <p class="text-[10px] text-surface-medium mt-1"
                       x-text="utilidad.ventas >= utilidad.punto_equilibrio
                           ? '✓ Superaste el punto de equilibrio este mes.'
                           : '⚠ No alcanzaste el punto de equilibrio este mes.'">
                    </p>
                </div>

            </div>
        </template>

    </div>
</div>

</div>