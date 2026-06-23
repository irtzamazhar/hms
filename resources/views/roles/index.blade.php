@extends('layouts.hms')
@section('title','Roles')
@section('breadcrumb')
    <span class="font-medium text-slate-700 dark:text-slate-200">Roles &amp; Permissions</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">Roles</h1>
        <p class="text-sm text-slate-400">Define roles and the permissions granted to each.</p>
    </div>
    <div class="flex items-center gap-2">
        @can('view permissions')
        <a href="{{ route('permissions.index') }}" class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/40">Manage Permissions</a>
        @endcan
        @can('create roles')
        <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Role
        </a>
        @endcan
    </div>
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search roles…" class="field flex-1">
        <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Filter</button>
        <a href="{{ route('roles.index') }}" class="btn-cancel">Reset</a>
    </div>
</form>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Role</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Permissions</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Users</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($roles as $role)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-slate-800 dark:text-white capitalize">{{ str_replace('_',' ',$role->name) }}</span>
                        @if($role->name === 'super_admin')
                        <x-badge color="amber">protected</x-badge>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3"><x-badge color="blue">{{ $role->permissions_count }}</x-badge></td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $role->users_count }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1.5 justify-end">
                        <a href="{{ route('roles.show', $role) }}" title="View"
                           class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        @can('edit roles')
                        <a href="{{ route('roles.edit', $role) }}" title="Edit"
                           class="p-1.5 rounded-lg text-slate-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @endcan
                        @can('delete roles')
                        @if($role->name !== 'super_admin' && $role->users_count === 0)
                        <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Delete the “{{ $role->name }}” role?')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Delete"
                                    class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        @endif
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No roles found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($roles->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $roles->links() }}</div>
    @endif
</div>
@endsection
