@extends('master')

@section('body_class', 'bg-background text-on-background min-h-screen flex flex-col font-body-md')

@section('content')
    <div class="flex h-screen flex-col">
        @if(!isset($skipTopbar) || !$skipTopbar)
            <x-topbar :title="$topbarTitle ?? 'Dashboard'" :showBackButton="$showBackButton ?? false" />
        @endif

        <div class="flex flex-1 overflow-hidden">
            @if(!isset($skipSidebar) || !$skipSidebar)
                <x-sidebar />
            @endif

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto py-space-lg px-gutter">
                <div class="max-w-7xl mx-auto">
                    @yield('app-content')
                </div>
            </main>
        </div>
    </div>

    @stack('modals')

    <script>
        document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const spacer = document.getElementById('sidebar-spacer');
            const backdrop = document.getElementById('sidebar-backdrop');

            if (!sidebar) {
                return;
            }

            const isMobile = window.matchMedia('(max-width: 639px)').matches;

            if (isMobile) {
                const isOpen = sidebar.classList.contains('translate-x-0');

                sidebar.classList.toggle('translate-x-0', !isOpen);
                sidebar.classList.toggle('-translate-x-full', isOpen);
                backdrop?.classList.toggle('hidden', isOpen);
                return;
            }

            if (spacer) {
                const isCollapsed = sidebar.style.width === '0px';

                if (isCollapsed) {
                    sidebar.style.width = '256px';
                    spacer.style.width = '256px';
                } else {
                    sidebar.style.width = '0px';
                    spacer.style.width = '0px';
                }
            }
        });

        document.getElementById('sidebar-backdrop')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar?.classList.remove('translate-x-0');
            sidebar?.classList.add('-translate-x-full');
            this.classList.add('hidden');
        });
    </script>
@endsection
