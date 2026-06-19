@extends('layouts.hms')
@section('title','Add User')
@section('breadcrumb')
    <a href="{{ route('users.index') }}" class="text-slate-400 hover:text-slate-600">Users</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Add</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Add New User</h1>
    <a href="{{ route('users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

<form method="POST" action="{{ route('users.store') }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    @csrf

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="name" label="Full Name" required />
        <x-form.input name="email" type="email" label="Email Address" required />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="phone" label="Phone" />
        <x-form.select name="user_type" label="User Type" required>
            @foreach(['admin','doctor','staff','receptionist','pharmacist','lab_technician'] as $t)
            <option value="{{ $t }}" @selected(old('user_type') === $t)>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
            @endforeach
        </x-form.select>
    </div>

    <x-form.select name="role" label="System Role" required>
        @foreach($roles as $role)
        <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ ucfirst(str_replace('_',' ',$role->name)) }}</option>
        @endforeach
    </x-form.select>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="password" type="password" label="Password" required />
        <x-form.input name="password_confirmation" type="password" label="Confirm Password" required />
    </div>

    <x-form.select name="status" label="Status" required>
        <option value="active" @selected(old('status') !== 'inactive')>Active</option>
        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
    </x-form.select>

    <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('users.index') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm rounded-lg hover:bg-slate-50">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Create User</button>
    </div>
</form>
</div>
@endsection
