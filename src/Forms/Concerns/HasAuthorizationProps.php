<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Forms\Concerns;

/**
 * Provides authorization-aware props for form components.
 *
 * Components using this trait can accept `canGate` and `canResource` props
 * to automatically disable or hide based on user permissions.
 *
 * Usage:
 * ```blade
 * <x-core-forms.input canGate="update" :canResource="$biolink" id="name" />
 * <x-core-forms.button canGate="delete" :canResource="$biolink" canHide>Delete</x-core-forms.button>
 * ```
 */
trait HasAuthorizationProps
{
    /**
     * The gate/ability to check (e.g., 'update', 'delete').
     */
    public ?string $canGate = null;

    /**
     * The resource/model to check the gate against.
     */
    public mixed $canResource = null;

    /**
     * Whether to hide the component (instead of disabling) when unauthorized.
     */
    public bool $canHide = false;

    /**
     * Resolve whether the component should be disabled based on authorization.
     *
     * If `canGate` and `canResource` are both provided and the user lacks
     * the required permission, the component will be disabled.
     *
     * @param  bool  $explicitlyDisabled  Whether the component was explicitly disabled via props
     */
    protected function resolveDisabledState(bool $explicitlyDisabled = false): bool
    {
        // Already explicitly disabled - no need to check authorization
        if ($explicitlyDisabled) {
            return true;
        }

        // No authorization check configured
        if (! $this->canGate || $this->canResource === null) {
            return false;
        }

        // Check if user can perform the action
        return ! $this->userCan();
    }

    /**
     * Resolve whether the component should be hidden based on authorization.
     *
     * Only hides if `canHide` is true and the user lacks permission.
     */
    protected function resolveHiddenState(): bool
    {
        // Not configured to hide on unauthorized
        if (! $this->canHide) {
            return false;
        }

        // No authorization check configured
        if (! $this->canGate || $this->canResource === null) {
            return false;
        }

        // Hide if user cannot perform the action
        return ! $this->userCan();
    }

    /**
     * Check if the current user can perform the gate action on the resource.
     */
    protected function userCan(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can($this->canGate, $this->canResource);
    }
}
