@extends('layouts.hms')
@section('title','New Token')
@section('breadcrumb')
    <a href="{{ route('tokens.index') }}" class="text-slate-400 hover:text-slate-600">Tokens</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">New Token</span>
@endsection

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-slate-800 dark:text-white">Generate Token</h1>
                <p class="text-sm text-slate-400">{{ now()->format('d M Y') }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-slate-400">Next Token No.</p>
                <p class="text-4xl font-black text-primary-600" id="tokenPreview">{{ $nextToken }}</p>
                <p class="text-xs text-slate-400 uppercase">{{ ucfirst($shift) }} shift</p>
            </div>
        </div>

        <form method="POST" action="{{ route('tokens.store') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Patient *</label>
                <select name="patient_id" required class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Search patient…</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" @selected(old('patient_id') == $p->id)>{{ $p->name }} ({{ $p->mr_number }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Shift *</label>
                <select name="shift" id="shiftSelect" required class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                    @foreach(['morning'=>'Morning','evening'=>'Evening','night'=>'Night'] as $val=>$label)
                        <option value="{{ $val }}" @selected(old('shift',$shift)===$val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Doctor</label>
                <select name="doctor_id" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                    <option value="">— Walk-in / No preference —</option>
                    @foreach($doctors as $d)
                        <option value="{{ $d->id }}" @selected(old('doctor_id')==$d->id)>Dr. {{ $d->user->name }} ({{ $d->specialization }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Department</label>
                <select name="department_id" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                    <option value="">— Any —</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id')==$dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Priority</label>
                <select name="priority" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                    <option value="normal" @selected(old('priority','normal')==='normal')>Normal</option>
                    <option value="urgent" @selected(old('priority')==='urgent')>Urgent</option>
                    <option value="emergency" @selected(old('priority')==='emergency')>Emergency</option>
                </select>
            </div>

            <x-form.textarea name="notes" label="Notes" :value="old('notes')" rows="2" />

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">
                    Generate Token #{{ $nextToken }}
                </button>
                <a href="{{ route('tokens.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm rounded-lg hover:bg-slate-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
