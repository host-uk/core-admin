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
 * Text input component with authorization support.
 *
 * Features:
 * - Authorization via `canGate` / `canResource` props
 * - Label with automatic `for` attribute
 * - Helper text support
 * - Error display from validation
 * - Dark mode support
 * - Disabled state styling
 * - Livewire and Alpine.js compatible
 *
 * Usage:
 * ```blade
 * <x-core-forms.input
 *     id="name"
 *     label="Display Name"
 *     helper="Enter a memorable display name"
 *     canGate="update"
 *     :canResource="$model"
 *     wire:model="name"
 * />
 * ```
 */
class Input extends Component
{
    use HasAuthorizationProps;

    public string $id;

    public ?string $label;

    public ?string $helper;

    public ?string $error;

    public string $type;

    public ?string $placeholder;

    public bool $disabled;

    public bool $hidden;

    public bool $required;

    public function __construct(
        string $id,
        ?string $label = null,
        ?string $helper = null,
        ?string $error = null,
        string $type = 'text',
        ?string $placeholder = null,
        bool $disabled = false,
        bool $required = false,
        // Authorization props
        ?string $canGate = null,
        mixed $canResource = null,
        bool $canHide = false,
    ) {
        $this->id = $id;
        $this->label = $label;
        $this->helper = $helper;
        $this->error = $error;
        $this->type = $type;
        $this->placeholder = $placeholder;
        $this->required = $required;

        // Authorization setup
        $this->canGate = $canGate;
        $this->canResource = $canResource;
        $this->canHide = $canHide;

        // Resolve states based on authorization
        $this->disabled = $this->resolveDisabledState($disabled);
        $this->hidden = $this->resolveHiddenState();
    }

    public function render()
    {
        return view('core-forms::components.forms.input');
    }
}
