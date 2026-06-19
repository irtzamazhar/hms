@extends('layouts.hms')
@section('title','Token #'.$token->token_number)
@section('breadcrumb')
    <a href="{{ route('tokens.index') }}" class="text-slate-400 hover:text-slate-600">Tokens</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Token #{{ $token->token_number }}</span>
@endsection

@section('content')
<div class="max-w-lg mx-auto space-y-4">

    {{-- Token Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 text-center">
        @if($token->priority !== 'normal')
        <p class="text-sm font-bold text-red-500 uppercase tracking-widest mb-1">⚠ {{ $token->priority }}</p>
        @endif
        <p class="text-slate-400 text-sm">Token Number</p>
        <p class="text-8xl font-black text-primary-600 leading-none my-3">{{ $token->token_number }}</p>
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ ucfirst($token->shift) }} Shift · {{ $token->token_date->format('d M Y') }}</p>
    </div>

    {{-- Details --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
        @foreach([
            ['Patient',    $token->patient->name . ' (' . $token->patient->mr_number . ')'],
            ['Doctor',     $token->doctor ? 'Dr. '.$token->doctor->user->name.' — '.$token->doctor->specialization : '—'],
            ['Department', $token->department->name ?? '—'],
            ['Status',     ucfirst(str_replace('_',' ',$token->status))],
            ['Notes',      $token->notes ?? '—'],
        ] as [$label, $value])
        <div class="px-5 py-3 flex items-start gap-4">
            <span class="text-sm text-slate-400 w-28 flex-shrink-0">{{ $label }}</span>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $value }}</span>
        </div>
        @endforeach
    </div>

    {{-- Actions --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex flex-wrap gap-3">
        <a href="{{ route('tokens.print', $token) }}" target="_blank"
           class="px-4 py-2 bg-slate-800 dark:bg-slate-600 text-white text-sm rounded-lg hover:bg-slate-700">
            🖨 Print Slip
        </a>
        @can('manage tokens')
        @foreach(['waiting'=>'Waiting','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled','no_show'=>'No Show'] as $status=>$label)
        @if($status !== $token->status)
        <form method="POST" action="{{ route('tokens.status', $token) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="{{ $status }}">
            <button type="submit"
                    class="px-4 py-2 text-sm rounded-lg font-medium
                    {{ $status==='completed' ? 'bg-green-100 text-green-700 hover:bg-green-200' :
                       ($status==='in_progress' ? 'bg-blue-100 text-blue-700 hover:bg-blue-200' :
                       ($status==='cancelled' ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200')) }}">
                Mark {{ $label }}
            </button>
        </form>
        @endif
        @endforeach
        <a href="{{ route('opd.create', ['patient_id'=>$token->patient_id,'doctor_id'=>$token->doctor_id,'token_id'=>$token->id]) }}"
           class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 font-medium">
            Start OPD Visit →
        </a>
        @endcan
    </div>

</div>
@endsection
