@extends('layouts.admin')

@section('header', 'Email Campaigns')

@section('content')
<div class="space-y-8">
    @livewire('admin.marketing.campaign-table')
    
    <!-- Wizard Prototype -->
    <div class="mt-12">
        @livewire('admin.marketing.campaign-wizard')
    </div>
</div>
@endsection
