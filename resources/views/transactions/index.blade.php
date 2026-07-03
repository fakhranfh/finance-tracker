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
            <a href="{{ route('transactions.create') }}"
                class="inline-flex items-center gap-space-xs px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Add Transaction
            </a>
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
@endsection
