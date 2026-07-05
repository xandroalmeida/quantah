<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        {{-- viewport-fit=cover habilita env(safe-area-inset-*) (notch/barra de gestos) — STORY-033 --}}
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

        {{-- PWA app-like (STORY-033): manifest standalone + metatags iOS (add à tela inicial sem barra) --}}
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#ffffff">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Quantah">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts — Inter 400/600/900 (DDR-001: família única do DS) -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,600,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
