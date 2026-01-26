{{--
    Input Component

    A text input with authorization support, labels, helper text, and error display.

    Props:
        - id: string (required) - Input element ID
        - label: string|null - Label text
        - helper: string|null - Helper text below input
        - error: string|null - Error message (auto-resolved from validation bag if not provided)
        - type: string - Input type (text, email, password, etc.)
        - placeholder: string|null - Placeholder text
        - disabled: bool - Whether input is disabled
        - required: bool - Whether input is required
        - canGate: string|null - Gate/ability to check
        - canResource: mixed|null - Resource to check against
        - canHide: bool - Hide instead of disable when unauthorized

    Usage:
        <x-core-forms.input
            id="name"
            label="Display Name"
            helper="Enter a memorable name"
            canGate="update"
            :canResource="$model"
            wire:model="name"
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

    {{-- Input --}}
    <input
        type="{{ $type }}"
        id="{{ $id }}"
        name="{{ $id }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($disabled) disabled @endif
        @if($required) required @endif
        {{ $attributes->except(['class', 'x-show', 'x-if', 'x-cloak'])->class([
            'block w-full rounded-lg border px-3 py-2 text-sm transition-colors duration-200',
            'bg-white dark:bg-gray-800',
            'text-gray-900 dark:text-gray-100',
            'placeholder-gray-400 dark:placeholder-gray-500',
            'focus:outline-none focus:ring-2 focus:ring-offset-0',
            // Normal state
            'border-gray-300 dark:border-gray-600 focus:border-violet-500 focus:ring-violet-500/20' => !$error,
            // Error state
            'border-red-500 dark:border-red-500 focus:border-red-500 focus:ring-red-500/20' => $error,
            // Disabled state
            'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed' => $disabled,
        ]) }}
    />

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
