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
 * Toggle switch component with authorization support.
 *
 * Features:
 * - Authorization via `canGate` / `canResource` props
 * - `instantSave` for Livewire real-time persistence
 * - Label and description
 * - Size variants: sm, md, lg
 * - Dark mode support
 *
 * Usage:
 * ```blade
 * <x-core-forms.toggle
 *     id="is_public"
 *     label="Public"
 *     description="Make this visible to everyone"
 *     instantSave
 *     canGate="update"
 *     :canResource="$model"
 *     wire:model="is_public"
 * />
 * ```
 */
class Toggle extends Component
{
    use HasAuthorizationProps;

    public string $id;

    public ?string $label;

    public ?string $description;

    public ?string $error;

    public string $size;

    public bool $instantSave;

    public ?string $instantSaveMethod;

    public bool $disabled;

    public bool $hidden;

    public string $trackClasses;

    public string $thumbClasses;

    public function __construct(
        string $id,
        ?string $label = null,
        ?string $description = null,
        ?string $error = null,
        string $size = 'md',
        bool $instantSave = false,
        ?string $instantSaveMethod = null,
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
        $this->size = $size;
        $this->instantSave = $instantSave;
        $this->instantSaveMethod = $instantSaveMethod;

        // Authorization setup
        $this->canGate = $canGate;
        $this->canResource = $canResource;
        $this->canHide = $canHide;

        // Resolve states based on authorization
        $this->disabled = $this->resolveDisabledState($disabled);
        $this->hidden = $this->resolveHiddenState();

        // Resolve size classes
        [$this->trackClasses, $this->thumbClasses] = $this->resolveSizeClasses();
    }

    protected function resolveSizeClasses(): array
    {
        return match ($this->size) {
            'sm' => ['w-8 h-4', 'w-3 h-3'],
            'lg' => ['w-14 h-7', 'w-6 h-6'],
            default => ['w-11 h-6', 'w-5 h-5'],
        };
    }

    /**
     * Get the wire:change directive for instant save.
     */
    public function wireChange(): ?string
    {
        if (! $this->instantSave) {
            return null;
        }

        // Default to 'save' method if not specified
        return $this->instantSaveMethod ?? 'save';
    }

    public function render()
    {
        return view('core-forms::components.forms.toggle');
    }
}
