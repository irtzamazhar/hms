@extends('layouts.hms')
@section('title','IPD Invoice — '.$admission->admission_number)
@section('breadcrumb')
    <a href="{{ route('ipd.show',$admission) }}" class="text-slate-400 hover:text-slate-600">{{ $admission->admission_number }}</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Invoice</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-end gap-2 mb-4">
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">🖨 Print</button>
        <a href="{{ route('ipd.show',$admission) }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm rounded-lg">Back</a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-8 print:border-0 print:shadow-none">
        {{-- Header --}}
        <div class="flex justify-between items-start mb-8">
            <div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white">{{ $setting->hospital_name }}</h1>
                <p class="text-sm text-slate-400 mt-1">{{ $setting->address }}</p>
                <p class="text-sm text-slate-400">{{ $setting->phone }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">IPD Discharge Bill</p>
                <p class="text-xl font-bold text-primary-600 mt-1">{{ $admission->admission_number }}</p>
                <p class="text-xs text-slate-400">{{ now()->format('d M Y') }}</p>
            </div>
        </div>

        {{-- Patient Info --}}
        <div class="grid grid-cols-2 gap-6 mb-8">
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold tracking-wide mb-2">Patient</p>
                <p class="font-bold text-slate-800 dark:text-white">{{ $admission->patient->name }}</p>
                <p class="text-sm text-slate-500">MR: {{ $admission->patient->mr_number }}</p>
                <p class="text-sm text-slate-500">{{ $admission->patient->phone ?? '' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold tracking-wide mb-2">Admission</p>
                <p class="text-sm text-slate-700 dark:text-slate-200">Ward: {{ $admission->ward->name ?? '—' }}, Bed: {{ $admission->bed->bed_number ?? '—' }}</p>
                <p class="text-sm text-slate-500">In: {{ $admission->admission_date->format('d M Y') }}</p>
                <p class="text-sm text-slate-500">Out: {{ $admission->discharge_date?->format('d M Y') ?? 'Still Admitted' }}</p>
                <p class="text-sm font-semibold text-slate-700 dark:text-white">Dr. {{ $admission->doctor->user->name }}</p>
            </div>
        </div>

        {{-- Charges --}}
        <table class="w-full text-sm mb-6">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50">
                    <th class="px-4 py-2.5 text-left text-xs text-slate-500 font-semibold uppercase">Description</th>
                    <th class="px-4 py-2.5 text-right text-xs text-slate-500 font-semibold uppercase">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <tr>
                    <td class="px-4 py-3">Bed Charges ({{ $admission->admission_date->diffInDays($admission->discharge_date ?? now()) }} days × ₨{{ number_format($admission->bed->charges_per_day ?? 0,0) }})</td>
                    <td class="px-4 py-3 text-right">₨ {{ number_format($admission->total_bed_charges ?? 0, 2) }}</td>
                </tr>
                @if(($admission->total_treatment_charges ?? 0) > 0)
                <tr>
                    <td class="px-4 py-3">Treatment & Procedures</td>
                    <td class="px-4 py-3 text-right">₨ {{ number_format($admission->total_treatment_charges ?? 0, 2) }}</td>
                </tr>
                @endif
                @if(($admission->other_charges ?? 0) > 0)
                <tr>
                    <td class="px-4 py-3">Other Charges</td>
                    <td class="px-4 py-3 text-right">₨ {{ number_format($admission->other_charges ?? 0, 2) }}</td>
                </tr>
                @endif
                @if(($admission->discount_amount ?? 0) > 0)
                <tr>
                    <td class="px-4 py-3 text-red-500">Discount</td>
                    <td class="px-4 py-3 text-right text-red-500">— ₨ {{ number_format($admission->discount_amount ?? 0, 2) }}</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-200 dark:border-slate-600">
                    <td class="px-4 py-3 font-bold">Total</td>
                    <td class="px-4 py-3 text-right font-bold text-lg">₨ {{ number_format($admission->net_amount ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-slate-400">Advance Paid</td>
                    <td class="px-4 py-2 text-right text-slate-400">— ₨ {{ number_format($admission->advance_payment ?? 0, 2) }}</td>
                </tr>
                <tr class="border-t border-slate-200 dark:border-slate-600">
                    <td class="px-4 py-3 font-bold text-red-600">Balance Due</td>
                    <td class="px-4 py-3 text-right font-bold text-red-600">₨ {{ number_format(($admission->net_amount ?? 0) - ($admission->advance_payment ?? 0), 2) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Treatment summary --}}
        @if($admission->treatments->count())
        <div class="border-t border-slate-200 dark:border-slate-700 pt-4 mb-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Treatment Summary</p>
            @foreach($admission->treatments as $t)
            <p class="text-sm text-slate-600 dark:text-slate-300">• {{ $t->description }} <span class="text-slate-400">({{ $t->created_at->format('d M') }})</span></p>
            @endforeach
        </div>
        @endif

        <div class="text-center text-xs text-slate-400 border-t border-slate-200 dark:border-slate-700 pt-4">
            {{ $setting->hospital_name }} · {{ now()->format('d M Y H:i') }} · Computer generated invoice
        </div>
    </div>
</div>
@endsection
