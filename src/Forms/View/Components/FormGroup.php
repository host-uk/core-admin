<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Forms\View\Components;

use Illuminate\View\Component;

/**
 * Form group wrapper component for consistent spacing and error display.
 *
 * Features:
 * - Consistent spacing between form elements
 * - Error display from validation bag
 * - Label support
 * - Helper text support
 * - Optional required indicator
 *
 * Usage:
 * ```blade
 * <x-core-forms.form-group label="Email" for="email" error="email" required>
 *     <input type="email" id="email" wire:model="email" />
 * </x-core-forms.form-group>
 * ```
 */
class FormGroup extends Component
{
    public ?string $label;

    public ?string $for;

    public ?string $error;

    public ?string $helper;

    public bool $required;

    public string $errorMessage;

    public function __construct(
        ?string $label = null,
        ?string $for = null,
        ?string $error = null,
        ?string $helper = null,
        bool $required = false,
    ) {
        $this->label = $label;
        $this->for = $for;
        $this->error = $error;
        $this->helper = $helper;
        $this->required = $required;

        // Resolve error message from validation bag
        $this->errorMessage = $this->resolveError();
    }

    protected function resolveError(): string
    {
        if (! $this->error) {
            return '';
        }

        $errors = session('errors');

        if (! $errors) {
            return '';
        }

        return $errors->first($this->error) ?? '';
    }

    public function hasError(): bool
    {
        return ! empty($this->errorMessage);
    }

    public function render()
    {
        return view('core-forms::components.forms.form-group');
    }
}
