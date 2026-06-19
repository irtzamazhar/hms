@extends('layouts.hms')
@section('title','OPD Invoice — '.$visit->visit_number)
@section('breadcrumb')
    <a href="{{ route('opd.show',$visit) }}" class="text-slate-400 hover:text-slate-600">{{ $visit->visit_number }}</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Invoice</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-end gap-2 mb-4">
        <a href="{{ route('opd.print',$visit) }}" target="_blank" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">
            🖨 Print / PDF
        </a>
        <a href="{{ route('opd.show',$visit) }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm rounded-lg">Back</a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-8" id="invoice">
        {{-- Header --}}
        <div class="flex justify-between items-start mb-8">
            <div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white">{{ $setting->hospital_name }}</h1>
                <p class="text-sm text-slate-400 mt-1">{{ $setting->address }}</p>
                <p class="text-sm text-slate-400">{{ $setting->phone }} · {{ $setting->email }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">OPD Invoice</p>
                <p class="text-xl font-bold text-primary-600 mt-1">{{ $visit->visit_number }}</p>
                <p class="text-xs text-slate-400">{{ $visit->visit_date->format('d M Y') }}</p>
            </div>
        </div>

        {{-- Patient & Doctor --}}
        <div class="grid grid-cols-2 gap-6 mb-8">
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold tracking-wide mb-2">Patient</p>
                <p class="font-bold text-slate-800 dark:text-white">{{ $visit->patient->name }}</p>
                <p class="text-sm text-slate-500">MR: {{ $visit->patient->mr_number }}</p>
                <p class="text-sm text-slate-500">{{ $visit->patient->phone ?? '' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold tracking-wide mb-2">Attending Doctor</p>
                <p class="font-bold text-slate-800 dark:text-white">Dr. {{ $visit->doctor->user->name }}</p>
                <p class="text-sm text-slate-500">{{ $visit->doctor->specialization }}</p>
                <p class="text-sm text-slate-500">{{ ucfirst($visit->shift) }} Shift</p>
            </div>
        </div>

        {{-- Fee table --}}
        <table class="w-full text-sm mb-6">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50">
                    <th class="px-4 py-2.5 text-left text-xs text-slate-500 font-semibold uppercase">Description</th>
                    <th class="px-4 py-2.5 text-right text-xs text-slate-500 font-semibold uppercase">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <tr>
                    <td class="px-4 py-3">Consultation Fee <span class="text-slate-400">({{ ucfirst(str_replace('_',' ',$visit->visit_type)) }})</span></td>
                    <td class="px-4 py-3 text-right">₨ {{ number_format($visit->consultation_fee, 2) }}</td>
                </tr>
                @if($visit->discount_amount > 0)
                <tr>
                    <td class="px-4 py-3 text-red-500">Discount</td>
                    <td class="px-4 py-3 text-right text-red-500">— ₨ {{ number_format($visit->discount_amount, 2) }}</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-200 dark:border-slate-600">
                    <td class="px-4 py-3 font-bold text-slate-800 dark:text-white">Total</td>
                    <td class="px-4 py-3 text-right font-bold text-lg text-slate-800 dark:text-white">₨ {{ number_format($visit->net_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Payment status --}}
        <div class="flex items-center justify-between py-3 px-4 rounded-lg {{ $visit->payment_status === 'paid' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-amber-50 dark:bg-amber-900/20' }}">
            <div>
                <p class="text-xs text-slate-400">Payment Method</p>
                <p class="font-medium text-slate-700 dark:text-white">{{ ucfirst(str_replace('_',' ',$visit->payment_method)) }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-400">Status</p>
                <p class="font-bold text-lg {{ $visit->payment_status === 'paid' ? 'text-green-600' : 'text-amber-600' }}">
                    {{ strtoupper($visit->payment_status) }}
                </p>
            </div>
        </div>

        {{-- Prescription summary --}}
        @if($visit->prescription && $visit->prescription->items->count())
        <div class="mt-6 border-t border-slate-200 dark:border-slate-700 pt-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Prescription</p>
            @foreach($visit->prescription->items as $item)
            <p class="text-sm text-slate-600 dark:text-slate-300">• {{ $item->medicine_name }} — {{ $item->dosage }}, {{ $item->frequency }}, {{ $item->duration }}</p>
            @endforeach
        </div>
        @endif

        <div class="mt-8 text-center text-xs text-slate-400 border-t border-slate-200 dark:border-slate-700 pt-4">
            Generated by {{ $setting->hospital_name }} HMS · {{ now()->format('d M Y H:i') }}
        </div>
    </div>
</div>
@endsection
