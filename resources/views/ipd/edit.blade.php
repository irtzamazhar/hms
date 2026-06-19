@extends('layouts.hms')
@section('title','Edit Admission')
@section('breadcrumb')
    <a href="{{ route('ipd.index') }}" class="text-slate-400 hover:text-slate-600">IPD</a> <span class="mx-1">/</span>
    <a href="{{ route('ipd.show',$admission) }}" class="text-slate-400 hover:text-slate-600">{{ $admission->admission_number }}</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Edit</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<form method="POST" action="{{ route('ipd.update',$admission) }}" class="space-y-4">
@csrf @method('PUT')

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Admission Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="field-label">Patient</label>
            <p class="text-sm text-slate-600 dark:text-slate-300 px-3 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-lg">{{ $admission->patient->name }}</p>
        </div>
        <div>
            <label class="field-label">Doctor *</label>
            <select name="doctor_id" required class="field">
                @foreach($doctors as $d)
                    <option value="{{ $d->id }}" @selected(old('doctor_id',$admission->doctor_id)==$d->id)>Dr. {{ $d->user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Ward</label>
            <select name="ward_id" class="field">
                @foreach($wards as $w)
                    <option value="{{ $w->id }}" @selected(old('ward_id',$admission->ward_id)==$w->id)>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Status</label>
            <select name="status" class="field">
                @foreach(['admitted','transferred','absconded'] as $s)
                    <option value="{{ $s }}" @selected(old('status',$admission->status)===$s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Other Charges (₨)</label>
            <input type="number" name="other_charges" value="{{ old('other_charges',$admission->other_charges) }}" min="0" step="0.01"
                   class="field">
        </div>
        <div>
            <label class="field-label">Discount (₨)</label>
            <input type="number" name="discount_amount" value="{{ old('discount_amount',$admission->discount_amount) }}" min="0" step="0.01"
                   class="field">
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Clinical</h2>
    <div class="space-y-4">
        <x-form.textarea name="diagnosis" label="Diagnosis" :value="old('diagnosis',$admission->diagnosis)" rows="3" />
        <x-form.textarea name="notes" label="Notes" :value="old('notes',$admission->notes)" rows="2" />
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Update Admission</button>
    <a href="{{ route('ipd.show',$admission) }}" class="btn-cancel">Cancel</a>
</div>

</form>
</div>
@endsection
