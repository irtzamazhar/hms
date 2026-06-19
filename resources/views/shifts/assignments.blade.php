@extends('layouts.hms')
@section('title','Shift Assignments')
@section('breadcrumb')
    <a href="{{ route('shifts.index') }}" class="text-slate-400 hover:text-slate-600">Shifts</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Assignments</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Shift Assignments</h1>
</div>

<div class="grid md:grid-cols-3 gap-4">
    {{-- Assign form --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Assign Shift</h2>
        <form method="POST" action="{{ route('shifts.assign') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Employee</label>
                <select name="user_id" required class="field">
                    <option value="">— Select —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->user_type) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Shift</label>
                <select name="shift_id" required class="field">
                    <option value="">— Select —</option>
                    @foreach($shifts as $shift)
                    <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Date</label>
                <input type="date" name="assignment_date" value="{{ today()->toDateString() }}" required
                       class="field">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Notes</label>
                <input type="text" name="notes" class="field" placeholder="Optional">
            </div>
            <button type="submit" class="w-full py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Assign</button>
        </form>
    </div>

    {{-- Recent assignments --}}
    <div class="md:col-span-2">
        <form method="GET" class="flex gap-3 mb-4">
            <input type="date" name="date" value="{{ request('date') }}"
                   class="field">
            <select name="shift_id" class="field">
                <option value="">All Shifts</option>
                @foreach($shifts as $s)
                <option value="{{ $s->id }}" @selected(request('shift_id') == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Filter</button>
        </form>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Shift</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($assignments as $a)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800 dark:text-white">{{ $a->user?->name }}</p>
                            <p class="text-xs text-slate-400">{{ $a->user?->employee_id }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $a->shift?->name }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $a->assignment_date->format('d M Y') }}</td>
                        <td class="px-4 py-3"><x-badge color="{{ $a->status === 'assigned' ? 'blue' : 'green' }}">{{ ucfirst($a->status) }}</x-badge></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No assignments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($assignments->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $assignments->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
