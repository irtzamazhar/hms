@extends('layouts.hms')
@section('title','OPD Visit — '.$visit->visit_number)
@section('breadcrumb')
    <a href="{{ route('opd.index') }}" class="text-slate-400 hover:text-slate-600">OPD Visits</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $visit->visit_number }}</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    {{-- Header card --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs text-slate-400 font-mono">{{ $visit->visit_number }}</p>
            <h1 class="text-lg font-bold text-slate-800 dark:text-white mt-0.5">{{ $visit->patient->name }}</h1>
            <p class="text-sm text-slate-400">{{ $visit->patient->mr_number }} · {{ $visit->patient->age }} yrs · {{ ucfirst($visit->patient->gender) }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('opd.invoice', $visit) }}" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">Invoice</a>
            @can('edit opd')
            <a href="{{ route('opd.edit', $visit) }}" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">Edit</a>
            @endcan
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        {{-- Visit Details --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
            <div class="px-5 py-3 flex justify-between">
                <span class="text-sm text-slate-400">Doctor</span>
                <span class="text-sm font-medium text-slate-700 dark:text-white">Dr. {{ $visit->doctor->user->name }}</span>
            </div>
            <div class="px-5 py-3 flex justify-between">
                <span class="text-sm text-slate-400">Date</span>
                <span class="text-sm font-medium text-slate-700 dark:text-white">{{ $visit->visit_date->format('d M Y') }}</span>
            </div>
            <div class="px-5 py-3 flex justify-between">
                <span class="text-sm text-slate-400">Shift</span>
                <x-badge color="{{ ['morning'=>'amber','evening'=>'blue','night'=>'purple'][$visit->shift] ?? 'slate' }}">{{ ucfirst($visit->shift) }}</x-badge>
            </div>
            <div class="px-5 py-3 flex justify-between">
                <span class="text-sm text-slate-400">Type</span>
                <span class="text-sm font-medium text-slate-700 dark:text-white">{{ ucfirst(str_replace('_',' ',$visit->visit_type)) }}</span>
            </div>
            <div class="px-5 py-3 flex justify-between">
                <span class="text-sm text-slate-400">Status</span>
                <x-badge color="{{ ['completed'=>'green','in_progress'=>'blue','waiting'=>'amber','cancelled'=>'red'][$visit->status] ?? 'slate' }}">
                    {{ ucfirst(str_replace('_',' ',$visit->status)) }}
                </x-badge>
            </div>
        </div>

        {{-- Vitals --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
            <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-3">Vitals</h3>
            <div class="grid grid-cols-2 gap-3">
                @foreach([
                    ['Weight', $visit->weight_kg ? $visit->weight_kg.' kg' : '—'],
                    ['Height', $visit->height_cm ? $visit->height_cm.' cm' : '—'],
                    ['Temperature', $visit->temperature ? $visit->temperature.'°F' : '—'],
                    ['Pulse', $visit->pulse_rate ? $visit->pulse_rate.' bpm' : '—'],
                    ['BP', $visit->blood_pressure ?: '—'],
                    ['SpO₂', $visit->oxygen_saturation ? $visit->oxygen_saturation.'%' : '—'],
                ] as [$label, $val])
                <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg px-3 py-2">
                    <p class="text-xs text-slate-400">{{ $label }}</p>
                    <p class="text-sm font-semibold text-slate-700 dark:text-white">{{ $val }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Clinical Notes --}}
    @if($visit->symptoms || $visit->diagnosis || $visit->notes)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
        @if($visit->symptoms)
        <div class="px-5 py-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Symptoms</p>
            <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $visit->symptoms }}</p>
        </div>
        @endif
        @if($visit->diagnosis)
        <div class="px-5 py-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Diagnosis</p>
            <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $visit->diagnosis }}</p>
        </div>
        @endif
        @if($visit->notes)
        <div class="px-5 py-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Doctor Notes</p>
            <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $visit->notes }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Prescription --}}
    @php $prescription = $visit->prescriptions->first(); @endphp
    @if($prescription)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-white">Prescription</h3>
            <a href="{{ route('opd.print', $visit) }}" target="_blank" class="text-xs text-primary-600 hover:underline">Print</a>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50">
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Medicine</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Dosage</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Frequency</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Duration</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($prescription->items as $item)
                <tr>
                    <td class="px-4 py-2 font-medium text-slate-700 dark:text-white">{{ $item->medicine_name }}</td>
                    <td class="px-4 py-2 text-slate-600 dark:text-slate-300">{{ $item->dosage }}</td>
                    <td class="px-4 py-2 text-slate-600 dark:text-slate-300">{{ $item->frequency }}</td>
                    <td class="px-4 py-2 text-slate-600 dark:text-slate-300">{{ $item->duration }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($prescription->notes)
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">
            <p class="text-xs text-slate-400">Notes: {{ $prescription->notes }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Payment --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-white mb-3">Payment</h3>
        <div class="flex flex-wrap gap-6">
            <div><p class="text-xs text-slate-400">Consultation Fee</p><p class="font-semibold text-slate-700 dark:text-white">₨ {{ number_format($visit->consultation_fee,0) }}</p></div>
            <div><p class="text-xs text-slate-400">Discount</p><p class="font-semibold text-red-500">— ₨ {{ number_format($visit->discount_amount,0) }}</p></div>
            <div><p class="text-xs text-slate-400">Net Amount</p><p class="font-semibold text-lg text-green-600">₨ {{ number_format($visit->net_amount,0) }}</p></div>
            <div><p class="text-xs text-slate-400">Method</p><p class="font-semibold text-slate-700 dark:text-white">{{ ucfirst(str_replace('_',' ',$visit->payment_method)) }}</p></div>
            <div>
                <p class="text-xs text-slate-400">Status</p>
                <x-badge color="{{ ['paid'=>'green','pending'=>'amber','partial'=>'blue','waived'=>'slate'][$visit->payment_status] ?? 'slate' }}">{{ ucfirst($visit->payment_status) }}</x-badge>
            </div>
        </div>
    </div>

</div>
@endsection
