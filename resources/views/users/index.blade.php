@extends('layouts.hms')
@section('title','Users')
@section('breadcrumb')
    <span class="font-medium text-slate-700 dark:text-slate-200">User Management</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">User Management</h1>
    @can('create users')
    <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add User
    </a>
    @endcan
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
               class="field">
        <select name="role" class="field">
            <option value="">All Roles</option>
            @foreach($roles as $role)
            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ ucfirst(str_replace('_',' ',$role->name)) }}</option>
            @endforeach
        </select>
        <select name="status" class="field">
            <option value="">All Status</option>
            <option value="active" @selected(request('status')==='active')>Active</option>
            <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
        </select>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Filter</button>
            <a href="{{ route('users.index') }}" class="btn-cancel">Reset</a>
        </div>
    </div>
</form>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">User</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Role</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Type</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Employee ID</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Last Login</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($users as $user)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover">
                        <div>
                            <p class="font-medium text-slate-800 dark:text-white">{{ $user->name }}</p>
                            <p class="text-xs text-slate-400">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    @foreach($user->roles as $role)
                    <x-badge color="blue">{{ ucfirst(str_replace('_',' ',$role->name)) }}</x-badge>
                    @endforeach
                </td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400 capitalize">{{ str_replace('_',' ',$user->user_type) }}</td>
                <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $user->employee_id ?? '—' }}</td>
                <td class="px-4 py-3"><x-badge color="{{ $user->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($user->status) }}</x-badge></td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400 text-xs">{{ $user->last_login_at?->diffForHumans() ?? '—' }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1.5 justify-end">
                        <a href="{{ route('users.show', $user) }}" title="View"
                           class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        @can('edit users')
                        <a href="{{ route('users.edit', $user) }}" title="Edit"
                           class="p-1.5 rounded-lg text-slate-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @endcan
                        @can('assign user permissions')
                        <a href="{{ route('users.permissions', $user) }}" title="Manage Permissions"
                           class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </a>
                        @endcan
                        @can('delete users')
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
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
            <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $users->links() }}</div>
    @endif
</div>
@endsection
