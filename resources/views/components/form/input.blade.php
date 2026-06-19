@props(['name', 'label' => '', 'type' => 'text', 'value' => '', 'required' => false, 'placeholder' => '', 'min' => null, 'max' => null])

<div>
    @if($label)
    <label for="{{ $name }}" class="field-label">{{ $label }}@if($required) <span class="text-red-500 ml-0.5">*</span>@endif</label>
    @endif
    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if($required) required @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($min !== null) min="{{ $min }}" @endif
        @if($max !== null) max="{{ $max }}" @endif
        {{ $attributes->class(['field', 'error' => $errors->has($name)]) }}
    >
    @error($name)
    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>
