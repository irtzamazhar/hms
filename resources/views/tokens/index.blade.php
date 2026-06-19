@extends('layouts.hms')
@section('title','Token Management')
@section('breadcrumb')
    <span class="text-slate-400">OPD</span> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Tokens</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">Token Management</h1>
        <p class="text-sm text-slate-400">{{ $date }} — {{ ucfirst($shift) }} shift</p>
    </div>
    @can('create tokens')
    <a href="{{ route('tokens.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Token
    </a>
    @endcan
</div>

{{-- Shift & Date Filter --}}
<form method="GET" class="flex flex-wrap gap-3 mb-4">
    <input type="date" name="date" value="{{ $date }}" class="field">
    @foreach(['all','morning','evening','night'] as $s)
    <a href="{{ request()->fullUrlWithQuery(['shift' => $s, 'date' => $date]) }}"
       class="px-4 py-2 text-sm rounded-lg font-medium transition-colors {{ $shift === $s ? 'bg-primary-600 text-white' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50' }}">
        {{ ucfirst($s) }}
    </a>
    @endforeach
    <button type="submit" class="px-4 py-2 bg-slate-800 dark:bg-slate-600 text-white text-sm rounded-lg">Go</button>
</form>

{{-- Token Grid --}}
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-4">
    @php
    $statusColors = [
        'waiting'     => 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800',
        'in_progress' => 'border-blue-400 bg-blue-50 dark:bg-blue-900/20',
        'completed'   => 'border-green-400 bg-green-50 dark:bg-green-900/20',
        'cancelled'   => 'border-red-300 bg-red-50 dark:bg-red-900/20 opacity-60',
        'no_show'     => 'border-slate-300 bg-slate-50 dark:bg-slate-700 opacity-60',
    ];
    @endphp

    @forelse($tokens as $token)
    <a href="{{ route('tokens.show', $token) }}"
       class="rounded-xl border-2 p-3 text-center hover:shadow-md transition-all cursor-pointer {{ $statusColors[$token->status] ?? $statusColors['waiting'] }}">
        @if($token->priority !== 'normal')
        <span class="text-xs text-red-500 font-semibold uppercase">{{ $token->priority }}</span>
        @endif
        <p class="text-3xl font-black text-slate-800 dark:text-white">{{ $token->token_number }}</p>
        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 truncate mt-0.5">{{ $token->patient->name }}</p>
        <p class="text-xs text-slate-400 truncate">{{ $token->doctor ? 'Dr. '.Str::words($token->doctor->user->name,1,'') : ($token->department->name ?? '—') }}</p>
        <span class="inline-block mt-1 text-xs px-1.5 py-0.5 rounded-full
            {{ $token->status === 'completed' ? 'bg-green-100 text-green-700' :
               ($token->status === 'in_progress' ? 'bg-blue-100 text-blue-700' :
               ($token->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-500')) }}">
            {{ ucfirst(str_replace('_',' ',$token->status)) }}
        </span>
    </a>
    @empty
    <div class="col-span-full py-16 text-center">
        <p class="text-slate-400 text-sm">No tokens for this shift/date.</p>
        @can('create tokens')<a href="{{ route('tokens.create') }}" class="text-primary-600 hover:underline text-sm">Generate first token →</a>@endcan
    </div>
    @endforelse
</div>

{{-- Legend --}}
<div class="flex flex-wrap gap-3 text-xs text-slate-500">
    @foreach(['waiting' => 'slate','in_progress' => 'blue','completed' => 'green','cancelled' => 'red','no_show' => 'slate'] as $status => $color)
    <span class="flex items-center gap-1">
        <span class="w-3 h-3 rounded-sm border-2 border-{{ $color }}-400 {{ $status === 'completed' ? 'bg-green-100' : ($status === 'in_progress' ? 'bg-blue-100' : 'bg-slate-100') }}"></span>
        {{ ucfirst(str_replace('_',' ',$status)) }}
    </span>
    @endforeach
</div>
@endsection
