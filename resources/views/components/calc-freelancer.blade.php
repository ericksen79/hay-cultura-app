<div x-data="{
    cargando: false, mostrar: false, reset: false, error: null,
    monto_facturado: '', metodo_cobro: 'wise', banco_receptor: 'agricola',
    feePct: 1.00, feeFijo: 0.00, aplicar_iva_comision: false,
    inscrito_hacienda: true, cotiza_isss: 'no', fondo_emergencia_pct: 10,
    gastos: [
        { concepto: 'Adobe Creative Cloud', monto: 65.00 },
        { concepto: 'Internet de Fibra', monto: 30.00 },
        { concepto: 'ChatGPT Plus / AI Tools', monto: 20.00 },
        { concepto: 'Hosting & Servidores', monto: 15.00 }
    ],
    resultado: null,
    modalAyuda: false,
    toast: { mostrar: false, mensaje: '' },

    mostrarToast(msg) {
        this.toast.mensaje = msg;
        this.toast.mostrar = true;
        setTimeout(() => { this.toast.mostrar = false; }, 3000);
    },

    actualizarComisionesDefault() {
        const defaults = {
            payoneer: { pct: 3.99, fijo: 0.49 },
            wise: { pct: 1.00, fijo: 0.00 },
            paypal: { pct: 5.40, fijo: 0.30 },
            stripe: { pct: 3.90, fijo: 0.30 },
            deel: { pct: 0.00, fijo: 5.00 },
            upwork: { pct: 10.00, fijo: 2.00 },
            western_union: { pct: 4.00, fijo: 0.00 },
            swift: { pct: 0.00, fijo: 0.00 },
            bitcoin: { pct: 1.00, fijo: 0.00 },
            otro: { pct: 0.00, fijo: 0.00 }
        };
        const m = this.metodo_cobro;
        if (defaults[m]) {
            this.feePct = defaults[m].pct;
            this.feeFijo = defaults[m].fijo;
            this.mostrarToast('Costos de plataforma cargados por defecto.');
        }
    },

    agregarGasto() {
        this.gastos.push({ concepto: '', monto: '' });
        this.mostrarToast('Fila de gasto agregada.');
    },

    quitarGasto(index) {
        this.gastos.splice(index, 1);
        this.mostrarToast('Fila de gasto eliminada.');
    },

    obtenerTotalGastos() {
        return this.gastos.reduce((sum, g) => sum + (parseFloat(g.monto) || 0), 0);
    },

    async calcular() {
        this.cargando = true; this.error = null;
        if (!this.monto_facturado || parseFloat(this.monto_facturado) <= 0) {
            this.error = 'Por favor ingresa un monto facturado mayor a 0.';
            this.cargando = false; return;
        }
        try {
            const res = await fetch('{{ route('calcular.freelancer.salud') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({
                    monto_facturado: this.monto_facturado,
                    metodo_cobro: this.metodo_cobro,
                    banco_receptor: this.banco_receptor,
                    fee_porcentual_manual: this.feePct,
                    fee_fijo_manual: this.feeFijo,
                    aplicar_iva_comision: this.aplicar_iva_comision,
                    inscrito_hacienda: this.inscrito_hacienda,
                    cotiza_isss: this.cotiza_isss,
                    fondo_emergencia_pct: this.fondo_emergencia_pct,
                    gastos_deducibles: this.gastos
                })
            });
            const j = await res.json();
            if (j.ok) {
                this.resultado = j.resultado;
                this.mostrar = true;
                this.mostrarToast('¡Cálculo realizado y guardado con éxito! 🚀');
                // Guardar en el estado global para uso de otras calculadoras tradicionales si es necesario
                window.hcGuardar({
                    utilidad_operativa: j.resultado.utilidad_operativa,
                    isr_mensual: j.resultado.isr.mensual_sugerido,
                    gastos_fijos: j.resultado.gastos_operativos.total
                });
            } else { 
                this.error = 'Error en el cálculo. Verifica que los montos sean correctos.'; 
            }
        } catch { 
            this.error = 'No se pudo conectar con el servidor. Revisa tu conexión.'; 
        }
        finally { this.cargando = false; }
    },

    limpiar() {
        if (!this.reset) { this.reset = true; setTimeout(() => this.reset = false, 3000); return; }
        this.monto_facturado = '';
        this.metodo_cobro = 'wise';
        this.banco_receptor = 'agricola';
        this.feePct = 1.00;
        this.feeFijo = 0.00;
        this.aplicar_iva_comision = false;
        this.inscrito_hacienda = true;
        this.cotiza_isss = 'no';
        this.fondo_emergencia_pct = 10;
        this.gastos = [
            { concepto: 'Adobe Creative Cloud', monto: 65.00 },
            { concepto: 'Internet de Fibra', monto: 30.00 },
            { concepto: 'ChatGPT Plus / AI Tools', monto: 20.00 },
            { concepto: 'Hosting & Servidores', monto: 15.00 }
        ];
        this.resultado = null;
        this.mostrar = false;
        this.error = null;
        this.reset = false;
        this.mostrarToast('Formulario restablecido.');
        window.hcLimpiarTodo();
    }
}">

