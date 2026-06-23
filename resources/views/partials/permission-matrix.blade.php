{{--
    Reusable permission checkbox matrix.

    Expects:
      $grouped    array<string, Collection<Permission>>  grouped by module label
      $checked    array<string>   permission names that are checked & editable   (default [])
      $inherited  array<string>   permission names shown checked + disabled, tagged "via role" (default [])
      $locked     bool            disable the whole matrix (read-only)            (default false)
--}}
@php
    $checked   = $checked   ?? [];
    $inherited = $inherited ?? [];
    $locked    = $locked    ?? false;
@endphp

<div x-data="{ q: '' }">
    <div class="flex items-center gap-3 mb-4">
        <div class="relative flex-1 max-w-xs">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="q" placeholder="Filter permissions…" class="field pl-9">
        </div>
        @unless($locked)
        <p class="text-xs text-slate-400 hidden sm:block">Tip: use a group’s checkbox to toggle all of its permissions.</p>
        @endunless
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        @foreach($grouped as $label => $permissions)
        <div data-group
             x-show="permVisible($el, q)"
             class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-700/40 px-4 py-2.5">
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $label }}</span>
                @unless($locked)
                <label class="inline-flex items-center gap-1.5 text-xs text-slate-400 cursor-pointer select-none">
                    All
                    <input type="checkbox"
                           @change="$el.closest('[data-group]').querySelectorAll('input.perm-check:not(:disabled)').forEach(c => c.checked = $el.checked)"
                           class="rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500">
                </label>
                @endunless
            </div>
            <div class="p-2 divide-y divide-slate-50 dark:divide-slate-700/50">
                @foreach($permissions as $perm)
                @php
                    $isInherited = in_array($perm->name, $inherited, true);
                    $isChecked   = $isInherited || in_array($perm->name, $checked, true);
                    $isDisabled  = $locked || $isInherited;
                @endphp
                <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/30 cursor-pointer"
                       data-perm="{{ $perm->name }}"
                       x-show="!q || '{{ $perm->name }}'.includes(q.toLowerCase())">
                    {{-- Disabled inputs are not submitted; inherited perms persist through the role anyway. --}}
                    <input type="checkbox"
                           class="perm-check rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500 disabled:opacity-50"
                           name="permissions[]"
                           value="{{ $perm->name }}"
                           @checked($isChecked)
                           @disabled($isDisabled)>
                    <span class="text-sm text-slate-600 dark:text-slate-300 capitalize">{{ $perm->name }}</span>
                    @if($isInherited)
                    <x-badge color="indigo" class="ml-auto !text-[10px] !px-1.5 !py-0">via role</x-badge>
                    @endif
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

@once
@push('scripts')
<script>
    // Hide a group card when the current filter matches none of its permissions.
    function permVisible(card, q) {
        if (!q) return true;
        q = q.toLowerCase();
        return [...card.querySelectorAll('[data-perm]')].some(el => el.dataset.perm.includes(q));
    }
</script>
@endpush
@endonce
