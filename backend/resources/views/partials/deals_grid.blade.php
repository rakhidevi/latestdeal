@if($deals->isEmpty())
  <div class="col-span-full rounded-3xl border border-dashed border-gray-300 dark:border-slate-800 p-12 text-center w-full bg-slate-50/50 dark:bg-slate-900/40">
    <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3 text-xl">
      🔍
    </div>
    <h3 class="text-base font-bold text-gray-900 dark:text-slate-100">No deals match your filter criteria</h3>
    <p class="mt-1 text-xs text-gray-500 dark:text-slate-400 max-w-sm mx-auto">Try broadening your budget, category, or discount filters to view available catalog offers.</p>
    <div class="mt-4">
      <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-bold text-xs rounded-xl shadow-sm transition">
        <span>View All Deals</span>
        <span>&rarr;</span>
      </a>
    </div>
  </div>
@else
  @foreach($deals as $deal)
    <x-deal-card :deal="$deal" />
    
    @if($loop->iteration % 8 == 0)
        <!-- In-feed Ad after every 8 deals -->
        <div class="col-span-full">
            <x-ad-banner slot="in-feed" />
        </div>
    @endif
  @endforeach
@endif
