@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $topbarTitle = 'Dashboard';
@endphp

@section('app-content')
    <div class="space-y-space-lg">

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-space-lg">
            <!-- Total Balance Card -->
            <div class="bg-surface border border-outline-variant rounded-lg p-space-lg hover:border-outline transition-colors duration-150">
                <div class="space-y-space-md">
                    <div class="flex items-center justify-between">
                        <span class="text-label-md text-secondary uppercase font-label-md">Total Saldo</span>
                        <span class="material-symbols-outlined text-primary text-[20px]">account_balance_wallet</span>
                    </div>
                    <div>
                        <p class="font-headline-md text-headline-md text-on-surface">Rp {{ number_format($totalBalance, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Income Card -->
            <div class="bg-surface border border-outline-variant rounded-lg p-space-lg hover:border-outline transition-colors duration-150">
                <div class="space-y-space-md">
                    <div class="flex items-center justify-between">
                        <span class="text-label-md text-secondary uppercase font-label-md">Pemasukan Bulan Ini</span>
                        <span class="material-symbols-outlined text-success text-[20px]">trending_up</span>
                    </div>
                    <div>
                        <p class="font-headline-md text-headline-md text-success">Rp {{ number_format($cashFlow['income'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Expense Card -->
            <div class="bg-surface border border-outline-variant rounded-lg p-space-lg hover:border-outline transition-colors duration-150">
                <div class="space-y-space-md">
                    <div class="flex items-center justify-between">
                        <span class="text-label-md text-secondary uppercase font-label-md">Pengeluaran Bulan Ini</span>
                        <span class="material-symbols-outlined text-error text-[20px]">trending_down</span>
                    </div>
                    <div>
                        <p class="font-headline-md text-headline-md text-error">Rp {{ number_format($cashFlow['expense'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Expense Categories -->
        <div class="bg-surface border border-outline-variant rounded-lg p-space-lg">
            <div class="flex items-center gap-space-md mb-space-md">
                <span class="material-symbols-outlined text-on-surface text-[20px]">pie_chart</span>
                <h2 class="font-headline-sm text-headline-sm text-on-surface">Pengeluaran Terbesar per Kategori (Bulan Ini)</h2>
            </div>

            @if ($topExpenseCategories->isEmpty())
                <p class="font-body-md text-secondary py-space-lg text-center">Belum ada pengeluaran bulan ini.</p>
            @else
                <div class="max-w-md mx-auto">
                    <canvas id="expense-category-chart"></canvas>
                </div>
            @endif
        </div>

        <!-- Recent Activity Section -->
        <div class="bg-surface border border-outline-variant rounded-lg overflow-hidden">
            <div class="px-space-lg py-space-md border-b border-outline-variant">
                <div class="flex items-center gap-space-md">
                    <span class="material-symbols-outlined text-on-surface text-[20px]">history</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface">Transaksi Terakhir</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-outline-variant bg-surface-container-lowest">
                            <th scope="col" class="px-space-lg py-space-md text-left font-label-md text-label-md text-secondary uppercase">Tanggal</th>
                            <th scope="col" class="px-space-lg py-space-md text-left font-label-md text-label-md text-secondary uppercase">Kategori</th>
                            <th scope="col" class="px-space-lg py-space-md text-left font-label-md text-label-md text-secondary uppercase">Dompet</th>
                            <th scope="col" class="px-space-lg py-space-md text-right font-label-md text-label-md text-secondary uppercase">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse ($recentTransactions as $transaction)
                            <tr class="hover:bg-surface-container-lowest transition-colors duration-150">
                                <td class="px-space-lg py-space-md font-body-md text-secondary">{{ $transaction->transaction_date->format('d M Y H:i') }}</td>
                                <td class="px-space-lg py-space-md font-body-md text-on-surface">{{ $transaction->category->name }}</td>
                                <td class="px-space-lg py-space-md font-body-md text-secondary">{{ $transaction->wallet->name }}</td>
                                <td class="px-space-lg py-space-md font-body-md text-right {{ $transaction->type->value === 'income' ? 'text-success' : 'text-error' }}">
                                    {{ $transaction->type->value === 'income' ? '+' : '-' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-space-lg py-space-lg text-center font-body-md text-secondary">Belum ada transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @if ($topExpenseCategories->isNotEmpty())
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    new Chart(document.getElementById('expense-category-chart'), {
                        type: 'pie',
                        data: {
                            labels: @json($topExpenseCategories->pluck('category_name')),
                            datasets: [{
                                data: @json($topExpenseCategories->pluck('total')),
                            }],
                        },
                        options: {
                            plugins: {
                                legend: { position: 'bottom' },
                            },
                        },
                    });
                });
            </script>
        @endpush
    @endif
@endsection
