@extends('layouts.hms')
@section('title',$user->name)
@section('breadcrumb')
    <a href="{{ route('users.index') }}" class="text-slate-400 hover:text-slate-600">Users</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">{{ $user->name }}</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">{{ $user->name }}</h1>
    @can('edit users')
    <a href="{{ route('users.edit', $user) }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg">Edit User</a>
    @endcan
</div>

<div class="grid md:grid-cols-3 gap-4">
    {{-- Profile card --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 text-center">
        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full mx-auto object-cover mb-3">
        <h2 class="font-bold text-slate-800 dark:text-white text-lg">{{ $user->name }}</h2>
        <p class="text-sm text-slate-400">{{ $user->email }}</p>
        @foreach($user->roles as $role)
        <x-badge color="blue" class="mt-2">{{ ucfirst(str_replace('_',' ',$role->name)) }}</x-badge>
        @endforeach
        <div class="mt-3">
            <x-badge color="{{ $user->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($user->status) }}</x-badge>
        </div>
    </div>

    {{-- Details --}}
    <div class="md:col-span-2 space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Account Details</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                @foreach([
                    ['Employee ID', $user->employee_id ?? '—'],
                    ['User Type', ucfirst(str_replace('_',' ',$user->user_type))],
                    ['Phone', $user->phone ?? '—'],
                    ['Joining Date', $user->joining_date?->format('d M Y') ?? '—'],
                    ['Last Login', $user->last_login_at?->format('d M Y H:i') ?? 'Never'],
                    ['2FA', $user->is_two_factor_enabled ? 'Enabled' : 'Disabled'],
                ] as [$k,$v])
                <div><dt class="text-slate-400">{{ $k }}</dt><dd class="font-medium text-slate-700 dark:text-white mt-0.5">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>

        @if($user->doctor)
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Doctor Profile</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div><dt class="text-slate-400">Doctor ID</dt><dd class="font-medium text-slate-700 dark:text-white">{{ $user->doctor->doctor_id }}</dd></div>
                <div><dt class="text-slate-400">Department</dt><dd class="font-medium text-slate-700 dark:text-white">{{ $user->doctor->department?->name ?? '—' }}</dd></div>
                <div><dt class="text-slate-400">Specialization</dt><dd class="font-medium text-slate-700 dark:text-white">{{ $user->doctor->specialization }}</dd></div>
                <div><dt class="text-slate-400">Fee</dt><dd class="font-medium text-slate-700 dark:text-white">₨ {{ number_format($user->doctor->consultation_fee, 0) }}</dd></div>
            </dl>
        </div>
        @endif

        @if($user->salaryStructure)
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Salary</h2>
            <div class="grid grid-cols-3 gap-3 text-sm">
                <div><p class="text-slate-400">Basic</p><p class="font-semibold text-slate-700 dark:text-white">₨ {{ number_format($user->salaryStructure->basic_salary, 0) }}</p></div>
                <div><p class="text-slate-400">Allowances</p><p class="font-semibold text-green-600">₨ {{ number_format($user->salaryStructure->total_allowances, 0) }}</p></div>
                <div><p class="text-slate-400">Net Salary</p><p class="font-bold text-primary-600">₨ {{ number_format($user->salaryStructure->net_salary, 0) }}</p></div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
