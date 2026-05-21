<div class="grid grid-cols-1 lg:grid-cols-2 gap-6"
     x-data="{
         cargando: false, mostrar: false, reset: false, error: null, resultado: null,
         async calcular() {
             this.cargando = true; this.error = null;
             const ventas = document.getElementById('iva-ventas').value;
             if (!ventas || parseFloat(ventas) < 0) { this.error = 'Ingresa el total de tus ventas gravadas.'; this.cargando = false; return; }
             try {
                 const res = await fetch('{{ route('calcular.iva') }}', {
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                     body: JSON.stringify({ ventas_gravadas: ventas, compras_con_cf: document.getElementById('iva-compras').value || 0 })
                 });
                 const j = await res.json();
                 if (j.ok) { this.resultado = j.resultado; this.mostrar = true; window.hcGuardar({ iva_pagar: j.resultado.iva_pagar }); }
                 else { this.error = 'Error en el cálculo.'; }
             } catch { this.error = 'No se pudo conectar.'; }
             finally { this.cargando = false; }
         },
         limpiar() {
             if (!this.reset) { this.reset = true; setTimeout(() => this.reset = false, 3000); return; }
             ['iva-ventas','iva-compras'].forEach(id => document.getElementById(id).value = '');
             this.resultado = null; this.mostrar = false; this.error = null; this.reset = false;
         }
     }">

    {{-- Formulario --}}
    <div class="bg-white rounded-2xl p-6 border border-surface-light card-shadow">
        <div class="flex items-start justify-between mb-5">
            <div class="flex items-center gap-2">
                <span class="icon icon-lg text-brand-primary">percent</span>
                <div>
                    <h2 class="text-base font-semibold text-surface-dark">Calculadora IVA</h2>
                    <p class="text-xs text-surface-medium">Tasa vigente: 13% · Art. 54 Ley del IVA</p>
                </div>
            </div>
            <button type="button" @click="limpiar()"
                    class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg border transition-colors"
                    :class="reset ? 'border-red-300 text-red-500 bg-red-50' : 'border-surface-light text-surface-medium hover:border-brand-light hover:text-brand-primary'">
                <span class="icon icon-sm">restart_alt</span>
                <span x-text="reset ? '¿Confirmar?' : 'Limpiar'"></span>
            </button>
        </div>

        @include('components.input-field', ['id'=>'iva-ventas','label'=>'Ventas gravadas del período','prefix'=>'$','hint'=>'Total sujeto al 13%, sin IVA incluido','helper'=>'Si tienes el total con IVA, divídelo entre 1.13.'])
        @include('components.input-field', ['id'=>'iva-compras','label'=>'Compras con crédito fiscal','prefix'=>'$','hint'=>'Compras de proveedores con CCF','tooltip'=>'El crédito fiscal solo aplica con el CCF (Comprobante de Crédito Fiscal). Las facturas de consumidor final no generan crédito.'])

        <div class="bg-brand-primary/5 rounded-xl p-3 mb-4 flex items-start gap-2">
            <span class="icon icon-sm text-brand-primary mt-0.5 flex-shrink-0">info</span>
            <p class="text-xs text-brand-primary leading-relaxed">Solo genera crédito fiscal el CCF. Las facturas de consumidor final no aplican.</p>
        </div>

        <div role="alert" x-show="error" class="mb-3">
            <p class="text-xs text-red-500 flex items-center gap-1">
                <span class="icon icon-sm">error</span><span x-text="error"></span>
            </p>
        </div>

        <button type="button" @click="calcular()" :disabled="cargando"
                class="w-full inline-flex items-center justify-center gap-2 bg-brand-secondary text-white
                       text-sm font-medium py-3 rounded-xl hover:opacity-90 transition-opacity
                       disabled:opacity-50 disabled:cursor-not-allowed">
            <span class="icon icon-sm" x-show="!cargando">play_arrow</span>
            <span class="icon icon-sm animate-spin" x-show="cargando">progress_activity</span>
            <span x-text="cargando ? 'Calculando...' : 'Calcular IVA'"></span>
        </button>
        <p class="text-[10px] text-surface-medium/60 text-center mt-3">Art. 54 y 65 · Ley del Impuesto al Valor Agregado</p>
    </div>

    {{-- Resultados --}}
    <div role="region" aria-live="polite" class="space-y-3">
        <div x-show="!mostrar" class="bg-surface-light/30 rounded-2xl p-6 border border-dashed border-surface-light flex items-center justify-center min-h-48">
            <p class="text-sm text-surface-medium text-center">Ingresa tus ventas y compras para calcular el IVA.</p>
        </div>
        <template x-if="mostrar && resultado">
            <div class="fade-in space-y-3">
                <div :class="resultado.hay_deuda ? 'bg-[#FCEBEB]' : 'bg-[#E1F5EE]'" class="rounded-xl p-4">
                    <p class="text-xs font-medium mb-1" :class="resultado.hay_deuda ? 'text-[#A32D2D]' : 'text-[#085041]'"
                       x-text="resultado.hay_deuda ? 'IVA a pagar a Hacienda' : 'Saldo a tu favor'"></p>
                    <p class="text-2xl font-semibold" :class="resultado.hay_deuda ? 'text-[#501313]' : 'text-[#042C53]'"
                       x-text="'$' + (resultado.hay_deuda ? resultado.iva_pagar : resultado.saldo_a_favor).toFixed(2)"></p>
                    <p class="text-xs mt-1 opacity-70" :class="resultado.hay_deuda ? 'text-[#A32D2D]' : 'text-[#085041]'"
                       x-text="resultado.hay_deuda ? 'Debes declarar y pagar a Hacienda' : 'Puedes trasladarlo al siguiente período'"></p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-[#FAEEDA] rounded-xl p-3">
                        <p class="text-xs text-[#854F0B] font-medium mb-1">Débito fiscal</p>
                        <p class="text-base font-semibold text-[#633806]" x-text="'$' + resultado.debito_fiscal.toFixed(2)"></p>
                        <p class="text-[10px] text-[#854F0B] opacity-70">IVA cobrado en ventas</p>
                    </div>
                    <div class="bg-[#E6F1FB] rounded-xl p-3">
                        <p class="text-xs text-[#185FA5] font-medium mb-1">Crédito fiscal</p>
                        <p class="text-base font-semibold text-[#042C53]" x-text="'$' + resultado.credito_fiscal.toFixed(2)"></p>
                        <p class="text-[10px] text-[#185FA5] opacity-70">IVA pagado en compras</p>
                    </div>
                </div>
                <div class="bg-surface-light/40 rounded-xl p-4">
                    <p class="text-xs font-medium text-surface-dark mb-2">Cómo se calculó</p>
                    <p class="text-xs text-surface-medium font-mono" x-text="resultado.desglose.formula_debito"></p>
                    <p class="text-xs text-surface-medium font-mono" x-text="resultado.desglose.formula_credito"></p>
                    <div class="border-t border-surface-light mt-2 pt-2">
                        <p class="text-xs text-surface-dark font-medium font-mono" x-text="resultado.desglose.formula_final"></p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>