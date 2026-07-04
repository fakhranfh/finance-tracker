@extends('master')

@section('title', 'Take control of your money')

@section('body_class', 'bg-background text-on-background font-body-md')

@push('styles')
    <style>
        @media (prefers-reduced-motion: no-preference) {
            .reveal {
                opacity: 0;
                transform: translateY(14px);
                animation: reveal 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            .reveal-delay-1 { animation-delay: 0.08s; }
            .reveal-delay-2 { animation-delay: 0.16s; }
            .reveal-delay-3 { animation-delay: 0.24s; }
        }

        @keyframes reveal {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .bar {
            transform-origin: bottom;
        }
        @media (prefers-reduced-motion: no-preference) {
            .bar {
                animation: grow 0.9s cubic-bezier(0.16, 1, 0.3, 1) backwards;
            }
        }
        @keyframes grow {
            from { transform: scaleY(0); }
            to { transform: scaleY(1); }
        }
    </style>
@endpush

@section('content')
    <!-- Navbar -->
    <header class="border-b border-outline-variant">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-gutter py-space-md lg:px-space-xl" aria-label="Global">
            <a href="/" class="flex items-center gap-x-space-xs">
                <img src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'Laravel') }}" class="h-8 w-auto">
                <span class="font-headline-sm text-headline-sm text-on-background">{{ config('app.name', 'Laravel') }}</span>
            </a>

            @if (Route::has('login'))
                <div class="flex items-center gap-x-space-lg">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-label-md text-label-md text-on-surface-variant hover:text-on-background transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="font-label-md text-label-md text-on-surface-variant hover:text-on-background transition-colors">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-lg bg-primary px-space-md py-space-xs font-label-md text-label-md text-on-primary shadow-sm hover:bg-[#003ea8] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-colors">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </nav>
    </header>

    <main>
        <!-- Hero -->
        <section class="mx-auto max-w-7xl px-gutter py-space-xl lg:px-space-xl lg:py-24">
            <div class="grid items-center gap-space-xl lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="reveal font-body-md text-body-md text-primary font-semibold">Personal finance, without the spreadsheet</p>
                    <h1 class="reveal reveal-delay-1 mt-space-sm font-headline-lg text-on-background text-[clamp(2.25rem,4vw+1rem,3.5rem)] font-bold leading-[1.08] tracking-[-0.03em]" style="text-wrap: balance;">
                        Know exactly where your money goes
                    </h1>
                    <p class="reveal reveal-delay-2 mt-space-md max-w-[42ch] font-body-lg text-body-lg text-on-surface-variant">
                        {{ config('app.name', 'Laravel') }} records every transaction, sorts it into categories, and shows your real cash flow &mdash; so decisions come from data, not guesswork.
                    </p>
                    <div class="reveal reveal-delay-3 mt-space-lg flex flex-wrap items-center gap-space-sm">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="rounded-lg bg-primary px-space-lg py-space-sm font-label-md text-label-md text-on-primary shadow-sm hover:bg-[#003ea8] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-colors">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-lg bg-primary px-space-lg py-space-sm font-label-md text-label-md text-on-primary shadow-sm hover:bg-[#003ea8] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-colors">
                                Get started for free
                            </a>
                            <a href="{{ route('login') }}" class="font-label-md text-label-md text-on-background hover:text-primary transition-colors">
                                I already have an account
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Product visual: a live-feeling balance card, not a stock illustration -->
                <div class="reveal reveal-delay-2 relative">
                    <div class="rounded-xl border border-outline-variant bg-surface p-space-lg shadow-[0_20px_60px_-15px_rgb(0,0,0,0.12)]">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-[0.05em]">Current balance</p>
                                <p class="mt-space-xxs font-headline-lg text-headline-lg text-on-background tabular-nums">Rp 24.850.000</p>
                            </div>
                            <span class="rounded-full bg-success-container px-space-sm py-space-xxs font-label-sm text-label-sm text-on-success-container">+12.4%</span>
                        </div>

                        <div class="mt-space-lg flex h-32 items-end gap-space-xs" role="img" aria-label="Monthly cash flow trending upward over six months">
                            <div class="bar flex-1 rounded-t-md bg-primary-fixed" style="height: 40%; animation-delay: .05s"></div>
                            <div class="bar flex-1 rounded-t-md bg-primary-fixed" style="height: 55%; animation-delay: .1s"></div>
                            <div class="bar flex-1 rounded-t-md bg-primary-fixed" style="height: 48%; animation-delay: .15s"></div>
                            <div class="bar flex-1 rounded-t-md bg-primary" style="height: 72%; animation-delay: .2s"></div>
                            <div class="bar flex-1 rounded-t-md bg-primary" style="height: 65%; animation-delay: .25s"></div>
                            <div class="bar flex-1 rounded-t-md bg-primary" style="height: 88%; animation-delay: .3s"></div>
                        </div>

                        <div class="mt-space-md flex items-center justify-between border-t border-outline-variant pt-space-md">
                            <div class="flex items-center gap-x-space-xs">
                                <span class="material-symbols-outlined text-[18px] text-on-success-container">arrow_upward</span>
                                <span class="font-body-sm text-body-sm text-on-surface-variant">Income</span>
                            </div>
                            <span class="font-body-sm text-body-sm text-on-background tabular-nums">Rp 9.200.000</span>
                        </div>
                        <div class="mt-space-xs flex items-center justify-between">
                            <div class="flex items-center gap-x-space-xs">
                                <span class="material-symbols-outlined text-[18px] text-error">arrow_downward</span>
                                <span class="font-body-sm text-body-sm text-on-surface-variant">Expenses</span>
                            </div>
                            <span class="font-body-sm text-body-sm text-on-background tabular-nums">Rp 3.650.000</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="border-t border-outline-variant bg-surface-container-low">
            <div class="mx-auto max-w-7xl px-gutter py-space-xl lg:px-space-xl">
                <div class="max-w-[50ch]">
                    <h2 class="font-headline-md text-headline-md text-on-background" style="text-wrap: balance;">
                        Built around the questions you actually ask
                    </h2>
                </div>

                <div class="mt-space-xl grid gap-space-lg lg:grid-cols-3">
                    <div class="group">
                        <span class="material-symbols-outlined flex h-11 w-11 items-center justify-center rounded-lg bg-primary-container text-2xl text-on-primary-container">
                            receipt_long
                        </span>
                        <h3 class="mt-space-md font-label-md text-headline-sm text-on-background">Transaction tracking</h3>
                        <p class="mt-space-xs font-body-md text-body-md text-on-surface-variant">
                            Log income and expenses in seconds, each one dated and categorized so nothing gets lost in a spreadsheet.
                        </p>
                    </div>

                    <div class="group">
                        <span class="material-symbols-outlined flex h-11 w-11 items-center justify-center rounded-lg bg-primary-container text-2xl text-on-primary-container">
                            monitoring
                        </span>
                        <h3 class="mt-space-md font-label-md text-headline-sm text-on-background">Cash flow overview</h3>
                        <p class="mt-space-xs font-body-md text-body-md text-on-surface-variant">
                            Balance, income, and spending in one dashboard that updates the moment a transaction is recorded.
                        </p>
                    </div>

                    <div class="group">
                        <span class="material-symbols-outlined flex h-11 w-11 items-center justify-center rounded-lg bg-primary-container text-2xl text-on-primary-container">
                            category
                        </span>
                        <h3 class="mt-space-md font-label-md text-headline-sm text-on-background">Flexible categories</h3>
                        <p class="mt-space-xs font-body-md text-body-md text-on-surface-variant">
                            Group spending your own way, so reports reflect how you actually think about your money.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="bg-primary">
            <div class="mx-auto max-w-2xl px-gutter py-space-xl text-center lg:px-space-xl">
                <h2 class="font-headline-md text-headline-md text-on-primary" style="text-wrap: balance;">
                    Start managing your finances today
                </h2>
                <p class="mx-auto mt-space-sm max-w-[45ch] font-body-lg text-body-lg text-primary-fixed">
                    Free to get started. No credit card required.
                </p>
                <div class="mt-space-lg">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-block rounded-lg bg-on-primary px-space-lg py-space-sm font-label-md text-label-md text-primary shadow-sm hover:bg-surface-container-lowest focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-primary transition-colors">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="inline-block rounded-lg bg-on-primary px-space-lg py-space-sm font-label-md text-label-md text-primary shadow-sm hover:bg-surface-container-lowest focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-primary transition-colors">
                            Sign up now
                        </a>
                    @endauth
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-outline-variant">
        <div class="mx-auto max-w-7xl px-gutter py-space-lg text-center lg:px-space-xl">
            <p class="font-body-sm text-body-sm text-on-surface-variant">
                &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
            </p>
        </div>
    </footer>
@endsection
