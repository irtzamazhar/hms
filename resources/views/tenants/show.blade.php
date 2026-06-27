@extends('layouts.hms')
@section('title', $tenant->name)
@section('breadcrumb')
    <a href="{{ route('tenants.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Tenants</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">{{ $tenant->name }}</span>
@endsection

@section('content')
<div class="max-w-3xl space-y-4">

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-lg font-bold text-slate-800 dark:text-white">{{ $tenant->name }}</h1>
                <p class="text-sm text-slate-400 font-mono">{{ $tenant->slug }} · {{ $tenant->users_count }} user(s)</p>
            </div>
            <div class="text-right text-sm">
                @if($tenant->status === 'suspended')
                    <span class="text-red-600 dark:text-red-400 font-semibold">Suspended</span>
                @elseif($tenant->subscriptionActive())
                    <span class="text-green-600 dark:text-green-400 font-semibold">Subscribed</span>
                    <p class="text-xs text-slate-400">until {{ $tenant->subscribed_until?->toFormattedDateString() }}</p>
                @elseif($tenant->onTrial())
                    <span class="text-amber-600 dark:text-amber-400 font-semibold">Trial · {{ $tenant->trialDaysLeft() }} days left</span>
                    <p class="text-xs text-slate-400">ends {{ $tenant->trial_ends_at?->toFormattedDateString() }}</p>
                @else
                    <span class="text-slate-500 font-semibold">Trial expired</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Mark subscribed --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Mark Subscribed (manual)</h2>
        <form method="POST" action="{{ route('tenants.subscribe', $tenant) }}" class="flex flex-wrap items-end gap-3">
            @csrf @method('PATCH')
            <div><label class="field-label">Subscribed until</label><input type="date" name="subscribed_until" required class="field" value="{{ old('subscribed_until', now()->addYear()->toDateString()) }}"></div>
            <div><label class="field-label">Plan</label><input type="text" name="plan" class="field" value="{{ old('plan', 'standard') }}"></div>
            <button class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">Activate</button>
        </form>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        {{-- Extend trial --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Extend Trial</h2>
            <form method="POST" action="{{ route('tenants.extend-trial', $tenant) }}" class="flex items-end gap-3">
                @csrf @method('PATCH')
                <div><label class="field-label">Add days</label><input type="number" name="days" min="1" max="365" value="30" class="field w-28"></div>
                <button class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg">Extend</button>
            </form>
        </div>

        {{-- Suspend / reactivate --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Access</h2>
            <form method="POST" action="{{ route('tenants.status', $tenant) }}">
                @csrf @method('PATCH')
                @if($tenant->status === 'active')
                    <input type="hidden" name="status" value="suspended">
                    <button class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg">Suspend access</button>
                @else
                    <input type="hidden" name="status" value="active">
                    <button class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">Reactivate</button>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
