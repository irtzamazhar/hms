@extends('layouts.hms')

@section('title', 'Dashboard')
@section('breadcrumb')
    <span class="text-slate-400 dark:text-slate-600">Home</span>
    <svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-600 dark:text-slate-300 font-medium">Dashboard</span>
@endsection

@section('content')

{{-- ── Page header ── --}}
<div class="mb-5 flex items-center justify-between">
    <div>
        <h1 class="text-lg font-bold text-slate-800 dark:text-white">Overview</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ now()->format('l, F j Y') }}</p>
    </div>
    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-white dark:bg-dark-800 border border-slate-200 dark:border-dark-700 rounded-lg px-3 py-1.5">
        <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
        Live data
    </div>
</div>

{{-- ── Stat Cards ── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">

    {{-- Total Patients --}}
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4 hover:shadow-card-hover transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl icon-gradient-blue flex items-center justify-center shadow-md">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800 dark:text-white leading-none mb-1">{{ number_format($summary['hospital']['total_patients']) }}</p>
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Total Patients</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Registered</p>
    </div>

    {{-- Today's OPD --}}
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4 hover:shadow-card-hover transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl icon-gradient-green flex items-center justify-center shadow-md">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800 dark:text-white leading-none mb-1">{{ $summary['hospital']['today_opd'] }}</p>
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Today's OPD</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Visits today</p>
    </div>

    {{-- Inpatients --}}
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4 hover:shadow-card-hover transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl icon-gradient-purple flex items-center justify-center shadow-md">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M12 3v18"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800 dark:text-white leading-none mb-1">{{ $summary['hospital']['current_ipd'] }}</p>
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Inpatients</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Currently admitted</p>
    </div>

    {{-- Today's Revenue --}}
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4 hover:shadow-card-hover transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl icon-gradient-amber flex items-center justify-center shadow-md">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800 dark:text-white leading-none mb-1">₨ {{ number_format($summary['hospital']['today_revenue'], 0) }}</p>
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Today's Revenue</p>
        <p class="text-[11px] text-slate-400 mt-0.5">OPD collections</p>
    </div>

    {{-- Stock Value --}}
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4 hover:shadow-card-hover transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl icon-gradient-teal flex items-center justify-center shadow-md">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800 dark:text-white leading-none mb-1">₨ {{ number_format($summary['pharmacy']['stock_value'], 0) }}</p>
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Stock Value</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Pharmacy</p>
    </div>

    {{-- Low Stock --}}
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4 hover:shadow-card-hover transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl icon-gradient-red flex items-center justify-center shadow-md">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            @if($summary['pharmacy']['low_stock_count'] > 0)
            <span class="text-[10px] bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-1.5 py-0.5 rounded-md font-semibold">Alert</span>
            @endif
        </div>
        <p class="text-2xl font-bold text-slate-800 dark:text-white leading-none mb-1">{{ $summary['pharmacy']['low_stock_count'] }}</p>
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Low Stock</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Medicines</p>
    </div>

    {{-- Today's Lab --}}
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4 hover:shadow-card-hover transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl icon-gradient-indigo flex items-center justify-center shadow-md">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v11.5A3.5 3.5 0 0012.5 18h0a3.5 3.5 0 003.5-3.5V3M9 3h6M9 7h6"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800 dark:text-white leading-none mb-1">{{ $summary['lab']['today_bookings'] }}</p>
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Today's Lab</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Bookings</p>
    </div>

    {{-- Pending Reports --}}
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4 hover:shadow-card-hover transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl icon-gradient-orange flex items-center justify-center shadow-md">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800 dark:text-white leading-none mb-1">{{ $summary['lab']['pending_reports'] }}</p>
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Pending Reports</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Lab results</p>
    </div>

</div>

{{-- ── Finance Bar ── --}}
@php
$profit = $summary['finance']['monthly_profit'];
@endphp
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-1.5 h-4 rounded-full bg-emerald-500"></div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Monthly Revenue</p>
        </div>
        <p class="text-xl font-bold text-emerald-500">₨ {{ number_format($summary['finance']['monthly_revenue'], 0) }}</p>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-1.5 h-4 rounded-full bg-red-500"></div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Monthly Expenses</p>
        </div>
        <p class="text-xl font-bold text-red-500">₨ {{ number_format($summary['finance']['monthly_expenses'], 0) }}</p>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-1.5 h-4 rounded-full bg-amber-500"></div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Salary Due</p>
        </div>
        <p class="text-xl font-bold text-amber-500">₨ {{ number_format($summary['finance']['salary_due'], 0) }}</p>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-1.5 h-4 rounded-full {{ $profit >= 0 ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Net Profit</p>
        </div>
        <p class="text-xl font-bold {{ $profit >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
            ₨ {{ number_format(abs($profit), 0) }}
            <span class="text-sm ml-1">{{ $profit >= 0 ? '▲' : '▼' }}</span>
        </p>
    </div>

</div>

{{-- ── Charts ── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
    <div class="lg:col-span-2 bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Revenue Trend</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Last 14 days</p>
            </div>
        </div>
        <canvas id="revenueChart" height="110"></canvas>
    </div>
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Patient Growth</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Last 6 months</p>
            </div>
        </div>
        <canvas id="growthChart" height="110"></canvas>
    </div>
</div>

{{-- ── Activity ── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

    {{-- OPD Visits --}}
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-dark-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-4 rounded-full icon-gradient-green" style="background: linear-gradient(135deg,#059669,#10b981)"></div>
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Today's OPD Visits</h3>
            </div>
            <a href="{{ route('opd.index') }}" class="text-xs text-primary-500 hover:text-primary-600 font-semibold flex items-center gap-1 transition-colors">
                View all
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        @forelse($activity['opd_visits'] as $v)
        <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 dark:border-dark-700/50 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr($v->patient->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $v->patient->name }}</p>
                    <p class="text-[11px] text-slate-400">{{ $v->patient->mr_number }} · Dr. {{ $v->doctor->user->name }}</p>
                </div>
            </div>
            <span class="text-[11px] px-2 py-0.5 rounded-md font-semibold
                @if($v->status === 'completed') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                @elseif($v->status === 'in_progress') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                @else bg-slate-100 text-slate-500 dark:bg-slate-700/50 dark:text-slate-400 @endif">
                {{ ucfirst(str_replace('_', ' ', $v->status)) }}
            </span>
        </div>
        @empty
        <div class="px-5 py-10 text-center">
            <svg class="w-8 h-8 mx-auto text-slate-200 dark:text-slate-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-sm text-slate-400">No OPD visits today</p>
        </div>
        @endforelse
    </div>

    <div class="space-y-3">
        {{-- Admissions --}}
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-dark-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-4 rounded-full" style="background: linear-gradient(135deg,#7c3aed,#a78bfa)"></div>
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Today's Admissions</h3>
                </div>
                <a href="{{ route('ipd.index') }}" class="text-xs text-primary-500 hover:text-primary-600 font-semibold flex items-center gap-1 transition-colors">
                    View all
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            @forelse($activity['admissions'] as $a)
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 dark:border-dark-700/50 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($a->patient->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $a->patient->name }}</p>
                        <p class="text-[11px] text-slate-400">{{ $a->admission_number }} · {{ $a->ward->name ?? '—' }}</p>
                    </div>
                </div>
                <span class="text-[11px] text-slate-400 font-medium">{{ $a->admission_datetime->format('h:i A') }}</span>
            </div>
            @empty
            <div class="px-5 py-6 text-center">
                <p class="text-sm text-slate-400">No admissions today</p>
            </div>
            @endforelse
        </div>

        {{-- Lab Bookings --}}
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-dark-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-4 rounded-full" style="background: linear-gradient(135deg,#4338ca,#818cf8)"></div>
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Today's Lab Bookings</h3>
                </div>
                <a href="{{ route('lab.index') }}" class="text-xs text-primary-500 hover:text-primary-600 font-semibold flex items-center gap-1 transition-colors">
                    View all
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            @forelse($activity['lab_bookings'] as $lb)
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 dark:border-dark-700/50 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($lb->patient->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $lb->patient->name }}</p>
                        <p class="text-[11px] text-slate-400">{{ $lb->booking_number }}</p>
                    </div>
                </div>
                <span class="text-[11px] px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 font-semibold">
                    {{ ucfirst(str_replace('_', ' ', $lb->status)) }}
                </span>
            </div>
            @empty
            <div class="px-5 py-6 text-center">
                <p class="text-sm text-slate-400">No lab bookings today</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const isDark = document.documentElement.classList.contains('dark');
    const tickColor  = isDark ? '#64748b' : '#94a3b8';
    const gridColor  = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
    const tooltipBg  = isDark ? '#1e293b' : '#ffffff';
    const tooltipBorder = isDark ? '#334155' : '#e2e8f0';

    const commonTooltip = {
        backgroundColor: tooltipBg,
        borderColor: tooltipBorder,
        borderWidth: 1,
        titleColor: isDark ? '#e2e8f0' : '#1e293b',
        bodyColor: isDark ? '#94a3b8' : '#64748b',
        padding: 10,
        cornerRadius: 8,
    };

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: @json($revenue['labels']),
            datasets: [
                {
                    label: 'OPD',
                    data: @json($revenue['opd']),
                    borderColor: '#3b82f6',
                    backgroundColor: isDark ? 'rgba(59,130,246,0.1)' : 'rgba(59,130,246,0.08)',
                    tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 5,
                    pointBackgroundColor: '#3b82f6', borderWidth: 2,
                },
                {
                    label: 'Pharmacy',
                    data: @json($revenue['pharmacy']),
                    borderColor: '#10b981',
                    backgroundColor: isDark ? 'rgba(16,185,129,0.1)' : 'rgba(16,185,129,0.08)',
                    tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 5,
                    pointBackgroundColor: '#10b981', borderWidth: 2,
                },
                {
                    label: 'Lab',
                    data: @json($revenue['lab']),
                    borderColor: '#8b5cf6',
                    backgroundColor: isDark ? 'rgba(139,92,246,0.1)' : 'rgba(139,92,246,0.08)',
                    tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 5,
                    pointBackgroundColor: '#8b5cf6', borderWidth: 2,
                },
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { color: tickColor, boxWidth: 10, usePointStyle: true, pointStyle: 'circle', font: { size: 11 } } },
                tooltip: commonTooltip,
            },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 } }, border: { display: false } },
                y: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 }, callback: v => '₨' + v.toLocaleString() }, border: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('growthChart'), {
        type: 'bar',
        data: {
            labels: @json($growth['labels']),
            datasets: [{
                label: 'New Patients',
                data: @json($growth['counts']),
                backgroundColor: isDark ? 'rgba(59,130,246,0.6)' : 'rgba(59,130,246,0.75)',
                hoverBackgroundColor: '#3b82f6',
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: commonTooltip,
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 } }, border: { display: false } },
                y: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 } }, border: { display: false } }
            }
        }
    });
})();
</script>
@endpush
