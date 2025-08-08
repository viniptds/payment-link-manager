<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="pb-5">
                <h1 class="text-lg font-bold ">{{ __('Welcome') }}</h1>
            </div>
            Acesse a aba de Links para mais informações
        </div>
    </div> -->

    <div class="py-5">
        <div
            class="max-w-12lg mx-auto px-2 lg:px-8 sm:px-6 gap-4 grid sm:grid-cols-2 lg:grid-cols-6 grid-cols-1 lg:px-3 flex flex-wrap">
            <div class="bg-white flex-1  shadow-sm sm:rounded-lg p-5">
                <p class="font-bold">Total de Links</p>
                <p>{{ $data['numbers']['payments'] }}</p>
            </div>
            <div class="bg-white flex-1  shadow-sm sm:rounded-lg p-5">
                <p class="font-bold">Ativos</p>
                <p>{{ $data['numbers']['active'] }}</p>
            </div>
            <div class="bg-white flex-1  shadow-sm sm:rounded-lg p-5">
                <p class="font-bold">Inativos</p>
                <p>{{ $data['numbers']['inactive'] }}</p>
            </div>
            <div class="bg-white flex-1  shadow-sm sm:rounded-lg p-5">
                <p class="font-bold">Pagos</p>
                <p>{{ $data['numbers']['paid'] }}</p>
            </div>
            <div class="bg-white flex-1  shadow-sm sm:rounded-lg p-5">
                <p class="font-bold">Estornados</p>
                <p>{{ $data['numbers']['canceled'] }}</p>
            </div>
            <div class="bg-white flex-1  shadow-sm sm:rounded-lg p-5">
                <p class="font-bold">Expirados</p>
                <p>{{ $data['numbers']['expired'] }}</p>
            </div>
        </div>
    </div>

    @if (Auth::user()->is_admin)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <div class="py-5 px-2 sm:px-6 gap-4
        flex grid lg:grid-cols-3">
            <div class=" bg-white shadow-sm sm:rounded-lg p-5 w-auto">
                <p class="font-bold">Total de Pagamentos</p>
                <p>R$ {{ $data['payments'] }}</p>

                <canvas id="payment-chart"></canvas>
            </div>
        </div>
        <script type="text/javascript">
            const ctx = document.getElementById('payment-chart');
            const labels = @json($data['labels']);
            const values = @json($data['values']);
            console.log(labels, values);
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pagamentos',
                        data: Object.values(values),
                        borderWidth: 1,
                        backgroundColor: [
                            'rgb(75, 192, 192)',
                            'rgb(255, 99, 132)',
                            'rgb(255, 206, 86)',
                            'rgb(54, 162, 235)',
                            'rgb(201, 203, 207)'
                        ]
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    @endif
</x-app-layout>
