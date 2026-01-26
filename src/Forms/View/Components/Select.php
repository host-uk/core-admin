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
 * Select dropdown component with authorization support.
 *
 * Features:
 * - Authorization via `canGate` / `canResource` props
 * - Options array support (value => label or flat array)
 * - Placeholder option
 * - Multiple selection support
 * - Label with automatic `for` attribute
 * - Helper text support
 * - Error display from validation
 * - Dark mode support
 *
 * Usage:
 * ```blade
 * <x-core-forms.select
 *     id="status"
 *     label="Status"
 *     :options="['draft' => 'Draft', 'published' => 'Published']"
 *     placeholder="Select a status..."
 *     canGate="update"
 *     :canResource="$model"
 *     wire:model="status"
 * />
 * ```
 */
class Select extends Component
{
    use HasAuthorizationProps;

    public string $id;

    public ?string $label;

    public ?string $helper;

    public ?string $error;

    public ?string $placeholder;

    public array $options;

    public array $normalizedOptions;

    public bool $multiple;

    public bool $disabled;

    public bool $hidden;

    public bool $required;

    public function __construct(
        string $id,
        array $options = [],
        ?string $label = null,
        ?string $helper = null,
        ?string $error = null,
        ?string $placeholder = null,
        bool $multiple = false,
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
        $this->options = $options;
        $this->multiple = $multiple;
        $this->required = $required;

        // Normalize options to value => label format
        $this->normalizedOptions = $this->normalizeOptions($options);

        // Authorization setup
        $this->canGate = $canGate;
        $this->canResource = $canResource;
        $this->canHide = $canHide;

        // Resolve states based on authorization
        $this->disabled = $this->resolveDisabledState($disabled);
        $this->hidden = $this->resolveHiddenState();
    }

    /**
     * Normalize options to ensure consistent value => label format.
     */
    protected function normalizeOptions(array $options): array
    {
        $normalized = [];

        foreach ($options as $key => $value) {
            // Handle grouped options (optgroup)
            if (is_array($value) && ! isset($value['label'])) {
                $normalized[$key] = $this->normalizeOptions($value);

                continue;
            }

            // Handle array format: ['label' => 'Display', 'value' => 'actual']
            if (is_array($value) && isset($value['label'])) {
                $normalized[$value['value'] ?? $key] = $value['label'];

                continue;
            }

            // Handle flat array: ['option1', 'option2']
            if (is_int($key)) {
                $normalized[$value] = $value;

                continue;
            }

            // Handle associative array: ['value' => 'Label']
            $normalized[$key] = $value;
        }

        return $normalized;
    }

    public function render()
    {
        return view('core-forms::components.forms.select');
    }
}
