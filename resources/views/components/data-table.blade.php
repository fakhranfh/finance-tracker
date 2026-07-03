@props(['title' => null, 'tableId' => 'dataTable', 'listUrl' => '#', 'filters' => [], 'sort' => null, 'dir' => 'desc'])

@if (!empty($filters))
    <div class="bg-surface border border-outline-variant rounded-lg p-space-lg" id="{{ $tableId }}-filters">
        <div class="flex flex-wrap gap-space-md items-end">
            @foreach ($filters as $filter)
                @if ($filter['type'] === 'text')
                    <div class="flex-1 min-w-[160px]">
                        <label class="block font-label-md text-label-md text-secondary mb-space-xs">
                            {{ __($filter['label']) }}
                        </label>
                        <input
                            type="text"
                            data-filter-table="{{ $tableId }}"
                            data-filter-key="{{ $filter['key'] }}"
                            placeholder="{{ __('Search') }} {{ __($filter['label']) }}..."
                            class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                    </div>
                @elseif ($filter['type'] === 'enum')
                    <div class="min-w-[160px]">
                        <label class="block font-label-md text-label-md text-secondary mb-space-xs">
                            {{ __($filter['label']) }}
                        </label>
                        <select
                            data-filter-table="{{ $tableId }}"
                            data-filter-key="{{ $filter['key'] }}"
                            class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($filter['options'] as $value => $label)
                                <option value="{{ $value }}">{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif ($filter['type'] === 'datetime')
                    <div class="min-w-[160px]">
                        <label class="block font-label-md text-label-md text-secondary mb-space-xs">
                            {{ __($filter['label']) }} {{ __('From') }}
                        </label>
                        <input
                            type="date"
                            data-filter-table="{{ $tableId }}"
                            data-filter-key="{{ $filter['key'] }}_from"
                            class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                    </div>
                    <div class="min-w-[160px]">
                        <label class="block font-label-md text-label-md text-secondary mb-space-xs">
                            {{ __($filter['label']) }} {{ __('To') }}
                        </label>
                        <input
                            type="date"
                            data-filter-table="{{ $tableId }}"
                            data-filter-key="{{ $filter['key'] }}_to"
                            class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                    </div>
                @endif
            @endforeach
            <div class="flex gap-space-sm">
                <button type="button" onclick="applyFilters('{{ $tableId }}')"
                    class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                    {{ __('Filter') }}
                </button>
                <button type="button" onclick="resetFilters('{{ $tableId }}')"
                    class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                    {{ __('Reset') }}
                </button>
            </div>
        </div>
    </div>
@endif

<!-- Data Table Card -->
<div class="bg-surface border border-outline-variant rounded-lg overflow-hidden">
    @if ($title)
        <div class="px-space-lg py-space-md border-b border-outline-variant">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">
                {{ $title }}
            </h3>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left" id="{{ $tableId }}" data-list-url="{{ $listUrl }}"
            @if ($sort) data-sort="{{ $sort }}" data-dir="{{ $dir }}" @endif>
            <thead class="bg-surface-container-lowest border-b border-outline-variant">
                <tr>
                    {{ $headers }}
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                <!-- Data will be loaded here -->
            </tbody>
        </table>
    </div>
</div>
