<?php

namespace App\Console\Commands\Stubs;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TailwindBladeEditStubGenerator
{
    public function generate(string $name, string $label, array $columnInputTypes = [], array $filterDefinitions = []): string
    {
        $enumOptions = [];
        foreach ($filterDefinitions as $filter) {
            if ($filter['type'] === 'enum' && isset($filter['options'])) {
                $enumOptions[$filter['key']] = $filter['options'];
            }
        }

        $labelKebab = Str::kebab($label);

        // Get columns from database or columnInputTypes
        $tableName = Str::snake(Str::plural($name));
        $columns = [];

        try {
            // First try direct Schema method
            if (Schema::hasTable($tableName)) {
                $columnNames = Schema::getColumnListing($tableName);
                // Map column names to type 'string' as default
                foreach ($columnNames as $col) {
                    $columns[$col] = 'string';
                }
            }
        } catch (\Exception $e) {
            // Table might not exist yet
        }

        // If no columns found and columnInputTypes provided, use those
        if (empty($columns) && ! empty($columnInputTypes)) {
            $columns = array_fill_keys(array_keys($columnInputTypes), 'string');
        }

        $exclude = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'password'];
        $inputs = '';

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
        if (! $firstCol) {
            $firstCol = 'id';
        }

        foreach ($columns as $col => $type) {
            if (in_array($col, $exclude)) {
                continue;
            }

            // Skip field if input type is 'skip'
            if (isset($columnInputTypes[$col]) && $columnInputTypes[$col] === 'skip') {
                continue;
            }

            $colLabel = Str::title(str_replace('_', ' ', $col));
            $field = '';

            if (isset($foreignKeys[$col])) {
                $relatedTable = $foreignKeys[$col]['table'];
                $relatedModel = Str::studly(Str::singular($relatedTable));
                $optionsVar = "\${$relatedTable}";
                $displayCol = 'name';

                if (class_exists("App\\Models\\$relatedModel")) {
                    try {
                        $connection = Schema::getConnection();
                        if (Schema::hasTable($relatedTable)) {
                            $relatedCols = Schema::getColumnListing($relatedTable);
                            $displayCol = 'id';
                            foreach ($relatedCols as $rc) {
                                if ($rc !== 'id') {
                                    $displayCol = $rc;
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // Related table might not exist yet, use default display column
                    }
                }

                $field = <<<HTML
<select id="$col" name="$col" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
  <option value="">-- @lang('Select') --</option>
  @foreach($optionsVar as \$opt)
    <option value="{{ \$opt->id }}"
      {{ (isset(\$item) && \$item->$col == \$opt->id) || old('$col') == \$opt->id ? 'selected' : '' }}>
      {{ \$opt->$displayCol }}
    </option>
  @endforeach
</select>
HTML;
            } else {
                // Use custom input type if provided, otherwise default based on column type
                $inputType = $columnInputTypes[$col] ?? null;

                if ($inputType) {
                    $field = match ($inputType) {
                        'textarea' => $this->generateTextareaField($col),
                        'date' => $this->generateDateField($col),
                        'datetime-local' => $this->generateDatetimeField($col),
                        'number' => $this->generateNumberField($col),
                        'email' => $this->generateEmailField($col),
                        'password' => $this->generatePasswordField($col),
                        'url' => $this->generateUrlField($col),
                        'tel' => $this->generateTelField($col),
                        'checkbox' => $this->generateCheckboxField($col),
                        'radio' => $this->generateBooleanField($col),
                        'select' => $this->generateSelectField($col, $enumOptions[$col] ?? []),
                        default => $this->generateTextField($col),
                    };
                } else {
                    $field = match ($type) {
                        'boolean' => $this->generateBooleanField($col),
                        'text' => $this->generateTextareaField($col),
                        'date' => $this->generateDateField($col),
                        'datetime', 'timestamp' => $this->generateDatetimeField($col),
                        'integer', 'bigint', 'smallint', 'tinyint' => $this->generateNumberField($col),
                        'float', 'double', 'decimal' => $this->generateDecimalField($col),
                        default => $this->generateTextField($col),
                    };
                }
            }

            $inputs .= <<<HTML
        <div class="mb-4">
          <label for="$col" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@lang('$colLabel')</label>
          $field
          @error('$col')
            <p class="mt-1 text-sm text-red-500">{{ \$message }}</p>
          @enderror
        </div>

HTML;
        }

        return <<<BLADE
@extends('layouts.app')

@section('title', __('Edit LABEL'))

@php
    \$topbarTitle = __('Edit LABEL');
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
                <li>
                    <a href="{{ route('ROUTENAME.show', \$item) }}" class="hover:text-gray-900 dark:hover:text-white">
                        {{ \$item->{$firstCol} ?? \$item->id }}
                    </a>
                </li>
                <li class="text-gray-400">/</li>
                <li class="text-gray-900 dark:text-white">{{ __('Edit') }}</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ __('Edit LABEL') }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('Update the record details') }}
            </p>
        </div>

