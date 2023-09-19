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
            {{ __('Link de Pagamento - ' . $payment->id ) }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col pb-5">
                <p><a class="btn btn-blue" href="{{url('/payments')}}">Voltar para Pagamentos</a></p>
                <h1 class="text-lg font-bold pt-7">Informações do Link</h1>

            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                <p class='mb-4'>Link: <a href="{{url('pay/' . $payment->id)}}" target="_blank">{{url('pay/' . $payment->id)}}</a></p>

                <p>Valor: R$ {{ str_replace('.', ',', $payment->value)}}</p>
                <p class='mb-4'>Descrição: {{$payment->description}}</p>
                <p>Status: {{ __('payments.status.' . $payment->status)}}</p>

                @if($payment->paid_at)
                <p>Pago em: {{ $payment->paid_at ? date("d/m/Y H:i:s ", strtotime($payment->paid_at)) : '-'}}</p>
                @endif

                @if($payment->cancelled_at)
                <p>Cancelado em: {{ $payment->cancelled_at ? date("d/m/Y H:i:s", strtotime($payment->cancelled_at)) : '-'}}</p>
                @endif

                @if($payment->expire_at)
                <p>Expira em: {{ $payment->expire_at ? date("d/m/Y H:i:s ", strtotime($payment->expire_at)) : '-'}}</p>
                @endif

                

                <div class="flex">
                  @if ($payment->status == 'paid')
                  <div class="my-5 mr-3">
                    <a class="btn btn-blue" href="{{url('/pay/' . $payment->id . '/receipt')}}" target="_blank"> Ver Recibo </a>
                  </div>
                  @endif
                  @if ($payment->status == 'active')
                  <div class="my-5 mr-3">
                    <a class="btn btn-blue" href="{{url('/payments/' . $payment->id . '/mark-as-paid')}}"> Marcar como Pago </a>
                  </div>
                  @endif
                  @if ($payment->status != 'paid')
                  <div class="my-5">
                    <a class="btn btn-red" href="{{url('/payments/' . $payment->id . '/delete')}}"> Remover </a>
                  </div>
                  @endif
                </div>

                @if ($payment->transaction_log ?? false)
                <p>Logs da transação:</p>
                {{$payment->transaction_log}}
                @endif
            </div>

            @if ($payment->status != 'paid')
            <div class="flex flex-col pb-5">
                <h1 class="text-lg font-bold pt-10">Editar Link</h1>

            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                @if (session('editMessage'))
                <div class="pb-5">
                  {{ session('editMessage')}}
                </div>
                @endif
                <form method="POST" action="{{$payment->id}}">
                @csrf
                @method('PATCH')
                  <label for="valueInput" >Valor de Pagamento</label>
                  <input class='form-control' id='valueInput' name='value' type="number" step="0.01" value="{{$payment->value}}">
                  
                  <label>Descrição</label>
                  <input class='form-control' name='description' id='descriptionInput' type="text" maxlength='100' value="{{$payment->description}}">
                  
                  <label>Válido até</label>
                  <input class='form-control' name='expire_at' id='expireAtInput' type="datetime-local" value="{{date('Y-m-d H:i:s', strtotime($payment->expire_at))}}">
                  
                  <label>Status</label>
                  <select class="form-control" name="status">
                    <option value='active' {{in_array($payment->status, ['active', 'expired']) ? 'selected' : ''}}>{{__('payments.status.active')}}</option>
                    <option value='inactive' {{$payment->status == 'inactive' ? 'selected' : ''}}>{{__('payments.status.inactive')}}</option>
                  </select>
            
                  <button type="submit" class="btn btn-blue mt-4" id="submitCreateLink">Salvar</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
