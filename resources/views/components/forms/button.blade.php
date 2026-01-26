{{--
    Button Component

    A button with authorization support, variants, loading states, and icons.

    Props:
        - type: string - Button type (button, submit, reset)
        - variant: string - Button style: primary, secondary, danger, ghost
        - size: string - Button size: sm, md, lg
        - icon: string|null - Icon name (left position)
        - iconRight: string|null - Icon name (right position)
        - loading: bool - Show loading state
        - loadingText: string|null - Text to show during loading
        - disabled: bool - Whether button is disabled
        - canGate: string|null - Gate/ability to check
        - canResource: mixed|null - Resource to check against
        - canHide: bool - Hide instead of disable when unauthorized

    Usage:
        <x-core-forms.button variant="primary" icon="check">
            Save Changes
        </x-core-forms.button>

        <x-core-forms.button
            variant="danger"
            canGate="delete"
            :canResource="$model"
            canHide
        >
            Delete
        </x-core-forms.button>

        {{-- With loading state --}}
        <x-core-forms.button
            variant="primary"
            wire:click="save"
            wire:loading.attr="disabled"
            loadingText="Saving..."
        >
            <span wire:loading.remove>Save</span>
            <span wire:loading>Saving...</span>
        </x-core-forms.button>
--}}

@if(!$hidden)
<button
    type="{{ $type }}"
    @if($disabled) disabled @endif
    {{ $attributes->class([
        'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition-all duration-200',
        'focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
        'disabled:cursor-not-allowed disabled:opacity-60',
        $variantClasses,
        $sizeClasses,
    ]) }}
>
    {{-- Loading spinner (wire:loading compatible) --}}
    @if($loading)
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif

    {{-- Left icon --}}
    @if($icon && !$loading)
        <flux:icon :name="$icon" class="w-4 h-4" />
    @endif

    {{-- Button content --}}
    @if($loading && $loadingText)
        {{ $loadingText }}
    @else
        {{ $slot }}
    @endif

    {{-- Right icon --}}
    @if($iconRight)
        <flux:icon :name="$iconRight" class="w-4 h-4" />
    @endif
</button>
@endif
