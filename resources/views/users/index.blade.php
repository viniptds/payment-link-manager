<?php

$pages = 1;
$currentPage = 1;
$hasMorePages = false;
?>
<x-app-layout>
    <x-slot name="header">
      <div class="flex justify-between vertical-align-center">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ __('Users') }}
        </h2>
        <button class="btn btn-blue text-lg font-bold "
          data-te-toggle="modal"
          data-te-target="#createUserModal"
          data-te-ripple-init
          data-te-ripple-color="light">Novo Operador
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
                <table id="links_table" class="w-full">
                    <thead class="py-5">
                        <th class="">ID</th>
                        <th class="">Nome</th>
                        <th class="">Email</th>
                        <th class="">Permissão</th>
                        <th class="">Criado Em</th>
                        <th>Ações</th>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                        <tr class="p-5 m-10"> 
                            
                            <td>{{ $user->id }}
                            <td>{{ $user->name }}
                            <td>{{ $user->email }}
                            <td>{{ $user->is_admin ? __('Admin') : __('User') }}
                            <td>{{ date('d/m/Y H:i:s', strtotime($user->created_at)) }}</td>
                            @if ($user->id != Auth::user()->id )
                            <td><a href="{{url('/users/' . $user->id . '/toggle-admin' )}}">{{$user->is_admin ? 'Remover' : 'Conceder' }} Permissões</a></td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <nav aria-label="" class='flex justify-center mt-5'>
                <ul class="list-style-none flex">
                  @if($currentPage > $pages)
                  <li>
                    <a
                      class="relative block rounded bg-transparent px-3 py-1.5 text-sm text-neutral-600 transition-all duration-300 hover:bg-neutral-100 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                      href="{{url('payments?page=' . ($i - 1))}}"
                      >{{__('Previous')}}</a
                    >
                  </li>
                 
                  @endif
                  @for ($i = 1; $i <= $pages; $i++)
                  <li aria-current="page">
                    <a
                      class="relative block rounded bg-transparent px-3 py-1.5 text-sm text-neutral-600 transition-all duration-300 hover:bg-neutral-100 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                      href="{{url('payments?page=' . $i)}}"
                      >{{$i}}</a
                    >
                  </li>
                  @endfor
                  @if($currentPage < $pages)
                  <li>
                    <a
                      class="relative block rounded bg-transparent px-3 py-1.5 text-sm text-neutral-600 transition-all duration-300 hover:bg-neutral-100 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                      href="#"
                      >{{__('Next')}}</a
                    >
                  </li>
                  @endif
                </ul>
              </nav>
            </div>
        </div>
    </div>

    <div
  data-te-modal-init
  class="fixed left-0 top-20 z-[1055] hidden h-full w-full overflow-y-auto overflow-x-hidden outline-none"
  id="createUserModal"
  tabindex="-1"
  aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div
    data-te-modal-dialog-ref
    class="pointer-events-none relative w-auto translate-y-[-50px] opacity-0 transition-all duration-300 ease-in-out min-[576px]:mx-auto min-[576px]:mt-7 min-[576px]:max-w-[500px]">
    <div
      class="min-[576px]:shadow-[0_0.5rem_1rem_rgba(#000, 0.15)] pointer-events-auto relative flex w-full flex-col rounded-md border-none bg-white bg-clip-padding text-current shadow-lg outline-none dark:bg-neutral-600">
      <div
        class="flex flex-shrink-0 items-center justify-between rounded-t-md border-b-2 border-neutral-100 border-opacity-100 p-6 dark:border-opacity-50 bg-blue-400">
        <!--Modal title-->
        <h5
          class="text-xl font-medium leading-normal text-neutral-800 dark:text-neutral-200"
          id="exampleModalLabel">
          Novo Operador
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
      <form method="POST" action="users">
        <!--Modal body-->
        <div class="relative flex-auto p-4" data-te-modal-body-ref>
        
            @csrf

            <label for="nameInput" >Nome</label>
            <input class='form-control' id='nameInput' name='name' type="text">
            
            <label for="emailInput">Email</label>
            <input name='email' id='emailInput' class='form-control' type="email" maxlength='100'>
            
            <label for="passwordInput">Senha</label>
            <input name='password' id='passwordInput' class='form-control' type="password" maxlength='100'>

            <label for="password_confirmationInput">Confirmar Senha</label>
            <input name='password_confirmation' id='password_confirmationInput' class='form-control' type="password" maxlength='100'>
            
            <label>Permissões de Administrador</label>
            <select name='is_admin' id='is_adminInput' class='form-control' type="datetime-local">
              <option value="0">Não</option>
              <option value="1">Sim</option>
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
              id='submitCreateLink' >
            Criar Link
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

</x-app-layout>
