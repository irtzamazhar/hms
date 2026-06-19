@extends('layouts.hms')
@section('title','Pharmacy Report')
@section('breadcrumb')
    <a href="{{ route('reports.index') }}" class="text-slate-400 hover:text-slate-600">Reports</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Pharmacy Report</span>
@endsection

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Pharmacy Report</h1>
    <a href="{{ route('reports.pharmacy.export', request()->query()) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export Excel
    </a>
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="flex gap-3">
        <input type="date" name="from" value="{{ request('from', today()->startOfMonth()->toDateString()) }}"
               class="field">
        <input type="date" name="to" value="{{ request('to', today()->toDateString()) }}"
               class="field">
        <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Generate</button>
    </div>
</form>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
    @foreach([
        ['Total Sales', $summary['total_sales'] ?? 0, 'text-slate-800'],
        ['Revenue', '₨ '.number_format($summary['revenue'] ?? 0, 0), 'text-green-600'],
        ['Cost', '₨ '.number_format($summary['cost'] ?? 0, 0), 'text-red-500'],
        ['Gross Profit', '₨ '.number_format(($summary['revenue'] ?? 0) - ($summary['cost'] ?? 0), 0), 'text-primary-600'],
    ] as [$l,$v,$c])
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">{{ $l }}</p>
        <p class="text-2xl font-bold {{ $c }} mt-1">{{ $v }}</p>
    </div>
    @endforeach
</div>

{{-- Low stock alert --}}
@if(isset($lowStock) && $lowStock->count())
<div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-4 mb-4">
    <p class="text-sm font-bold text-amber-700 dark:text-amber-400 mb-2">⚠ Low Stock Alert ({{ $lowStock->count() }} medicines)</p>
    <div class="flex flex-wrap gap-2">
        @foreach($lowStock as $m)
        <span class="px-2 py-1 bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200 text-xs rounded-lg">{{ $m->name }} ({{ $m->stock_quantity }})</span>
        @endforeach
    </div>
</div>
@endif

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 font-semibold text-sm text-slate-700 dark:text-white">Sales Records</div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs text-slate-500">Sale #</th>
                    <th class="px-4 py-2.5 text-left text-xs text-slate-500">Patient</th>
                    <th class="px-4 py-2.5 text-center text-xs text-slate-500">Items</th>
                    <th class="px-4 py-2.5 text-right text-xs text-slate-500">Amount</th>
                    <th class="px-4 py-2.5 text-left text-xs text-slate-500">Shift</th>
                    <th class="px-4 py-2.5 text-left text-xs text-slate-500">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($sales as $s)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                    <td class="px-4 py-2.5 font-mono text-xs text-primary-600">{{ $s->sale_number }}</td>
                    <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $s->patient?->name ?? 'Walk-in' }}</td>
                    <td class="px-4 py-2.5 text-center text-slate-500">{{ $s->items_count }}</td>
                    <td class="px-4 py-2.5 text-right font-semibold text-slate-700 dark:text-white">₨ {{ number_format($s->net_amount, 0) }}</td>
                    <td class="px-4 py-2.5 text-slate-500">{{ ucfirst($s->shift) }}</td>
                    <td class="px-4 py-2.5 text-xs text-slate-400">{{ $s->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400 text-sm">No sales for selected period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
