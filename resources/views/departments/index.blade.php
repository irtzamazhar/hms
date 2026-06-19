@extends('layouts.hms')
@section('title','Departments')
@section('breadcrumb')
    <span class="text-slate-400">Administration</span> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Departments</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Departments</h1>
    @can('create departments')
    <a href="{{ route('departments.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Department
    </a>
    @endcan
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($departments as $d)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <div class="flex justify-between items-start">
            <div>
                <p class="font-bold text-slate-800 dark:text-white">{{ $d->name }}</p>
                <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $d->code }}</p>
                @if($d->description)<p class="text-sm text-slate-500 mt-1">{{ $d->description }}</p>@endif
            </div>
            @canany(['edit departments', 'delete departments'])
            <div class="flex gap-1">
                @can('edit departments')
                <a href="{{ route('departments.edit',$d) }}" title="Edit"
                   class="p-1.5 rounded text-slate-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                @endcan
                @can('delete departments')
                <form method="POST" action="{{ route('departments.destroy',$d) }}"
                      onsubmit="return confirm('Delete {{ addslashes($d->name) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" title="Delete"
                            class="p-1.5 rounded text-slate-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
                @endcan
            </div>
            @endcanany
        </div>
        <div class="flex gap-4 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
            <div><p class="text-xs text-slate-400">Doctors</p><p class="font-semibold text-slate-700 dark:text-white">{{ $d->doctors_count ?? 0 }}</p></div>
            <div><p class="text-xs text-slate-400">Staff</p><p class="font-semibold text-slate-700 dark:text-white">{{ $d->staff_count ?? 0 }}</p></div>
            <div class="ml-auto"><x-badge color="{{ $d->status === 'active' ? 'green' : 'slate' }}">{{ $d->status === 'active' ? 'Active' : 'Inactive' }}</x-badge></div>
        </div>
    </div>
    @empty
    <div class="col-span-3 py-12 text-center text-slate-400">No departments found.</div>
    @endforelse
</div>
@endsection
