@extends('layouts.hms')
@section('title','New Token')
@section('breadcrumb')
    <a href="{{ route('tokens.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Tokens</a>
    <svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="font-medium text-slate-600 dark:text-slate-300">New Token</span>
@endsection

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white dark:bg-dark-800 rounded-2xl border border-slate-200 dark:border-dark-700 overflow-hidden shadow-sm"
         x-data="{ newPatient: {{ $errors->has('new_name') || $errors->has('new_age') || $errors->has('new_phone') ? 'true' : 'false' }} }">

        {{-- Card header --}}
        <div class="px-6 py-5 border-b border-slate-100 dark:border-dark-700 flex items-center justify-between bg-slate-50 dark:bg-dark-900/40">
            <div>
                <h1 class="text-base font-bold text-slate-800 dark:text-white">Generate Token</h1>
                <p class="text-xs text-slate-400 mt-0.5">{{ now()->format('d M Y') }}</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Next Token No.</p>
                <p class="text-5xl font-black text-blue-600 dark:text-blue-400 leading-none" id="tokenPreview">{{ $nextToken }}</p>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mt-1">{{ ucfirst($shift) }} Shift</p>
            </div>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('tokens.store') }}" class="p-6 space-y-5">
            @csrf

            {{-- Patient toggle --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="field-label mb-0">Patient <span class="text-red-500">*</span></label>
                    <button type="button"
                            @click="newPatient = !newPatient"
                            class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                        <template x-if="!newPatient">
                            <span>+ New Patient</span>
                        </template>
                        <template x-if="newPatient">
                            <span>← Existing Patient</span>
                        </template>
                    </button>
                </div>

                {{-- Existing patient select --}}
                <div x-show="!newPatient">
                    <select name="patient_id" id="patient_id" class="field">
                        <option value="">Search patient…</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" @selected(old('patient_id') == $p->id)>
                                {{ $p->name }} — {{ $p->mr_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Quick new patient fields --}}
                <div x-show="newPatient" class="rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50/50 dark:bg-blue-950/20 p-4 space-y-3">
                    <p class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wide">First Visit — Quick Register</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="field-label">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="new_name" value="{{ old('new_name') }}"
                                   placeholder="Patient's full name"
                                   class="field" />
                            @error('new_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label">Age <span class="text-red-500">*</span></label>
                            <input type="number" name="new_age" value="{{ old('new_age') }}"
                                   placeholder="e.g. 35" min="0" max="150"
                                   class="field" />
                            @error('new_age')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label">Gender</label>
                            <select name="new_gender" class="field">
                                <option value="male"   @selected(old('new_gender','male') === 'male')>Male</option>
                                <option value="female" @selected(old('new_gender') === 'female')>Female</option>
                                <option value="other"  @selected(old('new_gender') === 'other')>Other</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="field-label">Mobile Number <span class="text-red-500">*</span></label>
                            <input type="text" name="new_phone" value="{{ old('new_phone') }}"
                                   placeholder="e.g. 03001234567"
                                   class="field" />
                            @error('new_phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shift --}}
            <div>
                <label for="shift" class="field-label">Shift <span class="text-red-500">*</span></label>
                <select name="shift" id="shiftSelect" required class="field">
                    @foreach(['morning' => 'Morning', 'evening' => 'Evening', 'night' => 'Night'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('shift', $shift) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('shift')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Doctor --}}
            <div>
                <label for="doctor_id" class="field-label">Doctor</label>
                <select name="doctor_id" id="doctor_id" class="field">
                    <option value="">— Walk-in / No preference —</option>
                    @foreach($doctors as $d)
                        <option value="{{ $d->id }}" @selected(old('doctor_id') == $d->id)>
                            Dr. {{ $d->user->name }} ({{ $d->specialization }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Department --}}
            <div>
                <label for="department_id" class="field-label">Department</label>
                <select name="department_id" id="department_id" class="field">
                    <option value="">— Any —</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Priority --}}
            <div>
                <label for="priority" class="field-label">Priority</label>
                <select name="priority" id="priority" class="field">
                    <option value="normal"    @selected(old('priority', 'normal') === 'normal')>Normal</option>
                    <option value="urgent"    @selected(old('priority') === 'urgent')>Urgent</option>
                    <option value="emergency" @selected(old('priority') === 'emergency')>Emergency</option>
                </select>
            </div>

            {{-- Notes --}}
            <x-form.textarea name="notes" label="Notes" :value="old('notes')" rows="2" placeholder="Optional notes…" />

            {{-- Actions --}}
            <div class="flex gap-3 pt-1">
                <button type="submit"
                        class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    Generate Token #{{ $nextToken }}
                </button>
                <a href="{{ route('tokens.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
