@props(['name', 'label' => '', 'options' => [], 'selected' => '', 'required' => false, 'placeholder' => 'Select...'])

<div>
    @if($label)
    <label for="{{ $name }}" class="field-label">{{ $label }}@if($required) <span class="text-red-500 ml-0.5">*</span>@endif</label>
    @endif
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if($required) required @endif
        {{ $attributes->class(['field', 'error' => $errors->has($name)]) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $value => $text)
            <option value="{{ $value }}" @selected(old($name, $selected) == $value)>{{ $text }}</option>
        @endforeach
    </select>
    @error($name)
    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>
