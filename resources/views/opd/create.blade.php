@extends('layouts.hms')
@section('title','New OPD Visit')
@section('breadcrumb')
    <a href="{{ route('opd.index') }}" class="text-slate-400 hover:text-slate-600">OPD Visits</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">New Visit</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
<form method="POST" action="{{ route('opd.store') }}" class="space-y-4">
@csrf

{{-- Patient & Doctor --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Visit Information</h2>
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
            <label class="field-label">Doctor *</label>
            <select name="doctor_id" required class="field">
                <option value="">Select doctor…</option>
                @foreach($doctors as $d)
                    <option value="{{ $d->id }}" @selected(old('doctor_id', request('doctor_id'))==$d->id)>Dr. {{ $d->user->name }} — {{ $d->specialization }}</option>
                @endforeach
            </select>
            @error('doctor_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="field-label">Shift *</label>
            <select name="shift" required class="field">
                @foreach(['morning'=>'Morning','evening'=>'Evening','night'=>'Night'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('shift', $currentShift)===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Visit Date *</label>
            <input type="date" name="visit_date" value="{{ old('visit_date', today()->toDateString()) }}" required
                   class="field">
        </div>
        <div>
            <label class="field-label">Visit Type</label>
            <select name="visit_type" class="field">
                @foreach(['new'=>'New Patient','follow_up'=>'Follow Up','emergency'=>'Emergency'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('visit_type','new')===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        @if(request('token_id'))
        <input type="hidden" name="token_id" value="{{ request('token_id') }}">
        @endif
    </div>
</div>

{{-- Vitals --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Vitals <span class="text-slate-400 font-normal">(optional)</span></h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['weight_kg','Weight (kg)','number','0.1'],
            ['height_cm','Height (cm)','number','1'],
            ['temperature','Temp (°F)','number','0.1'],
            ['pulse_rate','Pulse (bpm)','number','1'],
        ] as [$n,$l,$t,$step])
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ $l }}</label>
            <input type="{{ $t }}" name="{{ $n }}" value="{{ old($n) }}" step="{{ $step }}"
                   class="field">
        </div>
        @endforeach
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Blood Pressure</label>
            <input type="text" name="blood_pressure" value="{{ old('blood_pressure') }}" placeholder="120/80"
                   class="field">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">O₂ Saturation (%)</label>
            <input type="number" name="oxygen_saturation" value="{{ old('oxygen_saturation') }}" min="0" max="100"
                   class="field">
        </div>
        <div class="col-span-2">
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Respiratory Rate</label>
            <input type="number" name="respiratory_rate" value="{{ old('respiratory_rate') }}"
                   class="field">
        </div>
    </div>
</div>

{{-- Symptoms & Diagnosis --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Clinical Notes</h2>
    <div class="space-y-4">
        <x-form.textarea name="symptoms" label="Chief Complaints / Symptoms" :value="old('symptoms')" rows="3" />
        <x-form.textarea name="diagnosis" label="Diagnosis" :value="old('diagnosis')" rows="3" />
        <x-form.textarea name="notes" label="Doctor Notes" :value="old('notes')" rows="2" />
    </div>
</div>

{{-- Payment --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Fee & Payment</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Consultation Fee *</label>
            <input type="number" name="consultation_fee" value="{{ old('consultation_fee', 500) }}" min="0" step="0.01" required
                   class="field">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Discount (₨)</label>
            <input type="number" name="discount_amount" value="{{ old('discount_amount', 0) }}" min="0" step="0.01"
                   class="field">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Payment Method</label>
            <select name="payment_method" class="field">
                @foreach(['cash'=>'Cash','card'=>'Card','bank_transfer'=>'Bank Transfer','insurance'=>'Insurance'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('payment_method','cash')===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Payment Status</label>
            <select name="payment_status" class="field">
                @foreach(['paid'=>'Paid','pending'=>'Pending','waived'=>'Waived'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('payment_status','paid')===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- Prescription --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6" x-data="prescription()">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">Prescription <span class="text-slate-400 font-normal">(optional)</span></h2>
        <button type="button" @click="addRow()" class="text-sm text-primary-600 hover:text-primary-700 font-medium">+ Add Medicine</button>
    </div>
    <div class="space-y-2" id="prescriptionRows">
        <template x-for="(row, i) in rows" :key="i">
        <div class="grid grid-cols-6 gap-2 items-end">
            <div class="col-span-2">
                <input type="text" :name="'medicines['+i+'][name]'" x-model="row.name" placeholder="Medicine name"
                       class="field">
            </div>
            <div>
                <input type="text" :name="'medicines['+i+'][dosage]'" x-model="row.dosage" placeholder="Dosage"
                       class="field">
            </div>
            <div>
                <input type="text" :name="'medicines['+i+'][frequency]'" x-model="row.frequency" placeholder="e.g. 3x daily"
                       class="field">
            </div>
            <div>
                <input type="text" :name="'medicines['+i+'][duration]'" x-model="row.duration" placeholder="e.g. 7 days"
                       class="field">
            </div>
            <button type="button" @click="rows.splice(i,1)" class="p-2 text-red-400 hover:text-red-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        </template>
        <template x-if="rows.length === 0">
            <p class="text-sm text-slate-400 py-2">No medicines added.</p>
        </template>
    </div>
    <x-form.textarea name="prescription_notes" label="Prescription Notes" :value="old('prescription_notes')" rows="2" class="mt-4" />
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Save Visit</button>
    <a href="{{ route('opd.index') }}" class="btn-cancel">Cancel</a>
</div>

</form>
</div>
@endsection

@push('scripts')
<script>
function prescription() {
    return {
        rows: [{name:'',dosage:'',frequency:'',duration:''}],
        addRow() { this.rows.push({name:'',dosage:'',frequency:'',duration:''}); }
    }
}
</script>
@endpush
