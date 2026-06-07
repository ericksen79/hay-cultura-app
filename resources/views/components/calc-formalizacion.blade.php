<div x-data="{
    preguntas: [
        { id: 'nit', text: '¿Tienes tu NIT o DUI unificado y homologado en Hacienda?', desc: 'Es el paso fundamental para poder emitir facturas y realizar declaraciones fiscales en El Salvador.', check: false },
        { id: 'facturas', text: '¿Emites facturas legales (como FEX o de Sujeto Excluido)?', desc: 'Si trabajas con clientes en el exterior, debes emitir Facturas de Exportación (FEX) autorizadas por Hacienda. Si es local, Facturas de Sujeto Excluido.', check: false },
        { id: 'declaras', text: '¿Declaras tu Impuesto sobre la Renta (ISR) anualmente?', desc: 'Declarar tu impuesto anual (F11) antes del 30 de abril es obligatorio por ley para todo profesional independiente.', check: false },
        { id: 'cuenta', text: '¿Utilizas una cuenta bancaria separada exclusiva para tu negocio?', desc: 'Evita mezclar finanzas personales con profesionales. Te dará claridad sobre tus ingresos y gastos reales.', check: false },
        { id: 'reserva', text: '¿Guardas una reserva preventiva mensual para tus impuestos?', desc: 'Separar el ISR estimado mes a mes te previene de deudas masivas y apuros de caja al finalizar el período fiscal.', check: false },
        { id: 'isss', text: '¿Cotizas al Régimen Especial de ISSS Independiente?', desc: 'Cotizar al ISSS (Individual de $40 o Familiar de $56) te da acceso a cobertura de salud y seguridad social.', check: false },
        { id: 'gastos', text: '¿Registras y archivas tus facturas de gastos operativos?', desc: 'Llevar un control ordenado de tus gastos (suscripciones, hosting, internet, equipos) te permite deducirlos de tu ISR legalmente.', check: false }
    ],

    obtenerPuntaje() {
        const checkeds = this.preguntas.filter(p => p.check).length;
        return Math.round((checkeds / this.preguntas.length) * 100);
    },

    obtenerEstado() {
        const score = this.obtenerPuntaje();
        if (score <= 25) return {
            titulo: 'Informal',
            rango: '0-25',
            color: 'red',
            badge: 'bg-red-50 text-red-700 border-red-200',
            mensaje: 'Tu actividad profesional opera en la informalidad total. Esto te expone a riesgos de fiscalización del Ministerio de Hacienda, dificulta justificar tus ingresos ante bancos y te impide acceder a financiamientos o créditos para vivienda o crecimiento comercial.',
            consejos: [
                'Homologa tu NIT con tu DUI asistiendo a Hacienda o mediante su portal digital.',
                'Abre una cuenta bancaria de ahorros separada que utilices exclusivamente para recibir tus cobros y pagar tus herramientas de trabajo.',
                'Empieza a registrar en un cuaderno o excel cada dólar que gastas en internet, licencias de software u oficina.'
            ]
        };
        if (score <= 50) return {
            titulo: 'En transición',
            rango: '26-50',
            color: 'amber',
            badge: 'bg-amber-50 text-amber-700 border-amber-200',
            mensaje: 'Estás dando los primeros pasos para organizarte, pero aún tienes vacíos legales y financieros críticos. Formalizar tus facturas y separar de forma preventiva tus impuestos te dará gran tranquilidad frente a Hacienda y aumentará tu perfil profesional ante grandes clientes corporativos.',
            consejos: [
                'Asesórate con un contador para inscribirte en el régimen de Factura de Exportación (FEX) si tus clientes están fuera.',
                'Implementa la regla de guardar preventivamente del 10% al 15% de tu utilidad del mes para impuestos.',
                'Evita pagar gastos personales (como comida o salidas) desde la cuenta de tu freelance.'
            ]
        };
        if (score <= 75) return {
            titulo: 'Organizado',
            rango: '51-75',
            color: 'blue',
            badge: 'bg-blue-50 text-blue-700 border-blue-200',
            mensaje: '¡Buen trabajo! Cuentas con un orden financiero admirable y cumples con varios requisitos formales. Considera oficializar tus facturas de exportación para clientes extranjeros y cotizar al ISSS independiente para blindar tu salud familiar.',
            consejos: [
                'Inscríbete en el régimen de Trabajador Independiente del ISSS en su oficina administrativa para asegurar a tu familia de forma voluntaria.',
                'Utiliza un clasificador de gastos para asegurar que lo que declaras como deducible cumple plenamente con el Art. 29 de la LISR.',
                'Empieza a considerar la formalización mercantil completa para emitir Factura Electrónica (DTE), lo cual proyectará tu negocio a otro nivel.'
            ]
        };
        return {
            titulo: 'Profesional Formalizado',
            rango: '76-100',
            color: 'green',
            badge: 'bg-green-50 text-green-700 border-green-200',
            mensaje: '¡Felicidades! Operas como un verdadero profesional formalizado en El Salvador. Tienes tus cuentas completamente estructuradas, tus impuestos previstos y cuentas con el respaldo legal formal del ISSS. Estás listo para solicitar financiamientos bancarios, licitar con grandes empresas y hacer crecer tu negocio al siguiente nivel.',
            consejos: [
                'Implementa la facturación electrónica salvadoreña (DTE) para automatizar tu emisión fiscal.',
                'Revisa con tu banco la posibilidad de acceder a tasas preferenciales de crédito para inversión productiva o hipoteca de local.',
                '¡Continúa así y comparte estas buenas prácticas con otros colegas freelancers!'
            ]
        };
    },

    reiniciar() {
        this.preguntas.forEach(p => {
            p.check = false;
            this.notificarCambio(p.id, false);
        });
    },

    notificarCambio(id, checked) {
        window.dispatchEvent(new CustomEvent('formalizacion-cambiado', {
            detail: { id: id, checked: checked }
        }));
    },

    init() {
        window.addEventListener('freelancer-actualizado', (e) => {
            if (e.detail) {
                // Sync declaras and reserva questions
                const qDeclaras = this.preguntas.find(p => p.id === 'declaras');
                if (qDeclaras) qDeclaras.check = e.detail.inscrito_hacienda;

                const qReserva = this.preguntas.find(p => p.id === 'reserva');
                if (qReserva) qReserva.check = e.detail.inscrito_hacienda;

                // Sync isss question
                const qIsss = this.preguntas.find(p => p.id === 'isss');
                if (qIsss) qIsss.check = (e.detail.cotiza_isss !== 'no');

                // Sync gastos question
                const qGastos = this.preguntas.find(p => p.id === 'gastos');
                if (qGastos) qGastos.check = e.detail.gastos_activos;
            }
        });
    }
}">

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- ===== CHECKLIST DE PREGUNTAS (3/5) ===== --}}
    <div class="lg:col-span-3 bg-white rounded-2xl p-6 border border-surface-light card-shadow space-y-4">
        
        <div class="flex items-center justify-between border-b border-surface-light pb-3">
            <div class="flex items-center gap-2">
                <span class="icon icon-lg text-brand-primary">fact_check</span>
                <div>
                    <h2 class="text-base font-semibold text-surface-dark">Test de Formalización</h2>
                    <p class="text-xs text-surface-medium">Evalúa tu nivel de formalidad legal y financiera</p>
                </div>
            </div>
            <button type="button" @click="reiniciar()" class="text-xs text-surface-medium hover:text-brand-primary inline-flex items-center gap-0.5">
                <span class="icon text-xs">restart_alt</span> Reiniciar
            </button>
        </div>

        <div class="space-y-4 pt-1">
            <template x-for="(pregunta, index) in preguntas" :key="pregunta.id">
                <label class="flex items-start gap-3.5 p-3 rounded-xl hover:bg-surface-light/20 cursor-pointer transition-colors border border-transparent hover:border-surface-light/40">
                    <input type="checkbox"
                           x-model="pregunta.check"
                           @change="notificarCambio(pregunta.id, pregunta.check)"
                           class="rounded text-brand-primary focus:ring-brand-primary w-4.5 h-4.5 mt-0.5 flex-shrink-0">
                    <div>
                        <span class="text-xs font-semibold text-surface-dark" x-text="pregunta.text"></span>
                        <p class="text-[11px] text-surface-medium mt-1 leading-relaxed" x-text="pregunta.desc"></p>
                    </div>
                </label>
            </template>
        </div>
    </div>

    {{-- ===== DIAGNÓSTICO E ÍNDICE (2/5) ===== --}}
    <div class="lg:col-span-2 space-y-4" role="region" aria-live="polite">

        {{-- Tarjeta de Índice --}}
        <div class="bg-white border border-surface-light rounded-2xl p-6 card-shadow text-center space-y-3">
            <p class="text-xs font-bold text-surface-medium uppercase tracking-wider">Índice de Formalización</p>
            
            {{-- Número y Círculo --}}
            <div class="relative w-28 h-28 mx-auto flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="56" cy="56" r="48" stroke="currentColor" stroke-width="8" class="text-surface-light/40" fill="transparent" />
                    <circle cx="56" cy="56" r="48" stroke="currentColor" stroke-width="8" class="transition-all duration-500"
                            :class="{
                                'text-red-500': obtenerPuntaje() <= 25,
                                'text-amber-500': obtenerPuntaje() > 25 && obtenerPuntaje() <= 50,
                                'text-blue-500': obtenerPuntaje() > 50 && obtenerPuntaje() <= 75,
                                'text-green-500': obtenerPuntaje() > 75
                            }"
                            :stroke-dasharray="2 * Math.PI * 48"
                            :stroke-dashoffset="2 * Math.PI * 48 * (1 - obtenerPuntaje() / 100)"
                            fill="transparent" />
                </svg>
                <div class="absolute text-center">
                    <span class="text-3xl font-extrabold text-surface-dark" x-text="obtenerPuntaje()"></span>
                    <span class="text-xs text-surface-medium block">/100</span>
                </div>
            </div>

            {{-- Estado Badge --}}
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-semibold uppercase tracking-wider"
                 :class="obtenerEstado().badge">
                <span class="w-2 h-2 rounded-full"
                      :class="{
                          'bg-red-500': obtenerEstado().color === 'red',
                          'bg-amber-500': obtenerEstado().color === 'amber',
                          'bg-blue-500': obtenerEstado().color === 'blue',
                          'bg-green-500': obtenerEstado().color === 'green'
                      }"></span>
                <span x-text="obtenerEstado().titulo"></span>
            </div>
        </div>

        {{-- Diagnóstico y Recomendaciones --}}
        <div class="bg-white border border-surface-light rounded-2xl p-5 card-shadow space-y-4">
            <div>
                <p class="text-[10px] font-bold text-surface-medium uppercase tracking-wider mb-1">Diagnóstico situacional</p>
                <p class="text-xs text-surface-medium leading-relaxed" x-text="obtenerEstado().mensaje"></p>
            </div>

            <div class="border-t border-surface-light pt-4">
                <p class="text-[10px] font-bold text-surface-medium uppercase tracking-wider mb-2">Pasos recomendados para avanzar</p>
                <ul class="space-y-2 text-xs">
                    <template x-for="(consejo, idx) in obtenerEstado().consejos" :key="idx">
                        <li class="flex items-start gap-2 text-surface-medium leading-relaxed">
                            <span class="icon text-xs text-brand-primary mt-0.5">arrow_forward</span>
                            <span x-text="consejo"></span>
                        </li>
                    </template>
                </ul>
            </div>

            {{-- Botón a Aprender más --}}
            <a href="{{ route('referencias') }}" class="w-full inline-flex items-center justify-center gap-2 border border-brand-primary/20 text-brand-primary text-xs font-semibold py-2.5 rounded-xl hover:bg-brand-primary/5 transition-colors">
                <span class="icon text-xs">open_in_new</span> Ver Guías y Base Legal de El Salvador
            </a>
        </div>
    </div>
</div>
</div>
