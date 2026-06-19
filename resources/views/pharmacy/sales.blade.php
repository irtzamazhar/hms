@extends('layouts.hms')
@section('title','Pharmacy Sales')
@section('breadcrumb')
    <span class="text-slate-400">Pharmacy</span> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Sales</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">Pharmacy Sales</h1>
        <p class="text-sm text-slate-400">{{ $sales->total() }} total records</p>
    </div>
    @can('create pharmacy')
    <a href="{{ route('pharmacy.pos') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        New Sale (POS)
    </a>
    @endcan
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <input type="date" name="from" value="{{ request('from', today()->toDateString()) }}"
               class="field">
        <input type="date" name="to" value="{{ request('to', today()->toDateString()) }}"
               class="field">
        <select name="shift" class="field">
            <option value="">All Shifts</option>
            @foreach(['morning','evening','night'] as $s)
                <option value="{{ $s }}" @selected(request('shift')===$s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 text-white text-sm rounded-lg">Filter</button>
            <a href="{{ route('pharmacy.sales') }}" class="btn-cancel">Reset</a>
        </div>
    </div>
</form>

{{-- Summary cards --}}
<div class="grid grid-cols-3 gap-4 mb-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Total Sales</p>
        <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $sales->total() }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Total Revenue</p>
        <p class="text-2xl font-bold text-green-600">₨ {{ number_format($totalRevenue ?? 0, 0) }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Total Discount</p>
        <p class="text-2xl font-bold text-red-500">₨ {{ number_format($totalDiscount ?? 0, 0) }}</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Sale #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Patient</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Items</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Amount</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Shift</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($sales as $s)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 py-3 font-mono text-xs text-primary-600 dark:text-primary-400 font-medium">{{ $s->sale_number }}</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $s->patient?->name ?? 'Walk-in' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $s->items_count }} items</td>
                    <td class="px-4 py-3 font-semibold text-slate-700 dark:text-white">₨ {{ number_format($s->net_amount, 0) }}</td>
                    <td class="px-4 py-3">
                        <x-badge color="{{ ['morning'=>'amber','evening'=>'blue','night'=>'purple'][$s->shift] ?? 'slate' }}">{{ ucfirst($s->shift) }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-400">{{ $s->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1">
                            <a href="{{ route('pharmacy.sale.show',$s) }}" class="p-1.5 rounded text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('pharmacy.sale.print',$s) }}" target="_blank" class="p-1.5 rounded text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-green-600" title="Print">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400 text-sm">No sales found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sales->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $sales->links() }}</div>
    @endif
</div>
@endsection
