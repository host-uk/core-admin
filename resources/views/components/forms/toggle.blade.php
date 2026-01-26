{{--
    Toggle Component

    A toggle switch with authorization support and instant save capability.

    Props:
        - id: string (required) - Toggle element ID
        - label: string|null - Label text
        - description: string|null - Description text
        - error: string|null - Error message
        - size: string - Toggle size: sm, md, lg
        - instantSave: bool - Enable instant save on change
        - instantSaveMethod: string|null - Livewire method to call on change
        - disabled: bool - Whether toggle is disabled
        - canGate: string|null - Gate/ability to check
        - canResource: mixed|null - Resource to check against
        - canHide: bool - Hide instead of disable when unauthorized

    Usage:
        <x-core-forms.toggle
            id="is_public"
            label="Public"
            description="Make this visible to everyone"
            canGate="update"
            :canResource="$model"
            wire:model="is_public"
        />

        {{-- With instant save --}}
        <x-core-forms.toggle
            id="notifications"
            label="Notifications"
            instantSave
            instantSaveMethod="savePreferences"
            wire:model.live="notifications"
        />
--}}

@if(!$hidden)
<div {{ $attributes->only(['class', 'x-show', 'x-if', 'x-cloak'])->merge(['class' => 'space-y-1']) }}>
    <div class="flex items-center justify-between gap-4">
        {{-- Label and description --}}
        @if($label || $description)
            <div class="flex-1">
                @if($label)
                    <label for="{{ $id }}" @class([
                        'block text-sm font-medium',
                        'text-gray-700 dark:text-gray-300' => !$disabled,
                        'text-gray-500 dark:text-gray-500' => $disabled,
                    ])>
                        {{ $label }}
                    </label>
                @endif

                @if($description)
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
                @endif
            </div>
        @endif

        {{-- Toggle switch --}}
        <button
            type="button"
            role="switch"
            id="{{ $id }}"
            @if($disabled) disabled @endif
            x-data="{ enabled: $wire?.entangle?.('{{ $id }}') ?? false }"
            x-on:click="enabled = !enabled; $el.setAttribute('aria-checked', enabled)"
            :aria-checked="enabled"
            @if($instantSave && $wireChange())
                x-on:click.debounce.300ms="$wire.{{ $wireChange() }}()"
            @endif
            {{ $attributes->except(['class', 'x-show', 'x-if', 'x-cloak', 'wire:model', 'wire:model.live', 'wire:model.defer'])->class([
                'relative inline-flex shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out',
                'focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
                'cursor-pointer' => !$disabled,
                'cursor-not-allowed opacity-60' => $disabled,
                $trackClasses,
            ]) }}
            :class="enabled ? 'bg-violet-600' : 'bg-gray-200 dark:bg-gray-700'"
        >
            <span class="sr-only">{{ $label ?? 'Toggle' }}</span>
            <span
                aria-hidden="true"
                class="pointer-events-none inline-block rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $thumbClasses }}"
                :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                x-bind:class="{
                    'translate-x-5': enabled && '{{ $size }}' === 'md',
                    'translate-x-4': enabled && '{{ $size }}' === 'sm',
                    'translate-x-7': enabled && '{{ $size }}' === 'lg',
                    'translate-x-0': !enabled
                }"
            ></span>
        </button>
    </div>

    {{-- Error message --}}
    @if($error)
        <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @elseif($errors->has($id))
        <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first($id) }}</p>
    @endif
</div>
@endif
