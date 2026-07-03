@extends('layouts.app')

@section('title', 'Transactions')

@php
    $topbarTitle = 'Transactions';
@endphp

@section('app-content')
    <div class="space-y-space-lg">
        @include('auth.success-and-error-alert')

        <!-- Header -->
        <div class="flex items-start justify-between gap-space-md flex-wrap">
            <div>
                <h1 class="font-headline-md text-headline-md text-on-surface">Transaction History</h1>
                <p class="font-body-md text-secondary mt-space-xs">Income, expenses, and transfers across all your wallets.</p>
            </div>
            <button type="button" onclick="document.getElementById('add-transaction-modal').classList.remove('hidden')"
                class="inline-flex items-center gap-space-xs px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Add Transaction
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-surface border border-outline-variant rounded-lg p-space-lg">
            <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-end gap-space-md">
                <div>
                    <label for="filter-wallet" class="block font-label-md text-label-md text-secondary mb-space-xs">Wallet</label>
                    <select name="wallet_id" id="filter-wallet"
                        class="rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                        <option value="">All Wallets</option>
                        @foreach ($wallets as $wallet)
                            <option value="{{ $wallet->id }}" @selected(($filters['wallet_id'] ?? null) === $wallet->id)>{{ $wallet->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-date-from" class="block font-label-md text-label-md text-secondary mb-space-xs">From</label>
                    <input type="date" name="date_from" id="filter-date-from" value="{{ $filters['date_from'] ?? '' }}"
                        class="rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label for="filter-date-to" class="block font-label-md text-label-md text-secondary mb-space-xs">To</label>
                    <input type="date" name="date_to" id="filter-date-to" value="{{ $filters['date_to'] ?? '' }}"
                        class="rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                </div>
                <div class="flex gap-space-sm">
                    <button type="submit"
                        class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                        Filter
                    </button>
                    <a href="{{ route('transactions.index') }}"
                        class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- History -->
        @if ($history->isEmpty())
            <div class="bg-surface border border-outline-variant rounded-lg p-space-xl text-center">
                <span class="material-symbols-outlined text-secondary text-[48px]">receipt_long</span>
                <p class="font-headline-sm text-headline-sm text-on-surface mt-space-md">No transactions yet</p>
                <p class="font-body-md text-secondary mt-space-xs">Record your first income, expense, or transfer to see it here.</p>
            </div>
        @else
            <div class="bg-surface border border-outline-variant rounded-lg overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-surface-container-lowest border-b border-outline-variant">
                        <tr>
                            <th class="px-space-lg py-space-sm font-label-md text-label-md text-secondary uppercase">Date</th>
                            <th class="px-space-lg py-space-sm font-label-md text-label-md text-secondary uppercase">Type</th>
                            <th class="px-space-lg py-space-sm font-label-md text-label-md text-secondary uppercase">Description</th>
                            <th class="px-space-lg py-space-sm font-label-md text-label-md text-secondary uppercase">Wallet</th>
                            <th class="px-space-lg py-space-sm font-label-md text-label-md text-secondary uppercase text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach ($history as $entry)
                            @php
                                $kind = $entry['kind'];
                                $model = $entry['model'];

                                $badge = match ($kind) {
                                    'income' => ['label' => 'Income', 'class' => 'bg-success-container text-on-success-container'],
                                    'expense' => ['label' => 'Expense', 'class' => 'bg-error-container text-on-error-container'],
                                    default => ['label' => 'Transfer', 'class' => 'bg-secondary-container text-on-secondary-container'],
                                };

                                $amountClass = match ($kind) {
                                    'income' => 'text-success',
                                    'expense' => 'text-error',
                                    default => 'text-secondary',
                                };

                                $amountPrefix = match ($kind) {
                                    'income' => '+',
                                    'expense' => '-',
                                    default => '',
                                };

                                $description = $kind === 'transfer'
                                    ? ($model->notes ?: 'Wallet transfer')
                                    : ($model->notes ?: $model->category->name);

                                $walletLabel = $kind === 'transfer'
                                    ? $model->fromWallet->name.' → '.$model->toWallet->name
                                    : $model->wallet->name;
                            @endphp
                            <tr>
                                <td class="px-space-lg py-space-sm font-body-md text-on-surface whitespace-nowrap">{{ $entry['date']->format('d M Y') }}</td>
                                <td class="px-space-lg py-space-sm">
                                    <span class="inline-flex px-space-sm py-[2px] rounded-full font-label-sm text-label-sm uppercase {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                </td>
                                <td class="px-space-lg py-space-sm font-body-md text-on-surface">{{ $description }}</td>
                                <td class="px-space-lg py-space-sm font-body-md text-secondary">{{ $walletLabel }}</td>
                                <td class="px-space-lg py-space-sm font-label-md text-label-md text-right {{ $amountClass }}">
                                    {{ $amountPrefix }}Rp {{ number_format($model->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Add Transaction Modal -->
    <div id="add-transaction-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-inverse-surface/40 p-gutter">
        <div class="bg-surface rounded-lg p-space-lg w-full max-w-md">
            <div class="flex items-center justify-between mb-space-md">
                <h2 class="font-headline-sm text-headline-sm text-on-surface">Add Transaction</h2>
                <button type="button" onclick="document.getElementById('add-transaction-modal').classList.add('hidden')" class="text-secondary hover:text-on-surface">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('transactions.store') }}" class="space-y-space-md" onsubmit="return combineTransactionDateTime()">
                @csrf

                <div>
                    <label for="transaction-type" class="block font-label-md text-label-md text-secondary mb-space-xs">Type</label>
                    <select name="type" id="transaction-type" required onchange="toggleTransactionType()"
                        class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                        <option value="expense">Expense</option>
                        <option value="income">Income</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>

                <div>
                    <label for="transaction-wallet" class="block font-label-md text-label-md text-secondary mb-space-xs" id="transaction-wallet-label">Wallet</label>
                    <select name="wallet_id" id="transaction-wallet" required
                        class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                        <option value="" selected disabled>Select a wallet</option>
                        @foreach ($wallets as $wallet)
                            <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="transaction-category-field">
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

                <div id="transaction-to-wallet-field" class="hidden">
                    <label for="transaction-to-wallet" class="block font-label-md text-label-md text-secondary mb-space-xs">Destination Wallet</label>
                    <select name="to_wallet_id" id="transaction-to-wallet"
                        class="w-full rounded-lg border border-outline-variant bg-surface px-space-md py-space-sm font-body-md text-on-surface focus:outline-none focus:border-primary">
                        <option value="" selected disabled>Select a wallet</option>
                        @foreach ($wallets as $wallet)
                            <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                        @endforeach
                    </select>
                </div>

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

                <div class="flex justify-end gap-space-sm">
                    <button type="button" onclick="document.getElementById('add-transaction-modal').classList.add('hidden')"
                        class="px-space-lg py-space-sm rounded-lg border border-outline-variant font-label-md text-label-md text-on-surface hover:bg-surface-container-lowest transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                        Save Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleTransactionType() {
            const type = document.getElementById('transaction-type').value;
            const walletLabel = document.getElementById('transaction-wallet-label');
            const categoryField = document.getElementById('transaction-category-field');
            const toWalletField = document.getElementById('transaction-to-wallet-field');
            const walletSelect = document.getElementById('transaction-wallet');
            const categorySelect = document.getElementById('transaction-category');
            const toWalletSelect = document.getElementById('transaction-to-wallet');
            const categoryOptions = categorySelect.querySelectorAll('option[data-type]');

            walletSelect.value = '';
            toWalletSelect.value = '';

            if (type === 'transfer') {
                walletLabel.textContent = 'Source Wallet';
                categoryField.classList.add('hidden');
                toWalletField.classList.remove('hidden');
                categorySelect.disabled = true;
                toWalletSelect.disabled = false;
            } else {
                walletLabel.textContent = 'Wallet';
                categoryField.classList.remove('hidden');
                toWalletField.classList.add('hidden');
                categorySelect.disabled = false;
                toWalletSelect.disabled = true;

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
