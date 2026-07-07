@extends('layouts.app')

@section('content')
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
  <div class="sm:mx-auto sm:w-full sm:max-w-sm">
    <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Sign in to Publisher Portal</h2>
  </div>

  <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
    <form class="space-y-6" action="{{ url('/publisher/login') }}" method="POST">
        @csrf
      <div>
        <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
        <div class="mt-2">
          <input id="email" name="email" type="email" autocomplete="email" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 px-3">
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between">
          <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
        </div>
        <div class="mt-2">
          <input id="password" name="password" type="password" autocomplete="current-password" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 px-3">
        </div>
      </div>
      
      @if($errors->any())
        <div class="text-sm text-red-600">
            {{ $errors->first() }}
        </div>
      @endif

      <div>
        <x-button variant="primary" class="w-full">Sign in</x-button>
      </div>
    </form>
    
    <p class="mt-10 text-center text-sm text-gray-500">
      Not a publisher?
      <a href="{{ url('/publisher/register') }}" class="font-semibold leading-6 text-primary hover:text-red-500">Register here</a>
    </p>
  </div>
</div>
@endsection
