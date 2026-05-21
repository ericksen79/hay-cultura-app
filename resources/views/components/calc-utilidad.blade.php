<div class="grid grid-cols-1 lg:grid-cols-2 gap-6"
     x-data="{
         cargando: false, mostrar: false, reset: false, error: null, resultado: null,
         async calcular() {
             this.cargando = true; this.error = null;
             const campos = ['ut-ventas','ut-costos','ut-fijos','ut-variables'];
             if (campos.some(id => !document.getElementById(id).value)) {
                 this.error = 'Completa todos los campos para ver tu utilidad.';
                 this.cargando = false; return;
             }
             try {
                 const res = await fetch('{{ route('calcular.utilidad') }}', {
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                     body: JSON.stringify({
                         ventas:    document.getElementById('ut-ventas').value,
                         costos:    document.getElementById('ut-costos').value,
                         fijos:     document.getElementById('ut-fijos').value,
                         variables: document.getElementById('ut-variables').value,
                     })
                 });
                 const j = await res.json();
                 if (j.ok) {
                     this.resultado = j.resultado; this.mostrar = true;
                     window.hcGuardar({ utilidad_operativa: j.resultado.utilidad_operativa, gastos_fijos: j.resultado.gastos_fijos });
                 } else { this.error = 'Error en el cálculo.'; }
             } catch { this.error = 'No se pudo conectar.'; }
             finally { this.cargando = false; }
         },
         limpiar() {
             if (!this.reset) { this.reset = true; setTimeout(() => this.reset = false, 3000); return; }
             ['ut-ventas','ut-costos','ut-fijos','ut-variables'].forEach(id => document.getElementById(id).value = '');
             this.resultado = null; this.mostrar = false; this.error = null; this.reset = false;
         }
     }">

    {{-- Formulario --}}
    <div class="bg-white rounded-2xl p-6 border border-surface-light card-shadow">

        <div class="flex items-start justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="icon icon-lg text-brand-primary">trending_up</span>
                <div>
                    <h2 class="text-base font-semibold text-surface-dark">Utilidad mensual</h2>
                    <p class="text-xs text-surface-medium">¿Ganaste o solo vendiste?</p>
                </div>
            </div>
            <button type="button" @click="limpiar()"
                    class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg border transition-colors"
                    :class="reset ? 'border-red-300 text-red-500 bg-red-50' : 'border-surface-light text-surface-medium hover:border-brand-light hover:text-brand-primary'">
                <span class="icon icon-sm">restart_alt</span>
                <span x-text="reset ? '¿Confirmar?' : 'Limpiar'"></span>
            </button>
        </div>

        {{-- Explicación visual simple del concepto --}}
        <div class="bg-surface-light/40 rounded-xl p-3 mb-5 mt-2">
            <p class="text-xs text-surface-dark font-medium mb-2">¿Qué calcula esto?</p>
            <div class="flex items-center gap-2 text-xs flex-wrap">
                <span class="bg-white border border-surface-light rounded-lg px-2 py-1 text-surface-dark font-medium">Ventas</span>
                <span class="text-surface-medium">−</span>
                <span class="bg-white border border-surface-light rounded-lg px-2 py-1 text-surface-dark font-medium">Costos</span>
                <span class="text-surface-medium">−</span>
                <span class="bg-white border border-surface-light rounded-lg px-2 py-1 text-surface-dark font-medium">Gastos</span>
                <span class="text-surface-medium">=</span>
                <span class="bg-brand-primary/10 border border-brand-primary/20 rounded-lg px-2 py-1 text-brand-primary font-medium">Tu ganancia real</span>
            </div>
        </div>

        @include('components.input-field', [
            'id'      => 'ut-ventas',
            'label'   => 'Total que cobraste este mes',
            'prefix'  => '$',
            'hint'    => 'Sin incluir el 13% de IVA',
            'helper'  => 'Ej: si facturaste $1,130 con IVA incluido, escribe $1,000',
        ])

        @include('components.input-field', [
            'id'      => 'ut-costos',
            'label'   => 'Costos de lo que vendiste',
            'prefix'  => '$',
            'hint'    => 'Lo que pagaste para poder producir o entregar',
            'helper'  => 'Freelancer: hosting, licencias. Pastelería: harina, azúcar, ingredientes.',
        ])

        @include('components.input-field', [
            'id'      => 'ut-fijos',
            'label'   => 'Gastos fijos del mes',
            'prefix'  => '$',
            'hint'    => 'Los que pagas siempre, vendas o no vendas',
            'helper'  => 'Alquiler, internet, planilla, suscripciones mensuales.',
        ])

        @include('components.input-field', [
            'id'      => 'ut-variables',
            'label'   => 'Gastos variables del mes',
            'prefix'  => '$',
            'hint'    => 'Los que cambian según cuánto trabajaste o vendiste',
            'helper'  => 'Gasolina, envíos, materiales extra, comisiones por venta.',
        ])

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
            <span x-text="cargando ? 'Calculando...' : 'Ver mi ganancia real'"></span>
        </button>
    </div>

    {{-- Resultados --}}
    <div role="region" aria-live="polite" class="space-y-3">

        <div x-show="!mostrar"
             class="bg-surface-light/30 rounded-2xl p-6 border border-dashed border-surface-light
                    flex flex-col items-center justify-center gap-3 min-h-48">
            <span class="icon icon-xl text-surface-light">trending_up</span>
            <p class="text-sm text-surface-medium text-center">Completa los campos y verás aquí cuánto ganaste realmente este mes.</p>
        </div>

        <template x-if="mostrar && resultado">
            <div class="fade-in space-y-3">

                {{-- Banner estado compartido --}}
                <div class="bg-brand-primary/8 border border-brand-primary/20 rounded-xl px-4 py-3 flex items-center gap-2">
                    <span class="icon icon-sm text-brand-primary">check_circle</span>
                    <p class="text-xs text-brand-primary">Ganancia guardada. Úsala en <strong>Retiro seguro</strong> para saber cuánto puedes sacar.</p>
                </div>

                {{-- Ganancia real — resultado principal --}}
                <div :class="resultado.es_rentable ? 'bg-[#E1F5EE] border-[#9DBFBF]' : 'bg-[#FCEBEB] border-[#E8A8A8]'"
                     class="rounded-xl p-5 border">
                    <p class="text-xs font-medium mb-1"
                       :class="resultado.es_rentable ? 'text-[#085041]' : 'text-[#A32D2D]'">
                        Tu ganancia real del mes
                    </p>
                    <p class="text-3xl font-semibold"
                       :class="resultado.es_rentable ? 'text-[#042C53]' : 'text-[#501313]'"
                       x-text="'$' + resultado.utilidad_neta.toFixed(2)"></p>

                    {{-- Mensaje en lenguaje simple --}}
                    <p class="text-xs mt-2"
                       :class="resultado.es_rentable ? 'text-[#085041]' : 'text-[#A32D2D]'"
                       x-text="resultado.es_rentable
                           ? 'De cada $' + resultado.ventas.toFixed(0) + ' que vendiste, te quedaron $' + resultado.utilidad_neta.toFixed(0) + ' reales.'
                           : 'Este mes gastaste más de lo que ganaste. Revisa tus costos.'">
                    </p>
                </div>

                {{-- Grid de métricas con etiquetas simples --}}
                <div class="grid grid-cols-2 gap-3">

                    <div class="bg-white border border-surface-light rounded-xl p-3">
                        <div class="flex items-center gap-1 mb-1">
                            <span class="icon icon-sm text-[#185FA5]">remove_shopping_cart</span>
                            <p class="text-xs text-[#185FA5] font-medium">Después de producir</p>
                        </div>
                        <p class="text-base font-semibold text-surface-dark"
                           x-text="'$' + resultado.utilidad_bruta.toFixed(2)"></p>
                        <p class="text-[10px] text-surface-medium mt-0.5">Ventas − Costos directos</p>
                    </div>

                    <div class="bg-white border border-surface-light rounded-xl p-3">
                        <div class="flex items-center gap-1 mb-1">
                            <span class="icon icon-sm text-[#854F0B]">receipt</span>
                            <p class="text-xs text-[#854F0B] font-medium">Total de gastos</p>
                        </div>
                        <p class="text-base font-semibold text-surface-dark"
                           x-text="'$' + resultado.gastos_operativos.toFixed(2)"></p>
                        <p class="text-[10px] text-surface-medium mt-0.5">Fijos + Variables</p>
                    </div>

                    <div class="bg-[#FCEBEB] rounded-xl p-3">
                        <div class="flex items-center gap-1 mb-1">
                            <span class="icon icon-sm text-[#A32D2D]">savings</span>
                            <p class="text-xs text-[#A32D2D] font-medium">Aparta para impuestos</p>
                        </div>
                        <p class="text-base font-semibold text-[#501313]"
                           x-text="'$' + resultado.reserva_isr.toFixed(2)"></p>
                        <p class="text-[10px] text-[#A32D2D] opacity-70 mt-0.5">15% estimado — no lo gastes</p>
                    </div>

                    <div class="bg-surface-light/40 rounded-xl p-3">
                        <div class="flex items-center gap-1 mb-1">
                            <span class="icon icon-sm text-surface-medium">balance</span>
                            <p class="text-xs text-surface-medium font-medium">Mínimo para no perder</p>
                        </div>
                        <p class="text-base font-semibold text-surface-dark"
                           x-text="'$' + resultado.punto_equilibrio.toFixed(2)"></p>
                        <p class="text-[10px] text-surface-medium mt-0.5">Punto de equilibrio</p>
                    </div>

                </div>

                {{-- Margen con contexto --}}
                <div class="bg-white border border-surface-light rounded-xl p-4">
                    <div class="flex justify-between items-center mb-1">
                        <div class="flex items-center gap-1.5">
                            <span class="icon icon-sm text-brand-primary">donut_large</span>
                            <p class="text-xs font-medium text-surface-dark">¿Qué tan rentable eres?</p>
                        </div>
                        <p class="text-sm font-semibold text-brand-primary"
                           x-text="resultado.margen_ganancia + '%'"></p>
                    </div>
                    <div class="w-full bg-surface-light rounded-full h-2 mb-2">
                        <div class="h-2 rounded-full transition-all duration-500"
                             :class="resultado.margen_ganancia >= 20 ? 'bg-brand-secondary' : resultado.margen_ganancia >= 5 ? 'bg-amber-400' : 'bg-red-400'"
                             :style="'width: ' + Math.min(Math.max(resultado.margen_ganancia, 0), 100) + '%'"></div>
                    </div>
                    {{-- Referencias de margen para contexto --}}
                    <div class="flex justify-between text-[10px] text-surface-medium mb-2">
                        <span>0%</span>
                        <span class="text-amber-500">5% mínimo</span>
                        <span class="text-brand-secondary">20% bueno</span>
                        <span>100%</span>
                    </div>
                    <p class="text-xs text-surface-medium" x-text="resultado.salud.mensaje"></p>
                </div>

            </div>
        </template>
    </div>
</div>