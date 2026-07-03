@extends('layouts.app')

@section('title', 'Add Transaction')

@php
    $topbarTitle = 'Add Transaction';
    $showBackButton = true;
@endphp

@section('app-content')
    <div class="space-y-space-lg max-w-3xl mx-auto">
        @include('auth.success-and-error-alert')

        <form method="POST" action="{{ route('transactions.store') }}" class="space-y-space-lg" onsubmit="return combineTransactionDateTime()">
            @csrf

            <div class="bg-surface border border-outline-variant rounded-lg p-space-lg">
                <label for="transaction-type" class="block font-label-md text-label-md text-secondary mb-space-xs">Type</label>
                <select name="type" id="transaction-type" required onchange="toggleTransactionType()"
                    class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                    <option value="expense">Expense</option>
                    <option value="income">Income</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>

            <div class="bg-surface border border-outline-variant rounded-lg p-space-lg">
                <label class="block font-label-md text-label-md text-secondary mb-space-md" id="transaction-wallet-label">Wallet</label>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-md">
                    @foreach ($wallets as $wallet)
                        <label class="wallet-card cursor-pointer relative block">
                            <input type="radio" name="wallet_id" value="{{ $wallet->id }}" required
                                class="wallet-card-input peer absolute top-space-md right-space-md h-4 w-4 accent-primary" data-group="wallet_id">
                            <div class="border border-outline-variant rounded-lg p-space-lg hover:border-outline transition-colors duration-150 peer-checked:border-primary peer-checked:ring-2 peer-checked:ring-primary/20">
                                <div class="flex items-center gap-space-sm min-w-0 pr-space-lg">
                                    <span class="material-symbols-outlined text-primary text-[20px]">account_balance_wallet</span>
                                    <span class="font-label-md text-label-md text-secondary uppercase truncate">{{ $wallet->name }}</span>
                                </div>
                                <p class="font-headline-md text-headline-md text-on-surface mt-space-md">{{ $wallet->formatted_balance }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div id="transaction-category-field" class="bg-surface border border-outline-variant rounded-lg p-space-lg">
                <label for="transaction-category" class="block font-label-md text-label-md text-secondary mb-space-xs">Category</label>
                <select name="category_id" id="transaction-category"
                    class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                    <option value="" selected disabled>Select a category</option>
                    @foreach ($expenseCategories as $category)
                        <option value="{{ $category->id }}" data-type="expense">{{ $category->name }}</option>
                    @endforeach
                    @foreach ($incomeCategories as $category)
                        <option value="{{ $category->id }}" data-type="income" hidden style="display: none;">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="transaction-to-wallet-field" class="hidden bg-surface border border-outline-variant rounded-lg p-space-lg">
                <label class="block font-label-md text-label-md text-secondary mb-space-md">Destination Wallet</label>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-md">
                    @foreach ($wallets as $wallet)
                        <label class="wallet-card cursor-pointer relative block">
                            <input type="radio" name="to_wallet_id" value="{{ $wallet->id }}"
                                class="wallet-card-input peer absolute top-space-md right-space-md h-4 w-4 accent-primary" data-group="to_wallet_id" disabled>
                            <div class="border border-outline-variant rounded-lg p-space-lg hover:border-outline transition-colors duration-150 peer-checked:border-primary peer-checked:ring-2 peer-checked:ring-primary/20">
                                <div class="flex items-center gap-space-sm min-w-0 pr-space-lg">
                                    <span class="material-symbols-outlined text-primary text-[20px]">account_balance_wallet</span>
                                    <span class="font-label-md text-label-md text-secondary uppercase truncate">{{ $wallet->name }}</span>
                                </div>
                                <p class="font-headline-md text-headline-md text-on-surface mt-space-md">{{ $wallet->formatted_balance }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-surface border border-outline-variant rounded-lg p-space-lg space-y-space-md">
                <div>
                    <label for="transaction-amount" class="block font-label-md text-label-md text-secondary mb-space-xs">Amount (IDR)</label>
                    <input type="number" name="amount" id="transaction-amount" min="1" step="1" required placeholder="0"
                        class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                </div>

                <div class="flex gap-space-sm">
                    <div class="flex-1">
                        <label for="transaction-date" class="block font-label-md text-label-md text-secondary mb-space-xs">Date</label>
                        <input type="date" id="transaction-date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required
                            onchange="capTransactionTime()"
                            class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                    </div>
                    <div class="flex-1">
                        <label for="transaction-time" class="block font-label-md text-label-md text-secondary mb-space-xs">Time</label>
                        <input type="time" id="transaction-time" value="{{ now()->format('H:i') }}" max="{{ now()->format('H:i') }}" required
                            class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                    </div>
                    <input type="hidden" name="transaction_date" id="transaction-date-time">
                </div>

                <div>
                    <label for="transaction-notes" class="block font-label-md text-label-md text-secondary mb-space-xs">Notes (optional)</label>
                    <input type="text" name="notes" id="transaction-notes" maxlength="255"
                        class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                </div>
            </div>

            <div class="flex justify-end gap-space-sm">
                <a href="{{ route('transactions.index') }}"
                    class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                    Save Transaction
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleTransactionType() {
            const type = document.getElementById('transaction-type').value;
            const walletLabel = document.getElementById('transaction-wallet-label');
            const categoryField = document.getElementById('transaction-category-field');
            const toWalletField = document.getElementById('transaction-to-wallet-field');
            const categorySelect = document.getElementById('transaction-category');
            const walletRadios = document.querySelectorAll('input[data-group="wallet_id"]');
            const toWalletRadios = document.querySelectorAll('input[data-group="to_wallet_id"]');
            const categoryOptions = categorySelect.querySelectorAll('option[data-type]');

            walletRadios.forEach((radio) => { radio.checked = false; });
            toWalletRadios.forEach((radio) => { radio.checked = false; });

            if (type === 'transfer') {
                walletLabel.textContent = 'Source Wallet';
                categoryField.classList.add('hidden');
                toWalletField.classList.remove('hidden');
                categorySelect.disabled = true;
                toWalletRadios.forEach((radio) => { radio.disabled = false; });
            } else {
                walletLabel.textContent = 'Wallet';
                categoryField.classList.remove('hidden');
                toWalletField.classList.add('hidden');
                categorySelect.disabled = false;
                toWalletRadios.forEach((radio) => { radio.disabled = true; });

                categoryOptions.forEach((option) => {
                    const isVisible = option.dataset.type === type;
                    option.hidden = !isVisible;
                    option.classList.toggle('hidden', !isVisible);
                    option.style.display = isVisible ? '' : 'none';
                });

                categorySelect.value = '';
            }
        }

        function capTransactionTime() {
            const dateInput = document.getElementById('transaction-date');
            const timeInput = document.getElementById('transaction-time');

            const today = new Date().toISOString().slice(0, 10);
            const nowTime = new Date().toTimeString().slice(0, 5);

            if (dateInput.value === today) {
                timeInput.max = nowTime;
                if (timeInput.value > nowTime) {
                    timeInput.value = nowTime;
                }
            } else {
                timeInput.removeAttribute('max');
            }
        }

        function combineTransactionDateTime() {
            const dateInput = document.getElementById('transaction-date');
            const timeInput = document.getElementById('transaction-time');
            const combinedInput = document.getElementById('transaction-date-time');

            capTransactionTime();
            combinedInput.value = `${dateInput.value} ${timeInput.value}`;

            return true;
        }

        document.addEventListener('DOMContentLoaded', toggleTransactionType);
        document.addEventListener('DOMContentLoaded', capTransactionTime);
    </script>
@endsection
