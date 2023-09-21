<?php
$parseStatus = [
  'active' => __('Active'),
  'inactive' => __('Inactive'),
  'cancelled' => __('Cancelled'),
  'paid' => __('Paid'),
  'expired' => __('Expired')
];

?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cliente') . ' - ' . $customer->name}}
        </h2>
    </x-slot>

    <div class="py-5">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col pb-5">
          <p><a class="btn btn-blue" href="{{url('/customers')}}">Voltar para Clientes</a></p>
          <h1 class="text-lg font-bold pt-7">Informações do Pagador</h1>

        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
          <p class='mb-4'>Cliente: {{$customer->id}}</p>

          <p>Nome: {{$customer->name}}</p>
          <p class=''>Email: {{$customer->email}}</p>
          <p class=''>CPF: {{$customer->cpf}}</p>
          <p class=''>Nº OAB: {{$customer->document ?? '-'}}</p>
        </div>
      </div>
    </div>

@section('js')

@endsection
</x-app-layout>
