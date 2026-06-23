@extends('layouts.hms')
@section('title','Purchase — '.$purchase->purchase_number)
@section('breadcrumb')
    <a href="{{ route('purchases.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Purchases</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">{{ $purchase->purchase_number }}</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">{{ $purchase->purchase_number }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('purchases.print', $purchase) }}" target="_blank" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">PDF</a>
        @can('manage purchases')
        <a href="{{ route('purchases.edit', $purchase) }}" class="px-4 py-2 border text-slate-600 dark:text-slate-300 hover:bg-slate-50">Edit Payment</a>
        @endcan
    </div>
</div>

<div class="grid md:grid-cols-3 gap-4 mb-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 md:col-span-2">
        <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
            <div><dt class="text-slate-400">Supplier</dt><dd class="font-medium text-slate-700 dark:text-white">{{ $purchase->supplier?->name }}</dd></div>
            <div><dt class="text-slate-400">Date</dt><dd class="font-medium text-slate-700 dark:text-white">{{ $purchase->purchase_date->format('d M Y') }}</dd></div>
            <div><dt class="text-slate-400">Invoice #</dt><dd class="font-medium text-slate-700 dark:text-white">{{ $purchase->invoice_number ?? '—' }}</dd></div>
            <div><dt class="text-slate-400">Payment Method</dt><dd class="font-medium text-slate-700 dark:text-white">{{ ucfirst(str_replace('_',' ',$purchase->payment_method)) }}</dd></div>
            <div><dt class="text-slate-400">Created By</dt><dd class="font-medium text-slate-700 dark:text-white">{{ $purchase->createdBy?->name }}</dd></div>
            <div><dt class="text-slate-400">Status</dt><dd><x-badge color="{{ ['paid'=>'green','partial'=>'amber','pending'=>'red'][$purchase->payment_status] ?? 'slate' }}">{{ ucfirst($purchase->payment_status) }}</x-badge></dd></div>
        </dl>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 space-y-3">
        @foreach([
            ['Subtotal', '₨ '.number_format($purchase->subtotal,0), ''],
            ['Discount', '− ₨ '.number_format($purchase->discount,0), 'text-red-500'],
            ['Tax', '+ ₨ '.number_format($purchase->tax,0), ''],
            ['Total', '₨ '.number_format($purchase->total_amount,0), 'text-lg font-bold'],
            ['Paid', '₨ '.number_format($purchase->paid_amount,0), 'text-green-600'],
            ['Due', '₨ '.number_format($purchase->due_amount,0), 'text-red-500'],
        ] as [$l,$v,$c])
        <div class="flex justify-between items-center text-sm border-b border-slate-100 dark:border-slate-700 pb-2 last:border-0">
            <span class="text-slate-400">{{ $l }}</span>
            <span class="font-semibold {{ $c }} text-slate-800 dark:text-white">{{ $v }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- Items table --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700">
        <h2 class="font-semibold text-slate-800 dark:text-white">Items ({{ $purchase->items->count() }})</h2>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Medicine</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Qty</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Unit Price</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Sale Price</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Batch</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Expiry</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($purchase->items as $item)
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-white">{{ $item->medicine?->name }}</td>
                <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-300">{{ $item->quantity }}</td>
                <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">₨ {{ number_format($item->unit_price, 2) }}</td>
                <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">₨ {{ number_format($item->sale_price, 2) }}</td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400 font-mono text-xs">{{ $item->batch_number ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $item->expiry_date?->format('M Y') ?? '—' }}</td>
                <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-white">₨ {{ number_format($item->total_price, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
