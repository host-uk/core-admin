{{--
    Checkbox Component

    A checkbox with authorization support, label positioning, and description.

    Props:
        - id: string (required) - Checkbox element ID
        - label: string|null - Label text
        - description: string|null - Description text below label
        - error: string|null - Error message
        - labelPosition: string - Label position: 'left' or 'right' (default: 'right')
        - disabled: bool - Whether checkbox is disabled
        - canGate: string|null - Gate/ability to check
        - canResource: mixed|null - Resource to check against
        - canHide: bool - Hide instead of disable when unauthorized

    Usage:
        <x-core-forms.checkbox
            id="is_active"
            label="Active"
            description="Enable this feature for users"
            canGate="update"
            :canResource="$model"
            wire:model="is_active"
        />

        {{-- Label on left --}}
        <x-core-forms.checkbox
            id="remember"
            label="Remember me"
            labelPosition="left"
            wire:model="remember"
        />
--}}

@if(!$hidden)
<div {{ $attributes->only(['class', 'x-show', 'x-if', 'x-cloak'])->merge(['class' => 'space-y-1']) }}>
    <div @class([
        'flex items-start gap-3',
        'flex-row-reverse justify-end' => $labelPosition === 'left',
    ])>
        {{-- Checkbox --}}
        <div class="flex items-center h-5">
            <input
                type="checkbox"
                id="{{ $id }}"
                name="{{ $id }}"
                @if($disabled) disabled @endif
                {{ $attributes->except(['class', 'x-show', 'x-if', 'x-cloak'])->class([
                    'h-4 w-4 rounded transition-colors duration-200',
                    'border-gray-300 dark:border-gray-600',
                    'text-violet-600 dark:text-violet-500',
                    'focus:ring-2 focus:ring-violet-500/20 focus:ring-offset-0',
                    'bg-white dark:bg-gray-800',
                    // Disabled state
                    'bg-gray-100 dark:bg-gray-900 cursor-not-allowed' => $disabled,
                ]) }}
            />
        </div>

        {{-- Label and description --}}
        @if($label || $description)
            <div class="text-sm">
                @if($label)
                    <label for="{{ $id }}" @class([
                        'font-medium',
                        'text-gray-700 dark:text-gray-300' => !$disabled,
                        'text-gray-500 dark:text-gray-500 cursor-not-allowed' => $disabled,
                    ])>
                        {{ $label }}
                    </label>
                @endif

                @if($description)
                    <p class="text-gray-500 dark:text-gray-400">{{ $description }}</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Error message --}}
    @if($error)
        <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @elseif($errors->has($id))
        <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first($id) }}</p>
    @endif
</div>
@endif
