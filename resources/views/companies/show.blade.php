<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ url('/companies') }}">{{ __('Companies') }} </a> / {{ $company->name }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col pb-5">
                <h1 class="text-lg font-bold pt-7">Informações da Empresa</h1>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                <p class='mb-4'>Empresa: {{ $company->id }}</p>

                <p>Nome: {{ $company->name }}</p>
                <p class=''>Título do Site: {{ $company->site_title ?? '-' }}</p>
                <p class=''>Endereço:
                    @if ($company->url())
                        <a href="{{ $company->url() }}" target="_blank">{{ $company->url() }}</a>
                    @else
                        -
                    @endif
                </p>
                <p class=''>Status: {{ $company->status ? __('Active') : __('Inactive') }}</p>
                <p class=''>Usuário Principal: {{ $company->mainUser->name ?? '-' }}</p>
                <p class=''>Criado Em: {{ date('d/m/Y H:i:s', strtotime($company->created_at)) }}</p>

                @if ($company->logo_url)
                    <img src="{{ $company->logo() }}" alt="{{ $company->title() }}" class="h-16 mt-4">
                @endif

                <div class="flex">
                    <div class="my-5 mr-3">
                        <a class="btn btn-red confirm-delete-company"
                            href="{{ url('/companies/' . $company->id . '/delete') }}">Remover Empresa</a>
                    </div>
                </div>
            </div>

            <div class="flex flex-col pb-5">
                <h1 class="text-lg font-bold pt-10">Editar Empresa</h1>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 gap-4">
                @if (session('editMessage'))
                    <div class="pb-5">
                        {{ session('editMessage') }}
                    </div>
                @endif
                <form method="POST" action="{{ url('/companies/' . $company->id) }}" id="sendUpdateCompanyForm">
                    @csrf
                    @method('PATCH')

                    <label for="nameInput">Nome</label>
                    <input class='form-control' id='nameInput' name='name' type="text" maxlength='255'
                        value="{{ old('name', $company->name) }}">

                    <label for="slugInput">Slug (subdomínio)</label>
                    <input class='form-control' id='slugInput' name='slug' type="text" maxlength='63'
                        placeholder="minhaempresa" value="{{ old('slug', $company->slug) }}">

                    <label for="siteTitleInput">Título do Site</label>
                    <input class='form-control' id='siteTitleInput' name='site_title' type="text" maxlength='255'
                        value="{{ old('site_title', $company->site_title) }}">

                    <label for="logoUrlInput">URL do Logo</label>
                    <input class='form-control' id='logoUrlInput' name='logo_url' type="text" maxlength='255'
                        placeholder="https://..." value="{{ old('logo_url', $company->logo_url) }}">

                    <label for="statusInput">Status</label>
                    <select class="form-control" name='status' id='statusInput'>
                        <option value="1" {{ old('status', $company->status ? '1' : '0') == '1' ? 'selected' : '' }}>
                            {{ __('Active') }}</option>
                        <option value="0" {{ old('status', $company->status ? '1' : '0') == '0' ? 'selected' : '' }}>
                            {{ __('Inactive') }}</option>
                    </select>

                    <label for="mainUserInput">Usuário Principal</label>
                    <select class="form-control" name='main_user' id='mainUserInput'>
                        <option value="">Selecione</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}"
                                {{ old('main_user', $company->main_user) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>

                    <div>
                        <ul>
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-blue mt-4" id="submitUpdateCompany">Salvar</button>
                </form>
            </div>
        </div>
    </div>

    @section('js')
        <script>
            document.querySelector('.confirm-delete-company').addEventListener('click', function(e) {
                if (!confirm('Tem certeza que deseja remover esta empresa?')) {
                    e.preventDefault();
                }
            });
        </script>
    @endsection
</x-app-layout>
