@extends('layouts.app')

@section('title', 'Referencias fiscales y educación financiera en El Salvador | Hay Cultura App')
@section('meta_description', 'Aprende cómo funcionan el ISR, IVA, gastos deducibles y otros conceptos fiscales en El Salvador con explicaciones simples y referencias legales para freelancers y pequeñas empresas.')
@section('og_title', 'Centro educativo fiscal | Hay Cultura App')
@section('og_description', 'Explora referencias legales, conceptos fiscales y educación financiera aplicada a freelancers, autónomos y PYMEs salvadoreñas.')

@section('content')

{{-- Hero --}}
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-12 text-center">
    <span class="inline-flex items-center gap-1.5 bg-brand-secondary/10 text-brand-secondary
                 text-xs font-medium px-3 py-1 rounded-full mb-5">
        <span class="icon icon-sm">school</span>
        Centro de aprendizaje práctico
    </span>

    <h1 class="text-3xl sm:text-4xl font-semibold text-surface-dark leading-tight max-w-3xl mx-auto mb-4">
        Aprende a entender tus impuestos y tus finanzas sin lenguaje complicado.
    </h1>

    <p class="text-surface-medium text-base max-w-xl mx-auto mb-8 leading-relaxed">
        Hay Cultura App reúne referencias legales, conceptos financieros y explicaciones prácticas para ayudar a freelancers, emprendedores y pequeñas empresas salvadoreñas a tomar decisiones con mayor claridad.
    </p>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="#conceptos"
           class="inline-flex items-center gap-2 bg-brand-primary text-white font-medium
                  px-6 py-3 rounded-xl hover:bg-brand-secondary transition-colors text-sm
                  w-full sm:w-auto justify-center">
            <span class="icon icon-sm">menu_book</span>
            Explorar conceptos
        </a>
        <a href="#referencias"
           class="inline-flex items-center gap-2 border border-brand-primary/30 text-brand-primary
                  font-medium px-6 py-3 rounded-xl hover:bg-brand-primary/5 transition-colors
                  text-sm w-full sm:w-auto justify-center">
            <span class="icon icon-sm">gavel</span>
            Ver referencias legales
        </a>
    </div>
</section>

