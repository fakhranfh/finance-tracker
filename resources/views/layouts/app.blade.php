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

            if (sidebar && spacer) {
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
    </script>
@endsection
