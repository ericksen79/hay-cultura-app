{{--
| Componente: input-field
| Parámetros:
|   $id, $label, $type (default: number), $placeholder, $prefix
|   $hint    → siempre visible debajo del campo
|   $helper  → aparece al enfocar el campo (💡)
|   $tooltip → aparece al hacer clic en (?)
--}}
<div class="mb-4" x-data="{ enfocado: false, tooltip: false }">

    {{-- Etiqueta + botón tooltip --}}
    <div class="flex items-center gap-1.5 mb-1">
        <label for="{{ $id }}" class="text-xs font-medium text-surface-dark">
            {{ $label }}
        </label>

        @if(isset($tooltip))
            <div class="relative">
                <button type="button"
                        @click="tooltip = !tooltip"
                        @click.away="tooltip = false"
                        class="w-4 h-4 rounded-full bg-brand-primary/15 text-brand-primary
                               flex items-center justify-center hover:bg-brand-primary/25 transition-colors"
                        aria-label="Más información">
                    <span class="icon text-[11px]">help</span>
                </button>
                <div x-show="tooltip"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute z-20 bottom-6 left-0 w-64 bg-surface-dark text-white
                            text-xs leading-relaxed rounded-xl p-3 shadow-lg">
                    {{ $tooltip }}
                    <div class="absolute -bottom-1.5 left-3 w-3 h-3 bg-surface-dark rotate-45 rounded-sm"></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Input --}}
    <div class="flex items-center bg-white border rounded-xl transition-colors"
         :class="enfocado
             ? 'border-brand-primary'
             : 'border-surface-light hover:border-brand-light'">
        @if(isset($prefix))
            <span class="pl-3 text-sm text-surface-medium select-none">{{ $prefix }}</span>
        @endif
        <input type="{{ $type ?? 'number' }}"
               id="{{ $id }}"
               name="{{ $id }}"
               placeholder="{{ $placeholder ?? '0.00' }}"
               @if(($type ?? 'number') === 'number') min="0" step="0.01" @endif
               @focus="enfocado = true"
               @blur="enfocado = false"
               class="flex-1 px-3 py-2.5 text-sm text-surface-dark bg-transparent
                      outline-none placeholder-surface-light/70
                      [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none
                      [&::-webkit-inner-spin-button]:appearance-none">
    </div>

    {{-- Textos de ayuda --}}
    <div class="mt-1.5 space-y-1">
        @if(isset($hint))
            <p class="text-xs text-surface-medium flex items-start gap-1">
                <span class="icon icon-sm text-surface-light mt-0.5 flex-shrink-0">remove</span>
                {{ $hint }}
            </p>
        @endif
        @if(isset($helper))
            <p x-show="enfocado"
               x-transition:enter="transition ease-out duration-150"
               x-transition:enter-start="opacity-0 -translate-y-1"
               x-transition:enter-end="opacity-100 translate-y-0"
               class="text-xs text-brand-secondary flex items-start gap-1">
                <span class="icon icon-sm flex-shrink-0 mt-0.5">lightbulb</span>
                {{ $helper }}
            </p>
        @endif
    </div>

</div>