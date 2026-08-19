<x-app-layout>
    <x-slot name="header">
      <div class="flex justify-between vertical-align-center">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight content-center flex flex-wrap">
          {{ __('Companies') }}
        </h2>
        <button class="btn btn-blue text-lg font-bold "
          data-te-toggle="modal"
          data-te-target="#createCompanyModal"
          data-te-ripple-init
          data-te-ripple-color="light">Nova Empresa
        </button>
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                @if (session('message'))
                <div class="pb-5">
                  {{ session('message') }}
                </div>
                @endif
                <table id="links_table" class="w-full">
                    <thead class="py-5">
                        <th class="hidden lg:block">ID</th>
                        <th class="">Nome</th>
                        <th class="">Endereço</th>
                        <th class="">Status</th>
                        <th class="">Usuário Principal</th>
                        <th class="">Criado Em</th>
                        <th>Ações</th>
                    </thead>
                    <tbody>
                        @foreach ($companies as $company)
                        <tr class="p-5 m-10">

                            <td class="hidden lg:block">{{ $company->id }}
                            <td>{{ $company->name }}
                            <td>
                              @if ($company->url())
                              <a href="{{ $company->url() }}" target="_blank">{{ $company->slug }}</a>
                              @else
                              -
                              @endif
                            <td>{{ $company->status ? __('Active') : __('Inactive') }}
                            <td>{{ $company->mainUser->name ?? '-' }}
                            <td>{{ date('d/m/Y H:i:s', strtotime($company->created_at)) }}</td>
                            <td><a href="{{url('/companies/' . $company->id )}}">Ver</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {!! $companies->links('util.paginator')!!}
            </div>
        </div>
    </div>

    <div
  data-te-modal-init
  class="fixed left-0 top-20 z-[1055] hidden h-full w-full overflow-y-auto overflow-x-hidden outline-none"
  id="createCompanyModal"
  tabindex="-1"
  aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div
    data-te-modal-dialog-ref
    class="pointer-events-none relative w-auto translate-y-[-50px] opacity-0 transition-all duration-300 ease-in-out min-[576px]:mx-auto min-[576px]:mt-12 min-[576px]:max-w-[500px] top-20">
    <div
      class="min-[576px]:shadow-[0_0.5rem_1rem_rgba(#000, 0.15)] pointer-events-auto relative flex w-full flex-col rounded-md border-none bg-white bg-clip-padding text-current shadow-lg outline-none dark:bg-neutral-600">
      <div
        class="flex flex-shrink-0 items-center justify-between rounded-t-md border-b-2 border-neutral-100 border-opacity-100 p-6 dark:border-opacity-50 bg-blue-400">
        <!--Modal title-->
        <h5
          class="text-xl font-medium leading-normal text-neutral-800 dark:text-neutral-200"
          id="exampleModalLabel">
          Nova Empresa
        </h5>
        <!--Close button-->
        <button
          type="button"
          class="box-content rounded-none border-none hover:no-underline hover:opacity-75 focus:opacity-100 focus:shadow-none focus:outline-none"
          data-te-modal-dismiss
          aria-label="Close">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="h-6 w-6">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <form method="POST" action="{{ route('companies.store') }}">
        <!--Modal body-->
        <div class="relative flex-auto p-4" data-te-modal-body-ref>

            @csrf

            <label for="nameInput">Nome</label>
            <input class='form-control' id='nameInput' name='name' type="text" maxlength='255' value="{{ old('name') }}">

            <label for="statusInput">Status</label>
            <select name='status' id='statusInput' class='form-control'>
              <option value="1" {{ old('status') == '0' ? '' : 'selected' }}>{{ __('Active') }}</option>
              <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
            </select>

            <label for="slugInput">Slug (subdomínio)</label>
            <input class='form-control' id='slugInput' name='slug' type="text" maxlength='63'
              placeholder="minhaempresa" value="{{ old('slug') }}">

            <label for="siteTitleInput">Título do Site</label>
            <input class='form-control' id='siteTitleInput' name='site_title' type="text" maxlength='255'
              value="{{ old('site_title') }}">

            <label for="logoUrlInput">URL do Logo</label>
            <input class='form-control' id='logoUrlInput' name='logo_url' type="text" maxlength='255'
              placeholder="https://..." value="{{ old('logo_url') }}">

            <label for="mainUserInput">Usuário Principal</label>
            <select name='main_user' id='mainUserInput' class='form-control'>
              <option value="">Selecione</option>
              @foreach ($users as $user)
              <option value="{{ $user->id }}" {{ old('main_user') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
              @endforeach
            </select>
        </div>

        <!--Modal footer-->
        <div
          class="flex flex-shrink-0 flex-wrap items-center justify-end rounded-b-md border-t-2 border-neutral-100 border-opacity-100 p-4 dark:border-opacity-50">
          <button
            type="button"
            class="inline-block rounded bg-primary-100 px-6 pb-2 pt-2.5 text-xs font-medium uppercase leading-normal text-primary-700 transition duration-150 ease-in-out hover:bg-primary-accent-100 focus:bg-primary-accent-100 focus:outline-none focus:ring-0 active:bg-primary-accent-200"
            data-te-modal-dismiss
            data-te-ripple-init
            data-te-ripple-color="light">
            Close
          </button>
          <button
            type="submit"
            class="btn btn-blue ml-1 inline-block rounded bg-primary  font-medium leading-normal "
            data-te-ripple-init
            data-te-ripple-color="light"
              id='submitCreateCompany' >
            Criar Empresa
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

</x-app-layout>
