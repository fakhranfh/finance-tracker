@extends('layouts.app')

@section('title', 'Users')

@php
    $topbarTitle = 'Users';
@endphp

@section('app-content')
    <div class="space-y-space-lg">
        @include('auth.success-and-error-alert')

        <h2 class="font-headline-sm text-headline-sm text-on-surface">Users</h2>

        <div class="bg-surface border border-outline-variant rounded-lg divide-y divide-outline-variant">
            @foreach ($users as $user)
                <div class="flex items-center justify-between p-space-lg gap-space-sm">
                    <div class="min-w-0">
                        <p class="font-label-lg text-label-lg text-on-surface">{{ $user->name }}</p>
                        <p class="font-body-sm text-body-sm text-secondary">{{ $user->email }}</p>
                        <p class="font-label-sm text-label-sm text-secondary uppercase mt-space-xs">
                            {{ $user->roles->pluck('name')->join(', ') ?: 'No role' }}
                        </p>
                    </div>
                    <div class="shrink-0 flex items-center gap-space-xs">
                        @can('update', $user)
                            @if (auth()->id() !== $user->id)
                                <button type="button" title="Change role"
                                    onclick="document.getElementById('change-role-modal-{{ $user->id }}').classList.remove('hidden')"
                                    class="p-space-xs rounded-md text-secondary hover:text-primary hover:bg-surface-container-lowest transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">manage_accounts</span>
                                </button>
                                <button type="button" title="Reset password"
                                    onclick="document.getElementById('reset-password-modal-{{ $user->id }}').classList.remove('hidden')"
                                    class="p-space-xs rounded-md text-secondary hover:text-primary hover:bg-surface-container-lowest transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                                </button>
                            @endif
                        @endcan
                    </div>
                </div>

                <x-modal id="change-role-modal-{{ $user->id }}" title="Change Role">
                    <form method="POST" action="{{ route('users.update-role', $user) }}" class="space-y-space-md">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="role-{{ $user->id }}" class="block font-label-md text-label-md text-secondary mb-space-xs">Role for {{ $user->name }}</label>
                            <select name="role" id="role-{{ $user->id }}" required
                                class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" @selected($user->hasRole($role->name))>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end gap-space-sm">
                            <button type="button" onclick="document.getElementById('change-role-modal-{{ $user->id }}').classList.add('hidden')"
                                class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                                Save
                            </button>
                        </div>
                    </form>
                </x-modal>

                <x-modal id="reset-password-modal-{{ $user->id }}" title="Reset Password">
                    <form method="POST" action="{{ route('users.reset-password', $user) }}" class="space-y-space-md">
                        @csrf
                        @method('PUT')
                        <p class="font-body-sm text-body-sm text-secondary">Set a new password for {{ $user->name }}. They will need to use this password on their next login.</p>
                        <div>
                            <label for="password-{{ $user->id }}" class="block font-label-md text-label-md text-secondary mb-space-xs">New Password</label>
                            <input type="password" name="password" id="password-{{ $user->id }}" required
                                class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                            @error('password')
                                <p class="text-error text-body-sm font-body-sm mt-space-xs">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password-confirmation-{{ $user->id }}" class="block font-label-md text-label-md text-secondary mb-space-xs">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password-confirmation-{{ $user->id }}" required
                                class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                        </div>
                        <div class="flex justify-end gap-space-sm">
                            <button type="button" onclick="document.getElementById('reset-password-modal-{{ $user->id }}').classList.add('hidden')"
                                class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                                Reset Password
                            </button>
                        </div>
                    </form>
                </x-modal>
            @endforeach
        </div>
    </div>
@endsection
