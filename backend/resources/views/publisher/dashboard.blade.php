@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Publisher Dashboard
            </h2>
            <p class="text-sm text-gray-500 mt-1">Welcome back, {{ $user->name }}!</p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <form action="{{ url('/publisher/logout') }}" method="POST">
                @csrf
                <x-button variant="secondary">Sign Out</x-button>
            </form>
        </div>
    </div>

    <!-- API Token Alert (Only shown on registration) -->
    @if(session('api_token'))
        <div class="mb-8 rounded-md bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Registration Successful!</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>Save this API token securely. It will not be shown again:</p>
                        <code class="block mt-2 bg-green-100 p-2 rounded">{{ session('api_token') }}</code>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-2 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg border">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Clicks (Across all integrations)</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $metrics->total_clicks }}</dd>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg border">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Aggregate CTR</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $metrics->ctr }}</dd>
            </div>
        </div>
    </div>

    <!-- Analytics Chart -->
    <div class="bg-white shadow sm:rounded-lg border mb-8">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">Click History (Last 30 Days)</h3>
            <div class="h-64">
                <canvas id="clicksChart"></canvas>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('clicksChart').getContext('2d');
            const rawMetrics = @json($metrics);
            let metricsData = {};
            if(rawMetrics.total_clicks !== undefined) {
                metricsData = rawMetrics;
            } else if (typeof rawMetrics === 'string') {
                metricsData = JSON.parse(rawMetrics); // Sometimes comes as string depending on getData()
            } else {
                metricsData = rawMetrics;
            }
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: metricsData.chart_labels || [],
                    datasets: [{
                        label: 'Total Clicks',
                        data: metricsData.chart_data || [],
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        });
    </script>

    <!-- Manual Affiliate Link Generator -->
    <div class="bg-white shadow sm:rounded-lg border mb-8" x-data="{ 
            rawUrl: '', 
            generatedUrl: '', 
            tag: '{{ $integrations->first()->affiliate_tag ?? '' }}',
            generate() {
                if(!this.rawUrl || !this.tag) return;
                try {
                    let url = new URL(this.rawUrl);
                    url.searchParams.set('tag', this.tag);
                    this.generatedUrl = url.toString();
                } catch(e) {
                    alert('Invalid URL');
                }
            },
            copy() {
                navigator.clipboard.writeText(this.generatedUrl);
                alert('Copied to clipboard!');
            }
        }">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900">Affiliate Link Generator</h3>
            <div class="mt-2 max-w-xl text-sm text-gray-500">
                <p>Paste any Amazon product URL to instantly generate your tracked affiliate link.</p>
            </div>
            <div class="mt-5 flex gap-3">
                <input type="text" x-model="rawUrl" placeholder="https://amazon.in/dp/B08N5W4NNB..." class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                <button @click="generate" type="button" class="inline-flex items-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                    Generate
                </button>
            </div>
            <div x-show="generatedUrl" class="mt-4 p-4 bg-gray-50 rounded border flex items-center justify-between" style="display: none;">
                <code class="text-sm text-gray-800 break-all" x-text="generatedUrl"></code>
                <button @click="copy" class="ml-4 text-primary hover:text-blue-500 text-sm font-semibold">Copy</button>
            </div>
        </div>
    </div>

    <!-- Integrations -->
    <div class="bg-white shadow sm:rounded-lg border">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900">Your Integrations</h3>
            <div class="mt-2 max-w-xl text-sm text-gray-500">
                <p>Manage your active Telegram/WhatsApp bot tokens and affiliate IDs.</p>
            </div>
            <div class="mt-5 border-t border-gray-200 pt-5">
                @if($integrations->isEmpty())
                    <p class="text-sm text-gray-500 italic">No integrations configured yet.</p>
                @else
                    <ul role="list" class="divide-y divide-gray-200">
                        @foreach($integrations as $integration)
                            <li class="py-4 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ ucfirst($integration->platform) }}</p>
                                    <p class="text-sm text-gray-500">Tag: {{ $integration->affiliate_tag }}</p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Active</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
