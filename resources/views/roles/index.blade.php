@extends('layouts.app')

@section('title', 'Roles')

@php
    $topbarTitle = 'Roles';
@endphp

@section('app-content')
    <div class="space-y-space-lg">
        @include('auth.success-and-error-alert')

        <div class="flex items-center justify-between">
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Roles &amp; Permissions</h2>
            @can('create', Spatie\Permission\Models\Role::class)
                <button type="button" onclick="document.getElementById('add-role-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-space-xs px-space-md py-space-xs rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    Add Role
                </button>
            @endcan
        </div>

        <div class="bg-surface border border-outline-variant rounded-lg divide-y divide-outline-variant">
            @forelse ($roles as $role)
                <div class="flex items-center justify-between p-space-lg gap-space-sm">
                    <div class="min-w-0">
                        <p class="font-label-lg text-label-lg text-on-surface">{{ $role->name }}</p>
                        <p class="font-body-sm text-body-sm text-secondary">{{ $role->permissions->count() }} permissions</p>
                    </div>
                    <div class="flex items-center gap-space-xs shrink-0">
                        @can('update', $role)
                            <button type="button" title="Edit role"
                                onclick="document.getElementById('edit-role-modal-{{ $role->id }}').classList.remove('hidden')"
                                class="p-space-xs rounded-md text-secondary hover:text-primary hover:bg-surface-container-lowest transition-colors">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                            </button>
                        @endcan
                        @can('delete', $role)
                            <button type="button" title="Delete role"
                                onclick="document.getElementById('delete-role-modal-{{ $role->id }}').classList.remove('hidden')"
                                class="p-space-xs rounded-md text-secondary hover:text-error hover:bg-surface-container-lowest transition-colors">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        @endcan
                    </div>
                </div>

                <x-modal id="edit-role-modal-{{ $role->id }}" title="Edit Role" maxWidth="max-w-lg">
                    <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-space-md">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="name-{{ $role->id }}" class="block font-label-md text-label-md text-secondary mb-space-xs">Role Name</label>
                            <input type="text" name="name" id="name-{{ $role->id }}" value="{{ $role->name }}" required maxlength="255"
                                class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <p class="block font-label-md text-label-md text-secondary mb-space-xs">Permissions</p>
                            <div class="space-y-space-sm max-h-64 overflow-y-auto">
                                @php $rolePermissionNames = $role->permissions->pluck('name')->all(); @endphp
                                @foreach ($permissionsByEntity as $entity => $permissions)
                                    <div>
                                        <p class="font-label-sm text-label-sm text-secondary capitalize mb-space-xs">{{ $entity }}</p>
                                        <div class="flex flex-wrap gap-space-md">
                                            @foreach ($permissions as $permission)
                                                <label class="inline-flex items-center gap-space-xs font-body-sm text-body-sm text-on-surface">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                        @checked(in_array($permission->name, $rolePermissionNames, true))>
                                                    {{ $permission->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex justify-end gap-space-sm">
                            <button type="button" onclick="document.getElementById('edit-role-modal-{{ $role->id }}').classList.add('hidden')"
                                class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </x-modal>

                <x-modal id="delete-role-modal-{{ $role->id }}" title="Delete Role">
                    <p class="font-body-md text-secondary mb-space-lg">
                        Are you sure you want to delete <span class="text-on-surface font-label-md">{{ $role->name }}</span>? Users assigned to this role will lose it.
                    </p>
                    <form method="POST" action="{{ route('roles.destroy', $role) }}" class="flex justify-end gap-space-sm">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="document.getElementById('delete-role-modal-{{ $role->id }}').classList.add('hidden')"
                            class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-space-lg py-space-sm rounded-lg bg-error text-on-error font-label-md text-label-md hover:opacity-90 transition-opacity">
                            Delete
                        </button>
                    </form>
                </x-modal>
            @empty
                <p class="font-body-md text-secondary text-center py-space-lg">No roles yet.</p>
            @endforelse
        </div>

        <x-modal id="add-role-modal" title="Add Role" maxWidth="max-w-lg">
            <form method="POST" action="{{ route('roles.store') }}" class="space-y-space-md">
                @csrf
                <div>
                    <label for="new-role-name" class="block font-label-md text-label-md text-secondary mb-space-xs">Role Name</label>
                    <input type="text" name="name" id="new-role-name" required maxlength="255" placeholder="e.g. auditor"
                        class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                </div>
                <div>
                    <p class="block font-label-md text-label-md text-secondary mb-space-xs">Permissions</p>
                    <div class="space-y-space-sm max-h-64 overflow-y-auto">
                        @foreach ($permissionsByEntity as $entity => $permissions)
                            <div>
                                <p class="font-label-sm text-label-sm text-secondary capitalize mb-space-xs">{{ $entity }}</p>
                                <div class="flex flex-wrap gap-space-md">
                                    @foreach ($permissions as $permission)
                                        <label class="inline-flex items-center gap-space-xs font-body-sm text-body-sm text-on-surface">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}">
                                            {{ $permission->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-space-sm">
                    <button type="button" onclick="document.getElementById('add-role-modal').classList.add('hidden')"
                        class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                        Create Role
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
@endsection
