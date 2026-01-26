{{--
    Select Component

    A dropdown select with authorization support, options, and error display.

    Props:
        - id: string (required) - Select element ID
        - options: array - Options as value => label or grouped options
        - label: string|null - Label text
        - helper: string|null - Helper text below select
        - error: string|null - Error message
        - placeholder: string|null - Placeholder option text
        - multiple: bool - Allow multiple selection
        - disabled: bool - Whether select is disabled
        - required: bool - Whether select is required
        - canGate: string|null - Gate/ability to check
        - canResource: mixed|null - Resource to check against
        - canHide: bool - Hide instead of disable when unauthorized

    Usage:
        <x-core-forms.select
            id="status"
            label="Status"
            :options="['draft' => 'Draft', 'published' => 'Published']"
            placeholder="Select a status..."
            canGate="update"
            :canResource="$model"
            wire:model="status"
        />

        {{-- With grouped options --}}
        <x-core-forms.select
            id="timezone"
            :options="[
                'America' => ['America/New_York' => 'New York', 'America/Los_Angeles' => 'Los Angeles'],
                'Europe' => ['Europe/London' => 'London', 'Europe/Paris' => 'Paris'],
            ]"
        />
--}}

@if(!$hidden)
<div {{ $attributes->only(['class', 'x-show', 'x-if', 'x-cloak'])->merge(['class' => 'space-y-1']) }}>
    {{-- Label --}}
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{-- Select --}}
    <select
        id="{{ $id }}"
        name="{{ $id }}"
        @if($multiple) multiple @endif
        @if($disabled) disabled @endif
        @if($required) required @endif
        {{ $attributes->except(['class', 'x-show', 'x-if', 'x-cloak'])->class([
            'block w-full rounded-lg border px-3 py-2 text-sm transition-colors duration-200',
            'bg-white dark:bg-gray-800',
            'text-gray-900 dark:text-gray-100',
            'focus:outline-none focus:ring-2 focus:ring-offset-0',
            // Normal state
            'border-gray-300 dark:border-gray-600 focus:border-violet-500 focus:ring-violet-500/20' => !$error,
            // Error state
            'border-red-500 dark:border-red-500 focus:border-red-500 focus:ring-red-500/20' => $error,
            // Disabled state
            'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed' => $disabled,
        ]) }}
    >
        {{-- Placeholder option --}}
        @if($placeholder)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif

        {{-- Options --}}
        @foreach($normalizedOptions as $value => $labelOrGroup)
            @if(is_array($labelOrGroup))
                {{-- Optgroup --}}
                <optgroup label="{{ $value }}">
                    @foreach($labelOrGroup as $optValue => $optLabel)
                        <option value="{{ $optValue }}">{{ $optLabel }}</option>
                    @endforeach
                </optgroup>
            @else
                <option value="{{ $value }}">{{ $labelOrGroup }}</option>
            @endif
        @endforeach

        {{-- Slot for custom options --}}
        {{ $slot }}
    </select>

    {{-- Helper text --}}
    @if($helper && !$error)
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $helper }}</p>
    @endif

    {{-- Error message --}}
    @if($error)
        <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @elseif($errors->has($id))
        <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first($id) }}</p>
    @endif
</div>
@endif
