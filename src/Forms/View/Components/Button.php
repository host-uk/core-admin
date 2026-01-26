<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Forms\View\Components;

use Core\Admin\Forms\Concerns\HasAuthorizationProps;
use Illuminate\View\Component;

/**
 * Button component with authorization support.
 *
 * Features:
 * - Authorization via `canGate` / `canResource` props (disables or hides)
 * - Variants: primary, secondary, danger, ghost
 * - Loading state support (with wire:loading integration)
 * - Icon support (left and right positions)
 * - Size variants: sm, md, lg
 * - Dark mode support
 *
 * Usage:
 * ```blade
 * <x-core-forms.button
 *     variant="primary"
 *     icon="check"
 *     canGate="update"
 *     :canResource="$model"
 * >
 *     Save Changes
 * </x-core-forms.button>
 *
 * <x-core-forms.button
 *     variant="danger"
 *     canGate="delete"
 *     :canResource="$model"
 *     canHide
 * >
 *     Delete
 * </x-core-forms.button>
 * ```
 */
class Button extends Component
{
    use HasAuthorizationProps;

    public string $type;

    public string $variant;

    public string $size;

    public ?string $icon;

    public ?string $iconRight;

    public bool $loading;

    public ?string $loadingText;

    public bool $disabled;

    public bool $hidden;

    public string $variantClasses;

    public string $sizeClasses;

    public function __construct(
        string $type = 'button',
        string $variant = 'primary',
        string $size = 'md',
        ?string $icon = null,
        ?string $iconRight = null,
        bool $loading = false,
        ?string $loadingText = null,
        bool $disabled = false,
        // Authorization props
        ?string $canGate = null,
        mixed $canResource = null,
        bool $canHide = false,
    ) {
        $this->type = $type;
        $this->variant = $variant;
        $this->size = $size;
        $this->icon = $icon;
        $this->iconRight = $iconRight;
        $this->loading = $loading;
        $this->loadingText = $loadingText;

        // Authorization setup
        $this->canGate = $canGate;
        $this->canResource = $canResource;
        $this->canHide = $canHide;

        // Resolve states based on authorization
        $this->disabled = $this->resolveDisabledState($disabled);
        $this->hidden = $this->resolveHiddenState();

        // Resolve variant and size classes
        $this->variantClasses = $this->resolveVariantClasses();
        $this->sizeClasses = $this->resolveSizeClasses();
    }

    protected function resolveVariantClasses(): string
    {
        return match ($this->variant) {
            'primary' => 'bg-violet-600 hover:bg-violet-700 text-white focus:ring-violet-500 disabled:bg-violet-400',
            'secondary' => 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 focus:ring-gray-500 disabled:bg-gray-100 disabled:dark:bg-gray-800',
            'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500 disabled:bg-red-400',
            'ghost' => 'bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-gray-500',
            default => 'bg-violet-600 hover:bg-violet-700 text-white focus:ring-violet-500 disabled:bg-violet-400',
        };
    }

    protected function resolveSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'px-3 py-1.5 text-sm',
            'lg' => 'px-6 py-3 text-base',
            default => 'px-4 py-2 text-sm',
        };
    }

    public function render()
    {
        return view('core-forms::components.forms.button');
    }
}
