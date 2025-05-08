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

    <div class="max-w-7lg sm:px-6 lg:px-8 pt-4">
        <button class="btn btn-blue text-lg font-bold " data-te-toggle="modal" data-te-target="#createGatewayModal"
            data-te-ripple-init data-te-ripple-color="light">Novo Gateway</button>
    </div>
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

    <div data-te-modal-init
        class="fixed left-0 top-20 z-[99999] hidden h-full w-full overflow-y-auto overflow-x-hidden outline-none"
        id="createGatewayModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div data-te-modal-dialog-ref
            class="pointer-events-none relative w-auto translate-y-[-50px] opacity-0 transition-all duration-300 ease-in-out min-[576px]:mx-auto min-[576px]:mt-12 min-[576px]:max-w-[500px] top-20">
            <div
                class="min-[576px]:shadow-[0_0.5rem_1rem_rgba(#000, 0.15)] pointer-events-auto relative flex w-full flex-col rounded-md border-none bg-white bg-clip-padding text-current shadow-lg outline-none dark:bg-neutral-600">
                <div
                    class="flex flex-shrink-0 items-center justify-between rounded-t-md border-b-2 border-neutral-100 border-opacity-100 p-6 dark:border-opacity-50 bg-blue-400">
                    <!--Modal title-->
                    <h5 class="text-xl font-medium leading-normal text-neutral-800 dark:text-neutral-200"
                        id="exampleModalLabel">
                        Novo Gateway
                    </h5>
                    <!--Close button-->
                    <button type="button"
                        class="box-content rounded-none border-none hover:no-underline hover:opacity-75 focus:opacity-100 focus:shadow-none focus:outline-none"
                        data-te-modal-dismiss aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{route('settings.gateways.store')}}">
                    <!--Modal body-->
                    <div class="relative flex-auto p-4" data-te-modal-body-ref>
                        @csrf
                        <div class="mb-2">
                            <label>Nome *</label>
                            <input class="form-control" name='name' id='descriptionInput' type="text"
                                maxlength='100' required>
                        </div>
                        <div class="mb-2">
                            <label>Descrição *</label>
                            <input class="form-control" name='description' id='descriptionInput' type="text"
                                maxlength='100' required>
                        </div>
                        <div class="mb-2">
                            <label>Foto</label>
                            <input class="form-control" name='photo' id='expireAtInput' type="file">
                        </div>
                        <div class="mb-2">
                            <p>Credenciais</p>
                            <button id="addCredential" type="button" class="btn btn-info">+ Credencial</button>
                            <div id="credential-group" class="mt-4">
                            </div>
                        </div>
                    </div>

                    <!--Modal footer-->
                    <div
                        class="flex flex-shrink-0 flex-wrap items-center justify-end rounded-b-md border-t-2 border-neutral-100 border-opacity-100 p-4 dark:border-opacity-50">
                        <button type="button"
                            class="inline-block rounded bg-primary-100 px-6 pb-2 pt-2.5 text-xs font-medium uppercase leading-normal text-primary-700 transition duration-150 ease-in-out hover:bg-primary-accent-100 focus:bg-primary-accent-100 focus:outline-none focus:ring-0 active:bg-primary-accent-200"
                            data-te-modal-dismiss data-te-ripple-init data-te-ripple-color="light">
                            Close
                        </button>
                        <button type="submit"
                            class="btn btn-blue ml-1 inline-block rounded bg-primary  font-medium leading-normal "
                            data-te-ripple-init data-te-ripple-color="light" id='submitCreateLink'>
                            Criar Gateway
                        </button>
                    </div>
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

    document.querySelector('#addCredential').addEventListener('click', function(e) {
        let credentialGroup = document.querySelector('#credential-group');
        let credentialList = document.querySelectorAll('#credential-group .credential-item');

        let lastElement = 0;

        console.log(credentialList[credentialList.length-1])
        if (credentialList[credentialList.length-1]) {
            lastElement = parseInt(credentialList[credentialList.length-1].dataset.id);
        }
        let input = getCreatedButton(1 + lastElement);

        credentialGroup.appendChild(input);
    })

    function getCreatedButton(id) {
        let inputLabel = document.createElement('input');
        inputLabel.type = 'text';
        inputLabel.name = 'credentials[label][]';
        inputLabel.className = 'form-control ';
        inputLabel.required = true;

        let inputValue = document.createElement('input');
        inputValue.type = 'text';
        inputValue.name = 'credentials[value][]';
        inputValue.className = 'form-control';
        inputValue.required = true;

        let labelLabel = document.createElement('label');
        labelLabel.textContent = 'Credencial';
        labelLabel.className = 'input-group';
        labelLabel.appendChild(inputLabel);

        let labelValue = document.createElement('label');
        labelValue.textContent = 'Valor';
        labelValue.className = 'input-group';
        
        labelValue.appendChild(inputValue);

        let buttonRemove = document.createElement('button');
        buttonRemove.type = 'button';
        buttonRemove.textContent = 'X'
        buttonRemove.className = 'btn btn-info';
        buttonRemove.onclick = () => {
            console.log('it');
            const outerDiv = event.target.closest('.credential-item'); // Finds the closest ancestor with the specified class
            if (outerDiv) {
                outerDiv.remove();
            }
        }

        let divContainer = document.createElement('div');
        divContainer.id = 'credential_' + id;
        divContainer.dataset.id = id;
        divContainer.className = 'flex flex-row justify-between credential-item mb-4';

        divContainer.appendChild(labelLabel);
        divContainer.appendChild(labelValue);
        divContainer.appendChild(buttonRemove);

        return divContainer;
    }
</script>
