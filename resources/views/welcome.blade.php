<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{config('settings.app_name')}}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    </head>
    <body class="antialiased">
        {{-- <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white"> --}}
            @if (Route::has('login'))
                <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Login</a>

                        @if (false)
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
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
              <img src="{{asset('storage/assets/' . config('settings.logo_main', 'logo.png'))}}" alt="Charging Manager Logo" class="w-64 h-64 mb-6" />
              <h1 class="text-4xl font-bold mb-2">{{__('app.name')}}</h1>
              <p class="text-lg mb-6 max-w-xl">
                {{__("app.description")}}
              </p>
              <a href="login" class="bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-700 transition">
                {{__('home.cta_1')}}
              </a>
            </section>
          
            <!-- Features Section -->
            <section class="bg-white py-20 px-6">
              <div class="max-w-5xl mx-auto text-center">
                <h2 class="text-3xl font-semibold mb-8">{{__('home.section_1_header')}}</h2>
                <div class="grid md:grid-cols-3 gap-10">
                  <div>
                    <h3 class="text-xl font-bold">{{__('home.section_1_option_1_header')}}</h3>
                    <p class="text-sm mt-2 text-gray-600">{{__('home.section_1_option_1_text')}}</p>
                  </div>
                  <div>
                    <h3 class="text-xl font-bold">{{__('home.section_1_option_2_header')}}</h3>
                    <p class="text-sm mt-2 text-gray-600">{{__('home.section_1_option_2_text')}}</p>
                  </div>
                  <div>
                    <h3 class="text-xl font-bold">{{__('home.section_1_option_3_header')}}</h3>
                    <p class="text-sm mt-2 text-gray-600">{{__('home.section_1_option_3_text')}}</p>
                  </div>
                </div>
              </div>
            </section>
          
            <!-- Contact Section -->
            <section id="get-started" class="bg-gray-100 py-16 px-6">
              <div class="max-w-xl mx-auto text-center">
                <h2 class="text-2xl font-bold mb-4">{{__('home.cta_section_1_title')}}</h2>
                {{-- <p class="text-sm text-gray-600 mb-6">Contact us at <a href="mailto:contact@waygex.com" class="text-blue-600 underline">contact@waygex.com</a></p> --}}
                <a href="mailto:contact@waygex.com" class="bg-green-600 text-white px-6 py-3 mt-6 rounded-full hover:bg-green-700 transition">
                  {{__('home.cta_section_1_button')}}
                </a>
              </div>
            </section>
          
            <footer class="text-center text-sm text-gray-500 py-6">
              &copy; 2025 {{__('app.name')}} - {{env('APP_NAME')}}. {{__('config.copyright')}}
            </footer>
    </body>
</html>
