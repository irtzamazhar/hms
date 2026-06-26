@extends('layouts.hms')
@section('title','Audit Trail')
@section('breadcrumb')
    <span class="font-medium text-slate-700 dark:text-slate-200">Audit Trail</span>
@endsection

@php
    $eventColor = ['created' => 'green', 'updated' => 'blue', 'deleted' => 'red', 'restored' => 'amber'];
    $fmt = function ($v) {
        if (is_null($v) || $v === '') {
            return '—';
        }
        return is_scalar($v) ? (string) $v : json_encode($v);
    };
@endphp

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Audit Trail</h1>
            <p class="text-sm text-slate-400">{{ number_format($audits->total()) }} recorded {{ Str::plural('change', $audits->total()) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <select name="model" class="field">
                <option value="">All Records</option>
                @foreach($models as $m)
                    <option value="{{ $m }}" @selected(request('model')===$m)>{{ class_basename($m) }}</option>
                @endforeach
            </select>
            <select name="event" class="field">
                <option value="">All Actions</option>
                @foreach($events as $e)
                    <option value="{{ $e }}" @selected(request('event')===$e)>{{ ucfirst($e) }}</option>
                @endforeach
            </select>
            <select name="user_id" class="field">
                <option value="">All Users</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="field">
            <input type="date" name="to" value="{{ request('to') }}" class="field">
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg">Filter</button>
                <a href="{{ route('audit-logs.index') }}" class="btn-cancel">Reset</a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">When</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Record</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Changes</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                @forelse($audits as $audit)
                @php
                    $old = $audit->old_values ?? [];
                    $new = $audit->new_values ?? [];
                    $keys = array_keys($new + $old);
                @endphp
                <tbody x-data="{ open: false }" class="border-b border-slate-100 dark:border-slate-700/50">
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 cursor-pointer" @click="open = !open">
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $audit->created_at->format('d M Y H:i:s') }}</td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $audit->user?->name ?? 'System' }}</td>
                        <td class="px-4 py-3">
                            <x-badge color="{{ $eventColor[$audit->event] ?? 'slate' }}">{{ ucfirst($audit->event) }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                            <span class="font-medium">{{ class_basename($audit->auditable_type) }}</span>
                            <span class="text-xs text-slate-400 font-mono">#{{ $audit->auditable_id }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ count($keys) }} {{ Str::plural('field', count($keys)) }}</td>
                        <td class="px-4 py-3 text-slate-400">
                            <svg class="w-4 h-4 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </td>
                    </tr>
                    <tr x-show="open" x-cloak>
                        <td colspan="6" class="px-4 pb-4 pt-0 bg-slate-50/60 dark:bg-slate-900/30">
                            <div class="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <table class="w-full text-xs">
                                    <thead class="bg-slate-100 dark:bg-slate-700/50">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-semibold text-slate-500">Field</th>
                                            <th class="px-3 py-2 text-left font-semibold text-slate-500">Old</th>
                                            <th class="px-3 py-2 text-left font-semibold text-slate-500">New</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                        @foreach($keys as $key)
                                        <tr>
                                            <td class="px-3 py-1.5 font-medium text-slate-600 dark:text-slate-300 align-top">{{ $key }}</td>
                                            <td class="px-3 py-1.5 text-red-600 dark:text-red-400 align-top break-all">{{ $fmt($old[$key] ?? null) }}</td>
                                            <td class="px-3 py-1.5 text-green-600 dark:text-green-400 align-top break-all">{{ $fmt($new[$key] ?? null) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-slate-400 mt-2">IP: {{ $audit->ip_address ?? '—' }} · {{ $audit->url ?? '—' }}</p>
                        </td>
                    </tr>
                </tbody>
                @empty
                <tbody>
                    <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400 text-sm">No audit records found.</td></tr>
                </tbody>
                @endforelse
            </table>
        </div>
        @if($audits->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $audits->links() }}</div>
        @endif
    </div>

</div>
@endsection