{{-- Content (Bento Grid) --}}
<section id="conceptos" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        {{-- Introducción (Ocupa 3 columnas) --}}
        <div class="lg:col-span-3 bg-white rounded-2xl p-6 card-shadow border border-surface-light mb-2">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-brand-primary/10 flex items-center justify-center">
                    <span class="icon text-brand-primary">lightbulb</span>
                </div>
                <h2 class="text-lg font-semibold text-surface-dark">Entender tus números también es parte de hacer crecer tu negocio.</h2>
            </div>
            <p class="text-sm text-surface-medium leading-relaxed mb-3">
                Muchas personas comienzan un negocio, trabajan como freelancers o administran una pequeña empresa sin una guía clara sobre impuestos, utilidad, gastos deducibles o flujo de dinero.
            </p>
            <p class="text-sm text-surface-medium leading-relaxed">
                Nuestro objetivo es transformar conceptos fiscales complejos en herramientas y explicaciones más fáciles de entender, usando referencias basadas en legislación salvadoreña y principios financieros aplicados a negocios reales.
            </p>
        </div>

        {{-- ISR (Ocupa 2 columnas) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 card-shadow border border-surface-light">
            <div class="w-10 h-10 rounded-xl bg-[#E6F1FB] flex items-center justify-center mb-4">
                <span class="icon text-[#3A6E8A]">receipt_long</span>
            </div>
            <h2 class="text-base font-semibold text-surface-dark mb-2">Impuesto Sobre la Renta (ISR)</h2>
            <p class="text-sm text-surface-medium leading-relaxed mb-4">
                El ISR es un impuesto aplicado sobre las ganancias obtenidas por personas naturales y jurídicas. En términos simples, se calcula tomando los ingresos obtenidos y restando costos, gastos deducibles y otras deducciones permitidas por ley para determinar la renta neta imponible.
            </p>
            <p class="text-sm text-surface-medium leading-relaxed mb-4">
                Hay Cultura App utiliza cálculos estimados basados en tablas progresivas y referencias legales salvadoreñas para ayudarte a visualizar una posible carga tributaria anual.
            </p>
            <div class="bg-surface-white p-4 rounded-xl border border-surface-light font-mono text-xs text-surface-dark">
                Renta Neta =<br>
                Renta Obtenida<br>
                − Costos y Gastos Deducibles<br>
                − Otras Deducciones
            </div>
        </div>

        {{-- IVA (Ocupa 1 columna) --}}
        <div class="lg:col-span-1 bg-white rounded-2xl p-6 card-shadow border border-surface-light">
            <div class="w-10 h-10 rounded-xl bg-[#E1F5EE] flex items-center justify-center mb-4">
                <span class="icon text-[#377B7B]">percent</span>
            </div>
            <h2 class="text-base font-semibold text-surface-dark mb-2">¿Cómo funciona el IVA?</h2>
            <p class="text-sm text-surface-medium leading-relaxed mb-4">
                El Impuesto al Valor Agregado (IVA) tiene una tasa general del 13%. Cuando un negocio vende productos o servicios gravados genera débito fiscal. Al mismo tiempo, ciertas compras relacionadas con la actividad económica generan crédito fiscal. La diferencia entre ambos determina el IVA estimado a pagar.
            </p>
            <div class="bg-surface-white p-4 rounded-xl border border-surface-light font-mono text-xs text-surface-dark mt-auto">
                IVA por pagar =<br>
                IVA Débito Fiscal<br>
                − IVA Crédito Fiscal
            </div>
        </div>

        {{-- Gastos Deducibles (Ocupa 3 columnas) --}}
        <div class="lg:col-span-3 bg-white rounded-2xl p-6 card-shadow border border-surface-light mt-2 mb-2">
            <div class="flex flex-col md:flex-row md:items-start gap-6">
                <div class="md:w-1/3">
                    <div class="w-10 h-10 rounded-xl bg-[#E1F5EE] flex items-center justify-center mb-4">
                        <span class="icon text-[#377B7B]">category</span>
                    </div>
                    <h2 class="text-base font-semibold text-surface-dark mb-2">¿Qué significa que un gasto sea deducible?</h2>
                    <p class="text-sm text-surface-medium leading-relaxed">
                        Un gasto deducible es aquel necesario para producir ingresos o mantener la operación del negocio. La ley también establece gastos que no pueden deducirse, especialmente aquellos de carácter personal o que no tengan relación directa con la actividad económica.
                    </p>
                </div>
                <div class="md:w-2/3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="bg-green-50/50 p-4 rounded-xl border border-green-100">
                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="icon text-green-600 text-[18px]">check_circle</span>
                            <h3 class="font-medium text-green-900 text-xs uppercase tracking-wide">Deducibles</h3>
                        </div>
                        <p class="text-xs text-green-800 leading-relaxed">Internet, hosting, materia prima, delivery, herramientas digitales, transporte de trabajo.</p>
                    </div>
                    
                    <div class="bg-yellow-50/50 p-4 rounded-xl border border-yellow-100">
                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="icon text-yellow-600 text-[18px]">warning</span>
                            <h3 class="font-medium text-yellow-900 text-xs uppercase tracking-wide">Revisar contexto</h3>
                        </div>
                        <p class="text-xs text-yellow-800 leading-relaxed">Gasolina, telefonía mixta, viáticos, compras compartidas.</p>
                    </div>

                    <div class="bg-red-50/50 p-4 rounded-xl border border-red-100">
                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="icon text-red-600 text-[18px]">cancel</span>
                            <h3 class="font-medium text-red-900 text-xs uppercase tracking-wide">No deducibles</h3>
                        </div>
                        <p class="text-xs text-red-800 leading-relaxed">Ropa casual, entretenimiento personal, supermercado familiar.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Depreciación (Ocupa 2 columnas) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 card-shadow border border-surface-light">
            <div class="w-10 h-10 rounded-xl bg-[#FAEEDA] flex items-center justify-center mb-4">
                <span class="icon text-[#854F0B]">trending_down</span>
            </div>
            <h2 class="text-base font-semibold text-surface-dark mb-2">¿Qué es la depreciación?</h2>
            <p class="text-sm text-surface-medium leading-relaxed mb-4">
                Algunos bienes utilizados en el negocio pierden valor con el tiempo debido al uso o desgaste. La legislación salvadoreña permite reconocer esta disminución de valor mediante depreciación, normalmente usando el método de línea recta.
            </p>
            <p class="text-sm text-surface-medium leading-relaxed mb-4">
                <strong>Aplica a:</strong> laptops, hornos, vitrinas, cámaras, mobiliario, equipo técnico.
            </p>
            <div class="bg-surface-white p-4 rounded-xl border border-surface-light font-mono text-xs text-surface-dark">
                Depreciación Anual =<br>
                (Costo del activo − Valor residual) ÷ Vida útil
            </div>
        </div>

        {{-- Retiro seguro (Ocupa 1 columna) --}}
        <div class="lg:col-span-1 bg-white rounded-2xl p-6 card-shadow border border-surface-light">
            <div class="w-10 h-10 rounded-xl bg-[#FBEAF0] flex items-center justify-center mb-4">
                <span class="icon text-[#993556]">savings</span>
            </div>
            <h2 class="text-base font-semibold text-surface-dark mb-2">¿Qué es un retiro seguro?</h2>
            <p class="text-sm text-surface-medium leading-relaxed mb-4">
                Uno de los errores más comunes en pequeños negocios es retirar dinero sin considerar impuestos, costos futuros o liquidez mínima.
            </p>
            <p class="text-sm text-surface-medium leading-relaxed">
                La calculadora de retiro seguro busca ayudarte a estimar cuánto dinero podrías retirar sin comprometer la estabilidad financiera básica de tu negocio.
            </p>
        </div>

        {{-- Ingresos (Ocupa 1 columna) --}}
        <div class="lg:col-span-1 bg-white rounded-2xl p-6 card-shadow border border-surface-light mt-2">
            <div class="w-10 h-10 rounded-xl bg-brand-secondary/10 flex items-center justify-center mb-4">
                <span class="icon text-brand-secondary">account_balance_wallet</span>
            </div>
            <h2 class="text-base font-semibold text-surface-dark mb-2">Tipos de ingresos</h2>
            <p class="text-sm text-surface-medium leading-relaxed">
                La legislación salvadoreña contempla diferentes tipos de ingresos sujetos a renta, incluyendo salarios y honorarios, actividades empresariales, alquileres, intereses, utilidades y servicios profesionales.
            </p>
        </div>

        {{-- Persona Natural vs Jurídica (Ocupa 2 columnas) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 card-shadow border border-surface-light mt-2">
            <div class="w-10 h-10 rounded-xl bg-[#E6F1FB] flex items-center justify-center mb-4">
                <span class="icon text-[#3A6E8A]">account_circle</span>
            </div>
            <h2 class="text-base font-semibold text-surface-dark mb-2">Persona Natural vs Jurídica</h2>
            <p class="text-sm text-surface-medium leading-relaxed mb-3">
                En El Salvador, muchas personas comienzan sus actividades económicas como Persona Natural antes de constituir formalmente una sociedad. La categoría seleccionada afecta el cálculo del ISR, obligaciones fiscales, porcentaje de impuestos y responsabilidades contables.
            </p>
            <p class="text-sm text-surface-medium leading-relaxed">
                Si tu negocio no está formalmente constituido como sociedad, normalmente deberías evaluar la opción de Persona Natural con apoyo profesional.
            </p>
        </div>

    </div> {{-- Fin del Grid --}}

    {{-- MYPE --}}
    <div class="bg-white rounded-2xl p-6 card-shadow border border-surface-light mt-6 mb-8 text-center sm:text-left">
        <h2 class="text-lg font-semibold text-surface-dark mb-2">La realidad de las MYPE salvadoreñas</h2>
        <p class="text-sm text-surface-medium leading-relaxed">
            Las micro y pequeñas empresas representan una parte fundamental de la economía salvadoreña y muchas operan enfrentando desafíos relacionados con formalización, educación financiera y acceso a herramientas administrativas. Hay Cultura App busca contribuir a una cultura financiera más accesible, práctica y comprensible para este sector.
        </p>
    </div>

    {{-- Referencias y Disclaimer en dos columnas o una --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4" id="referencias">
        
        {{-- Referencias Legales --}}
        <div class="lg:col-span-2 bg-surface-white rounded-2xl p-6 border border-surface-light">
            <div class="flex items-center gap-2 mb-4">
                <span class="icon text-surface-medium">menu_book</span>
                <h2 class="text-lg font-semibold text-surface-dark">Referencias utilizadas</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-medium text-surface-dark text-sm mb-2">Legislación oficial</h3>
                    <ul class="list-disc pl-4 text-xs text-surface-medium space-y-2">
                        <li><a href="https://transparencia.mh.gob.sv/downloads/pdf/DC5101_19_Ley_de_Impuesto_sobre_la_Renta.pdf" target="_blank" class="text-brand-primary hover:underline">Ley de Impuesto sobre la Renta — Ministerio de Hacienda</a></li>
                        <li><a href="https://www.conamype.gob.sv/temas-2/ley-mype/" target="_blank" class="text-brand-primary hover:underline">CONAMYPE — Ley MYPE</a></li>
                        <li><a href="https://www.conamype.gob.sv/download/ley-de-fomento-proteccion-y-desarrollo-para-la-micro-y-pequena-empresa/" target="_blank" class="text-brand-primary hover:underline">Ley de Fomento Protección y Desarrollo (MYPE)</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-medium text-surface-dark text-sm mb-2">Educación financiera</h3>
                    <ul class="list-disc pl-4 text-xs text-surface-medium space-y-2">
                        <li><a href="https://legalclarity.org/impuestos-en-el-salvador-isr-iva-y-obligaciones-fiscales/" target="_blank" class="text-brand-primary hover:underline">Legal Clarity — ISR, IVA y obligaciones fiscales</a></li>
                        <li><a href="https://www.elsalvador.com/dinero-y-negocios/entorno-economico/pequenas-y-medianas-empresas-el-salvador-economia/1273812/2026/" target="_blank" class="text-brand-primary hover:underline">El Diario de Hoy — Situación de las PYMES</a></li>
                        <li><a href="https://fusades.org/publicaciones/serie_de_investigacion_3-2001__la_pequena_y_mediana_empresa_en_el_salvador__un_potencial_para_el_desarrollo.pdf" target="_blank" class="text-brand-primary hover:underline">FUSADES - La Pyme en El Salvador</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Disclaimer --}}
        <div class="lg:col-span-1 bg-[#FBEAF0]/60 rounded-2xl p-6 border border-[#993556]/20">
            <div class="flex items-center gap-2 mb-3">
                <span class="icon text-[#993556]">info</span>
                <h2 class="text-base font-semibold text-[#993556]">Importante</h2>
            </div>
            <p class="text-xs text-[#993556]/80 leading-relaxed mb-3">
                La información presentada tiene fines educativos. Los cálculos mostrados son estimaciones basadas en referencias legales y escenarios comunes para freelancers en El Salvador.
            </p>
            <p class="text-xs text-[#993556]/80 leading-relaxed">
                Se recomienda consultar con un contador autorizado o asesor tributario antes de presentar declaraciones oficiales o tomar decisiones fiscales importantes.
            </p>
        </div>

    </div>

</section>

{{-- Footer CTA --}}
<section class="border-t border-surface-light bg-surface-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <h2 class="text-2xl sm:text-3xl font-semibold text-surface-dark leading-tight mb-8">
            Comprender tus finanzas también es parte de hacer crecer tu negocio.
        </h2>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('calculadoras') }}"
               class="inline-flex items-center gap-2 bg-brand-primary text-white font-medium
                      px-8 py-3.5 rounded-xl hover:bg-brand-secondary transition-colors text-sm
                      w-full sm:w-auto justify-center">
                <span class="icon icon-sm">calculate</span>
                Ir a calculadoras
            </a>
            <a href="{{ route('home') }}#calculadoras"
               class="inline-flex items-center gap-2 border border-brand-primary/30 text-brand-primary
                      font-medium px-8 py-3.5 rounded-xl hover:bg-brand-primary/5 transition-colors
                      text-sm w-full sm:w-auto justify-center">
                <span class="icon icon-sm">explore</span>
                Explorar herramientas
            </a>
        </div>
    </div>
</section>

@endsection
