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
 * Textarea component with authorization support.
 *
 * Features:
 * - Authorization via `canGate` / `canResource` props
 * - Configurable rows
 * - Auto-resize option (via Alpine.js)
 * - Label with automatic `for` attribute
 * - Helper text support
 * - Error display from validation
 * - Dark mode support
 *
 * Usage:
 * ```blade
 * <x-core-forms.textarea
 *     id="description"
 *     label="Description"
 *     rows="4"
 *     autoResize
 *     canGate="update"
 *     :canResource="$model"
 *     wire:model="description"
 * />
 * ```
 */
class Textarea extends Component
{
    use HasAuthorizationProps;

    public string $id;

    public ?string $label;

    public ?string $helper;

    public ?string $error;

    public ?string $placeholder;

    public int $rows;

    public bool $autoResize;

    public bool $disabled;

    public bool $hidden;

    public bool $required;

    public function __construct(
        string $id,
        ?string $label = null,
        ?string $helper = null,
        ?string $error = null,
        ?string $placeholder = null,
        int $rows = 3,
        bool $autoResize = false,
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
        $this->placeholder = $placeholder;
        $this->rows = $rows;
        $this->autoResize = $autoResize;
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
        return view('core-forms::components.forms.textarea');
    }
}
