@extends('layouts.hms')
@section('title','Reports')
@section('breadcrumb')
    <span class="font-medium text-slate-700 dark:text-slate-200">Reports</span>
@endsection

@section('content')
<h1 class="text-xl font-bold text-slate-800 dark:text-white mb-6">Reports & Analytics</h1>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach([
        ['OPD Report', 'Daily OPD visits, doctor-wise summary, revenue breakdown', 'reports.opd', 'bg-blue-500', 'M16 4v12l-4-4-4 4V4'],
        ['IPD Report', 'Admissions, discharges, bed occupancy & billing', 'reports.ipd', 'bg-green-500', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['Pharmacy Report', 'Medicine sales, P&L, stock movements', 'reports.pharmacy', 'bg-purple-500', 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
        ['Laboratory Report', 'Test bookings, revenue by category, turnaround time', 'reports.laboratory', 'bg-cyan-500', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['Expense Report', 'Category-wise expenses, approval tracking', 'reports.expenses', 'bg-amber-500', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Profit & Loss', 'Revenue vs expenses, net profit by period', 'reports.profit-loss', 'bg-emerald-600', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ['Daily Closing', 'End-of-shift summary report for all departments', 'reports.daily-closing', 'bg-slate-600', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['Monthly Closing', 'Month-end consolidated financial report', 'reports.monthly-closing', 'bg-indigo-600', 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ] as [$title, $desc, $route, $color, $icon])
    <a href="{{ route($route) }}" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex gap-4 hover:border-primary-400 dark:hover:border-primary-500 hover:shadow-md transition-all group">
        <div class="w-10 h-10 {{ $color }} rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
        </div>
        <div>
            <p class="font-semibold text-slate-800 dark:text-white group-hover:text-primary-600">{{ $title }}</p>
            <p class="text-sm text-slate-400 mt-0.5">{{ $desc }}</p>
        </div>
    </a>
    @endforeach
</div>
@endsection
