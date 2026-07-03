@extends('layouts.app')

@section('title', 'Categories')

@php
    $topbarTitle = 'Categories';
@endphp

@section('app-content')
    <div class="space-y-space-lg">
        @include('auth.success-and-error-alert')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-space-lg">
            @foreach ([
                'expense' => ['title' => 'Expense', 'categories' => $expenseCategories, 'icon' => 'trending_down'],
                'income' => ['title' => 'Income', 'categories' => $incomeCategories, 'icon' => 'trending_up'],
            ] as $type => $group)
                <div class="bg-surface border border-outline-variant rounded-lg p-space-lg">
                    <div class="flex items-center justify-between mb-space-md">
                        <div class="flex items-center gap-space-sm">
                            <span class="material-symbols-outlined text-primary text-[20px]">{{ $group['icon'] }}</span>
                            <h2 class="font-headline-sm text-headline-sm text-on-surface">{{ $group['title'] }}</h2>
                        </div>
                        <button type="button" onclick="document.getElementById('add-category-modal-{{ $type }}').classList.remove('hidden')"
                            class="inline-flex items-center gap-space-xs px-space-md py-space-xs rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            Add
                        </button>
                    </div>

                    @if ($group['categories']->isEmpty())
                        <p class="font-body-md text-secondary text-center py-space-lg">No {{ strtolower($group['title']) }} categories yet.</p>
                    @else
                        <ul class="divide-y divide-outline-variant">
                            @foreach ($group['categories'] as $category)
                                <li class="flex items-center justify-between py-space-sm gap-space-sm">
                                    <div class="flex items-center gap-space-sm min-w-0">
                                        <span class="font-body-md text-on-surface truncate">{{ $category->name }}</span>
                                        @if ($category->user_id === null)
                                            <span class="shrink-0 px-space-xs py-[2px] rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm uppercase">Global</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-space-xs shrink-0">
                                        @can('update', $category)
                                            <button type="button" title="Edit category"
                                                onclick="document.getElementById('edit-category-modal-{{ $category->id }}').classList.remove('hidden')"
                                                class="p-space-xs rounded-md text-secondary hover:text-primary hover:bg-surface-container-lowest transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>
                                        @endcan
                                        @can('delete', $category)
                                            <button type="button" title="Delete category"
                                                onclick="document.getElementById('delete-category-modal-{{ $category->id }}').classList.remove('hidden')"
                                                class="p-space-xs rounded-md text-secondary hover:text-error hover:bg-surface-container-lowest transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        @endcan
                                    </div>
                                </li>

                                <!-- Edit Category Modal -->
                                <div id="edit-category-modal-{{ $category->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-inverse-surface/40 p-gutter">
                                    <div class="bg-surface rounded-lg p-space-lg w-full max-w-md">
                                        <div class="flex items-center justify-between mb-space-md">
                                            <h2 class="font-headline-sm text-headline-sm text-on-surface">Edit Category</h2>
                                            <button type="button" onclick="document.getElementById('edit-category-modal-{{ $category->id }}').classList.add('hidden')" class="text-secondary hover:text-on-surface">
                                                <span class="material-symbols-outlined text-[20px]">close</span>
                                            </button>
                                        </div>
                                        <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-space-md">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label for="name-{{ $category->id }}" class="block font-label-md text-label-md text-secondary mb-space-xs">Category Name</label>
                                                <input type="text" name="name" id="name-{{ $category->id }}" value="{{ $category->name }}" required maxlength="255"
                                                    class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                                            </div>
                                            <div>
                                                <label for="type-{{ $category->id }}" class="block font-label-md text-label-md text-secondary mb-space-xs">Type</label>
                                                <select name="type" id="type-{{ $category->id }}" required
                                                    class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                                                    <option value="expense" @selected($category->type === 'expense')>Expense</option>
                                                    <option value="income" @selected($category->type === 'income')>Income</option>
                                                </select>
                                            </div>
                                            <div class="flex justify-end gap-space-sm">
                                                <button type="button" onclick="document.getElementById('edit-category-modal-{{ $category->id }}').classList.add('hidden')"
                                                    class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                                                    Cancel
                                                </button>
                                                <button type="submit"
                                                    class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                                                    Save Changes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Delete Category Modal -->
                                <div id="delete-category-modal-{{ $category->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-inverse-surface/40 p-gutter">
                                    <div class="bg-surface rounded-lg p-space-lg w-full max-w-md">
                                        <div class="flex items-center justify-between mb-space-md">
                                            <h2 class="font-headline-sm text-headline-sm text-on-surface">Delete Category</h2>
                                            <button type="button" onclick="document.getElementById('delete-category-modal-{{ $category->id }}').classList.add('hidden')" class="text-secondary hover:text-on-surface">
                                                <span class="material-symbols-outlined text-[20px]">close</span>
                                            </button>
                                        </div>
                                        <p class="font-body-md text-secondary mb-space-lg">
                                            Are you sure you want to delete <span class="text-on-surface font-label-md">{{ $category->name }}</span>? This category can be restored later, but it will be hidden from your overview.
                                        </p>
                                        <form method="POST" action="{{ route('categories.destroy', $category) }}" class="flex justify-end gap-space-sm">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="document.getElementById('delete-category-modal-{{ $category->id }}').classList.add('hidden')"
                                                class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                class="px-space-lg py-space-sm rounded-lg bg-error text-on-error font-label-md text-label-md hover:opacity-90 transition-opacity">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- Add Category Modal -->
                <div id="add-category-modal-{{ $type }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-inverse-surface/40 p-gutter">
                    <div class="bg-surface rounded-lg p-space-lg w-full max-w-md">
                        <div class="flex items-center justify-between mb-space-md">
                            <h2 class="font-headline-sm text-headline-sm text-on-surface">Add {{ $group['title'] }} Category</h2>
                            <button type="button" onclick="document.getElementById('add-category-modal-{{ $type }}').classList.add('hidden')" class="text-secondary hover:text-on-surface">
                                <span class="material-symbols-outlined text-[20px]">close</span>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('categories.store') }}" class="space-y-space-md">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">
                            <div>
                                <label for="new-category-name-{{ $type }}" class="block font-label-md text-label-md text-secondary mb-space-xs">Category Name</label>
                                <input type="text" name="name" id="new-category-name-{{ $type }}" required maxlength="255" placeholder="e.g. Hobi Kucing"
                                    class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                            </div>
                            <div class="flex justify-end gap-space-sm">
                                <button type="button" onclick="document.getElementById('add-category-modal-{{ $type }}').classList.add('hidden')"
                                    class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                                    Create Category
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
