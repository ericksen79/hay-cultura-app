<div class="grid grid-cols-1 lg:grid-cols-2 gap-6"
     x-data="{
         cargando: false, mostrar: false, reset: false, error: null, resultado: null, precargado: false,
         init() {
             const u = window.hcLeer('utilidad_neta');
             const i = window.hcLeer('isr_mensual');
             const f = window.hcLeer('gastos_fijos');
             if (u !== null) { document.getElementById('ret-utilidad').value = u.toFixed(2); this.precargado = true; }
             if (i !== null) { document.getElementById('ret-reserva').value = i.toFixed(2); }
             if (f !== null) { document.getElementById('ret-caja').value = f.toFixed(2); }
         },
         async calcular() {
             this.cargando = true; this.error = null;
             const campos = ['ret-utilidad','ret-reserva','ret-caja'];
             if (campos.some(id => !document.getElementById(id).value)) {
                 this.error = 'Completa todos los campos para continuar.'; this.cargando = false; return;
             }
             try {
                 const res = await fetch('{{ route('calcular.retiro') }}', {
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                     body: JSON.stringify({
                         utilidad_neta:     document.getElementById('ret-utilidad').value,
                         reserva_impuestos: document.getElementById('ret-reserva').value,
                         caja_minima:       document.getElementById('ret-caja').value,
                     })
                 });
                 const j = await res.json();
                 if (j.ok) { this.resultado = j.resultado; this.mostrar = true; }
                 else { this.error = 'Error en el cálculo.'; }
             } catch { this.error = 'No se pudo conectar.'; }
             finally { this.cargando = false; }
         },
         limpiar() {
             if (!this.reset) { this.reset = true; setTimeout(() => this.reset = false, 3000); return; }
             ['ret-utilidad','ret-reserva','ret-caja'].forEach(id => document.getElementById(id).value = '');
             this.resultado = null; this.mostrar = false; this.error = null; this.reset = false; this.precargado = false;
         }
     }">

    {{-- Formulario --}}
    <div class="bg-white rounded-2xl p-6 border border-surface-light card-shadow">
        <div class="flex items-start justify-between mb-1">
            <div class="flex items-center gap-2">
                <span class="icon icon-lg text-brand-primary">savings</span>
                <div>
                    <h2 class="text-base font-semibold text-surface-dark">Retiro seguro</h2>
                    <p class="text-xs text-surface-medium">¿Cuánto puedes sacar este mes?</p>
                </div>
            </div>
            <button type="button" @click="limpiar()"
                    class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg border transition-colors"
                    :class="reset ? 'border-red-300 text-red-500 bg-red-50' : 'border-surface-light text-surface-medium hover:border-brand-light hover:text-brand-primary'">
                <span class="icon icon-sm">restart_alt</span>
                <span x-text="reset ? '¿Confirmar?' : 'Limpiar'"></span>
            </button>
        </div>

        <div x-show="precargado"
             class="bg-brand-primary/8 border border-brand-primary/20 rounded-xl px-3 py-2 mt-3 mb-4 flex items-center gap-2">
            <span class="icon icon-sm text-brand-primary">download</span>
            <p class="text-xs text-brand-primary">Datos cargados desde la calculadora de utilidad mensual.</p>
        </div>

        <div class="mt-4">
            @include('components.input-field', ['id'=>'ret-utilidad','label'=>'Utilidad neta del mes','prefix'=>'$','hint'=>'Tu ganancia del mes ya descontando todos los gastos','helper'=>'Obtenla con la calculadora de utilidad mensual primero.'])
            @include('components.input-field', ['id'=>'ret-reserva','label'=>'Reserva para impuestos','prefix'=>'$','hint'=>'Lo que debes apartar para pagar ISR e IVA','helper'=>'Si no lo sabes, usa el 15% de tu utilidad.','tooltip'=>'Este dinero sigue en tu cuenta, pero es intocable. Sacarlo hoy significa no tener para pagar Hacienda después.'])
            @include('components.input-field', ['id'=>'ret-caja','label'=>'Caja mínima para operar','prefix'=>'$','hint'=>'Lo mínimo para operar el próximo mes','helper'=>'Regla simple: usa tus gastos fijos del mes.'])
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
            <span x-text="cargando ? 'Calculando...' : 'Calcular retiro'"></span>
        </button>
    </div>

    {{-- Resultados --}}
    <div role="region" aria-live="polite" class="space-y-3">
        <div x-show="!mostrar" class="bg-surface-light/30 rounded-2xl p-6 border border-dashed border-surface-light flex items-center justify-center min-h-48">
            <p class="text-sm text-surface-medium text-center">Ingresa tu utilidad, reserva y caja mínima.</p>
        </div>
        <template x-if="mostrar && resultado">
            <div class="fade-in space-y-3">
                <div :class="{
                        'bg-[#E1F5EE]': resultado.situacion === 'holgado' || resultado.situacion === 'moderado',
                        'bg-[#FAEEDA]': resultado.situacion === 'ajustado',
                        'bg-[#FCEBEB]': resultado.situacion === 'sin_retiro',
                     }" class="rounded-xl p-4">
                    <p class="text-xs font-medium mb-1"
                       :class="{'text-[#085041]': resultado.situacion !== 'ajustado' && resultado.situacion !== 'sin_retiro', 'text-[#854F0B]': resultado.situacion === 'ajustado', 'text-[#A32D2D]': resultado.situacion === 'sin_retiro'}">
                        Puedes retirar con seguridad</p>
                    <p class="text-2xl font-semibold"
                       :class="{'text-[#042C53]': resultado.situacion !== 'ajustado' && resultado.situacion !== 'sin_retiro', 'text-[#633806]': resultado.situacion === 'ajustado', 'text-[#501313]': resultado.situacion === 'sin_retiro'}"
                       x-text="'$' + resultado.retiro_seguro.toFixed(2)"></p>
                    <p class="text-xs mt-2 opacity-80"
                       :class="{'text-[#085041]': resultado.situacion !== 'ajustado' && resultado.situacion !== 'sin_retiro', 'text-[#854F0B]': resultado.situacion === 'ajustado', 'text-[#A32D2D]': resultado.situacion === 'sin_retiro'}"
                       x-text="resultado.mensaje"></p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-[#FCEBEB] rounded-xl p-3">
                        <p class="text-xs text-[#A32D2D] font-medium mb-1">Reserva impuestos</p>
                        <p class="text-base font-semibold text-[#501313]" x-text="'$' + resultado.reserva_impuestos.toFixed(2)"></p>
                        <p class="text-[10px] text-[#A32D2D] opacity-70">Apartado para Hacienda</p>
                    </div>
                    <div class="bg-[#FAEEDA] rounded-xl p-3">
                        <p class="text-xs text-[#854F0B] font-medium mb-1">Caja mínima</p>
                        <p class="text-base font-semibold text-[#633806]" x-text="'$' + resultado.caja_minima.toFixed(2)"></p>
                        <p class="text-[10px] text-[#854F0B] opacity-70">Para operar el próximo mes</p>
                    </div>
                </div>
                <div class="bg-surface-light/40 rounded-xl p-4">
                    <p class="text-xs font-medium text-surface-dark mb-1">Cómo se calculó</p>
                    <p class="text-xs text-surface-medium font-mono" x-text="resultado.desglose.formula"></p>
                    <p class="text-xs text-surface-medium mt-1.5 opacity-70" x-text="'Representa el ' + resultado.porcentaje_retiro + '% de tu utilidad neta'"></p>
                </div>
            </div>
        </template>
    </div>
</div>