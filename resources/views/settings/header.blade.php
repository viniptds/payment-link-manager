<x-slot name="header">
    <div class="flex justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight content-center flex flex-wrap">
            {{-- TODO: prepare tab setup for multiple settings --}}
            <a href="{{url('settings')}}" class="mx-2">{{ __('Settings') }}</a>
            | 
            <a href="{{route('settings.gateways')}}" class="mx-2">{{ __('Gateways') }}</a>
        </h2>
        @if (Request::is('settings'))
        <a class="btn btn-blue text-lg font-bold confirm-restore-option" href="{{route('settings.restore-from-factory')}}">Restaurar configuração de fábrica</a>
        @endif
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