<?php
$parseStatus = [
    'active' => __('Active'),
    'inactive' => __('Inactive'),
    'cancelled' => __('Cancelled'),
    'paid' => __('Paid'),
    'expired' => __('Expired'),
];
$statusColor = [
    'paid' => 'success',
    'active' => 'blue',
    'cancelled' => 'red',
    'expired' => 'yellow',
];
$hasPayment = ($payment->latestPayment->type ?? null) == 'pay';
$transactions = $payment->gatewayOperations()->orderByDesc('created_at')->get()->all() ?? false;

$customer = $payment->customer ?? false;
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ url('/payments') }}">{{ __('payment_links_header') }}</a> / <a
                href="{{ url('pay/' . $payment->id) }}" target="_blank">{{ $payment->id }}</a>
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col pb-5">
                <h1 class="text-lg font-bold pt-7">Informações do Link</h1>

            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                <p class='mb-4'>Link Público: <a href="{{ url('pay/' . $payment->id) }}"
                        target="_blank">{{ url('pay/' . $payment->id) }}</a></p>

                <p>Valor: R$ {{ str_replace('.', ',', sprintf('%.2f', $payment->value)) }}</p>
                <p class='mb-4'>Descrição: {{ $payment->description }}</p>
                <p class='mb-4'>Status:
                    @if ($payment->status == 'active' && $payment->gatewayOperations->count())
                        <span class="alert-warning">Não Pago</span>
                </p>
            @else
                <span
                    class="alert-{{ $statusColor[$payment->status] ?? 'info' }}">{{ __('payments.status.' . $payment->status) }}</span>
                </p>
                @endif
                @if ($payment->cancelled_at)
                    <p class='mb-4'>Cancelado em:
                        {{ $payment->cancelled_at ? date('d/m/Y H:i:s', strtotime($payment->cancelled_at)) : '-' }}</p>
                @endif
                @if ($payment->expire_at)
                    <p class='mb-4'>Expira em:
                        {{ $payment->expire_at ? date('d/m/Y H:i:s ', strtotime($payment->expire_at)) : '-' }}</p>
                @endif

                @if ($payment->paid_at)
                    <p class='mb-4'>Pago em:
                        {{ $payment->paid_at ? date('d/m/Y H:i:s ', strtotime($payment->paid_at)) : '-' }}</p>
                @endif

                <div class="flex">
                    @if ($payment->status == 'paid')
                        <div class="my-5 mr-3">
                            <button class="btn btn-blue" onclick="viewReceipt()" target="_blank"> Ver Recibo </button>
                        </div>
                        @if ($hasPayment)
                            @if (date('Y-m-d H:i:s', strtotime($payment->paid_at . ' + 1 day')) <= date('Y-m-d H:i:s'))
                                <div class="my-5 mr-3">
                                    <button class="btn btn-red cursor-pointer" id="btnCancelPurchase"
                                        data-paymentid="{{ $payment->id }}">Cancelar Compra</button>
                                </div>
                            @else
                                <div class="my-5 mr-3">
                                    <p class="">A compra poderá ser cancelada após 24 horas do pagamento</p>
                                </div>
                            @endif
                        @endif
                    @endif
                    @if (Auth::user()->is_admin && in_array($payment->status, ['active', 'inactive']))
                        <div class="my-5 mr-3">
                            <button onclick="toggleStatus()"
                                class="btn btn-{{ $payment->status == 'active' ? 'info' : 'success' }}">
                                {{ $payment->status == 'active' ? 'Desativar' : 'Ativar' }} </button>
                        </div>
                    @endif
                    @if ($payment->status == 'active')
                        <div class="my-5 mr-3">
                            <button class="btn btn-blue" onclick="confirmMarkAsPaid()">Marcar como Pago </button>
                            {{-- <a class="btn btn-blue" href="{{url('/payments/' . $payment->id . '/mark-as-paid')}}"> Marcar como Pago </a> --}}
                        </div>
                    @endif
                    @if (empty($payment->gatewayOperations) || !in_array($payment->status, ['paid', 'cancelled']))
                        <div class="my-5">

                            <button class="btn btn-red" onclick="confirmRemove()">Remover </button>
                            {{-- <a class="btn btn-red" href="{{url('/payments/' . $payment->id . '/delete')}}"> Remover </a> --}}
                        </div>
                    @endif
                </div>
                <div id="messageBox" class="hidden p-3"></div>

                @if ($transactions)
                    <p class="text-lg my-3 font-bold">Movimentações: </p>
                    @foreach ($transactions as $paymentTransaction)
                        <?php
                        $log = json_decode($paymentTransaction->log, 1);
                        ?>
                        <p class="">Data: {{ date('d/m/Y H:i:s', strtotime($paymentTransaction->created_at)) }}
                        </p>
                        <p class="">Tipo:
                            {{ $paymentTransaction->type == 'void' ? 'Cancelamento' : 'Pagamento' }}</p>
                        @if ($paymentTransaction->status)
                            <p class="">Cartão:
                                {{ substr($log['creditCard']['cardNumber'], strpos($log['creditCard']['cardNumber'], '*')) }}
                            </p>
                        @endif
                        <p class="">Status: {{ $paymentTransaction->status ? 'Aprovado' : 'Negado' }}</p>
                        <details class="mt-2 mb-4 ">
                            <summary class="w-16 "><span class="btn btn-info">Logs</span></summary>
                            <p class="flex break-all m-2">{{ $paymentTransaction->log }}</p>

                        </details>
                    @endforeach
                @endif
            </div>


            @if ($customer)
                <div class="flex flex-col pb-5">
                    <h1 class="text-lg font-bold pt-7">Informações do Cliente</h1>

                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                    <p class='mb-4'>Cliente: {{ $customer->id }}</p>

                    <p>Nome: {{ $customer->name }}</p>
                    <p class=''>Email: {{ $customer->email }}</p>
                    <p class=''>CPF: {{ $customer->cpf }}</p>
                    <p class=''>Nº OAB: {{ $customer->document }}</p>

                    <div class="flex">
                        <div class="my-5 mr-3">
                            <a class="btn btn-blue" href="{{ url('/customers/' . $customer->id) }}" target="_blank">
                                Ver Cliente </a>
                        </div>
                    </div>
                </div>
            @endif
            @if (in_array($payment->status, ['active', 'inactive', 'expired']))
                <div class="flex flex-col pb-5">
                    <h1 class="text-lg font-bold pt-10">Editar Link</h1>

                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 gap-4">
                    @if (session('editMessage'))
                        <div class="pb-5">
                            {{ session('editMessage') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ $payment->id }}" id="sendUpdateLinkForm">
                        @csrf
                        @method('PATCH')
                        <label for="valueInput">Valor de Pagamento</label>
                        <input class='form-control money-mask' id='valueInput' name='value' type="text"
                            onkeyup="calculateValue(this)">

                        <label>Descrição</label>
                        <input class='form-control' name='description' id='descriptionInput' type="text"
                            maxlength='100' value="{{ $payment->description }}">

                        <label>Válido até</label>
                        <input class='form-control' name='expire_at' id='expireAtInput' type="datetime-local"
                            value="{{ $payment->expire_at ? date('Y-m-d H:i:s', strtotime($payment->expire_at)) : null }}">

                        <div class="grid grid-cols-2 gap-2 my-3">
                            <div class="">
                                <label>Definir número de parcelas</label>
                                <select class="form-control" name='max_installments'
                                    id='maxInstallmentsSelect'></select>
                            </div>
                            <div class="">
                                <label>Permitir parcelamento em</label>
                                <select class="form-control" name='max_installments_type'
                                    id='typeMaxInstallmentsSelect'>
                                    <option value=""
                                        {{ $payment->max_installments_type == '' ? 'selected' : '' }}>1x a
                                        {{ env('CIELO_MAX_INSTALLMENTS', 12) }}x</option>
                                    <option value="max"
                                        {{ $payment->max_installments_type == 'max' ? 'selected' : '' }}>1x a <span
                                            class="maxInstallmentsValue"></span>x</option>
                                    <option value="min"
                                        {{ $payment->max_installments_type == 'min' ? 'selected' : '' }}>
                                        <span class="maxInstallmentsValue"></span>x a
                                        {{ env('CIELO_MAX_INSTALLMENTS', 12) }}x
                                    </option>
                                    <option value="exact"
                                        {{ $payment->max_installments_type == 'exact' ? 'selected' : '' }}>Somente em
                                        <span class="maxInstallmentsValue"></span>x
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- <label>Atribuir cliente</label>
                  <select class="form-control" name="customer_id">
                    <option value="">Selecione</option>
                    @foreach ($customers as $customer)
                    <option value='{{$customer->id}}' {{$payment->customer_id == $customer->id ? 'selected' : ''}}>{{$customer->name}}</option>
                    @endforeach
                  </select> --}}

                        {{-- <input type="text" id="searchableGateway" class="form-control"> --}}
                        <label>Atribuir gateway(s)</label>
                        <div class="options">
                            <input type="hidden" name="customer_id">
                            <select class="form-control" name="gateway_ids[]" multiple>
                                <option value="">Selecione</option>
                                @foreach ($available_gateways as $gateway)
                                    <option value='{{ $gateway->id }}'
                                        {{ in_array($gateway->id, $payment->gateways->pluck('id')->toArray()) ? 'selected' : '' }}>
                                        {{ $gateway->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <ul>
                                @foreach ($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>

                        </div>
                        <button type="submit" class="btn btn-blue mt-4" id="submitCreateLink">Salvar</button>
                    </form>
                </div>
        </div>
        @endif
    </div>
    </div>

    @section('js')
        <script>
            const maxInstallments = parseInt("{{ env('CIELO_MAX_INSTALLMENTS', 12) }}");
            const installmentMinValue = parseFloat("{{ env('CIELO_MIN_INSTALLMENT_VALUE', 50) }}");
            const paymentMaxInstallments = parseInt('{{ $payment->max_installments ?? '1' }}');
            const paymentInitialValue = '{{ $payment->value ?? '0' }}';
            var availableMaxInstallments = 0;

            console.log('paymentMaxInstallments', paymentMaxInstallments);
            function calculateValue(input) {
                let value = $(input).maskMoney('unmasked')[0];
                updateInstallments(parseFloat(value), 'maxInstallmentsSelect', 1);
            }

            function confirmMarkAsPaid() {
                if (confirm('Tem certeza que deseja marcar este pagamento como pago?')) {
                    window.location.href = "{{ url('/payments/' . $payment->id . '/mark-as-paid') }}";
                }
            }

            function confirmRemove() {
                if (confirm('Tem certeza que deseja remover este link de pagamento?')) {
                    window.location.href = "{{ url('/payments/' . $payment->id . '/delete') }}";
                }
            }

            function toggleStatus() {
                // if (confirm('Tem certeza que deseja alterar o status deste link de pagamento?')) {
                window.location.href = "{{ url('/payments/' . $payment->id . '/toggle-active') }}";
                // }
            }

            function viewReceipt() {
                window.open("{{ url('/pay/' . $payment->id . '/receipt') }}", '_blank');
            }

            // Handle form update submit
            $('#sendUpdateLinkForm').on('submit', function(e) {
                // Pega valor original formatado
                const masked = $('#valueInput').val();
                // Pega o valor desmascarado como array de números
                const unmasked = $('#valueInput').maskMoney('unmasked')[0];
                // Substitui no input antes de enviar
                $('#valueInput').val(unmasked);

            })

            @if ($hasPayment)
                function voidTransaction(element) {
                    let paymentId = element.dataset.paymentid;
                    let messageBox = document.querySelector("#messageBox");
                    messageBox.innerHTML = '';
                    messageBox.classList.add('hidden');

                    const requestOptions = {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: []
                    };

                    fetch(baseURL + '/payments/' + paymentId + '/void', requestOptions)
                        .then((json) => {
                            return json.json();
                        })
                        .then((data) => {
                            if (data.status) {
                                location.reload();
                            } else {
                                messageBox.innerHTML = data.message;
                                messageBox.classList.remove('hidden');
                            }
                        })
                        .catch((err) => console.log(err))
                        .finally(() => {
                            element.removeAttribute('data-clicked')
                        })
                }

                document.querySelector('#btnCancelPurchase').addEventListener('click', function(evt) {
                    evt.preventDefault();

                    if (evt.target.dataset['clicked'] == '') {
                        return;
                    }
                    if (confirm('Tem certeza que deseja fazer o estorno desse pagamento?')) {
                        evt.target.setAttribute("data-clicked", '')

                        voidTransaction(evt.target)
                    }

                });
            @endif
        </script>
        <script src="{{ url('/cielo/installment-calculator-dynamic.js') }}"></script>
    @endsection
</x-app-layout>
