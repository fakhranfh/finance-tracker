@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $topbarTitle = 'Dashboard';
@endphp

@section('app-content')
    <div class="space-y-space-lg">

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-space-lg">
                <!-- Total Users Card -->
                <div class="bg-surface border border-outline-variant rounded-lg p-space-lg hover:border-outline transition-colors duration-150">
                    <div class="space-y-space-md">
                        <div class="flex items-center justify-between">
                            <span class="text-label-md text-secondary uppercase font-label-md">Total Pengguna</span>
                            <span class="material-symbols-outlined text-primary text-[20px]">group</span>
                        </div>
                        <div>
                            <p class="font-headline-md text-headline-md text-on-surface">1.284</p>
                            <div class="flex items-center gap-space-xs mt-space-sm">
                                <span class="material-symbols-outlined text-[14px] text-success">trending_up</span>
                                <p class="font-body-sm text-body-sm text-success">+12% bulan ini</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Card -->
                <div class="bg-surface border border-outline-variant rounded-lg p-space-lg hover:border-outline transition-colors duration-150">
                    <div class="space-y-space-md">
                        <div class="flex items-center justify-between">
                            <span class="text-label-md text-secondary uppercase font-label-md">Pendapatan</span>
                            <span class="material-symbols-outlined text-success text-[20px]">attach_money</span>
                        </div>
                        <div>
                            <p class="font-headline-md text-headline-md text-on-surface">Rp 45.000.000</p>
                            <div class="flex items-center gap-space-xs mt-space-sm">
                                <span class="material-symbols-outlined text-[14px] text-secondary">dashboard</span>
                                <p class="font-body-sm text-body-sm text-secondary">Stabil</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Status Card -->
                <div class="bg-surface border border-outline-variant rounded-lg p-space-lg hover:border-outline transition-colors duration-150">
                    <div class="space-y-space-md">
                        <div class="flex items-center justify-between">
                            <span class="text-label-md text-secondary uppercase font-label-md">Sistem Status</span>
                            <span class="material-symbols-outlined text-info text-[20px]">shield</span>
                        </div>
                        <div>
                            <p class="font-headline-md text-headline-md text-success">Optimal</p>
                            <div class="flex items-center gap-space-xs mt-space-sm">
                                <span class="material-symbols-outlined text-[14px] text-success" data-weight="fill">check_circle</span>
                                <p class="font-body-sm text-body-sm text-secondary">Semua layanan berjalan baik</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="bg-surface border border-outline-variant rounded-lg overflow-hidden">
                <div class="px-space-lg py-space-md border-b border-outline-variant">
                    <div class="flex items-center gap-space-md">
                        <span class="material-symbols-outlined text-on-surface text-[20px]">history</span>
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Aktivitas Terbaru</h2>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container-lowest">
                                <th scope="col" class="px-space-lg py-space-md text-left font-label-md text-label-md text-secondary uppercase">Nama</th>
                                <th scope="col" class="px-space-lg py-space-md text-left font-label-md text-label-md text-secondary uppercase">Aktivitas</th>
                                <th scope="col" class="px-space-lg py-space-md text-left font-label-md text-label-md text-secondary uppercase">Tanggal</th>
                                <th scope="col" class="px-space-lg py-space-md text-left font-label-md text-label-md text-secondary uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr class="hover:bg-surface-container-lowest transition-colors duration-150">
                                <td class="px-space-lg py-space-md font-body-md text-on-surface">Budi Santoso</td>
                                <td class="px-space-lg py-space-md font-body-md text-secondary">Login ke sistem</td>
                                <td class="px-space-lg py-space-md font-body-md text-secondary">18 Jun 2026</td>
                                <td class="px-space-lg py-space-md">
                                    <div class="inline-flex items-center gap-space-xs px-space-sm py-space-xs rounded-full bg-success/10 border border-success/20">
                                        <span class="material-symbols-outlined text-[14px] text-success" data-weight="fill">check_circle</span>
                                        <span class="font-label-md text-label-md text-success">Sukses</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors duration-150">
                                <td class="px-space-lg py-space-md font-body-md text-on-surface">Siti Aminah</td>
                                <td class="px-space-lg py-space-md font-body-md text-secondary">Memperbarui profil</td>
                                <td class="px-space-lg py-space-md font-body-md text-secondary">17 Jun 2026</td>
                                <td class="px-space-lg py-space-md">
                                    <div class="inline-flex items-center gap-space-xs px-space-sm py-space-xs rounded-full bg-success/10 border border-success/20">
                                        <span class="material-symbols-outlined text-[14px] text-success" data-weight="fill">check_circle</span>
                                        <span class="font-label-md text-label-md text-success">Sukses</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors duration-150">
                                <td class="px-space-lg py-space-md font-body-md text-on-surface">Andi Setiawan</td>
                                <td class="px-space-lg py-space-md font-body-md text-secondary">Gagal reset password</td>
                                <td class="px-space-lg py-space-md font-body-md text-secondary">16 Jun 2026</td>
                                <td class="px-space-lg py-space-md">
                                    <div class="inline-flex items-center gap-space-xs px-space-sm py-space-xs rounded-full bg-error/10 border border-error/20">
                                        <span class="material-symbols-outlined text-[14px] text-error" data-weight="fill">cancel</span>
                                        <span class="font-label-md text-label-md text-error">Gagal</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

    </div>
@endsection