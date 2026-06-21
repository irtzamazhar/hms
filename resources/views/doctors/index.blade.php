@extends('layouts.hms')
@section('title','Doctors')
@section('breadcrumb')
    <span class="text-slate-400">Administration</span> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Doctors</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800 dark:text-white">Doctors</h1><p class="text-sm text-slate-400">{{ $doctors->total() }} registered</p></div>
    @can('create doctors')
    <a href="{{ route('doctors.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Doctor
    </a>
    @endcan
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Doctor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Specialization</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Department</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Contact</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($doctors as $d)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-sm font-bold text-primary-600">{{ substr($d->user?->name ?? '?', 0, 1) }}</div>
                            <div>
                                <p class="font-medium text-slate-700 dark:text-white">Dr. {{ $d->user?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $d->user?->email ?? '—' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $d->specialization ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $d->department->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $d->user?->phone ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @php $dc = ['active'=>'green','inactive'=>'slate','on_leave'=>'amber']; @endphp
                        <x-badge color="{{ $dc[$d->status] ?? 'slate' }}">{{ ucfirst(str_replace('_',' ',$d->status)) }}</x-badge>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1">
                            <a href="{{ route('doctors.show',$d) }}" class="p-1.5 rounded text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-white">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @can('edit doctors')
                            <a href="{{ route('doctors.edit',$d) }}" class="p-1.5 rounded text-slate-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endcan
                            @can('delete doctors')
                            <form method="POST" action="{{ route('doctors.destroy',$d) }}"
                                  onsubmit="return confirm('Remove Dr. {{ addslashes($d->user?->name ?? 'this doctor') }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete"
                                        class="p-1.5 rounded text-slate-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400 text-sm">No doctors registered yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($doctors->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $doctors->links() }}</div>
    @endif
</div>
@endsection
