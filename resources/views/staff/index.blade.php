@extends('layouts.hms')
@section('title','Staff')
@section('breadcrumb')
    <span class="text-slate-400">Administration</span> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Staff</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800 dark:text-white">Staff</h1><p class="text-sm text-slate-400">{{ $staff->total() }} members</p></div>
    @can('create staff')
    <a href="{{ route('staff.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Staff
    </a>
    @endcan
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, ID…"
               class="field">
        <select name="department_id" class="field">
            <option value="">All Departments</option>
            @foreach($departments as $d)
                <option value="{{ $d->id }}" @selected(request('department_id')==$d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
        <select name="status" class="field">
            <option value="">All Statuses</option>
            <option value="active" @selected(request('status')==='active')>Active</option>
            <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
        </select>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 text-white text-sm rounded-lg">Filter</button>
            <a href="{{ route('staff.index') }}" class="btn-cancel">Reset</a>
        </div>
    </div>
</form>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Staff Member</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Role</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Department</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Contact</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($staff as $s)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="field w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-600 dark:text-slate-300">{{ substr($s->user->name,0,1) }}</div>
                        <div>
                            <p class="font-medium text-slate-700 dark:text-white">{{ $s->user->name }}</p>
                            <p class="text-xs text-slate-400">{{ $s->user->employee_id ?? $s->user->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ ucfirst(str_replace('_',' ',$s->position ?? '—')) }}</td>
                <td class="px-4 py-3 text-slate-500">{{ $s->department->name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-500">{{ $s->user->phone ?? '—' }}</td>
                <td class="px-4 py-3"><x-badge color="{{ ($s->is_active ?? true) ? 'green' : 'slate' }}">{{ ($s->is_active ?? true) ? 'Active' : 'Inactive' }}</x-badge></td>
                <td class="px-4 py-3">
                    <div class="flex gap-1">
                        <a href="{{ route('staff.show',$s) }}" class="p-1.5 rounded text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        @can('update staff')
                        <a href="{{ route('staff.edit',$s) }}" class="p-1.5 rounded text-slate-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400 text-sm">No staff members found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($staff->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $staff->links() }}</div>
    @endif
</div>
@endsection