<!-- Toast Notification Component -->
<div x-show="toast.mostrar" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed bottom-5 right-5 z-50 bg-surface-dark text-white rounded-xl px-4 py-3 shadow-2xl flex items-center gap-2 border border-brand-primary/25"
     style="display: none;">
    <span class="icon text-brand-light text-sm">check_circle</span>
    <span class="text-xs font-semibold" x-text="toast.mensaje"></span>
</div>

<!-- Modal / Popover de Ayuda Detallada -->
<div x-show="modalAyuda" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;" 
     role="dialog" 
     aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-surface-dark/70 backdrop-blur-sm transition-opacity" @click="modalAyuda = false"></div>
    
    <!-- Modal content -->
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-surface-light overflow-hidden fade-in">
            <div class="flex items-center justify-between border-b border-surface-light pb-3.5 mb-4">
                <div class="flex items-center gap-2">
                    <span class="icon text-brand-primary" style="font-size: 24px;">help_center</span>
                    <h3 class="text-base font-bold text-surface-dark">Guía de Comisiones e Impuestos</h3>
                </div>
                <button type="button" @click="modalAyuda = false" class="text-surface-medium hover:text-brand-primary transition-colors">
                    <span class="icon">close</span>
                </button>
            </div>
            
            <div class="space-y-4 text-xs text-surface-medium leading-relaxed max-h-[60vh] overflow-y-auto pr-1">
                <div>
                    <h4 class="font-bold text-surface-dark mb-1 flex items-center gap-1">
                        <span class="icon text-sm">payment</span> 1. Plataformas y sus comisiones reales
                    </h4>
                    <p>
                        Cada pasarela de pago retiene una comisión por transacción antes de recibir los fondos. Por ejemplo, <strong>Payoneer</strong> cobra un 3.99% + $0.49 fijo y, adicionalmente, cobra un <strong>2% de retiro</strong> al transferir a un banco de El Salvador. <strong>Wise</strong> cobra comisiones porcentuales bajas (~1%) y un retiro fijo de $3.00, siendo la alternativa más competitiva del mercado actual.
                    </p>
                </div>

                <div>
                    <h4 class="font-bold text-surface-dark mb-1 flex items-center gap-1">
                        <span class="icon text-sm">language</span> 2. ¿Qué es el Banco Corresponsal?
                    </h4>
                    <p>
                        Cuando una transferencia internacional viaja desde Europa o EE.UU. hacia un banco local de El Salvador mediante el sistema <strong>SWIFT</strong> directo, el dinero pasa obligatoriamente por bancos intermediarios (ej. Citibank o JPMorgan en Nueva York). Estos bancos pueden descontar una comisión automática de <strong>$15 a $30 adicionales</strong> en el camino de forma invisible, independientemente de lo que te cobre tu banco local.
                    </p>
                </div>

                <div>
                    <h4 class="font-bold text-surface-dark mb-1 flex items-center gap-1">
                        <span class="icon text-sm">receipt_long</span> 3. Impuesto sobre la Renta (ISR) en El Salvador
                    </h4>
                    <p>
                        Bajo la legislación de El Salvador (Art. 37 de la LISR), las personas naturales independientes están exentas si su renta neta anual es menor a $4,064.00. Para montos superiores, se aplican tramos del <strong>10%, 20% y 30%</strong> con cuotas fijas específicas. Esta calculadora proyecta tu utilidad mensual de forma anualizada para estimar la retención preventiva mensual exacta que debes guardar.
                    </p>
                </div>

                <div>
                    <h4 class="font-bold text-surface-dark mb-1 flex items-center gap-1">
                        <span class="icon text-sm">medical_services</span> 4. Cotización del ISSS Independiente
                    </h4>
                    <p>
                        Los profesionales independientes en El Salvador pueden afiliarse de forma voluntaria al Régimen Especial de Salud del Seguro Social (ISSS). Tiene dos cuotas fijas configurables: <strong>$40.00 mensuales</strong> para cobertura individual y <strong>$56.00 mensuales</strong> para cobertura familiar (cónyuge e hijos menores de 12 años).
                    </p>
                </div>

                <div class="bg-brand-primary/5 rounded-xl p-3 border border-brand-primary/20 space-y-1.5 mt-2">
                    <p class="font-bold text-brand-primary uppercase text-[10px] tracking-wider">Enlaces de referencia oficial:</p>
                    <a href="https://transparencia.mh.gob.sv/downloads/pdf/DC5101_19_Ley_de_Impuesto_sobre_la_Renta.pdf" 
                       target="_blank" rel="noopener" class="text-brand-primary hover:underline flex items-center gap-1">
                        <span class="icon text-[10px]">open_in_new</span> Ley de Impuesto sobre la Renta (PDF Ministerio de Hacienda)
                    </a>
                    <a href="https://erickhernandez.notion.site/Impuesto-sobre-la-renta-en-El-Salvador-f6c3c0ce5a9f4ff585321d4094d9c436" 
                       target="_blank" rel="noopener" class="text-brand-primary hover:underline flex items-center gap-1">
                        <span class="icon text-[10px]">open_in_new</span> Guía Tributaria para Independientes (Notion Educativo)
                    </a>
                    <a href="https://www.isss.gob.sv/" 
                       target="_blank" rel="noopener" class="text-brand-primary hover:underline flex items-center gap-1">
                        <span class="icon text-[10px]">open_in_new</span> Portal de Cotización Voluntaria ISSS Independiente
                    </a>
                </div>
            </div>
            
            <div class="mt-5 pt-3 border-t border-surface-light text-right">
                <button type="button" @click="modalAyuda = false" class="bg-brand-primary text-white text-xs font-semibold px-4 py-2 rounded-xl hover:bg-brand-secondary transition-colors">
                    Entendido, Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- ===== COLUMNA FORMULARIO DE ENTRADAS (2/5) ===== --}}
    <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-surface-light card-shadow">
        
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="icon icon-lg text-brand-primary">payments</span>
                <div>
                    <h2 class="text-base font-semibold text-surface-dark">Flujo de Cobro</h2>
                    <p class="text-xs text-surface-medium">Ingresos y comisiones internacionales</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="modalAyuda = true" class="inline-flex items-center gap-0.5 text-xs text-brand-primary font-medium hover:underline">
                    <span class="icon text-sm">help_outline</span> Guía
                </button>
                <button type="button" @click="limpiar()"
                        class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 rounded-lg border transition-colors flex-shrink-0"
                        :class="reset ? 'border-red-300 text-red-500 bg-red-50' : 'border-surface-light text-surface-medium hover:border-brand-light hover:text-brand-primary'">
                    <span class="icon icon-sm">restart_alt</span>
                    <span x-text="reset ? '¿Confirmar?' : 'Limpiar'"></span>
                </button>
            </div>
        </div>

        {{-- 1. Información General --}}
        <div class="space-y-4">
            {{-- Dropdown: Tipo de Profesional --}}
            <div x-data="{ tooltip: false }">
                <div class="flex items-center justify-between mb-1">
                    <label for="tipo_profesional" class="text-xs font-semibold text-surface-dark flex items-center gap-1">
                        ¿Cuál es tu rol/profesión?
                    </label>
                    <div class="relative">
                        <button type="button" @click="tooltip = !tooltip" @click.away="tooltip = false" class="w-4.5 h-4.5 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center hover:bg-brand-primary/20 transition-colors">
                            <span class="icon text-[10px]">help</span>
                        </button>
                        <!-- Tooltip Popover -->
                        <div x-show="tooltip" 
                             x-transition
                             class="absolute z-30 bottom-6 right-0 w-64 bg-surface-dark text-white text-[10px] leading-relaxed rounded-xl p-3 shadow-xl"
                             style="display: none;">
                            Tu rol profesional nos sirve para clasificar y orientarte sobre gastos típicos deducibles en El Salvador.
                            <div class="absolute -bottom-1.5 right-1 w-3 h-3 bg-surface-dark rotate-45 rounded-sm"></div>
                        </div>
                    </div>
                </div>
                <select id="tipo_profesional"
                        class="w-full bg-surface-white border border-surface-light rounded-xl px-3 py-2.5 text-sm text-surface-dark outline-none focus:border-brand-primary transition-colors">
                    <option value="desarrollador">Desarrollador (Web, Full Stack, Mobile)</option>
                    <option value="disenador">Diseñador (UX/UI, Product, Gráfico)</option>
                    <option value="marketing">Especialista en Marketing Digital / SEO</option>
                    <option value="video">Editor de Video / Motion Designer</option>
                    <option value="va">Asistente Virtual (VA)</option>
                    <option value="customer">Representante de Soporte al Cliente</option>
                    <option value="redactor">Redactor de Contenido / Copywriter</option>
                    <option value="otro">Otro Profesional Remoto</option>
                </select>
                <span class="text-[10px] text-surface-medium block mt-1">Selecciona la actividad más afín a tus servicios prestados.</span>
            </div>

            {{-- Input: Monto Facturado --}}
            <div x-data="{ tooltip: false }">
                <div class="flex items-center justify-between mb-1">
                    <label for="monto_facturado" class="text-xs font-semibold text-surface-dark">
                        Monto facturado al cliente
                    </label>
                    <div class="relative">
                        <button type="button" @click="tooltip = !tooltip" @click.away="tooltip = false" class="w-4.5 h-4.5 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center hover:bg-brand-primary/20 transition-colors">
                            <span class="icon text-[10px]">help</span>
                        </button>
                        <!-- Tooltip Popover -->
                        <div x-show="tooltip" 
                             x-transition
                             class="absolute z-30 bottom-6 right-0 w-64 bg-surface-dark text-white text-[10px] leading-relaxed rounded-xl p-3 shadow-xl"
                             style="display: none;">
                            Corresponde a la suma bruta total que le cobraste a tu cliente en la factura de exportación, antes de que la plataforma te reste comisiones.
                            <div class="absolute -bottom-1.5 right-1 w-3 h-3 bg-surface-dark rotate-45 rounded-sm"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center bg-white border border-surface-light rounded-xl focus-within:border-brand-primary transition-colors">
                    <span class="pl-3 text-sm text-surface-medium select-none">$</span>
                    <input type="number"
                           id="monto_facturado"
                           placeholder="Ej: 2000.00"
                           min="0.01"
                           step="0.01"
                           x-model="monto_facturado"
                           class="flex-1 px-3 py-2.5 text-sm text-surface-dark bg-transparent outline-none placeholder-surface-light/70">
                </div>
                <span class="text-[10px] text-surface-medium block mt-1">💡 Helper: Escribe el valor neto facturado. Ej: 2000.00</span>
            </div>

            {{-- Dropdown: Método de cobro --}}
            <div x-data="{ tooltip: false }">
                <div class="flex items-center justify-between mb-1">
                    <label for="metodo_cobro" class="text-xs font-semibold text-surface-dark">
                        ¿Cómo cobraste este dinero?
                    </label>
                    <div class="relative">
                        <button type="button" @click="tooltip = !tooltip" @click.away="tooltip = false" class="w-4.5 h-4.5 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center hover:bg-brand-primary/20 transition-colors">
                            <span class="icon text-[10px]">help</span>
                        </button>
                        <!-- Tooltip Popover -->
                        <div x-show="tooltip" 
                             x-transition
                             class="absolute z-30 bottom-6 right-0 w-64 bg-surface-dark text-white text-[10px] leading-relaxed rounded-xl p-3 shadow-xl"
                             style="display: none;">
                            El método seleccionado carga automáticamente las comisiones promedio de dicha plataforma para brindarte un precálculo exacto.
                            <div class="absolute -bottom-1.5 right-1 w-3 h-3 bg-surface-dark rotate-45 rounded-sm"></div>
                        </div>
                    </div>
                </div>
                <select id="metodo_cobro"
                        x-model="metodo_cobro"
                        @change="actualizarComisionesDefault()"
                        class="w-full bg-surface-white border border-surface-light rounded-xl px-3 py-2.5 text-sm text-surface-dark outline-none focus:border-brand-primary transition-colors">
                    <option value="wise">Wise (antes TransferWise)</option>
                    <option value="payoneer">Payoneer</option>
                    <option value="paypal">PayPal</option>
                    <option value="deel">Deel (Retiro local/SWIFT)</option>
                    <option value="stripe">Stripe</option>
                    <option value="upwork">Upwork Platform</option>
                    <option value="western_union">Western Union</option>
                    <option value="swift">SWIFT Directa (Transferencia Internacional Bancaria)</option>
                    <option value="bitcoin">Bitcoin / Billetera Cripto</option>
                    <option value="otro">Otro Método de Cobro</option>
                </select>
                <span class="text-[10px] text-surface-medium block mt-1" x-text="metodo_cobro === 'payoneer' ? '💡 Tip: Retirar de Payoneer a bancos locales tiene un 2% adicional sobre los fondos recibidos.' : '💡 Tip: Las pasarelas de pago digitales aplican comisiones porcentuales y fijas.'"></span>
            </div>

            {{-- Dropdown: Banco receptor --}}
            <div x-data="{ tooltip: false }">
                <div class="flex items-center justify-between mb-1">
                    <label for="banco_receptor" class="text-xs font-semibold text-surface-dark">
                        Banco de El Salvador donde recibes los fondos
                    </label>
                    <div class="relative">
                        <button type="button" @click="tooltip = !tooltip" @click.away="tooltip = false" class="w-4.5 h-4.5 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center hover:bg-brand-primary/20 transition-colors">
                            <span class="icon text-[10px]">help</span>
                        </button>
                        <!-- Tooltip Popover -->
                        <div x-show="tooltip" 
                             x-transition
                             class="absolute z-30 bottom-6 right-0 w-64 bg-surface-dark text-white text-[10px] leading-relaxed rounded-xl p-3 shadow-xl"
                             style="display: none;">
                            El banco receptor aplica tarifas fijas por transferencias internacionales SWIFT. Banco Agrícola aplica $5.65 (hasta $3,000) o $11.30, BAC aplica $35.00 fijos.
                            <div class="absolute -bottom-1.5 right-1 w-3 h-3 bg-surface-dark rotate-45 rounded-sm"></div>
                        </div>
                    </div>
                </div>
                <select id="banco_receptor"
                        x-model="banco_receptor"
                        class="w-full bg-surface-white border border-surface-light rounded-xl px-3 py-2.5 text-sm text-surface-dark outline-none focus:border-brand-primary transition-colors">
                    <option value="agricola">Banco Agrícola</option>
                    <option value="bac">BAC Credomatic</option>
                    <option value="cuscatlan">Banco Cuscatlán</option>
                    <option value="promerica">Banco Promerica</option>
                    <option value="atlantida">Banco Atlántida</option>
                    <option value="industrial">Banco Industrial (BI)</option>
                    <option value="otro">Otro Banco / Billetera local</option>
                </select>
                <span class="text-[10px] text-surface-medium block mt-1">Específica qué entidad bancaria recibe tu saldo retirado.</span>
            </div>

            {{-- Costos Ajustables de Plataforma --}}
            <div class="bg-surface-light/20 rounded-2xl p-4 border border-surface-light/40 space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-bold text-surface-medium uppercase tracking-wider">
                        Costos de plataforma cargados
                    </p>
                    <span class="text-[9px] text-brand-primary font-semibold italic">Ajustables</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] text-surface-medium block mb-1">Fee Porcentual (%)</label>
                        <input type="number" step="0.01" x-model="feePct" class="w-full bg-white border border-surface-light rounded-lg px-2 py-1.5 text-xs outline-none focus:border-brand-primary">
                    </div>
                    <div>
                        <label class="text-[10px] text-surface-medium block mb-1">Fee Fijo ($)</label>
                        <input type="number" step="0.01" x-model="feeFijo" class="w-full bg-white border border-surface-light rounded-lg px-2 py-1.5 text-xs outline-none focus:border-brand-primary">
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer mt-1">
                    <input type="checkbox" x-model="aplicar_iva_comision" class="rounded text-brand-primary focus:ring-brand-primary">
                    <span class="text-[10px] font-medium text-surface-dark select-none">¿Aplicar IVA 13% sobre comisiones de pasarela?</span>
                </label>
            </div>

            {{-- 2. Gastos Deducibles (Dynamic List) --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-semibold text-surface-dark flex items-center gap-1">
                        Gastos operativos mensuales
                    </label>
                    <button type="button" @click="agregarGasto()" class="text-xs text-brand-primary font-medium hover:underline inline-flex items-center gap-0.5">
                        <span class="icon text-xs">add</span> Agregar fila
                    </button>
                </div>
                
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    <template x-for="(gasto, index) in gastos" :key="index">
                        <div class="flex items-center gap-2">
                            <input type="text"
                                   placeholder="Concepto (ej. Internet)"
                                   x-model="gasto.concepto"
                                   class="flex-1 bg-surface-white border border-surface-light rounded-lg px-2.5 py-1.5 text-xs text-surface-dark outline-none focus:border-brand-primary">
                            
                            <div class="flex items-center bg-white border border-surface-light rounded-lg w-20 focus-within:border-brand-primary transition-colors">
                                <span class="pl-1.5 text-[10px] text-surface-medium">$</span>
                                <input type="number"
                                       placeholder="0.00"
                                       x-model="gasto.monto"
                                       class="w-full px-1.5 py-1.5 text-xs text-surface-dark bg-transparent outline-none">
                            </div>

                            <button type="button" @click="quitarGasto(index)" class="text-red-500 hover:text-red-700 flex-shrink-0">
                                <span class="icon text-xs">delete</span>
                            </button>
                        </div>
                    </template>
                </div>
                <div class="flex justify-between items-center bg-brand-primary/5 rounded-lg p-2.5 mt-2.5">
                    <p class="text-[10px] font-semibold text-brand-primary uppercase">Suma de Gastos del Freelance</p>
                    <p class="text-xs font-bold text-brand-primary" x-text="'$' + obtenerTotalGastos().toFixed(2)"></p>
                </div>
            </div>

            {{-- 3. Formalización y Retención --}}
            <div class="border-t border-surface-light pt-4 space-y-4">
                {{-- Toggle: Inscrito en Hacienda --}}
                <label class="flex items-center justify-between gap-3 cursor-pointer">
                    <div>
                        <span class="text-xs font-semibold text-surface-dark block">¿Inscrito en Hacienda (Declaras ISR)?</span>
                        <p class="text-[10px] text-surface-medium">Actívalo si calculas y reservas mes a mes tu ISR estimado (F11 anual).</p>
                    </div>
                    <div class="relative flex-shrink-0">
                        <input type="checkbox" x-model="inscrito_hacienda" class="sr-only">
                        <div class="w-9 h-5 rounded-full transition-colors" :class="inscrito_hacienda ? 'bg-brand-primary' : 'bg-surface-light'"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform" :class="inscrito_hacienda ? 'translate-x-4' : 'translate-x-0'"></div>
                    </div>
                </label>

                {{-- Dropdown: ISSS Independiente --}}
                <div x-data="{ tooltip: false }">
                    <div class="flex items-center justify-between mb-1">
                        <label for="cotiza_isss" class="text-xs font-semibold text-surface-dark">
                            ¿Cotizas ISSS voluntario independiente?
                        </label>
                        <div class="relative">
                            <button type="button" @click="tooltip = !tooltip" @click.away="tooltip = false" class="w-4.5 h-4.5 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center hover:bg-brand-primary/20 transition-colors">
                                <span class="icon text-[10px]">help</span>
                            </button>
                            <!-- Tooltip Popover -->
                            <div x-show="tooltip" 
                                 x-transition
                                 class="absolute z-30 bottom-6 right-0 w-64 bg-surface-dark text-white text-[10px] leading-relaxed rounded-xl p-3 shadow-xl"
                                 style="display: none;">
                                Como trabajador independiente voluntario en El Salvador, cotizar al ISSS te garantiza cobertura médica hospitalaria y medicamentos completos.
                                <div class="absolute -bottom-1.5 right-1 w-3 h-3 bg-surface-dark rotate-45 rounded-sm"></div>
                            </div>
                        </div>
                    </div>
                    <select id="cotiza_isss"
                            x-model="cotiza_isss"
                            class="w-full bg-surface-white border border-surface-light rounded-xl px-3 py-2.5 text-sm text-surface-dark outline-none focus:border-brand-primary transition-colors">
                        <option value="no">No cotizo ISSS (Sin seguro público)</option>
                        <option value="individual">Sí, Cobertura Individual ($40.00/mes)</option>
                        <option value="familiar">Sí, Cobertura Familiar ($56.00/mes)</option>
                    </select>
                    <span class="text-[10px] text-surface-medium block mt-1">💡 Tip: Afiliarte voluntariamente blinda tu salud familiar.</span>
                </div>

                {{-- Dropdown: Fondo de emergencia --}}
                <div x-data="{ tooltip: false }">
                    <div class="flex items-center justify-between mb-1">
                        <label for="fondo_emergencia_pct" class="text-xs font-semibold text-surface-dark">
                            Reserva para Fondo de Emergencia
                        </label>
                        <div class="relative">
                            <button type="button" @click="tooltip = !tooltip" @click.away="tooltip = false" class="w-4.5 h-4.5 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center hover:bg-brand-primary/20 transition-colors">
                                <span class="icon text-[10px]">help</span>
                            </button>
                            <!-- Tooltip Popover -->
                            <div x-show="tooltip" 
                                 x-transition
                                 class="absolute z-30 bottom-6 right-0 w-64 bg-surface-dark text-white text-[10px] leading-relaxed rounded-xl p-3 shadow-xl"
                                 style="display: none;">
                                El fondo de emergencia amortigua los meses en que tus ingresos como freelancer fluctúan. Se recomienda acumular de 3 a 6 meses de gastos.
                                <div class="absolute -bottom-1.5 right-1 w-3 h-3 bg-surface-dark rotate-45 rounded-sm"></div>
                            </div>
                        </div>
                    </div>
                    <select id="fondo_emergencia_pct"
                            x-model="fondo_emergencia_pct"
                            class="w-full bg-surface-white border border-surface-light rounded-xl px-3 py-2.5 text-sm text-surface-dark outline-none focus:border-brand-primary transition-colors">
                        <option value="0">0% (Sin reserva para emergencias)</option>
                        <option value="5">5% de tus ingresos netos</option>
                        <option value="10">10% de tus ingresos netos (Altamente Recomendado)</option>
                        <option value="15">15% de tus ingresos netos</option>
                    </select>
                </div>
            </div>

            {{-- Errores y botón calcular --}}
            <div role="alert" x-show="error" class="mb-3">
                <p class="text-xs text-red-500 flex items-center gap-1 font-semibold">
                    <span class="icon icon-sm">error</span><span x-text="error"></span>
                </p>
            </div>

            <button type="button" @click="calcular()" :disabled="cargando"
                    class="w-full inline-flex items-center justify-center gap-2 bg-brand-primary text-white
                           text-sm font-medium py-3 rounded-xl hover:bg-brand-secondary transition-colors
                           disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                <span class="icon icon-sm" x-show="!cargando">calculate</span>
                <span class="icon icon-sm animate-spin" x-show="cargando">progress_activity</span>
                <span x-text="cargando ? 'Analizando tu flujo financiero...' : 'Calcular Salud Financiera'"></span>
            </button>
        </div>
    </div>

    {{-- ===== COLUMNA RESULTADOS Y DIAGNÓSTICOS (3/5) ===== --}}
    <div class="lg:col-span-3 space-y-4" role="region" aria-live="polite">

        <div x-show="!mostrar"
             class="bg-surface-light/30 rounded-2xl p-8 border border-dashed border-surface-light
                    flex flex-col items-center justify-center gap-3 min-h-64 text-center">
            <span class="icon icon-xl text-surface-light" style="font-size: 38px;">account_balance</span>
            <p class="text-sm font-semibold text-surface-dark mt-1">¿Cuánto dinero es realmente mío?</p>
            <p class="text-xs text-surface-medium max-w-sm">
                Ingresa tu cobro bruto, comisiones y gastos operativos en la columna izquierda para calcular qué porcentaje real de tu dinero llega libre a tu bolsillo.
            </p>
        </div>

        <template x-if="mostrar && resultado">
            <div class="fade-in space-y-4">

                {{-- 1. Barra de Eficiencia de Cobro --}}
                <div class="bg-white border border-surface-light rounded-2xl p-5 card-shadow">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-surface-medium mb-0.5">Eficiencia de cobro internacional</p>
                            <h3 class="text-lg font-bold text-surface-dark flex items-center gap-1.5">
                                <span class="capitalize" x-text="resultado.eficiencia.rango"></span>
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                                      :class="{
                                          'bg-[#E1F5EE] text-[#085041]': resultado.eficiencia.rango === 'excelente',
                                          'bg-[#E6F1FB] text-[#185FA5]': resultado.eficiencia.rango === 'bueno',
                                          'bg-[#FAEEDA] text-[#854F0B]': resultado.eficiencia.rango === 'mejorable',
                                          'bg-[#FCEBEB] text-[#A32D2D]': resultado.eficiencia.rango === 'costoso',
                                      }"
                                      x-text="resultado.eficiencia.porcentaje + '%'">
                                </span>
                            </h3>
                            <p class="text-xs text-surface-medium mt-1" x-text="resultado.eficiencia.mensaje"></p>
                        </div>
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                             :class="{
                                 'bg-[#E1F5EE]': resultado.eficiencia.rango === 'excelente',
                                 'bg-[#E6F1FB]': resultado.eficiencia.rango === 'bueno',
                                 'bg-[#FAEEDA]': resultado.eficiencia.rango === 'mejorable',
                                 'bg-[#FCEBEB]': resultado.eficiencia.rango === 'costoso',
                             }">
                            <span class="icon text-base"
                                  :class="{
                                      'text-[#085041]': resultado.eficiencia.rango === 'excelente',
                                      'text-[#185FA5]': resultado.eficiencia.rango === 'bueno',
                                      'text-[#854F0B]': resultado.eficiencia.rango === 'mejorable',
                                      'text-[#A32D2D]': resultado.eficiencia.rango === 'costoso',
                                  }"
                                  x-text="resultado.eficiencia.rango === 'excelente' ? 'thumb_up' : (resultado.eficiencia.rango === 'bueno' ? 'check_circle' : 'warning')">
                            </span>
                        </div>
                    </div>

                    {{-- Barra progreso lineal --}}
                    <div class="mt-3.5">
                        <div class="w-full bg-surface-light/40 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-700"
                                 :class="{
                                     'bg-[#0F6E56]': resultado.eficiencia.rango === 'excelente',
                                     'bg-[#185FA5]': resultado.eficiencia.rango === 'bueno',
                                     'bg-[#854F0B]': resultado.eficiencia.rango === 'mejorable',
                                     'bg-[#A32D2D]': resultado.eficiencia.rango === 'costoso',
                                 }"
                                 :style="'width: ' + resultado.eficiencia.porcentaje + '%'"></div>
                        </div>
                        <div class="flex justify-between text-[9px] font-medium text-surface-medium/60 mt-1 uppercase tracking-wider">
                            <span>Costoso (<90%)</span>
                            <span>Mejorable (90-93%)</span>
                            <span>Bueno (94-96%)</span>
                            <span>Excelente (97%+)</span>
                        </div>
                    </div>
                </div>

                {{-- Banner Educativo Ahorro --}}
                <template x-if="resultado.eficiencia.ahorro_potencial > 0">
                    <div class="bg-[#E1F5EE] border border-[#9DBFBF] rounded-2xl p-4 flex items-start gap-3">
                        <span class="icon text-[#0F6E56] mt-0.5">lightbulb</span>
                        <div>
                            <p class="text-xs font-bold text-[#085041]">💡 Recomendación de ahorro potencial</p>
                            <p class="text-xs text-[#085041]/90 mt-0.5" x-text="resultado.eficiencia.ahorro_mensaje"></p>
                            <button type="button" @click="metodo_cobro = 'wise'; actualizarComisionesDefault(); calcular();" class="text-xs font-semibold text-[#0F6E56] underline mt-1.5 hover:text-[#085041]">
                                Simular cobro con Wise ahora
                            </button>
                        </div>
                    </div>
                </template>

                {{-- 2. Diagnóstico de Salud Financiera --}}
                <div :class="{
                         'bg-[#E1F5EE] border-[#9DBFBF]': resultado.salud_financiera.estado === 'excelente',
                         'bg-[#E6F1FB] border-[#7CC0E4]': resultado.salud_financiera.estado === 'saludable',
                         'bg-[#FAEEDA] border-[#F0C97A]': resultado.salud_financiera.estado === 'riesgo_moderado',
                         'bg-[#FCEBEB] border-[#E8A8A8]': resultado.salud_financiera.estado === 'riesgo_alto',
                     }"
                     class="rounded-2xl p-5 border">
                    <div class="flex items-start gap-3">
                        <span class="icon text-xl mt-0.5"
                              :class="{
                                  'text-[#085041]': resultado.salud_financiera.estado === 'excelente',
                                  'text-[#185FA5]': resultado.salud_financiera.estado === 'saludable',
                                  'text-[#854F0B]': resultado.salud_financiera.estado === 'riesgo_moderado',
                                  'text-[#A32D2D]': resultado.salud_financiera.estado === 'riesgo_alto',
                              }"
                              x-text="resultado.salud_financiera.estado === 'excelente' ? 'verified' : (resultado.salud_financiera.estado === 'saludable' ? 'sentiment_satisfied' : 'report')">
                        </span>
                        <div>
                            <p class="text-[10px] font-semibold text-surface-medium uppercase tracking-wider">Salud Financiera Freelance</p>
                            <h4 class="text-base font-bold text-surface-dark capitalize mb-1"
                                x-text="resultado.salud_financiera.estado.replace('_', ' ')"></h4>
                            <p class="text-xs text-surface-medium leading-relaxed" x-text="resultado.salud_financiera.mensaje"></p>
                        </div>
                    </div>
                </div>

                {{-- 3. Desglose detallado del Flujo de Fondos --}}
                <div class="bg-white border border-surface-light rounded-2xl p-5 card-shadow space-y-4">
                    <p class="text-xs font-semibold text-surface-dark flex items-center gap-1.5 border-b border-surface-light pb-2.5">
                        <span class="icon text-brand-primary">account_tree</span>
                        Desglose de tu flujo mensual
                    </p>

                    <div class="space-y-3 text-xs">
                        {{-- Bruto Facturado --}}
                        <div class="flex justify-between items-center font-medium">
                            <span class="text-surface-dark">Monto bruto facturado</span>
                            <span class="text-surface-dark font-semibold" x-text="'$' + resultado.monto_facturado.toFixed(2)"></span>
                        </div>

                        {{-- Comisiones Invisibles --}}
                        <div class="space-y-1.5 pl-3 border-l-2 border-surface-light">
                            <div class="flex justify-between items-center text-surface-medium">
                                <span>Fee Plataforma (<span class="uppercase" x-text="metodo_cobro"></span>)</span>
                                <span class="text-red-500" x-text="'−$' + resultado.comisiones.plataforma.toFixed(2)"></span>
                            </div>
                            <template x-if="resultado.comisiones.iva > 0">
                                <div class="flex justify-between items-center text-surface-medium">
                                    <span>IVA 13% sobre comisión</span>
                                    <span class="text-red-500" x-text="'−$' + resultado.comisiones.iva.toFixed(2)"></span>
                                </div>
                            </template>
                            <template x-if="resultado.comisiones.corresponsal > 0">
                                <div class="flex justify-between items-center text-surface-medium">
                                    <span>Banco Corresponsal (SWIFT)</span>
                                    <span class="text-red-500" x-text="'−$' + resultado.comisiones.corresponsal.toFixed(2)"></span>
                                </div>
                            </template>
                            <div class="flex justify-between items-center text-surface-medium">
                                <span>Retiro a banco salvadoreño</span>
                                <span class="text-red-500" x-text="'−$' + resultado.comisiones.banco.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between items-center font-semibold text-surface-dark pt-1">
                                <span>Total comisiones de cobro</span>
                                <span class="text-red-500" x-text="'−$' + resultado.comisiones.total.toFixed(2)"></span>
                            </div>
                        </div>

                        {{-- Neto Recibido --}}
                        <div class="flex justify-between items-center font-bold bg-surface-light/30 rounded-lg p-2">
                            <span class="text-brand-primary">Neto Recibido en Cuenta</span>
                            <span class="text-brand-primary" x-text="'$' + resultado.neto_recibido.toFixed(2)"></span>
                        </div>

                        {{-- Gastos Operativos --}}
                        <div class="space-y-1.5 pl-3 border-l-2 border-surface-light">
                            <div class="flex justify-between items-center text-surface-medium font-semibold">
                                <span>Total gastos operativos</span>
                                <span class="text-red-500" x-text="'−$' + resultado.gastos_operativos.total.toFixed(2)"></span>
                            </div>
                            <template x-for="g in resultado.gastos_operativos.detalle">
                                <div class="flex justify-between items-center text-[11px] text-surface-medium/80 italic">
                                    <span x-text="g.concepto"></span>
                                    <span x-text="'$' + g.monto.toFixed(2)"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Utilidad Operativa --}}
                        <div class="flex justify-between items-center font-semibold text-surface-dark">
                            <span>Utilidad Operativa</span>
                            <span x-text="'$' + resultado.utilidad_operativa.toFixed(2)"></span>
                        </div>

                        {{-- Reservas Formales y Seguro --}}
                        <div class="space-y-1.5 pl-3 border-l-2 border-surface-light">
                            <div class="flex justify-between items-center text-surface-medium" x-data="{ tooltip: false }">
                                <div class="flex items-center gap-1 relative">
                                    <span>Reserva ISR sugerida</span>
                                    <button type="button" @click="tooltip = !tooltip" @click.away="tooltip = false" class="text-brand-primary hover:text-brand-secondary">
                                        <span class="icon text-[10px]">help</span>
                                    </button>
                                    <!-- Tooltip Popover -->
                                    <div x-show="tooltip" 
                                         x-transition
                                         class="absolute z-30 bottom-6 left-0 w-64 bg-surface-dark text-white text-[10px] leading-relaxed rounded-xl p-3 shadow-xl"
                                         style="display: none;">
                                        ISR estimado proyectando tu utilidad mensual al año completo. Basado en los tramos oficiales de la Ley de Impuesto sobre la Renta (Art. 37).
                                        <div class="absolute -bottom-1.5 left-2 w-3 h-3 bg-surface-dark rotate-45 rounded-sm"></div>
                                    </div>
                                </div>
                                <span class="text-red-500" x-text="'−$' + resultado.isr.mensual_sugerido.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between items-center text-[10px] text-surface-medium/70 italic -mt-1">
                                <span>¿Reserva activa en tus cálculos?</span>
                                <span class="font-semibold" :class="resultado.isr.activo ? 'text-[#0F6E56]' : 'text-red-500'" x-text="resultado.isr.activo ? 'Sí' : 'No (Riesgo fiscal)'"></span>
                            </div>
                            
                            <template x-if="resultado.isss.monto > 0">
                                <div class="flex justify-between items-center text-surface-medium">
                                    <span>Seguro ISSS (<span x-text="resultado.isss.tipo"></span>)</span>
                                    <span class="text-red-500" x-text="'−$' + resultado.isss.monto.toFixed(2)"></span>
                                </div>
                            </template>
                            
                            <template x-if="resultado.fondo_emergencia.monto > 0">
                                <div class="flex justify-between items-center text-surface-medium">
                                    <span>Fondo de Emergencia (<span x-text="resultado.fondo_emergencia.porcentaje"></span>%)</span>
                                    <span class="text-red-500" x-text="'−$' + resultado.fondo_emergencia.monto.toFixed(2)"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Disponible para retiro --}}
                        <div class="flex justify-between items-center font-extrabold bg-[#E6F1FB] text-[#042C53] rounded-xl p-3.5 text-sm border border-[#7CC0E4]/30">
                            <span>Disponible para Retiro Seguro</span>
                            <span x-text="'$' + resultado.dinero_disponible.toFixed(2)"></span>
                        </div>
                    </div>

                    {{-- Nota aclaratoria --}}
                    <div class="text-[10px] text-surface-medium leading-relaxed italic border-t border-surface-light pt-2.5 flex items-start gap-1">
                        <span class="icon text-xs mt-0.5">info</span>
                        El disponible es lo que puedes retirar para tus gastos personales sin descapitalizar el negocio y estando al día con Hacienda, ISSS y reservas.
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
