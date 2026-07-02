<?php

namespace App\Console\Commands\Stubs;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Console\Commands\Helpers\SchemaHelper;

class TailwindBladeShowStubGenerator
{
    public function generate(string $name, string $label): string
    {
        $labelKebab = Str::kebab($label);
        $columns = SchemaHelper::getTableColumns($name);
        $exclude = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'password'];
        $fields = '';

        $modelClass = "App\\Models\\$name";
        $foreignKeys = [];
        if (class_exists($modelClass)) {
            try {
                $model = new $modelClass;
                $table = $model->getTable();
                $connection = Schema::getConnection();
                if (method_exists($connection, 'getDoctrineSchemaManager')) {
                    $sm = $connection->getDoctrineSchemaManager();
                    $doctrineTable = $sm->introspectTable($table);
                    foreach ($doctrineTable->getForeignKeys() as $fk) {
                        foreach ($fk->getLocalColumns() as $localCol) {
                            $foreignKeys[$localCol] = [
                                'table' => $fk->getForeignTableName(),
                                'column' => $fk->getForeignColumns()[0],
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Silently fail foreign key detection
            }
        }

        $firstCol = null;
        foreach (array_keys(array_diff_key($columns, $foreignKeys)) as $colName) {
            if ($colName !== 'id') {
                $firstCol = $colName;
                break;
            }
        }
        if (!$firstCol) {
            $firstCol = 'id';
        }

        foreach ($columns as $col => $type) {
            if (in_array($col, $exclude)) {
                continue;
            }

            $colLabel = Str::title(str_replace('_', ' ', $col));

            if (isset($foreignKeys[$col])) {
                $relatedModel = Str::studly(Str::singular($foreignKeys[$col]['table']));
                $fields .= <<<HTML
        <div class="border-t border-gray-200 dark:border-gray-700 py-4">
          <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">@lang('$colLabel')</dt>
          <dd class="mt-1 text-sm text-gray-900 dark:text-white">
            @if(\$item->{$col})
              <a href="#" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                {{ \$item->{$col}Object?->name ?? \$item->{$col} }}
              </a>
            @else
              <span class="text-gray-400 dark:text-gray-500">{{ __('Not set') }}</span>
            @endif
          </dd>
        </div>

HTML;
            } else {
                if ($type === 'boolean') {
                    $displayValue = '{{ $item->' . $col . ' ? __(\'Yes\') : __(\'No\') }}';
                } elseif (in_array($type, ['date', 'datetime', 'timestamp'])) {
                    $displayValue = '{{ $item->' . $col . '?->format(\'d M Y H:i\') ?? $item->' . $col . ' }}';
                } else {
                    $displayValue = '{{ $item->' . $col . ' }}';
                }

                $fields .= <<<HTML
        <div class="border-t border-gray-200 dark:border-gray-700 py-4">
          <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">@lang('$colLabel')</dt>
          <dd class="mt-1 text-sm text-gray-900 dark:text-white">
            $displayValue
          </dd>
        </div>

HTML;
            }
        }

        return <<<BLADE
@extends('layouts.app')

@section('title', \$item->{$firstCol} ?? __('View LABEL'))

@php
    \$topbarTitle = \$item->{$firstCol} ?? __('View LABEL');
@endphp

@section('app-content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex space-x-2 text-sm text-gray-600 dark:text-gray-400">
                <li>
                    <a href="{{ route('ROUTENAME.index') }}" class="hover:text-gray-900 dark:hover:text-white">
                        {{ __('LABEL') }}
                    </a>
                </li>
                <li class="text-gray-400">/</li>
                <li class="text-gray-900 dark:text-white">{{ \$item->{$firstCol} ?? \$item->id }}</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ \$item->{$firstCol} ?? __('View LABEL') }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('Details for this record') }}
            </p>
        </div>

        <!-- Content Card -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('Details') }}
                        </h3>
                    </div>
                    <div class="px-6">
                        <dl class="divide-y divide-gray-200 dark:divide-gray-700">
{$fields}                        </dl>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('Information') }}
                    </h3>

                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Created') }}</p>
                            <p class="text-gray-900 dark:text-white font-medium">
                                {{ \$item->created_at?->format('d M Y H:i') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Updated') }}</p>
                            <p class="text-gray-900 dark:text-white font-medium">
                                {{ \$item->updated_at?->format('d M Y H:i') }}
                            </p>
                        </div>
                    </div>

                    <hr class="my-4 border-gray-200 dark:border-gray-700">

                    <div class="space-y-2">
                        <a href="{{ route('ROUTENAME.edit', \$item) }}"
                            class="w-full block px-4 py-2 bg-blue-600 text-white border border-transparent rounded-md font-semibold text-sm hover:bg-blue-700 dark:hover:bg-blue-600 transition text-center">
                            <svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                            </svg>
                            {{ __('Edit') }}
                        </a>

                        <button type="button"
                            class="w-full px-4 py-2 bg-red-100 text-red-700 border border-red-300 rounded-md font-semibold text-sm hover:bg-red-200 dark:bg-red-900 dark:text-red-100 dark:border-red-800 dark:hover:bg-red-800 transition"
                            onclick="document.getElementById('deleteModal').classList.remove('hidden')">
                            <svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ __('Delete') }}
                        </button>

                        <a href="{{ route('ROUTENAME.index') }}"
                            class="w-full block px-4 py-2 bg-gray-200 text-gray-700 border border-gray-300 rounded-md font-semibold text-sm hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition text-center">
                            {{ __('Back to List') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-sm w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('Confirm Delete') }}
            </h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-600 dark:text-gray-300">
                {{ __('Are you sure you want to delete this record? This action cannot be undone.') }}
            </p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex gap-3 justify-end">
            <button type="button"
                class="px-4 py-2 text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition"
                onclick="document.getElementById('deleteModal').classList.add('hidden')">
                {{ __('Cancel') }}
            </button>
            <form method="POST" action="{{ route('ROUTENAME.destroy', \$item) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white border border-red-600 rounded-md hover:bg-red-700 transition">
                    {{ __('Delete') }}
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Close modal when clicking outside
    document.getElementById('deleteModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
</script>
@endsection
BLADE;
    }
}
