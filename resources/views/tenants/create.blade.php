@extends('layouts.hms')
@section('title','New Hospital')
@section('breadcrumb')
    <a href="{{ route('tenants.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Tenants</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">New</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white mb-1">Provision a Hospital</h1>
    <p class="text-sm text-slate-400 mb-6">Creates the tenant on a free trial plus its first admin user.</p>

    <form method="POST" action="{{ route('tenants.store') }}" class="space-y-6">
        @csrf
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Hospital</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form.input name="name" label="Hospital Name" :value="old('name')" required />
                <x-form.input name="slug" label="Subdomain (slug)" :value="old('slug')" required placeholder="cityhospital" />
                <x-form.input name="email" type="email" label="Contact Email" :value="old('email')" />
                <x-form.input name="phone" label="Phone" :value="old('phone')" />
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">First Admin User</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form.input name="admin_name" label="Admin Name" :value="old('admin_name')" required />
                <x-form.input name="admin_email" type="email" label="Admin Email" :value="old('admin_email')" required />
                <x-form.input name="admin_password" type="password" label="Password" required />
                <x-form.input name="admin_password_confirmation" type="password" label="Confirm Password" required />
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Provision Tenant</button>
            <a href="{{ route('tenants.index') }}" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>
@endsection
