{{--
    Textarea Component

    A textarea with authorization support, auto-resize, labels, and error display.

    Props:
        - id: string (required) - Textarea element ID
        - label: string|null - Label text
        - helper: string|null - Helper text below textarea
        - error: string|null - Error message
        - placeholder: string|null - Placeholder text
        - rows: int - Number of visible rows (default: 3)
        - autoResize: bool - Enable auto-resize via Alpine.js
        - disabled: bool - Whether textarea is disabled
        - required: bool - Whether textarea is required
        - canGate: string|null - Gate/ability to check
        - canResource: mixed|null - Resource to check against
        - canHide: bool - Hide instead of disable when unauthorized

    Usage:
        <x-core-forms.textarea
            id="description"
            label="Description"
            rows="4"
            autoResize
            canGate="update"
            :canResource="$model"
            wire:model="description"
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

    {{-- Textarea --}}
    <textarea
        id="{{ $id }}"
        name="{{ $id }}"
        rows="{{ $rows }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($disabled) disabled @endif
        @if($required) required @endif
        @if($autoResize)
            x-data="{ resize: () => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' } }"
            x-init="resize()"
            x-on:input="resize()"
            style="overflow: hidden;"
        @endif
        {{ $attributes->except(['class', 'x-show', 'x-if', 'x-cloak'])->class([
            'block w-full rounded-lg border px-3 py-2 text-sm transition-colors duration-200',
            'bg-white dark:bg-gray-800',
            'text-gray-900 dark:text-gray-100',
            'placeholder-gray-400 dark:placeholder-gray-500',
            'focus:outline-none focus:ring-2 focus:ring-offset-0',
            'resize-y' => !$autoResize,
            'resize-none' => $autoResize,
            // Normal state
            'border-gray-300 dark:border-gray-600 focus:border-violet-500 focus:ring-violet-500/20' => !$error,
            // Error state
            'border-red-500 dark:border-red-500 focus:border-red-500 focus:ring-red-500/20' => $error,
            // Disabled state
            'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed' => $disabled,
        ]) }}
    >{{ $slot }}</textarea>

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
