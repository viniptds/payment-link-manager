<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight content-center flex flex-wrap">
                {{ __('Settings') }}
            </h2>
            <a class="btn btn-blue text-lg font-bold confirm-restore-option" href="{{route('settings.restore-from-factory')}}">Restaurar configuração de fábrica</a>
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
                <table id="links_table" class="w-full table-auto">
                    <thead class="py-5">
                        <th class="">Nome</th>
                        <th class="">Valor</th>
                        <th class="">Tipo</th>
                        <th class="">Novo Valor</th>
                        <th class="">Última alteração</th>
                        <th>Ações</th>
                    </thead>
                    <tbody>
                        @foreach ($settings as $setting)
                        <tr class="p-5 mb-4">
                            <td title="{{$setting->description}}">{{ $setting->id }}</td>
                            <td id="label_{{$setting->id}}">{{ $setting->value }} </td>
                            <td>{{ $setting->type ?? 'Any' }} </td>
                            <td> <input type="text" class="" id="value_{{$setting->id}}" value="{{$setting->value}}"> </td>
                            
                            <td>{{ date('d/m/Y H:i:s', strtotime($setting->updated_at)) }} - {{$setting->user->email}}</td>

                            <td>
                                <button type='button' class="btn btn-info btn-save-setting" data-id='{{$setting->id}}'>Salvar</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    document.querySelector('.confirm-restore-option').addEventListener('click', function(e) {
        if (!confirm('Deseja realmente executar essa operação?')) {
            e.preventDefault();
        }
    });

    document.querySelectorAll('.btn-save-setting').forEach((item) => {
        item.addEventListener('click', function(e) {
            let target = e.target.dataset.id;

            if (!target) {
                return;
            }

            let value = document.querySelector('#value_' + target).value;
            let data = {
                id: target,
                value: value,
            };

            fetch(`/settings/${target}`, {
                method: "POST",
                body: JSON.stringify(data),
                headers: {
                    "Content-Type": "application/json",
                },
            })
            .then(json => {
                return json.json();
            })
            .then(data => {
                console.log(data)
                if (data.status) {
                    document.querySelector('#label_' + data.id).innerHTML = data.value; 
                    alert('Configuração salva com sucesso!');
                }
            })
        })
    })
</script>