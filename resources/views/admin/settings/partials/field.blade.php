@php
    $type = $field['type'] ?? 'text';
    $label = $field['label'] ?? $key;
    $hint = $field['hint'] ?? null;
    $required = in_array('required', $field['rules'] ?? [], true);
    $width = $field['width'] ?? 12;
    $current = old($key, $type === 'password' ? '' : $value);
    $invalid = $errors->has($key) ? ' is-invalid' : '';
@endphp

<div class="col-md-{{ $width }}">
    @if ($type === 'boolean')
        <label class="form-label d-block">{{ $label }}</label>
        <input type="hidden" name="{{ $key }}" value="0">
        <div class="form-check form-switch">
            <input type="checkbox" name="{{ $key }}" id="{{ $key }}" value="1"
                   class="form-check-input{{ $invalid }}" @checked((bool) $current)>
            <label class="form-check-label small text-secondary" for="{{ $key }}">
                {{ $hint ?? 'Enable' }}
            </label>
        </div>

    @elseif ($type === 'image')
        <label for="{{ $key }}" class="form-label">{{ $label }}</label>
        @if ($existing = \App\Models\Setting::url($key))
            <div class="d-flex align-items-center gap-2 mb-2">
                <img src="{{ $existing }}" alt="{{ $label }}" class="settings-preview">
                <div class="form-check">
                    <input type="checkbox" name="remove_{{ $key }}" id="remove_{{ $key }}" value="1"
                           class="form-check-input">
                    <label for="remove_{{ $key }}" class="form-check-label small text-danger">Remove</label>
                </div>
            </div>
        @endif
        <input type="file" name="{{ $key }}" id="{{ $key }}" accept="image/*"
               class="form-control{{ $invalid }}">

    @elseif ($type === 'select')
        <label for="{{ $key }}" class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>
        <select name="{{ $key }}" id="{{ $key }}" class="form-select{{ $invalid }}">
            <option value="">— Select —</option>
            @foreach ($field['options'] ?? [] as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>

    @elseif ($type === 'textarea')
        <label for="{{ $key }}" class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>
        <textarea name="{{ $key }}" id="{{ $key }}" rows="3"
                  class="form-control{{ $invalid }}">{{ $current }}</textarea>

    @elseif ($type === 'color')
        <label for="{{ $key }}" class="form-label">{{ $label }}</label>
        <div class="input-group">
            <input type="color" id="{{ $key }}_picker" class="form-control form-control-color"
                   value="{{ $current ?: '#4f46e5' }}"
                   oninput="document.getElementById('{{ $key }}').value = this.value">
            <input type="text" name="{{ $key }}" id="{{ $key }}"
                   class="form-control{{ $invalid }}" value="{{ $current }}" placeholder="#4f46e5">
        </div>

    @else
        <label for="{{ $key }}" class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>
        <input type="{{ $type }}" name="{{ $key }}" id="{{ $key }}"
               class="form-control{{ $invalid }}" value="{{ $current }}"
               @if ($type === 'password') autocomplete="new-password" @endif>
    @endif

    @error($key)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror

    @if ($hint && $type !== 'boolean')
        <div class="form-text">{{ $hint }}</div>
    @endif
</div>
