@extends('admin.layout')

@section('title', 'Marketing Home')

@section('content')
<div class="space-y-8">
    @livewire('admin.marketing.dashboard-cards')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        @livewire('admin.marketing.queue-monitor')
        @livewire('admin.marketing.activity-timeline')
    </div>
</div>
@endsection
