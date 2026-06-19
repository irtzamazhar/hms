@extends('layouts.hms')
@section('title','New IPD Admission')
@section('breadcrumb')
    <a href="{{ route('ipd.index') }}" class="text-slate-400 hover:text-slate-600">IPD</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">New Admission</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<form method="POST" action="{{ route('ipd.store') }}" class="space-y-4">
@csrf

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Admission Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="field-label">Patient *</label>
            <select name="patient_id" required class="field">
                <option value="">Select patient…</option>
                @foreach($patients as $p)
                    <option value="{{ $p->id }}" @selected(old('patient_id', request('patient_id'))==$p->id)>{{ $p->name }} ({{ $p->mr_number }})</option>
                @endforeach
            </select>
            @error('patient_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="field-label">Attending Doctor *</label>
            <select name="doctor_id" required class="field">
                <option value="">Select doctor…</option>
                @foreach($doctors as $d)
                    <option value="{{ $d->id }}" @selected(old('doctor_id')==$d->id)>Dr. {{ $d->user->name }} — {{ $d->specialization }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Ward *</label>
            <select name="ward_id" id="wardSelect" required class="field">
                <option value="">Select ward…</option>
                @foreach($wards as $w)
                    <option value="{{ $w->id }}" @selected(old('ward_id')==$w->id)>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Bed *</label>
            <select name="bed_id" required class="field">
                <option value="">Select bed…</option>
                @foreach($beds as $b)
                    <option value="{{ $b->id }}" @selected(old('bed_id')==$b->id)>{{ $b->bed_number }} — {{ $b->room->room_number ?? '' }} (₨{{ number_format($b->charges_per_day,0) }}/day)</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Admission Date *</label>
            <input type="datetime-local" name="admission_datetime" value="{{ old('admission_datetime', now()->format('Y-m-d\TH:i')) }}" required
                   class="field">
        </div>
        <div>
            <label class="field-label">Admission Type</label>
            <select name="admission_type" class="field">
                @foreach(['elective'=>'Elective','emergency'=>'Emergency','referral'=>'Referral'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('admission_type','elective')===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Clinical</h2>
    <div class="space-y-4">
        <x-form.textarea name="diagnosis" label="Admitting Diagnosis *" :value="old('diagnosis')" rows="3" required />
        <x-form.textarea name="notes" label="Notes" :value="old('notes')" rows="2" />
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Payment</h2>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="field-label">Advance Payment (₨)</label>
            <input type="number" name="advance_payment" value="{{ old('advance_payment', 0) }}" min="0" step="0.01"
                   class="field">
        </div>
        <div>
            <label class="field-label">Payment Method</label>
            <select name="payment_method" class="field">
                @foreach(['cash'=>'Cash','card'=>'Card','bank_transfer'=>'Bank Transfer','insurance'=>'Insurance'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('payment_method','cash')===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Admit Patient</button>
    <a href="{{ route('ipd.index') }}" class="btn-cancel">Cancel</a>
</div>

</form>
</div>
@endsection
