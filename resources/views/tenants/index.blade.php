@extends('layouts.hms')
@section('title','Tenants')
@section('breadcrumb')<span class="font-medium text-slate-700 dark:text-slate-200">Tenants</span>@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">Hospitals (Tenants)</h1>
        <p class="text-sm text-slate-400">{{ $hospitals->total() }} hospital(s) on the platform.</p>
    </div>
    <a href="{{ route('tenants.create') }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">+ New Hospital</a>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Hospital</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Users</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($hospitals as $h)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-700 dark:text-slate-200">{{ $h->name }}</p>
                    <p class="text-xs text-slate-400 font-mono">{{ $h->slug }}</p>
                </td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $h->users_count }}</td>
                <td class="px-4 py-3">
                    @if($h->status === 'suspended')
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Suspended</span>
                    @elseif($h->subscriptionActive())
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Subscribed · until {{ $h->subscribed_until?->toFormattedDateString() }}</span>
                    @elseif($h->onTrial())
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Trial · {{ $h->trialDaysLeft() }} days left</span>
                    @else
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">Expired</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('tenants.show', $h) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Manage</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No hospitals yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $hospitals->links() }}</div>
@endsection
