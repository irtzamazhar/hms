@extends('layouts.hms')
@section('title','Beds — '.$ward->name)
@section('breadcrumb')
    <a href="{{ route('wards.index') }}" class="text-slate-400 hover:text-slate-600">Wards</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">{{ $ward->name }} — Beds</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">{{ $ward->name }}</h1>
        <p class="text-sm text-slate-400 mt-0.5">{{ ucfirst($ward->ward_type) }} · Floor {{ $ward->floor ?? 'G' }} · {{ $ward->beds->count() }} beds</p>
    </div>
    <div class="flex gap-3 text-xs">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-green-500 inline-block"></span>Available</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-red-400 inline-block"></span>Occupied</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-amber-400 inline-block"></span>Maintenance</span>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    @php
    $available = $ward->beds->where('status','available')->count();
    $occupied  = $ward->beds->where('status','occupied')->count();
    $maintenance = $ward->beds->where('status','maintenance')->count();
    @endphp
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Available</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $available }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Occupied</p>
        <p class="text-2xl font-bold text-red-500 mt-1">{{ $occupied }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Maintenance</p>
        <p class="text-2xl font-bold text-amber-500 mt-1">{{ $maintenance }}</p>
    </div>
</div>

{{-- Beds grid --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-12 gap-2">
        @foreach($ward->beds->sortBy('bed_number') as $bed)
        <div title="Bed {{ $bed->bed_number }} — {{ ucfirst($bed->status) }}"
             class="aspect-square rounded-lg border-2 flex items-center justify-center text-xs font-bold cursor-default
             {{ $bed->status === 'available' ? 'border-green-400 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' :
                ($bed->status === 'occupied' ? 'border-red-400 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' :
                'border-amber-400 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400') }}">
            {{ $bed->bed_number }}
        </div>
        @endforeach
    </div>
</div>

{{-- Occupied beds detail --}}
@if($occupied > 0)
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden mt-4">
    <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700">
        <h2 class="font-semibold text-slate-800 dark:text-white">Occupied Beds</h2>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Bed</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Patient</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Admission Date</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Charge/Day</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($ward->beds->where('status','occupied') as $bed)
            <tr>
                <td class="px-4 py-3 font-mono font-bold text-red-600">{{ $bed->bed_number }}</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                    @if($bed->currentAdmission?->patient)
                    {{ $bed->currentAdmission->patient->name }}
                    @else
                    <span class="text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                    {{ $bed->currentAdmission?->admission_date?->format('d M Y') ?? '—' }}
                </td>
                <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">₨ {{ number_format($bed->charge_per_day, 0) }}/day</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
