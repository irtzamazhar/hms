@extends('layouts.hms')
@section('title','Wards')
@section('breadcrumb')
    <span class="text-slate-400">IPD</span> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Wards</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Wards</h1>
    @can('manage settings')
    <a href="{{ route('wards.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Ward
    </a>
    @endcan
</div>

<div class="space-y-4">
    @forelse($wards as $w)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <div>
                <span class="font-bold text-slate-800 dark:text-white">{{ $w->name }}</span>
                @if($w->ward_type)<span class="ml-2 text-xs text-slate-400">({{ ucfirst($w->ward_type) }})</span>@endif
            </div>
            <div class="flex gap-2 items-center">
                <span class="text-sm text-slate-500">{{ $w->beds_count ?? 0 }} beds</span>
                <x-badge color="{{ $w->status === 'active' ? 'green' : 'slate' }}">{{ $w->status === 'active' ? 'Active' : 'Inactive' }}</x-badge>
                @can('manage settings')
                <a href="{{ route('wards.edit',$w) }}" class="p-1.5 rounded text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                @endcan
            </div>
        </div>
        {{-- Beds grid --}}
        @if($w->beds && $w->beds->count())
        <div class="p-4 grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-12 gap-2">
            @foreach($w->beds as $bed)
            <div class="rounded-lg border-2 p-2 text-center text-xs font-semibold
                {{ $bed->status === 'available' ? 'border-green-400 bg-green-50 dark:bg-green-900/20 text-green-700' :
                   ($bed->status === 'occupied' ? 'border-red-400 bg-red-50 dark:bg-red-900/20 text-red-700' :
                   'border-amber-300 bg-amber-50 dark:bg-amber-900/20 text-amber-700') }}">
                {{ $bed->bed_number }}
            </div>
            @endforeach
        </div>
        <div class="px-4 pb-3 flex gap-4 text-xs text-slate-400">
            <span><span class="inline-block w-3 h-3 rounded bg-green-400 mr-1"></span>Available</span>
            <span><span class="inline-block w-3 h-3 rounded bg-red-400 mr-1"></span>Occupied</span>
            <span><span class="inline-block w-3 h-3 rounded bg-amber-400 mr-1"></span>Maintenance</span>
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-12 text-center text-slate-400">No wards configured.</div>
    @endforelse
</div>
@endsection
