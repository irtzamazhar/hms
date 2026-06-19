@props(['title', 'subtitle' => '', 'action' => null, 'actionLabel' => '', 'actionRoute' => ''])

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">{{ $title }}</h1>
        @if($subtitle)<p class="text-sm text-slate-400 mt-0.5">{{ $subtitle }}</p>@endif
    </div>
    @if($action)
    <a href="{{ $actionRoute }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        {{ $actionLabel }}
    </a>
    @endif
    {{ $slot }}
</div>
