@props([
    'id',
    'title' => null,
    'maxWidth' => 'max-w-md',
    'panelClass' => 'bg-surface rounded-lg p-space-lg',
    'close' => null,
    'zIndex' => 'z-50',
])

@php
    $closeJs = $close ?? "document.getElementById('{$id}').classList.add('hidden')";
@endphp

<div id="{{ $id }}" class="hidden fixed inset-0 {{ $zIndex }} flex items-center justify-center p-gutter">
    <div class="absolute inset-0 bg-inverse-surface/40" onclick="{{ $closeJs }}"></div>
    <div {{ $attributes->merge(['class' => "relative w-full {$maxWidth} {$panelClass}"]) }}>
        @if ($title)
            <div class="flex items-center justify-between mb-space-md">
                <h2 class="font-headline-sm text-headline-sm text-on-surface">{{ $title }}</h2>
                <button type="button" onclick="{{ $closeJs }}" class="text-secondary hover:text-on-surface">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
