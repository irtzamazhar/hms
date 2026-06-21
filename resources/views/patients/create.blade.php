@extends('layouts.hms')

@section('title', 'Register Patient')
@section('breadcrumb')
    <a href="{{ route('patients.index') }}" class="text-slate-400 hover:text-slate-600">Patients</a>
    <span class="mx-1">/</span> <span class="text-slate-700 dark:text-slate-200 font-medium">Register</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700">
            <h1 class="text-lg font-bold text-slate-800 dark:text-white">Register New Patient</h1>
            <p class="text-sm text-slate-400">MR number will be auto-assigned.</p>
        </div>

        <form method="POST" action="{{ route('patients.store') }}" class="p-6 space-y-6">
            @csrf

            {{-- Personal Info --}}
            <div>
                <h2 class="text-sm font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-3">Personal Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-form.input name="name" label="Full Name *" :value="old('name')" required />
                    </div>
                    <x-form.input name="cnic" label="CNIC" :value="old('cnic')" placeholder="00000-0000000-0" />
                    <x-form.input name="phone" label="Phone" :value="old('phone')" placeholder="+92-300-0000000" />
                    <x-form.input name="email" type="email" label="Email" :value="old('email')" />

                    <div>
                        <label class="field-label">Gender *</label>
                        <select name="gender" required class="field">
                            <option value="">Select</option>
                            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('gender') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-form.input name="dob" type="date" label="Date of Birth" :value="old('dob')" />

                    <x-form.input name="age" type="number" label="Age (years)" :value="old('age')" min="0" max="150" />

                    <div>
                        <label class="field-label">Blood Group</label>
                        <select name="blood_group" class="field">
                            <option value="unknown">Unknown</option>
                            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                                <option value="{{ $bg }}" @selected(old('blood_group') === $bg)>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div>
                <h2 class="text-sm font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-3">Address</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-form.textarea name="address" label="Street Address" :value="old('address')" rows="2" />
                    </div>
                    <x-form.input name="city" label="City" :value="old('city')" />
                </div>
            </div>

            {{-- Emergency Contact --}}
            <div>
                <h2 class="text-sm font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-3">Emergency Contact</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-form.input name="emergency_contact_name" label="Name" :value="old('emergency_contact_name')" />
                    <x-form.input name="emergency_contact_phone" label="Phone" :value="old('emergency_contact_phone')" />
                    <x-form.input name="emergency_contact_relation" label="Relation" :value="old('emergency_contact_relation')" />
                </div>
            </div>

            {{-- Medical --}}
            <div>
                <h2 class="text-sm font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-3">Medical Notes</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.textarea name="allergies" label="Allergies" :value="old('allergies')" rows="2" />
                    <x-form.textarea name="medical_history" label="Medical History" :value="old('medical_history')" rows="2" />
                    <x-form.input name="referred_by" label="Referred By" :value="old('referred_by')" />
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Register Patient
                </button>
                <a href="{{ route('patients.index') }}" class="btn-cancel">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
