<?php
$customer = $payment->customer;
?>
<x-guest-layout>
  @if($payment->status == 'active')
    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-items-center pb-5">
                <h1 class="text-lg font-bold ">Pagamento por Cartão de Crédito</h1>
            </div>
            <div class="tab-header">

                <ul
                class="mb-5 flex list-none flex-row flex-wrap border-b-0 pl-0"
                role="tablist"
                data-te-nav-ref>
                <li role="presentation" class="flex-grow basis-0 text-center">
                    <a
                    class="cursor-pointer my-2 block border-x-0 border-b-2 border-t-0 border-transparent px-7 pb-3.5 pt-4 text-xs font-medium uppercase leading-tight text-neutral-500 hover:isolate hover:border-transparent hover:bg-neutral-100 focus:isolate focus:border-transparent data-[te-nav-active]:border-primary data-[te-nav-active]:text-primary dark:text-neutral-400 dark:hover:bg-transparent dark:data-[te-nav-active]:border-primary-400 dark:data-[te-nav-active]:text-primary-400"
                    onclick="setTab('tab-home')"
                    >Resumo de Checkout</a
                    >
                </li>
                <li role="presentation" class="flex-grow basis-0 text-center">
                    <a
                    class="cursor-pointer my-2 block border-x-0 border-b-2 border-t-0 border-transparent px-7 pb-3.5 pt-4 text-xs font-medium uppercase leading-tight text-neutral-500 hover:isolate hover:border-transparent hover:bg-neutral-100 focus:isolate focus:border-transparent data-[te-nav-active]:border-primary data-[te-nav-active]:text-primary dark:text-neutral-400 dark:hover:bg-transparent dark:data-[te-nav-active]:border-primary-400 dark:data-[te-nav-active]:text-primary-400"
                    onclick="setTab('tab-personal')"
                    >Dados Pessoais</a
                    >
                </li>
                <li role="presentation" class="flex-grow basis-0 text-center">
                    <a
                    class="cursor-pointer my-2 block border-x-0 border-b-2 border-t-0 border-transparent px-7 pb-3.5 pt-4 text-xs font-medium uppercase leading-tight text-neutral-500 hover:isolate hover:border-transparent hover:bg-neutral-100 focus:isolate focus:border-transparent data-[te-nav-active]:border-primary data-[te-nav-active]:text-primary dark:text-neutral-400 dark:hover:bg-transparent dark:data-[te-nav-active]:border-primary-400 dark:data-[te-nav-active]:text-primary-400"
                    onclick="setTab('tab-card')"
                    >Dados do Cartão</a
                    >
                </li>
                </ul>
                @if($errors->any())
                    <div class="bg-red">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{$error}}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
            </div>
            <div class="tab-body">
                <div
                class="tab-panel hidden opacity-100 transition-opacity duration-150 ease-linear data-[te-tab-active]:block"
                id="tab-home"
                role="tabpanel"
                aria-labelledby="tab-home-tab"
                data-te-tab-active>
                    <div class="my-4">
                    Vendido por: <b>{{env('APP_NAME')}}</b>
                    </div>

                    <div class="my-4">
                        Item: {{$payment->description}}
                    </div>
                    <div class="my-4">
                    Valor: <b>R$ {{$payment->value}}</b>
                    </div>
                    <div class="flex bg-white overflow-hidden sm:rounded-lg justify-end ">
                    <a class="btn btn-blue" id="nextPage" onclick="setTab('tab-personal')">Avançar</a>
                    </div>
                </div>

                <div
                class="tab-panel hidden opacity-0 transition-opacity duration-150 ease-linear data-[te-tab-active]:block"
                id="tab-personal"
                role="tabpanel"
                aria-labelledby="tab-personal">
                <h1 class="">Dados Pessoais</h1>
                <form action="{{ $payment->id . '/personal'}}" method="post">
                    <input type="hidden" name="" id="personal-paymentId" value="{{$payment->id}}">
                    <input type="hidden" name="customer_id" id="personal-paymentId" value="{{$customer->id ?? ''}}">
                    <div class="my-4">
                        <label for="payment-name" class="pointer-events-none left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[1.6] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[te-input-state-active]:-translate-y-[0.9rem] peer-data-[te-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-200 dark:peer-focus:text-primary">
                            Nome completo</label>
                        <input type="text" name="name" id="payment-name" placeholder="João da Silva" class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[1.6] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 data-[te-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-neutral-200 dark:placeholder:text-neutral-200 [&:not([data-te-input-placeholder-active])]:placeholder:opacity-0" value="{{$customer->name ?? ''}}">
                    </div>
                    <div class="my-4">
                        <label>Email</label>
                        <input type="email" name="email" id="payment-email" placeholder="exemplo@exemplo.com" value="{{$customer->email ?? old('email')}}">
                    </div>
                    <div class="my-4">
                        <label>CPF</label>
                        <input type="text" name="cpf" id="payment-document" placeholder="000.000.000-00" value="{{$customer->cpf ?? old('email')}}">
                    </div>
                    <!-- <div class="my-4">
                        <label>Documento</label>
                        <input type="text" name="document" id="payment-document" placeholder="OA">
                    </div> -->
                    
                    <div class="flex bg-white overflow-hidden sm:rounded-lg justify-end ">
                        <button type="submit" class="btn">Enviar Dados</button>
                    </div>
                </form>
                </div>

                <div
                class="tab-panel hidden opacity-0 transition-opacity duration-150 ease-linear data-[te-tab-active]:block"
                id="tab-card"
                role="tabpanel"
                aria-labelledby="tab-card">
                <h1 class="">Dados do Cartão</h1>
                <form action="{{ $payment->id . '/checkout'}}" method="post">
                    <input type="hidden" name="" id="cardInfo-paymentId" value="{{$payment->id}}">
                    <div class="my-4">
                        <label>Nome impresso no Cartão</label>
                        <input type="text"
                            class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[1.6] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[te-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-neutral-200 dark:placeholder:text-neutral-200 dark:peer-focus:text-primary [&:not([data-te-input-placeholder-active])]:placeholder:opacity-0"
                            placeholder="Default input" />
                        <label
                            for="exampleFormControlInpu3"
                            class="pointer-events-none left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[1.6] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[te-input-state-active]:-translate-y-[0.9rem] peer-data-[te-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-200 dark:peer-focus:text-primary"
                            >Default input
                        </label>
                        <input type="text" name="card_holder" id="cardInfo-name" placeholder="João da Silva" value="{{old('card_holder')}}">
                    </div>
                    <div class="my-4">
                        <label>Número no Cartão</label>
                        <input type="number" name="card_number" id="cardInfo-cardNumber" placeholder="0000 0000 0000 0000 0000" value="{{old('card_number')}}">
                    </div>

                    <div class="my-4">
                        <label>CVV</label>
                        <input type="number" maxlength=3 name="card_cvv" id="cardInfo-cvv" placeholder="000">
                    </div>
                    <div class="my-4">
                        <label for="card_brand" class="">Bandeira</label>
                        <select name="card_brand" id="cardInfo-card_brand">
                        @foreach($card_brands as $brand)
                        <option value="{{$brand}}" {{old('card_brand') == $brand ? 'selected' : ''}}>
                            {{$brand}}
                        </option>
                        @endforeach
                        </select>
                    </div>
                    <div class="my-4">
                        <label>Validade</label>
                        <input type="month" maxlength=3 name="card_expiration_date" id="cardInfo-expiration_date" value="{{old('card_expiration_date')}}">
                    </div>
                    <div class="my-4">
                        <label for="installments" class="">Parcelas</label>
                        <select name="payment_installments" id="cardInfo-installments">
                        @for($i = 1; $i <= env('CIELO_MAX_INSTALLMENTS', 12); $i++)
                        <option value="{{$i}}" {{old('payment_installments') == $i ? 'selected' : ''}}>
                            {{$i . ' x R$ ' . round($payment->value / $i, 2) }}
                        </option>
                        @endfor
                        </select>
                    </div>
                    <div class="flex bg-white overflow-hidden sm:rounded-lg justify-end ">
                        <button class="btn" type="submit">Pagar</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>


  @elseif ($payment->status == 'paid')
  <div class="paid">Esse link já foi pago. Obrigado por usar nossos serviços.</div>

  @elseif (in_array($payment->status, ['expired', 'cancelled', 'inactive']))
  <div class="paid">Esse link não é válido. Solicite um novo link.</div>

  @endif



@section('js')
<script>
    function sendPersonalData()
    {

    }


    function sendCardData()
    {

    }

    function redirectToPage()
    {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page');

        if (page) {
            setTab('tab-' + page);
        }
    }

    function setTab(tabId) {
        let tabs = document.querySelectorAll('.tab-panel');
        let tab = document.querySelector('#' + tabId);

        if(tab) {
            tabs.forEach((tab) => {
                tab.removeAttribute('data-te-tab-active');
                // tab.classList.add('hidden');
                tab.classList.remove('opacity-100');
                tab.classList.add('opacity-0');
            })
    
            tab.setAttribute('data-te-tab-active', '');
            // tab.classList.remove('hidden');
            tab.classList.remove('opacity-0');
            tab.classList.add('opacity-100');
        }

    }

    document.addEventListener("DOMContentLoaded", function(e) {
        redirectToPage();
    });
</script>
@endsection

</x-guest-layout>