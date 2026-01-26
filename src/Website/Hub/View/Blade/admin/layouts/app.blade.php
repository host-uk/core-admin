@php
    $darkMode = request()->cookie('dark-mode') === 'true';
@endphp
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overscroll-none {{ $darkMode ? 'dark' : '' }}"
      style="color-scheme: {{ $darkMode ? 'dark' : 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin' }} - {{ config('app.name', 'Host Hub') }}</title>

    {{-- Critical CSS: Prevents white flash during page load/navigation --}}
    <style>
        html {
            background-color: #f3f4f6;
        }

        html.dark {
            background-color: #111827;
        }
    </style>

    <script>
        {{-- Sync all settings from localStorage to cookies for PHP middleware --}}
        (function () {
            // Dark mode - sync our key with Flux's key
            var darkMode = localStorage.getItem('dark-mode');
            if (darkMode === 'true') {
                // Sync to Flux's appearance key so the Flux directive doesn't override
                localStorage.setItem('flux.appearance', 'dark');
            } else if (darkMode === 'false') {
                localStorage.setItem('flux.appearance', 'light');
            }
            // Set cookie for PHP
            document.cookie = 'dark-mode=' + (darkMode || 'false') + '; path=/; SameSite=Lax';

            // Icon settings
            var iconStyle = localStorage.getItem('icon-style') || 'fa-notdog fa-solid';
            var iconSize = localStorage.getItem('icon-size') || 'fa-lg';
            document.cookie = 'icon-style=' + iconStyle + '; path=/; SameSite=Lax';
            document.cookie = 'icon-size=' + iconSize + '; path=/; SameSite=Lax';
        })();
    </script>

    <!-- Fonts -->
    @include('layouts::partials.fonts')

    <!-- Font Awesome -->
    @if(file_exists(public_path('vendor/fontawesome/css/all.min.css')))
        <link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css?v={{ filemtime(public_path('vendor/fontawesome/css/all.min.css')) }}">
    @else
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @endif

    <!-- Scripts -->
    @vite(['resources/css/admin.css', 'resources/js/app.js'])

    <!-- Flux -->
    @fluxAppearance
</head>
<body
    class="font-inter antialiased bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 overscroll-none"
    x-data="{ sidebarOpen: false }"
    @open-sidebar.window="sidebarOpen = true"
>


<!-- Page wrapper -->
<div class="flex h-[100dvh] overflow-hidden overscroll-none">

    @include('hub::admin.components.sidebar')

    <!-- Content area (offset for fixed sidebar) -->
    <div
        class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden overscroll-none ml-0 sm:ml-20 lg:ml-64"
        x-ref="contentarea">

        @include('hub::admin.components.header')

        <main class="grow px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
            {{ $slot }}
        </main>

    </div>

</div>

<!-- Toast Notifications -->
@persist('toast')
    <flux:toast position="bottom end" />
@endpersist

<!-- Global Search (Command+K) -->
@persist('global-search')
    <livewire:hub.admin.global-search />
@endpersist

<!-- Developer Bar (Hades accounts only) -->
@include('hub::admin.components.developer-bar')

<!-- Flux Scripts -->
@fluxScripts

@stack('scripts')

<script>
    // Light/Dark mode toggle (guarded for Livewire navigation)
    (function() {
        if (window.__lightSwitchInitialized) return;
        window.__lightSwitchInitialized = true;

        const lightSwitch = document.querySelector('.light-switch');
        if (lightSwitch) {
            lightSwitch.addEventListener('change', () => {
                const {checked} = lightSwitch;
                document.documentElement.classList.toggle('dark', checked);
                document.documentElement.style.colorScheme = checked ? 'dark' : 'light';
                localStorage.setItem('dark-mode', checked);
            });
        }
    })();
</script>
</body>
</html>
