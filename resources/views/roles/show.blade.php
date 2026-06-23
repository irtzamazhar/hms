@extends('layouts.hms')
@section('title','Role — '.$role->name)
@section('breadcrumb')
    <a href="{{ route('roles.index') }}" class="text-slate-400 hover:text-slate-600">Roles</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200 capitalize">{{ str_replace('_',' ',$role->name) }}</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <h1 class="text-xl font-bold text-slate-800 dark:text-white capitalize">{{ str_replace('_',' ',$role->name) }}</h1>
        @if($role->name === 'super_admin')<x-badge color="amber">protected</x-badge>@endif
    </div>
    @can('edit roles')
    <a href="{{ route('roles.edit', $role) }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg">Edit Role</a>
    @endcan
</div>

<div class="grid md:grid-cols-3 gap-4 mb-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <p class="text-xs text-slate-400 uppercase tracking-wide">Permissions</p>
        <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ $role->permissions->count() }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <p class="text-xs text-slate-400 uppercase tracking-wide">Users with this role</p>
        <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ $role->users->count() }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <p class="text-xs text-slate-400 uppercase tracking-wide">Guard</p>
        <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ $role->guard_name }}</p>
    </div>
</div>

{{-- Granted permissions, grouped --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 mb-4">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Granted Permissions</h2>
    <div class="grid md:grid-cols-2 gap-4">
        @foreach($groupedPermissions as $label => $permissions)
            @php $granted = $permissions->whereIn('name', $assigned); @endphp
            @continue($granted->isEmpty())
            <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                <div class="bg-slate-50 dark:bg-slate-700/40 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $label }}</div>
                <div class="p-3 flex flex-wrap gap-1.5">
                    @foreach($granted as $perm)
                    <x-badge color="green" class="capitalize">{{ $perm->name }}</x-badge>
                    @endforeach
                </div>
            </div>
        @endforeach
        @if(empty($assigned))
        <p class="text-sm text-slate-400">No permissions granted to this role yet.</p>
        @endif
    </div>
</div>

{{-- Users holding this role --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Users ({{ $role->users->count() }})</h2>
    @if($role->users->isEmpty())
    <p class="text-sm text-slate-400">No users currently have this role.</p>
    @else
    <div class="flex flex-wrap gap-3">
        @foreach($role->users as $u)
        <a href="{{ route('users.show', $u) }}" class="flex items-center gap-2.5 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/40">
            <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" class="w-7 h-7 rounded-full object-cover">
            <div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $u->name }}</p>
                <p class="text-xs text-slate-400">{{ $u->email }}</p>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
