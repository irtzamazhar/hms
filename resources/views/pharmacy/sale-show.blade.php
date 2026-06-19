@extends('layouts.hms')
@section('title','Sale — '.$sale->sale_number)
@section('breadcrumb')
    <a href="{{ route('pharmacy.sales') }}" class="text-slate-400 hover:text-slate-600">Sales</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $sale->sale_number }}</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="flex justify-end gap-2">
        <a href="{{ route('pharmacy.sale.print',$sale) }}" target="_blank" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">🖨 Print Receipt</a>
        <a href="{{ route('pharmacy.sales') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm rounded-lg">Back</a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
        <div class="px-5 py-4 flex justify-between items-start">
            <div>
                <p class="text-xs text-slate-400 font-mono">{{ $sale->sale_number }}</p>
                <p class="font-bold text-slate-800 dark:text-white">{{ $sale->patient?->name ?? 'Walk-in Customer' }}</p>
                <p class="text-sm text-slate-400">{{ $sale->created_at->format('d M Y H:i') }} · {{ ucfirst($sale->shift) }} Shift</p>
            </div>
            <x-badge color="{{ ['paid'=>'green','pending'=>'amber','partial'=>'blue'][$sale->payment_status ?? 'paid'] ?? 'slate' }}">
                {{ ucfirst($sale->payment_status ?? 'paid') }}
            </x-badge>
        </div>

        {{-- Items --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs text-slate-500">Medicine</th>
                        <th class="px-4 py-2.5 text-center text-xs text-slate-500">Qty</th>
                        <th class="px-4 py-2.5 text-right text-xs text-slate-500">Unit Price</th>
                        <th class="px-4 py-2.5 text-right text-xs text-slate-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($sale->items as $item)
                    <tr>
                        <td class="px-4 py-2.5">
                            <p class="font-medium text-slate-700 dark:text-white">{{ $item->medicine->name ?? $item->medicine_name }}</p>
                            <p class="text-xs text-slate-400">{{ $item->medicine->generic_name ?? '' }}</p>
                        </td>
                        <td class="px-4 py-2.5 text-center text-slate-600 dark:text-slate-300">{{ $item->quantity }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600 dark:text-slate-300">₨ {{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-medium text-slate-700 dark:text-white">₨ {{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="px-5 py-4 space-y-2">
            <div class="flex justify-between text-sm"><span class="text-slate-400">Subtotal</span><span>₨ {{ number_format($sale->total_amount, 2) }}</span></div>
            @if(($sale->discount_amount ?? 0) > 0)
            <div class="flex justify-between text-sm text-red-500"><span>Discount</span><span>— ₨ {{ number_format($sale->discount_amount, 2) }}</span></div>
            @endif
            <div class="flex justify-between font-bold text-base border-t border-slate-200 dark:border-slate-600 pt-2">
                <span>Net Total</span><span class="text-primary-600">₨ {{ number_format($sale->net_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm text-slate-400">
                <span>Payment</span><span>{{ ucfirst(str_replace('_',' ',$sale->payment_method ?? 'cash')) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
