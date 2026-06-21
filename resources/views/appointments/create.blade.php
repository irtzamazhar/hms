@extends('layouts.hms')
@section('title','Book Appointment')
@section('breadcrumb')
    <a href="{{ route('appointments.index') }}" class="text-slate-400 hover:text-slate-600">Appointments</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Book</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto" x-data="bookingForm()">

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Book Appointment</h1>
    <a href="{{ route('appointments.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

<form method="POST" action="{{ route('appointments.store') }}" class="space-y-4" @submit="onSubmit">
    @csrf

    {{-- Patient + Doctor --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 space-y-4">
        <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Who &amp; With Whom</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="field-label">Patient <span class="text-red-500">*</span></label>
                <select name="patient_id" required class="field">
                    <option value="">— Select Patient —</option>
                    @foreach($patients as $p)
                    <option value="{{ $p->id }}" @selected(request('patient_id') == $p->id)>{{ $p->name }} ({{ $p->mr_number }})</option>
                    @endforeach
                </select>
                @error('patient_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="field-label">Doctor <span class="text-red-500">*</span></label>
                <select name="doctor_id" required class="field" x-model="doctorId" @change="onDoctorChange">
                    <option value="">— Select Doctor —</option>
                    @foreach($doctors as $d)
                    <option value="{{ $d->id }}"
                            data-days="{{ json_encode($d->available_days ?? []) }}"
                            @selected(request('doctor_id') == $d->id)>
                        Dr. {{ $d->user?->name }} — {{ $d->specialization }}
                    </option>
                    @endforeach
                </select>
                @error('doctor_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Doctor availability badge --}}
        <div x-show="doctorInfo" x-cloak>
            <div class="rounded-lg bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40 px-4 py-3 flex flex-wrap gap-4 text-xs text-blue-700 dark:text-blue-300">
                <span><strong>Hours:</strong> <span x-text="doctorInfo?.available_from + ' – ' + doctorInfo?.available_to"></span></span>
                <span><strong>Slot:</strong> <span x-text="doctorInfo?.appointment_duration + ' min'"></span></span>
                <span><strong>Days:</strong>
                    <template x-if="doctorInfo?.available_days?.length">
                        <span x-text="doctorInfo.available_days.map(d => d.charAt(0).toUpperCase() + d.slice(1,3)).join(', ')"></span>
                    </template>
                    <template x-if="!doctorInfo?.available_days?.length">
                        <span>All days</span>
                    </template>
                </span>
                <span><strong>Fee:</strong> ₨ <span x-text="doctorInfo?.consultation_fee"></span></span>
            </div>
        </div>
    </div>

    {{-- Date Picker --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 space-y-3">
        <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Select Date</h2>
        <div>
            <input type="date" class="field max-w-xs"
                   :min="today"
                   x-model="selectedDate"
                   @change="loadSlots"
                   :disabled="!doctorId">
            <p x-show="!doctorId" class="mt-1 text-xs text-slate-400">Select a doctor first.</p>
            <p x-show="selectedDate && unavailableDay" class="mt-1 text-xs text-amber-600">
                This doctor is not available on the selected day. Please choose another date.
            </p>
        </div>
    </div>

    {{-- Time Slots --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 space-y-3"
         x-show="selectedDate && !unavailableDay && slots.length > 0">
        <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Select Time Slot</h2>
        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
            <template x-for="slot in slots" :key="slot.time">
                <button type="button"
                        @click="selectSlot(slot)"
                        :disabled="slot.booked"
                        :class="{
                            'bg-primary-600 text-white border-primary-600': selectedSlot === slot.time,
                            'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 hover:bg-green-100': !slot.booked && selectedSlot !== slot.time,
                            'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800 text-red-400 cursor-not-allowed opacity-60': slot.booked,
                        }"
                        class="py-2 px-1 rounded-lg border text-xs font-semibold text-center transition-colors"
                        x-text="slot.label">
                </button>
            </template>
        </div>
        <p x-show="slots.length > 0 && !selectedSlot" class="text-xs text-slate-400">Click a green slot to select.</p>
    </div>

    <div x-show="selectedDate && !unavailableDay && slots.length === 0 && !loadingSlots"
         class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 text-sm text-amber-700 dark:text-amber-300 text-center">
        No available slots for this date. Try another day.
    </div>

    <div x-show="loadingSlots" class="text-center py-4 text-sm text-slate-400">Loading slots…</div>

    {{-- Hidden datetime sent to server --}}
    <input type="hidden" name="appointment_datetime" :value="appointmentDatetime">
    <input type="hidden" name="duration_minutes" :value="doctorInfo?.appointment_duration ?? 30">

    {{-- Other Details --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 space-y-4">
        <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="field-label">Department</label>
                <select name="department_id" class="field" x-model="departmentId">
                    <option value="">— Select —</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label">Appointment Type <span class="text-red-500">*</span></label>
                <select name="type" required class="field">
                    @foreach(['opd'=>'OPD / New Visit','follow_up'=>'Follow Up','emergency'=>'Emergency','teleconsultation'=>'Teleconsultation'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('type','opd') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label">Consultation Fee (₨)</label>
                <input type="number" name="fee" step="0.01" min="0" class="field"
                       :value="doctorInfo?.consultation_fee ?? 0">
            </div>
        </div>
        <div>
            <label class="field-label">Reason for Visit</label>
            <textarea name="reason" rows="2" class="field" placeholder="Brief reason…">{{ old('reason') }}</textarea>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('appointments.index') }}" class="btn-cancel">Cancel</a>
        <button type="submit"
                :disabled="!selectedSlot"
                class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors">
            Book Appointment
        </button>
    </div>
</form>
</div>
@endsection

@push('scripts')
<script>
function bookingForm() {
    return {
        doctorId: '{{ old('doctor_id', request('doctor_id')) }}',
        doctorInfo: null,
        selectedDate: '',
        today: new Date().toISOString().split('T')[0],
        slots: [],
        selectedSlot: null,
        unavailableDay: false,
        loadingSlots: false,
        departmentId: '',
        get appointmentDatetime() {
            if (!this.selectedDate || !this.selectedSlot) return '';
            return this.selectedDate + 'T' + this.selectedSlot;
        },
        async onDoctorChange() {
            this.doctorInfo = null;
            this.slots = [];
            this.selectedSlot = null;
            this.selectedDate = '';
            if (!this.doctorId) return;
            const res = await fetch(`/appointments/doctor-info/${this.doctorId}`);
            this.doctorInfo = await res.json();
            if (this.doctorInfo.department_id) this.departmentId = this.doctorInfo.department_id;
        },
        async loadSlots() {
            this.slots = [];
            this.selectedSlot = null;
            this.unavailableDay = false;
            if (!this.doctorId || !this.selectedDate) return;
            this.loadingSlots = true;
            const res = await fetch(`/appointments/slots?doctor_id=${this.doctorId}&date=${this.selectedDate}`);
            const data = await res.json();
            this.loadingSlots = false;
            if (data.unavailable) { this.unavailableDay = true; return; }
            this.slots = data.slots;
        },
        selectSlot(slot) {
            if (slot.booked) return;
            this.selectedSlot = slot.time;
        },
        onSubmit(e) {
            if (!this.selectedSlot) {
                e.preventDefault();
                alert('Please select an appointment time slot.');
            }
        },
    }
}
</script>
@endpush
