<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="pb-5">
                <h1 class="text-lg font-bold ">{{__("Welcome")}}</h1>
            </div>
            Acesse a aba de Links para mais informações
        </div>
    </div>
</x-app-layout>
