<div class="grid grid-cols-1 lg:grid-cols-2 gap-6"
     x-data="{
         cargando: false, mostrar: false, reset: false, error: null,
         resultados: [], gastos: ['', '', ''],

         agregarGasto() {
             if (this.gastos.length < 10) {
                 this.gastos.push('');
                 this.$nextTick(() => {
                     const campos = document.querySelectorAll('[id^=gasto-]');
                     if (campos.length) campos[campos.length - 1].focus();
                 });
             }
         },

         eliminarGasto(i) {
             if (this.gastos.length > 1) this.gastos.splice(i, 1);
         },

         async clasificar() {
             this.cargando = true; this.error = null;
             const limpios = this.gastos.filter(g => g.trim() !== '');
             if (limpios.length === 0) {
                 this.error = 'Escribe al menos un gasto para clasificarlo.';
                 this.cargando = false; return;
             }
             try {
                 const res = await fetch('{{ route('calcular.gastos') }}', {
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                     body: JSON.stringify({ gastos: limpios })
                 });
                 const j = await res.json();
                 if (j.ok) { this.resultados = j.resultados; this.mostrar = true; }
                 else { this.error = 'Error al clasificar. Intenta de nuevo.'; }
             } catch { this.error = 'No se pudo conectar.'; }
             finally { this.cargando = false; }
         },

         limpiar() {
             if (!this.reset) { this.reset = true; setTimeout(() => this.reset = false, 3000); return; }
             this.gastos = ['', '', ''];
             this.resultados = []; this.mostrar = false;
             this.error = null; this.reset = false;
         },

         colorBg(t)    { return {deducible:'bg-[#E1F5EE]',no_deducible:'bg-[#FCEBEB]',activo:'bg-[#E6F1FB]',revisar:'bg-[#FAEEDA]'}[t] || 'bg-surface-light/40'; },
         colorText(t)  { return {deducible:'text-[#085041]',no_deducible:'text-[#501313]',activo:'text-[#042C53]',revisar:'text-[#633806]'}[t] || 'text-surface-dark'; },
         colorLabel(t) { return {deducible:'text-[#0F6E56]',no_deducible:'text-[#A32D2D]',activo:'text-[#185FA5]',revisar:'text-[#854F0B]'}[t] || 'text-surface-medium'; },
         iconoTipo(t)  { return {deducible:'check_circle',no_deducible:'cancel',activo:'inventory_2',revisar:'help'}[t] || 'help'; }
     }">

    {{-- Formulario --}}
    <div class="bg-white rounded-2xl p-6 border border-surface-light card-shadow">

        <div class="flex items-start justify-between mb-1">
            <div class="flex items-center gap-2">
                <span class="icon icon-lg text-brand-primary">category</span>
                <div>
                    <h2 class="text-base font-semibold text-surface-dark">Clasificador de gastos</h2>
                    <p class="text-xs text-surface-medium">¿Deducible, activo o personal?</p>
                </div>
            </div>
            <button type="button" @click="limpiar()"
                    class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg border transition-colors"
                    :class="reset ? 'border-red-300 text-red-500 bg-red-50' : 'border-surface-light text-surface-medium hover:border-brand-light hover:text-brand-primary'">
                <span class="icon icon-sm">restart_alt</span>
                <span x-text="reset ? '¿Confirmar?' : 'Limpiar'"></span>
            </button>
        </div>

        {{-- Nota de contexto importante --}}
        <div class="bg-brand-primary/5 border border-brand-primary/15 rounded-xl p-3 mt-4 mb-3">
            <p class="text-xs text-brand-primary leading-relaxed">
                <span class="icon icon-sm align-middle mr-1">info</span>
                <strong>¿Tienes negocio de alimentos?</strong> Ingredientes como harina, azúcar o huevos
                son deducibles si son para producir lo que vendes — no si son para tu consumo personal.
            </p>
        </div>

        <p class="text-xs text-surface-medium mb-1 flex items-center gap-1">
            <span class="icon icon-sm text-surface-light">remove</span>
            Escribe el nombre como lo conoces, no el nombre contable
        </p>
        <p class="text-xs text-brand-secondary mb-4 flex items-center gap-1">
            <span class="icon icon-sm">lightbulb</span>
            Ej: "Adobe", "Harina negocio", "Laptop", "Gas propano", "Netflix"
        </p>

        {{-- Campos dinámicos --}}
        <div class="space-y-2 mb-4">
            <template x-for="(gasto, i) in gastos" :key="i">
                <div class="flex gap-2 items-center">
                    <label :for="'gasto-' + i" class="sr-only" x-text="'Gasto ' + (i+1)"></label>
                    <input type="text"
                           :id="'gasto-' + i"
                           x-model="gastos[i]"
                           :placeholder="['Adobe', 'Harina negocio', 'Gas propano', 'Netflix', 'Laptop', 'Gasolina', 'Empaque', 'Ropa', 'Horno', 'Internet'][i % 10]"
                           class="flex-1 bg-surface-white border border-surface-light rounded-xl
                                  px-3 py-2.5 text-sm text-surface-dark outline-none
                                  focus:border-brand-primary transition-colors">
                    <button type="button"
                            @click="eliminarGasto(i)"
                            x-show="gastos.length > 1"
                            class="w-8 h-8 rounded-lg bg-surface-light/60 flex items-center justify-center
                                   hover:bg-red-50 hover:text-red-400 transition-colors flex-shrink-0">
                        <span class="icon icon-sm text-surface-medium">close</span>
                    </button>
                </div>
            </template>
        </div>

        <button type="button" @click="agregarGasto()" x-show="gastos.length < 10"
                class="w-full inline-flex items-center justify-center gap-1.5 border border-dashed
                       border-brand-primary/30 text-brand-primary text-xs font-medium py-2 rounded-xl
                       hover:bg-brand-primary/5 transition-colors mb-4">
            <span class="icon icon-sm">add</span>
            Agregar otro gasto
        </button>

        <div role="alert" x-show="error" class="mb-3">
            <p class="text-xs text-red-500 flex items-center gap-1">
                <span class="icon icon-sm">error</span><span x-text="error"></span>
            </p>
        </div>

        <button type="button" @click="clasificar()" :disabled="cargando"
                class="w-full inline-flex items-center justify-center gap-2 bg-brand-secondary text-white
                       text-sm font-medium py-3 rounded-xl hover:opacity-90 transition-opacity
                       disabled:opacity-50 disabled:cursor-not-allowed">
            <span class="icon icon-sm" x-show="!cargando">play_arrow</span>
            <span class="icon icon-sm animate-spin" x-show="cargando">progress_activity</span>
            <span x-text="cargando ? 'Clasificando...' : 'Clasificar gastos'"></span>
        </button>

        {{-- Enlace a documentación legal --}}
        <div class="mt-4 pt-4 border-t border-surface-light space-y-1.5">
            <p class="text-[10px] text-surface-medium font-medium uppercase tracking-wider">Base legal</p>
            <a href="https://centr4l.com/project/gastos-deducibles-no-deducibles-segun-la-ley-el-salvador/"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-1 text-xs text-brand-primary hover:underline">
                <span class="icon icon-sm">open_in_new</span>
                Guía de gastos deducibles El Salvador
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

        {{-- Estado vacío --}}
        <div x-show="!mostrar"
             class="bg-surface-light/30 rounded-2xl p-6 border border-dashed border-surface-light
                    flex flex-col items-center justify-center gap-4 min-h-48">
            <p class="text-sm text-surface-medium text-center">
                Ingresa tus gastos y presiona clasificar.
            </p>
            <div class="flex flex-wrap gap-2 justify-center">
                <span class="inline-flex items-center gap-1 text-xs bg-[#E1F5EE] text-[#085041] px-2 py-1 rounded-lg">
                    <span class="icon icon-sm">check_circle</span> Deducible — reduce tu ISR
                </span>
                <span class="inline-flex items-center gap-1 text-xs bg-[#E6F1FB] text-[#042C53] px-2 py-1 rounded-lg">
                    <span class="icon icon-sm">inventory_2</span> Activo — se deprecia
                </span>
                <span class="inline-flex items-center gap-1 text-xs bg-[#FCEBEB] text-[#501313] px-2 py-1 rounded-lg">
                    <span class="icon icon-sm">cancel</span> No deducible — personal
                </span>
                <span class="inline-flex items-center gap-1 text-xs bg-[#FAEEDA] text-[#633806] px-2 py-1 rounded-lg">
                    <span class="icon icon-sm">help</span> Consultar contador
                </span>
            </div>
        </div>

        {{-- Resultados --}}
        <template x-if="mostrar && resultados.length > 0">
            <div class="fade-in space-y-2">

                <p class="text-xs text-surface-medium mb-1">
                    <span x-text="resultados.length"></span> gasto(s) clasificado(s) ·
                    <span class="text-[#0F6E56] font-medium"
                          x-text="resultados.filter(r => r.tipo === 'deducible').length + ' deducible(s)'"></span>
                    <span x-show="resultados.filter(r => r.tipo === 'activo').length > 0"
                          class="text-[#185FA5] font-medium"
                          x-text="' · ' + resultados.filter(r => r.tipo === 'activo').length + ' activo(s)'"></span>
                </p>

                <template x-for="item in resultados" :key="item.gasto">
                    <div :class="colorBg(item.tipo)" class="rounded-xl p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2 flex-1">
                                <span class="icon icon-sm flex-shrink-0" :class="colorLabel(item.tipo)"
                                      x-text="iconoTipo(item.tipo)"></span>
                                <div>
                                    <p class="text-sm font-medium" :class="colorText(item.tipo)" x-text="item.gasto"></p>
                                    <p class="text-xs opacity-70 mt-0.5" :class="colorLabel(item.tipo)" x-text="item.descripcion"></p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-xs font-medium px-2 py-1 rounded-lg whitespace-nowrap block"
                                      :class="colorLabel(item.tipo)"
                                      x-text="item.etiqueta"></span>
                                {{-- Base legal --}}
                                <span class="text-[10px] opacity-60 mt-1 block"
                                      :class="colorLabel(item.tipo)"
                                      x-text="item.base_legal"></span>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t border-white/40 flex items-start gap-1.5">
                            <span class="icon icon-sm flex-shrink-0 mt-0.5" :class="colorLabel(item.tipo)">tips_and_updates</span>
                            <p class="text-xs" :class="colorLabel(item.tipo) + ' opacity-80'" x-text="item.consejo"></p>
                        </div>
                    </div>
                </template>

            </div>
        </template>
    </div>

</div>