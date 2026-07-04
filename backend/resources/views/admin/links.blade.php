@extends('admin.layout')

@section('title', 'Affiliate Link Generator')

@section('content')
<div class="max-w-2xl bg-white rounded-lg shadow-sm border p-6">
    <p class="text-sm text-gray-600 mb-6">Manually generate tracked affiliate links for custom campaigns utilizing our saved Store IDs.</p>

    <form class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Target URL (Raw Product URL)</label>
            <input type="url" id="target_url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border py-2 px-3" placeholder="https://amazon.in/product-xyz">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Select Merchant</label>
            <select id="merchant_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border py-2 px-3">
                <option value="">-- Select Merchant --</option>
                @foreach($merchants as $merchant)
                    <option value="{{ $merchant->id }}">{{ $merchant->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700">Custom Sub-ID (Optional)</label>
            <input type="text" id="sub_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border py-2 px-3" placeholder="e.g. email_campaign_v2">
        </div>

        <button type="button" onclick="generateLink()" class="w-full bg-primary text-white px-4 py-2 rounded shadow hover:bg-blue-700 font-medium">Generate Link</button>
    </form>
    
    <div class="mt-8 pt-6 border-t hidden" id="resultBox">
        <label class="block text-sm font-medium text-gray-700 mb-2">Your Tracked Link:</label>
        <div class="flex">
            <input type="text" readonly id="resultUrl" class="block w-full rounded-l-md border-gray-300 shadow-sm sm:text-sm border bg-gray-50 py-2 px-3">
            <button onclick="navigator.clipboard.writeText(document.getElementById('resultUrl').value); alert('Copied!')" class="bg-gray-200 px-4 py-2 border border-l-0 border-gray-300 rounded-r-md text-gray-700 hover:bg-gray-300">Copy</button>
        </div>
    </div>
</div>

<script>
async function generateLink() {
    const url = document.getElementById('target_url').value;
    const merchant_id = document.getElementById('merchant_id').value;
    const sub_id = document.getElementById('sub_id').value;

    if (!url) {
        alert('Please enter a target URL');
        return;
    }

    try {
        const res = await fetch('{{ route("admin.links.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ url, merchant_id, sub_id })
        });
        const data = await res.json();
        
        if (!res.ok) {
            alert(data.error || 'Failed to generate link');
            return;
        }

        document.getElementById('resultUrl').value = data.url;
        document.getElementById('resultBox').classList.remove('hidden');
    } catch (e) {
        alert('Error generating link');
    }
}
</script>
@endsection
