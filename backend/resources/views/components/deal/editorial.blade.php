@props(['deal'])

<div class="bg-white rounded-[2rem] border border-slate-200 p-8 shadow-sm">
    <div class="flex items-start gap-4 mb-6">
        <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
        </div>
        <div>
            <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-1">AI-Assisted Summary</h2>
            <h3 class="text-2xl font-black text-slate-900">Our Take</h3>
        </div>
    </div>

    @if($deal->editorial_summary)
        <div class="text-slate-700 leading-relaxed font-medium mb-8">
            {!! nl2br(e($deal->editorial_summary)) !!}
        </div>
    @endif

    <div class="space-y-4">
        @php
            $pros = is_string($deal->pros) ? json_decode($deal->pros, true) : $deal->pros;
            $cons = is_string($deal->cons) ? json_decode($deal->cons, true) : $deal->cons;
        @endphp

        @if(!empty($pros) && is_array($pros))
            <ul class="space-y-3 mb-6">
                @foreach($pros as $pro)
                    <li class="flex items-start gap-3 text-sm font-bold text-slate-700">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        {{ $pro }}
                    </li>
                @endforeach
            </ul>
        @endif

        @if(!empty($cons) && is_array($cons))
            <div class="pt-6 border-t border-slate-100">
                <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">What to consider</h4>
                <ul class="space-y-3">
                    @foreach($cons as $con)
                        <li class="flex items-start gap-3 text-sm font-bold text-slate-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-2 shrink-0"></span>
                            {{ $con }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