        <!-- Alerts -->
        @if (\$errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded dark:bg-red-900 dark:border-red-600 dark:text-red-100">
                <h3 class="font-bold">{{ __('Please fix the following errors:') }}</h3>
                <ul class="mt-2 space-y-1">
                    @foreach (\$errors->all() as \$error)
                        <li>{{ \$error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-3">
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <form method="POST" action="{{ route('ROUTENAME.update', \$item) }}" class="space-y-4" id="editForm" onsubmit="handleEditFormSubmit(event)">
                        @csrf
                        @method('PUT')

                        <!-- Fields -->
{$inputs}
                        <!-- Submit Buttons -->
                        <div class="flex gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" id="editSubmitBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-50 transition ease-in-out duration-150">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span id="editSubmitText">{{ __('Save Changes') }}</span>
                            </button>
                            <a href="{{ route('ROUTENAME.show', \$item) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-400 focus:ring ring-gray-300 disabled:opacity-50 transition ease-in-out duration-150 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
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

                    <button type="button"
                        class="w-full px-4 py-2 bg-red-100 text-red-700 border border-red-300 rounded-md font-semibold text-sm hover:bg-red-200 dark:bg-red-900 dark:text-red-100 dark:border-red-800 dark:hover:bg-red-800 transition"
                        id="deleteShowBtn"
                        onclick="document.getElementById('deleteModal').classList.remove('hidden')">
                        <svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Delete') }}
                    </button>
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
                id="deleteModalCancelBtn"
                onclick="document.getElementById('deleteModal').classList.add('hidden')">
                {{ __('Cancel') }}
            </button>
            <form method="POST" action="{{ route('ROUTENAME.destroy', \$item) }}" class="inline" id="deleteForm" onsubmit="handleDeleteSubmit(event)">
                @csrf
                @method('DELETE')
                <button type="submit"
                    id="deleteModalDeleteBtn"
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

    // Handle edit form submission
    function handleEditFormSubmit(e) {
        const submitBtn = document.getElementById('editSubmitBtn');
        const submitText = document.getElementById('editSubmitText');

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
            if (submitText) {
                submitText.textContent = '{{ __("Saving...") }}';
            }
        }
    }

    // Handle delete form submission
    function handleDeleteSubmit(e) {
        e.preventDefault();

        const deleteBtn = document.getElementById('deleteModalDeleteBtn');
        const cancelBtn = document.getElementById('deleteModalCancelBtn');
        const form = document.getElementById('deleteForm');

        // Show loading state
        if (deleteBtn) {
            deleteBtn.disabled = true;
            deleteBtn.textContent = '{{ __("Deleting...") }}';
            deleteBtn.style.opacity = '0.5';
            deleteBtn.style.cursor = 'not-allowed';
        }
        if (cancelBtn) {
            cancelBtn.disabled = true;
            cancelBtn.style.opacity = '0.5';
            cancelBtn.style.cursor = 'not-allowed';
        }

        // Submit form after short delay for visual feedback
        setTimeout(() => {
            form.submit();
        }, 300);
    }
</script>
@endsection
BLADE;
    }

    private function generateBooleanField(string $col): string
    {
        return <<<HTML
<div class="mt-1 space-y-2">
  <div class="flex items-center">
    <input class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" type="radio" id="{$col}_1" name="$col" value="1"
      @if((isset(\$item) && \$item->$col == 1) || old('$col') == 1) checked @endif>
    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300" for="{$col}_1">@lang('Yes')</label>
  </div>
  <div class="flex items-center">
    <input class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" type="radio" id="{$col}_0" name="$col" value="0"
      @if((isset(\$item) && \$item->$col == 0) || old('$col') == 0) checked @endif>
    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300" for="{$col}_0">@lang('No')</label>
  </div>
</div>
HTML;
    }

    private function generateTextareaField(string $col): string
    {
        return <<<HTML
<textarea id="$col" name="$col" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>{{ isset(\$item) ? \$item->$col : old('$col') }}</textarea>
HTML;
    }

    private function generateDateField(string $col): string
    {
        return <<<HTML
<input type="date" id="$col" name="$col" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="{{ isset(\$item) ? \$item->$col : old('$col') }}" required />
HTML;
    }

    private function generateDatetimeField(string $col): string
    {
        return <<<HTML
<input type="datetime-local" id="$col" name="$col" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="{{ isset(\$item) ? \$item->$col : old('$col') }}" required />
HTML;
    }

    private function generateNumberField(string $col): string
    {
        return <<<HTML
<input type="number" id="$col" name="$col" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="{{ isset(\$item) ? \$item->$col : old('$col') }}" required />
HTML;
    }

    private function generateDecimalField(string $col): string
    {
        return <<<HTML
<input type="number" step="0.01" id="$col" name="$col" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="{{ isset(\$item) ? \$item->$col : old('$col') }}" required />
HTML;
    }

    private function generateTextField(string $col): string
    {
        return <<<HTML
<input type="text" id="$col" name="$col" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="{{ isset(\$item) ? \$item->$col : old('$col') }}" required />
HTML;
    }

    private function generateEmailField(string $col): string
    {
        return <<<HTML
<input type="email" id="$col" name="$col" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="{{ isset(\$item) ? \$item->$col : old('$col') }}" required />
HTML;
    }

    private function generatePasswordField(string $col): string
    {
        return <<<HTML
<input type="password" id="$col" name="$col" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required />
HTML;
    }

    private function generateUrlField(string $col): string
    {
        return <<<HTML
<input type="url" id="$col" name="$col" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="{{ isset(\$item) ? \$item->$col : old('$col') }}" required />
HTML;
    }

    private function generateTelField(string $col): string
    {
        return <<<HTML
<input type="tel" id="$col" name="$col" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="{{ isset(\$item) ? \$item->$col : old('$col') }}" required />
HTML;
    }

    private function generateCheckboxField(string $col): string
    {
        return <<<HTML
<div class="mt-1 space-y-2">
  <div class="flex items-center">
    <input class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" type="checkbox" id="$col" name="$col" value="1"
      @if((isset(\$item) && \$item->$col == 1) || old('$col') == 1) checked @endif>
    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300" for="$col">@lang('Yes')</label>
  </div>
</div>
HTML;
    }

    private function generateSelectField(string $col, array $options = []): string
    {
        $optionLines = '';
        foreach ($options as $value => $label) {
            $optionLines .= "\n            <option value=\"{$value}\" {{ old('{$col}', \$item->{$col}) === '{$value}' ? 'selected' : '' }}>@lang('{$label}')</option>";
        }

        return <<<HTML
<select id="$col" name="$col" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            <option value="">-- @lang('Select') --</option>{$optionLines}
          </select>
HTML;
    }
}
