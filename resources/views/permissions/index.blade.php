@extends('layouts.hms')
@section('title','Permissions')
@section('breadcrumb')
    <a href="{{ route('roles.index') }}" class="text-slate-400 hover:text-slate-600">Roles</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Permissions</span>
@endsection

@php
    $coreAccessControl = \App\Support\Permissions::groups()['Access Control'];
@endphp

@section('content')
<div x-data="{ showCreate: {{ $errors->hasAny(['name', 'group']) ? 'true' : 'false' }} }">
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">Permissions</h1>
        <p class="text-sm text-slate-400">{{ $total }} permissions across the system.</p>
    </div>
    @can('create permissions')
    <button @click="showCreate = true" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Permission
    </button>
    @endcan
</div>

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-600 dark:text-red-400 rounded-xl text-sm">
    {{ $errors->first() }}
</div>
@endif

<div class="grid md:grid-cols-2 gap-4">
    @foreach($grouped as $label => $permissions)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="bg-slate-50 dark:bg-slate-700/40 px-4 py-2.5 flex items-center justify-between">
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $label }}</span>
            <span class="text-xs text-slate-400">{{ $permissions->count() }}</span>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($permissions as $perm)
            <div class="flex items-center justify-between px-4 py-2.5">
                <div>
                    <p class="text-sm text-slate-700 dark:text-slate-200 capitalize">{{ $perm->name }}</p>
                    <p class="text-xs text-slate-400">{{ $perm->roles_count }} {{ Str::plural('role', $perm->roles_count) }}</p>
                </div>
                @can('delete permissions')
                @unless(in_array($perm->name, $coreAccessControl, true))
                <form method="POST" action="{{ route('permissions.destroy', $perm) }}" onsubmit="return confirm('Delete the “{{ $perm->name }}” permission? It will be removed from all roles and users.')">
                    @csrf @method('DELETE')
                    <button type="submit" title="Delete"
                            class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
                @else
                <x-badge color="amber">core</x-badge>
                @endunless
                @endcan
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

{{-- Create permission modal --}}
@can('create permissions')
<div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
    <div class="absolute inset-0 bg-black/40" @click="showCreate = false"></div>
    <div class="relative bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 w-full max-w-md p-6 shadow-xl">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Add Permission</h2>
        <form method="POST" action="{{ route('permissions.store') }}" class="space-y-4">
            @csrf
            <x-form.input name="name" label="Permission Name" placeholder="e.g. view ward billing" required />
            <p class="text-xs text-slate-400">Convention: <code>verb area</code> (e.g. <code>view billing</code>, <code>manage assets</code>). Lowercase only.</p>
            <x-form.select name="group" label="Module" required>
                @foreach(\App\Support\Permissions::moduleOptions() as $module)
                <option value="{{ $module }}" @selected(old('group') === $module)>{{ $module }}</option>
                @endforeach
            </x-form.select>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="showCreate = false" class="btn-cancel">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Create</button>
            </div>
        </form>
    </div>
</div>
@endcan
</div>
@endsection
