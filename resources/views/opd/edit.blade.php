@extends('layouts.hms')
@section('title','Edit OPD Visit')
@section('breadcrumb')
    <a href="{{ route('opd.index') }}" class="text-slate-400 hover:text-slate-600">OPD Visits</a> <span class="mx-1">/</span>
    <a href="{{ route('opd.show',$visit) }}" class="text-slate-400 hover:text-slate-600">{{ $visit->visit_number }}</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Edit</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
<form method="POST" action="{{ route('opd.update', $visit) }}" class="space-y-4">
@csrf @method('PUT')

{{-- Visit Info --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Visit Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Patient</label>
            <p class="text-sm text-slate-600 dark:text-slate-300 px-3 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-lg">{{ $visit->patient->name }} ({{ $visit->patient->mr_number }})</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Doctor *</label>
            <select name="doctor_id" required class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                @foreach($doctors as $d)
                    <option value="{{ $d->id }}" @selected(old('doctor_id',$visit->doctor_id)==$d->id)>Dr. {{ $d->user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
            <select name="status" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                @foreach(['waiting','in_progress','completed','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(old('status',$visit->status)===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Visit Type</label>
            <select name="visit_type" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                @foreach(['new'=>'New Patient','follow_up'=>'Follow Up','emergency'=>'Emergency'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('visit_type',$visit->visit_type)===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- Vitals --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Vitals</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['weight_kg','Weight (kg)','number','0.1'],
            ['height_cm','Height (cm)','number','1'],
            ['temperature','Temp (°F)','number','0.1'],
            ['pulse_rate','Pulse (bpm)','number','1'],
        ] as [$n,$l,$t,$step])
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ $l }}</label>
            <input type="{{ $t }}" name="{{ $n }}" value="{{ old($n, $visit->$n) }}" step="{{ $step }}"
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
        @endforeach
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Blood Pressure</label>
            <input type="text" name="blood_pressure" value="{{ old('blood_pressure', $visit->blood_pressure) }}"
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">O₂ Saturation</label>
            <input type="number" name="oxygen_saturation" value="{{ old('oxygen_saturation', $visit->oxygen_saturation) }}"
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
    </div>
</div>

{{-- Clinical Notes --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Clinical Notes</h2>
    <div class="space-y-4">
        <x-form.textarea name="symptoms" label="Symptoms" :value="old('symptoms', $visit->symptoms)" rows="3" />
        <x-form.textarea name="diagnosis" label="Diagnosis" :value="old('diagnosis', $visit->diagnosis)" rows="3" />
        <x-form.textarea name="notes" label="Doctor Notes" :value="old('notes', $visit->notes)" rows="2" />
    </div>
</div>

{{-- Payment --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Payment</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Consultation Fee</label>
            <input type="number" name="consultation_fee" value="{{ old('consultation_fee', $visit->consultation_fee) }}" step="0.01"
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Discount</label>
            <input type="number" name="discount_amount" value="{{ old('discount_amount', $visit->discount_amount) }}" step="0.01"
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Payment Method</label>
            <select name="payment_method" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                @foreach(['cash'=>'Cash','card'=>'Card','bank_transfer'=>'Bank Transfer','insurance'=>'Insurance'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('payment_method',$visit->payment_method)===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Payment Status</label>
            <select name="payment_status" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                @foreach(['paid'=>'Paid','pending'=>'Pending','partial'=>'Partial','waived'=>'Waived'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('payment_status',$visit->payment_status)===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Update Visit</button>
    <a href="{{ route('opd.show', $visit) }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm rounded-lg hover:bg-slate-200">Cancel</a>
</div>

</form>
</div>
@endsection
