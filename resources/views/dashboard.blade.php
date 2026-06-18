@extends('layouts.hms')

@section('title', 'Dashboard')
@section('breadcrumb')
    <span class="text-slate-400">Home</span> <span class="mx-1">/</span>
    <span class="text-slate-700 dark:text-slate-200 font-medium">Dashboard</span>
@endsection

@section('content')

{{-- ── Summary Cards ── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @php
    $cards = [
        ['label' => 'Total Patients',    'value' => number_format($summary['hospital']['total_patients']),          'icon' => '👥', 'bg' => 'blue',   'sub' => 'Registered'],
        ['label' => "Today's OPD",        'value' => $summary['hospital']['today_opd'],                              'icon' => '📋', 'bg' => 'green',  'sub' => 'Visits today'],
        ['label' => 'Inpatients',         'value' => $summary['hospital']['current_ipd'],                            'icon' => '🛏️', 'bg' => 'purple', 'sub' => 'Currently admitted'],
        ['label' => "Today's Revenue",    'value' => '₨ ' . number_format($summary['hospital']['today_revenue'], 0), 'icon' => '💰', 'bg' => 'amber',  'sub' => 'OPD collections'],
        ['label' => 'Stock Value',        'value' => '₨ ' . number_format($summary['pharmacy']['stock_value'], 0),  'icon' => '💊', 'bg' => 'teal',   'sub' => 'Pharmacy'],
        ['label' => 'Low Stock',          'value' => $summary['pharmacy']['low_stock_count'],                        'icon' => '⚠️', 'bg' => 'red',    'sub' => 'Medicines'],
        ['label' => "Today's Lab",        'value' => $summary['lab']['today_bookings'],                              'icon' => '🧪', 'bg' => 'indigo', 'sub' => 'Bookings'],
        ['label' => 'Pending Reports',    'value' => $summary['lab']['pending_reports'],                             'icon' => '⏳', 'bg' => 'orange', 'sub' => 'Lab results'],
    ];
    $colors = [
        'blue'   => 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        'green'  => 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400',
        'purple' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
        'amber'  => 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
        'teal'   => 'bg-teal-50 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400',
        'red'    => 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400',
        'indigo' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
        'orange' => 'bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
    ];
    @endphp

    @foreach($cards as $c)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0 {{ $colors[$c['bg']] }}">
            {{ $c['icon'] }}
        </div>
        <div class="min-w-0">
            <p class="text-xl font-bold text-slate-800 dark:text-white leading-tight truncate">{{ $c['value'] }}</p>
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 truncate">{{ $c['label'] }}</p>
            <p class="text-xs text-slate-400 truncate">{{ $c['sub'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Finance Bar ── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @php
    $profit = $summary['finance']['monthly_profit'];
    $finCards = [
        ['label' => 'Monthly Revenue',  'value' => '₨ ' . number_format($summary['finance']['monthly_revenue'], 0), 'class' => 'text-green-600'],
        ['label' => 'Monthly Expenses', 'value' => '₨ ' . number_format($summary['finance']['monthly_expenses'], 0), 'class' => 'text-red-500'],
        ['label' => 'Salary Due',       'value' => '₨ ' . number_format($summary['finance']['salary_due'], 0),       'class' => 'text-amber-500'],
        ['label' => 'Net Profit',       'value' => '₨ ' . number_format(abs($profit), 0) . ($profit >= 0 ? ' ▲' : ' ▼'), 'class' => $profit >= 0 ? 'text-green-600' : 'text-red-500'],
    ];
    @endphp

    @foreach($finCards as $fc)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-1">{{ $fc['label'] }}</p>
        <p class="text-lg font-bold {{ $fc['class'] }}">{{ $fc['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── Charts ── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-white mb-4">Revenue Trend (Last 14 Days)</h3>
        <canvas id="revenueChart" height="100"></canvas>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-white mb-4">Patient Growth (6 Months)</h3>
        <canvas id="growthChart" height="100"></canvas>
    </div>
</div>

{{-- ── Activity ── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-white">Today's OPD Visits</h3>
            <a href="{{ route('opd.index') }}" class="text-xs text-primary-600 hover:underline font-medium">View all →</a>
        </div>
        @forelse($activity['opd_visits'] as $v)
        <div class="px-5 py-3 flex items-center justify-between border-b border-slate-100 dark:border-slate-700 last:border-0">
            <div>
                <p class="text-sm font-medium text-slate-700 dark:text-white">{{ $v->patient->name }}</p>
                <p class="text-xs text-slate-400">{{ $v->patient->mr_number }} · Dr. {{ $v->doctor->user->name }}</p>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                @if($v->status === 'completed') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                @elseif($v->status === 'in_progress') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                @else bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400 @endif">
                {{ ucfirst(str_replace('_', ' ', $v->status)) }}
            </span>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-sm text-slate-400">No OPD visits today.</div>
        @endforelse
    </div>

    <div class="space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-white">Today's Admissions</h3>
                <a href="{{ route('ipd.index') }}" class="text-xs text-primary-600 hover:underline font-medium">View all →</a>
            </div>
            @forelse($activity['admissions'] as $a)
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-100 dark:border-slate-700 last:border-0">
                <div>
                    <p class="text-sm font-medium text-slate-700 dark:text-white">{{ $a->patient->name }}</p>
                    <p class="text-xs text-slate-400">{{ $a->admission_number }} · {{ $a->ward->name ?? '—' }}</p>
                </div>
                <span class="text-xs text-slate-500">{{ $a->admission_datetime->format('h:i A') }}</span>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-sm text-slate-400">No admissions today.</div>
            @endforelse
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-white">Today's Lab Bookings</h3>
                <a href="{{ route('lab.index') }}" class="text-xs text-primary-600 hover:underline font-medium">View all →</a>
            </div>
            @forelse($activity['lab_bookings'] as $lb)
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-100 dark:border-slate-700 last:border-0">
                <div>
                    <p class="text-sm font-medium text-slate-700 dark:text-white">{{ $lb->patient->name }}</p>
                    <p class="text-xs text-slate-400">{{ $lb->booking_number }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 font-medium">
                    {{ ucfirst(str_replace('_', ' ', $lb->status)) }}
                </span>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-sm text-slate-400">No lab bookings today.</div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const tColor = '#64748b';
const gColor = 'rgba(0,0,0,0.06)';

new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: @json($revenue['labels']),
        datasets: [
            { label: 'OPD',      data: @json($revenue['opd']),      borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.08)',  tension: 0.4, fill: true, pointRadius: 3 },
            { label: 'Pharmacy', data: @json($revenue['pharmacy']),  borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', tension: 0.4, fill: true, pointRadius: 3 },
            { label: 'Lab',      data: @json($revenue['lab']),       borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.08)', tension: 0.4, fill: true, pointRadius: 3 },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: tColor, boxWidth: 10, font: { size: 11 } } } },
        scales: {
            x: { grid: { color: gColor }, ticks: { color: tColor, font: { size: 10 } } },
            y: { grid: { color: gColor }, ticks: { color: tColor, font: { size: 10 }, callback: v => '₨' + v.toLocaleString() } }
        }
    }
});

new Chart(document.getElementById('growthChart'), {
    type: 'bar',
    data: {
        labels: @json($growth['labels']),
        datasets: [{ label: 'New Patients', data: @json($growth['counts']), backgroundColor: 'rgba(59,130,246,0.7)', borderRadius: 5 }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: tColor, font: { size: 10 } } },
            y: { grid: { color: gColor }, ticks: { color: tColor, font: { size: 10 } } }
        }
    }
});
</script>
@endpush
