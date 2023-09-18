<?php
$transaction = json_decode($payment->transaction_log, 0);
?>

<x-guest-layout>
    @if(session('receiptMessage'))
    <h1 class="text-lg mb-2">{{session('receiptMessage')}}</h1>
    @endif

    <p class="mb-2">Transação <b>{{$payment->id}}</b></p>
    <p class="mb-5">Valor: R$ {{str_replace('.', ',', sprintf("%.2f", $payment->value))}}</p>
    
    <h1 class="text-lg mb-2">Detalhes da transação: </h1>

    <p>Parcelas: {{$transaction->installments . ' x R$ ' . str_replace('.', ',', sprintf("%.2f", round($payment->value / $transaction->installments, 2))) }}</p>
    <p>Cartão: {{substr($transaction->creditCard->cardNumber,strpos($transaction->creditCard->cardNumber, '*'))}}</p>
    <p>Recebido em: {{date('d/m/Y H:i:s', strtotime($transaction->capturedDate))}}
    <p>Pagamento {{$transaction->paymentId}}
</x-guest-layout>