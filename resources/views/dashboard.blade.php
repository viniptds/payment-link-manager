<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="pb-5">
                <h1 class="text-lg font-bold ">{{__("Welcome")}}</h1>
            </div>
            Acesse a aba de Links para mais informações
        </div>
    </div> -->

    <div class="py-5">
        <div class="max-w-12lg mx-auto px-2 lg:px-8 sm:px-6 gap-4 grid sm:grid-cols-2 lg:grid-cols-6 grid-cols-1 lg:px-3 flex flex-wrap">
            <div class="bg-white grow flex-1  shadow-sm sm:rounded-lg p-5">
                <p class="font-bold" >Total de Links</p>
                <p>{{$data['payments']}}</p>
            </div>
            <div class="bg-white grow flex-1  shadow-sm sm:rounded-lg p-5">
                <p class="font-bold">Ativos</p>
                <p>{{$data['active']}}</p>
            </div>
            <div class="bg-white grow flex-1  shadow-sm sm:rounded-lg p-5">
                <p class="font-bold">Inativos</p>
                <p>{{$data['inactive']}}</p>
            </div>
            <div class="bg-white grow flex-1  shadow-sm sm:rounded-lg p-5">
                <p class="font-bold">Pagos</p>
                <p>{{$data['paid']}}</p>
            </div>
            <div class="bg-white grow flex-1  shadow-sm sm:rounded-lg p-5">
                <p class="font-bold">Estornados</p>
                <p>{{$data['canceled']}}</p>
            </div>
            <div class="bg-white grow flex-1  shadow-sm sm:rounded-lg p-5">
                <p class="font-bold">Expirados</p>
                <p>{{$data['expired']}}</p>
            </div>
        </div>
    </div>
</x-app-layout>
