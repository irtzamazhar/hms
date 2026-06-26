@extends('layouts.hms')
@section('title','Daily Closing Report')
@section('breadcrumb')
    <a href="{{ route('reports.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Reports</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Daily Closing</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
<div class="flex justify-between items-center mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Daily Closing Report</h1>
    <div class="flex gap-2">
        @if($report)
        <a href="{{ route('reports.daily.pdf', $report->id) }}" target="_blank" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">📄 PDF</a>
        @endif
    </div>
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="grid grid-cols-3 gap-3">
        <input type="date" name="date" value="{{ request('date', today()->toDateString()) }}"
               class="field">
        <select name="shift" class="field">
            @foreach(['morning','evening','night'] as $s)
                <option value="{{ $s }}" @selected(request('shift', $currentShift)===$s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-3 py-2 bg-primary-600 text-white text-sm rounded-lg">Load</button>
    </div>
</form>

@if($report)
<div class="space-y-4">
    {{-- Header --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-slate-400">Closing Report</p>
                <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $report->report_date->format('d M Y') }}</h2>
            </div>
            <x-badge color="{{ $report->closed_at ? 'green' : 'amber' }}">{{ $report->closed_at ? 'Closed' : 'Draft' }}</x-badge>
        </div>
    </div>

    {{-- Department summaries --}}
    <div class="grid md:grid-cols-2 gap-4">
        @foreach([
            ['OPD', $report->total_opd_patients ?? 0, $report->opd_revenue ?? 0],
            ['IPD', $report->total_ipd_admissions ?? 0, $report->ipd_revenue ?? 0],
            ['Pharmacy', null, $report->pharmacy_revenue ?? 0],
            ['Laboratory', null, $report->lab_revenue ?? 0],
        ] as [$dept, $count, $revenue])
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">{{ $dept }}</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">
                @if(!is_null($count)){{ $count }} <span class="text-sm font-normal text-slate-400">records</span>@else ₨ {{ number_format($revenue, 0) }}@endif
            </p>
            @if(!is_null($count))<p class="text-sm font-semibold text-green-600 mt-0.5">₨ {{ number_format($revenue, 0) }}</p>@endif
        </div>
        @endforeach
    </div>

    {{-- Totals --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
        <div class="px-5 py-3 flex justify-between"><span class="text-slate-400">Total Revenue</span><span class="font-bold text-green-600">₨ {{ number_format($report->total_revenue ?? 0, 0) }}</span></div>
        <div class="px-5 py-3 flex justify-between"><span class="text-slate-400">Total Expenses</span><span class="font-bold text-red-500">₨ {{ number_format($report->total_expenses ?? 0, 0) }}</span></div>
        <div class="px-5 py-3 flex justify-between"><span class="font-bold text-slate-700 dark:text-white">Net Profit</span><span class="font-bold text-lg {{ ($report->net_profit ?? 0) >= 0 ? 'text-green-600' : 'text-red-500' }}">₨ {{ number_format($report->net_profit ?? 0, 0) }}</span></div>
    </div>

    {{-- Regenerate button --}}
    @can('close daily reports')
    <form method="POST" action="{{ route('reports.daily-closing.close') }}" onsubmit="return confirm('Regenerate today\'s closing report with the latest figures?')">
        @csrf
        <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl">Regenerate Report</button>
    </form>
    @endcan
</div>
@else
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-12 text-center">
    <p class="text-slate-400 text-sm">No report found for the selected date and shift.</p>
    @can('close daily reports')
    <form method="POST" action="{{ route('reports.daily-closing.close') }}" class="mt-4 inline-block">
        @csrf
        <input type="hidden" name="date" value="{{ request('date', today()->toDateString()) }}">
        <input type="hidden" name="shift" value="{{ request('shift', $currentShift) }}">
        <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Generate Report</button>
    </form>
    @endcan
</div>
@endif
</div>
@endsection
