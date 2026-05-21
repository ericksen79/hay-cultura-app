<div class="grid grid-cols-1 lg:grid-cols-2 gap-6"
     x-data="{
         cargando: false, mostrar: false, reset: false, error: null, resultado: null, precargado: false,
         cargarDatos() {
             const u = window.hcLeer('utilidad_operativa');
             if (u !== null && !document.getElementById('isr-renta').value) {
                 document.getElementById('isr-renta').value = (u * 12).toFixed(2);
                 this.precargado = true;
             }
         },
         init() {
             this.cargarDatos();
             window.addEventListener('tab-cambiado', (e) => {
                 if (e.detail === 'isr') this.cargarDatos();
             });
         },
         async calcular() {
             this.cargando = true; this.error = null;
             const renta = document.getElementById('isr-renta').value;
             if (!renta || parseFloat(renta) <= 0) { this.error = 'Ingresa tu renta neta anual para calcular el ISR.'; this.cargando = false; return; }
             try {
                 const res = await fetch('{{ route('calcular.isr') }}', {
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                     body: JSON.stringify({ renta_neta_anual: renta, tipo_persona: document.getElementById('isr-tipo').value })
                 });
                 const j = await res.json();
                 if (j.ok) { this.resultado = j.resultado; this.mostrar = true; window.hcGuardar({ isr_mensual: j.resultado.mensual }); }
                 else { this.error = 'Error en el cálculo. Verifica los valores.'; }
             } catch { this.error = 'No se pudo conectar.'; }
             finally { this.cargando = false; }
         },
         limpiar() {
             if (!this.reset) { this.reset = true; setTimeout(() => this.reset = false, 3000); return; }
             document.getElementById('isr-renta').value = '';
             document.getElementById('isr-tipo').value = 'natural';
             this.resultado = null; this.mostrar = false; this.error = null; this.reset = false;
         }
     }">

    {{-- Formulario --}}
    <div class="bg-white rounded-2xl p-6 border border-surface-light card-shadow">

        <div class="flex items-start justify-between mb-5">
            <div class="flex items-center gap-2">
                <span class="icon icon-lg text-brand-primary">receipt_long</span>
                <div>
                    <h2 class="text-base font-semibold text-surface-dark">ISR estimado</h2>
                    <p class="text-xs text-surface-medium">Impuesto sobre la renta · Art. 37 LISR</p>
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
             class="bg-brand-primary/8 border border-brand-primary/20 rounded-xl px-3 py-2 mt-3 mb-4 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="icon icon-sm text-brand-primary">download</span>
                <p class="text-xs text-brand-primary">Renta anual estimada calculada en base a tu utilidad.</p>
            </div>
        </div>

        <div x-show="window.hcLeer('utilidad_operativa') !== null && !precargado" class="mb-4 mt-4">
            <button type="button" @click="cargarDatos()" class="w-full inline-flex items-center justify-center gap-2 bg-surface-light text-surface-dark text-xs font-medium py-2.5 rounded-xl hover:bg-surface-light/80 transition-colors">
                <span class="icon icon-sm">download</span> Cargar y proyectar datos guardados
            </button>
        </div>

        {{-- Selector tipo con tooltip y nota aclaratoria --}}
        <div class="mb-4" x-data="{ tooltip: false }">
            <div class="flex items-center gap-1.5 mb-1">
                <label for="isr-tipo" class="text-xs font-medium text-surface-dark">
                    ¿Cómo operas tu negocio?
                </label>
                <div class="relative">
                    <button type="button" @click="tooltip = !tooltip" @click.away="tooltip = false"
                            class="w-4 h-4 rounded-full bg-brand-primary/15 flex items-center justify-center hover:bg-brand-primary/25 transition-colors">
                        <span class="icon text-[11px] text-brand-primary">help</span>
                    </button>
                    <div x-show="tooltip" x-transition
                         class="absolute z-20 bottom-6 left-0 w-72 bg-surface-dark text-white text-xs leading-relaxed rounded-xl p-3 shadow-lg">
                        <p class="font-medium mb-1">Persona natural</p>
                        <p class="opacity-80 mb-2">Freelancer, profesional independiente, comerciante individual o dueño de negocio no constituido formalmente. Es el caso más común.</p>
                        <p class="font-medium mb-1">Persona jurídica</p>
                        <p class="opacity-80">Empresa registrada formalmente ante el CNR: S.A. de C.V., S. de R.L., etc. Si tienes duda, probablemente seas persona natural.</p>
                        <div class="absolute -bottom-1.5 left-3 w-3 h-3 bg-surface-dark rotate-45 rounded-sm"></div>
                    </div>
                </div>
            </div>
            <select id="isr-tipo"
                    class="w-full bg-surface-white border border-surface-light rounded-xl px-3 py-2.5 text-sm text-surface-dark outline-none focus:border-brand-primary transition-colors">
                <option value="natural">Persona natural — freelancer, negocio propio</option>
                <option value="juridica">Persona jurídica — S.A. de C.V., empresa formal</option>
            </select>
            {{-- Nota orientativa --}}
            <p class="text-xs text-surface-medium mt-1.5 flex items-start gap-1">
                <span class="icon icon-sm text-surface-light mt-0.5 flex-shrink-0">info</span>
                Si vendes servicios o tienes un negocio sin inscribir como sociedad, selecciona <strong>Persona natural</strong>.
            </p>
        </div>

        @include('components.input-field', [
            'id'      => 'isr-renta',
            'label'   => 'Renta neta del año',
            'prefix'  => '$',
            'hint'    => 'Lo que ganaste en el año, menos tus gastos deducibles',
            'helper'  => 'Si no tienes el dato exacto, multiplica tu utilidad mensual × 12.',
            'tooltip' => 'Renta neta = total de ingresos del año − gastos necesarios para el negocio. El ISR se calcula sobre esta cifra, no sobre tus ventas brutas.',
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
            <span x-text="cargando ? 'Calculando...' : 'Calcular ISR'"></span>
        </button>

        {{-- Links legales --}}
        <div class="mt-4 pt-4 border-t border-surface-light space-y-1.5">
            <p class="text-[10px] text-surface-medium font-medium uppercase tracking-wider">Base legal</p>
            <a href="https://erickhernandez.notion.site/Impuesto-sobre-la-renta-en-El-Salvador-f6c3c0ce5a9f4ff585321d4094d9c436"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-1 text-xs text-brand-primary hover:underline">
                <span class="icon icon-sm">open_in_new</span>
                Guía ISR El Salvador
            </a><br>
            <a href="https://transparencia.mh.gob.sv/downloads/pdf/DC5101_19_Ley_de_Impuesto_sobre_la_Renta.pdf"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-1 text-xs text-brand-primary hover:underline">
                <span class="icon icon-sm">open_in_new</span>
                Ley de Impuesto sobre la Renta (PDF oficial)
            </a>
        </div>
    </div>

    {{-- Resultados --}}
    <div role="region" aria-live="polite" class="space-y-3">
        <div x-show="!mostrar" class="bg-surface-light/30 rounded-2xl p-6 border border-dashed border-surface-light flex items-center justify-center min-h-48">
            <p class="text-sm text-surface-medium text-center">Ingresa tu renta neta anual y presiona calcular.</p>
        </div>
        <template x-if="mostrar && resultado">
            <div class="fade-in space-y-3">
                <div class="bg-brand-primary/8 border border-brand-primary/20 rounded-xl px-4 py-3 flex items-center gap-2">
                    <span class="icon icon-sm text-brand-primary">check_circle</span>
                    <p class="text-xs text-brand-primary">ISR mensual guardado para <strong>Retiro seguro</strong>.</p>
                </div>
                <div class="bg-[#FCEBEB] rounded-xl p-4">
                    <p class="text-xs text-[#A32D2D] font-medium mb-1">ISR anual estimado</p>
                    <p class="text-2xl font-semibold text-[#501313]" x-text="'$' + resultado.isr_anual.toFixed(2)"></p>
                    <p class="text-xs text-[#A32D2D] mt-1 opacity-70" x-text="resultado.exento ? 'Estás exento — no pagas ISR este año' : resultado.descripcion"></p>
                </div>
                <div class="bg-[#FAEEDA] rounded-xl p-4">
                    <p class="text-xs text-[#854F0B] font-medium mb-1">Guarda este monto cada mes</p>
                    <p class="text-xl font-semibold text-[#633806]" x-text="'$' + resultado.mensual.toFixed(2) + '/mes'"></p>
                    <p class="text-xs text-[#854F0B] opacity-70 mt-1">Para no llevarte sorpresas cuando llegue la declaración anual</p>
                </div>
                <template x-if="resultado.tramo">
                    <div class="bg-[#E6F1FB] rounded-xl p-4">
                        <p class="text-xs text-[#185FA5] font-medium mb-1">Tramo que te aplica</p>
                        <p class="text-base font-semibold text-[#042C53]" x-text="resultado.tramo"></p>
                        <p class="text-xs text-[#185FA5] opacity-70 mt-1" x-text="resultado.descripcion"></p>
                    </div>
                </template>
                <template x-if="resultado.tasa">
                    <div class="bg-[#E6F1FB] rounded-xl p-4">
                        <p class="text-xs text-[#185FA5] font-medium mb-1">Tasa aplicada a tu empresa</p>
                        <p class="text-base font-semibold text-[#042C53]" x-text="resultado.tasa"></p>
                        <p class="text-xs text-[#185FA5] opacity-70 mt-1" x-text="resultado.descripcion"></p>
                    </div>
                </template>
                <template x-if="resultado.desglose && resultado.desglose.formula">
                    <div class="bg-surface-light/40 rounded-xl p-4">
                        <p class="text-xs font-medium text-surface-dark mb-1">Cómo se calculó</p>
                        <p class="text-xs text-surface-medium font-mono" x-text="resultado.desglose.formula"></p>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>