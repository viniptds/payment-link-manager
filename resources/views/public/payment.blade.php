<?php
$customer = $payment->customer;
$page = $_GET['page'] ?? 'home';
?>
<x-guest-layout>
  @if($payment->status == 'active')
    <div class="py-5 mb-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-center pb-5">
                <h1 class="text-bold text-2xl">Pagamento por Cartão de Crédito</h1>
            </div>
            <div class="tab-header">
                <ul
                class="mb-5 flex list-none flex-row flex-wrap border-b-0 pl-0"
                role="tablist"
                data-te-nav-ref>
                <li role="presentation" class="flex-grow basis-0 text-center">
                    <a
                    class="cursor-pointer my-2 block border-x-0 border-b-2 border-t-0 border-transparent px-7 pb-3.5 pt-4 text-xs font-medium uppercase leading-tight text-neutral-500 hover:isolate hover:border-transparent hover:bg-neutral-100 focus:isolate focus:border-transparent data-[te-nav-active]:border-primary data-[te-nav-active]:text-primary dark:text-neutral-400 dark:hover:bg-transparent dark:data-[te-nav-active]:border-primary-400 dark:data-[te-nav-active]:text-primary-400 {{$page == 'home' ? 'shadow-[0_4px_9px_-4px_#3b71ca]' : ''}}"
                    onclick="setTab('home')">
                    Resumo de Checkout
                    </a>
                </li>
                <li role="presentation" class="flex-grow basis-0 text-center">
                    <a
                    class="cursor-pointer my-2 block border-x-0 border-b-2 border-t-0 border-transparent px-7 pb-3.5 pt-4 text-xs font-medium uppercase leading-tight text-neutral-500 hover:isolate hover:border-transparent hover:bg-neutral-100 focus:isolate focus:border-transparent data-[te-nav-active]:border-primary data-[te-nav-active]:text-primary dark:text-neutral-400 dark:hover:bg-transparent dark:data-[te-nav-active]:border-primary-400 dark:data-[te-nav-active]:text-primary-400 {{$page == 'personal' ? 'shadow-[0_4px_9px_-4px_#3b71ca]' : ''}}" 
                    onclick="setTab('personal')"
                    >Dados Pessoais</a
                    >
                </li>
                <li role="presentation" class="flex-grow basis-0 text-center">
                    <a
                    class="cursor-pointer my-2 block border-x-0 border-b-2 border-t-0 border-transparent px-7 pb-3.5 pt-4 text-xs font-medium uppercase leading-tight text-neutral-500 hover:isolate hover:border-transparent hover:bg-neutral-100 focus:isolate focus:border-transparent data-[te-nav-active]:border-primary data-[te-nav-active]:text-primary dark:text-neutral-400 dark:hover:bg-transparent dark:data-[te-nav-active]:border-primary-400 dark:data-[te-nav-active]:text-primary-400 {{$page == 'card' ? 'shadow-[0_4px_9px_-4px_#3b71ca]' : ''}}"
                    onclick="setTab('card')"
                    >Dados do Cartão</a
                    >
                </li>
                </ul>
                @if($errors->all() ?? false)
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
                class="tab-panel hidden opacity-0 transition-opacity duration-150 ease-linear data-[te-tab-active]:block"
                id="tab-home"
                role="tabpanel"
                aria-labelledby="tab-home-tab">
                    <h1 class="text-bold text-xl">Resumo do Checkout</h1>
                    <div class="my-4">
                        <label class="label-control">Recebedor</label> 
                        <input class="form-control" readonly value="{{env('APP_NAME')}}">
                    </div>

                    <div class="my-4">
                        <label class="label-control">Item</label> 
                        <input class="form-control" readonly value="{{$payment->description}}">
                    </div>

                    <div class="my-4">
                        <label class="label-control">Valor</label> 
                        <input class="form-control" readonly value='R$ {{str_replace(".", ",", sprintf("%.2f", $payment->value))}}'>
                    </div>
                    <div class="flex bg-white sm:rounded-lg justify-end ">
                        <a class="btn btn-blue" id="nextPage" onclick="setTab('personal')">Avançar</a>
                    </div>
                </div>

                <div
                class="tab-panel hidden opacity-0 transition-opacity duration-150 ease-linear data-[te-tab-active]:block"
                id="tab-personal"
                role="tabpanel"
                aria-labelledby="tab-personal">
                <h1 class="text-bold text-xl">Dados Pessoais</h1>
                <form action="{{ $payment->id . '/personal'}}" method="post">
                    <input type="hidden" name="customer_id" id="personal-paymentId" value="{{$customer->id ?? ''}}">
                    <div class="my-4">
                        <label for="payment-name" class="label-control">
                            Nome completo</label>
                        <input type="text" name="name" id="payment-name" placeholder="João da Silva" class="form-control" value="{{$customer->name ?? ''}}">
                        <!-- peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[1.6] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 data-[te-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-neutral-200 dark:placeholder:text-neutral-200 [&:not([data-te-input-placeholder-active])]:placeholder:opacity-0" -->
                    </div>
                    <div class="my-4">
                        <label class="label-control">Email</label>
                        <input class="form-control" type="email" name="email" id="payment-email" placeholder="exemplo@exemplo.com" value="{{old('email') ?? $customer->email ?? ''}}">
                    </div>
                    <div class="my-4">
                        <label class="label-control">CPF</label>
                        <input class="form-control" type="text" name="cpf" id="payment-document" placeholder="000.000.000-00" value="{{old('cpf') ?? $customer->cpf ?? ''}}">
                    </div>
                    <div class="my-4">
                        <label class="label-control">Nº OAB</label>
                        <input class="form-control" type="text" name="document" id="payment-document" placeholder="00000" maxlength=7 value="{{old('document') ?? $customer->document ?? ''}}">
                    </div>
                    <!-- <div class="my-4">
                        <label>Documento</label>
                        <input type="text" name="document" id="payment-document" placeholder="OA">
                    </div> -->
                    
                    <div class="flex bg-white sm:rounded-lg justify-end ">
                        <button type="submit" class="btn btn-blue">Salvar</button>
                    </div>
                </form>
                </div>

                <div
                class="tab-panel hidden opacity-0 transition-opacity duration-150 ease-linear data-[te-tab-active]:block"
                id="tab-card"
                role="tabpanel"
                aria-labelledby="tab-card">
                <h1 class="text-bold text-xl">Dados do Cartão</h1>
                @if (session('cardMessage'))
                <div class="p-4">
                    {{session('cardMessage')}}
                </div> 
                @endif
                <form action="{{ $payment->id . '/checkout'}}" method="post">
                    <input type="hidden" name="customer_id" id="cardInfo-customerId" value="{{old('customer_id') ?? $customer->id ?? ''}}">
                    <div class="my-4">
                        <label class="label-control">Número do Cartão</label>
                        <input class="form-control" type="number" name="card_number" id="cardInfo-cardNumber" placeholder="0000 0000 0000 0000 0000" value="{{old('card_number')}}">
                    </div>

                    <div class="my-4">
                        <label class="label-control">CVV</label>
                        <input class="form-control" type="number" maxlength=3 name="card_cvv" id="cardInfo-cvv" placeholder="000">
                    </div>
                    <div class="my-4">
                        <label class="label-control" for="card_brand" class="">Bandeira</label>
                        <select class="form-control" name="card_brand" id="cardInfo-card_brand" placeholder="Selecione a bandeira">
                            <option></option>
                        @foreach($card_brands as $brand)
                        <option value="{{$brand}}" {{old('card_brand') == $brand ? 'selected' : ''}}>
                            {{$brand}}
                        </option>
                        @endforeach
                        </select>
                    </div>
                    <div class="my-4">
                        <label class="label-control"  for="installments">Parcelas</label>
                        <select class="form-control" name="payment_installments" id="cardInfo-installments">
                        @for($i = 1; $i <= env('CIELO_MAX_INSTALLMENTS', 12) && ($i == 1 || (floor($payment->value / $i) >= floatval(env('CIELO_MIN_INSTALLMENT_VALUE')))); $i++)
                        <option value="{{$i}}" {{old('payment_installments') == $i ? 'selected' : ''}}>
                            {{$i . ' x R$ ' . str_replace('.', ',', sprintf("%.2f", round($payment->value / $i, 2, PHP_ROUND_HALF_DOWN))) }}
                        </option>
                        @endfor
                        </select>
                    </div>
                    
                    <div class="my-4">
                        <label class="label-control" >Validade</label>
                        <input class="form-control" type="month" maxlength=3 name="card_expiration_date" id="cardInfo-expiration_date" value="{{old('card_expiration_date')}}">
                    </div>

                    <div class="mt-4 mb-10">
                        <label class="label-control">Nome impresso no Cartão</label>

                        <input class="form-control" type="text" name="card_holder" id="cardInfo-name" placeholder="João da Silva" value="{{old('card_holder')}}">
                    </div>
                    
                    <div class="flex bg-white sm:rounded-lg justify-end ">
                        <button class="btn btn-success" type="submit">Pagar</button>
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
        let page = urlParams.get('page');

        if (!page) {
            page = 'home';
        }
        setTab(page);
    }

    function setTab(tabId) {
        let tabs = document.querySelectorAll('.tab-panel');
        let selectedTab = document.querySelector('#tab-' + tabId);

        if(selectedTab) {
            tabs.forEach((tab) => {
                tab.removeAttribute('data-te-tab-active');
                // tab.classList.add('hidden');
                tab.classList.remove('opacity-100');
                tab.classList.add('opacity-0');
            })
    
            selectedTab.setAttribute('data-te-tab-active', '');
            // selectedTab.classList.remove('hidden');
            selectedTab.classList.remove('opacity-0');
            selectedTab.classList.add('opacity-100');

            var searchParams = new URLSearchParams(window.location.search);
            let page = searchParams.get('page');

            if (page != tabId) {
                searchParams.set("page", tabId);
                window.location.search = searchParams.toString();
            }
        }

    }

    document.addEventListener("DOMContentLoaded", function(e) {
        redirectToPage();
    });
</script>
@endsection

</x-guest-layout>