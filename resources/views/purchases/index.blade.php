@extends('layouts.hms')
@section('title','Purchases')
@section('breadcrumb')
    <span class="text-slate-400">Pharmacy</span> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Purchases</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Purchase Orders</h1>
    <div class="flex gap-2">
        <a href="{{ route('purchases.export', request()->query()) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Excel
        </a>
        @can('manage purchases')
        <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Purchase
        </a>
        @endcan
    </div>
</div>

{{-- Summary --}}
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Month Purchases</p>
        <p class="text-xl font-bold text-blue-600 mt-1">₨ {{ number_format($summary['month_total'], 0) }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Pending Payment</p>
        <p class="text-xl font-bold text-amber-600 mt-1">{{ $summary['pending'] }} orders</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <select name="supplier_id" class="field">
            <option value="">All Suppliers</option>
            @foreach($suppliers as $s)
            <option value="{{ $s->id }}" @selected(request('supplier_id') == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
        <select name="status" class="field">
            <option value="">All Status</option>
            @foreach(['pending','partial','paid'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From"
               class="field">
        <div class="flex gap-2">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To"
                   class="field">
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Go</button>
        </div>
    </div>
</form>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">PO #</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Supplier</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Date</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Total</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Paid</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Payment</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($purchases as $p)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $p->purchase_number }}</td>
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800 dark:text-white">{{ $p->supplier?->name }}</p>
                    @if($p->supplier?->company)<p class="text-xs text-slate-400">{{ $p->supplier->company }}</p>@endif
                </td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $p->purchase_date->format('d M Y') }}</td>
                <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-white">₨ {{ number_format($p->total_amount, 0) }}</td>
                <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">₨ {{ number_format($p->paid_amount, 0) }}</td>
                <td class="px-4 py-3">
                    <x-badge color="{{ ['paid'=>'green','partial'=>'amber','pending'=>'red'][$p->payment_status] ?? 'slate' }}">{{ ucfirst($p->payment_status) }}</x-badge>
                </td>
                <td class="px-4 py-3 flex gap-2 justify-end">
                    <a href="{{ route('purchases.show', $p) }}" class="text-xs text-primary-600 hover:underline">View</a>
                    <a href="{{ route('purchases.print', $p) }}" target="_blank" class="text-xs text-green-600 hover:underline">PDF</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No purchases found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($purchases->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $purchases->links() }}</div>
    @endif
</div>
@endsection
