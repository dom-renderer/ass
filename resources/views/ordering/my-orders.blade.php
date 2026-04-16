<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="max-w-5xl mx-auto px-3 py-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">My Orders</h1>
                <p class="text-xs text-slate-500">Recent and past orders with full details</p>
            </div>
            <a href="{{ route('ordering.menu') }}" class="text-sm bg-slate-900 text-white px-3 py-2 rounded-lg">Back to Menu</a>
        </div>

        @if($orders->isEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-500">
                No orders found yet.
            </div>
        @else
            <div class="space-y-3">
                @foreach($orders as $order)
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-100 flex flex-wrap gap-3 items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Order {{ $order->order_number }}</div>
                                <div class="text-xs text-slate-500">{{ optional($order->store)->name }} • Table {{ $order->table_number }} • {{ $order->created_at->format('d M Y, h:i A') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-slate-500">Status</div>
                                <div class="text-sm font-semibold capitalize">{{ $order->status }}</div>
                                <div class="text-sm font-bold text-emerald-600">Rs {{ number_format((float)$order->grand_total, 2) }}</div>
                            </div>
                        </div>
                        <div class="px-4 py-3">
                            <div class="text-xs text-slate-500 mb-2">Items</div>
                            <div class="space-y-2">
                                @foreach($order->items as $item)
                                    @php
                                        $attrs = collect($item->attributes ?? [])->map(function($a){ return ($a['attribute_name'] ?? 'Attr').': '.($a['value'] ?? ''); })->filter()->values();
                                        $adds = collect($item->addons ?? [])->map(function($a){ return 'Add-on: '.($a['name'] ?? ''); })->filter()->values();
                                        $chips = $attrs->merge($adds);
                                    @endphp
                                    <div class="rounded-lg border border-slate-200 p-2">
                                        <div class="flex justify-between gap-2">
                                            <div>
                                                <div class="text-sm font-medium text-slate-800">{{ $item->product_name }} <span class="text-slate-500">x{{ $item->quantity }}</span></div>
                                                @if($chips->isNotEmpty())
                                                    <div class="mt-1 flex flex-wrap gap-1">
                                                        @foreach($chips as $chip)
                                                            <span class="text-[11px] px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $chip }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-sm font-semibold text-slate-700">Rs {{ number_format((float)$item->line_total, 2) }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($order->coupon_code)
                                <div class="mt-2 text-xs text-slate-500">Coupon: <span class="font-medium text-slate-700">{{ $order->coupon_code }}</span></div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>

