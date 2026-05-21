@extends('layouts.app')

@section('title', 'Sobre Hay Cultura App | Finanzas e impuestos para freelancers y PYMEs en El Salvador')
@section('meta_description', 'Conoce Hay Cultura App, una plataforma diseñada para ayudar a freelancers, emprendedores y pequeñas empresas salvadoreñas a entender sus finanzas, calcular impuestos y tomar mejores decisiones financieras.')
@section('og_title', 'Hay Cultura App | Finanzas claras para freelancers y PYMEs')
@section('og_description', 'Herramientas y calculadoras financieras diseñadas para ayudar a emprendedores salvadoreños a entender utilidad, IVA, ISR y salud financiera de forma simple.')

@section('content')

<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-16 text-center">
    <span class="inline-flex items-center gap-1.5 bg-brand-primary/10 text-brand-primary
                 text-xs font-medium px-3 py-1 rounded-full mb-5">
        <span class="icon icon-sm">info</span>
        Sobre Nosotros
    </span>

    <h1 class="text-3xl sm:text-4xl font-semibold text-surface-dark leading-tight max-w-3xl mx-auto mb-6">
        Finanzas e impuestos para freelancers y PYMEs en El Salvador
    </h1>

    <div class="max-w-2xl mx-auto bg-white rounded-2xl p-8 card-shadow border border-surface-light text-left mt-8">
        <p class="text-surface-medium text-base mb-6 leading-relaxed">
            Nuestra plataforma está diseñada para ayudar a freelancers, emprendedores y pequeñas empresas salvadoreñas a entender sus finanzas, calcular impuestos y tomar mejores decisiones financieras.
        </p>

        <p class="text-surface-medium text-base mb-6 leading-relaxed">
            La idea nace de la necesidad de hacer que conceptos fiscales complejos, como el Impuesto Sobre la Renta (ISR), el IVA, la deducción de gastos y la salud financiera en general, sean fáciles de entender sin utilizar un lenguaje contable difícil de procesar.
        </p>

        <p class="text-surface-medium text-base mb-8 leading-relaxed">
            En <strong>Hay Cultura App</strong>, creemos que comprender tus números es parte fundamental para hacer crecer tu negocio con seguridad y transparencia.
        </p>

        <div class="flex flex-col sm:flex-row items-center gap-4">
            <a href="{{ route('calculadoras') }}"
               class="inline-flex items-center gap-2 bg-brand-primary text-white font-medium
                      px-6 py-3 rounded-xl hover:bg-brand-secondary transition-colors text-sm
                      w-full sm:w-auto justify-center">
                <span class="icon icon-sm">calculate</span>
                Ir a las Calculadoras
            </a>
            <a href="{{ route('referencias') }}"
               class="inline-flex items-center gap-2 border border-brand-primary/30 text-brand-primary
                      font-medium px-6 py-3 rounded-xl hover:bg-brand-primary/5 transition-colors
                      text-sm w-full sm:w-auto justify-center">
                <span class="icon icon-sm">school</span>
                Centro de Aprendizaje
            </a>
        </div>
    </div>
</section>

@endsection
