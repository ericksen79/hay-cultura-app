<div class="grid grid-cols-1 lg:grid-cols-2 gap-6"
     id="res-clasificacion-seccion"
     x-data="{
         cargando: false, mostrar: false, reset: false, error: null,
         resultados: [],
         gastos: [{ nombre: '', precio: '' }, { nombre: '', precio: '' }, { nombre: '', precio: '' }],
         tasaIsr: 20,
         copiadoExito: false,

         agregarGasto() {
             if (this.gastos.length < 20) {
                 this.gastos.push({ nombre: '', precio: '' });
                 this.$nextTick(() => {
                     const campos = document.querySelectorAll('[id^=gasto-nombre-]');
                     if (campos.length) campos[campos.length - 1].focus();
                 });
             }
         },

         eliminarGasto(i) {
             if (this.gastos.length > 1) {
                 this.gastos.splice(i, 1);
                 this.$nextTick(() => {
                     const campos = document.querySelectorAll('[id^=gasto-nombre-]');
                     if (campos.length) {
                         const targetIdx = Math.max(0, i - 1);
                         campos[targetIdx].focus();
                     }
                 });
             }
         },

         async clasificar() {
             this.cargando = true; this.error = null;
             const limpios = this.gastos.filter(g => g.nombre.trim() !== '').map(g => ({
                 nombre: g.nombre.trim(),
                 precio: g.precio !== '' && g.precio !== null ? parseFloat(g.precio) : 0
             }));
             if (limpios.length === 0) {
                 this.error = 'Escribe al menos un gasto con nombre para clasificarlo.';
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
                 if (j.ok) { 
                     this.resultados = j.resultados; 
                     this.mostrar = true; 
                     this.$nextTick(() => {
                         const el = document.getElementById('res-clasificacion-seccion');
                         if (el) el.scrollIntoView({ behavior: 'smooth' });
                     });
                 }
                 else { this.error = 'Error al clasificar. Intenta de nuevo.'; }
             } catch { this.error = 'No se pudo conectar.'; }
             finally { this.cargando = false; }
         },

         limpiar() {
             if (!this.reset) { this.reset = true; setTimeout(() => this.reset = false, 3000); return; }
             this.gastos = [{ nombre: '', precio: '' }, { nombre: '', precio: '' }, { nombre: '', precio: '' }];
             this.resultados = []; this.mostrar = false;
             this.error = null; this.reset = false;
         },

         get totalDeducibles() {
             return this.resultados
                 .filter(r => r.tipo === 'deducible')
                 .reduce((sum, r) => sum + r.precio, 0);
         },

         get totalActivos() {
             return this.resultados
                 .filter(r => r.tipo === 'activo')
                 .reduce((sum, r) => sum + r.precio, 0);
         },

         get totalDepreciacionAnual() {
             return this.resultados
                 .filter(r => r.tipo === 'activo')
                 .reduce((sum, r) => sum + (r.depreciacion_anual || 0), 0);
         },

         get totalDepreciacionMensual() {
             return this.resultados
                 .filter(r => r.tipo === 'activo')
                 .reduce((sum, r) => sum + (r.depreciacion_mensual || 0), 0);
         },

         get totalNoDeducibles() {
             return this.resultados
                 .filter(r => r.tipo === 'no_deducible')
                 .reduce((sum, r) => sum + r.precio, 0);
         },

         get totalNoDeduciblesCount() {
             return this.resultados.filter(r => r.tipo === 'no_deducible').length;
         },

         get totalConsultarCount() {
             return this.resultados.filter(r => r.tipo === 'revisar').length;
         },

         get totalConsultar() {
             return this.resultados
                 .filter(r => r.tipo === 'revisar')
                 .reduce((sum, r) => sum + r.precio, 0);
         },

         copiarResultados() {
             let t = '📊 RESUMEN DE CLASIFICACIÓN DE GASTOS · HAY CULTURA APP SV\n';
             t += '=========================================================\n\n';
             
             t += `🟢 GASTOS DEDUCIBLES: $${this.totalDeducibles.toFixed(2)}\n`;
             t += `   Ahorro estimado en ISR (tasa ${this.tasaIsr}%): $${(this.totalDeducibles * (this.tasaIsr / 100)).toFixed(2)}\n\n`;
             
             if (this.totalActivos > 0) {
                 t += `🔵 ACTIVOS DEPRECIABLES: $${this.totalActivos.toFixed(2)}\n`;
                 t += `   Depreciación Anual Total: $${this.totalDepreciacionAnual.toFixed(2)}\n`;
                 t += `   Depreciación Mensual Total: $${this.totalDepreciacionMensual.toFixed(2)}\n\n`;
             }
             
             if (this.totalConsultarCount > 0) {
                 t += `🟡 GASTOS A CONSULTAR CON CONTADOR: ${this.totalConsultarCount} gasto(s) ($${this.totalConsultar.toFixed(2)})\n\n`;
             }
             
             t += `🔴 GASTOS NO DEDUCIBLES REGISTRADOS: ${this.totalNoDeduciblesCount} gasto(s) ($${this.totalNoDeducibles.toFixed(2)})\n\n`;
             
             t += '---------------------------------------------------------\n';
             t += 'DETALLE DE CLASIFICACIÓN:\n';
             
             this.resultados.forEach(r => {
                 let simb = { deducible: '🟢', activo: '🔵', revisar: '🟡', no_deducible: '🔴' }[r.tipo] || '⚪';
                 t += `${simb} ${r.gasto} ($${r.precio.toFixed(2)}) — ${r.etiqueta}\n`;
                 t += `   Explicación: ${r.descripcion}\n`;
                 t += `   Base Legal: ${r.base_legal}\n`;
                 if (r.tipo === 'activo') {
                     t += `   Depreciación: ${r.porcentaje_depreciacion}% anual (LISR Art. 30)\n`;
                 }
                 t += '\n';
             });
             
             t += 'Cálculos y clasificación basados en la legislación de El Salvador 🇸🇻\n';
             t += 'Generado con Hay Cultura App SV · Herramienta Financiera para Independientes';
             
             navigator.clipboard.writeText(t).then(() => {
                 this.copiadoExito = true;
                 setTimeout(() => this.copiadoExito = false, 3000);
             });
         },

         colorBg(t)    { return {deducible:'bg-[#E1F5EE]',no_deducible:'bg-[#FCEBEB]',activo:'bg-[#E6F1FB]',revisar:'bg-[#FAEEDA]'}[t] || 'bg-surface-light/40'; },
         colorText(t)  { return {deducible:'text-[#085041]',no_deducible:'text-[#501313]',activo:'text-[#042C53]',revisar:'text-[#633806]'}[t] || 'text-surface-dark'; },
         colorLabel(t) { return {deducible:'text-[#0F6E56]',no_deducible:'text-[#A32D2D]',activo:'text-[#185FA5]',revisar:'text-[#854F0B]'}[t] || 'text-surface-medium'; },
         iconoTipo(t)  { return {deducible:'check_circle',no_deducible:'cancel',activo:'inventory_2',revisar:'help'}[t] || 'help'; }
     }">

    {{-- Formulario --}}
    <div class="bg-white rounded-2xl p-6 border border-surface-light card-shadow h-fit">

        <div class="flex items-start justify-between mb-1">
            <div class="flex items-center gap-2">
                <span class="icon icon-lg text-brand-primary">category</span>
                <div>
                    <h2 class="text-base font-semibold text-surface-dark">Clasificador de gastos</h2>
                    <p class="text-xs text-surface-medium">¿Deducible, activo o personal?</p>
                </div>
            </div>
            <button type="button" @click="limpiar()"
                    class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg border transition-colors flex-shrink-0 ml-2"
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
            Escribe el nombre del gasto y su precio
        </p>
        <p class="text-xs text-brand-secondary mb-4 flex items-center gap-1">
            <span class="icon icon-sm">lightbulb</span>
            Ej: "Adobe Cloud" ($50.00), "Laptop Asus" ($1,200), "Harina" ($25.00)
        </p>

        {{-- Campos dinámicos --}}
        <div class="space-y-2.5 mb-4">
            <template x-for="(gasto, i) in gastos" :key="i">
                <div class="grid grid-cols-12 gap-2 items-center">
                    {{-- Input Nombre --}}
                    <div class="col-span-7">
                        <label :for="'gasto-nombre-' + i" class="sr-only" x-text="'Descripción del gasto ' + (i+1)"></label>
                        <input type="text"
                               :id="'gasto-nombre-' + i"
                               x-model="gastos[i].nombre"
                               :placeholder="['Adobe Cloud', 'Harina negocio', 'Gas propano', 'Netflix', 'Laptop', 'Gasolina', 'Empaque', 'Ropa personal', 'Horno', 'Internet'][i % 10]"
                               class="w-full bg-surface-white border border-surface-light rounded-xl
                                      px-3 py-2 text-sm text-surface-dark outline-none
                                      focus:border-brand-primary transition-colors">
                    </div>
                    {{-- Input Precio --}}
                    <div class="col-span-4 flex items-center bg-surface-white border border-surface-light rounded-xl px-2.5 py-1.5 focus-within:border-brand-primary transition-colors">
                        <span class="text-xs text-surface-medium mr-1 font-medium select-none">$</span>
                        <label :for="'gasto-precio-' + i" class="sr-only" x-text="'Monto del gasto ' + (i+1)"></label>
                        <input type="number"
                               :id="'gasto-precio-' + i"
                               x-model="gastos[i].precio"
                               placeholder="0.00"
                               step="0.01"
                               min="0"
                               class="w-full bg-transparent text-sm text-surface-dark outline-none
                                      [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none
                                      [&::-webkit-inner-spin-button]:appearance-none">
                    </div>
                    {{-- Eliminar --}}
                    <div class="col-span-1 flex justify-center">
                        <button type="button"
                                @click="eliminarGasto(i)"
                                x-show="gastos.length > 1"
                                :aria-label="'Eliminar gasto ' + (i+1)"
                                class="w-8 h-8 rounded-lg bg-surface-light/40 flex items-center justify-center
                                       hover:bg-red-50 hover:text-red-500 transition-colors flex-shrink-0">
                            <span class="icon icon-sm text-surface-medium">close</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <button type="button" @click="agregarGasto()" x-show="gastos.length < 20"
                class="w-full inline-flex items-center justify-center gap-1.5 border border-dashed
                       border-brand-primary/30 text-brand-primary text-xs font-medium py-2.5 rounded-xl
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
    <div role="region" aria-live="polite" class="space-y-4">

        {{-- Estado vacío --}}
        <div x-show="!mostrar"
             class="bg-surface-light/30 rounded-2xl p-6 border border-dashed border-surface-light
                    flex flex-col items-center justify-center gap-4 min-h-48 text-center">
            <p class="text-sm text-surface-medium">
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
            <div class="fade-in space-y-4">

                {{-- Resúmenes Agregados Organizados en Cuadrícula 2x2 --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    {{-- Card Deducibles e ISR --}}
                    <div class="bg-[#E1F5EE] border border-[#9DBFBF] rounded-2xl p-6 shadow-sm flex flex-col justify-between min-h-[160px] card-shadow transition-all">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="icon text-[#0F6E56] text-[24px]">check_circle</span>
                                <p class="text-xs font-bold text-[#085041] uppercase tracking-wider">Total Deducibles</p>
                            </div>
                            <p class="text-3xl sm:text-4xl font-extrabold text-[#0F6E56] tracking-tight" x-text="'$' + totalDeducibles.toFixed(2)"></p>
                        </div>
                        
                        <div class="mt-5 pt-4 border-t border-[#0F6E56]/15 space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs text-[#085041] font-semibold">Tasa ISR:</span>
                                <select x-model="tasaIsr" class="bg-white text-xs text-surface-dark border border-[#9DBFBF] rounded-xl px-2.5 py-1.5 outline-none font-bold cursor-pointer hover:border-brand-primary transition-colors">
                                    <option value="10">10% (Natural - T2)</option>
                                    <option value="20">20% (Natural - T3)</option>
                                    <option value="30">30% (Natural - T4)</option>
                                    <option value="25">25% (Jurídica - Flat)</option>
                                </select>
                            </div>
                            <div class="flex items-center justify-between gap-3 bg-white/60 px-3 py-2 rounded-xl border border-[#9DBFBF]/20">
                                <span class="text-xs text-[#085041]/85 font-bold uppercase tracking-wider">Ahorro ISR</span>
                                <span class="font-extrabold text-[#0F6E56] text-xl" x-text="'$' + (totalDeducibles * (tasaIsr / 100)).toFixed(2)"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Card Activos y Depreciación --}}
                    <div class="bg-[#E6F1FB] border border-[#7CC0E4] rounded-2xl p-6 shadow-sm flex flex-col justify-between min-h-[160px] card-shadow transition-all">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="icon text-[#185FA5] text-[24px]">inventory_2</span>
                                <p class="text-xs font-bold text-[#042C53] uppercase tracking-wider">Activos Fijos</p>
                            </div>
                            <p class="text-3xl sm:text-4xl font-extrabold text-[#042C53] tracking-tight" x-text="'$' + totalActivos.toFixed(2)"></p>
                        </div>
                        
                        <div class="mt-5 pt-4 border-t border-[#185FA5]/15 grid grid-cols-2 gap-3">
                            <div class="bg-white/60 px-3 py-2 rounded-xl text-center border border-[#7CC0E4]/20 flex flex-col justify-center">
                                <span class="block uppercase font-bold text-[#185FA5] text-[10px] tracking-wider mb-0.5">Depr. Anual</span>
                                <span class="font-extrabold text-sm text-[#042C53]" x-text="'$' + totalDepreciacionAnual.toFixed(2)"></span>
                            </div>
                            <div class="bg-white/60 px-3 py-2 rounded-xl text-center border border-[#7CC0E4]/20 flex flex-col justify-center">
                                <span class="block uppercase font-bold text-[#185FA5] text-[10px] tracking-wider mb-0.5">Depr. Mensual</span>
                                <span class="font-extrabold text-sm text-[#042C53]" x-text="'$' + totalDepreciacionMensual.toFixed(2)"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Card No Deducibles --}}
                    <div class="bg-[#FCEBEB] border border-[#E8A8A8] rounded-2xl p-6 shadow-sm flex flex-col justify-between min-h-[160px] card-shadow transition-all">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="icon text-[#A32D2D] text-[24px]">cancel</span>
                                <p class="text-xs font-bold text-[#501313] uppercase tracking-wider">No Deducibles</p>
                            </div>
                            <p class="text-3xl sm:text-4xl font-extrabold text-[#A32D2D] tracking-tight" x-text="'$' + totalNoDeducibles.toFixed(2)"></p>
                        </div>
                        
                        <div class="mt-5 pt-4 border-t border-[#A32D2D]/15 space-y-3">
                            <div class="flex items-center justify-between gap-3 bg-white/60 px-3 py-2 rounded-xl border border-[#E8A8A8]/20">
                                <span class="text-xs text-[#501313]/85 font-bold uppercase tracking-wider">Cantidad</span>
                                <span class="font-extrabold text-sm text-[#A32D2D]" x-text="totalNoDeduciblesCount + ' gasto(s)'"></span>
                            </div>
                            <p class="text-[10px] text-[#A32D2D]/85 leading-normal font-semibold">
                                *Gastos de consumo personal no reducen carga fiscal (Art. 29-A LISR).
                            </p>
                        </div>
                    </div>

                    {{-- Card Consultar Contador --}}
                    <div class="bg-[#FAEEDA] border border-[#F0C97A] rounded-2xl p-6 shadow-sm flex flex-col justify-between min-h-[160px] card-shadow transition-all">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="icon text-[#854F0B] text-[24px]">help</span>
                                <p class="text-xs font-bold text-[#633806] uppercase tracking-wider">Consultar Contador</p>
                            </div>
                            <p class="text-3xl sm:text-4xl font-extrabold text-[#854F0B] tracking-tight" x-text="'$' + totalConsultar.toFixed(2)"></p>
                        </div>
                        
                        <div class="mt-5 pt-4 border-t border-[#F0C97A]/20 space-y-3">
                            <div class="flex items-center justify-between gap-3 bg-white/60 px-3 py-2 rounded-xl border border-[#F0C97A]/20">
                                <span class="text-xs text-[#633806]/85 font-bold uppercase tracking-wider">Cantidad</span>
                                <span class="font-extrabold text-sm text-[#854F0B]" x-text="totalConsultarCount + ' gasto(s)'"></span>
                            </div>
                            <p class="text-[10px] text-[#854F0B]/85 leading-normal font-semibold">
                                *Gastos con clasificación ambigua. Depende de su uso para el negocio (Art. 29 LISR).
                            </p>
                        </div>
                    </div>

                </div>

                {{-- Cabecera Detalle y Copiado --}}
                <div class="flex items-center justify-between gap-4 pt-1 border-t border-surface-light">
                    <p class="text-xs text-surface-medium">
                        Detalle de clasificación (<span class="font-semibold text-surface-dark" x-text="resultados.length"></span> gasto(s)):
                    </p>
                    <button type="button" @click="copiarResultados()"
                            class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-xl border border-brand-primary/20
                                   bg-brand-primary/5 text-brand-primary hover:bg-brand-primary hover:text-white transition-all font-medium"
                            :aria-label="copiadoExito ? 'Resultados copiados con éxito' : 'Copiar resultados en texto'">
                        <span class="icon icon-sm" x-text="copiadoExito ? 'check' : 'content_copy'"></span>
                        <span x-text="copiadoExito ? '¡Copiado!' : 'Copiar reporte'"></span>
                    </button>
                </div>

                {{-- Cards del detalle --}}
                <div class="space-y-2">
                    <template x-for="item in resultados" :key="item.gasto">
                        <div :class="colorBg(item.tipo)" class="rounded-xl p-4 transition-all">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5 flex-1">
                                    <span class="icon icon-sm flex-shrink-0" :class="colorLabel(item.tipo)"
                                          x-text="iconoTipo(item.tipo)"></span>
                                    <div>
                                        <p class="text-sm font-semibold" :class="colorText(item.tipo)">
                                            <span x-text="item.gasto"></span>
                                            <span class="ml-1 opacity-80 text-xs font-normal" x-text="'($' + item.precio.toFixed(2) + ')'"></span>
                                        </p>
                                        <p class="text-xs opacity-75 mt-0.5 font-medium" :class="colorLabel(item.tipo)" x-text="item.descripcion"></p>
                                        
                                        {{-- Desglose de Depreciación si es activo --}}
                                        <template x-if="item.tipo === 'activo'">
                                            <div class="mt-1 flex items-center gap-2 text-[10px] font-bold" :class="colorLabel(item.tipo)">
                                                <span>Depreciación:</span>
                                                <span class="bg-white/60 px-1.5 py-0.5 rounded" x-text="'$' + item.depreciacion_anual.toFixed(2) + ' / año'"></span>
                                                <span class="bg-white/60 px-1.5 py-0.5 rounded" x-text="'$' + item.depreciacion_mensual.toFixed(2) + ' / mes'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-lg whitespace-nowrap block bg-white/60 text-center uppercase tracking-wide"
                                          :class="colorLabel(item.tipo)"
                                          x-text="item.etiqueta"></span>
                                    {{-- Base legal --}}
                                    <span class="text-[9px] opacity-60 mt-1 block font-semibold"
                                          :class="colorLabel(item.tipo)"
                                          x-text="item.base_legal"></span>
                                </div>
                            </div>
                            <div class="mt-2.5 pt-2.5 border-t border-white/40 flex items-start gap-1.5">
                                <span class="icon icon-sm flex-shrink-0 mt-0.5" :class="colorLabel(item.tipo)">tips_and_updates</span>
                                <p class="text-xs leading-relaxed font-medium" :class="colorLabel(item.tipo) + ' opacity-85'" x-text="item.consejo"></p>
                            </div>
                        </div>
                    </template>
                </div>

            </div>
        </template>
    </div>

</div>