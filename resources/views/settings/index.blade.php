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
                :class="tab==='{{ $key }}' ? 'bg-white text-slate-800 shadow' : 'text-slate-500 hover:text-slate-700'">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Hospital Info --}}
    <div x-show="tab==='hospital'">
        <form method="POST" action="{{ route('settings.hospital') }}" enctype="multipart/form-data">
            @csrf @method('PATCH')
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
                    <label class="field-label">Logo</label>
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
            @csrf @method('PATCH')
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">System Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Timezone</label>
                        <select name="timezone" class="field">
                            @foreach(['Asia/Karachi'=>'Asia/Karachi (PKT)','UTC'=>'UTC','Asia/Kolkata'=>'Asia/Kolkata (IST)','America/New_York'=>'America/New_York (EST)'] as $v=>$l)
                                <option value="{{ $v }}" @selected(old('timezone',$setting->timezone ?? 'Asia/Karachi')===$v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Date Format</label>
                        <select name="date_format" class="field">
                            @foreach(['d M Y'=>'01 Jan 2025','Y-m-d'=>'2025-01-01','d/m/Y'=>'01/01/2025'] as $v=>$example)
                                <option value="{{ $v }}" @selected(old('date_format',$setting->date_format ?? 'd M Y')===$v)>{{ $v }} ({{ $example }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-sm text-blue-700 dark:text-blue-300">
                        Shift timings are managed on the <a href="{{ route('shifts.index') }}" class="font-semibold underline">Shifts page</a>.
                    </div>
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
