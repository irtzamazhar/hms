@extends('layouts.hms')
@section('title','Settings')
@section('breadcrumb')
    <span class="font-medium text-slate-700 dark:text-slate-200">Settings</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{tab:'hospital'}">

    {{-- Tabs --}}
    <div class="flex gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl w-fit">
        @foreach(['hospital'=>'Hospital Info','system'=>'System Settings'] as $key=>$label)
        <button @click="tab='{{ $key }}'" type="button"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                :class="tab==='{{ $key }}' ? 'bg-white dark:bg-slate-700 text-slate-800 dark:text-white shadow' : 'text-slate-500 hover:text-slate-700'">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Hospital Info --}}
    <div x-show="tab==='hospital'">
        <form method="POST" action="{{ route('settings.hospital') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">Hospital Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="hospital_name" label="Hospital Name *" :value="old('hospital_name',$setting->hospital_name)" required class="md:col-span-2" />
                    <x-form.input name="phone" label="Phone" :value="old('phone',$setting->phone)" />
                    <x-form.input name="email" label="Email" type="email" :value="old('email',$setting->email)" />
                    <x-form.input name="website" label="Website" :value="old('website',$setting->website)" />
                    <x-form.input name="registration_no" label="Registration No." :value="old('registration_no',$setting->registration_no)" />
                    <x-form.input name="tax_no" label="Tax No. (NTN)" :value="old('tax_no',$setting->tax_no)" />
                    <x-form.input name="currency" label="Currency" :value="old('currency',$setting->currency ?? 'PKR')" />
                </div>
                <x-form.textarea name="address" label="Full Address" :value="old('address',$setting->address)" rows="2" />
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Logo</label>
                    @if($setting->logo)
                    <div class="mb-2"><img src="{{ asset('storage/'.$setting->logo) }}" alt="Logo" class="h-12 rounded-lg border border-slate-200"></div>
                    @endif
                    <input type="file" name="logo" accept="image/*"
                           class="text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                </div>
                <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Save Hospital Info</button>
            </div>
        </form>
    </div>

    {{-- System Settings --}}
    <div x-show="tab==='system'">
        <form method="POST" action="{{ route('settings.system') }}">
            @csrf @method('PUT')
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">System Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Timezone</label>
                        <select name="timezone" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                            @foreach(['Asia/Karachi'=>'Asia/Karachi (PKT)','UTC'=>'UTC','Asia/Kolkata'=>'Asia/Kolkata (IST)','America/New_York'=>'America/New_York (EST)'] as $v=>$l)
                                <option value="{{ $v }}" @selected(old('timezone',$setting->timezone ?? 'Asia/Karachi')===$v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Date Format</label>
                        <select name="date_format" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                            @foreach(['d M Y'=>'01 Jan 2025','Y-m-d'=>'2025-01-01','d/m/Y'=>'01/01/2025'] as $v=>$example)
                                <option value="{{ $v }}" @selected(old('date_format',$setting->date_format ?? 'd M Y')===$v)>{{ $v }} ({{ $example }})</option>
                            @endforeach
                        </select>
                    </div>
                    <x-form.input name="morning_shift_start" label="Morning Shift Start" type="time" :value="old('morning_shift_start',$setting->morning_shift_start ?? '08:00')" />
                    <x-form.input name="morning_shift_end" label="Morning Shift End" type="time" :value="old('morning_shift_end',$setting->morning_shift_end ?? '14:00')" />
                    <x-form.input name="evening_shift_start" label="Evening Shift Start" type="time" :value="old('evening_shift_start',$setting->evening_shift_start ?? '14:00')" />
                    <x-form.input name="evening_shift_end" label="Evening Shift End" type="time" :value="old('evening_shift_end',$setting->evening_shift_end ?? '20:00')" />
                    <x-form.input name="night_shift_start" label="Night Shift Start" type="time" :value="old('night_shift_start',$setting->night_shift_start ?? '20:00')" />
                    <x-form.input name="night_shift_end" label="Night Shift End" type="time" :value="old('night_shift_end',$setting->night_shift_end ?? '08:00')" />
                </div>
                <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                    @foreach([
                        ['enable_two_factor','Enable Two-Factor Auth'],
                        ['enable_audit_log','Enable Audit Logging'],
                        ['enable_lab_module','Enable Lab Module'],
                        ['enable_pharmacy_module','Enable Pharmacy Module'],
                    ] as [$key,$label])
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, $setting->$key ?? false))
                               class="rounded text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Save System Settings</button>
            </div>
        </form>
    </div>

</div>
@endsection
