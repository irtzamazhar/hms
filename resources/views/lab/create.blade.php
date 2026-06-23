@extends('layouts.hms')
@section('title','New Lab Booking')
@section('breadcrumb')
    <a href="{{ route('lab.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Lab</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">New Booking</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<form method="POST" action="{{ route('lab.store') }}" class="space-y-4" x-data="labBooking()">
@csrf

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Booking Details</h2>
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
            <label class="field-label">Referred By</label>
            <select name="doctor_id" class="field">
                <option value="">— None —</option>
                @foreach($doctors as $d)
                    <option value="{{ $d->id }}" @selected(old('doctor_id')==$d->id)>Dr. {{ $d->user?->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Payment Method</label>
            <select name="payment_method" class="field">
                @foreach(['cash'=>'Cash','card'=>'Card','insurance'=>'Insurance'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('payment_method','cash')===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- Test Selection --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">Select Tests</h2>
        <p class="text-sm font-bold text-primary-600">Total: ₨ <span x-text="total()"></span></p>
    </div>

    @foreach($categories as $cat)
    <div class="mb-4">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">{{ $cat->name }}</p>
        <div class="space-y-1">
            @foreach($cat->tests as $test)
            <label class="flex items-center justify-between p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-primary-400 cursor-pointer"
                   :class="isSelected({{ $test->id }}) ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : ''">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="tests[]" value="{{ $test->id }}" x-model="selected"
                           class="rounded text-primary-600 focus:ring-primary-500"
                           @change="updatePrice({{ $test->id }}, {{ (float) $test->cost }})">
                    <div>
                        <p class="text-sm text-slate-700 dark:text-white">{{ $test->name }}</p>
                        @if($test->code)<p class="text-xs text-slate-400">{{ $test->code }}</p>@endif
                    </div>
                </div>
                <span class="text-sm font-semibold text-slate-700 dark:text-white">₨ {{ number_format($test->cost, 0) }}</span>
            </label>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
        <div>
            <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mr-2">Discount (₨)</label>
            <input type="number" name="discount" value="{{ old('discount', 0) }}" min="0" step="0.01"
                   class="field w-28">
        </div>
        <p class="text-base font-bold text-slate-800 dark:text-white">Total: ₨ <span x-text="total()"></span></p>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" :disabled="selected.length === 0"
            class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg">
        Create Booking
    </button>
    <a href="{{ route('lab.index') }}" class="btn-cancel">Cancel</a>
</div>

</form>
</div>
@endsection

@push('scripts')
<script>
function labBooking() {
    return {
        selected: [],
        prices: {},
        isSelected(id) { return this.selected.includes(id); },
        updatePrice(id, price) {
            if (this.prices[id] === undefined) this.prices[id] = price;
        },
        total() {
            return this.selected.reduce((s, id) => s + (this.prices[id] || 0), 0).toLocaleString();
        }
    }
}
</script>
@endpush
