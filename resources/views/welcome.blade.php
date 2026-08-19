<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @if (config('settings.gtm_tag'))
        <!-- Google Tag Manager -->
        <script>
            (function(w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({
                    'gtm.start': new Date().getTime(),
                    event: 'gtm.js'
                });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s),
                    dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', '{{ config('settings.gtm_tag') }}');
        </script>
    @endif

    <!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('settings.app_name', 'App') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="antialiased">
    @if (config('settings.gtm_tag'))
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('settings.gtm_tag') }}"
                height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
    @endif

    {{-- <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white"> --}}
    @if (Route::has('login'))
        <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
            @auth
                <a href="{{ url('/dashboard') }}"
                    class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
            @else
                <a href="{{ route('login') }}"
                    class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Login</a>

                @if (false)
                    <a href="{{ route('register') }}"
                        class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                @endif
            @endauth
        </div>
    @endif

    {{-- <div class="max-w-7xl mx-auto p-6 lg:p-8">
                <div class="my-10 mb-10">
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6 lg:gap-8">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Ferramenta de Pagamentos</h2>
                    </div>
                </div>
                <div class="mt-4 flex justify-center">
                    <img src="{{url('logo.png')}}" class="w-50 h-50">
                </div>

                

            </div> --}}
    {{-- </div> --}}

    {{-- <body class="bg-gray-50 text-gray-900 font-sans"> --}}

    <!-- Hero Section -->
    <section class="min-h-screen flex flex-col items-center justify-center text-center px-4">
        <img src="{{ config('settings.logo_url') }}" alt="{{ config('settings.app_name') }}"
            class="w-64 mb-6" />
        <h1 class="text-4xl font-bold mb-2">{{ config('settings.app_name') }}</h1>
        <p class="text-lg mb-6 max-w-xl">
            Manage, monitor and optimize your charging stations with ease. Reliable, fast, and secure.
        </p>
        <a href="#get-started" class="bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-700 transition">
            Get Started
        </a>
    </section>

    <!-- Features Section -->
    <section class="bg-white py-20 px-6">
        <div class="max-w-5xl mx-auto text-center">
            <h2 class="text-3xl font-semibold mb-8">Why Choose Charging Manager?</h2>
            <div class="grid md:grid-cols-3 gap-10">
                <div>
                    <h3 class="text-xl font-bold">Real-Time Monitoring</h3>
                    <p class="text-sm mt-2 text-gray-600">Track station status, power usage, and faults live.</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Smart Scheduling</h3>
                    <p class="text-sm mt-2 text-gray-600">Optimize energy consumption and reduce costs.</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Secure Access</h3>
                    <p class="text-sm mt-2 text-gray-600">Role-based controls and encrypted access for safety.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="get-started" class="bg-gray-100 py-16 px-6">
        <div class="max-w-xl mx-auto text-center">
            <h2 class="text-2xl font-bold mb-4">Ready to manage your charging stations?</h2>
            {{-- <p class="text-sm text-gray-600 mb-6">Contact us at <a href="mailto:contact@waygex.com" class="text-blue-600 underline">contact@waygex.com</a></p> --}}
            <a href="mailto:contact@waygex.com"
                class="bg-green-600 text-white px-6 py-3 mt-6 rounded-full hover:bg-green-700 transition">
                Contact Us
            </a>
        </div>
    </section>

    <footer class="text-center text-sm text-gray-500 py-6">
        &copy; 2025 Charging Manager by {{ env('APP_NAME') }}. All rights reserved.
    </footer>
</body>

</html>
