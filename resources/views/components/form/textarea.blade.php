@props(['name', 'label' => '', 'value' => '', 'rows' => 3, 'placeholder' => '', 'required' => false])

<div>
    @if($label)
    <label for="{{ $name }}" class="field-label">{{ $label }}@if($required) <span class="text-red-500 ml-0.5">*</span>@endif</label>
    @endif
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        {{ $attributes->class(['field', 'error' => $errors->has($name)]) }}
    >{{ old($name, $value) }}</textarea>
    @error($name)
    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>
