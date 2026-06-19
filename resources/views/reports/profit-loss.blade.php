@extends('layouts.hms')
@section('title','Profit & Loss')
@section('breadcrumb')
    <a href="{{ route('reports.index') }}" class="text-slate-400 hover:text-slate-600">Reports</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Profit & Loss</span>
@endsection

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Profit & Loss Statement</h1>
    <a href="{{ route('reports.profit-loss.export', request()->query()) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export Excel
    </a>
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="flex gap-3">
        <input type="date" name="from" value="{{ request('from', today()->startOfMonth()->toDateString()) }}"
               class="text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        <input type="date" name="to" value="{{ request('to', today()->toDateString()) }}"
               class="text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Generate</button>
    </div>
</form>

<div class="max-w-2xl space-y-4">
    {{-- Revenue --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 bg-green-50 dark:bg-green-900/20">
            <h3 class="font-semibold text-green-700 dark:text-green-400">Revenue</h3>
        </div>
        @foreach([
            ['OPD Revenue', $revenue['opd'] ?? 0],
            ['IPD Revenue', $revenue['ipd'] ?? 0],
            ['Pharmacy Revenue', $revenue['pharmacy'] ?? 0],
            ['Lab Revenue', $revenue['lab'] ?? 0],
            ['Other Revenue', $revenue['other'] ?? 0],
        ] as [$l,$v])
        <div class="px-5 py-3 flex justify-between border-b border-slate-100 dark:border-slate-700">
            <span class="text-sm text-slate-600 dark:text-slate-300">{{ $l }}</span>
            <span class="text-sm font-medium text-slate-700 dark:text-white">₨ {{ number_format($v, 0) }}</span>
        </div>
        @endforeach
        <div class="px-5 py-3 flex justify-between bg-green-50 dark:bg-green-900/20">
            <span class="font-bold text-slate-700 dark:text-white">Total Revenue</span>
            <span class="font-bold text-green-600 text-lg">₨ {{ number_format(array_sum($revenue ?? []), 0) }}</span>
        </div>
    </div>

    {{-- Expenses --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 bg-red-50 dark:bg-red-900/20">
            <h3 class="font-semibold text-red-700 dark:text-red-400">Expenses</h3>
        </div>
        @foreach($expenseByCategory ?? [] as $cat)
        <div class="px-5 py-3 flex justify-between border-b border-slate-100 dark:border-slate-700">
            <span class="text-sm text-slate-600 dark:text-slate-300">{{ $cat->name ?? '—' }}</span>
            <span class="text-sm font-medium text-slate-700 dark:text-white">₨ {{ number_format($cat->total ?? 0, 0) }}</span>
        </div>
        @endforeach
        @foreach([['Salaries', $totalSalaries ?? 0]] as [$l,$v])
        <div class="px-5 py-3 flex justify-between border-b border-slate-100 dark:border-slate-700">
            <span class="text-sm text-slate-600 dark:text-slate-300">{{ $l }}</span>
            <span class="text-sm font-medium text-slate-700 dark:text-white">₨ {{ number_format($v, 0) }}</span>
        </div>
        @endforeach
        <div class="px-5 py-3 flex justify-between bg-red-50 dark:bg-red-900/20">
            <span class="font-bold text-slate-700 dark:text-white">Total Expenses</span>
            <span class="font-bold text-red-500 text-lg">₨ {{ number_format($totalExpenses ?? 0, 0) }}</span>
        </div>
    </div>

    {{-- Net Profit --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border-2 {{ ($netProfit ?? 0) >= 0 ? 'border-green-400' : 'border-red-400' }} p-5 flex justify-between items-center">
        <span class="text-lg font-bold text-slate-800 dark:text-white">Net Profit / (Loss)</span>
        <span class="text-2xl font-black {{ ($netProfit ?? 0) >= 0 ? 'text-green-600' : 'text-red-500' }}">
            ₨ {{ number_format($netProfit ?? 0, 0) }}
        </span>
    </div>
</div>
@endsection
