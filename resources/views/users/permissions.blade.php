@extends('layouts.hms')
@section('title','Permissions — '.$user->name)
@section('breadcrumb')
    <a href="{{ route('users.index') }}" class="text-slate-400 hover:text-slate-600">Users</a>
    <span class="mx-1">/</span>
    <a href="{{ route('users.show', $user) }}" class="text-slate-400 hover:text-slate-600">{{ $user->name }}</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Permissions</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">Permissions — {{ $user->name }}</h1>
        <p class="text-sm text-slate-400">
            Roles:
            @forelse($user->roles as $role)
                <span class="capitalize">{{ str_replace('_',' ',$role->name) }}</span>@if(!$loop->last), @endif
            @empty
                <span class="italic">none</span>
            @endforelse
        </p>
    </div>
    <a href="{{ route('users.show', $user) }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

<div class="mb-4 px-4 py-3 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800/50 text-indigo-700 dark:text-indigo-400 rounded-xl text-sm flex items-start gap-2">
    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <p>Permissions marked <strong>via role</strong> are inherited from the user’s role(s) and are always granted. The checkboxes below assign <strong>extra direct permissions</strong> on top of the role.</p>
</div>

<form method="POST" action="{{ route('users.permissions.update', $user) }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
    @csrf
    @method('PUT')

    @include('partials.permission-matrix', [
        'grouped'   => $grouped,
        'checked'   => $direct,
        'inherited' => $viaRole,
    ])

    <div class="flex justify-end gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
        <a href="{{ route('users.show', $user) }}" class="btn-cancel">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Save Permissions</button>
    </div>
</form>
</div>
@endsection
