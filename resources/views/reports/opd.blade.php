@extends('layouts.hms')
@section('title','OPD Report')
@section('breadcrumb')
    <a href="{{ route('reports.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Reports</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">OPD Report</span>
@endsection

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">OPD Report</h1>
    <a href="{{ route('reports.opd.export', request()->query()) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export Excel
    </a>
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <input type="date" name="from" value="{{ request('from', today()->startOfMonth()->toDateString()) }}"
               class="field">
        <input type="date" name="to" value="{{ request('to', today()->toDateString()) }}"
               class="field">
        <select name="doctor_id" class="field">
            <option value="">All Doctors</option>
            @foreach($doctors as $d)
                <option value="{{ $d->id }}" @selected(request('doctor_id')==$d->id)>Dr. {{ $d->user->name }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 text-white text-sm rounded-lg">Generate</button>
            <a href="{{ route('reports.opd') }}" class="btn-cancel">Reset</a>
        </div>
    </div>
</form>

{{-- Summary --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
    @foreach([
        ['Total Visits', $summary['total_visits'] ?? 0, 'text-slate-800'],
        ['Revenue', '₨ '.number_format($summary['total_revenue'] ?? 0, 0), 'text-green-600'],
        ['Discount', '₨ '.number_format($summary['total_discount'] ?? 0, 0), 'text-red-500'],
        ['Avg Fee', '₨ '.number_format($summary['avg_fee'] ?? 0, 0), 'text-blue-600'],
    ] as [$l,$v,$c])
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">{{ $l }}</p>
        <p class="text-2xl font-bold {{ $c }} mt-1">{{ $v }}</p>
    </div>
    @endforeach
</div>

{{-- Doctor-wise breakdown --}}
@if(isset($byDoctor) && $byDoctor->count())
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden mb-4">
    <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 font-semibold text-sm text-slate-700 dark:text-white">Doctor-wise Summary</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-2.5 text-left text-xs text-slate-500">Doctor</th>
                <th class="px-4 py-2.5 text-center text-xs text-slate-500">Visits</th>
                <th class="px-4 py-2.5 text-right text-xs text-slate-500">Revenue</th>
                <th class="px-4 py-2.5 text-right text-xs text-slate-500">Discount</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($byDoctor as $row)
            <tr>
                <td class="px-4 py-2.5 font-medium text-slate-700 dark:text-white">Dr. {{ $row->doctor_name ?? '—' }}</td>
                <td class="px-4 py-2.5 text-center text-slate-600 dark:text-slate-300">{{ $row->visits_count }}</td>
                <td class="px-4 py-2.5 text-right font-semibold text-green-600">₨ {{ number_format($row->revenue, 0) }}</td>
                <td class="px-4 py-2.5 text-right text-red-500">₨ {{ number_format($row->discount, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Visits table --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 font-semibold text-sm text-slate-700 dark:text-white">Visit Details</div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs text-slate-500">Visit #</th>
                    <th class="px-4 py-2.5 text-left text-xs text-slate-500">Patient</th>
                    <th class="px-4 py-2.5 text-left text-xs text-slate-500">Doctor</th>
                    <th class="px-4 py-2.5 text-left text-xs text-slate-500">Shift</th>
                    <th class="px-4 py-2.5 text-right text-xs text-slate-500">Amount</th>
                    <th class="px-4 py-2.5 text-left text-xs text-slate-500">Date</th>
                    <th class="px-4 py-2.5 text-left text-xs text-slate-500">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($visits as $v)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                    <td class="px-4 py-2.5 font-mono text-xs text-primary-600">{{ $v->visit_number }}</td>
                    <td class="px-4 py-2.5 text-slate-700 dark:text-white">{{ $v->patient->name }}</td>
                    <td class="px-4 py-2.5 text-slate-500">Dr. {{ $v->doctor->user->name }}</td>
                    <td class="px-4 py-2.5 text-slate-500">{{ ucfirst($v->shift) }}</td>
                    <td class="px-4 py-2.5 text-right font-semibold text-slate-700 dark:text-white">₨ {{ number_format($v->net_amount, 0) }}</td>
                    <td class="px-4 py-2.5 text-xs text-slate-400">{{ $v->visit_date->format('d M Y') }}</td>
                    <td class="px-4 py-2.5"><x-badge color="{{ ['completed'=>'green','waiting'=>'amber','cancelled'=>'red','in_progress'=>'blue'][$v->status] ?? 'slate' }}">{{ ucfirst(str_replace('_',' ',$v->status)) }}</x-badge></td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400 text-sm">No data for selected period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($visits) && method_exists($visits,'hasPages') && $visits->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $visits->links() }}</div>
    @endif
</div>
@endsection
