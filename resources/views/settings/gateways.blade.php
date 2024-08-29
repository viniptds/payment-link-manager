<?php
// Mock data
// $gateway = new \StdClass();

// $gateway->id = 13513513;
// $gateway->name = 'CIELO 3.0 API';
// $gateway->status = 0;

// $gateway->settings = '[{
//     "key": "API_KEY",
//     "value": "",
//     "label": "The API key of CIELO 3.0"
// }]';
?>
<x-app-layout>
    @include('settings.header')

    <div class="py-5">
        <div class="max-w-7lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-y-hidden shadow-sm sm:rounded-lg p-5">
                @foreach ($gateways as $gateway)
                    <form action="" method="post" id="f-update-gateway">
                        <input type="hidden" name="gateway_{{ $gateway->id }}">
                        <p>Gateway: <i>{{ $gateway->name }}</i></p>
                        <p>Status:
                            <input type="radio" class="gateway_status" name="gateway_status_{{ $gateway->id }}"
                                id="gateway_status_on" value="1" required {{ $gateway->status ? 'checked' : '' }}>
                            <label for="gateway_status_on" class="mr-2">{{ __('util.option_active') }}</label>
                            <input type="radio" class="gateway_status" name="gateway_status_{{ $gateway->id }}"
                                id="gateway_status_off" value="0" required
                                {{ !$gateway->status ? 'checked' : '' }}>
                            <label for="gateway_status_off">{{ __('util.option_inactive') }}</label>
                        </p>
                        <p>Transactions using this gateway: 000 transactions</p>

                        <?php
                        // $gatewaySettings = json_decode($gateway->settings ?? '{}', 1);
                        ?>
                        {{-- TODO: add gateway options (API_KEY, BASE_URL, environment, WEBHOOK path) --}}
                        {{-- @foreach ($gatewaySettings as $gwSetting)
                        <label for="gw_{{$gateway->id . '_' . $gwSetting['key']}}">{{$gwSetting['label']}}</label>
                        <input type="text" name="gw_{{$gateway->id . '_' . $gwSetting['key']}}" value="{{$gwSetting['value'] ?? ''}}">
                    @endforeach --}}

                        <a type='button' class="btn btn-blue btn-save-gateway text-lg font-bold"
                            data-id='{{ $gateway->id }}'>{{ __('Save') }}</a>
                @endforeach
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    document.querySelectorAll('.btn-save-gateway').forEach((item) => {
        item.addEventListener('click', function(e) {

            let target = e.target.dataset.id;

            if (!target) {
                return;
            }

            let value = document.querySelector('[name=gateway_status_' + target + ']:checked').value;

            let data = {
                id: target,
                status: value,
            };

            fetch(`/settings/gateways/${target}`, {
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
                        alert('Configuração salva com sucesso!');
                    } else {
                        alert('Erro ao salvar configuração. Por favor tente novamente mais tarde.');
                    }
                })
        })
    })
</script>
