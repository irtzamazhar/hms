<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription Required — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="bg-slate-950 text-slate-200 min-h-screen flex items-center justify-center p-6">
    @php $hospital = \App\Support\Tenancy::current(); @endphp
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center">
        <div class="w-14 h-14 mx-auto mb-5 rounded-full bg-amber-500/15 flex items-center justify-center">
            <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h1 class="text-xl font-bold text-white mb-2">Subscription required</h1>
        <p class="text-sm text-slate-400 mb-1">
            @if($hospital && $hospital->status !== 'active')
                Access for <span class="font-semibold text-slate-300">{{ $hospital->name }}</span> has been suspended.
            @else
                The free trial for <span class="font-semibold text-slate-300">{{ $hospital?->name ?? 'your hospital' }}</span> has ended.
            @endif
        </p>
        <p class="text-sm text-slate-400 mb-6">Please subscribe to restore access to the system. Your data is safe.</p>

        <a href="mailto:{{ config('mail.from.address') }}?subject=HMS%20Subscription"
           class="block w-full px-5 py-2.5 bg-primary-600 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg mb-3">
            Contact us to subscribe
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-xs text-slate-500 hover:text-slate-300">Sign out</button>
        </form>
    </div>
</body>
</html>
