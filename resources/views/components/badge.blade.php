@props(['color' => 'slate'])
@php
$classes = match($color) {
    'green'  => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    'red'    => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    'blue'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    'amber'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
    default  => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
};
@endphp
<span {{ $attributes->class(["inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium $classes"]) }}>
    {{ $slot }}
</span>
