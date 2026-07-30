@extends('layouts.admin')

@section('header', 'System & Marketing Settings')

@section('content')
<div class="space-y-8">
    @livewire('admin.marketing.settings-manager')
</div>
@endsection
