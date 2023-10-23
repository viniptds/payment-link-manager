<?php

$pages = 1;
$currentPage = 1;
$hasMorePages = false;

$statusColor = [
    'paid' => 'success',
    'active' => 'blue',
    'cancelled' => 'red',
    'expired' => 'yellow',
];
?>
<x-app-layout>
    <x-slot name="header">
      <div class="flex justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight content-center flex flex-wrap">
            {{ __('Links') }}
        </h2>
        <button class="btn btn-blue text-lg font-bold "
                data-te-toggle="modal"
                data-te-target="#createLinkModal"
                data-te-ripple-init
                data-te-ripple-color="light">Novo Link de Pagamento</button>
      </div>
      @if($errors->all())
      <div>
        <ul>
        @foreach ($errors->all() as $error)
          <li>{{$error}}</li>
        @endforeach
        </ul>
      </div>
      @endif
    </x-slot>

    <div class="py-5">
        <div class="max-w-7lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-y-hidden shadow-sm sm:rounded-lg p-5">
                <table id="links_table" class="w-full">
                    <thead class="py-5">
                        <th class="">Link</th>
                        <th class="">Descrição</th>
                        <th class="">Valor</th>
                        <th class="">Status</th>
                        <th class="">Criado Em</th>
                        <th>Exipira Em</th>
                        <th>Ações</th>
                    </thead>
                    <tbody>
                        @foreach ($links as $payment)
                        <tr class="p-5 m-10">
                            <td>
                              <div class="flex justify-between">
                              <a href="{{url('/pay') . '/' . $payment->id}}" target="_blank"> {{ $payment->id }}</a> 
                                <button type="button" class="btn-copy btn btn-blue mr-3" data-content="{{url('/pay') . '/' . $payment->id}}">Copiar</button>
                              </div>
                            <td>{{ $payment->description }}
                            <td>R$ {{ str_replace('.', ',', sprintf ("%.2f", $payment->value)) }}</td>
                            @if ($payment->status == 'active' && $payment->gatewayOperations->count())
                            <td> <p class="text-center alert-warning">Não Pago</p></td>
                            @else
                            <td> <p class="text-center alert-{{$statusColor[$payment->status] ?? 'info'}}">{{ __('payments.status.' . $payment->status) }}</p></td>
                            @endif
                            <td>{{ date('d/m/Y H:i:s', strtotime($payment->created_at)) }}</td>
                            <td>{{ $payment->expire_at ? date('d/m/Y H:i:s', strtotime($payment->expire_at)): 'Indeterminado' }}</td>
                            <td>
                              <a class="btn btn-info" href="{{url('/payments/' . $payment->id )}}">Ver</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <nav aria-label="" class='flex justify-center mt-5'>
                <ul class="list-style-none flex">
                  @if($currentPage > $pages)
                  <li>
                    <a
                      class="relative block rounded bg-transparent px-3 py-1.5 text-sm text-neutral-600 transition-all duration-300 hover:bg-neutral-100 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                      href="{{url('payments?page=' . ($i - 1))}}"
                      >{{__('Previous')}}</a
                    >
                  </li>

                  @endif
                  @for ($i = 1; $i <= $pages; $i++)
                  <li aria-current="page">
                    <a
                      class="relative block rounded bg-transparent px-3 py-1.5 text-sm text-neutral-600 transition-all duration-300 hover:bg-neutral-100 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                      href="{{url('payments?page=' . $i)}}"
                      >{{$i}}</a
                    >
                  </li>
                  @endfor
                  @if($currentPage < $pages)
                  <li>
                    <a
                      class="relative block rounded bg-transparent px-3 py-1.5 text-sm text-neutral-600 transition-all duration-300 hover:bg-neutral-100 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                      href="#"
                      >{{__('Next')}}</a
                    >
                  </li>
                  @endif
                </ul>
              </nav>
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
    class="pointer-events-none relative w-auto translate-y-[-50px] opacity-0 transition-all duration-300 ease-in-out min-[576px]:mx-auto min-[576px]:mt-12 min-[576px]:max-w-[500px] top-20">
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
      <form method="POST" action="payments">
        <!--Modal body-->
        <div class="relative flex-auto p-4" data-te-modal-body-ref>
              @csrf
              <div class="mb-2">
                <label for="valueInput" >Valor de Pagamento *</label>
                <input class='form-control' id='valueInput' name='value' type="number" step="0.01" min="{{env('CIELO_MIN_INSTALLMENT_VALUE', 50)}}" title="Valor mínimo de R$ 50,00" onkeyup="updateInstallments(this, 'maxInstallmentsSelect')" required>
              </div>
              <div class="mb-2">
                <label>Descrição *</label>
                <input class="form-control" name='description' id='descriptionInput' type="text" maxlength='100' required>
              </div>
              <div class="mb-2">
                <label>Válido até</label>
                <input class="form-control" name='expire_at' id='expireAtInput' type="datetime-local">
              </div>
              <div class="mb-2">
                <label>Número de Parcelas</label>
                <select class="form-control" name='max_installments' id='maxInstallmentsSelect'></select>
              </div>
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
            type="submit"
            class="btn btn-blue ml-1 inline-block rounded bg-primary  font-medium leading-normal "
            data-te-ripple-init
            data-te-ripple-color="light"
              id='submitCreateLink' >
            Criar Link
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@section('js')
<script src="{{url('/cielo/installment-calculator-dynamic.js')}}"></script>
<script>
    const maxInstallments = parseInt("{{env('CIELO_MAX_INSTALLMENTS', 12)}}");
    const installmentMinValue = parseFloat("{{env('CIELO_MIN_INSTALLMENT_VALUE', 50)}}");

    document.querySelectorAll('.btn-copy').forEach((item) => {
      item.addEventListener('click', function (evt) {
        let content = evt.target.dataset.content;
        navigator.clipboard.writeText(content);

        let target = evt.target;
        target.classList.add('btn-success');
        target.classList.remove('btn-blue');

        setTimeout(() => returnCopied(target), 3000);
      })
    });

    function returnCopied(element) {
      element.classList.add('btn-blue');
      element.classList.remove('btn-success');
    }
</script>
@endsection
</x-app-layout>
