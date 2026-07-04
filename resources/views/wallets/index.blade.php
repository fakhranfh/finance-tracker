@extends('layouts.app')

@section('title', 'Wallets')

@php
    $topbarTitle = 'Wallets';
@endphp

@section('app-content')
    <div class="space-y-space-lg">
        @include('auth.success-and-error-alert')

        <!-- Total Consolidated Balance -->
        <div class="bg-surface border border-outline-variant rounded-lg p-space-lg">
            <div class="flex items-start justify-between gap-space-md flex-wrap">
                <div>
                    <span class="text-label-md text-secondary uppercase font-label-md">Total Consolidated Balance</span>
                    <p class="font-headline-lg text-headline-lg text-on-surface mt-space-xs">
                        Rp {{ number_format($totalBalance, 0, ',', '.') }}
                    </p>
                </div>
                <button type="button" onclick="document.getElementById('add-wallet-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-space-xs px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Add Wallet
                </button>
            </div>
        </div>

        <!-- Wallet Cards -->
        @if ($wallets->isEmpty())
            <div class="bg-surface border border-outline-variant rounded-lg p-space-xl text-center">
                <span class="material-symbols-outlined text-secondary text-[48px]">account_balance_wallet</span>
                <p class="font-headline-sm text-headline-sm text-on-surface mt-space-md">No wallets yet?</p>
                <p class="font-body-md text-secondary mt-space-xs">Start organizing your finances by adding your first wallet now.</p>
                <button type="button" onclick="document.getElementById('add-wallet-modal').classList.remove('hidden')"
                    class="mt-space-lg inline-flex items-center gap-space-xs px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Add Your First Wallet
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-lg">
                @foreach ($wallets as $wallet)
                    <div class="bg-surface border border-outline-variant rounded-lg p-space-lg hover:border-outline transition-colors duration-150">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-space-sm min-w-0">
                                <span class="material-symbols-outlined text-primary text-[20px]">account_balance_wallet</span>
                                <span class="font-label-md text-label-md text-secondary uppercase truncate">{{ $wallet->name }}</span>
                            </div>
                            <div class="flex items-center gap-space-xs shrink-0">
                                <button type="button" title="Edit wallet"
                                    onclick="document.getElementById('edit-wallet-modal-{{ $wallet->id }}').classList.remove('hidden')"
                                    class="p-space-xs rounded-md text-secondary hover:text-primary hover:bg-surface-container-lowest transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button type="button" title="Delete wallet"
                                    onclick="document.getElementById('delete-wallet-modal-{{ $wallet->id }}').classList.remove('hidden')"
                                    class="p-space-xs rounded-md text-secondary hover:text-error hover:bg-surface-container-lowest transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </div>
                        <p class="font-headline-md text-headline-md text-on-surface mt-space-md">{{ $wallet->formatted_balance }}</p>
                    </div>

                    <!-- Edit Wallet Modal -->
                    <x-modal id="edit-wallet-modal-{{ $wallet->id }}" title="Edit Wallet">
                        <form method="POST" action="{{ route('wallets.update', $wallet) }}" class="space-y-space-md">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="name-{{ $wallet->id }}" class="block font-label-md text-label-md text-secondary mb-space-xs">Wallet Name</label>
                                <input type="text" name="name" id="name-{{ $wallet->id }}" value="{{ $wallet->name }}" required maxlength="255"
                                    class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                            </div>
                            <div class="flex justify-end gap-space-sm">
                                <button type="button" onclick="document.getElementById('edit-wallet-modal-{{ $wallet->id }}').classList.add('hidden')"
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

                    <!-- Delete Wallet Modal -->
                    <x-modal id="delete-wallet-modal-{{ $wallet->id }}" title="Delete Wallet">
                        <p class="font-body-md text-secondary mb-space-lg">
                            Are you sure you want to delete <span class="text-on-surface font-label-md">{{ $wallet->name }}</span>? This wallet can be restored later, but it will be hidden from your overview.
                        </p>
                        <form method="POST" action="{{ route('wallets.destroy', $wallet) }}" class="flex justify-end gap-space-sm">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="document.getElementById('delete-wallet-modal-{{ $wallet->id }}').classList.add('hidden')"
                                class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-space-lg py-space-sm rounded-lg bg-error text-on-error font-label-md text-label-md hover:opacity-90 transition-opacity">
                                Delete
                            </button>
                        </form>
                    </x-modal>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Add Wallet Modal -->
    <x-modal id="add-wallet-modal" title="Add Wallet">
        <form method="POST" action="{{ route('wallets.store') }}" class="space-y-space-md">
            @csrf
            <div>
                <label for="new-wallet-name" class="block font-label-md text-label-md text-secondary mb-space-xs">Wallet Name</label>
                <input type="text" name="name" id="new-wallet-name" required maxlength="255" placeholder="e.g. Mandiri Account"
                    class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
            </div>
            <div>
                <label for="new-wallet-balance" class="block font-label-md text-label-md text-secondary mb-space-xs">Starting Balance (IDR)</label>
                <input type="number" name="balance" id="new-wallet-balance" min="0" step="1" placeholder="0"
                    class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
            </div>
            <div class="flex justify-end gap-space-sm">
                <button type="button" onclick="document.getElementById('add-wallet-modal').classList.add('hidden')"
                    class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                    Create Wallet
                </button>
            </div>
        </form>
    </x-modal>
@endsection
