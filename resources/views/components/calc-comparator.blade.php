<div x-data="{
    monto: 2000, cargando: false, resultados: null, error: null,

    async comparar() {
        this.cargando = true; this.error = null;
        if (!this.monto || parseFloat(this.monto) <= 0) {
            this.error = 'Por favor ingresa un monto válido.';
            this.cargando = false; return;
        }
        try {
            const res = await fetch('{{ route('calcular.freelancer.comparar') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ monto: this.monto })
            });
            const j = await res.json();
            if (j.ok) {
                this.resultados = j.resultados;
            } else { this.error = 'Error al comparar plataformas.'; }
        } catch { this.error = 'Error de conexión.'; }
        finally { this.cargando = false; }
    },

    init() {
        this.comparar();
        window.addEventListener('tab-cambiado', (e) => {
            if (e.detail === 'comparator' && !this.resultados) this.comparar();
        });
    }
}">

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- ===== PANEL DE CONTROL (2/5) ===== --}}
    <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-surface-light card-shadow h-fit">
        
        <div class="flex items-center gap-2 mb-4">
            <span class="icon icon-lg text-brand-primary">compare_arrows</span>
            <div>
                <h2 class="text-base font-semibold text-surface-dark">Comparar Tarifas</h2>
                <p class="text-xs text-surface-medium">Evita pérdidas invisibles por comisiones</p>
            </div>
        </div>

        <div class="space-y-4">
            {{-- Botones rápidos --}}
            <div>
                <label class="text-xs font-semibold text-surface-dark block mb-2">Montos facturados típicos</label>
                <div class="grid grid-cols-5 gap-1.5">
                    <template x-for="val in [500, 1000, 2000, 5000, 10000]">
                        <button type="button" 
                                @click="monto = val; comparar();"
                                class="px-2 py-2 text-xs font-semibold rounded-xl border transition-all select-none"
                                :class="monto == val 
                                    ? 'bg-brand-primary text-white border-brand-primary shadow-sm' 
                                    : 'bg-white text-surface-medium border-surface-light hover:border-brand-light hover:text-brand-primary'">
                            <span x-text="'$' + val"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Input personalizado --}}
            <div>
                <label for="comp_monto" class="text-xs font-semibold text-surface-dark block mb-1">Monto personalizado</label>
                <div class="flex items-center bg-white border border-surface-light rounded-xl focus-within:border-brand-primary transition-colors">
                    <span class="pl-3 text-sm text-surface-medium select-none">$</span>
                    <input type="number"
                           id="comp_monto"
                           placeholder="0.00"
                           min="10"
                           x-model="monto"
                           @input.debounce.500ms="comparar()"
                           class="flex-1 px-3 py-2.5 text-sm text-surface-dark bg-transparent outline-none placeholder-surface-light/70">
                </div>
                <p class="text-[10px] text-surface-medium mt-1">Escribe cualquier cantidad para comparar en tiempo real.</p>
            </div>

            {{-- Alerta informativa --}}
            <div class="bg-brand-primary/5 rounded-xl p-4 space-y-2 text-xs text-brand-primary leading-relaxed">
                <p class="font-bold flex items-center gap-1">
                    <span class="icon text-xs">info</span> ¿Por qué hay tanta diferencia?
                </p>
                <p>
                    Muchas plataformas aplican <strong>comisiones porcentuales + fijas</strong> y después suman un <strong>2% de retiro a cuenta local</strong>, mientras que transferir directamente por <strong>SWIFT</strong> tiene altos costos fijos bancarios que solo convienen para montos elevados.
                </p>
            </div>

            <button type="button" @click="comparar()" :disabled="cargando"
                    class="w-full inline-flex items-center justify-center gap-2 bg-brand-primary text-white
                           text-sm font-medium py-3 rounded-xl hover:bg-brand-secondary transition-colors
                           disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="icon icon-sm" x-show="!cargando">refresh</span>
                <span class="icon icon-sm animate-spin" x-show="cargando">progress_activity</span>
                <span x-text="cargando ? 'Actualizando...' : 'Comparar de Nuevo'"></span>
            </button>
        </div>
    </div>

    {{-- ===== TABLA Y BARRAS COMPARATIVAS (3/5) ===== --}}
    <div class="lg:col-span-3 space-y-4" role="region" aria-live="polite">

        <div x-show="cargando && !resultados" class="bg-white rounded-2xl p-8 border border-surface-light card-shadow flex justify-center items-center h-64">
            <div class="text-center space-y-2">
                <span class="icon icon-xl animate-spin text-brand-primary">progress_activity</span>
                <p class="text-xs text-surface-medium">Analizando tarifas en tiempo real...</p>
            </div>
        </div>

        <template x-if="resultados">
            <div class="fade-in space-y-4">
                
                {{-- Resumen del mejor método --}}
                <div class="bg-[#E1F5EE] border border-[#9DBFBF] rounded-2xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="icon text-[#0F6E56]" style="font-size: 24px;">verified</span>
                        <div>
                            <p class="text-[10px] font-bold text-[#085041] uppercase">El método más rentable</p>
                            <p class="text-base font-bold text-surface-dark" x-text="resultados[0].nombre"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-surface-medium">Recibes libre</p>
                        <p class="text-lg font-bold text-[#085041]" x-text="'$' + resultados[0].neto.toFixed(2)"></p>
                    </div>
                </div>

                {{-- Tabla y barras --}}
                <div class="bg-white border border-surface-light rounded-2xl p-5 card-shadow">
                    <div class="flex justify-between items-center border-b border-surface-light pb-3 mb-4">
                        <p class="text-xs font-semibold text-surface-dark">Resultados ordenados por rentabilidad</p>
                        <span class="text-[10px] text-surface-medium font-medium" x-text="'Monto base: $' + parseFloat(monto).toFixed(2)"></span>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(plat, index) in resultados" :key="plat.id">
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center text-xs">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-surface-light/30 flex items-center justify-center text-brand-primary">
                                            <span class="icon text-sm" x-text="plat.logo"></span>
                                        </div>
                                        <span class="font-semibold text-surface-dark" x-text="plat.nombre"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-surface-dark" x-text="'$' + plat.neto.toFixed(2)"></span>
                                        <span class="text-[10px] text-surface-medium/70 ml-1.5" x-text="'(Eficiencia: ' + plat.eficiencia + '%)'"></span>
                                    </div>
                                </div>

                                {{-- Barra de eficiencia --}}
                                <div class="relative">
                                    <div class="w-full bg-surface-light/30 rounded-full h-2.5 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-700"
                                             :class="{
                                                 'bg-[#0F6E56]': plat.eficiencia >= 97,
                                                 'bg-[#185FA5]': plat.eficiencia >= 94 && plat.eficiencia < 97,
                                                 'bg-[#D97706]': plat.eficiencia >= 90 && plat.eficiencia < 94,
                                                 'bg-[#DC2626]': plat.eficiencia < 90
                                             }"
                                             :style="'width: ' + plat.eficiencia + '%'"></div>
                                    </div>
                                    
                                    {{-- Desglose rápido debajo de la barra --}}
                                    <div class="flex justify-between text-[10px] text-surface-medium/70 mt-1">
                                        <span class="font-medium" x-text="'Pérdida por comisión: $' + plat.comision.toFixed(2)"></span>
                                        <span class="font-bold"
                                              :class="{
                                                  'text-[#0F6E56]': plat.eficiencia >= 97,
                                                  'text-[#185FA5]': plat.eficiencia >= 94 && plat.eficiencia < 97,
                                                  'text-[#D97706]': plat.eficiencia >= 90 && plat.eficiencia < 94,
                                                  'text-[#DC2626]': plat.eficiencia < 90
                                              }"
                                              x-text="plat.eficiencia >= 97 ? 'Excelente' : (plat.eficiencia >= 94 ? 'Bueno' : (plat.eficiencia >= 90 ? 'Mejorable' : 'Costoso'))">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Nota sobre bancos corresponsales --}}
                    <div class="mt-5 pt-4 border-t border-surface-light text-[10px] text-surface-medium leading-relaxed italic flex items-start gap-1">
                        <span class="icon text-xs mt-0.5">warning</span>
                        Nota sobre Bancos Corresponsales: Cuando transfieres vía SWIFT directo, bancos intermediarios de EE.UU. o Europa pueden descontar de $15 a $30 adicionales de forma automática en el camino. Los valores SWIFT calculados aquí reflejan la tarifa receptora en El Salvador y el corresponsal estándar de $25.
                    </div>
                </div>

            </div>
        </template>
    </div>
</div>
