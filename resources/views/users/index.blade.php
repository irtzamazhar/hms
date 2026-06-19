@extends('layouts.hms')
@section('title','Users')
@section('breadcrumb')
    <span class="font-medium text-slate-700 dark:text-slate-200">User Management</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">User Management</h1>
    @can('manage users')
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
            <a href="{{ route('users.index') }}" class="px-3 py-2 border text-slate-600 dark:text-slate-300">Clear</a>
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
                <td class="px-4 py-3 flex gap-2 justify-end">
                    <a href="{{ route('users.show', $user) }}" class="text-xs text-primary-600 hover:underline">View</a>
                    @can('manage users')
                    <a href="{{ route('users.edit', $user) }}" class="text-xs text-slate-500 hover:underline">Edit</a>
                    @endcan
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
