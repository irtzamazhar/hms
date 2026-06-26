@extends('layouts.hms')
@section('title','IPD — '.$admission->admission_number)
@section('breadcrumb')
    <a href="{{ route('ipd.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">IPD</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $admission->admission_number }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    {{-- Header --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs text-slate-400 font-mono">{{ $admission->admission_number }}</p>
            <h1 class="text-lg font-bold text-slate-800 dark:text-white mt-0.5">{{ $admission->patient->name }}</h1>
            <p class="text-sm text-slate-400">{{ $admission->patient->mr_number }} · Dr. {{ $admission->doctor->user->name }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('ipd.invoice',$admission) }}" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">Invoice</a>
            @if($admission->status === 'admitted')
                @can('edit ipd')
                <a href="{{ route('ipd.edit',$admission) }}" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">Edit</a>
                <form method="POST" action="{{ route('ipd.discharge',$admission) }}" onsubmit="return confirm('Discharge this patient?')">
                    @csrf @method('PATCH')
                    <input type="hidden" name="discharge_datetime" value="{{ now()->format('Y-m-d H:i:s') }}">
                    <button class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm rounded-lg">Discharge</button>
                </form>
                @endcan
            @else
                <x-badge color="{{ ['discharged'=>'green','transferred'=>'amber','absconded'=>'red'][$admission->status] ?? 'slate' }}">
                    {{ ucfirst($admission->status) }}
                </x-badge>
            @endif
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        {{-- Admission Details --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
            <div class="px-5 py-3 font-semibold text-xs text-slate-500 uppercase tracking-wide">Admission</div>
            @foreach([
                ['Type', ucfirst($admission->admission_type)],
                ['Ward', $admission->ward->name ?? '—'],
                ['Bed', $admission->bed->bed_number ?? '—'],
                ['Admitted', $admission->admission_datetime->format('d M Y H:i')],
                ['Discharged', $admission->discharge_datetime?->format('d M Y H:i') ?? '—'],
                ['Days', $admission->admission_datetime->diffInDays($admission->discharge_datetime ?? now()).' day(s)'],
            ] as [$l,$v])
            <div class="px-5 py-2.5 flex justify-between">
                <span class="text-sm text-slate-400">{{ $l }}</span>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $v }}</span>
            </div>
            @endforeach
        </div>

        {{-- Financial --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
            <div class="px-5 py-3 font-semibold text-xs text-slate-500 uppercase tracking-wide">Financials</div>
            @foreach([
                ['Bed Charges', '₨ '.number_format($charges['bed_charge'] ?? 0, 0)],
                ['Treatment Charges', '₨ '.number_format(($admission->doctor_charges ?? 0) + ($admission->nursing_charges ?? 0) + ($admission->medicine_charges ?? 0) + ($admission->lab_charges ?? 0), 0)],
                ['Other Charges', '₨ '.number_format($admission->other_charges ?? 0, 0)],
                ['Discount', '— ₨ '.number_format($admission->discount ?? 0, 0)],
                ['Net Amount', '₨ '.number_format($charges['net'] ?? 0, 0)],
                ['Advance Paid', '₨ '.number_format($admission->paid_amount ?? 0, 0)],
                ['Balance', '₨ '.number_format(($charges['net'] ?? 0) - ($admission->paid_amount ?? 0), 0)],
            ] as [$l,$v])
            <div class="px-5 py-2.5 flex justify-between">
                <span class="text-sm text-slate-400">{{ $l }}</span>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200 {{ $l==='Balance' ? 'text-red-500 dark:text-red-400' : '' }}">{{ $v }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Diagnosis & Notes --}}
    @if($admission->diagnosis || $admission->notes)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
        @if($admission->diagnosis)
        <div class="px-5 py-4"><p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Diagnosis</p><p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $admission->diagnosis }}</p></div>
        @endif
        @if($admission->notes)
        <div class="px-5 py-4"><p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Notes</p><p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $admission->notes }}</p></div>
        @endif
        @if($admission->discharge_summary)
        <div class="px-5 py-4"><p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Discharge Summary</p><p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $admission->discharge_summary }}</p></div>
        @endif
    </div>
    @endif

    {{-- Treatment notes --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <h3 class="font-semibold text-sm text-slate-700 dark:text-white">Treatment Notes</h3>
            @if($admission->status === 'admitted')
            @can('edit ipd')
            <button onclick="document.getElementById('addTreatmentForm').classList.toggle('hidden')"
                    class="text-sm text-primary-600 hover:text-primary-700 font-medium">+ Add Note</button>
            @endcan
            @endif
        </div>

        {{-- Add treatment form (hidden by default) --}}
        <div id="addTreatmentForm" class="hidden border-b border-slate-200 dark:border-slate-700 p-4">
            <form method="POST" action="{{ route('ipd.treatment.add',$admission) }}">
                @csrf
                <textarea name="treatment_notes" rows="2" placeholder="Treatment notes…" required
                          class="field mb-3"></textarea>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Add</button>
            </form>
        </div>

        @forelse($admission->treatments as $t)
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700 last:border-0">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-700 dark:text-white whitespace-pre-line">{{ $t->treatment_notes }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $t->treatment_datetime?->format('d M Y H:i') ?? $t->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-slate-400 text-sm">No treatment notes yet.</div>
        @endforelse
    </div>

</div>
@endsection
