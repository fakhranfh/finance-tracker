@props(['tableId', 'column', 'label', 'align' => 'left'])

<th class="px-space-lg py-space-sm font-label-md text-label-md text-secondary uppercase {{ $align === 'right' ? 'text-right' : '' }}">
    <button type="button" onclick="toggleSort('{{ $tableId }}', '{{ $column }}')"
        class="inline-flex items-center gap-space-xxs {{ $align === 'right' ? 'flex-row-reverse' : '' }} hover:text-on-surface transition-colors">
        <span>{{ $label }}</span>
        <span class="material-symbols-outlined text-[16px] leading-none" data-sort-icon="{{ $column }}"></span>
    </button>
</th>
