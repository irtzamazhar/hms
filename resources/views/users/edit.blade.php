@extends('layouts.hms')
@section('title','Edit User — '.$user->name)
@section('breadcrumb')
    <a href="{{ route('users.index') }}" class="text-slate-400 hover:text-slate-600">Users</a>
    <span class="mx-1">/</span>
    <a href="{{ route('users.show', $user) }}" class="text-slate-400 hover:text-slate-600">{{ $user->name }}</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Edit</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Edit User</h1>
    <a href="{{ route('users.show', $user) }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

<form method="POST" action="{{ route('users.update', $user) }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="name" label="Full Name" :value="$user->name" required />
        <x-form.input name="email" type="email" label="Email Address" :value="$user->email" required />
    </div>

    <x-form.input name="phone" label="Phone" :value="$user->phone" />

    <x-form.select name="role" label="System Role" required>
        @foreach($roles as $role)
        <option value="{{ $role->name }}" @selected($user->hasRole($role->name))>{{ ucfirst(str_replace('_',' ',$role->name)) }}</option>
        @endforeach
    </x-form.select>

    <div class="border-t border-slate-200 dark:border-slate-700 pt-4">
        <p class="text-xs text-slate-400 mb-3">Leave password blank to keep unchanged.</p>
        <div class="grid grid-cols-2 gap-4">
            <x-form.input name="password" type="password" label="New Password" />
            <x-form.input name="password_confirmation" type="password" label="Confirm Password" />
        </div>
    </div>

    <x-form.select name="status" label="Status" required>
        <option value="active" @selected($user->status === 'active')>Active</option>
        <option value="inactive" @selected($user->status === 'inactive')>Inactive</option>
    </x-form.select>

    <div class="flex justify-between items-center pt-2">
        @can('manage users')
        @if($user->id !== auth()->id())
        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-sm text-red-500 hover:text-red-700">Delete User</button>
        </form>
        @else
        <span></span>
        @endif
        @endcan
        <div class="flex gap-3">
            <a href="{{ route('users.show', $user) }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Update User</button>
        </div>
    </div>
</form>
</div>
@endsection
