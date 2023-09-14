<x-guest-layout>
    <p>O pagamento foi realizado com sucesso!</p>

    <p>Transação {{$payment->id}}</p>
    <p>Valor: R$ {{$payment->value}}</p>
    <p>Detalhes da transação: {{$payment->transaction_log}}</p>
</x-guest-layout>