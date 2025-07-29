<x-app-layout>
    @include('settings.header')

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
                            <tr class="p-10 mb-4 border-b border-gray-200 hover:bg-gray-100">
                                <td title="{{ $setting->description }}">{{ $setting->id }}</td>
                                <td id="label_{{ $setting->id }}">
                                    @if ($setting->type == 'file')
                                        <img src="{{ $setting->value }}" class="w-10 h-10">
                                    @else
                                        {{ $setting->value }}
                                    @endif
                                </td>
                                <td>{{ __('settings.type.' . ($setting->type ?? 'any')) }} </td>
                                <td>
                                    @switch ($setting->type)
                                        @case('boolean')
                                            <select class="form-select" id="value_{{ $setting->id }}">
                                                <option value="1" {{ $setting->value ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ !$setting->value ? 'selected' : '' }}>Não</option>
                                            </select>
                                        @break

                                        @case('file')
                                            <input type="file" class="" id="value_{{ $setting->id }}"
                                                value="{{ $setting->value }}">
                                        @break

                                        @default
                                            <input type="text" class="" id="value_{{ $setting->id }}"
                                                value="{{ $setting->value }}">
                                    @endswitch
                                </td>

                                <td>{{ date('d/m/Y H:i:s', strtotime($setting->updated_at)) }} -
                                    {{ $setting->user->email }}</td>

                                <td>
                                    <button type='button' class="btn btn-info btn-save-setting"
                                        data-id='{{ $setting->id }}'>Salvar</a>
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

            let formData = new FormData();

            let fileInput = document.querySelector('#value_' + target);
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                formData.append('file', fileInput.files[0]);
            }

            let value = document.querySelector('#value_' + target).value;
            if (value) {
                formData.append('value', value);
            }

            fetch(`/settings/${target}`, {
                    method: "POST",
                    body: formData,
                    headers: {
                        // "Content-Type": "application/json",
                    },
                })
                .then(json => {
                    return json.json();
                })
                .then(data => {
                    console.log(data)
                    if (data.status) {
                        Message.success('Configuração salva com sucesso!');

                        setTimeout(() => {
                            window.location.reload();

                        }, timeout = 2000);

                        // document.querySelector('#label_' + data.id).innerHTML = data.value;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Message.error('Erro ao salvar configuração: ' + error.message);
                });
        })
    })
</script>
