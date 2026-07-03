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

        <!-- History -->
        <x-data-table
            table-id="transactionsTable"
            :list-url="route('transactions.data')"
            sort="date"
            dir="desc"
            :filters="[
                ['type' => 'enum', 'key' => 'wallet_id', 'label' => 'Wallet', 'options' => $wallets->pluck('name', 'id')->all()],
                ['type' => 'datetime', 'key' => 'date', 'label' => 'Date'],
            ]"
        >
            <x-slot:headers>
                <x-sortable-th table-id="transactionsTable" column="date" label="Date" />
                <x-sortable-th table-id="transactionsTable" column="type" label="Type" />
                <x-sortable-th table-id="transactionsTable" column="description" label="Description" />
                <x-sortable-th table-id="transactionsTable" column="wallet" label="Wallet" />
                <x-sortable-th table-id="transactionsTable" column="amount" label="Amount" align="right" />
            </x-slot:headers>
        </x-data-table>
    </div>

    <x-data-table-scripts />

    <script>
        const transactionBadges = {
            income: { label: 'Income', class: 'bg-success-container text-on-success-container', amountClass: 'text-success', amountPrefix: '+' },
            expense: { label: 'Expense', class: 'bg-error-container text-on-error-container', amountClass: 'text-error', amountPrefix: '-' },
            transfer: { label: 'Transfer', class: 'bg-secondary-container text-on-secondary-container', amountClass: 'text-secondary', amountPrefix: '' },
        };

        function renderTransactionRow(item) {
            const badge = transactionBadges[item.kind] ?? transactionBadges.transfer;
            const row = document.createElement('tr');

            const dateCell = document.createElement('td');
            dateCell.className = 'px-space-lg py-space-sm font-body-md text-on-surface whitespace-nowrap';
            dateCell.textContent = formatLocalDateTime(item.date);
            row.appendChild(dateCell);

            const typeCell = document.createElement('td');
            typeCell.className = 'px-space-lg py-space-sm';
            typeCell.innerHTML = `<span class="inline-flex px-space-sm py-[2px] rounded-full font-label-sm text-label-sm uppercase ${badge.class}">${badge.label}</span>`;
            row.appendChild(typeCell);

            const descriptionCell = document.createElement('td');
            descriptionCell.className = 'px-space-lg py-space-sm font-body-md text-on-surface';
            descriptionCell.textContent = item.description;
            row.appendChild(descriptionCell);

            const walletCell = document.createElement('td');
            walletCell.className = 'px-space-lg py-space-sm font-body-md text-secondary';
            walletCell.textContent = item.wallet_label;
            row.appendChild(walletCell);

            const amountCell = document.createElement('td');
            amountCell.className = `px-space-lg py-space-sm font-label-md text-label-md text-right ${badge.amountClass}`;
            amountCell.textContent = `${badge.amountPrefix}Rp ${new Intl.NumberFormat('id-ID').format(item.amount)}`;
            row.appendChild(amountCell);

            return row;
        }

        function loadTransactionsTable() {
            loadTableData('transactionsTable', buildFilterUrl('transactionsTable'), renderTransactionRow);
        }
    </script>
@endsection
