{{--
    Form Group Component

    A wrapper component for consistent form field spacing and error display.

    Props:
        - label: string|null - Label text
        - for: string|null - ID of the form element (for label)
        - error: string|null - Error bag key to check
        - helper: string|null - Helper text
        - required: bool - Show required indicator

    Usage:
        <x-core-forms.form-group label="Email" for="email" error="email" required>
            <input type="email" id="email" wire:model="email" />
        </x-core-forms.form-group>

        {{-- Without label --}}
        <x-core-forms.form-group error="terms">
            <x-core-forms.checkbox id="terms" label="I agree to the terms" />
        </x-core-forms.form-group>
--}}

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    {{-- Label --}}
    @if($label)
        <label
            @if($for) for="{{ $for }}" @endif
            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
        >
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{-- Content slot --}}
    {{ $slot }}

    {{-- Helper text --}}
    @if($helper && !$hasError())
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $helper }}</p>
    @endif

    {{-- Error message --}}
    @if($hasError())
        <p class="text-sm text-red-600 dark:text-red-400">{{ $errorMessage }}</p>
    @endif
</div>
