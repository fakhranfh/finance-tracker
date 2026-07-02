<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Google Fonts: Inter for clean typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="antialiased bg-white text-gray-900 selection:bg-gray-900 selection:text-white">

    <!-- Navbar -->
    <header class="absolute inset-x-0 top-0 z-50">
        <nav class="flex items-center justify-between p-6 lg:px-8" aria-label="Global">
            <div class="flex lg:flex-1">
                <a href="/" class="-m-1.5 p-1.5 text-2xl font-bold tracking-tight text-gray-900">
                    {{ config('app.name', 'Laravel') }}
                </a>
            </div>

            <!-- Login / Register / Dashboard Links -->
            @if (Route::has('login'))
                <div class="flex flex-1 justify-end items-center gap-x-6">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-600 transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-600 transition">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-md bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 transition">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </nav>
    </header>

    <!-- Hero Section -->
    <main>
        <div class="relative isolate px-6 pt-14 lg:px-8">
            <div class="mx-auto max-w-3xl py-32 sm:py-48 lg:py-56 text-center">
                <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-6xl">
                    Solid Foundation for Your App
                </h1>
                <p class="mt-6 text-lg leading-8 text-gray-600">
                    Stop wasting time on complicated setups. Build your project on a clean codebase and focus on what actually matters.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="rounded-md bg-gray-900 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 transition">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="rounded-md bg-gray-900 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 transition">
                            Get Started
                        </a>
                        <a href="{{ route('login') }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-600 transition">
                            Already have an account? <span aria-hidden="true">→</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </main>

</body>
</html>