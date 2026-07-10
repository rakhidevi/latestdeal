@if($deals->isEmpty())
  <div class="col-span-full rounded-2xl border border-dashed border-gray-300 p-12 text-center dark:border-slate-700 w-full">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100">No deals available yet</h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Try adjusting your filters or check back later.</p>
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
