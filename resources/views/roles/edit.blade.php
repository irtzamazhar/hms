@extends('layouts.hms')
@section('title','Edit Role — '.$role->name)
@section('breadcrumb')
    <a href="{{ route('roles.index') }}" class="text-slate-400 hover:text-slate-600">Roles</a>
    <span class="mx-1">/</span>
    <a href="{{ route('roles.show', $role) }}" class="text-slate-400 hover:text-slate-600 capitalize">{{ str_replace('_',' ',$role->name) }}</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Edit</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Edit Role</h1>
    <a href="{{ route('roles.show', $role) }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-600 dark:text-red-400 rounded-xl text-sm">
    {{ $errors->first() }}
</div>
@endif

@if($locked)
<div class="mb-4 px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 text-amber-700 dark:text-amber-400 rounded-xl text-sm">
    <strong>{{ str_replace('_',' ',$role->name) }}</strong> is a protected role. It always retains full access and its permissions cannot be changed.
</div>
@endif

<form method="POST" action="{{ route('roles.update', $role) }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
    @csrf
    @method('PUT')

    <div class="max-w-md">
        <x-form.input name="name" label="Role Name" :value="$role->name" required :readonly="$locked" />
    </div>

    <div class="border-t border-slate-200 dark:border-slate-700 pt-5">
        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Permissions</h2>
        <p class="text-xs text-slate-400 mb-4">{{ $locked ? 'This role has full access.' : 'Select the permissions this role should grant.' }}</p>
        @include('partials.permission-matrix', ['grouped' => $groupedPermissions, 'checked' => $assigned, 'locked' => $locked])
    </div>

    <div class="flex justify-end gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
        <a href="{{ route('roles.show', $role) }}" class="btn-cancel">Cancel</a>
        @unless($locked)
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Update Role</button>
        @endunless
    </div>
</form>
</div>
@endsection
