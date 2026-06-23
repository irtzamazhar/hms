@extends('layouts.hms')
@section('title','Monthly Closing Report')
@section('breadcrumb')
    <a href="{{ route('reports.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Reports</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Monthly Closing</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
<div class="flex justify-between items-center mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Monthly Closing Report</h1>
    @if($report)
    <a href="{{ route('reports.monthly.pdf', $report->id) }}" target="_blank" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">📄 PDF</a>
    @endif
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="flex gap-3">
        <select name="month" class="field">
            @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" @selected(request('month', now()->month)==$m)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
            @endfor
        </select>
        <select name="year" class="field">
            @for($y = now()->year; $y >= now()->year-3; $y--)
            <option value="{{ $y }}" @selected(request('year', now()->year)==$y)>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Load</button>
    </div>
</form>

@if($report)
<div class="space-y-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex justify-between items-start">
        <div>
            <p class="text-sm text-slate-400">Monthly Report</p>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ date('F Y', mktime(0,0,0,$report->month,1,$report->year)) }}</h2>
        </div>
        <x-badge color="{{ $report->is_closed ? 'green' : 'amber' }}">{{ $report->is_closed ? 'Closed' : 'Draft' }}</x-badge>
    </div>

    {{-- Department Totals --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['OPD Revenue', '₨ '.number_format($report->opd_revenue ?? 0, 0), 'blue'],
            ['IPD Revenue', '₨ '.number_format($report->ipd_revenue ?? 0, 0), 'green'],
            ['Pharmacy', '₨ '.number_format($report->pharmacy_revenue ?? 0, 0), 'purple'],
            ['Lab Revenue', '₨ '.number_format($report->lab_revenue ?? 0, 0), 'cyan'],
        ] as [$l,$v,$c])
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs text-slate-400">{{ $l }}</p>
            <p class="text-xl font-bold text-{{ $c }}-600 mt-1">{{ $v }}</p>
        </div>
        @endforeach
    </div>

    {{-- P&L --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
        @foreach([
            ['Total Revenue', '₨ '.number_format($report->total_revenue ?? 0, 0), 'text-green-600'],
            ['Salaries', '₨ '.number_format($report->total_salaries ?? 0, 0), 'text-red-500'],
            ['Expenses', '₨ '.number_format($report->total_expenses ?? 0, 0), 'text-red-500'],
            ['Net Profit', '₨ '.number_format($report->net_profit ?? 0, 0), ($report->net_profit ?? 0) >= 0 ? 'text-green-600 text-lg font-bold' : 'text-red-500 text-lg font-bold'],
        ] as [$l,$v,$c])
        <div class="px-5 py-3 flex justify-between"><span class="text-slate-500 dark:text-slate-400">{{ $l }}</span><span class="font-semibold {{ $c }}">{{ $v }}</span></div>
        @endforeach
    </div>

    @if(!$report->is_closed)
    @can('close monthly reports')
    <form method="POST" action="{{ route('reports.monthly-closing.close') }}" onsubmit="return confirm('Close this month? This cannot be undone.')">
        @csrf
        <input type="hidden" name="month" value="{{ $report->month }}">
        <input type="hidden" name="year" value="{{ $report->year }}">
        <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl">Close Month — {{ date('F Y', mktime(0,0,0,$report->month,1,$report->year)) }}</button>
    </form>
    @endcan
    @endif
</div>
@else
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-12 text-center">
    <p class="text-slate-400 text-sm">No report found for selected month.</p>
    @can('close monthly reports')
    <form method="POST" action="{{ route('reports.monthly-closing.close') }}" class="mt-4 inline-block">
        @csrf
        <input type="hidden" name="month" value="{{ request('month', now()->month) }}">
        <input type="hidden" name="year" value="{{ request('year', now()->year) }}">
        <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Generate Report</button>
    </form>
    @endcan
</div>
@endif
</div>
@endsection
