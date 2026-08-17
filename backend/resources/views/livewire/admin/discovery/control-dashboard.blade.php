<div>
    <h2>Discovery Control Dashboard</h2>
    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Queue Health (Pressure)</div>
        <div class="card-body d-flex justify-content-between">
            <div><strong>Generated:</strong> {{ number_format($queueStats['generated']) }}</div>
            <div><strong>Queued:</strong> {{ number_format($queueStats['queued']) }}</div>
            <div><strong>Processing:</strong> {{ number_format($queueStats['processing']) }}</div>
            <div><strong>Published:</strong> {{ number_format($queueStats['published']) }}</div>
            <div><strong>Failed:</strong> {{ number_format($queueStats['failed']) }}</div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Crawl Budget Visualization (Amazon)</div>
        <div class="card-body">
            <div>Search: ██████████ 42%</div>
            <div>Lightning: █████ 18%</div>
            <div>Coupons: ████ 14%</div>
            <div>Warehouse: ███ 10%</div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">Brand Coverage (Wave 2)</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Brand</th>
                                <th>Products</th>
                                <th>Published</th>
                                <th>ROI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($brandCoverage as $brand)
                            <tr>
                                <td>{{ $brand['brand'] }}</td>
                                <td>{{ number_format($brand['products']) }}</td>
                                <td>{{ number_format($brand['published']) }}</td>
                                <td><span class="badge bg-success">{{ $brand['roi'] }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-info text-white">Price Drops Monitor (Wave 2)</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Current</th>
                                <th>Avg(30d)</th>
                                <th>Drop</th>
                                <th>Signal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($priceDrops as $drop)
                            <tr>
                                <td title="Lowest: ₹{{ number_format($drop['lowest_30d']) }}">{{ Str::limit($drop['product'], 20) }}</td>
                                <td>₹{{ number_format($drop['current']) }}</td>
                                <td><small class="text-muted">₹{{ number_format($drop['average']) }}</small></td>
                                <td class="text-danger">↓ {{ $drop['drop_percent'] }}%</td>
                                <td>
                                    @if($drop['buy_signal'])
                                        <span class="badge bg-success">BUY</span>
                                    @else
                                        <span class="badge bg-secondary">WAIT</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">Strategy ROI Ranking (Wave 3)</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($strategyRoiRanking as $rank)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $rank['strategy'] }}
                            <span class="badge bg-success rounded-pill">{{ $rank['roi'] }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">Opportunity Sources (Wave 3)</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($opportunitySources as $source)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $source['source'] }}
                            <span class="badge bg-secondary rounded-pill">{{ number_format($source['count']) }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">Provider Capability Matrix (Wave 3)</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0 text-center">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th title="Lightning">⚡</th>
                                <th title="Coupons">✂️</th>
                                <th title="Warehouse">📦</th>
                                <th title="Bank Offers">🏦</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($providerCapabilityMatrix as $provider => $caps)
                            <tr>
                                <td>{{ $provider }}</td>
                                <td>{!! $caps['Lightning'] ? '✅' : '❌' !!}</td>
                                <td>{!! $caps['Coupons'] ? '✅' : '❌' !!}</td>
                                <td>{!! $caps['Warehouse'] ? '✅' : '❌' !!}</td>
                                <td>{!! $caps['Bank Offers'] ? '✅' : '❌' !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="mt-4 mb-3">Consumer Value Dashboard (Wave 4)</h3>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">Effective Price Leaderboard</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0 text-center">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Base</th>
                                <th>Bank/Coupon</th>
                                <th>Exchange</th>
                                <th>Effective</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($effectivePriceLeaderboard as $deal)
                            <tr>
                                <td class="text-start">{{ Str::limit($deal['product'], 20) }}</td>
                                <td>₹{{ number_format($deal['base']) }}</td>
                                <td class="text-success">-₹{{ number_format(($deal['bank'] ?? 0) + ($deal['coupon'] ?? 0) + ($deal['subscribe'] ?? 0)) }}</td>
                                <td class="text-primary">-₹{{ number_format($deal['exchange'] ?? 0) }}</td>
                                <td><strong>₹{{ number_format($deal['effective']) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">Cross Provider Comparison</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0 text-center">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Amazon</th>
                                <th>Flipkart</th>
                                <th>Winner</th>
                                <th>Diff</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($crossProviderTracker as $tracker)
                            <tr>
                                <td class="text-start">{{ Str::limit($tracker['product'], 20) }}</td>
                                <td>₹{{ number_format($tracker['amazon']) }}</td>
                                <td>₹{{ number_format($tracker['flipkart']) }}</td>
                                <td><span class="badge {{ $tracker['winner'] == 'Tie' ? 'bg-secondary' : 'bg-success' }}">{{ $tracker['winner'] }}</span></td>
                                <td>₹{{ number_format($tracker['diff']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">Strategy Certification Status</div>
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap p-2">
                        @foreach($strategyCertification as $cert)
                        <div class="p-2 border rounded m-1 text-center" style="min-width: 150px;">
                            <strong>{{ $cert['strategy'] }}</strong><br>
                            <span class="badge bg-{{ $cert['class'] }} mt-1">{{ $cert['status'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3>Strategy Table</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Strategy</th>
                <th>Certification</th>
                <th>Mode</th>
                <th>Next Run</th>
                <th>Budget</th>
                <th>Metrics</th>
                <th>Health Breakdown</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($strategies as $strat)
                <tr>
                    <td>
                        <strong>{{ $strat['name'] }}</strong><br>
                        <small>v{{ $strat['version'] }} - <em>{{ $strat['notes'] }}</em></small><br>
                        <span class="badge bg-info">Heat: {{ $strat['heat'] }}</span>
                    </td>
                    <td>
                        @if($strat['lifecycle'] == 'CERTIFIED')
                            <span class="badge bg-success">🟢 CERTIFIED</span>
                        @elseif($strat['lifecycle'] == 'SHADOW')
                            <span class="badge bg-warning text-dark">🟡 SHADOW</span>
                        @else
                            <span class="badge bg-danger">🔴 EXPERIMENTAL</span>
                        @endif
                    </td>
                    <td>{{ $strat['mode'] }}</td>
                    <td>{{ $strat['next_run'] }}</td>
                    <td>{{ $strat['budget'] }}</td>
                    <td style="font-size: 0.85em;">
                        Gen: {{ number_format($strat['generated']) }}<br>
                        Pub: {{ number_format($strat['published']) }}<br>
                        Rev: {{ $strat['revenue'] }}<br>
                        ROI: {{ $strat['roi'] }}
                    </td>
                    <td style="font-size: 0.85em;">
                        OVR: {{ $strat['health']['overall'] }}%<br>
                        EXT: {{ $strat['health']['extraction'] }}%<br>
                        VAL: {{ $strat['health']['validation'] }}%<br>
                        PUB: {{ $strat['health']['publishing'] }}%<br>
                        ROI: {{ $strat['health']['roi'] }}%<br>
                        EXC: {{ $strat['health']['exceptions'] }}%
                    </td>
                    <td>
                        <button wire:click="forceRun('{{ $strat['id'] }}', 'run')" class="btn btn-sm btn-primary mb-1">Run</button>
                        <button wire:click="forceRun('{{ $strat['id'] }}', 'shadow')" class="btn btn-sm btn-secondary mb-1">Shadow</button>
                        <button wire:click="forceRun('{{ $strat['id'] }}', 'replay')" class="btn btn-sm btn-outline-info mb-1">Replay</button>
                        <button wire:click="forceRun('{{ $strat['id'] }}', 'dry_run')" class="btn btn-sm btn-outline-dark mb-1">Dry Run</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
