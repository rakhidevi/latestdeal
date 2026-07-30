@extends('admin.layout')

@section('title', 'Email Campaigns')

@section('content')
<div class="space-y-8">
    @livewire('admin.marketing.campaign-table')
</div>
@endsection
