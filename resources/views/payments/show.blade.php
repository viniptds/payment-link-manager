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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex pb-5">

                <a class="btn btn-blue" href="{{url('/payments')}}">Voltar para Pagamentos</a>
                <h1 class="text-lg font-bold ">Informações do Link</h1>

            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                <p>Id: {{$payment->id}}</p>

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

                <p>Descrição: {{$payment->description}}</p>

                @if ($payment->status == 'paid')
                <!-- TODO: Mostra o cupom/comprovante -->
                @endif
                <br>
                <br>
                <p>Logs da transação:</p>
                {{$payment->transaction_log}}
            </div>
        </div>
    </div>

    <div
  data-te-modal-init
  class="fixed left-0 top-20 z-[1055] hidden h-full w-full overflow-y-auto overflow-x-hidden outline-none"
  id="createLinkModal"
  tabindex="-1"
  aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div
    data-te-modal-dialog-ref
    class="pointer-events-none relative w-auto translate-y-[-50px] opacity-0 transition-all duration-300 ease-in-out min-[576px]:mx-auto min-[576px]:mt-7 min-[576px]:max-w-[500px]">
    <div
      class="min-[576px]:shadow-[0_0.5rem_1rem_rgba(#000, 0.15)] pointer-events-auto relative flex w-full flex-col rounded-md border-none bg-white bg-clip-padding text-current shadow-lg outline-none dark:bg-neutral-600">
      <div
        class="flex flex-shrink-0 items-center justify-between rounded-t-md border-b-2 border-neutral-100 border-opacity-100 p-6 dark:border-opacity-50 bg-blue-400">
        <!--Modal title-->
        <h5
          class="text-xl font-medium leading-normal text-neutral-800 dark:text-neutral-200"
          id="exampleModalLabel">
          Novo Link de Pagamento
        </h5>
        <!--Close button-->
        <button
          type="button"
          class="box-content rounded-none border-none hover:no-underline hover:opacity-75 focus:opacity-100 focus:shadow-none focus:outline-none"
          data-te-modal-dismiss
          aria-label="Close">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="h-6 w-6">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!--Modal body-->
      <div class="relative flex-auto p-4" data-te-modal-body-ref>
        <form method="POST" action="payments">
            @csrf

            <label for="valueInput" >Valor de Pagamento</label>
            <input class='form-control' id='valueInput' name='value' type="number" step="0.01">
            <label>Descrição</label>
            <input name='description' id='descriptionInput' type="text" maxlength='100'>
            <label>Válido até</label>
            <input name='expire_at' id='expireAtInput' type="datetime-local">
            <button type="submit" id="submitCreateLink"></button>
        </form>
      </div>

      <!--Modal footer-->
      <div
        class="flex flex-shrink-0 flex-wrap items-center justify-end rounded-b-md border-t-2 border-neutral-100 border-opacity-100 p-4 dark:border-opacity-50">
        <button
          type="button"
          class="inline-block rounded bg-primary-100 px-6 pb-2 pt-2.5 text-xs font-medium uppercase leading-normal text-primary-700 transition duration-150 ease-in-out hover:bg-primary-accent-100 focus:bg-primary-accent-100 focus:outline-none focus:ring-0 active:bg-primary-accent-200"
          data-te-modal-dismiss
          data-te-ripple-init
          data-te-ripple-color="light">
          Close
        </button>
        <button
          type="button"
          class="ml-1 inline-block rounded bg-primary px-6 pb-2 pt-2.5 text-xs font-medium uppercase leading-normal text-white shadow-[0_4px_9px_-4px_#3b71ca] transition duration-150 ease-in-out hover:bg-primary-600 hover:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.3),0_4px_18px_0_rgba(59,113,202,0.2)] focus:bg-primary-600 focus:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.3),0_4px_18px_0_rgba(59,113,202,0.2)] focus:outline-none focus:ring-0 active:bg-primary-700 active:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.3),0_4px_18px_0_rgba(59,113,202,0.2)] dark:shadow-[0_4px_9px_-4px_rgba(59,113,202,0.5)] dark:hover:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.2),0_4px_18px_0_rgba(59,113,202,0.1)] dark:focus:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.2),0_4px_18px_0_rgba(59,113,202,0.1)] dark:active:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.2),0_4px_18px_0_rgba(59,113,202,0.1)]"
          data-te-ripple-init
          data-te-ripple-color="light"
            id='submitCreateLink' >
          Criar Link
        </button>
      </div>
    </div>
  </div>
</div>

@section('js')
<script>
    function sendLink(evt){
        console.log(evt);
        let action = evt.target.form.action;
        console.log(evt, action);
        evt.preventDefault();

        let description = document.querySelector('#descriptionInput');
        let value = document.querySelector('#valueInput');
        let expireAt = document.querySelector('#expireAtInput');
        let data = [
            description,
            value,
            expireAt
        ]
        fetch(action, data)
        .then((res) => {

        })
    }
    // document.querySelector('#submitCreateLink').addEventListener('click', function (evt) { alert('aa'); sendLink(evt)});
    document.querySelector('#submitCreateLink').onclick = function (evt) { alert('aa'); sendLink(evt)};
</script>
@endsection
</x-app-layout>
