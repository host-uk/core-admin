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
 * Checkbox component with authorization support.
 *
 * Features:
 * - Authorization via `canGate` / `canResource` props
 * - Label positioning (left/right)
 * - Description text
 * - Error display from validation
 * - Dark mode support
 *
 * Usage:
 * ```blade
 * <x-core-forms.checkbox
 *     id="is_active"
 *     label="Active"
 *     description="Enable this feature for users"
 *     canGate="update"
 *     :canResource="$model"
 *     wire:model="is_active"
 * />
 * ```
 */
class Checkbox extends Component
{
    use HasAuthorizationProps;

    public string $id;

    public ?string $label;

    public ?string $description;

    public ?string $error;

    public string $labelPosition;

    public bool $disabled;

    public bool $hidden;

    public function __construct(
        string $id,
        ?string $label = null,
        ?string $description = null,
        ?string $error = null,
        string $labelPosition = 'right',
        bool $disabled = false,
        // Authorization props
        ?string $canGate = null,
        mixed $canResource = null,
        bool $canHide = false,
    ) {
        $this->id = $id;
        $this->label = $label;
        $this->description = $description;
        $this->error = $error;
        $this->labelPosition = $labelPosition;

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
        return view('core-forms::components.forms.checkbox');
    }
}
