@extends('layouts.app')
@section('title', 'Shopper Register')
@section('content')
<div class="max-w-md mx-auto mt-10 bg-white p-8 rounded-xl shadow-sm border">
    <h2 class="text-2xl font-bold mb-6 text-center">Shopper Register</h2>
    @if($errors->any())
        <div class="bg-red-50 text-red-500 p-3 rounded mb-4">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('shopper.register') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" name="password_confirmation" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
        </div>
        <button type="submit" class="w-full bg-primary text-white py-2 rounded-md font-medium hover:bg-red-700">Register</button>
    </form>
    <div class="mt-4 text-center text-sm text-gray-600">
        Already have an account? <a href="{{ route('shopper.login') }}" class="text-primary hover:underline">Login</a>
    </div>
</div>
@endsection
