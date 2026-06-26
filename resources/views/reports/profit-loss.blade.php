@extends('layouts.hms')
@section('title','Profit & Loss')
@section('breadcrumb')
    <a href="{{ route('reports.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Reports</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Profit & Loss</span>
@endsection

@php
    $totalRevenue = array_sum($revenue);
    $netMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;
    $isProfit = ($netProfit ?? 0) >= 0;

    $revenueItems = collect([
        ['label' => 'OPD',        'value' => $revenue['opd'] ?? 0],
        ['label' => 'IPD',        'value' => $revenue['ipd'] ?? 0],
        ['label' => 'Pharmacy',   'value' => $revenue['pharmacy'] ?? 0],
        ['label' => 'Laboratory', 'value' => $revenue['lab'] ?? 0],
        ['label' => 'Other',      'value' => $revenue['other'] ?? 0],
    ])->sortByDesc('value')->values();

    $expenseItems = collect($expenseByCategory)
        ->map(fn ($c) => ['label' => $c->name ?? '—', 'value' => (float) $c->total])
        ->push(['label' => 'Salaries', 'value' => (float) $totalSalaries])
        ->sortByDesc('value')->values();
@endphp

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Profit &amp; Loss Statement</h1>
            <p class="text-sm text-slate-400">{{ \Illuminate\Support\Carbon::parse($from)->format('d M Y') }} — {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }}</p>
        </div>
        <a href="{{ route('reports.profit-loss.export', request()->query()) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Excel
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <div class="flex flex-wrap gap-3">
            <input type="date" name="from" value="{{ request('from', today()->startOfMonth()->toDateString()) }}" class="field md:w-auto">
            <input type="date" name="to" value="{{ request('to', today()->toDateString()) }}" class="field md:w-auto">
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg">Generate</button>
        </div>
    </form>

    {{-- KPI summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Total Revenue --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-green-500"></div>
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Revenue</p>
                <span class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17l6-6 4 4 8-8m0 0h-5m5 0v5"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-green-600 mt-2">₨ {{ number_format($totalRevenue, 0) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $revenueItems->where('value', '>', 0)->count() }} active streams</p>
        </div>

        {{-- Total Expenses --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Expenses</p>
                <span class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l6 6 4-4 8 8m0 0h-5m5 0v-5"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-red-500 mt-2">₨ {{ number_format($totalExpenses, 0) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $totalRevenue > 0 ? round(($totalExpenses / $totalRevenue) * 100) : 0 }}% of revenue</p>
        </div>

        {{-- Net Profit / Loss --}}
        <div class="rounded-xl border p-5 relative overflow-hidden {{ $isProfit ? 'bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-700' : 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-700' }}">
            <div class="absolute top-0 left-0 w-1 h-full {{ $isProfit ? 'bg-green-500' : 'bg-red-500' }}"></div>
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide {{ $isProfit ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">Net {{ $isProfit ? 'Profit' : 'Loss' }}</p>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $isProfit ? 'bg-green-200 dark:bg-green-800 text-green-800 dark:text-green-200' : 'bg-red-200 dark:bg-red-800 text-red-800 dark:text-red-100' }}">
                    {{ $netMargin }}% margin
                </span>
            </div>
            <p class="text-2xl font-black mt-2 {{ $isProfit ? 'text-green-600' : 'text-red-500' }}">₨ {{ number_format($netProfit ?? 0, 0) }}</p>
            <p class="text-xs mt-1 {{ $isProfit ? 'text-green-700/70 dark:text-green-400/70' : 'text-red-700/70 dark:text-red-400/70' }}">Revenue − Expenses</p>
        </div>
    </div>

    {{-- Breakdown: Revenue | Expenses side by side --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">

        {{-- Revenue breakdown --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-green-50 dark:bg-green-900/20">
                <h3 class="font-semibold text-green-700 dark:text-green-400">Revenue Breakdown</h3>
                <span class="text-sm font-bold text-green-600">₨ {{ number_format($totalRevenue, 0) }}</span>
            </div>
            <div class="p-5 space-y-4">
                @forelse($revenueItems as $item)
                @php $pct = $totalRevenue > 0 ? round(($item['value'] / $totalRevenue) * 100, 1) : 0; @endphp
                <div>
                    <div class="flex justify-between items-baseline mb-1.5">
                        <span class="text-sm text-slate-600 dark:text-slate-300">{{ $item['label'] }}</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-white">₨ {{ number_format($item['value'], 0) }}
                            <span class="text-xs font-normal text-slate-400 ml-1">{{ $pct }}%</span>
                        </span>
                    </div>
                    <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">No revenue in this period.</p>
                @endforelse
            </div>
        </div>

        {{-- Expense breakdown --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-red-50 dark:bg-red-900/20">
                <h3 class="font-semibold text-red-700 dark:text-red-400">Expense Breakdown</h3>
                <span class="text-sm font-bold text-red-500">₨ {{ number_format($totalExpenses, 0) }}</span>
            </div>
            <div class="p-5 space-y-4">
                @forelse($expenseItems as $item)
                @php $pct = $totalExpenses > 0 ? round(($item['value'] / $totalExpenses) * 100, 1) : 0; @endphp
                <div>
                    <div class="flex justify-between items-baseline mb-1.5">
                        <span class="text-sm text-slate-600 dark:text-slate-300">{{ $item['label'] }}</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-white">₨ {{ number_format($item['value'], 0) }}
                            <span class="text-xs font-normal text-slate-400 ml-1">{{ $pct }}%</span>
                        </span>
                    </div>
                    <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-red-500 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">No expenses in this period.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
