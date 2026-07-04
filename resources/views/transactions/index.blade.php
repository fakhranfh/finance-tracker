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
                ['type' => 'enum', 'key' => 'type', 'label' => 'Type', 'options' => ['income' => 'Income', 'expense' => 'Expense', 'transfer' => 'Transfer']],
                ['type' => 'datetime', 'key' => 'date', 'label' => 'Date'],
            ]"
        >
            <x-slot:headers>
                <x-sortable-th table-id="transactionsTable" column="date" label="Date" />
                <x-sortable-th table-id="transactionsTable" column="type" label="Type" />
                <x-sortable-th table-id="transactionsTable" column="description" label="Description" />
                <x-sortable-th table-id="transactionsTable" column="wallet" label="Wallet" />
                <x-sortable-th table-id="transactionsTable" column="amount" label="Amount" align="right" />
                <th class="px-space-lg py-space-sm font-label-md text-label-md text-secondary uppercase text-right">Actions</th>
            </x-slot:headers>
        </x-data-table>
    </div>

    @push('modals')
        <!-- Delete Confirmation Modal -->
        <div id="deleteTransactionModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" onclick="closeDeleteTransactionModal()"></div>
            <div class="relative bg-surface-container-lowest rounded-xl shadow-lg w-full max-w-sm mx-space-md p-space-lg space-y-space-md">
                <h2 class="font-title-md text-title-md text-on-surface">Delete this entry?</h2>
                <p class="font-body-md text-secondary">This cannot be undone and will reverse the wallet balance.</p>
                <div class="flex justify-end gap-space-sm">
                    <button type="button" onclick="closeDeleteTransactionModal()"
                        class="px-space-lg py-space-sm rounded-lg font-label-md text-label-md text-secondary hover:bg-surface-container transition-colors">
                        Cancel
                    </button>
                    <button type="button" onclick="confirmDeleteTransactionEntry()"
                        class="px-space-lg py-space-sm rounded-lg bg-error text-on-error font-label-md text-label-md hover:opacity-90 transition-opacity">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endpush

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
            dateCell.textContent = item.date;
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

            const actionsCell = document.createElement('td');
            actionsCell.className = 'px-space-lg py-space-sm text-right';
            actionsCell.innerHTML = `
                <button type="button" title="Delete"
                    onclick="deleteTransactionEntry('${item.kind}', '${item.id}')"
                    class="p-space-xs rounded-md text-secondary hover:text-error hover:bg-surface-container-lowest transition-colors">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                </button>
            `;
            row.appendChild(actionsCell);

            return row;
        }

        const transactionDeleteBaseUrl = "{{ url('/transactions') }}";
        const transferDeleteBaseUrl = "{{ url('/transfers') }}";

        let pendingDeleteTransaction = null;

        function deleteTransactionEntry(kind, id) {
            pendingDeleteTransaction = { kind, id };
            document.activeElement?.blur();
            document.querySelector('nav')?.classList.add('pointer-events-none');
            document.getElementById('deleteTransactionModal').classList.remove('hidden');
        }

        function closeDeleteTransactionModal() {
            pendingDeleteTransaction = null;
            document.querySelector('nav')?.classList.remove('pointer-events-none');
            document.getElementById('deleteTransactionModal').classList.add('hidden');
        }

        function confirmDeleteTransactionEntry() {
            if (!pendingDeleteTransaction) {
                return;
            }

            const { kind, id } = pendingDeleteTransaction;
            closeDeleteTransactionModal();

            const baseUrl = kind === 'transfer' ? transferDeleteBaseUrl : transactionDeleteBaseUrl;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

            fetch(`${baseUrl}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            })
                .then(async (response) => {
                    const body = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(body.message || 'Failed to delete entry.');
                    }
                    loadTransactionsTable();
                })
                .catch((error) => alert(error.message));
        }

        function loadTransactionsTable() {
            loadTableData('transactionsTable', buildFilterUrl('transactionsTable'), renderTransactionRow);
        }
    </script>
@endsection
