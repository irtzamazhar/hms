@extends('layouts.hms')
@section('title','Add Role')
@section('breadcrumb')
    <a href="{{ route('roles.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Roles</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Add</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Add New Role</h1>
    <a href="{{ route('roles.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-200">← Back</a>
</div>

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-600 dark:text-red-400 rounded-xl text-sm">
    {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ route('roles.store') }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
    @csrf

    <div class="max-w-md">
        <x-form.input name="name" label="Role Name" placeholder="e.g. ward_manager" required />
        <p class="mt-1 text-xs text-slate-400">Lowercase letters, numbers and underscores only.</p>
    </div>

    <div class="border-t border-slate-200 dark:border-slate-700 pt-5">
        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Permissions</h2>
        <p class="text-xs text-slate-400 mb-4">Select the permissions this role should grant.</p>
        @include('partials.permission-matrix', ['grouped' => $groupedPermissions, 'checked' => $assigned])
    </div>

    <div class="flex justify-end gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
        <a href="{{ route('roles.index') }}" class="btn-cancel">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Create Role</button>
    </div>
</form>
</div>
@endsection
